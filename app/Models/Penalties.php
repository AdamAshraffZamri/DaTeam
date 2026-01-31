<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Penalties Model
 * 
 * Records charges and fees imposed on customers for rental violations.
 * Tracks late returns, fuel surcharges, mileage overages, and damage penalties.
 * 
 * Key Features:
 * - Penalty amount tracking and status management
 * - Multiple charge types (late fees, fuel, mileage, damage)
 * - Reason documentation for transparency
 * - Payment proof storage (receipts, documentation)
 * - Penalty status tracking (pending, paid, disputed, resolved, waived)
 * - Automatic calculation from inspection data
 * 
 * Penalty Types:
 * 1. Late Return Fee: Hourly charge for returning vehicle late
 *    * Calculated: Late hours × hourly rate
 *    * Example: 3 hours late × RM20/hr = RM60
 * 
 * 2. Fuel Surcharge: Cost to refuel vehicle
 *    * Calculated: (Current fuel level - Expected) × RM per unit
 *    * Example: From Full to 1/4 tank = RM40
 * 
 * 3. Mileage Surcharge: Cost for exceeding mileage limit
 *    * Calculated: (Final mileage - Expected) × RM per km
 *    * Example: 200km excess × RM0.10 = RM20
 * 
 * 4. Damage Penalty: Cost to repair vehicle damage
 *    * Based on inspection damage assessment
 *    * Minimum charge: RM50, Maximum: security deposit
 * 
 * 5. Cleaning Surcharge: Cost for vehicle cleaning
 *    * Fixed charge: RM30-100 depending on condition
 * 
 * Database Constraints:
 * - amount: decimal(10,2) - Total penalty amount
 * - status: max 50 characters (pending, paid, disputed, resolved, waived)
 * - reason: text field with detailed penalty explanation
 * - penaltyFees: decimal(10,2) - Base penalty amount
 * - lateReturnHour: integer - Hours late
 * - fuelSurcharge: decimal(10,2) - Fuel charge
 * - mileageSurcharge: decimal(10,2) - Mileage charge
 * - penaltyStatus: max 50 characters (alternative status field)
 * - payment_proof: File path for payment evidence
 * - date_imposed: timestamp when penalty was created
 * - paid_at: timestamp when penalty was paid
 * 
 * Penalty Statuses:
 * - pending: Penalty awaiting payment
 * - paid: Penalty fully paid by customer
 * - disputed: Customer challenged the penalty
 * - resolved: Dispute settled
 * - waived: Staff forgave the penalty
 * 
 * Relationships:
 * - belongsTo: Booking (bookingID)
 * - belongsTo: Customer (customerID, implicit via Booking)
 * 
 * Payment Integration:
 * - Unpaid penalties block new bookings
 * - Penalties settled via payment system
 * - Paid penalties enable account normalization
 * - Dispute resolution by management
 * 
 * Collection Workflow:
 * 1. Inspection detects violation
 * 2. Penalty created with reason and amount
 * 3. Penalty status: pending
 * 4. Customer notified via email
 * 5. Payment method provided to customer
 * 6. Payment submitted with proof
 * 7. Staff verifies payment
 * 8. Penalty status: paid
 * 9. Account unfrozen for new bookings
 * 
 * @property int $penaltyID Primary key
 * @property int $customerID Customer ID (optional, can be retrieved via booking)
 * @property int $bookingID Foreign key to bookings
 * @property decimal $penaltyFees Base penalty fee amount
 * @property int $lateReturnHour Hours late (0 if not late fee)
 * @property decimal $fuelSurcharge Fuel charge amount
 * @property decimal $mileageSurcharge Mileage charge amount
 * @property string $penaltyStatus Status (max 50)
 * @property string $status Alternative status field (max 50)
 * @property decimal $amount Total penalty amount
 * @property string $reason Detailed reason text
 * @property string $payment_proof Payment proof/receipt path
 * @property timestamp $date_imposed When penalty was created
 * @property timestamp $paid_at When penalty was paid
 */
class Penalties extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'penalties';

    /**
     * The primary key for the model.
     * Database uses 'penaltyID' instead of default 'id'.
     * 
     * @var string
     */
    protected $primaryKey = 'penaltyID';

    /**
     * The attributes that are mass assignable.
     * Includes all fields that can be populated via fill() or create().
     * Maps database column names to model attributes.
     * 
     * @var array
     */
    protected $fillable = [
        'customerID',
        'bookingID', 
        'penaltyFees',     // database has this
        'lateReturnHour',  // database has this
        'fuelSurcharge',   // database has this
        'mileageSurcharge',// database has this
        'penaltyStatus',   // database has this
        'status', 
        'date_imposed',
        'reason',
        'amount',
        'payment_proof', // <--- TAMBAH INI
        'paid_at'
    ];


    
    // Tambah relationship ke User melalui Booking
    public function user() {
        return $this->hasOneThrough(User::class, Booking::class, 'bookingID', 'id', 'bookingID', 'user_id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'bookingID', 'bookingID');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customerID', 'customerID');
    }

    // Helper to calculate total (optional but useful)
    public function getTotalAmountAttribute()
    {
        return $this->penaltyFees + $this->fuelSurcharge + $this->mileageSurcharge;
    }
}