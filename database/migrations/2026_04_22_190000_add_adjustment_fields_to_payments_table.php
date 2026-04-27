<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'remarks')) {
                $table->text('remarks')->nullable()->after('depoStatus');
            }

            if (!Schema::hasColumn('payments', 'depo_evidence')) {
                $table->json('depo_evidence')->nullable()->after('remarks');
            }

            // FIXED: Explicitly reference 'staffID' instead of the default 'id'
            if (!Schema::hasColumn('payments', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable()->after('depo_evidence');
                
                $table->foreign('updated_by')
                      ->references('staffID')
                      ->on('staff')
                      ->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'updated_by')) {
                $table->dropForeign(['updated_by']);
                $table->dropColumn('updated_by');
            }

            if (Schema::hasColumn('payments', 'depo_evidence')) {
                $table->dropColumn('depo_evidence');
            }

            if (Schema::hasColumn('payments', 'remarks')) {
                $table->dropColumn('remarks');
            }
        });
    }
};