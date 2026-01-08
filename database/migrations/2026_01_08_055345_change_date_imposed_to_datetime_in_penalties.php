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
        // Tukar dari DATE ke DATETIME
        $table->dateTime('date_imposed')->change();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penalties', function (Blueprint $table) {
        $table->date('date_imposed')->change();
    });
    }
};
