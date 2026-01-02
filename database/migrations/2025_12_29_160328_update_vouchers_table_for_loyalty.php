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
        // Check if vouchers table exists and add/modify columns
        if (Schema::hasTable('vouchers')) {
            Schema::table('vouchers', function (Blueprint $table) {
                // Add user_id if it doesn't exist (as alias for customerID)
                if (!Schema::hasColumn('vouchers', 'user_id')) {
                    $table->unsignedBigInteger('user_id')->nullable()->after('customerID');
                    $table->foreign('user_id')->references('customerID')->on('customers')->onDelete('cascade');
                }
                
                // Add code column if it doesn't exist (as alias for voucherCode)
                if (!Schema::hasColumn('vouchers', 'code')) {
                    $table->string('code')->nullable()->after('voucherCode');
                }
                
                // Add discount_percent if it doesn't exist
                if (!Schema::hasColumn('vouchers', 'discount_percent')) {
                    $table->integer('discount_percent')->nullable()->after('voucherAmount');
                }
                
                // Add status column if it doesn't exist (as alias for isUsed)
                if (!Schema::hasColumn('vouchers', 'status')) {
                    $table->string('status')->default('unused')->after('isUsed');
                }
                
                // Add terms_conditions if it doesn't exist (as alias for conditions)
                if (!Schema::hasColumn('vouchers', 'terms_conditions')) {
                    $table->text('terms_conditions')->nullable()->after('conditions');
                }
                
                // Add redeem_place if it doesn't exist
                if (!Schema::hasColumn('vouchers', 'redeem_place')) {
                    $table->string('redeem_place')->nullable()->after('voucherType');
                }
                
                // Add expires_at if it doesn't exist (as alias for validUntil)
                if (!Schema::hasColumn('vouchers', 'expires_at')) {
                    $table->date('expires_at')->nullable()->after('validUntil');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('vouchers')) {
            Schema::table('vouchers', function (Blueprint $table) {
                if (Schema::hasColumn('vouchers', 'user_id')) {
                    $table->dropForeign(['user_id']);
                    $table->dropColumn('user_id');
                }
                if (Schema::hasColumn('vouchers', 'code')) {
                    $table->dropColumn('code');
                }
                if (Schema::hasColumn('vouchers', 'discount_percent')) {
                    $table->dropColumn('discount_percent');
                }
                if (Schema::hasColumn('vouchers', 'status')) {
                    $table->dropColumn('status');
                }
                if (Schema::hasColumn('vouchers', 'terms_conditions')) {
                    $table->dropColumn('terms_conditions');
                }
                if (Schema::hasColumn('vouchers', 'redeem_place')) {
                    $table->dropColumn('redeem_place');
                }
                if (Schema::hasColumn('vouchers', 'expires_at')) {
                    $table->dropColumn('expires_at');
                }
            });
        }
    }
};
