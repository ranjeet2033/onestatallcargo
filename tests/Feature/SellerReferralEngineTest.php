<?php

namespace Tests\Feature;

use App\Models\Kyc;
use App\Models\ReferralClick;
use App\Models\ReferralCommission;
use App\Models\ReferralRule;
use App\Models\SellerReferral;
use App\Models\Shipment;
use App\Models\User;
use App\Services\ReferralCommissionEngine;
use App\Services\ReferralService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class SellerReferralEngineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure active referral rule exists
        ReferralRule::query()->delete();
        ReferralRule::create([
            'name'                        => 'Test 10% Referral Rule',
            'reward_type'                 => 'percentage',
            'reward_value'                => 10.00,
            'trigger_event'               => 'first_delivered_shipment',
            'min_shipments_required'      => 1,
            'min_order_value'             => 50.00,
            'validity_days'               => 90,
            'max_commission_per_referral' => 1000.00,
            'requires_kyc'                => true,
            'requires_phone_verified'     => false,
            'is_active'                   => true,
        ]);
    }

    public function test_it_generates_unique_referral_code_and_url_for_seller()
    {
        $seller = User::create([
            'name'     => 'Referrer Seller',
            'email'    => 'referrer_' . Str::random(5) . '@test.com',
            'password' => Hash::make('password123'),
            'role'     => 'seller',
            'status'   => 'active',
        ]);

        $code = $seller->getOrGenerateReferralCode();
        $this->assertNotEmpty($code);
        $this->assertStringStartsWith('OSC', $code);
        $this->assertEquals($code, $seller->fresh()->referral_code);

        $url = $seller->referral_url;
        $this->assertStringContainsString('/register?ref=' . $code, $url);
    }

    public function test_it_tracks_referral_link_clicks()
    {
        $seller = User::create([
            'name'     => 'Click Tracker Referrer',
            'email'    => 'clickref_' . Str::random(5) . '@test.com',
            'password' => Hash::make('password123'),
            'role'     => 'seller',
            'status'   => 'active',
        ]);

        $code = $seller->getOrGenerateReferralCode();

        $referralService = app(ReferralService::class);
        $click = $referralService->recordClick($code);

        $this->assertNotNull($click);
        $this->assertEquals($code, $click->referral_code);
        $this->assertEquals($seller->id, $click->referrer_id);
        $this->assertDatabaseHas('referral_clicks', [
            'referral_code' => $code,
            'referrer_id'   => $seller->id,
        ]);
    }

    public function test_it_permanently_attributes_new_seller_registration()
    {
        $referrer = User::create([
            'name'     => 'Primary Referrer',
            'email'    => 'primary_' . Str::random(5) . '@test.com',
            'phone'    => '9100000001',
            'password' => Hash::make('password123'),
            'role'     => 'seller',
            'status'   => 'active',
        ]);
        $code = $referrer->getOrGenerateReferralCode();

        $response = $this->post('/register', [
            'name'                  => 'New Referred Seller',
            'email'                 => 'newreferred_' . Str::random(5) . '@test.com',
            'phone'                 => '9200000002',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'referral_code'         => $code,
        ]);

        $response->assertRedirect(route('seller.dashboard'));

        $newSeller = User::where('phone', '9200000002')->first();
        $this->assertNotNull($newSeller);
        $this->assertEquals($referrer->id, $newSeller->referred_by);

        $this->assertDatabaseHas('seller_referrals', [
            'referrer_id'   => $referrer->id,
            'referred_id'   => $newSeller->id,
            'referral_code' => $code,
            'status'        => 'registered',
        ]);
    }

    public function test_it_prevents_self_referral()
    {
        $seller = User::create([
            'name'     => 'Self Referrer',
            'email'    => 'self_' . Str::random(5) . '@test.com',
            'phone'    => '9300000003',
            'password' => Hash::make('password123'),
            'role'     => 'seller',
            'status'   => 'active',
        ]);
        $code = $seller->getOrGenerateReferralCode();

        $referralService = app(ReferralService::class);
        $result = $referralService->attributeRegistration($seller, $code);

        $this->assertNull($result);
        $this->assertNull($seller->fresh()->referred_by);
        $this->assertDatabaseMissing('seller_referrals', [
            'referred_id' => $seller->id,
        ]);
    }

    public function test_it_prevents_duplicate_referrals()
    {
        $referrer1 = User::create([
            'name'     => 'Referrer One',
            'email'    => 'ref1_' . Str::random(5) . '@test.com',
            'password' => Hash::make('password123'),
            'role'     => 'seller',
        ]);
        $code1 = $referrer1->getOrGenerateReferralCode();

        $referrer2 = User::create([
            'name'     => 'Referrer Two',
            'email'    => 'ref2_' . Str::random(5) . '@test.com',
            'password' => Hash::make('password123'),
            'role'     => 'seller',
        ]);
        $code2 = $referrer2->getOrGenerateReferralCode();

        $buyer = User::create([
            'name'     => 'Target Seller',
            'email'    => 'target_' . Str::random(5) . '@test.com',
            'password' => Hash::make('password123'),
            'role'     => 'seller',
        ]);

        $referralService = app(ReferralService::class);
        $firstAttr = $referralService->attributeRegistration($buyer, $code1);
        $this->assertNotNull($firstAttr);
        $this->assertEquals($referrer1->id, $buyer->fresh()->referred_by);

        // Second attribution attempt must be blocked
        $secondAttr = $referralService->attributeRegistration($buyer, $code2);
        $this->assertNull($secondAttr);
        $this->assertEquals($referrer1->id, $buyer->fresh()->referred_by);
        $this->assertEquals(1, SellerReferral::where('referred_id', $buyer->id)->count());
    }

    public function test_standard_registration_without_referral_code_works_unaffected()
    {
        $uniqueEmail = 'clean_' . Str::random(5) . '@test.com';

        $response = $this->post('/register', [
            'name'                  => 'Clean Seller',
            'email'                 => $uniqueEmail,
            'phone'                 => '9400000004',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('seller.dashboard'));

        $user = User::where('email', $uniqueEmail)->first();
        $this->assertNotNull($user);
        $this->assertNull($user->referred_by);
        $this->assertDatabaseMissing('seller_referrals', [
            'referred_id' => $user->id,
        ]);
    }

    public function test_it_evaluates_referral_commission_on_genuine_delivered_shipment()
    {
        $referrer = User::create([
            'name'     => 'Beneficiary Seller',
            'email'    => 'beneficiary_' . Str::random(5) . '@test.com',
            'password' => Hash::make('password123'),
            'role'     => 'seller',
            'status'   => 'active',
        ]);
        $code = $referrer->getOrGenerateReferralCode();

        $referred = User::create([
            'name'     => 'Active Referred Shipper',
            'email'    => 'active_shipper_' . Str::random(5) . '@test.com',
            'password' => Hash::make('password123'),
            'role'     => 'seller',
            'status'   => 'active',
        ]);

        // Attribute referral
        app(ReferralService::class)->attributeRegistration($referred, $code);

        // Give approved KYC to referred user
        Kyc::create([
            'user_id'         => $referred->id,
            'business_type'   => 'proprietorship',
            'document_type'   => 'gst',
            'document_number' => '07TEST123456789',
            'status'          => 'approved',
            'approved_at'     => now(),
        ]);

        // Create a delivered shipment
        $shipment = Shipment::create([
            'user_id'          => $referred->id,
            'awb_number'       => 'OSCREF' . rand(10000, 99999),
            'receiver_name'    => 'Test Customer',
            'receiver_phone'   => '9876543210',
            'delivery_address' => '123 Test Road',
            'delivery_city'    => 'Delhi',
            'delivery_pincode' => '110001',
            'weight_kg'        => 1.5,
            'invoice_value'    => 500.00,
            'shipping_charge'  => 250.00,
            'total_amount'     => 250.00,
            'status'           => 'Delivered',
        ]);

        $engine = app(ReferralCommissionEngine::class);
        $result = $engine->evaluateDeliveryCommission($shipment);

        $this->assertTrue($result['success']);
        $this->assertNotNull($result['commission']);

        // 10% of 250 = 25.00
        $this->assertEquals(25.00, (float) $result['commission']->commission_amount);
        $this->assertEquals(ReferralCommission::STATUS_APPROVED, $result['commission']->status);
        $this->assertEquals($referrer->id, $result['commission']->referrer_id);
        $this->assertEquals($referred->id, $result['commission']->referred_id);

        // IDEMPOTENCY TEST: Running it a second time on the same shipment MUST NOT create a duplicate
        $secondResult = $engine->evaluateDeliveryCommission($shipment);
        $this->assertTrue($secondResult['success']);
        $this->assertEquals(1, ReferralCommission::where('shipment_id', $shipment->id)->count());
    }

    public function test_cancelled_or_rto_shipments_do_not_qualify()
    {
        $referrer = User::create([
            'name'     => 'Referrer Cancel Check',
            'email'    => 'refcancel_' . Str::random(5) . '@test.com',
            'password' => Hash::make('password123'),
            'role'     => 'seller',
            'status'   => 'active',
        ]);
        $code = $referrer->getOrGenerateReferralCode();

        $referred = User::create([
            'name'     => 'Shipper Cancel Check',
            'email'    => 'shipcancel_' . Str::random(5) . '@test.com',
            'password' => Hash::make('password123'),
            'role'     => 'seller',
            'status'   => 'active',
        ]);

        app(ReferralService::class)->attributeRegistration($referred, $code);

        // Cancelled shipment
        $cancelledShipment = Shipment::create([
            'user_id'          => $referred->id,
            'awb_number'       => 'OSCCAN' . rand(10000, 99999),
            'receiver_name'    => 'Test Customer',
            'receiver_phone'   => '9876543210',
            'delivery_address' => '123 Test Road',
            'delivery_city'    => 'Delhi',
            'delivery_pincode' => '110001',
            'weight_kg'        => 1.0,
            'invoice_value'    => 500.00,
            'shipping_charge'  => 100.00,
            'total_amount'     => 100.00,
            'status'           => 'cancelled',
        ]);

        $engine = app(ReferralCommissionEngine::class);
        $res = $engine->evaluateDeliveryCommission($cancelledShipment);
        $this->assertFalse($res['success']);
        $this->assertEquals(0, ReferralCommission::where('shipment_id', $cancelledShipment->id)->count());

        // RTO shipment
        $rtoShipment = Shipment::create([
            'user_id'          => $referred->id,
            'awb_number'       => 'OSCRTO' . rand(10000, 99999),
            'receiver_name'    => 'Test Customer',
            'receiver_phone'   => '9876543210',
            'delivery_address' => '123 Test Road',
            'delivery_city'    => 'Delhi',
            'delivery_pincode' => '110001',
            'weight_kg'        => 1.0,
            'invoice_value'    => 500.00,
            'shipping_charge'  => 100.00,
            'total_amount'     => 100.00,
            'status'           => 'RTO Delivered',
        ]);

        $resRto = $engine->evaluateDeliveryCommission($rtoShipment);
        $this->assertFalse($resRto['success']);
        $this->assertEquals(0, ReferralCommission::where('shipment_id', $rtoShipment->id)->count());
    }

    public function test_unapproved_kyc_places_commission_under_review()
    {
        $referrer = User::create([
            'name'     => 'Referrer KYC Test',
            'email'    => 'refkyc_' . Str::random(5) . '@test.com',
            'password' => Hash::make('password123'),
            'role'     => 'seller',
            'status'   => 'active',
        ]);
        $code = $referrer->getOrGenerateReferralCode();

        $referred = User::create([
            'name'     => 'Shipper Without KYC',
            'email'    => 'nokyc_' . Str::random(5) . '@test.com',
            'password' => Hash::make('password123'),
            'role'     => 'seller',
            'status'   => 'active',
        ]);

        app(ReferralService::class)->attributeRegistration($referred, $code);

        // Shipment delivered before KYC is approved
        $shipment = Shipment::create([
            'user_id'          => $referred->id,
            'awb_number'       => 'OSCNK' . rand(10000, 99999),
            'receiver_name'    => 'Test Customer',
            'receiver_phone'   => '9876543210',
            'delivery_address' => '123 Test Road',
            'delivery_city'    => 'Delhi',
            'delivery_pincode' => '110001',
            'weight_kg'        => 1.0,
            'invoice_value'    => 500.00,
            'shipping_charge'  => 200.00,
            'total_amount'     => 200.00,
            'status'           => 'Delivered',
        ]);

        $engine = app(ReferralCommissionEngine::class);
        $result = $engine->evaluateDeliveryCommission($shipment);

        $this->assertTrue($result['success']);
        // Must be held under review because rule requires KYC
        $this->assertEquals(ReferralCommission::STATUS_UNDER_REVIEW, $result['commission']->status);
        $this->assertStringContainsString('KYC', $result['commission']->rejection_reason);

        // Now test admin workflow: approving the commission
        $admin = User::create([
            'name'     => 'Super Admin Test',
            'email'    => 'admin_' . Str::random(5) . '@test.com',
            'password' => Hash::make('password123'),
            'role'     => 'admin',
        ]);

        $approvedComm = $engine->approveCommission($result['commission'], $admin->id);
        $this->assertEquals(ReferralCommission::STATUS_APPROVED, $approvedComm->status);
        $this->assertNotNull($approvedComm->approved_at);

        // Test marking payable
        $payableComm = $engine->markPayable($approvedComm);
        $this->assertEquals(ReferralCommission::STATUS_PAYABLE, $payableComm->status);

        // Test marking paid
        $paidComm = $engine->markPaid($payableComm, 'UTR987654321', $admin->id, 'Paid via IMPS');
        $this->assertEquals(ReferralCommission::STATUS_PAID, $paidComm->status);
        $this->assertEquals('UTR987654321', $paidComm->payment_reference);
    }

    public function test_it_awards_200_to_referrer_on_first_wallet_recharge()
    {
        // Setup recharge referral rule: ₹200 for referrer only
        ReferralRule::query()->delete();
        ReferralRule::create([
            'name'                 => '₹200 Referrer Recharge Rule',
            'reward_type'          => 'fixed',
            'reward_value'         => 200.00,
            'referee_reward_value' => 0.00,
            'trigger_event'        => 'first_wallet_recharge',
            'min_order_value'      => 0.00,
            'is_active'            => true,
        ]);

        $referrer = User::create([
            'name'           => 'Referrer Seller',
            'email'          => 'ref_' . Str::random(5) . '@test.com',
            'password'       => Hash::make('password123'),
            'role'           => 'seller',
            'status'         => 'active',
            'wallet_balance' => 100.00,
        ]);

        $referee = User::create([
            'name'           => 'New Joined Seller',
            'email'          => 'new_' . Str::random(5) . '@test.com',
            'password'       => Hash::make('password123'),
            'role'           => 'seller',
            'status'         => 'active',
            'wallet_balance' => 0.00,
            'referred_by'    => $referrer->id,
        ]);

        SellerReferral::create([
            'referrer_id'   => $referrer->id,
            'referred_id'   => $referee->id,
            'referral_code' => $referrer->getOrGenerateReferralCode(),
            'status'        => 'registered',
        ]);

        // Simulate 1st successful recharge of ₹500
        $tx = \App\Models\WalletTransaction::create([
            'user_id'       => $referee->id,
            'type'          => 'credit',
            'amount'        => 500.00,
            'balance_after' => 500.00,
            'reference_id'  => 'TX_RECHARGE_TEST_1',
            'description'   => 'Wallet Recharge',
            'status'        => 'success',
        ]);
        $referee->update(['wallet_balance' => 500.00]);

        // Evaluate referral bonus
        $engine = app(ReferralCommissionEngine::class);
        $result = $engine->evaluateRechargeReferralBonus($referee, $tx);

        $this->assertTrue($result['success']);
        $this->assertEquals(200.00, $result['referrer_bonus']);
        $this->assertEquals(0.00, $result['referee_bonus']);

        // Check Referrer wallet: 100 + 200 = 300
        $this->assertEquals(300.00, (float) $referrer->fresh()->wallet_balance);

        // Check Referee wallet: only their own recharge of 500 (no extra bonus)
        $this->assertEquals(500.00, (float) $referee->fresh()->wallet_balance);

        // Check idempotency: second recharge should NOT double-credit
        $secondTx = \App\Models\WalletTransaction::create([
            'user_id'       => $referee->id,
            'type'          => 'credit',
            'amount'        => 1000.00,
            'balance_after' => 1500.00,
            'reference_id'  => 'TX_RECHARGE_TEST_2',
            'description'   => 'Wallet Recharge 2',
            'status'        => 'success',
        ]);
        $secondResult = $engine->evaluateRechargeReferralBonus($referee, $secondTx);
        $this->assertTrue($secondResult['success']);
        $this->assertStringContainsString('already awarded', $secondResult['message']);

        // Balances remain unchanged (no extra 200)
        $this->assertEquals(300.00, (float) $referrer->fresh()->wallet_balance);
        $this->assertEquals(500.00, (float) $referee->fresh()->wallet_balance);
    }
}
