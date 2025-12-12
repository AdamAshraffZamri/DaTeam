<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Staff
        Schema::create('staff', function (Blueprint $table) {
            $table->id('staff_id'); // Primary Key
            $table->string('name', 100);
            $table->string('email', 100)->unique();
            $table->string('phone_number', 20);
            $table->string('role', 30);
            $table->string('password');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Customer
        Schema::create('customers', function (Blueprint $table) {
            $table->id('customer_id');
            $table->string('name', 100);
            $table->string('email', 100)->unique();
            $table->string('phone_no', 20);
            $table->string('password');
            $table->string('driving_license', 50);
            $table->string('studentstaff_id', 50)->nullable();
            $table->string('verif_status', 20);
            $table->integer('loyalty_points')->default(0);
            $table->boolean('is_blacklisted')->default(false);
            $table->string('home_address', 100);
            $table->string('college_address', 100);
            $table->timestamps();
        });

        // 3. Voucher (Depends on Customer)
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id('voucher_id');
            $table->string('voucher_code', 20)->unique(); // Needs unique index for FK reference
            $table->string('voucher_type', 20);
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->decimal('discount_value', 8, 2);
            $table->boolean('is_used')->default(false);
            $table->date('valid_from');
            $table->date('valid_until');
            $table->text('conditions')->nullable();
            $table->timestamps();

            $table->foreign('customer_id')->references('customer_id')->on('customers');
        });

        // 4. Vehicle (Created WITHOUT current_book_id FK first to avoid error)
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id('vehicle_id');
            $table->string('plate_no', 15)->unique();
            $table->string('model', 50);
            $table->string('type', 20);
            $table->decimal('price_hour', 8, 2);
            $table->boolean('availability')->default(true);
            $table->integer('mileage');
            $table->string('fuel_pickup', 20);
            $table->decimal('base_deposit', 8, 2);
            $table->unsignedBigInteger('current_book_id')->nullable(); // Constraint added later
            $table->timestamps();
        });

        // 5. Booking (Depends on Staff, Customer, Vehicle, Voucher)
        Schema::create('bookings', function (Blueprint $table) {
            $table->id('booking_id');
            $table->unsignedBigInteger('staff_id')->nullable();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('vehicle_id');
            $table->dateTime('booking_date');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('pickup_location', 100)->nullable();
            $table->string('return_location', 100);
            $table->decimal('total_cost', 10, 2);
            $table->string('booking_status', 20);
            $table->string('digital_agreement_link', 255)->nullable();
            $table->dateTime('agreement_date')->nullable();
            $table->date('original_pickup_date')->nullable();
            $table->string('voucher_code_used', 20)->nullable();
            $table->string('booking_type', 20);
            $table->timestamps();

            // Foreign Keys
            $table->foreign('staff_id')->references('staff_id')->on('staff');
            $table->foreign('customer_id')->references('customer_id')->on('customers');
            $table->foreign('vehicle_id')->references('vehicle_id')->on('vehicles');
            $table->foreign('voucher_code_used')->references('voucher_code')->on('vouchers');
        });

        // 6. Maintenance (Depends on Vehicle, Staff)
        Schema::create('maintenances', function (Blueprint $table) {
            $table->id('maintenance_id');
            $table->unsignedBigInteger('vehicle_id');
            $table->unsignedBigInteger('staff_id');
            $table->date('maintenance_date');
            $table->string('maintenance_type', 50);
            $table->text('description')->nullable();
            $table->decimal('maintenance_cost', 8, 2);
            $table->timestamps();

            $table->foreign('vehicle_id')->references('vehicle_id')->on('vehicles');
            $table->foreign('staff_id')->references('staff_id')->on('staff');
        });

        // 7. Inspection (Depends on Booking, Staff)
        Schema::create('inspections', function (Blueprint $table) {
            $table->id('inspection_id');
            $table->unsignedBigInteger('booking_id');
            $table->unsignedBigInteger('staff_id');
            $table->dateTime('inspection_date');
            $table->string('inspection_type', 30);
            $table->text('condition_notes')->nullable();
            $table->decimal('damage_cost', 8, 2)->nullable();
            $table->string('photos_before', 255)->nullable();
            $table->string('photos_after', 255)->nullable();
            $table->string('fuel_before', 20)->nullable();
            $table->string('fuel_after', 20)->nullable();
            $table->integer('mileage_before')->nullable();
            $table->integer('mileage_after')->nullable();
            $table->timestamps();

            $table->foreign('booking_id')->references('booking_id')->on('bookings');
            $table->foreign('staff_id')->references('staff_id')->on('staff');
        });

        // 8. Payment (Depends on Booking)
        Schema::create('payments', function (Blueprint $table) {
            $table->id('payment_id');
            $table->unsignedBigInteger('booking_id');
            $table->string('payment_method', 20);
            $table->decimal('amount', 10, 2);
            $table->string('payment_status', 20);
            $table->decimal('deposit_amount', 8, 2)->nullable();
            $table->string('deposit_status', 20)->nullable();
            $table->date('depo_refund_request_date')->nullable();
            $table->date('depo_refunded_date')->nullable();
            $table->dateTime('transaction_date');
            $table->boolean('is_installment')->default(false);
            $table->text('installment_plan_details')->nullable();
            $table->timestamps();

            $table->foreign('booking_id')->references('booking_id')->on('bookings');
        });

        // 9. Loyalty (Depends on Customer)
        Schema::create('loyalties', function (Blueprint $table) {
            $table->id('loyalty_id');
            $table->unsignedBigInteger('customer_id');
            $table->integer('points_earned')->default(0);
            $table->integer('points_redeemed')->default(0);
            $table->integer('total_points')->default(0);
            $table->string('tier', 20);
            $table->timestamps();

            $table->foreign('customer_id')->references('customer_id')->on('customers');
        });

        // 10. Feedback (Depends on Booking, Customer)
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->id('feedback_id');
            $table->unsignedBigInteger('booking_id');
            $table->unsignedBigInteger('customer_id');
            $table->integer('rating');
            $table->text('comments')->nullable();
            $table->dateTime('feedback_date');
            $table->string('type', 20);
            $table->string('status', 20);
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            $table->foreign('booking_id')->references('booking_id')->on('bookings');
            $table->foreign('customer_id')->references('customer_id')->on('customers');
        });

        // 11. Penalty (Depends on Booking)
        Schema::create('penalties', function (Blueprint $table) {
            $table->id('penalty_id');
            $table->unsignedBigInteger('booking_id');
            $table->decimal('late_return_hours', 5, 2)->nullable();
            $table->decimal('penalty_fee', 8, 2)->nullable();
            $table->string('penalty_status', 20);
            $table->decimal('fuel_surcharge', 8, 2)->nullable();
            $table->decimal('mileage_surcharge', 8, 2)->nullable();
            $table->timestamps();

            $table->foreign('booking_id')->references('booking_id')->on('bookings');
        });

        // 12. Login / Security Log (Depends on Customer)
        Schema::create('security_logs', function (Blueprint $table) {
            $table->id('security_log_id');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('login_status', 20);
            $table->dateTime('timestamp');
            $table->text('event_details')->nullable();
            $table->timestamps();

            $table->foreign('customer_id')->references('customer_id')->on('customers');
        });

        // *** POST-CREATION CONSTRAINTS (Circular Dependency) ***
        // Add the FK to Vehicle now that Booking exists
        Schema::table('vehicles', function (Blueprint $table) {
            $table->foreign('current_book_id')->references('booking_id')->on('bookings');
        });
    }

    public function down(): void
    {
        // Drop tables in reverse order of dependency
        Schema::dropIfExists('security_logs');
        Schema::dropIfExists('penalties');
        Schema::dropIfExists('feedbacks');
        Schema::dropIfExists('loyalties');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('inspections');
        Schema::dropIfExists('maintenances');
        
        // Remove circular dependency before dropping
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropForeign(['current_book_id']);
        });
        
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('vehicles');
        Schema::dropIfExists('vouchers');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('staff');
    }
};