<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'role',
        'phone',
        'company_name',
        'brand_name',
        'gstin',
        'pan_number',
        'business_type',
        'company_address',
        'company_city',
        'company_state',
        'company_pincode',
        'website',
        'support_phone',
        'bank_name',
        'account_number',
        'ifsc_code',
        'account_holder_name',
        'wallet_balance',
        'permissions',
        'latitude',
        'longitude',
        'last_location_at',
        'status',
        'early_cod_plan',
        'early_cod_fee',
        'label_settings',
        'referral_code',
        'referred_by',
        'phone_verified_at',
    ];

    protected $hidden = [
        'password',
        'avatar',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'permissions' => 'array',
            'label_settings' => 'array',
        ];
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'super_admin', 'operations']);
    }

    public function isSeller(): bool
    {
        return in_array($this->role, ['seller', 'aggregator', 'b2b_customer', 'corporate']);
    }

    public function isFranchise(): bool
    {
        return $this->role === 'franchise';
    }

    public function isRider(): bool
    {
        return in_array($this->role, ['rider', 'pickup_rider', 'delivery_rider']);
    }

    public function kyc()
    {
        return $this->hasOne(Kyc::class);
    }

    public function isKycApproved(): bool
    {
        return $this->kyc && $this->kyc->status === 'approved';
    }

    public function rider()
    {
        return $this->hasOne(Rider::class, 'user_id');
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class, 'user_id');
    }

    public function warehouses()
    {
        return $this->hasMany(Warehouse::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Seller Referral & Earn Relationships and Helpers
    |--------------------------------------------------------------------------
    */

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    public function referralsGiven()
    {
        return $this->hasMany(SellerReferral::class, 'referrer_id');
    }

    public function referralAttribution()
    {
        return $this->hasOne(SellerReferral::class, 'referred_id');
    }

    public function referralClicks()
    {
        return $this->hasMany(ReferralClick::class, 'referrer_id');
    }

    public function referralCommissionsEarned()
    {
        return $this->hasMany(ReferralCommission::class, 'referrer_id');
    }

    public function referralCommissionsGenerated()
    {
        return $this->hasMany(ReferralCommission::class, 'referred_id');
    }

    public function referralPayoutMethods()
    {
        return $this->hasMany(ReferralPayoutMethod::class);
    }

    public function defaultReferralPayoutMethod()
    {
        return $this->hasOne(ReferralPayoutMethod::class)->where('is_default', true);
    }

    public function referralPayoutRequests()
    {
        return $this->hasMany(ReferralPayoutRequest::class);
    }

    public function getAvailableReferralBalanceAttribute(): float
    {
        // Eligible earnings: Approved and Payable referral commissions
        $totalApproved = (float) $this->referralCommissionsEarned()
            ->whereIn('status', [ReferralCommission::STATUS_APPROVED, ReferralCommission::STATUS_PAYABLE])
            ->sum('commission_amount');

        // Committed or paid withdrawal amounts
        $lockedOrPaid = (float) $this->referralPayoutRequests()
            ->whereIn('status', [
                ReferralPayoutRequest::STATUS_PENDING,
                ReferralPayoutRequest::STATUS_PROCESSING,
                ReferralPayoutRequest::STATUS_APPROVED,
                ReferralPayoutRequest::STATUS_PAID,
            ])
            ->sum('amount');

        return max(0.00, round($totalApproved - $lockedOrPaid, 2));
    }

    public function isEligibleReferrer(): bool
    {
        // Any active seller or user can share their link; commissions qualify per rule requirements
        return $this->status !== 'rejected' && $this->status !== 'suspended';
    }

    public function getOrGenerateReferralCode(): string
    {
        if (!empty($this->referral_code)) {
            return $this->referral_code;
        }

        do {
            $prefix = 'OSC';
            $uniquePart = strtoupper(\Illuminate\Support\Str::random(7));
            $candidate = $prefix . $uniquePart;
        } while (self::where('referral_code', $candidate)->exists());

        $this->referral_code = $candidate;
        $this->saveQuietly();

        return $candidate;
    }

    public function getReferralUrlAttribute(): string
    {
        return url('/register?ref=' . $this->getOrGenerateReferralCode());
    }

    public function getReferralStatsAttribute(): array
    {
        $earned = $this->referralCommissionsEarned();
        $payouts = $this->referralPayoutRequests();

        return [
            'referral_code'       => $this->getOrGenerateReferralCode(),
            'referral_url'        => $this->referral_url,
            'total_clicks'        => $this->referralClicks()->count(),
            'total_referred'      => $this->referralsGiven()->count(),
            'active_referred'     => $this->referralsGiven()->whereIn('status', ['kyc_verified', 'active', 'completed'])->count(),
            'total_earned'        => (float) (clone $earned)->whereIn('status', [ReferralCommission::STATUS_APPROVED, ReferralCommission::STATUS_PAYABLE, ReferralCommission::STATUS_PAID])->sum('commission_amount'),
            'pending_commission'  => (float) (clone $earned)->whereIn('status', [ReferralCommission::STATUS_PENDING, ReferralCommission::STATUS_UNDER_REVIEW])->sum('commission_amount'),
            'approved_commission' => (float) (clone $earned)->whereIn('status', [ReferralCommission::STATUS_APPROVED, ReferralCommission::STATUS_PAYABLE])->sum('commission_amount'),
            'payable_commission'  => (float) (clone $earned)->where('status', ReferralCommission::STATUS_PAYABLE)->sum('commission_amount'),
            'paid_commission'     => (float) (clone $earned)->where('status', ReferralCommission::STATUS_PAID)->sum('commission_amount'),
            'available_balance'   => $this->available_referral_balance,
            'pending_payouts'     => (float) (clone $payouts)->whereIn('status', [ReferralPayoutRequest::STATUS_PENDING, ReferralPayoutRequest::STATUS_PROCESSING])->sum('amount'),
            'total_paid_out'      => (float) (clone $payouts)->where('status', ReferralPayoutRequest::STATUS_PAID)->sum('amount'),
        ];
    }
}