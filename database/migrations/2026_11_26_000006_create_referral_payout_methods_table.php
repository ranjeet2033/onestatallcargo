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
        if (!Schema::hasTable('referral_payout_methods')) {
            Schema::create('referral_payout_methods', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('payout_type', 20)->default('bank'); // bank, upi
                $table->string('account_holder_name', 150)->nullable();
                $table->string('bank_name', 150)->nullable();
                $table->string('account_number', 50)->nullable();
                $table->string('ifsc_code', 20)->nullable();
                $table->string('upi_id', 100)->nullable();
                $table->boolean('is_default')->default(true);
                $table->boolean('is_verified')->default(false);
                $table->timestamps();

                $table->index(['user_id', 'is_default']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_payout_methods');
    }
};
