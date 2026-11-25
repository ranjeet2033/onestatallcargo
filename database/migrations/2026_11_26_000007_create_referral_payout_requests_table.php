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
        if (!Schema::hasTable('referral_payout_requests')) {
            Schema::create('referral_payout_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('payout_method_id')->nullable()->constrained('referral_payout_methods')->nullOnDelete();
                $table->json('payout_method_snapshot')->nullable(); // Immutable snapshot of payment details at withdrawal time
                $table->decimal('amount', 10, 2);
                
                // Statuses: pending, processing, approved, rejected, paid
                $table->string('status', 30)->default('pending');
                $table->text('rejection_reason')->nullable();
                $table->string('payment_reference', 100)->nullable(); // Bank UTR or Transaction Ref
                $table->string('payment_proof_path', 255)->nullable(); // File upload path
                $table->text('admin_notes')->nullable();
                $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('processed_at')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'status']);
                $table->index(['status', 'created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_payout_requests');
    }
};
