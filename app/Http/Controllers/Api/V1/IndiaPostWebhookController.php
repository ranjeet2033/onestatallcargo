<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Models\ShipmentEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class IndiaPostWebhookController extends Controller
{
    /**
     * Ingest tracking webhook events from India Post.
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();
        Log::info('IndiaPost Webhook Received:', $payload);

        // Can receive a single event object or a list of articles/events
        $events = isset($payload['events']) ? $payload['events'] : (isset($payload[0]) ? $payload : [$payload]);

        $processed = 0;

        foreach ($events as $event) {
            $awb = $event['article_number'] ?? $event['barcode_no'] ?? $event['article_no'] ?? $event['awb'] ?? null;
            if (!$awb) {
                continue;
            }

            $shipment = Shipment::where('courier_awb', $awb)
                ->orWhere('awb_number', $awb)
                ->first();

            if (!$shipment) {
                continue;
            }

            $eventCode = strtoupper($event['event_code'] ?? $event['event_type'] ?? 'UPDATE');
            $eventDesc = $event['event_description'] ?? $event['description'] ?? 'Status update from India Post';
            $location  = $event['office_name'] ?? $event['location'] ?? 'India Post Facility';
            $timestamp = $event['event_date'] ?? $event['timestamp'] ?? now();

            // Map India Post events to system shipment status
            $newStatus = $this->mapStatus($eventCode, $eventDesc);

            if ($newStatus && $shipment->status !== 'Delivered') {
                $shipment->status = $newStatus;
                $shipment->save();
            }

            // Create event trail
            ShipmentEvent::create([
                'shipment_id' => $shipment->id,
                'status'      => $newStatus ?: $shipment->status,
                'location'    => $location,
                'remarks'     => "{$eventCode}: {$eventDesc} (India Post)"
            ]);

            $processed++;
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Webhook processed successfully',
            'processed' => $processed,
        ], 200);
    }

    /**
     * Map India Post event code to internal shipment status.
     */
    protected function mapStatus(string $code, string $description): ?string
    {
        $desc = strtolower($description);

        if (str_contains($code, 'DEL') || str_contains($desc, 'item delivered') || str_contains($desc, 'delivered')) {
            return 'Delivered';
        }

        if (str_contains($code, 'OD') || str_contains($desc, 'out for delivery')) {
            return 'Out for Delivery';
        }

        if (str_contains($code, 'RTO') || str_contains($desc, 'returned') || str_contains($desc, 'return to sender')) {
            return 'RTO Initiated';
        }

        if (str_contains($code, 'DISP') || str_contains($code, 'BAG') || str_contains($desc, 'dispatched') || str_contains($desc, 'in transit')) {
            return 'In Transit';
        }

        if (str_contains($code, 'BK') || str_contains($desc, 'booked') || str_contains($desc, 'received')) {
            return 'In Transit';
        }

        return null;
    }
}
