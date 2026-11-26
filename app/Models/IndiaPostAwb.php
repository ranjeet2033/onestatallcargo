<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IndiaPostAwb extends Model
{
    use HasFactory;

    protected $table = 'indiapost_awbs';

    protected $fillable = [
        'awb_number',
        'status',
        'order_id',
        'shipment_id',
        'article_type',
    ];

    /**
     * Calculate UPU S10 check digit for an 8-digit serial.
     */
    public static function calculateCheckDigit(string $eightDigits): int
    {
        $weights = [8, 6, 4, 2, 3, 5, 9, 7];
        $sum = 0;
        for ($i = 0; $i < 8; $i++) {
            $sum += ((int)$eightDigits[$i]) * $weights[$i];
        }
        $remainder = $sum % 11;
        if ($remainder === 0) {
            return 5;
        }
        if ($remainder === 1) {
            return 0;
        }
        return 11 - $remainder;
    }

    /**
     * Build standard 13-character S10 Barcode.
     */
    public static function formatBarcode(string $prefix, int $serialNumber, string $countryCode = 'IN'): string
    {
        $eightDigits = str_pad((string)$serialNumber, 8, '0', STR_PAD_LEFT);
        $checkDigit = self::calculateCheckDigit($eightDigits);
        return strtoupper($prefix) . $eightDigits . $checkDigit . strtoupper($countryCode);
    }
}
