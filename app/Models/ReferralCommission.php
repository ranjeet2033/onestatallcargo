<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralCommission extends Model
{
    use HasFactory;

    // Commission Status Constants
    public const STATUS_PENDING      = 'pending';
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_APPROVED     = 'approved';
    public const STATUS_REJECTED     = 'rejected';
    public const STATUS_PAYABLE      = 'payable';
    public const STATUS_PAID         = 'paid';

    protected $fillable = [
        'seller_referral_id',
        'referrer_id',
        'referred_id',
        'shipment_id',
        'rule_id',
        'commission_type',
        'rate_applied',
        'base_amount',
        'commission_amount',
        'risk_score',
        'risk_level',
        'risk_factors',
        'is_held',
        'status',
        'rejection_reason',
        'approved_at',
        'approved_by',
        'payable_at',
        'paid_at',
        'paid_by',
        'payment_reference',
        'payout_notes',
        'idempotency_key',
    ];

    protected $casts = [
        'rate_applied'      => 'decimal:2',
        'base_amount'       => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'risk_score'        => 'integer',
        'risk_factors'      => 'array',
        'is_held'           => 'boolean',
        'approved_at'       => 'datetime',
        'payable_at'        => 'datetime',
        'paid_at'           => 'datetime',
    ];

    public function referral()
    {
        return $this->belongsTo(SellerReferral::class, 'seller_referral_id');
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referred()
    {
        return $this->belongsTo(User::class, 'referred_id');
    }

    public function shipment()
    {
        return $this->belongsTo(Shipment::class, 'shipment_id');
    }

    public function rule()
    {
        return $this->belongsTo(ReferralRule::class, 'rule_id');
    }

    public function approvedByUser()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function paidByUser()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    // Scopes for statuses
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeUnderReview($query)
    {
        return $query->where('status', self::STATUS_UNDER_REVIEW);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopePayable($query)
    {
        return $query->where('status', self::STATUS_PAYABLE);
    }

    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }
}
