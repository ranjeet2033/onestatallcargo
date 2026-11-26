<?php

namespace Tests\Feature;

use App\Models\Kyc;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class PasswordManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $seller;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seller = User::create([
            'name'     => 'Active Seller',
            'email'    => 'seller_pwd_' . Str::random(5) . '@test.com',
            'phone'    => '9888877771',
            'password' => Hash::make('oldpassword123'),
            'role'     => 'seller',
            'status'   => 'active',
        ]);

        Kyc::create([
            'user_id' => $this->seller->id,
            'status'  => 'approved',
        ]);

        $this->admin = User::create([
            'name'     => 'Master Admin',
            'email'    => 'admin_pwd_' . Str::random(5) . '@test.com',
            'phone'    => '9888877772',
            'password' => Hash::make('adminpassword'),
            'role'     => 'admin',
            'status'   => 'active',
        ]);
    }

    public function test_seller_can_update_own_password()
    {
        $response = $this->actingAs($this->seller)->postJson(route('seller.settings.password'), [
            'current_password'          => 'oldpassword123',
            'new_password'              => 'freshpassword456',
            'new_password_confirmation' => 'freshpassword456',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertTrue(Hash::check('freshpassword456', $this->seller->fresh()->password));
    }

    public function test_seller_cannot_update_with_wrong_current_password()
    {
        $response = $this->actingAs($this->seller)->postJson(route('seller.settings.password'), [
            'current_password'          => 'wrongpassword',
            'new_password'              => 'freshpassword456',
            'new_password_confirmation' => 'freshpassword456',
        ]);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
        $this->assertFalse(Hash::check('freshpassword456', $this->seller->fresh()->password));
    }

    public function test_seller_cannot_update_with_unmatched_confirmation()
    {
        $response = $this->actingAs($this->seller)->postJson(route('seller.settings.password'), [
            'current_password'          => 'oldpassword123',
            'new_password'              => 'freshpassword456',
            'new_password_confirmation' => 'different456',
        ]);

        $response->assertStatus(422);
        $this->assertFalse(Hash::check('freshpassword456', $this->seller->fresh()->password));
    }

    public function test_admin_can_reset_any_seller_password()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.sellers.password', $this->seller->id), [
            'password'              => 'adminResetPass99',
            'password_confirmation' => 'adminResetPass99',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertTrue(Hash::check('adminResetPass99', $this->seller->fresh()->password));
    }

    public function test_non_admin_cannot_reset_seller_password()
    {
        $otherSeller = User::create([
            'name'     => 'Other Seller',
            'email'    => 'other_' . Str::random(5) . '@test.com',
            'phone'    => '9888877779',
            'password' => Hash::make('password123'),
            'role'     => 'seller',
            'status'   => 'active',
        ]);

        $response = $this->actingAs($otherSeller)->post(route('admin.sellers.password', $this->seller->id), [
            'password'              => 'hackedPass123',
            'password_confirmation' => 'hackedPass123',
        ]);

        $response->assertRedirect(route('seller.dashboard'));
        $this->assertFalse(Hash::check('hackedPass123', $this->seller->fresh()->password));
    }
}
