<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; // Must extend Authenticatable
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
}