<?php

namespace App\Services;

use App\Models\Kyc;
use App\Models\ReferralAuditLog;
use App\Models\ReferralCommission;
use App\Models\ReferralFraudInvestigation;
use App\Models\ReferralPayoutMethod;
use App\Models\ReferralPayoutRequest;
use App\Models\SellerReferral;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReferralFraudDetectionEngine
{
    /**
     * Disposable / Burner email domains commonly used for fake registrations.
     */
    protected array $disposableDomains = [
        'mailinator.com', 'tempmail.com', '10minutemail.com', 'guerrillamail.com',
        'throwawaymail.com', 'yopmail.com', 'trashmail.com', 'sharklasers.com',
        'fakeinbox.com', 'getairmail.com', 'dispostable.com', 'mohmal.com'
    ];

    /**
     * Evaluate and calculate fraud risk score for a seller referral attribution.
     * Generates a 0-100 risk score and classifies into Low, Medium, High, or Critical.
     */
    public function evaluateReferral(SellerReferral $referral, ?Request $request = null): array
    {
        $referrer = $referral->referrer;
        $referred = $referral->referred;

        if (!$referrer || !$referred) {
            return [
                'score'   => 0,
                'level'   => SellerReferral::RISK_LOW,
                'factors' => [],
            ];
        }

        $score = 0;
        $factors = [];

        // 1. SELF-REFERRAL CHECKS (Critical)
        if ($referrer->id === $referred->id) {
            $score += 100;
            $factors[] = [
                'code'        => 'SELF_REFERRAL_ID',
                'severity'    => 'critical',
                'description' => 'Referrer and Referred user accounts have identical user ID.',
                'points'      => 100,
            ];
        }

        // Phone normalization & duplicate check
        $referrerPhone = $this->normalizePhone($referrer->phone);
        $referredPhone = $this->normalizePhone($referred->phone);

        if (!empty($referrerPhone) && !empty($referredPhone) && $referrerPhone === $referredPhone) {
            $score += 100;
            $factors[] = [
                'code'        => 'MATCHING_PHONE_NUMBER',
                'severity'    => 'critical',
                'description' => 'Referrer and Referred share the exact same contact phone number.',
                'points'      => 100,
            ];
        }

        // Email normalization check (handles gmail + subaddressing)
        $cleanReferrerEmail = $this->normalizeEmail($referrer->email);
        $cleanReferredEmail = $this->normalizeEmail($referred->email);

        if (!empty($cleanReferrerEmail) && !empty($cleanReferredEmail) && $cleanReferrerEmail === $cleanReferredEmail) {
            $score += 90;
            $factors[] = [
                'code'        => 'MATCHING_EMAIL_ADDRESS',
                'severity'    => 'critical',
                'description' => 'Referrer and Referred share the same base email address (sub-addressing detected).',
                'points'      => 90,
            ];
        }

        // 2. DUPLICATE GSTIN / PAN CHECKS (High / Critical)
        $referrerPan = $this->getUserPan($referrer);
        $referredPan = $this->getUserPan($referred);

        if (!empty($referrerPan) && !empty($referredPan) && $referrerPan === $referredPan) {
            $score += 90;
            $factors[] = [
                'code'        => 'MATCHING_PAN_NUMBER',
                'severity'    => 'critical',
                'description' => "PAN number ({$referredPan}) matches referrer's registered PAN.",
                'points'      => 90,
            ];
        }

        $referrerGst = $this->getUserGst($referrer);
        $referredGst = $this->getUserGst($referred);

        if (!empty($referrerGst) && !empty($referredGst) && $referrerGst === $referredGst) {
            $score += 90;
            $factors[] = [
                'code'        => 'MATCHING_GSTIN',
                'severity'    => 'critical',
                'description' => "GSTIN ({$referredGst}) matches referrer's registered GSTIN.",
                'points'      => 90,
            ];
        }

        // Check if referred PAN or GSTIN is already used by another distinct seller
        if (!empty($referredPan)) {
            $otherPanUsers = Kyc::where('pan_number', $referredPan)
                ->where('user_id', '!=', $referred->id)
                ->where('user_id', '!=', $referrer->id)
                ->count();
            if ($otherPanUsers > 0) {
                $score += 65;
                $factors[] = [
                    'code'        => 'DUPLICATE_PAN_SYSTEM',
                    'severity'    => 'high',
                    'description' => "PAN number is already associated with {$otherPanUsers} other user account(s).",
                    'points'      => 65,
                ];
            }
        }

        // 3. SHARED PAYOUT DETAILS (Bank / UPI sharing between accounts)
        $sharedPayoutMethods = $this->checkSharedPayoutDetails($referrer->id, $referred->id);
        if ($sharedPayoutMethods > 0) {
            $score += 85;
            $factors[] = [
                'code'        => 'SHARED_PAYOUT_METHODS',
                'severity'    => 'critical',
                'description' => 'Bank account or UPI ID is shared between referrer and referred seller.',
                'points'      => 85,
            ];
        }

        // 4. SUSPICIOUS IP & DEVICE PATTERNS
        // IP match between referrer and referee
        $ip = $referral->ip_address ?: ($request ? $request->ip() : null);
        $referrerRecentIps = SellerReferral::where('referrer_id', $referrer->id)
            ->whereNotNull('ip_address')
            ->pluck('ip_address')
            ->toArray();

        if ($ip && in_array($ip, $referrerRecentIps)) {
            $score += 20;
            $factors[] = [
                'code'        => 'SHARED_IP_ADDRESS',
                'severity'    => 'medium',
                'description' => "Registration IP ({$ip}) matches referrer's recorded network IP.",
                'points'      => 20,
            ];
        }

        // IP Velocity check: Multiple referral accounts registered from same IP within 24 hours
        if ($ip) {
            $recentSameIpCount = SellerReferral::where('ip_address', $ip)
                ->where('created_at', '>=', now()->subHours(24))
                ->count();
            if ($recentSameIpCount > 3) {
                $score += 30;
                $factors[] = [
                    'code'        => 'HIGH_IP_VELOCITY',
                    'severity'    => 'medium',
                    'description' => "High IP registration velocity: {$recentSameIpCount} accounts registered from {$ip} in last 24h.",
                    'points'      => 30,
                ];
            }
        }

        // Device fingerprint match
        if (!empty($referral->device_fingerprint)) {
            $sharedFingerprintCount = SellerReferral::where('device_fingerprint', $referral->device_fingerprint)
                ->where('referred_id', '!=', $referred->id)
                ->count();
            if ($sharedFingerprintCount > 0) {
                $score += 25;
                $factors[] = [
                    'code'        => 'SHARED_DEVICE_FINGERPRINT',
                    'severity'    => 'medium',
                    'description' => "Device fingerprint matches {$sharedFingerprintCount} other referral registration(s).",
                    'points'      => 25,
                ];
            }
        }

        // 5. FAKE REGISTRATION PATTERNS
        $referredDomain = substr(strrchr($referred->email, "@"), 1);
        if (in_array(strtolower($referredDomain), $this->disposableDomains)) {
            $score += 50;
            $factors[] = [
                'code'        => 'DISPOSABLE_EMAIL_DOMAIN',
                'severity'    => 'high',
                'description' => "Referred email domain ({$referredDomain}) is a known disposable/burner email service.",
                'points'      => 50,
            ];
        }

        // Check for email sub-addressing (+ character)
        if (str_contains($referred->email, '+')) {
            $score += 20;
            $factors[] = [
                'code'        => 'EMAIL_SUBADDRESSING',
                'severity'    => 'low',
                'description' => 'Email contains sub-addressing alias (+ tag).',
                'points'      => 20,
            ];
        }

        // 6. EXISTING SELLER ACCOUNT ABUSE
        // If the referred account was registered long before the referral was recorded
        if ($referred->created_at && $referred->created_at->lt($referral->created_at->subDays(2))) {
            $score += 50;
            $factors[] = [
                'code'        => 'PRE_EXISTING_SELLER_ACCOUNT',
                'severity'    => 'high',
                'description' => 'Referred account was already an established user before referral attribution was registered.',
                'points'      => 50,
            ];
        }

        // Determine Final Classification
        $score = min(100, max(0, $score));
        $level = $this->classifyRiskScore($score);

        $referral->risk_score = $score;
        $referral->risk_level = $level;
        $referral->risk_factors = $factors;
        $referral->is_flagged = ($score >= 60);

        // Put on hold if high or critical
        if ($score >= 60) {
            $referral->is_held = true;
        }

        $referral->saveQuietly();

        return [
            'score'   => $score,
            'level'   => $level,
            'factors' => $factors,
            'held'    => $referral->is_held,
        ];
    }

    /**
     * Evaluate fraud risk for a qualifying shipment referral commission.
     */
    public function evaluateCommissionRisk(ReferralCommission $commission, Shipment $shipment): array
    {
        $score = 0;
        $factors = [];

        // 1. Inherit underlying referral attribution risk
        $referral = $commission->referral;
        if ($referral && $referral->risk_score > 0) {
            $inheritedScore = (int) round($referral->risk_score * 0.5);
            $score += $inheritedScore;
            $factors[] = [
                'code'        => 'INHERITED_REFERRAL_RISK',
                'severity'    => $referral->risk_level,
                'description' => "Inherited {$inheritedScore} risk points from referral attribution (#{$referral->id}, {$referral->risk_level}).",
                'points'      => $inheritedScore,
            ];
        }

        // 2. CIRCULAR SHIPMENT / ADDRESS COLLUSION CHECK
        $referrer = $commission->referrer;
        $referred = $commission->referred;

        if ($referrer && $referred) {
            // Check if delivery phone matches referrer's phone
            $deliveryPhone = $this->normalizePhone($shipment->delivery_phone ?? $shipment->receiver_phone ?? '');
            $referrerPhone = $this->normalizePhone($referrer->phone);

            if (!empty($deliveryPhone) && !empty($referrerPhone) && $deliveryPhone === $referrerPhone) {
                $score += 75;
                $factors[] = [
                    'code'        => 'CIRCULAR_DELIVERY_PHONE',
                    'severity'    => 'critical',
                    'description' => "Shipment recipient phone number ({$deliveryPhone}) matches referrer's phone.",
                    'points'      => 75,
                ];
            }

            // Check if delivery pincode matches referrer's company pincode
            if (!empty($shipment->delivery_pincode) && !empty($referrer->company_pincode) && $shipment->delivery_pincode === $referrer->company_pincode) {
                $score += 25;
                $factors[] = [
                    'code'        => 'SAME_PINCODE_DELIVERY',
                    'severity'    => 'medium',
                    'description' => "Delivery pincode ({$shipment->delivery_pincode}) matches referrer's warehouse/registered pincode.",
                    'points'      => 25,
                ];
            }
        }

        // 3. ABNORMAL CANCELLATION / RTO RATE ON REFERRED SELLER
        $totalShipments = Shipment::where('user_id', $referred->id)->count();
        if ($totalShipments >= 5) {
            $badShipments = Shipment::where('user_id', $referred->id)
                ->where(function ($q) {
                    $q->where('status', 'LIKE', '%cancel%')
                      ->orWhere('status', 'LIKE', '%rto%');
                })->count();

            $badRate = ($badShipments / $totalShipments) * 100;
            if ($badRate > 40) {
                $score += 40;
                $factors[] = [
                    'code'        => 'HIGH_RTO_CANCELLATION_RATE',
                    'severity'    => 'high',
                    'description' => "Referred shipper has high failure rate ({$badShipments}/{$totalShipments} shipments, " . round($badRate) . "% cancelled/RTO).",
                    'points'      => 40,
                ];
            }
        }

        $score = min(100, max(0, $score));
        $level = $this->classifyRiskScore($score);

        $commission->risk_score = $score;
        $commission->risk_level = $level;
        $commission->risk_factors = $factors;

        // If high or critical, hold the commission under review
        if ($score >= 60) {
            $commission->is_held = true;
            $commission->status = ReferralCommission::STATUS_UNDER_REVIEW;
            $commission->rejection_reason = "Flagged by Fraud Engine: Risk score {$score} ({$level}). Awaiting manual investigation.";
        }

        $commission->saveQuietly();

        return [
            'score'   => $score,
            'level'   => $level,
            'factors' => $factors,
            'held'    => $commission->is_held,
        ];
    }

    /**
     * Evaluate fraud risk for a seller withdrawal request.
     */
    public function evaluatePayoutRequestRisk(ReferralPayoutRequest $payoutRequest): array
    {
        $user = $payoutRequest->user;
        $score = 0;
        $factors = [];

        // 1. Unverified KYC check
        $hasApprovedKyc = Kyc::where('user_id', $user->id)->where('status', 'approved')->exists();
        if (!$hasApprovedKyc) {
            $score += 35;
            $factors[] = [
                'code'        => 'UNAPPROVED_SELLER_KYC',
                'severity'    => 'medium',
                'description' => 'Withdrawal requested without an approved KYC on file.',
                'points'      => 35,
            ];
        }

        // 2. Beneficiary name mismatch check
        $snapshot = $payoutRequest->payout_method_snapshot ?? [];
        $accountHolderName = strtolower(trim($snapshot['account_holder_name'] ?? ''));
        $userName = strtolower(trim($user->name));
        $companyName = strtolower(trim($user->company_name ?? ''));

        if (!empty($accountHolderName) && !str_contains($userName, $accountHolderName) && !str_contains($accountHolderName, $userName)) {
            if (empty($companyName) || (!str_contains($companyName, $accountHolderName) && !str_contains($accountHolderName, $companyName))) {
                $score += 40;
                $factors[] = [
                    'code'        => 'BENEFICIARY_NAME_MISMATCH',
                    'severity'    => 'medium',
                    'description' => "Payout beneficiary name ('{$snapshot['account_holder_name']}') does not match seller account name ('{$user->name}').",
                    'points'      => 40,
                ];
            }
        }

        // 3. Shared Payout Account with Other Sellers
        $accountNum = $snapshot['account_number'] ?? null;
        $upiId = $snapshot['upi_id'] ?? null;

        if (!empty($accountNum)) {
            $otherUsersCount = ReferralPayoutMethod::where('account_number', $accountNum)
                ->where('user_id', '!=', $user->id)
                ->distinct('user_id')
                ->count();
            if ($otherUsersCount > 0) {
                $score += 85;
                $factors[] = [
                    'code'        => 'SHARED_BANK_ACCOUNT',
                    'severity'    => 'critical',
                    'description' => "Bank account number is shared with {$otherUsersCount} other seller account(s).",
                    'points'      => 85,
                ];
            }
        }

        if (!empty($upiId)) {
            $otherUpiUsersCount = ReferralPayoutMethod::where('upi_id', $upiId)
                ->where('user_id', '!=', $user->id)
                ->distinct('user_id')
                ->count();
            if ($otherUpiUsersCount > 0) {
                $score += 85;
                $factors[] = [
                    'code'        => 'SHARED_UPI_ID',
                    'severity'    => 'critical',
                    'description' => "UPI ID is shared with {$otherUpiUsersCount} other seller account(s).",
                    'points'      => 85,
                ];
            }
        }

        // 4. Rapid withdrawal after commission
        $recentCommission = ReferralCommission::where('referrer_id', $user->id)
            ->where('created_at', '>=', now()->subHours(2))
            ->exists();
        if ($recentCommission) {
            $score += 20;
            $factors[] = [
                'code'        => 'RAPID_WITHDRAWAL_REQUEST',
                'severity'    => 'low',
                'description' => 'Withdrawal requested within 2 hours of latest commission generation.',
                'points'      => 20,
            ];
        }

        $score = min(100, max(0, $score));
        $level = $this->classifyRiskScore($score);

        $payoutRequest->risk_score = $score;
        $payoutRequest->risk_level = $level;
        $payoutRequest->risk_factors = $factors;
        $payoutRequest->saveQuietly();

        return [
            'score'   => $score,
            'level'   => $level,
            'factors' => $factors,
        ];
    }

    /**
     * Admin Investigation Action: Hold, Release, Reject, or Verify.
     */
    public function recordInvestigation(
        User $admin,
        User $targetSeller,
        string $action,
        string $findings,
        ?SellerReferral $referral = null,
        ?ReferralCommission $commission = null,
        ?ReferralPayoutRequest $payoutRequest = null
    ): ReferralFraudInvestigation {
        if (!$admin->isAdmin()) {
            throw new \Exception('Unauthorized action. Admin privilege required.');
        }

        return DB::transaction(function () use ($admin, $targetSeller, $action, $findings, $referral, $commission, $payoutRequest) {
            $riskScore = 0;
            $riskLevel = SellerReferral::RISK_LOW;

            if ($referral) {
                $riskScore = $referral->risk_score;
                $riskLevel = $referral->risk_level;

                switch ($action) {
                    case 'hold':
                        $referral->is_held = true;
                        $referral->is_flagged = true;
                        $referral->investigated_at = now();
                        $referral->investigated_by = $admin->id;
                        $referral->investigation_notes = $findings;
                        $referral->save();
                        break;

                    case 'release':
                        $referral->is_held = false;
                        $referral->investigated_at = now();
                        $referral->investigated_by = $admin->id;
                        $referral->investigation_notes = "Released by Admin: {$findings}";
                        $referral->save();
                        break;

                    case 'reject':
                        $referral->is_held = false;
                        $referral->status = 'disqualified';
                        $referral->disqualified_reason = $findings;
                        $referral->investigated_at = now();
                        $referral->investigated_by = $admin->id;
                        $referral->investigation_notes = "Disqualified: {$findings}";
                        $referral->save();
                        break;
                }
            }

            if ($commission) {
                switch ($action) {
                    case 'hold':
                        $commission->is_held = true;
                        $commission->status = ReferralCommission::STATUS_UNDER_REVIEW;
                        $commission->rejection_reason = $findings;
                        $commission->save();
                        break;

                    case 'release':
                        $commission->is_held = false;
                        $commission->status = ReferralCommission::STATUS_APPROVED;
                        $commission->approved_at = now();
                        $commission->approved_by = $admin->id;
                        $commission->rejection_reason = null;
                        $commission->save();
                        break;

                    case 'reject':
                        $commission->is_held = false;
                        $commission->status = ReferralCommission::STATUS_REJECTED;
                        $commission->rejection_reason = $findings;
                        $commission->save();
                        break;
                }
            }

            $investigation = ReferralFraudInvestigation::create([
                'seller_referral_id' => $referral?->id,
                'commission_id'      => $commission?->id,
                'payout_request_id'  => $payoutRequest?->id,
                'user_id'            => $targetSeller->id,
                'admin_id'           => $admin->id,
                'action'             => $action,
                'risk_score'         => $riskScore,
                'risk_level'         => $riskLevel,
                'findings'           => $findings,
                'details'            => [
                    'ip'         => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ],
            ]);

            ReferralAuditLog::log("fraud_investigation_{$action}", $investigation, [
                'action'   => $action,
                'findings' => $findings,
                'target'   => $targetSeller->id,
            ], $admin->id);

            return $investigation;
        });
    }

    /**
     * Map numeric risk score (0-100) to standard Risk Classifications.
     */
    public function classifyRiskScore(int $score): string
    {
        if ($score >= 85) {
            return SellerReferral::RISK_CRITICAL;
        }
        if ($score >= 60) {
            return SellerReferral::RISK_HIGH;
        }
        if ($score >= 30) {
            return SellerReferral::RISK_MEDIUM;
        }
        return SellerReferral::RISK_LOW;
    }

    protected function normalizePhone(?string $phone): string
    {
        if (!$phone) {
            return '';
        }
        $digits = preg_replace('/\D+/', '', $phone);
        // Strip leading 91 country code if 12 digits
        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            $digits = substr($digits, 2);
        }
        return $digits;
    }

    protected function normalizeEmail(?string $email): string
    {
        if (!$email) {
            return '';
        }
        $email = strtolower(trim($email));
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return $email;
        }

        $local = $parts[0];
        $domain = $parts[1];

        // Strip subaddressing e.g. user+tag -> user
        if (str_contains($local, '+')) {
            $local = explode('+', $local)[0];
        }

        // For gmail, remove dots in local part
        if (in_array($domain, ['gmail.com', 'googlemail.com'])) {
            $local = str_replace('.', '', $local);
        }

        return "{$local}@{$domain}";
    }

    protected function getUserPan(User $user): ?string
    {
        if (!empty($user->pan_number)) {
            return strtoupper(trim($user->pan_number));
        }
        $kyc = Kyc::where('user_id', $user->id)->first();
        return $kyc && !empty($kyc->pan_number) ? strtoupper(trim($kyc->pan_number)) : null;
    }

    protected function getUserGst(User $user): ?string
    {
        if (!empty($user->gstin)) {
            return strtoupper(trim($user->gstin));
        }
        $kyc = Kyc::where('user_id', $user->id)->first();
        return $kyc && !empty($kyc->gst_number) ? strtoupper(trim($kyc->gst_number)) : null;
    }

    protected function checkSharedPayoutDetails(int $userId1, int $userId2): int
    {
        $methods1 = ReferralPayoutMethod::where('user_id', $userId1)->get();
        if ($methods1->isEmpty()) {
            return 0;
        }

        $accs1 = $methods1->pluck('account_number')->filter()->toArray();
        $upis1 = $methods1->pluck('upi_id')->filter()->toArray();

        return ReferralPayoutMethod::where('user_id', $userId2)
            ->where(function ($q) use ($accs1, $upis1) {
                if (!empty($accs1)) {
                    $q->whereIn('account_number', $accs1);
                }
                if (!empty($upis1)) {
                    $q->orWhereIn('upi_id', $upis1);
                }
            })->count();
    }
}
