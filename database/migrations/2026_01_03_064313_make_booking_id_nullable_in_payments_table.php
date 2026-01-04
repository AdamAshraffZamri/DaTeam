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
        Schema::table('payments', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['bookingID']);
            
            // Make bookingID nullable
            $table->unsignedBigInteger('bookingID')->nullable()->change();
            
            // Re-add foreign key constraint (nullable foreign keys are allowed)
            $table->foreign('bookingID')->references('bookingID')->on('bookings')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Drop foreign key constraint
            $table->dropForeign(['bookingID']);
            
            // Make bookingID NOT NULL again
            $table->unsignedBigInteger('bookingID')->nullable(false)->change();
            
            // Re-add foreign key constraint
            $table->foreign('bookingID')->references('bookingID')->on('bookings')->onDelete('cascade');
        });
    }
};
