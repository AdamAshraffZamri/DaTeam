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
        Schema::table('penalties', function (Blueprint $table) {
            $table->unsignedBigInteger('customerID')->nullable()->after('penaltyID');
            $table->text('reason')->nullable()->after('status');
            $table->decimal('amount', 10, 2)->default(0.00)->after('reason');
            $table->date('date_imposed')->nullable()->after('amount');
            
            // Make bookingID nullable to support customer-level penalties
            $table->unsignedBigInteger('bookingID')->nullable()->change();
            
            // Add foreign key for customerID
            $table->foreign('customerID')->references('customerID')->on('customers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penalties', function (Blueprint $table) {
            $table->dropForeign(['customerID']);
            $table->dropColumn(['customerID', 'reason', 'amount', 'date_imposed']);
            $table->unsignedBigInteger('bookingID')->nullable(false)->change();
        });
    }
};
