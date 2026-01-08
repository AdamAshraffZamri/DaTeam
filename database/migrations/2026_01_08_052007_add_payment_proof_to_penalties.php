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
            // Tambah column baru
            $table->string('payment_proof')->nullable()->after('amount');
            $table->timestamp('paid_at')->nullable()->after('payment_proof');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penalties', function (Blueprint $table) {
            $table->dropColumn(['payment_proof', 'paid_at']);
        });
    }
};
