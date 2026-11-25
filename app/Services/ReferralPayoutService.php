<?php

namespace App\Services;

use App\Models\ReferralAuditLog;
use App\Models\ReferralPayoutMethod;
use App\Models\ReferralPayoutRequest;
use App\Models\ReferralRule;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReferralPayoutService
{
    /**
     * Add or update a seller's payout method (Bank Account or UPI).
     */
    public function savePayoutMethod(User $user, array $data): ReferralPayoutMethod
    {
        $type = $data['payout_type'] ?? 'bank';

        if ($type === 'upi') {
            $cleanUpi = strtolower(trim($data['upi_id'] ?? ''));
            if (!preg_match('/^[a-zA-Z0-9.\-_]{2,256}@[a-zA-Z]{2,64}$/', $cleanUpi)) {
                throw ValidationException::withMessages(['upi_id' => 'Please enter a valid UPI ID (e.g. name@okhdfcbank or 9876543210@paytm).']);
            }

            // Set as default if requested or if it's the first method
            $isDefault = !empty($data['is_default']) || !$user->referralPayoutMethods()->exists();

            if ($isDefault) {
                ReferralPayoutMethod::where('user_id', $user->id)->update(['is_default' => false]);
            }

            $method = ReferralPayoutMethod::create([
                'user_id'             => $user->id,
                'payout_type'         => 'upi',
                'account_holder_name' => trim($data['account_holder_name'] ?? $user->name),
                'upi_id'              => $cleanUpi,
                'is_default'          => $isDefault,
                'is_verified'         => true,
            ]);
        } else {
            // Bank Account
            $cleanAcc = preg_replace('/\s+/', '', (string)($data['account_number'] ?? ''));
            $cleanIfsc = strtoupper(preg_replace('/\s+/', '', (string)($data['ifsc_code'] ?? '')));

            if (!preg_match('/^\d{9,18}$/', $cleanAcc)) {
                throw ValidationException::withMessages(['account_number' => 'Bank Account number must contain between 9 and 18 digits.']);
            }

            if (!preg_match('/^[A-Z]{4}0[A-Z0-9]{6}$/', $cleanIfsc)) {
                throw ValidationException::withMessages(['ifsc_code' => 'Please enter a valid 11-character Indian IFSC code (e.g. HDFC0001234).']);
            }

            $isDefault = !empty($data['is_default']) || !$user->referralPayoutMethods()->exists();

            if ($isDefault) {
                ReferralPayoutMethod::where('user_id', $user->id)->update(['is_default' => false]);
            }

            $method = ReferralPayoutMethod::create([
                'user_id'             => $user->id,
                'payout_type'         => 'bank',
                'account_holder_name' => trim($data['account_holder_name'] ?? $user->name),
                'bank_name'           => trim($data['bank_name'] ?? 'Bank'),
                'account_number'      => $cleanAcc,
                'ifsc_code'           => $cleanIfsc,
                'is_default'          => $isDefault,
                'is_verified'         => true,
            ]);
        }

        ReferralAuditLog::log('payout_method_saved', $method, [
            'type'   => $method->payout_type,
            'masked' => $method->masked_account,
        ], $user->id);

        return $method;
    }

    /**
     * Submit a withdrawal request against available payable referral balance.
     */
    public function requestWithdrawal(User $user, float $amount, ?int $payoutMethodId = null): ReferralPayoutRequest
    {
        return DB::transaction(function () use ($user, $amount, $payoutMethodId) {
            $lockedUser = User::lockForUpdate()->find($user->id);
            if (!$lockedUser) {
                throw new \Exception('User not found.');
            }

            $activeRule = ReferralRule::getActiveRule();
            $minWithdrawal = $activeRule ? (float) $activeRule->min_withdrawal_amount : 500.00;

            if ($amount < $minWithdrawal) {
                throw ValidationException::withMessages([
                    'amount' => "Minimum withdrawal amount is ₹" . number_format($minWithdrawal, 2) . ".",
                ]);
            }

            $availableBalance = $lockedUser->available_referral_balance;
            if ($amount > $availableBalance) {
                throw ValidationException::withMessages([
                    'amount' => "Requested amount (₹" . number_format($amount, 2) . ") exceeds your available payable referral balance (₹" . number_format($availableBalance, 2) . ").",
                ]);
            }

            // Resolve payout method
            $payoutMethod = null;
            if ($payoutMethodId) {
                $payoutMethod = ReferralPayoutMethod::where('user_id', $lockedUser->id)->find($payoutMethodId);
            }
            if (!$payoutMethod) {
                $payoutMethod = $lockedUser->defaultReferralPayoutMethod;
            }

            if (!$payoutMethod) {
                throw ValidationException::withMessages([
                    'payout_method_id' => 'Please add and select a verified Bank Account or UPI ID before requesting withdrawal.',
                ]);
            }

            $request = ReferralPayoutRequest::create([
                'user_id'                => $lockedUser->id,
                'payout_method_id'       => $payoutMethod->id,
                'payout_method_snapshot' => $payoutMethod->toSnapshot(),
                'amount'                 => $amount,
                'status'                 => ReferralPayoutRequest::STATUS_PENDING,
            ]);

            // Run Payout Fraud Risk Scoring
            app(ReferralFraudDetectionEngine::class)->evaluatePayoutRequestRisk($request);

            ReferralAuditLog::log('payout_requested', $request, [
                'amount' => $amount,
                'method' => $payoutMethod->toSnapshot(),
            ], $lockedUser->id);

            return $request;
        });
    }

    /**
     * Admin Workflow: Process, approve, mark paid, or reject a payout request.
     */
    public function processPayout(ReferralPayoutRequest $request, string $action, int $adminId, array $payload = []): ReferralPayoutRequest
    {
        $admin = User::findOrFail($adminId);
        if (!$admin->isAdmin()) {
            throw new \Exception('Unauthorized action. Admin privilege required.');
        }

        return DB::transaction(function () use ($request, $action, $admin, $payload) {
            $locked = ReferralPayoutRequest::lockForUpdate()->findOrFail($request->id);

            switch ($action) {
                case 'approve':
                    $locked->status = ReferralPayoutRequest::STATUS_APPROVED;
                    $locked->processed_by = $admin->id;
                    $locked->processed_at = now();
                    $locked->rejection_reason = null;
                    break;

                case 'mark_processing':
                    $locked->status = ReferralPayoutRequest::STATUS_PROCESSING;
                    $locked->processed_by = $admin->id;
                    $locked->processed_at = now();
                    break;

                case 'mark_paid':
                    $ref = trim($payload['payment_reference'] ?? '');
                    if (empty($ref)) {
                        throw ValidationException::withMessages(['payment_reference' => 'Bank UTR / Transaction Reference is required.']);
                    }

                    $locked->status = ReferralPayoutRequest::STATUS_PAID;
                    $locked->payment_reference = $ref;
                    if (!empty($payload['payment_proof_path'])) {
                        $locked->payment_proof_path = $payload['payment_proof_path'];
                    }
                    if (!empty($payload['admin_notes'])) {
                        $locked->admin_notes = $payload['admin_notes'];
                    }
                    $locked->processed_by = $admin->id;
                    $locked->processed_at = now();
                    $locked->paid_at = now();
                    break;

                case 'reject':
                    $reason = trim($payload['rejection_reason'] ?? '');
                    if (empty($reason)) {
                        throw ValidationException::withMessages(['rejection_reason' => 'A rejection reason is required.']);
                    }

                    $locked->status = ReferralPayoutRequest::STATUS_REJECTED;
                    $locked->rejection_reason = $reason;
                    $locked->processed_by = $admin->id;
                    $locked->processed_at = now();
                    break;

                default:
                    throw new \InvalidArgumentException("Invalid payout action: {$action}");
            }

            $locked->save();

            ReferralAuditLog::log("payout_{$action}", $locked, [
                'action'    => $action,
                'amount'    => $locked->amount,
                'status'    => $locked->status,
                'reference' => $locked->payment_reference,
                'reason'    => $locked->rejection_reason,
            ], $admin->id);

            return $locked;
        });
    }

    /**
     * Transfer available referral earnings directly into seller's shipping recharge wallet.
     */
    public function transferToShippingWallet(User $user, float $amount): ReferralPayoutRequest
    {
        return DB::transaction(function () use ($user, $amount) {
            $lockedUser = User::lockForUpdate()->find($user->id);
            if (!$lockedUser) {
                throw new \Exception('User not found.');
            }

            if ($amount <= 0) {
                throw ValidationException::withMessages(['amount' => 'Transfer amount must be greater than zero.']);
            }

            $availableBalance = $lockedUser->available_referral_balance;
            if ($amount > $availableBalance) {
                throw ValidationException::withMessages([
                    'amount' => "Requested amount (₹" . number_format($amount, 2) . ") exceeds your available referral balance (₹" . number_format($availableBalance, 2) . ").",
                ]);
            }

            // 1. Credit the Shipping Wallet
            $ref = 'REF_REWARD_' . strtoupper(\Illuminate\Support\Str::random(8));
            app(\App\Services\WalletService::class)->credit(
                $lockedUser->id,
                $amount,
                'Referral Commission Converted to Shipping Balance',
                $ref
            );

            // 2. Record settled Payout Request
            $request = ReferralPayoutRequest::create([
                'user_id'                => $lockedUser->id,
                'payout_method_id'       => null,
                'payout_method_snapshot' => [
                    'payout_type'    => 'shipping_wallet',
                    'masked_display' => 'Shipping Recharge Wallet (Instant Credit)',
                ],
                'amount'                 => $amount,
                'status'                 => ReferralPayoutRequest::STATUS_PAID,
                'payment_reference'      => $ref,
                'processed_by'           => $lockedUser->id,
                'processed_at'           => now(),
                'paid_at'                => now(),
                'admin_notes'            => 'Instant transfer to OneStall Shipping Recharge Wallet',
            ]);

            ReferralAuditLog::log('referral_wallet_transfer', $request, [
                'amount'    => $amount,
                'reference' => $ref,
            ], $lockedUser->id);

            return $request;
        });
    }
}
