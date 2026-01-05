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
            $table->string('road_tax_image')->nullable()->after('image');
            $table->string('grant_image')->nullable()->after('road_tax_image');
            $table->string('insurance_image')->nullable()->after('grant_image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn(['road_tax_image', 'grant_image', 'insurance_image']);
        });
    }
};
