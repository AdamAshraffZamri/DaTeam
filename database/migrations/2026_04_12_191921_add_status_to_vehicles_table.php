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
        Schema::table('vehicles', function (Blueprint $table) {
            // Check if 'status' column already exists before adding
            if (!Schema::hasColumn('vehicles', 'status')) {
                $table->string('status', 20)
                      ->default('available')
                      ->after('plateNo'); // Adjust 'after' to your preferred position
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            if (Schema::hasColumn('vehicles', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};