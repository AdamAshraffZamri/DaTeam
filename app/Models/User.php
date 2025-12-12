<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        
        // --- Added HASTA Custom Fields ---
        'phone',
        'student_staff_id',
        'ic_passport',
        'dob',
        'home_address',
        'college_address',
        'driving_license_no',
        'emergency_contact_no',
        'nationality',
        'role',              // 'customer' or 'staff'
        'security_question',
        'security_answer',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'security_answer', // Good practice to hide this too
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            // Ensure DOB is treated as a date object if you access it in code
            'dob' => 'date', 
        ];
    }
}