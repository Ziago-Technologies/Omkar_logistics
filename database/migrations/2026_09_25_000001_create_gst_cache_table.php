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
        Schema::create('gst_cache', function (Blueprint $table) {
            $table->id();
            $table->string('gstin', 15)->unique();
            $table->string('legal_name')->nullable();
            $table->string('trade_name')->nullable();
            $table->string('status')->default('Active'); // Active, Cancelled, Inactive, Suspended
            $table->string('taxpayer_type')->nullable(); // Regular, Composition, etc.
            $table->string('constitution')->nullable(); // Proprietorship, Private Limited Company, etc.
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('district')->nullable();
            $table->string('state_name')->nullable();
            $table->string('state_code', 2)->nullable();
            $table->string('pincode', 10)->nullable();
            $table->string('pan', 10)->nullable();
            $table->string('source')->default('live_api'); // live_api, cache, local_db, algorithm
            $table->json('raw_response')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamps();

            $table->index('state_code');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gst_cache');
    }
};
