<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'reward_type',
        'reward_value',
        'trigger_event',
        'min_shipments_required',
        'min_order_value',
        'validity_days',
        'max_commission_per_referral',
        'min_withdrawal_amount',
        'requires_kyc',
        'requires_phone_verified',
        'auto_approve_commission',
        'is_active',
    ];

    protected $casts = [
        'reward_value' => 'decimal:2',
        'min_order_value' => 'decimal:2',
        'max_commission_per_referral' => 'decimal:2',
        'min_withdrawal_amount' => 'decimal:2',
        'requires_kyc' => 'boolean',
        'requires_phone_verified' => 'boolean',
        'auto_approve_commission' => 'boolean',
        'is_active' => 'boolean',
        'min_shipments_required' => 'integer',
        'validity_days' => 'integer',
    ];

    public function commissions()
    {
        return $this->hasMany(ReferralCommission::class, 'rule_id');
    }

    public static function getActiveRule(): ?self
    {
        return self::where('is_active', true)->orderBy('id', 'desc')->first();
    }
}
