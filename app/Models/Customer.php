<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; // Must extend Authenticatable
use Illuminate\Notifications\Notifiable;

class Customer extends Authenticatable
{
    use HasFactory, Notifiable;

    // 1. Link to the correct table
    protected $table = 'customers'; 
    
    // 2. Define the Primary Key (since it's not 'id')
    protected $primaryKey = 'customerID'; 

    // 3. Map the columns that match your Database Migration
    protected $fillable = [
        'fullName',       // was 'name'
        'email',
        'password',
        'phoneNo',        // was 'phone'
        'stustaffID',     // was 'student_staff_id'
        'drivingNo',      // was 'driving_license_no'
        'homeAddress',
        'collegeAddress',
        'accountStat',
        'blacklisted',
        'nationality',
        'dob',
        'emergencyContactNo',
        'faculty',
    ];

    protected $casts = [
        'dob' => 'date', // This ensures Carbon handles the date format correctly
        'password' => 'hashed',
    ];

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