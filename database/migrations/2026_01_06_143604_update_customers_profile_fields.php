<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // 1. Drop the unique index and the old 'driving_license_expiry' column
            // (Note: We drop it because we can't convert a License Number like 'B2939' into a Date)
            $table->dropUnique(['driving_license_expiry']);
            $table->dropColumn('driving_license_expiry');

            // 2. Add the new Expiry Date column
            $table->date('driving_license_expiry')->nullable()->after('stustaffID');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('driving_license_expiry');
            $table->string('driving_license_expiry')->nullable()->unique();
        });
    }
};