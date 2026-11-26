<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\IndiaPostAwb;

class IndiaPostAwbSeeder extends Seeder
{
    /**
     * Seed test barcode series: ET21433001XIN to ET21434000XIN (1000 barcodes).
     */
    public function run(): void
    {
        $startSerial = 21433001;
        $endSerial   = 21434000;
        $now = now();
        $batch = [];

        for ($serial = $startSerial; $serial <= $endSerial; $serial++) {
            $barcode = IndiaPostAwb::formatBarcode('ET', $serial, 'IN');
            $batch[] = [
                'awb_number'   => $barcode,
                'status'       => 'available',
                'order_id'     => null,
                'shipment_id'  => null,
                'article_type' => 'SP_INLAND_PARCEL',
                'created_at'   => $now,
                'updated_at'   => $now,
            ];

            if (count($batch) >= 200) {
                IndiaPostAwb::insertOrIgnore($batch);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            IndiaPostAwb::insertOrIgnore($batch);
        }
    }
}
