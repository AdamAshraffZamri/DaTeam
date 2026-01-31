<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Customer Model
 * 
 * Represents a customer/user in the vehicle rental system.
 * Extends Authenticatable for Laravel authentication support.
 * 
 * Key Features:
 * - User authentication (login/registration)
 * - Profile information (personal, contact, identity, banking)
 * - Account status tracking (unverified, Confirmed, rejected, blacklisted)
 * - Document storage (avatar, student card, IC/passport, driving license)
 * - Relationship to bookings, payments, penalties, loyalty
 * 
 * Profile Fields:
 * - Personal: fullName (100), email (100), dob, faculty (100), nationality (50)
 * - Contact: phoneNo (20), homeAddress, collegeAddress
 * - Identity: ic_passport (50), stustaffID (50), driving_license_expiry, faculty
 * - Banking: bankName (100), bankAccountNo (50)
 * - Emergency: emergency_contact_name (100), emergency_contact_no (20)
 * - Documents: avatar, student_card_image, ic_passport_image, driving_license_image
 * 
 * Account Statuses:
 * - unverified: Initial state after registration
 * - Confirmed: Approved by staff, can make bookings
 * - rejected: Application rejected, reason stored in rejection_reason
 * - blacklisted: Restricted from booking, reason in blacklist_reason
 * 
 * Authentication:
 * - Uses web guard by default
 * - Password hashing via Laravel's built-in encryption
 * 
 * Relationships:
 * - hasMany: Bookings (Customer has many bookings)
 * - hasMany: Payments (Customer has many payments)
 * - hasMany: Penalties (Customer has many penalties)
 * - One: LoyaltyPoint (Customer has one loyalty record)
 * 
 * @property int $customerID Primary key
 * @property string $fullName Customer full name (max 100)
 * @property string $email Email address (max 100)
 * @property string $phoneNo Phone number (max 20)
 * @property string $ic_passport IC or passport number (max 50)
 * @property string $stustaffID Student or staff ID (max 50)
 * @property string $dob Date of birth
 * @property string $driving_license_expiry License expiry date
 * @property string $nationality Nationality (max 50)
 * @property string $faculty Faculty (max 100)
 * @property string $homeAddress Home address
 * @property string $collegeAddress College address
 * @property string $bankName Bank name (max 100)
 * @property string $bankAccountNo Bank account number (max 50)
 * @property string $emergency_contact_name Emergency contact name (max 100)
 * @property string $emergency_contact_no Emergency contact number (max 20)
 * @property string $accountStat Account status (max 50)
 * @property boolean $blacklisted Blacklist flag
 * @property string $blacklist_reason Reason for blacklist
 * @property string $rejection_reason Reason for rejection
 * @property string $previous_account_stat Previous status before blacklist
 * @property string $avatar Avatar file path
 * @property string $student_card_image Student card image path
 * @property string $ic_passport_image IC/passport image path
 * @property string $driving_license_image License image path
 */
class Customer extends Authenticatable
{
    use HasFactory, Notifiable;

    // 1. Link to the correct table
    protected $table = 'customers'; 
    
    // 2. Define the Primary Key (since it's not 'id')
    protected $primaryKey = 'customerID'; 

    // 3. Map the columns that match your Database Migration
    protected $fillable = [
    'fullName',
    'email',
    'password',
    'phoneNo',
    'stustaffID',
    'driving_license_expiry',
    'homeAddress',
    'collegeAddress',
    'accountStat',
    'blacklisted',
    'nationality',
    'dob',
    'emergency_contact_no',
    'emergency_contact_name',
    'faculty',
    'bankName',
    'bankAccountNo',
    'ic_passport',
    'rejection_reason',
    'blacklist_reason',
    'previous_account_stat',
    'avatar',
    'student_card_image',
    'ic_passport_image',
    'driving_license_image'
];
    protected $casts = [
        'dob' => 'date',
        'password' => 'hashed',
    ];
    public function bookings(): HasMany
    {
        // Link to Booking model using 'customerID' as the foreign key
        return $this->hasMany(Booking::class, 'customerID', 'customerID');
    }
    // 4. Password Override (Laravel expects 'password', your DB has 'password', so this is default behavior)
    public function getAuthPassword()
    {
        return $this->password;
    }
    
    // 5. Casts
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function unpaidPenalties()
{
    // Kita check table penalties, cari row yang status dia BUKAN 'Paid'
    return $this->hasMany(Penalties::class, 'customerID', 'customerID')
                ->where('penaltyStatus', '!=', 'Paid')->exists(); 
}
}