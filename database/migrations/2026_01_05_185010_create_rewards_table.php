<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('rewards', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., SEDAP KITCHEN
            $table->string('offer_description'); // e.g., RM 5 OFF
            $table->integer('points_required'); // e.g., 200
            $table->string('code_prefix'); // e.g., SDKTN
            $table->integer('validity_months')->default(3);
            $table->string('category')->default('Food'); // Food, Entertainment, etc.
            $table->string('icon_class')->default('fa-utensils'); // FontAwesome class
            $table->string('color_class')->default('bg-yellow-600/20 border-yellow-500/30'); // CSS classes
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('rewards');
    }
};