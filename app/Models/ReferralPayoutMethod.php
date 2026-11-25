<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralPayoutMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'payout_type',
        'account_holder_name',
        'bank_name',
        'account_number',
        'ifsc_code',
        'upi_id',
        'is_default',
        'is_verified',
    ];

    protected $casts = [
        'is_default'  => 'boolean',
        'is_verified' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payoutRequests()
    {
        return $this->hasMany(ReferralPayoutRequest::class, 'payout_method_id');
    }

    /**
     * Mask sensitive bank account number (e.g. •••• •••• 1234).
     */
    public function getMaskedAccountAttribute(): string
    {
        if ($this->payout_type === 'upi') {
            return $this->upi_id ?? 'N/A';
        }

        if (empty($this->account_number)) {
            return 'N/A';
        }

        $clean = preg_replace('/\s+/', '', (string) $this->account_number);
        $len = strlen($clean);
        if ($len <= 4) {
            return $clean;
        }

        $last4 = substr($clean, -4);
        return '•••• •••• ' . $last4;
    }

    /**
     * Generate an immutable snapshot of payout details for safe logging.
     */
    public function toSnapshot(): array
    {
        if ($this->payout_type === 'upi') {
            return [
                'type'           => 'upi',
                'upi_id'         => $this->upi_id,
                'holder_name'    => $this->account_holder_name,
                'masked_display' => 'UPI: ' . $this->upi_id,
            ];
        }

        return [
            'type'           => 'bank',
            'holder_name'    => $this->account_holder_name,
            'bank_name'      => $this->bank_name,
            'account_masked' => $this->masked_account,
            'ifsc_code'      => strtoupper($this->ifsc_code ?? ''),
            'masked_display' => ($this->bank_name ? $this->bank_name . ' - ' : '') . $this->masked_account . ' (' . strtoupper($this->ifsc_code ?? '') . ')',
        ];
    }
}
