<?php

namespace Tests\Feature;

use App\Models\Kyc;
use App\Models\ReferralCommission;
use App\Models\ReferralFraudInvestigation;
use App\Models\ReferralPayoutMethod;
use App\Models\ReferralPayoutRequest;
use App\Models\ReferralRule;
use App\Models\SellerReferral;
use App\Models\Shipment;
use App\Models\User;
use App\Services\ReferralCommissionEngine;
use App\Services\ReferralFraudDetectionEngine;
use App\Services\ReferralPayoutService;
use App\Services\ReferralService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class ReferralFraudAndSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $sellerA;
    protected User $sellerB;
    protected ReferralRule $rule;

    protected function setUp(): void
    {
        parent::setUp();

        ReferralRule::query()->delete();
        $this->rule = ReferralRule::create([
            'name'                        => 'Security Test Rule',
            'reward_type'                 => 'fixed',
            'reward_value'                => 100.00,
            'trigger_event'               => 'first_delivered_shipment',
            'min_shipments_required'      => 1,
            'min_order_value'             => 50.00,
            'validity_days'               => 90,
            'max_commission_per_referral' => 5000.00,
            'min_withdrawal_amount'       => 500.00,
            'requires_kyc'                => false,
            'requires_phone_verified'     => false,
            'auto_approve_commission'     => true,
            'is_active'                   => true,
        ]);

        $this->admin = User::create([
            'name'     => 'Super Admin',
            'email'    => 'admin_sec_' . Str::random(5) . '@test.com',
            'phone'    => '9900000001',
            'password' => Hash::make('password123'),
            'role'     => 'admin',
            'status'   => 'active',
        ]);

        $this->sellerA = User::create([
            'name'     => 'Primary Referrer Seller',
            'email'    => 'seller_a_' . Str::random(5) . '@test.com',
            'phone'    => '9900000002',
            'password' => Hash::make('password123'),
            'role'     => 'seller',
            'status'   => 'active',
            'pan_number' => 'ABCDE1234F',
            'gstin'      => '27ABCDE1234F1Z5',
        ]);

        Kyc::create([
            'user_id'    => $this->sellerA->id,
            'status'     => 'approved',
            'pan_number' => 'ABCDE1234F',
            'gst_number' => '27ABCDE1234F1Z5',
        ]);

        $this->sellerB = User::create([
            'name'     => 'Second Seller',
            'email'    => 'seller_b_' . Str::random(5) . '@test.com',
            'phone'    => '9900000003',
            'password' => Hash::make('password123'),
            'role'     => 'seller',
            'status'   => 'active',
        ]);

        Kyc::create([
            'user_id' => $this->sellerB->id,
            'status'  => 'approved',
        ]);
    }

    public function test_self_referral_by_email_subaddressing_is_detected_and_scored_critical()
    {
        $code = $this->sellerA->getOrGenerateReferralCode();

        // Create referee with subaddressed email e.g. primary+test@test.com
        $parts = explode('@', $this->sellerA->email);
        $subEmail = $parts[0] . '+fake@' . $parts[1];

        $referee = User::create([
            'name'     => 'Fake Referee',
            'email'    => $subEmail,
            'phone'    => '9900000099',
            'password' => Hash::make('password123'),
            'role'     => 'seller',
            'status'   => 'active',
        ]);

        $referral = SellerReferral::create([
            'referrer_id'   => $this->sellerA->id,
            'referred_id'   => $referee->id,
            'referral_code' => $code,
            'status'        => 'registered',
        ]);

        $fraudEngine = app(ReferralFraudDetectionEngine::class);
        $result = $fraudEngine->evaluateReferral($referral);

        $this->assertGreaterThanOrEqual(85, $result['score']);
        $this->assertEquals(SellerReferral::RISK_CRITICAL, $result['level']);
        $this->assertTrue($referral->fresh()->is_held);
        $this->assertTrue($referral->fresh()->is_flagged);
    }

    public function test_matching_pan_or_gstin_triggers_critical_risk()
    {
        $code = $this->sellerA->getOrGenerateReferralCode();

        $referee = User::create([
            'name'       => 'Colluding Referee',
            'email'      => 'collude_' . Str::random(5) . '@test.com',
            'phone'      => '9900000088',
            'password'   => Hash::make('password123'),
            'role'       => 'seller',
            'status'     => 'active',
            'pan_number' => 'ABCDE1234F', // Matches Seller A's PAN
        ]);

        $referral = SellerReferral::create([
            'referrer_id'   => $this->sellerA->id,
            'referred_id'   => $referee->id,
            'referral_code' => $code,
            'status'        => 'registered',
        ]);

        $fraudEngine = app(ReferralFraudDetectionEngine::class);
        $result = $fraudEngine->evaluateReferral($referral);

        $this->assertGreaterThanOrEqual(85, $result['score']);
        $this->assertEquals(SellerReferral::RISK_CRITICAL, $result['level']);
        $this->assertTrue($referral->fresh()->is_held);
    }

    public function test_shared_payout_details_between_referrer_and_referee_triggers_critical_risk()
    {
        $code = $this->sellerA->getOrGenerateReferralCode();

        $payoutService = app(ReferralPayoutService::class);
        $payoutService->savePayoutMethod($this->sellerA, [
            'payout_type'         => 'bank',
            'account_holder_name' => 'Seller A',
            'bank_name'           => 'HDFC Bank',
            'account_number'      => '998877665544',
            'ifsc_code'           => 'HDFC0001234',
            'is_default'          => true,
        ]);

        // Referee registers the exact same bank account
        $referee = User::create([
            'name'     => 'Referee Shared Bank',
            'email'    => 'shared_bank_' . Str::random(5) . '@test.com',
            'phone'    => '9900000077',
            'password' => Hash::make('password123'),
            'role'     => 'seller',
            'status'   => 'active',
        ]);

        $payoutService->savePayoutMethod($referee, [
            'payout_type'         => 'bank',
            'account_holder_name' => 'Referee B',
            'bank_name'           => 'HDFC Bank',
            'account_number'      => '998877665544', // Colliding account number
            'ifsc_code'           => 'HDFC0001234',
            'is_default'          => true,
        ]);

        $referral = SellerReferral::create([
            'referrer_id'   => $this->sellerA->id,
            'referred_id'   => $referee->id,
            'referral_code' => $code,
            'status'        => 'registered',
        ]);

        $fraudEngine = app(ReferralFraudDetectionEngine::class);
        $result = $fraudEngine->evaluateReferral($referral);

        $this->assertGreaterThanOrEqual(85, $result['score']);
        $this->assertEquals(SellerReferral::RISK_CRITICAL, $result['level']);
    }

    public function test_shared_ip_alone_does_not_automatically_reject_or_mark_critical()
    {
        $code = $this->sellerA->getOrGenerateReferralCode();

        $sharedIp = '203.0.113.195';

        // Record Seller A with this IP
        SellerReferral::create([
            'referrer_id'   => $this->sellerA->id,
            'referred_id'   => $this->sellerB->id,
            'referral_code' => $code,
            'status'        => 'registered',
            'ip_address'    => $sharedIp,
        ]);

        // New legitimate referee on same shared Wi-Fi IP
        $referee = User::create([
            'name'     => 'Coworking Colleague',
            'email'    => 'legit_' . Str::random(5) . '@legitcompany.com',
            'phone'    => '9800000055',
            'password' => Hash::make('password123'),
            'role'     => 'seller',
            'status'   => 'active',
        ]);

        $referral = SellerReferral::create([
            'referrer_id'   => $this->sellerA->id,
            'referred_id'   => $referee->id,
            'referral_code' => $code,
            'status'        => 'registered',
            'ip_address'    => $sharedIp,
        ]);

        $fraudEngine = app(ReferralFraudDetectionEngine::class);
        $result = $fraudEngine->evaluateReferral($referral);

        // Shared IP alone yields only 20 points (Low risk), ensuring manual review rather than auto-reject
        $this->assertLessThan(60, $result['score']);
        $this->assertNotEquals(SellerReferral::RISK_CRITICAL, $result['level']);
        $this->assertFalse($referral->fresh()->is_held);
    }

    public function test_circular_shipment_delivery_holds_commission_under_review()
    {
        $code = $this->sellerA->getOrGenerateReferralCode();

        $referee = User::create([
            'name'     => 'Referee Shipper',
            'email'    => 'shipper_' . Str::random(5) . '@test.com',
            'phone'    => '9800000044',
            'password' => Hash::make('password123'),
            'role'     => 'seller',
            'status'   => 'active',
        ]);

        Kyc::create([
            'user_id' => $referee->id,
            'status'  => 'approved',
        ]);

        $referral = SellerReferral::create([
            'referrer_id'   => $this->sellerA->id,
            'referred_id'   => $referee->id,
            'referral_code' => $code,
            'status'        => 'registered',
        ]);

        // Referee books a shipment whose receiver phone matches the Referrer's phone!
        $shipment = Shipment::create([
            'user_id'          => $referee->id,
            'awb_number'       => 'AWB_CIRCULAR_' . Str::random(6),
            'receiver_name'    => 'Recipient Referrer Collusion',
            'receiver_phone'   => $this->sellerA->phone, // Circular match!
            'delivery_address' => 'Sample Street 123',
            'delivery_city'    => 'Mumbai',
            'delivery_pincode' => '400001',
            'weight_kg'        => 1.5,
            'invoice_value'    => 500.00,
            'total_amount'     => 100.00,
            'status'           => 'Delivered',
        ]);

        $commissionEngine = app(ReferralCommissionEngine::class);
        $result = $commissionEngine->evaluateDeliveryCommission($shipment);

        $this->assertTrue($result['success']);
        $commission = $result['commission']->fresh();

        $this->assertGreaterThanOrEqual(60, $commission->risk_score);
        $this->assertTrue($commission->is_held);
        $this->assertEquals(ReferralCommission::STATUS_UNDER_REVIEW, $commission->status);
    }

    public function test_admin_can_investigate_hold_release_and_reject_with_audit_trail()
    {
        $referral = SellerReferral::create([
            'referrer_id'   => $this->sellerA->id,
            'referred_id'   => $this->sellerB->id,
            'referral_code' => $this->sellerA->getOrGenerateReferralCode(),
            'status'        => 'registered',
            'risk_score'    => 65,
            'risk_level'    => SellerReferral::RISK_HIGH,
        ]);

        // 1. Admin Action: Hold
        $response = $this->actingAs($this->admin)->post(route('admin.referrals.investigate'), [
            'target_type' => 'referral',
            'target_id'   => $referral->id,
            'action'      => 'hold',
            'findings'    => 'Flagged for suspicious cluster verification',
        ]);
        $response->assertRedirect();
        $this->assertTrue($referral->fresh()->is_held);

        // 2. Admin Action: Release
        $response = $this->actingAs($this->admin)->post(route('admin.referrals.investigate'), [
            'target_type' => 'referral',
            'target_id'   => $referral->id,
            'action'      => 'release',
            'findings'    => 'Verified seller identity via video call and GST verification',
        ]);
        $response->assertRedirect();
        $this->assertFalse($referral->fresh()->is_held);

        // 3. Admin Action: Reject / Disqualify
        $response = $this->actingAs($this->admin)->post(route('admin.referrals.investigate'), [
            'target_type' => 'referral',
            'target_id'   => $referral->id,
            'action'      => 'reject',
            'findings'    => 'Confirmed fake registration with burner credentials',
        ]);
        $response->assertRedirect();
        $this->assertEquals('disqualified', $referral->fresh()->status);

        // Verify Investigation History Audit Trail exists
        $this->assertDatabaseHas('referral_fraud_investigations', [
            'seller_referral_id' => $referral->id,
            'admin_id'           => $this->admin->id,
            'action'             => 'reject',
            'findings'           => 'Confirmed fake registration with burner credentials',
        ]);
    }

    public function test_non_admin_cannot_access_or_execute_investigations()
    {
        $referral = SellerReferral::create([
            'referrer_id'   => $this->sellerA->id,
            'referred_id'   => $this->sellerB->id,
            'referral_code' => $this->sellerA->getOrGenerateReferralCode(),
            'status'        => 'registered',
        ]);

        // Seller attempts to call admin investigation endpoint
        $response = $this->actingAs($this->sellerA)->post(route('admin.referrals.investigate'), [
            'target_type' => 'referral',
            'target_id'   => $referral->id,
            'action'      => 'release',
            'findings'    => 'Illegitimate self-release attempt',
        ]);

        // Gated by role middleware / admin check
        $this->assertTrue(in_array($response->status(), [403, 302]));
        $this->assertDatabaseMissing('referral_fraud_investigations', [
            'findings' => 'Illegitimate self-release attempt',
        ]);
    }

    public function test_seller_cannot_withdraw_using_another_sellers_payout_method()
    {
        $payoutService = app(ReferralPayoutService::class);
        $methodB = $payoutService->savePayoutMethod($this->sellerB, [
            'payout_type'         => 'upi',
            'account_holder_name' => $this->sellerB->name,
            'upi_id'              => 'sellerb@okaxis',
            'is_default'          => true,
        ]);

        // Credit ₹1,000 to Seller A
        $referral = SellerReferral::create([
            'referrer_id'   => $this->sellerA->id,
            'referred_id'   => $this->sellerB->id,
            'referral_code' => $this->sellerA->getOrGenerateReferralCode(),
            'status'        => 'active',
        ]);

        ReferralCommission::create([
            'seller_referral_id' => $referral->id,
            'referrer_id'        => $this->sellerA->id,
            'referred_id'        => $this->sellerB->id,
            'commission_type'    => 'fixed',
            'rate_applied'       => 1000.00,
            'base_amount'        => 1000.00,
            'commission_amount'  => 1000.00,
            'status'             => ReferralCommission::STATUS_PAYABLE,
            'idempotency_key'    => 'comm_' . Str::random(10),
        ]);

        // Seller A tries to withdraw into Seller B's method ID
        $response = $this->actingAs($this->sellerA)->post(route('seller.referrals.withdraw'), [
            'amount'           => 600.00,
            'payout_method_id' => $methodB->id, // Belongs to Seller B, not Seller A!
        ]);

        // Must fail with validation error because Seller A has no valid default payout method of their own
        $response->assertSessionHasErrors();
        $this->assertEquals(0, ReferralPayoutRequest::where('user_id', $this->sellerA->id)->count());
    }
}
