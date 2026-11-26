<?php

namespace App\Services;

use App\Models\Franchise;
use App\Models\Courier;
use Illuminate\Support\Facades\Log;

class ServiceabilityService
{
    /**
     * Determine the fulfillment and routing for a given delivery pincode.
     * Returns a structured array.
     */
    public function determineRouting(string $deliveryPincode, ?string $pickupPincode = null): array
    {
        // Check destination serviceability in OneStall network
        $destFranchiseId = $this->getFranchiseForPincode($deliveryPincode);
        $isDestDirectlyServiceable = \App\Models\ServiceablePincode::where('pincode', $deliveryPincode)->where('is_active', true)->exists();
        $isDestServiceable = ($destFranchiseId || $isDestDirectlyServiceable);

        // Check pickup serviceability in OneStall network (if pickup pincode provided)
        $pickupFranchiseId = null;
        $isPickupServiceable = false;
        if (!empty($pickupPincode)) {
            $pickupFranchiseId = $this->getFranchiseForPincode($pickupPincode);
            $isPickupDirectlyServiceable = \App\Models\ServiceablePincode::where('pincode', $pickupPincode)->where('is_active', true)->exists();
            $isPickupServiceable = ($pickupFranchiseId || $isPickupDirectlyServiceable);
        } else {
            // When pickup is not provided, default to matching destination status
            $isPickupServiceable = true;
        }

        // PRIORITY 1: OneStall Cargo own service/franchise
        // BOTH pickup and delivery must be serviceable in OneStall network for own fulfillment!
        if ($isDestServiceable && $isPickupServiceable) {
            $destFranchise = $destFranchiseId ? Franchise::find($destFranchiseId) : null;
            $districtFranchiseId = null;
            $stateFranchiseId = null;

            if ($destFranchise) {
                if ($destFranchise->isState()) {
                    $stateFranchiseId = $destFranchise->id;
                    $districtFranchiseId = $destFranchise->id;
                } else {
                    $districtFranchiseId = $destFranchise->id;
                    $stateFranchiseId = $destFranchise->parent_franchise_id;
                }
            }

            return [
                'serviceable' => true,
                'fulfillment_type' => 'onestall',
                'franchise_id' => $districtFranchiseId ?? $destFranchiseId,
                'district_franchise_id' => $districtFranchiseId ?? $destFranchiseId,
                'state_franchise_id' => $stateFranchiseId,
                'pickup_franchise_id' => $pickupFranchiseId,
                'provider_id' => null,
                'reason' => 'OneStall Cargo network service available for both pickup and delivery',
            ];
        }

        // PRIORITY 2: External Courier (Fallback)
        // Prioritize Xpressbees if active, or next active partner courier
        $provider = Courier::where('is_active', true)
            ->where('name', 'NOT LIKE', '%OneStall%')
            ->where('name', 'NOT LIKE', '%Onestall%')
            ->orderByRaw("CASE WHEN name LIKE '%Xpressbees%' THEN 0 ELSE 1 END")
            ->orderBy('id', 'asc')
            ->first();
        
        if ($provider) {
             return [
                'serviceable' => true,
                'fulfillment_type' => 'external',
                'franchise_id' => null,
                'provider_id' => $provider->id,
                'reason' => 'OneStall Cargo direct route unavailable; routed to partner courier (' . $provider->name . ')',
            ];
        }

        // PRIORITY 3: If destination is serviceable by OneStall and no external courier exists
        if ($isDestServiceable) {
            $destFranchise = $destFranchiseId ? Franchise::find($destFranchiseId) : null;
            $districtFranchiseId = null;
            $stateFranchiseId = null;

            if ($destFranchise) {
                if ($destFranchise->isState()) {
                    $stateFranchiseId = $destFranchise->id;
                    $districtFranchiseId = $destFranchise->id;
                } else {
                    $districtFranchiseId = $destFranchise->id;
                    $stateFranchiseId = $destFranchise->parent_franchise_id;
                }
            }

            return [
                'serviceable' => true,
                'fulfillment_type' => 'onestall',
                'franchise_id' => $districtFranchiseId ?? $destFranchiseId,
                'district_franchise_id' => $districtFranchiseId ?? $destFranchiseId,
                'state_franchise_id' => $stateFranchiseId,
                'pickup_franchise_id' => $pickupFranchiseId,
                'provider_id' => null,
                'reason' => 'OneStall Cargo network service available for delivery',
            ];
        }
        
        // Final fallback if no external couriers exist, try finding any active.
        $anyProvider = Courier::where('is_active', true)->first();
        if ($anyProvider) {
            return [
                'serviceable' => true,
                'fulfillment_type' => 'external',
                'franchise_id' => null,
                'provider_id' => $anyProvider->id,
                'reason' => 'Fallback provider',
            ];
        }

        return [
            'serviceable' => false,
            'fulfillment_type' => null,
            'franchise_id' => null,
            'provider_id' => null,
            'reason' => 'No active service available for this pincode',
        ];
    }

    public function getFranchiseForPincode(string $pincode): ?int
    {
        // DB-driven check first
        $serviceable = \App\Models\ServiceablePincode::where('pincode', $pincode)
            ->where('is_active', true)
            ->whereNotNull('franchise_id')
            ->first();

        if ($serviceable) {
            $franchise = Franchise::find($serviceable->franchise_id);
            if ($franchise && $franchise->status === 'approved') {
                return $franchise->id;
            }
        }

        // Fallback to legacy string check
        $franchises = Franchise::where('status', 'approved')->get();

        foreach ($franchises as $franchise) {
            $pincodes = $franchise->serviceable_pincodes ?? [];
            if (is_array($pincodes) && in_array($pincode, $pincodes)) {
                return $franchise->id;
            }
            if (is_string($pincodes) && str_contains($pincodes, $pincode)) {
                $pincodesArray = array_map('trim', explode(',', $pincodes));
                if (in_array($pincode, $pincodesArray)) {
                    return $franchise->id;
                }
            }
        }

        return null;
    }
}
