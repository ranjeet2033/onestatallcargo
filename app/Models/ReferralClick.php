<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralClick extends Model
{
    use HasFactory;

    protected $fillable = [
        'referral_code',
        'referrer_id',
        'ip_address',
        'user_agent',
        'referrer_url',
        'landing_page',
        'converted_at',
    ];

    protected $casts = [
        'converted_at' => 'datetime',
    ];

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referral()
    {
        return $this->hasOne(SellerReferral::class, 'click_id');
    }
}
