<?php

namespace Tests\Feature;

use App\Models\Courier;
use App\Models\IndiaPostAwb;
use App\Models\RateCard;
use App\Models\RateCardZone;
use App\Models\Shipment;
use App\Models\ShipmentEvent;
use App\Services\Couriers\IndiaPostApiService;
use App\Services\Couriers\IndiaPostParcelService;
use App\Services\Couriers\IndiaPostSpeedPostService;
use App\Services\Couriers\XpressbeesService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IndiaPostIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed Couriers
        DB::table('couriers')->insert([
            [
                'id' => 2,
                'name' => 'XpressBees',
                'mode' => 'sandbox',
                'is_active' => 1,
                'markup_type' => 'percentage',
                'markup_value' => 0.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 9,
                'name' => 'India Post Parcel',
                'mode' => 'sandbox',
                'is_active' => 1,
                'markup_type' => 'percentage',
                'markup_value' => 0.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 10,
                'name' => 'India Post Speed Post',
                'mode' => 'sandbox',
                'is_active' => 1,
                'markup_type' => 'percentage',
                'markup_value' => 0.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $zoneNames = ['Zone 1 - Local', 'Zone 2 - Regional', 'Zone 3 - Metros', 'Zone 4 - Rest of India'];

        // Seed Xpressbees RateCard #2 (Min Rs 30, 2%)
        $xbCard = RateCard::create([
            'id' => 2,
            'courier_id' => 2,
            'version_name' => 'Xpressbees Base Rates',
            'effective_from' => '2026-09-01',
            'is_active' => true,
            'fsc_percent' => 10.00,
            'cod_min_charge' => 30.00,
            'cod_percent' => 2.00,
            'dto_multiplier' => 1.00,
            'qc_base_charge' => 0.00,
            'qc_included_params' => 0,
            'qc_additional_param_charge' => 0.00,
            'volumetric_divisor' => 5000.00,
            'gst_percent' => 18.00,
        ]);

        $xbZoneNames = ['Zone 1', 'Zone 2', 'Zone 3', 'Zone 4', 'Zone 5'];
        foreach ($xbZoneNames as $zn) {
            RateCardZone::create([
                'rate_card_id' => $xbCard->id,
                'zone_name' => $zn,
                'first_0_5_kg' => 25.00,
                'addl_0_5_kg' => 20.00,
                'first_2_kg' => 55.00,
                'addl_1_kg_after_2' => 15.00,
                'first_5_kg' => 95.00,
                'addl_1_kg_after_5' => 15.00,
            ]);
        }

        // Seed India Post Parcel RateCard #5 (Min Rs 30, 2%)
        $parcelCard = RateCard::create([
            'id' => 5,
            'courier_id' => 9,
            'version_name' => 'India Post Parcel Contractual',
            'effective_from' => '2026-09-01',
            'is_active' => true,
            'fsc_percent' => 10.00,
            'cod_min_charge' => 30.00,
            'cod_percent' => 2.00,
            'dto_multiplier' => 1.00,
            'qc_base_charge' => 0.00,
            'qc_included_params' => 0,
            'qc_additional_param_charge' => 0.00,
            'volumetric_divisor' => 5000.00,
            'gst_percent' => 18.00,
        ]);

        foreach ($zoneNames as $zn) {
            RateCardZone::create([
                'rate_card_id' => $parcelCard->id,
                'zone_name' => $zn,
                'first_0_5_kg' => 32.40,
                'addl_0_5_kg' => 4.80,
                'first_2_kg' => 54.00,
                'addl_1_kg_after_2' => 14.40,
                'first_5_kg' => 97.20,
                'addl_1_kg_after_5' => 18.00,
            ]);
        }

        // Seed India Post Speed Post RateCard #6 (Min Rs 30, 2%)
        $speedCard = RateCard::create([
            'id' => 6,
            'courier_id' => 10,
            'version_name' => 'India Post Speed Post Contractual',
            'effective_from' => '2026-09-01',
            'is_active' => true,
            'fsc_percent' => 10.00,
            'cod_min_charge' => 30.00,
            'cod_percent' => 2.00,
            'dto_multiplier' => 1.00,
            'qc_base_charge' => 0.00,
            'qc_included_params' => 0,
            'qc_additional_param_charge' => 0.00,
            'volumetric_divisor' => 5000.00,
            'gst_percent' => 18.00,
        ]);

        foreach ($zoneNames as $zn) {
            RateCardZone::create([
                'rate_card_id' => $speedCard->id,
                'zone_name' => $zn,
                'first_0_5_kg' => 33.60,
                'addl_0_5_kg' => 3.60,
                'first_2_kg' => 54.00,
                'addl_1_kg_after_2' => 14.40,
                'first_5_kg' => 97.20,
                'addl_1_kg_after_5' => 18.00,
            ]);
        }
    }

    /**
     * Test UPU S10 Check Digit Calculation.
     */
    public function test_upu_s10_check_digit_calculation()
    {
        // 21433001 -> sum: 2*8 + 1*6 + 4*4 + 3*2 + 3*3 + 0*5 + 0*9 + 1*7 = 60. 60 % 11 = 5. 11 - 5 = 6.
        $cd1 = IndiaPostAwb::calculateCheckDigit('21433001');
        $this->assertEquals(6, $cd1);
        $this->assertEquals('ET214330016IN', IndiaPostAwb::formatBarcode('ET', 21433001, 'IN'));

        // 21434000 -> sum: 2*8 + 1*6 + 4*4 + 3*2 + 4*3 = 56. 56 % 11 = 1. rem == 1 -> check digit 0.
        $cd2 = IndiaPostAwb::calculateCheckDigit('21434000');
        $this->assertEquals(0, $cd2);
        $this->assertEquals('ET214340000IN', IndiaPostAwb::formatBarcode('ET', 21434000, 'IN'));
    }

    /**
     * Test Xpressbees COD Surcharge: Min Rs 30 or 2% (whichever is higher).
     */
    public function test_xpressbees_cod_surcharge()
    {
        $xb = new XpressbeesService();

        // 1. COD with low invoice value (Rs. 500) -> 2% is Rs. 10 -> Min Rs. 30 applies
        $quoteLow = $xb->calculateRate('110001', '122015', 0.5, true, 500);
        $this->assertEquals('success', $quoteLow['status']);
        $this->assertEquals(30.00, $quoteLow['cod_charge']);

        // 2. COD with high invoice value (Rs. 2,000) -> 2% is Rs. 40 -> Rs. 40 applies
        $quoteHigh = $xb->calculateRate('110001', '122015', 0.5, true, 2000);
        $this->assertEquals('success', $quoteHigh['status']);
        $this->assertEquals(40.00, $quoteHigh['cod_charge']);

        // 3. Non-COD -> COD charge is 0
        $quotePrepaid = $xb->calculateRate('110001', '122015', 0.5, false, 2000);
        $this->assertEquals(0.00, $quotePrepaid['cod_charge']);
    }

    /**
     * Test India Post Parcel & Speed Post COD Surcharge: Min Rs 30 or 2% (whichever is higher).
     */
    public function test_indiapost_cod_surcharge()
    {
        $parcelService = new IndiaPostParcelService();
        $speedService  = new IndiaPostSpeedPostService();

        // Low COD (Rs 800) -> 2% is 16 -> Min Rs 30 applies
        $parcelLow = $parcelService->calculateRate('110001', '122015', 0.5, true, 800);
        $this->assertEquals('success', $parcelLow['status']);
        $this->assertEquals(30.00, $parcelLow['cod_charge']);

        // High COD (Rs 3,000) -> 2% is 60 -> Rs 60 applies
        $speedHigh = $speedService->calculateRate('110001', '122015', 0.5, true, 3000);
        $this->assertEquals('success', $speedHigh['status']);
        $this->assertEquals(60.00, $speedHigh['cod_charge']);
    }

    /**
     * Test India Post API Service AWB Allocation & Booking.
     */
    public function test_indiapost_awb_allocation_and_booking()
    {
        // Insert sample AWB in pool
        IndiaPostAwb::create([
            'awb_number'   => 'ET214330016IN',
            'status'       => 'available',
            'article_type' => 'SP_INLAND_PARCEL',
        ]);

        Http::fake([
            '*/access/login*' => Http::response(['token' => 'MOCK_TOKEN_123', 'expiresIn' => 900], 200),
            '*/pincode-search*' => Http::response([['office_id' => 11000101, 'delivery_office_flag' => true]], 200),
            '*/process-articles/*' => Http::response(['success' => true, 'batch_id' => 'BATCH-TEST-001'], 200),
        ]);

        $apiService = new IndiaPostApiService();
        $awbNumber = $apiService->getNextAvailableAwb('SP_INLAND_PARCEL');
        $this->assertEquals('ET214330016IN', $awbNumber);

        // Perform booking
        $booking = $apiService->createShipment([
            'order_id'         => 'TEST-ORD-101',
            'delivery_pincode' => '110001',
            'pickup_pincode'   => '122015',
            'weight_kg'        => 0.8,
            'is_cod'           => true,
            'invoice_value'    => 1500,
        ]);

        $this->assertEquals('success', $booking['status']);
        $this->assertEquals('ET214330016IN', $booking['awb']);

        // Verify status in DB is marked 'used'
        $dbAwb = IndiaPostAwb::where('awb_number', 'ET214330016IN')->first();
        $this->assertEquals('used', $dbAwb->status);
        $this->assertEquals('TEST-ORD-101', $dbAwb->order_id);
    }

    /**
     * Test India Post Webhook Ingestion.
     */
    public function test_indiapost_webhook_event_ingestion()
    {
        // Create test user and shipment with an India Post AWB
        $user = \App\Models\User::create([
            'name'     => 'Seller Test',
            'email'    => 'seller@test.com',
            'password' => bcrypt('password'),
        ]);

        $shipment = Shipment::create([
            'order_id'         => 'ORD-WEBHOOK-TEST',
            'user_id'          => $user->id,
            'courier_partner'  => 'India Post Speed Post',
            'courier_awb'      => 'ET214339999IN',
            'awb_number'       => 'ET214339999IN',
            'status'           => 'In Transit',
            'pickup_name'      => 'Sender',
            'pickup_phone'     => '9999999999',
            'pickup_address'   => 'Pickup Address',
            'pickup_city'      => 'Delhi',
            'pickup_state'     => 'Delhi',
            'pickup_pincode'   => '110001',
            'receiver_name'    => 'Recipient',
            'receiver_phone'   => '8888888888',
            'receiver_email'   => 'test@example.com',
            'delivery_address' => 'Delivery Address',
            'delivery_city'    => 'Mumbai',
            'delivery_state'   => 'Maharashtra',
            'delivery_pincode' => '400001',
            'weight_kg'        => 1.0,
            'invoice_value'    => 1000,
            'shipping_charge'  => 85,
            'total_amount'     => 85,
        ]);

        // Post delivery webhook
        $response = $this->postJson(route('api.courier.indiapost.webhook'), [
            'events' => [
                [
                    'article_number'    => 'ET214339999IN',
                    'event_code'        => 'EM_DE',
                    'event_description' => 'Item Delivered To Addressee',
                    'office_name'       => 'Mumbai GPO',
                    'event_date'        => now()->toDateTimeString(),
                ]
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success', 'processed' => 1]);

        $shipment->refresh();
        $this->assertEquals('Delivered', $shipment->status);

        $this->assertDatabaseHas('shipment_events', [
            'shipment_id' => $shipment->id,
            'status'      => 'Delivered',
            'location'    => 'Mumbai GPO',
        ]);
    }
}
