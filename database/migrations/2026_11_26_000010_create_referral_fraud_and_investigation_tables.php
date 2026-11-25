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
        // 1. Add risk scoring and investigation columns to seller_referrals
        if (Schema::hasTable('seller_referrals')) {
            Schema::table('seller_referrals', function (Blueprint $table) {
                if (!Schema::hasColumn('seller_referrals', 'risk_score')) {
                    $table->unsignedSmallInteger('risk_score')->default(0)->after('status');
                }
                if (!Schema::hasColumn('seller_referrals', 'risk_level')) {
                    $table->string('risk_level', 20)->default('low')->after('risk_score'); // low, medium, high, critical
                }
                if (!Schema::hasColumn('seller_referrals', 'risk_factors')) {
                    $table->json('risk_factors')->nullable()->after('risk_level');
                }
                if (!Schema::hasColumn('seller_referrals', 'is_flagged')) {
                    $table->boolean('is_flagged')->default(false)->after('risk_factors');
                }
                if (!Schema::hasColumn('seller_referrals', 'is_held')) {
                    $table->boolean('is_held')->default(false)->after('is_flagged');
                }
                if (!Schema::hasColumn('seller_referrals', 'investigated_at')) {
                    $table->timestamp('investigated_at')->nullable()->after('is_held');
                }
                if (!Schema::hasColumn('seller_referrals', 'investigated_by')) {
                    $table->foreignId('investigated_by')->nullable()->constrained('users')->nullOnDelete()->after('investigated_at');
                }
                if (!Schema::hasColumn('seller_referrals', 'investigation_notes')) {
                    $table->text('investigation_notes')->nullable()->after('investigated_by');
                }
                if (!Schema::hasColumn('seller_referrals', 'device_fingerprint')) {
                    $table->string('device_fingerprint', 100)->nullable()->after('investigation_notes');
                }
            });
        }

        // 2. Add risk scoring to referral_commissions
        if (Schema::hasTable('referral_commissions')) {
            Schema::table('referral_commissions', function (Blueprint $table) {
                if (!Schema::hasColumn('referral_commissions', 'risk_score')) {
                    $table->unsignedSmallInteger('risk_score')->default(0)->after('commission_amount');
                }
                if (!Schema::hasColumn('referral_commissions', 'risk_level')) {
                    $table->string('risk_level', 20)->default('low')->after('risk_score');
                }
                if (!Schema::hasColumn('referral_commissions', 'risk_factors')) {
                    $table->json('risk_factors')->nullable()->after('risk_level');
                }
                if (!Schema::hasColumn('referral_commissions', 'is_held')) {
                    $table->boolean('is_held')->default(false)->after('risk_factors');
                }
            });
        }

        // 3. Add risk scoring to referral_payout_requests
        if (Schema::hasTable('referral_payout_requests')) {
            Schema::table('referral_payout_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('referral_payout_requests', 'risk_score')) {
                    $table->unsignedSmallInteger('risk_score')->default(0)->after('status');
                }
                if (!Schema::hasColumn('referral_payout_requests', 'risk_level')) {
                    $table->string('risk_level', 20)->default('low')->after('risk_score');
                }
                if (!Schema::hasColumn('referral_payout_requests', 'risk_factors')) {
                    $table->json('risk_factors')->nullable()->after('risk_level');
                }
            });
        }

        // 4. Create dedicated referral_fraud_investigations table
        if (!Schema::hasTable('referral_fraud_investigations')) {
            Schema::create('referral_fraud_investigations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('seller_referral_id')->nullable()->constrained('seller_referrals')->cascadeOnDelete();
                $table->foreignId('commission_id')->nullable()->constrained('referral_commissions')->nullOnDelete();
                $table->foreignId('payout_request_id')->nullable()->constrained('referral_payout_requests')->nullOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // Target seller
                $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete(); // Reviewer
                $table->string('action', 40); // hold, release, reject, verify, flag
                $table->unsignedSmallInteger('risk_score')->default(0);
                $table->string('risk_level', 20)->default('low');
                $table->text('findings')->nullable();
                $table->json('details')->nullable();
                $table->timestamps();

                $table->index(['seller_referral_id', 'action']);
                $table->index(['user_id', 'created_at']);
                $table->index(['admin_id', 'created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_fraud_investigations');

        if (Schema::hasTable('referral_payout_requests')) {
            Schema::table('referral_payout_requests', function (Blueprint $table) {
                $table->dropColumn(['risk_score', 'risk_level', 'risk_factors']);
            });
        }

        if (Schema::hasTable('referral_commissions')) {
            Schema::table('referral_commissions', function (Blueprint $table) {
                $table->dropColumn(['risk_score', 'risk_level', 'risk_factors', 'is_held']);
            });
        }

        if (Schema::hasTable('seller_referrals')) {
            Schema::table('seller_referrals', function (Blueprint $table) {
                $table->dropForeign(['investigated_by']);
                $table->dropColumn([
                    'risk_score',
                    'risk_level',
                    'risk_factors',
                    'is_flagged',
                    'is_held',
                    'investigated_at',
                    'investigated_by',
                    'investigation_notes',
                    'device_fingerprint',
                ]);
            });
        }
    }
};
