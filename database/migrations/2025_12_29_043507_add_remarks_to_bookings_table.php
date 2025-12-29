<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Solution for Requirement #4: add remarks in payment page
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Adds the remarks field for additional requests without touching existing columns
            $table->text('remarks')->nullable()->after('aggreementLink'); 
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('remarks');
        });
    }
};