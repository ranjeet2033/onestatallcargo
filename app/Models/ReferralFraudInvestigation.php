<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralFraudInvestigation extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_referral_id',
        'commission_id',
        'payout_request_id',
        'user_id',
        'admin_id',
        'action',
        'risk_score',
        'risk_level',
        'findings',
        'details',
    ];

    protected $casts = [
        'details' => 'array',
    ];

    public function referral()
    {
        return $this->belongsTo(SellerReferral::class, 'seller_referral_id');
    }

    public function commission()
    {
        return $this->belongsTo(ReferralCommission::class, 'commission_id');
    }

    public function payoutRequest()
    {
        return $this->belongsTo(ReferralPayoutRequest::class, 'payout_request_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
