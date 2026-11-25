<?php

namespace Tests\Feature;

use App\Models\Kyc;
use App\Models\ReferralCommission;
use App\Models\ReferralPayoutMethod;
use App\Models\ReferralPayoutRequest;
use App\Models\ReferralRule;
use App\Models\SellerReferral;
use App\Models\User;
use App\Services\ReferralPayoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class ReferralPayoutTest extends TestCase
{
    use RefreshDatabase;

    protected User $seller;
    protected User $referredUser;
    protected SellerReferral $sellerReferral;
    protected User $admin;
    protected ReferralRule $rule;

    protected function setUp(): void
    {
        parent::setUp();

        ReferralRule::query()->delete();
        $this->rule = ReferralRule::create([
            'name'                        => 'Standard Rule',
            'reward_type'                 => 'fixed',
            'reward_value'                => 150.00,
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

        $this->seller = User::create([
            'name'     => 'Test Seller Referrer',
            'email'    => 'seller_payout_' . Str::random(5) . '@test.com',
            'phone'    => '9888877771',
            'password' => Hash::make('password123'),
            'role'     => 'seller',
            'status'   => 'active',
        ]);

        // KYC approval so seller bypasses CheckKyc middleware
        Kyc::create([
            'user_id' => $this->seller->id,
            'status'  => 'approved',
        ]);

        $this->referredUser = User::create([
            'name'     => 'Referred Customer',
            'email'    => 'referred_' . Str::random(5) . '@test.com',
            'phone'    => '9888877773',
            'password' => Hash::make('password123'),
            'role'     => 'seller',
            'status'   => 'active',
        ]);

        $this->sellerReferral = SellerReferral::create([
            'referrer_id'   => $this->seller->id,
            'referred_id'   => $this->referredUser->id,
            'referral_code' => $this->seller->getOrGenerateReferralCode(),
            'status'        => 'active',
            'registered_at' => now(),
        ]);

        $this->admin = User::create([
            'name'     => 'Finance Admin',
            'email'    => 'admin_payout_' . Str::random(5) . '@test.com',
            'phone'    => '9888877772',
            'password' => Hash::make('password123'),
            'role'     => 'admin',
            'status'   => 'active',
        ]);
    }

    public function test_seller_can_add_bank_payout_method_with_masked_account()
    {
        $response = $this->actingAs($this->seller)->post(route('seller.referrals.payout-methods.store'), [
            'payout_type'         => 'bank',
            'account_holder_name' => 'John Doe',
            'bank_name'           => 'HDFC Bank',
            'account_number'      => '123456789012',
            'ifsc_code'           => 'HDFC0001234',
            'is_default'          => '1',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('referral_payout_methods', [
            'user_id'        => $this->seller->id,
            'payout_type'    => 'bank',
            'account_number' => '123456789012',
            'ifsc_code'      => 'HDFC0001234',
        ]);

        $method = ReferralPayoutMethod::where('user_id', $this->seller->id)->first();
        $this->assertEquals('•••• •••• 9012', $method->masked_account);
    }

    public function test_seller_can_add_upi_payout_method()
    {
        $response = $this->actingAs($this->seller)->post(route('seller.referrals.payout-methods.store'), [
            'payout_type'         => 'upi',
            'account_holder_name' => 'John Doe',
            'upi_id'              => 'john.doe@okaxis',
            'is_default'          => '1',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('referral_payout_methods', [
            'user_id'     => $this->seller->id,
            'payout_type' => 'upi',
            'upi_id'      => 'john.doe@okaxis',
        ]);
    }

    public function test_invalid_ifsc_or_short_account_fails_validation()
    {
        $response = $this->actingAs($this->seller)->post(route('seller.referrals.payout-methods.store'), [
            'payout_type'         => 'bank',
            'account_holder_name' => 'John Doe',
            'bank_name'           => 'HDFC Bank',
            'account_number'      => '123', // Too short (min 9)
            'ifsc_code'           => 'INVALIDIFSC',
        ]);

        $response->assertSessionHasErrors();
    }

    public function test_withdrawal_fails_when_below_minimum_amount()
    {
        $payoutService = app(ReferralPayoutService::class);
        $payoutService->savePayoutMethod($this->seller, [
            'payout_type'         => 'upi',
            'account_holder_name' => $this->seller->name,
            'upi_id'              => 'seller@okicici',
            'is_default'          => true,
        ]);

        // Seller has ₹1,000 commission
        ReferralCommission::create([
            'seller_referral_id' => $this->sellerReferral->id,
            'referrer_id'        => $this->seller->id,
            'referred_id'        => $this->referredUser->id,
            'commission_type'    => 'fixed',
            'rate_applied'       => 1000.00,
            'base_amount'        => 1000.00,
            'commission_amount'  => 1000.00,
            'status'             => ReferralCommission::STATUS_PAYABLE,
            'idempotency_key'    => 'comm_' . Str::random(10),
        ]);

        // Minimum is ₹500, seller requests ₹200
        $response = $this->actingAs($this->seller)->post(route('seller.referrals.withdraw'), [
            'amount' => 200.00,
        ]);

        $response->assertSessionHasErrors(['amount']);
        $this->assertEquals(0, ReferralPayoutRequest::count());
    }

    public function test_withdrawal_fails_when_amount_exceeds_available_balance()
    {
        $payoutService = app(ReferralPayoutService::class);
        $payoutService->savePayoutMethod($this->seller, [
            'payout_type'         => 'upi',
            'account_holder_name' => $this->seller->name,
            'upi_id'              => 'seller@okicici',
            'is_default'          => true,
        ]);

        // Seller has ₹600 commission
        ReferralCommission::create([
            'seller_referral_id' => $this->sellerReferral->id,
            'referrer_id'        => $this->seller->id,
            'referred_id'        => $this->referredUser->id,
            'commission_type'    => 'fixed',
            'rate_applied'       => 600.00,
            'base_amount'        => 600.00,
            'commission_amount'  => 600.00,
            'status'             => ReferralCommission::STATUS_APPROVED,
            'idempotency_key'    => 'comm_' . Str::random(10),
        ]);

        // Seller attempts to withdraw ₹1,200
        $response = $this->actingAs($this->seller)->post(route('seller.referrals.withdraw'), [
            'amount' => 1200.00,
        ]);

        $response->assertSessionHasErrors(['amount']);
        $this->assertEquals(0, ReferralPayoutRequest::count());
    }

    public function test_successful_withdrawal_deducts_from_available_balance()
    {
        $payoutService = app(ReferralPayoutService::class);
        $method = $payoutService->savePayoutMethod($this->seller, [
            'payout_type'         => 'bank',
            'account_holder_name' => $this->seller->name,
            'bank_name'           => 'State Bank of India',
            'account_number'      => '987654321098',
            'ifsc_code'           => 'SBIN0001234',
            'is_default'          => true,
        ]);

        // Credit ₹2,000 approved commission
        ReferralCommission::create([
            'seller_referral_id' => $this->sellerReferral->id,
            'referrer_id'        => $this->seller->id,
            'referred_id'        => $this->referredUser->id,
            'commission_type'    => 'fixed',
            'rate_applied'       => 2000.00,
            'base_amount'        => 2000.00,
            'commission_amount'  => 2000.00,
            'status'             => ReferralCommission::STATUS_APPROVED,
            'idempotency_key'    => 'comm_' . Str::random(10),
        ]);

        $this->assertEquals(2000.00, $this->seller->fresh()->available_referral_balance);

        // Request ₹800 withdrawal
        $response = $this->actingAs($this->seller)->post(route('seller.referrals.withdraw'), [
            'amount'           => 800.00,
            'payout_method_id' => $method->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('referral_payout_requests', [
            'user_id' => $this->seller->id,
            'amount'  => 800.00,
            'status'  => ReferralPayoutRequest::STATUS_PENDING,
        ]);

        // Available balance must now be ₹1,200 (₹2,000 - ₹800 pending)
        $this->assertEquals(1200.00, $this->seller->fresh()->available_referral_balance);
    }

    public function test_admin_can_approve_and_mark_payout_paid_with_utr()
    {
        $payoutService = app(ReferralPayoutService::class);
        $method = $payoutService->savePayoutMethod($this->seller, [
            'payout_type'         => 'upi',
            'account_holder_name' => $this->seller->name,
            'upi_id'              => 'seller@okhdfcbank',
            'is_default'          => true,
        ]);

        // Credit ₹1,500
        ReferralCommission::create([
            'seller_referral_id' => $this->sellerReferral->id,
            'referrer_id'        => $this->seller->id,
            'referred_id'        => $this->referredUser->id,
            'commission_type'    => 'fixed',
            'rate_applied'       => 1500.00,
            'base_amount'        => 1500.00,
            'commission_amount'  => 1500.00,
            'status'             => ReferralCommission::STATUS_PAYABLE,
            'idempotency_key'    => 'comm_' . Str::random(10),
        ]);

        $payoutRequest = $payoutService->requestWithdrawal($this->seller, 750.00, $method->id);
        $this->assertEquals(ReferralPayoutRequest::STATUS_PENDING, $payoutRequest->status);

        // Admin marks processing
        $response = $this->actingAs($this->admin)->post(route('admin.referrals.payouts.process', $payoutRequest->id), [
            'action' => 'mark_processing',
        ]);
        $response->assertRedirect();
        $this->assertEquals(ReferralPayoutRequest::STATUS_PROCESSING, $payoutRequest->fresh()->status);

        // Admin marks paid with UTR
        $response = $this->actingAs($this->admin)->post(route('admin.referrals.payouts.process', $payoutRequest->id), [
            'action'            => 'mark_paid',
            'payment_reference' => 'UTR998877665544',
            'admin_notes'       => 'Disbursed via IMPS batch',
        ]);
        $response->assertRedirect();

        $fresh = $payoutRequest->fresh();
        $this->assertEquals(ReferralPayoutRequest::STATUS_PAID, $fresh->status);
        $this->assertEquals('UTR998877665544', $fresh->payment_reference);
        $this->assertNotNull($fresh->paid_at);
        $this->assertEquals($this->admin->id, $fresh->processed_by);

        // Audit log exists
        $this->assertDatabaseHas('referral_audit_logs', [
            'action'  => 'payout_mark_paid',
            'user_id' => $this->admin->id,
        ]);
    }

    public function test_admin_rejection_restores_seller_available_balance()
    {
        $payoutService = app(ReferralPayoutService::class);
        $method = $payoutService->savePayoutMethod($this->seller, [
            'payout_type'         => 'upi',
            'account_holder_name' => $this->seller->name,
            'upi_id'              => 'seller@okhdfcbank',
            'is_default'          => true,
        ]);

        // Credit ₹1,000
        ReferralCommission::create([
            'seller_referral_id' => $this->sellerReferral->id,
            'referrer_id'        => $this->seller->id,
            'referred_id'        => $this->referredUser->id,
            'commission_type'    => 'fixed',
            'rate_applied'       => 1000.00,
            'base_amount'        => 1000.00,
            'commission_amount'  => 1000.00,
            'status'             => ReferralCommission::STATUS_PAYABLE,
            'idempotency_key'    => 'comm_' . Str::random(10),
        ]);

        $payoutRequest = $payoutService->requestWithdrawal($this->seller, 600.00, $method->id);
        $this->assertEquals(400.00, $this->seller->fresh()->available_referral_balance);

        // Admin rejects payout request
        $response = $this->actingAs($this->admin)->post(route('admin.referrals.payouts.process', $payoutRequest->id), [
            'action'           => 'reject',
            'rejection_reason' => 'Invalid beneficiary name matching bank records',
        ]);
        $response->assertRedirect();

        $fresh = $payoutRequest->fresh();
        $this->assertEquals(ReferralPayoutRequest::STATUS_REJECTED, $fresh->status);
        $this->assertEquals('Invalid beneficiary name matching bank records', $fresh->rejection_reason);

        // Available balance is restored back to ₹1,000 since rejected requests do not tie up funds
        $this->assertEquals(1000.00, $this->seller->fresh()->available_referral_balance);
    }

    public function test_admin_can_bulk_approve_commissions()
    {
        $comm1 = ReferralCommission::create([
            'seller_referral_id' => $this->sellerReferral->id,
            'referrer_id'        => $this->seller->id,
            'referred_id'        => $this->referredUser->id,
            'commission_type'    => 'fixed',
            'rate_applied'       => 200.00,
            'base_amount'        => 200.00,
            'commission_amount'  => 200.00,
            'status'             => ReferralCommission::STATUS_PENDING,
            'idempotency_key'    => 'comm_' . Str::random(10),
        ]);

        $comm2 = ReferralCommission::create([
            'seller_referral_id' => $this->sellerReferral->id,
            'referrer_id'        => $this->seller->id,
            'referred_id'        => $this->referredUser->id,
            'commission_type'    => 'fixed',
            'rate_applied'       => 300.00,
            'base_amount'        => 300.00,
            'commission_amount'  => 300.00,
            'status'             => ReferralCommission::STATUS_UNDER_REVIEW,
            'idempotency_key'    => 'comm_' . Str::random(10),
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.referrals.commissions.bulk-approve'), [
            'commission_ids' => [$comm1->id, $comm2->id],
        ]);
        $response->assertRedirect();

        $this->assertEquals(ReferralCommission::STATUS_APPROVED, $comm1->fresh()->status);
        $this->assertEquals(ReferralCommission::STATUS_APPROVED, $comm2->fresh()->status);
    }

    public function test_admin_can_update_seller_referral_code()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.referrals.sellers.code', $this->seller->id), [
            'custom_code' => 'SUPERVIP99',
        ]);
        $response->assertRedirect();

        $this->assertEquals('SUPERVIP99', $this->seller->fresh()->referral_code);
    }

    public function test_csv_export_routes_return_successful_stream()
    {
        $responseCommissions = $this->actingAs($this->admin)->get(route('admin.referrals.export.commissions'));
        $responseCommissions->assertStatus(200);
        $this->assertStringContainsString('text/csv', $responseCommissions->headers->get('content-type'));

        $responsePayouts = $this->actingAs($this->admin)->get(route('admin.referrals.export.payouts'));
        $responsePayouts->assertStatus(200);
        $this->assertStringContainsString('text/csv', $responsePayouts->headers->get('content-type'));
    }

    public function test_seller_can_transfer_referral_balance_to_shipping_wallet()
    {
        $this->seller->update(['wallet_balance' => 100.00]);

        ReferralCommission::create([
            'seller_referral_id' => $this->sellerReferral->id,
            'referrer_id'        => $this->seller->id,
            'referred_id'        => $this->referredUser->id,
            'commission_type'    => 'fixed',
            'rate_applied'       => 250.00,
            'base_amount'        => 250.00,
            'commission_amount'  => 250.00,
            'status'             => ReferralCommission::STATUS_APPROVED,
            'approved_at'        => now(),
            'idempotency_key'    => 'comm_' . Str::random(10),
        ]);

        $response = $this->actingAs($this->seller)->post(route('seller.referrals.transfer-wallet'), [
            'amount' => 150.00,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Check shipping wallet credited: 100 + 150 = 250
        $this->assertEquals(250.00, (float) $this->seller->fresh()->wallet_balance);

        // Check remaining available referral balance: 250 - 150 = 100
        $this->assertEquals(100.00, $this->seller->fresh()->available_referral_balance);

        // Check payout request record created
        $this->assertDatabaseHas('referral_payout_requests', [
            'user_id' => $this->seller->id,
            'amount'  => 150.00,
            'status'  => ReferralPayoutRequest::STATUS_PAID,
        ]);
    }

    public function test_seller_cannot_transfer_more_than_available_balance()
    {
        $this->seller->update(['wallet_balance' => 0.00]);

        $response = $this->actingAs($this->seller)->post(route('seller.referrals.transfer-wallet'), [
            'amount' => 500.00,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['amount']);
        $this->assertEquals(0.00, (float) $this->seller->fresh()->wallet_balance);
    }
}
