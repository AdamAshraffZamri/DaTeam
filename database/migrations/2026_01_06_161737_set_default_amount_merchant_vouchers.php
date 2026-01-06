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
    Schema::table('merchant_vouchers', function (Blueprint $table) {
        // Tukar column 'amount' supaya default dia 5.00
        // Pastikan kau dah install 'doctrine/dbal' kalau guna method ->change()
        $table->decimal('amount', 8, 2)->default(5.00)->change();
    });
}

 /**
     * Reverse the migrations.
     */

public function down()
{
    Schema::table('merchant_vouchers', function (Blueprint $table) {
        // Rollback ke default asal (mungkin 0 atau null)
        $table->decimal('amount', 8, 2)->default(0.00)->change();
    });
}

};
