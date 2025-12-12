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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            
            // Standard Fields
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            // --- HASTA Custom Fields (Based on your Screenshots) ---
            $table->string('phone')->nullable();               // "Phone No."
            $table->string('student_staff_id')->nullable();    // "Student/Staff ID"
            $table->string('ic_passport')->nullable();         // "IC/Passport No."
            $table->date('dob')->nullable();                   // "Date Of Birth"
            $table->text('home_address')->nullable();          // "Home Address"
            $table->text('college_address')->nullable();       // "College Address"
            $table->string('driving_license_no')->nullable();  // "Driving License No."
            $table->string('emergency_contact_no')->nullable();// "Emergency Contact No."
            $table->string('nationality')->default('Malaysia');// "Nationality"

            // Role Management (For the Customer/Staff toggle in Login)
            $table->enum('role', ['customer', 'staff'])->default('customer');

            // Security Question (For the specific Password Reset screen)
            $table->string('security_question')->nullable();
            $table->string('security_answer')->nullable();

            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};