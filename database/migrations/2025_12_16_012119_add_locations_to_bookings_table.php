<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('bookings', function (Blueprint $table) {
        // We ensure both columns exist and are nullable
        if (!Schema::hasColumn('bookings', 'pickup_location')) {
            $table->string('pickup_location')->nullable()->after('end_date');
        }
        if (!Schema::hasColumn('bookings', 'return_location')) {
            $table->string('return_location')->nullable()->after('pickup_location');
        }
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            //
        });
    }
};
