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
        Schema::table('customers', function (Blueprint $table) {
            // Add new columns (nullable so existing users don't break)
            $table->string('bankName')->nullable()->after('password');
            $table->string('bankAccountNo')->nullable()->after('bankName');            
        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'bankName',
                'bankAccountNo',
            ]);
        });
    }
};