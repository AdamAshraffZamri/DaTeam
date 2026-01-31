<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Booking Model
 * 
 * Represents a rental booking transaction between customer and vehicle.
 * Tracks complete booking lifecycle from creation through completion or cancellation.
 * 
 * Key Features:
 * - Booking lifecycle management (Pending → Confirmed → Active → Completed/Cancelled)
 * - Date and time tracking (booking date, pickup date, return date, actual dates)
 * - Cost tracking and calculation (base rental, deposit, surcharges, total)
 * - Agreement document management (PDF storage and linking)
 * - Payment tracking (deposit, balance, additional charges)
 * - Penalty and inspection integration
 * - Voucher and discount application
 * - Multi-status support for operational workflows
 * 
 * Booking Statuses:
 * - Pending: Initial booking, awaiting verification
 * - Submitted: Documentation submitted, pending staff review
 * - Confirmed: Payment received, ready for pickup
 * - Active/Ongoing: Vehicle in customer possession
 * - Completed: Booking finished, inspections done
 * - Cancelled: Booking cancelled by customer or staff
 * - Deposit Paid: Payment deposit received
 * 
 * Database Constraints:
 * - bookingStatus: max 50 characters
 * - bookingType: max 50 characters (Half Day, Full Day, Multi-day)
 * - remarks: max 150 characters
 * - pickupLocation, returnLocation: text fields for addresses
 * - totalCost: decimal(10,2)
 * - aggreementLink: file path (up to 255)
 * 
 * Relationships:
 * - belongsTo: Customer (customerID)
 * - belongsTo: Vehicle (vehicleID)
 * - hasOne: Payment (latest payment)
 * - hasMany: Payments (all payments history)
 * - hasMany: Inspections (pre and post inspections)
 * - hasMany: Penalties (charges and fees)
 * - belongsTo: Voucher (voucherID, optional)
 * 
 * Key Workflows:
 * 1. Creation: Customer submits booking request
 * 2. Verification: Staff verifies availability, checks customer profile
 * 3. Payment: Customer makes payment deposit
 * 4. Confirmation: Staff confirms booking after payment
 * 5. Pickup: Pre-inspection, customer picks up vehicle
 * 6. Active: Vehicle in use, customer has it
 * 7. Return: Post-inspection, customer returns vehicle
 * 8. Settlement: Penalties calculated, balance charged
 * 9. Completion: All charges settled, booking closed
 * 
 * @property int $bookingID Primary key
 * @property int $customerID Foreign key to customers
 * @property int $vehicleID Foreign key to vehicles
 * @property int $staffID Foreign key to staff (staff who confirmed booking)
 * @property int $voucherID Foreign key to vouchers (optional discount)
 * @property date $bookingDate Date booking was created
 * @property date $originalDate Date customer will pick up vehicle
 * @property time $bookingTime Time of booking creation
 * @property date $returnDate Date vehicle should be returned
 * @property time $returnTime Time vehicle should be returned
 * @property date $actualReturnDate Actual vehicle return date (if returned)
 * @property time $actualReturnTime Actual vehicle return time (if returned)
 * @property string $pickupLocation Location where customer picks up
 * @property string $returnLocation Location where customer returns
 * @property decimal $totalCost Total booking cost
 * @property date $aggreementDate Date agreement was signed
 * @property string $aggreementLink Path to PDF agreement document
 * @property string $bookingStatus Current booking status (max 50)
 * @property string $bookingType Rental type (max 50)
 * @property string $remarks Additional remarks (max 150)
 */
class Booking extends Model
{
    use HasFactory;

    /**
     * The primary key associated with the model.
     * Database uses 'bookingID' instead of default 'id'.
     * 
     * @var string
     */
    protected $primaryKey = 'bookingID'; 
    
    /**
     * The attributes that are mass assignable.
     * These fields can be populated via fill() or create() methods.
     * 
     * @var array
     */
    protected $fillable = [
        'customerID', 'vehicleID', 'staffID', 'voucherID',
        'bookingDate', 'originalDate', 'bookingTime',
        'returnDate', 'returnTime', 'actualReturnDate', 'actualReturnTime',
        'pickupLocation', 'returnLocation', 'totalCost',
        'aggreementDate', 'aggreementLink',
        'bookingStatus', 'bookingType','remarks'
    ];

    // --- RELATIONSHIPS ---

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customerID', 'customerID');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicleID', 'VehicleID');
    }

    // 1. ALLOWS ACCESSING $booking->payment (Single Latest Payment)
    public function payment()
    {
        return $this->hasOne(Payment::class, 'bookingID', 'bookingID')->latest();
    }

    // 2. KEEPS HISTORY OF ALL PAYMENTS
    public function payments()
    {
        return $this->hasMany(Payment::class, 'bookingID', 'bookingID');
    }

    // 3. THIS WAS MISSING -> FIXES THE ERROR
    public function inspections()
    {
        return $this->hasMany(Inspection::class, 'bookingID', 'bookingID');
    }

    public function penalties()
    {
        return $this->hasMany(Penalties::class, 'bookingID', 'bookingID');
    }
    
    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'voucherID', 'voucherID');
    }

    public function staff()
    {
        // A booking belongs to one staff member (assigned agent)
        return $this->belongsTo(Staff::class, 'staffID', 'staffID');
    }

    public function feedback()
    {
        // A booking has one feedback
        return $this->hasOne(Feedback::class, 'bookingID', 'bookingID');
    }   
    public function getRemainingBalanceAttribute()
    {
        // Ensure this matches exactly how you define "Paid" in your database
        $totalPaid = $this->payments()->where('paymentStatus', 'Verified')->sum('amount');
        return max(0, $this->totalCost - $totalPaid);
    }
}