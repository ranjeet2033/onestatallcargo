<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralPayoutRequest extends Model
{
    use HasFactory;

    // Statuses
    public const STATUS_PENDING    = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_APPROVED   = 'approved';
    public const STATUS_REJECTED   = 'rejected';
    public const STATUS_PAID       = 'paid';

    protected $fillable = [
        'user_id',
        'payout_method_id',
        'payout_method_snapshot',
        'amount',
        'risk_score',
        'risk_level',
        'risk_factors',
        'status',
        'rejection_reason',
        'payment_reference',
        'payment_proof_path',
        'admin_notes',
        'processed_by',
        'processed_at',
        'paid_at',
    ];

    protected $casts = [
        'amount'                 => 'decimal:2',
        'risk_score'             => 'integer',
        'risk_factors'           => 'array',
        'payout_method_snapshot' => 'array',
        'processed_at'           => 'datetime',
        'paid_at'                => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payoutMethod()
    {
        return $this->belongsTo(ReferralPayoutMethod::class, 'payout_method_id');
    }

    public function processedByUser()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function getMethodDisplayAttribute(): string
    {
        if (!empty($this->payout_method_snapshot['masked_display'])) {
            return $this->payout_method_snapshot['masked_display'];
        }

        if ($this->payoutMethod) {
            return $this->payoutMethod->masked_account;
        }

        return 'Default Payout Account';
    }
}
