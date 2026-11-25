<?php

namespace App\Services;

use App\Models\ReferralCommission;
use App\Models\ReferralRule;
use App\Models\SellerReferral;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReferralCommissionEngine
{
    /**
     * Evaluate and process referral commission for a delivered shipment.
     * Transaction-safe and idempotent: duplicate triggers will never double-credit.
     */
    public function evaluateDeliveryCommission(Shipment $shipment): array
    {
        return DB::transaction(function () use ($shipment) {
            // Row-level lock on shipment
            $lockedShipment = Shipment::lockForUpdate()->find($shipment->id);
            if (!$lockedShipment) {
                return ['success' => false, 'reason' => 'Shipment not found.'];
            }

            // 1. Genuine Delivery Check: Only genuine Delivered shipments qualify.
            $status = strtolower(trim($lockedShipment->status));
            if ($status !== 'delivered') {
                return ['success' => false, 'reason' => "Shipment status is '{$lockedShipment->status}', not delivered."];
            }

            // Never qualify Cancelled or RTO shipments
            if (str_contains($status, 'cancel') || str_contains($status, 'rto')) {
                return ['success' => false, 'reason' => 'Cancelled and RTO shipments do not qualify for referral commissions.'];
            }

            // 2. Attribution Check: Did this shipper register via referral?
            $referral = SellerReferral::where('referred_id', $lockedShipment->user_id)->first();
            if (!$referral) {
                return ['success' => false, 'reason' => 'Shipper has no referral attribution.'];
            }

            // Ensure referral status is not disqualified
            if ($referral->status === 'disqualified') {
                return ['success' => false, 'reason' => 'Referral attribution has been disqualified.'];
            }

            // 3. Referrer Verification Check
            $referrer = User::find($referral->referrer_id);
            if (!$referrer || !$referrer->isEligibleReferrer()) {
                return ['success' => false, 'reason' => 'Referrer account is inactive or ineligible.'];
            }

            // 4. Active Rule Lookup
            $rule = ReferralRule::getActiveRule();
            if (!$rule) {
                // Create a standard default rule if none exists
                $rule = ReferralRule::create([
                    'name'                         => 'Default 5% Referral Commission',
                    'reward_type'                  => 'percentage',
                    'reward_value'                 => 5.00,
                    'trigger_event'                => 'first_delivered_shipment',
                    'min_shipments_required'       => 1,
                    'min_order_value'              => 50.00,
                    'validity_days'                => 90,
                    'max_commission_per_referral'  => 5000.00,
                    'requires_kyc'                 => true,
                    'requires_phone_verified'      => false,
                    'is_active'                    => true,
                ]);
            }

            // 5. Idempotency Key: Unique key for this referral + shipment + rule
            $idempotencyKey = "REFCOMM_{$referral->id}_{$lockedShipment->id}_{$rule->id}";

            $existing = ReferralCommission::where('idempotency_key', $idempotencyKey)->first();
            if ($existing) {
                return [
                    'success'    => true,
                    'message'    => 'Commission already exists for this shipment.',
                    'commission' => $existing,
                ];
            }

            // Also check if any commission already exists for this shipment and referrer
            $existingShipmentComm = ReferralCommission::where('shipment_id', $lockedShipment->id)
                ->where('referrer_id', $referrer->id)
                ->first();
            if ($existingShipmentComm) {
                return [
                    'success'    => true,
                    'message'    => 'Commission already recorded for this shipment.',
                    'commission' => $existingShipmentComm,
                ];
            }

            // 6. Validity Window Check (Qualifying period from registration)
            $referralAgeDays = $referral->created_at->diffInDays(now());
            if ($referralAgeDays > $rule->validity_days) {
                return [
                    'success' => false,
                    'reason'  => "Shipment delivered outside the {$rule->validity_days}-day referral eligibility window.",
                ];
            }

            // 7. Base Amount Calculation (freight / shipping charge)
            $baseAmount = (float) ($lockedShipment->shipping_charge ?: $lockedShipment->total_amount ?: 0);
            if ($baseAmount < (float) $rule->min_order_value) {
                return [
                    'success' => false,
                    'reason'  => "Shipment value (₹{$baseAmount}) is below minimum requirement of ₹{$rule->min_order_value}.",
                ];
            }

            // 8. Trigger Event Check
            if ($rule->trigger_event === 'first_delivered_shipment') {
                $priorCommissions = ReferralCommission::where('seller_referral_id', $referral->id)
                    ->whereIn('status', [
                        ReferralCommission::STATUS_PENDING,
                        ReferralCommission::STATUS_UNDER_REVIEW,
                        ReferralCommission::STATUS_APPROVED,
                        ReferralCommission::STATUS_PAYABLE,
                        ReferralCommission::STATUS_PAID,
                    ])
                    ->count();

                if ($priorCommissions >= 1) {
                    return [
                        'success' => false,
                        'reason'  => 'Rule is limited to the first delivered shipment, which has already been rewarded.',
                    ];
                }
            }

            // 9. Commission Amount Calculation
            $rateApplied = (float) $rule->reward_value;
            if ($rule->reward_type === 'percentage') {
                $commissionAmount = round(($baseAmount * $rateApplied) / 100, 2);
            } else {
                $commissionAmount = round($rateApplied, 2);
            }

            if ($commissionAmount <= 0) {
                return ['success' => false, 'reason' => 'Calculated commission amount is zero.'];
            }

            // Cap check
            if ($rule->max_commission_per_referral && $rule->max_commission_per_referral > 0) {
                $earnedSoFar = (float) ReferralCommission::where('seller_referral_id', $referral->id)
                    ->whereIn('status', [
                        ReferralCommission::STATUS_APPROVED,
                        ReferralCommission::STATUS_PAYABLE,
                        ReferralCommission::STATUS_PAID,
                    ])
                    ->sum('commission_amount');

                $remainingCap = max(0, (float) $rule->max_commission_per_referral - $earnedSoFar);
                $commissionAmount = min($commissionAmount, $remainingCap);

                if ($commissionAmount <= 0) {
                    return [
                        'success' => false,
                        'reason'  => 'Maximum commission cap reached for this referral.',
                    ];
                }
            }

            // 10. KYC & Verification Checks for Status Determination
            $referredUser = User::find($lockedShipment->user_id);
            $initialStatus = ReferralCommission::STATUS_APPROVED;
            $rejectionReason = null;

            if ($rule->requires_kyc && (!$referredUser || !$referredUser->isKycApproved())) {
                // If KYC is not approved yet, hold under review
                $initialStatus = ReferralCommission::STATUS_UNDER_REVIEW;
                $rejectionReason = 'Referred seller KYC is pending or not yet approved.';
            }

            if ($rule->requires_phone_verified && (!$referredUser || empty($referredUser->phone_verified_at))) {
                $initialStatus = ReferralCommission::STATUS_UNDER_REVIEW;
                $rejectionReason = 'Referred seller phone verification is pending.';
            }

            // Check if referral attribution itself was flagged or held
            if ($referral->is_held || $referral->risk_score >= 60) {
                $initialStatus = ReferralCommission::STATUS_UNDER_REVIEW;
                $rejectionReason = "Referral attribution is currently held under fraud review (Risk: {$referral->risk_level}, score: {$referral->risk_score}).";
            }

            // 11. Create the Referral Commission Ledger Entry
            $commission = ReferralCommission::create([
                'seller_referral_id' => $referral->id,
                'referrer_id'        => $referrer->id,
                'referred_id'        => $referredUser->id,
                'shipment_id'        => $lockedShipment->id,
                'rule_id'            => $rule->id,
                'commission_type'    => $rule->reward_type,
                'rate_applied'       => $rateApplied,
                'base_amount'        => $baseAmount,
                'commission_amount'  => $commissionAmount,
                'status'             => $initialStatus,
                'rejection_reason'   => $rejectionReason,
                'approved_at'        => $initialStatus === ReferralCommission::STATUS_APPROVED ? now() : null,
                'payable_at'         => $initialStatus === ReferralCommission::STATUS_APPROVED ? now() : null,
                'idempotency_key'    => $idempotencyKey,
            ]);

            // Run Commission Fraud & Risk Evaluation
            app(ReferralFraudDetectionEngine::class)->evaluateCommissionRisk($commission, $lockedShipment);

            // Update referral status
            $referral->status = 'active';
            $referral->qualified_at = $referral->qualified_at ?: now();
            $referral->save();

            Log::info("Referral Commission created: ID #{$commission->id}, Referrer #{$referrer->id}, Amount: ₹{$commissionAmount}, Status: {$initialStatus}");

            return [
                'success'    => true,
                'commission' => $commission,
            ];
        });
    }

    /**
     * Admin Workflow: Approve a pending or under_review commission.
     */
    public function approveCommission(ReferralCommission $commission, int $adminId): ReferralCommission
    {
        return DB::transaction(function () use ($commission, $adminId) {
            $locked = ReferralCommission::lockForUpdate()->findOrFail($commission->id);
            $locked->status = ReferralCommission::STATUS_APPROVED;
            $locked->approved_at = now();
            $locked->approved_by = $adminId;
            $locked->payable_at = now();
            $locked->rejection_reason = null;
            $locked->save();

            return $locked;
        });
    }

    /**
     * Admin Workflow: Mark an approved commission as payable.
     */
    public function markPayable(ReferralCommission $commission): ReferralCommission
    {
        return DB::transaction(function () use ($commission) {
            $locked = ReferralCommission::lockForUpdate()->findOrFail($commission->id);
            $locked->status = ReferralCommission::STATUS_PAYABLE;
            $locked->payable_at = now();
            $locked->save();

            return $locked;
        });
    }

    /**
     * Admin Workflow: Mark a commission as paid (with transaction reference/UTR).
     */
    public function markPaid(ReferralCommission $commission, string $paymentReference, int $adminId, ?string $notes = null): ReferralCommission
    {
        return DB::transaction(function () use ($commission, $paymentReference, $adminId, $notes) {
            $locked = ReferralCommission::lockForUpdate()->findOrFail($commission->id);
            $locked->status = ReferralCommission::STATUS_PAID;
            $locked->paid_at = now();
            $locked->paid_by = $adminId;
            $locked->payment_reference = $paymentReference;
            if ($notes) {
                $locked->payout_notes = $notes;
            }
            $locked->save();

            return $locked;
        });
    }

    /**
     * Admin Workflow: Reject a commission with reason.
     */
    public function rejectCommission(ReferralCommission $commission, string $reason, int $adminId): ReferralCommission
    {
        return DB::transaction(function () use ($commission, $reason, $adminId) {
            $locked = ReferralCommission::lockForUpdate()->findOrFail($commission->id);
            $locked->status = ReferralCommission::STATUS_REJECTED;
            $locked->rejection_reason = $reason;
            $locked->approved_by = $adminId;
            $locked->save();

            return $locked;
        });
    }
}
