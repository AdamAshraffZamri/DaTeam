<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff', 'staffID')->cascadeOnDelete();
            
            // Current Deals
            $table->string('deals_image_path')->nullable();
            $table->text('deals_description')->nullable();
            
            // Johor Highlights
            $table->string('johor_highlights_image_path')->nullable();
            
            $table->timestamps();
            
            // Ensure one setting per staff member
            $table->unique('staff_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_settings');
    }
};
