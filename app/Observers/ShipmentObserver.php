<?php

namespace App\Observers;

use App\Models\Shipment;
use App\Services\ReferralCommissionEngine;
use Illuminate\Support\Facades\Log;

class ShipmentObserver
{
    /**
     * Handle the Shipment "updated" event.
     */
    public function updated(Shipment $shipment): void
    {
        if ($shipment->wasChanged('status')) {
            $newStatus = strtolower(trim((string)$shipment->status));
            if ($newStatus === 'delivered') {
                try {
                    app(ReferralCommissionEngine::class)->evaluateDeliveryCommission($shipment);
                } catch (\Throwable $e) {
                    Log::error("Referral commission evaluation failed for Shipment #{$shipment->id}: " . $e->getMessage());
                }
            }
        }
    }
}
