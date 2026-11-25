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
        if (!Schema::hasTable('referral_rules')) {
            Schema::create('referral_rules', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->string('reward_type', 20)->default('fixed'); // fixed, percentage
                $table->decimal('reward_value', 10, 2)->default(0.00); // flat Rs or percentage
                $table->string('trigger_event', 40)->default('first_delivered_shipment'); // first_delivered_shipment, every_delivered_shipment
                $table->unsignedInteger('min_shipments_required')->default(1);
                $table->decimal('min_order_value', 10, 2)->default(0.00); // min freight or invoice
                $table->unsignedInteger('validity_days')->default(90); // qualification window from registration
                $table->decimal('max_commission_per_referral', 10, 2)->nullable(); // cap
                $table->boolean('requires_kyc')->default(true);
                $table->boolean('requires_phone_verified')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_rules');
    }
};
