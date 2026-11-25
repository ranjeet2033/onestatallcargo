<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerReferral extends Model
{
    use HasFactory;

    const RISK_LOW = 'low';
    const RISK_MEDIUM = 'medium';
    const RISK_HIGH = 'high';
    const RISK_CRITICAL = 'critical';

    protected $fillable = [
        'referrer_id',
        'referred_id',
        'referral_code',
        'click_id',
        'status',
        'risk_score',
        'risk_level',
        'risk_factors',
        'is_flagged',
        'is_held',
        'investigated_at',
        'investigated_by',
        'investigation_notes',
        'device_fingerprint',
        'ip_address',
        'user_agent',
        'qualified_at',
        'disqualified_reason',
    ];

    protected $casts = [
        'risk_score'      => 'integer',
        'risk_factors'    => 'array',
        'is_flagged'      => 'boolean',
        'is_held'         => 'boolean',
        'qualified_at'    => 'datetime',
        'investigated_at' => 'datetime',
    ];

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referred()
    {
        return $this->belongsTo(User::class, 'referred_id');
    }

    public function click()
    {
        return $this->belongsTo(ReferralClick::class, 'click_id');
    }

    public function commissions()
    {
        return $this->hasMany(ReferralCommission::class, 'seller_referral_id');
    }

    public function investigator()
    {
        return $this->belongsTo(User::class, 'investigated_by');
    }

    public function investigations()
    {
        return $this->hasMany(ReferralFraudInvestigation::class, 'seller_referral_id');
    }

    public function getRiskBadgeClassAttribute(): string
    {
        return match ($this->risk_level) {
            self::RISK_CRITICAL => 'badge-danger bg-danger text-white',
            self::RISK_HIGH     => 'badge-warning bg-warning text-dark',
            self::RISK_MEDIUM   => 'badge-info bg-info text-white',
            default             => 'badge-success bg-success text-white',
        };
    }
}
