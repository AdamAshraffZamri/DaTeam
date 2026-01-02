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
        Schema::create('loyalty_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // References customerID
            $table->integer('points_change'); // Can be positive (earned) or negative (redeemed)
            $table->string('reason');
            $table->timestamps();

            $table->foreign('user_id')->references('customerID')->on('customers')->onDelete('cascade');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loyalty_histories');
    }
};
