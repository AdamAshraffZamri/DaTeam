<?php

// database/migrations/xxxx_xx_xx_xxxxxx_add_rejection_reason_to_customers.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->text('rejection_reason')->nullable()->after('accountStat');
        });
    }

    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('rejection_reason');
        });
    }
};
