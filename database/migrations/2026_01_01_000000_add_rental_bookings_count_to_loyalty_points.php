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
        Schema::table('loyalty_points', function (Blueprint $table) {
            if (!Schema::hasColumn('loyalty_points', 'rental_bookings_count')) {
                $table->integer('rental_bookings_count')->default(0)->after('tier');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loyalty_points', function (Blueprint $table) {
            if (Schema::hasColumn('loyalty_points', 'rental_bookings_count')) {
                $table->dropColumn('rental_bookings_count');
            }
        });
    }
};
