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
        Schema::create('indiapost_awbs', function (Blueprint $table) {
            $table->id();
            $table->string('awb_number', 20)->unique();
            $table->enum('status', ['available', 'used'])->default('available');
            $table->string('order_id')->nullable();
            $table->unsignedBigInteger('shipment_id')->nullable();
            $table->string('article_type', 50)->default('SP_INLAND_PARCEL');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('indiapost_awbs');
    }
};
