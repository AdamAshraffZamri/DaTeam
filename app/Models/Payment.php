<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Payment Model
 * 
 * Represents rental payment transactions for bookings.
 * Tracks deposits, balances, and installment payments.
 * 
 * Key Features:
 * - Payment amount tracking (deposit, balance, total)
 * - Payment method recording (credit card, online banking, etc.)
 * - Payment status management (pending, completed, failed, refunded)
 * - Deposit tracking and refund dates
 * - Installment payment support
 * - Payment proof/receipt storage
 * 
 * Payment Statuses:
 * - pending: Payment awaiting processing
 * - completed: Payment successfully received
 * - failed: Payment failed or rejected
 * - refunded: Payment refunded to customer
 * 
 * Deposit Statuses:
 * - pending: Deposit awaiting refund
 * - refunded: Deposit returned to customer
 * - forfeited: Deposit claimed for damages/penalties
 * 
 * Database Constraints:
 * - amount: decimal(10,2) - Total payment amount
 * - depoAmount: decimal(10,2) - Security deposit amount
 * - paymentStatus: max 50 characters
 * - depoStatus: max 50 characters
 * - paymentMethod: max 50 characters
 * - installmentDetails: File path for payment proof (up to 255)
 * - transactionDate: Timestamp of payment
 * 
 * Relationships:
 * - belongsTo: Booking (bookingID)
 * 
 * Payment Flow:
 * 1. Booking created → Payment record created with pending status
 * 2. Deposit collected → depoAmount recorded, depoStatus set to "pending"
 * 3. Full payment completed → amount updated, paymentStatus set to "completed"
 * 4. Rental completed → Inspect for damage
 * 5. Refund → depoStatus set to "refunded", refund date recorded
 * 6. Penalties → depoAmount may be forfeited, depoStatus set to "forfeited"
 * 
 * @property int $paymentID Primary key
 * @property int $bookingID Foreign key to bookings
 * @property decimal $amount Total payment amount
 * @property decimal $depoAmount Security deposit amount
 * @property timestamp $transactionDate Date of payment
 * @property string $paymentMethod Payment method (max 50)
 * @property string $paymentStatus Payment status (max 50)
 * @property string $depoStatus Deposit status (max 50)
 * @property date $depoRequestDate Date refund was requested
 * @property date $depoRefundedDate Date deposit was refunded
 * @property string $installmentDetails Payment proof file path
 * @property boolean $isInstallment Whether payment is installment
 */
class Payment extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'payments';

    /**
     * The primary key for the model.
     * 
     * @var string
     */
    protected $primaryKey = 'paymentID';

    /**
     * The attributes that are mass assignable.
     * 
     * @var array
     */
    protected $fillable = [
        'bookingID', 
        'amount', 
        'depoAmount', 
        'transactionDate',
        'paymentMethod', 
        'paymentStatus', 
        'depoStatus',
        'depoRequestDate', 
        'depoRefundedDate',
        'installmentDetails', // This stores your proof image path
        'isInstallment'
    ];
    public function booking()
    {
        return $this->belongsTo(Booking::class, 'bookingID', 'bookingID');
    }
}