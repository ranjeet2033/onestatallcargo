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
        if (!Schema::hasTable('referral_commissions')) {
            Schema::create('referral_commissions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('seller_referral_id')->constrained('seller_referrals')->cascadeOnDelete();
                $table->foreignId('referrer_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('referred_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('shipment_id')->nullable()->constrained('shipments')->nullOnDelete();
                $table->foreignId('rule_id')->nullable()->constrained('referral_rules')->nullOnDelete();
                $table->string('commission_type', 20)->default('fixed'); // fixed, percentage
                $table->decimal('rate_applied', 10, 2)->default(0.00);
                $table->decimal('base_amount', 10, 2)->default(0.00); // freight or order base value
                $table->decimal('commission_amount', 10, 2)->default(0.00);
                
                // Statuses: pending, under_review, approved, rejected, payable, paid
                $table->string('status', 30)->default('pending');
                $table->text('rejection_reason')->nullable();
                
                // Workflow audit timestamps & actors
                $table->timestamp('approved_at')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('payable_at')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete();
                $table->string('payment_reference', 100)->nullable(); // Bank UTR / Transaction ID
                $table->text('payout_notes')->nullable();

                // Idempotency key prevents duplicate commission creation across race conditions or retries
                $table->string('idempotency_key', 120)->unique();

                $table->timestamps();

                $table->index(['referrer_id', 'status']);
                $table->index(['referred_id', 'status']);
                $table->index(['shipment_id']);
                $table->index(['status', 'created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_commissions');
    }
};
