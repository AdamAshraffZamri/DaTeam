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
    Schema::table('rewards', function (Blueprint $table) {
        $table->integer('milestone_step')->nullable(); // Contoh: 3, 6, 9, 12
        $table->integer('discount_percent')->default(0); // Contoh: 20, 50, 70
        // Set 'category' kepada 'Milestone' untuk reward jenis ini
    });
}



    /**
     * Reverse the migrations.
     */
        public function down()
    {
        Schema::table('rewards', function (Blueprint $table) {
            $table->dropColumn(['milestone_step', 'discount_percent']);
        });
    }
};
