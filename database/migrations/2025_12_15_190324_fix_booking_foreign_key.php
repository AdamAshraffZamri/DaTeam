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
    Schema::table('bookings', function (Blueprint $table) {
        // 1. Drop the old link to the 'customers' table
        $table->dropForeign('bookings_customer_id_foreign'); 
        
        // 2. Create a new link to the 'users' table
        $table->foreign('customer_id')->references('id')->on('users')->onDelete('cascade');
    });
}

public function down(): void
{
    Schema::table('bookings', function (Blueprint $table) {
        $table->dropForeign(['customer_id']);
        $table->foreign('customer_id')->references('customer_id')->on('customers');
    });
}
};
