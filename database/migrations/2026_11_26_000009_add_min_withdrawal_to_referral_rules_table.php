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
        Schema::table('referral_rules', function (Blueprint $table) {
            if (!Schema::hasColumn('referral_rules', 'min_withdrawal_amount')) {
                $table->decimal('min_withdrawal_amount', 10, 2)->default(500.00)->after('max_commission_per_referral');
            }
            if (!Schema::hasColumn('referral_rules', 'auto_approve_commission')) {
                $table->boolean('auto_approve_commission')->default(true)->after('requires_phone_verified');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('referral_rules', function (Blueprint $table) {
            if (Schema::hasColumn('referral_rules', 'min_withdrawal_amount')) {
                $table->dropColumn('min_withdrawal_amount');
            }
            if (Schema::hasColumn('referral_rules', 'auto_approve_commission')) {
                $table->dropColumn('auto_approve_commission');
            }
        });
    }
};
