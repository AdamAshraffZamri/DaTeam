<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('maintenances', function (Blueprint $table) {
            // Change existing 'date' column usage or add new datetime columns
            // We will add specific datetime columns for precision
            $table->dateTime('start_time')->nullable()->after('date');
            $table->dateTime('end_time')->nullable()->after('start_time');
            
            // Add type to distinguish between Service, Holiday, Delivery
            $table->string('type')->default('maintenance')->after('StaffID'); // maintenance, holiday, delivery, other
            
            // Add reference ID for things like Delivery (linking to a booking)
            $table->string('reference_id')->nullable()->after('description');
        });
    }

    public function down()
    {
        Schema::table('maintenances', function (Blueprint $table) {
            $table->dropColumn(['start_time', 'end_time', 'type', 'reference_id']);
        });
    }
};
