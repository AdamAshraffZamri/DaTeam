<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // database/migrations/xxxx_xx_xx_xxxxxx_add_blacklist_reason_to_customers.php
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->text('blacklist_reason')->nullable()->after('blacklisted');
        });
    }

    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('blacklist_reason');
        });
    }
};
