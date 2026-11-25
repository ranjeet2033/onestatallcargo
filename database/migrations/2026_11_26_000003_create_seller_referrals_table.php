<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('seller_referrals')) {
            Schema::create('seller_referrals', function (Blueprint $table) {
                $table->id();
                $table->foreignId('referrer_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('referred_id')->unique()->constrained('users')->cascadeOnDelete(); // Exactly one referrer per seller
                $table->string('referral_code', 30);
                $table->foreignId('click_id')->nullable()->constrained('referral_clicks')->nullOnDelete();
                $table->string('status', 30)->default('registered'); // registered, kyc_verified, active, completed, disqualified
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamp('qualified_at')->nullable();
                $table->text('disqualified_reason')->nullable();
                $table->timestamps();

                $table->index(['referrer_id', 'status']);
                $table->index('referral_code');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seller_referrals');
    }
};
