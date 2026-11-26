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
            if (!Schema::hasColumn('referral_rules', 'referee_reward_value')) {
                $table->decimal('referee_reward_value', 10, 2)->default(0.00)->after('reward_value');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('referral_rules', function (Blueprint $table) {
            if (Schema::hasColumn('referral_rules', 'referee_reward_value')) {
                $table->dropColumn('referee_reward_value');
            }
        });
    }
};
