<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 0. SYSTEM TABLES (Required by Laravel)
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });

        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        
        // 1. STAFF (Independent)
        Schema::create('staff', function (Blueprint $table) {
            $table->id('staffID'); // PK
            $table->string('name');
            $table->string('role')->default('staff');
            $table->string('email')->unique();
            $table->string('phoneNo')->nullable();
            $table->string('password');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // 2. CUSTOMER (Independent) - UPDATED FOR PROFILE FORM
        Schema::create('customers', function (Blueprint $table) {
            $table->id('customerID'); // PK
            $table->string('fullName'); // Maps to 'name' in form
            $table->string('email')->unique();
            $table->string('phoneNo')->nullable();
            $table->string('stustaffID')->nullable();
            $table->string('driving_license_expiry')->nullable()->unique();
            $table->text('homeAddress')->nullable();
            $table->text('collegeAddress')->nullable();
            $table->string('password');
            
            // --- NEWLY ADDED FIELDS ---
            $table->string('faculty')->nullable(); // Added as requested
            $table->string('ic_passport')->nullable();
            $table->string('nationality')->nullable();
            $table->date('dob')->nullable();
            $table->string('emergency_contact_no')->nullable();
            
            // Image Upload Paths
            $table->string('avatar')->nullable();
            $table->string('student_card_image')->nullable();
            $table->string('ic_passport_image')->nullable();
            $table->string('driving_license_image')->nullable();
            // --------------------------

            $table->string('accountStat')->default('active');
            $table->boolean('blacklisted')->default(false);
            $table->timestamps();
        });

        // 3. VEHICLE (Independent)
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id('VehicleID'); // PK
            $table->string('plateNo')->unique();
            $table->string('model');
            $table->string('type');
            $table->decimal('priceHour', 10, 2);
            $table->boolean('availability')->default(true);
            $table->integer('mileage');
            $table->string('fuelType');
            $table->decimal('baseDepo', 10, 2);
            $table->timestamps();
        });

        // 4. VOUCHER (Depends on Customer)
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id('voucherID'); // PK
            $table->unsignedBigInteger('customerID')->nullable();
            $table->string('voucherCode')->unique();
            $table->decimal('voucherAmount', 10, 2);
            $table->string('voucherType');
            $table->date('validFrom');
            $table->date('validUntil');
            $table->text('conditions')->nullable();
            $table->boolean('isUsed')->default(false);
            $table->timestamps();

            $table->foreign('customerID')->references('customerID')->on('customers')->onDelete('cascade');
        });

        // 5. BOOKING (Depends on Staff, Customer, Vehicle, Voucher)
        Schema::create('bookings', function (Blueprint $table) {
            $table->id('bookingID'); // PK
            $table->unsignedBigInteger('staffID')->nullable();
            $table->unsignedBigInteger('customerID');
            $table->unsignedBigInteger('vehicleID');
            $table->unsignedBigInteger('voucherID')->nullable();

            $table->date('bookingDate');
            $table->date('originalDate'); // Start Date
            $table->time('bookingTime'); // Start Time
            
            $table->date('returnDate');
            $table->time('returnTime');
            
            $table->date('actualReturnDate')->nullable();
            $table->time('actualReturnTime')->nullable();

            $table->string('pickupLocation');
            $table->string('returnLocation');
            $table->decimal('totalCost', 10, 2);
            
            $table->date('aggreementDate')->nullable(); 
            $table->string('aggreementLink')->nullable(); 

            $table->string('bookingStatus')->default('Pending');
            $table->string('bookingType')->default('Standard');

            $table->timestamps();

            $table->foreign('staffID')->references('staffID')->on('staff');
            $table->foreign('customerID')->references('customerID')->on('customers');
            $table->foreign('vehicleID')->references('VehicleID')->on('vehicles'); 
            $table->foreign('voucherID')->references('voucherID')->on('vouchers');
        });

        // 6. PAYMENT (Depends on Booking)
        Schema::create('payments', function (Blueprint $table) {
            $table->id('paymentID'); // PK
            $table->unsignedBigInteger('bookingID');
            
            $table->decimal('amount', 10, 2);
            $table->decimal('depoAmount', 10, 2);
            $table->dateTime('transactionDate');
            $table->string('paymentMethod');
            $table->string('paymentStatus');
            
            $table->string('depoStatus')->default('Pending');
            $table->date('depoRequestDate')->nullable();
            $table->date('depoRefundedDate')->nullable();
            
            $table->boolean('isInstallment')->default(false);
            $table->text('installmentDetails')->nullable();

            $table->timestamps();

            $table->foreign('bookingID')->references('bookingID')->on('bookings')->onDelete('cascade');
        });

        // 7. PENALTY (Depends on Booking)
        Schema::create('penalties', function (Blueprint $table) {
            $table->id('penaltyID'); // PK
            $table->unsignedBigInteger('bookingID');

            $table->integer('lateReturnHour')->default(0);
            $table->decimal('penaltyFees', 10, 2)->default(0.00);
            $table->string('penaltyStatus')->default('Unpaid');
            $table->decimal('fuelSurcharge', 10, 2)->default(0.00);
            $table->decimal('mileageSurcharge', 10, 2)->default(0.00);
            $table->string('status')->default('Pending');

            $table->timestamps();

            $table->foreign('bookingID')->references('bookingID')->on('bookings')->onDelete('cascade');
        });

        // 8. INSPECTION (Depends on Booking, Staff)
        Schema::create('inspections', function (Blueprint $table) {
            $table->id('inspectionID'); // PK
            $table->unsignedBigInteger('bookingID');
            $table->unsignedBigInteger('staffID')->nullable();

            $table->string('inspectionType');
            $table->dateTime('inspectionDate');
            $table->decimal('damageCosts', 10, 2)->default(0.00);
            
            $table->text('photosBefore')->nullable();
            $table->text('photosAfter')->nullable();
            $table->string('fuelBefore')->nullable();
            $table->string('fuelAfter')->nullable();
            $table->integer('mileageBefore')->nullable();
            $table->integer('mileageAfter')->nullable();

            $table->timestamps();

            $table->foreign('bookingID')->references('bookingID')->on('bookings')->onDelete('cascade');
            $table->foreign('staffID')->references('staffID')->on('staff');
        });

        // 9. LOYALTY (Depends on Customer)
        Schema::create('loyalties', function (Blueprint $table) {
            $table->id('loyaltyID'); // PK
            $table->unsignedBigInteger('customerID')->unique(); // 1-to-1 relationship

            $table->string('tier')->default('Bronze');
            $table->integer('pointsEarned')->default(0);
            $table->integer('pointsRedeemed')->default(0);
            $table->integer('totalPoints')->default(0);

            $table->timestamps();

            $table->foreign('customerID')->references('customerID')->on('customers')->onDelete('cascade');
        });

        // 10. FEEDBACK (Depends on Customer, Booking)
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->id('feedbackID'); // PK
            $table->unsignedBigInteger('customerID');
            $table->unsignedBigInteger('bookingID')->nullable();

            $table->integer('rating');
            $table->text('comment')->nullable();
            $table->string('type');
            $table->string('status')->default('Pending');
            $table->text('adminNotes')->nullable();

            $table->timestamps();

            $table->foreign('customerID')->references('customerID')->on('customers')->onDelete('cascade');
            $table->foreign('bookingID')->references('bookingID')->on('bookings')->onDelete('set null');
        });

        // 11. LOGIN / SECURITY LOG (Depends on Customer, Staff)
        Schema::create('logins', function (Blueprint $table) {
            $table->id('securityLogID'); // PK
            $table->unsignedBigInteger('customerID')->nullable();
            $table->unsignedBigInteger('staffID')->nullable();

            $table->string('loginStatus');
            $table->dateTime('timestamp');
            $table->text('eventDetails')->nullable();

            $table->timestamps();

            $table->foreign('customerID')->references('customerID')->on('customers')->onDelete('cascade');
            $table->foreign('staffID')->references('staffID')->on('staff')->onDelete('cascade');
        });

        // 12. MAINTENANCE (Depends on Vehicle, Staff)
        Schema::create('maintenances', function (Blueprint $table) {
            $table->id('MaintenanceID'); // PK
            $table->unsignedBigInteger('VehicleID');
            $table->unsignedBigInteger('StaffID')->nullable();
            $table->date('date')->nullable(); 
            $table->string('description')->nullable();
            $table->decimal('cost', 10, 2)->default(0.00);

            $table->timestamps();

            $table->foreign('VehicleID')->references('VehicleID')->on('vehicles')->onDelete('cascade');
            $table->foreign('StaffID')->references('staffID')->on('staff')->onDelete('set null');
        });
    }

    public function down(): void
    {
        // Drop tables in REVERSE order
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('maintenances');
        Schema::dropIfExists('logins');
        Schema::dropIfExists('feedbacks');
        Schema::dropIfExists('loyalties');
        Schema::dropIfExists('inspections');
        Schema::dropIfExists('penalties');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('vouchers');
        Schema::dropIfExists('vehicles');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('staff');
    }
};