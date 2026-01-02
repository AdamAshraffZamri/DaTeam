<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            // This column will hold the status (e.g., 'active', 'pending') temporarily
            $table->string('previous_account_stat')->nullable()->after('accountStat');
        });
    }

    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('previous_account_stat');
        });
    }
};
