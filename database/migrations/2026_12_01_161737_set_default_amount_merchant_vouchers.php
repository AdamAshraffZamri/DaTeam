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
    // FIX: Only try to modify the table if it actually exists.
    // This prevents the "Table not found" error during tests if the load order is weird.
    if (Schema::hasTable('merchant_vouchers')) {
        
        // (Optional) SQLite Fix from before - keep it if you still run tests on SQLite sometimes
        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::connection()->getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
        }

        Schema::table('merchant_vouchers', function (Blueprint $table) {
            $table->decimal('amount', 8, 2)->default(0)->change();
        });
    }
}

 /**
     * Reverse the migrations.
     */

public function down()
{
    if (Schema::hasTable('merchant_vouchers')) {
        Schema::table('merchant_vouchers', function (Blueprint $table) {
            // Revert the change (make it nullable or remove default)
            $table->decimal('amount', 8, 2)->nullable()->change();
        });
    }
}

};
