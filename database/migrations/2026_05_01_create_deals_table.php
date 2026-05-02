<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create deals table for multiple current deals per staff
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff', 'staffID')->cascadeOnDelete();
            $table->string('image_path')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Update staff_settings to only store Johor highlights
        Schema::table('staff_settings', function (Blueprint $table) {
            // Drop old deals columns if they exist
            if (Schema::hasColumn('staff_settings', 'deals_image_path')) {
                $table->dropColumn('deals_image_path');
            }
            if (Schema::hasColumn('staff_settings', 'deals_description')) {
                $table->dropColumn('deals_description');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deals');
        
        Schema::table('staff_settings', function (Blueprint $table) {
            $table->string('deals_image_path')->nullable();
            $table->text('deals_description')->nullable();
        });
    }
};
