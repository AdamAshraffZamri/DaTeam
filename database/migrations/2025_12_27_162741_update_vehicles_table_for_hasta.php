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
            $table->string('vehicle_category')->default('car'); // car or bike
            $table->string('brand')->nullable();
            $table->integer('year')->nullable();
            $table->string('color')->nullable();
            $table->json('hourly_rates')->nullable(); // Store tiers: 1, 3, 5, 7, 9, 12, 24
            $table->string('owner_name')->nullable();
            $table->string('owner_phone')->nullable();
            $table->string('owner_nric')->nullable();
            $table->string('image')->nullable();
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
