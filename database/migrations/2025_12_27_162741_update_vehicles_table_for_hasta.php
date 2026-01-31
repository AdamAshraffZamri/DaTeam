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
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('vehicle_category', 50)->default('car'); // car or bike
            $table->string('brand', 100)->nullable();
            $table->integer('year')->nullable();
            $table->string('color', 50)->nullable();
            $table->json('hourly_rates')->nullable(); // Store tiers: 1, 3, 5, 7, 9, 12, 24
            $table->string('owner_name', 100)->nullable();
            $table->string('owner_phone', 20)->nullable();
            $table->string('owner_nric', 50)->nullable();
            $table->string('image', 255)->nullable();
            $table->json('blocked_dates')->nullable()->after('availability');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'vehicle_category',
                'brand',
                'year',
                'color',
                'hourly_rates',
                'owner_name',
                'owner_phone',
                'owner_nric',
                'image',
                'blocked_dates'
            ]);
        });
    }
};
