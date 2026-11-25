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
        if (!Schema::hasTable('referral_clicks')) {
            Schema::create('referral_clicks', function (Blueprint $table) {
                $table->id();
                $table->string('referral_code', 30)->index();
                $table->foreignId('referrer_id')->constrained('users')->cascadeOnDelete();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->text('referrer_url')->nullable();
                $table->string('landing_page', 255)->nullable();
                $table->timestamp('converted_at')->nullable();
                $table->timestamps();

                $table->index(['referrer_id', 'created_at']);
                $table->index(['referral_code', 'created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_clicks');
    }
};
