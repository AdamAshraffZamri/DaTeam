<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Staff Model
 * 
 * Represents a staff member with administrative or operational access.
 * Extends Authenticatable for Laravel authentication via staff guard.
 * 
 * Key Features:
 * - Staff authentication (login via staff guard)
 * - Role-based access control
 * - Email and phone contact information
 * - Active/inactive status management
 * 
 * Staff Roles:
 * - admin: Full system access (user management, settings, reporting)
 * - manager: Operations and financial oversight
 * - staff: Day-to-day operations (inspections, bookings, customer service)
 * - driver: Logistics and delivery operations
 * 
 * Database Constraints:
 * - name: max 100 characters
 * - email: max 100 characters (unique)
 * - role: max 50 characters
 * - phoneNo: max 20 characters
 * - password: hashed (min 8 characters, max 8 characters after update)
 * 
 * Authentication:
 * - Uses separate 'staff' guard for authentication
 * - Prevents collision with customer authentication
 * - Password hashing via Laravel's built-in encryption
 * - Session regeneration for security
 * 
 * Operational Access:
 * - Customer management (approve, reject, blacklist)
 * - Fleet management (add, edit, deactivate vehicles)
 * - Booking verification and processing
 * - Inspection coordination
 * - Payment processing
 * - Penalty management
 * - Reporting and analytics
 * 
 * Relationships:
 * - hasMany: Inspections (inspections performed by staff)
 * - hasMany: Bookings (bookings verified by staff)
 * 
 * @property int $staffID Primary key
 * @property string $name Staff full name (max 100)
 * @property string $role Staff role (max 50)
 * @property string $email Email address (max 100)
 * @property string $phoneNo Phone number (max 20)
 * @property string $password Hashed password
 * @property boolean $active Active status flag
 * @property timestamp $created_at Account creation date
 * @property timestamp $updated_at Last update date
 */
class Staff extends Authenticatable
{
    use Notifiable;

    /**
     * The table associated with the model.
     * Connects to 'staff' table (matches ERD naming).
     * 
     * @var string
     */
    protected $table = 'staff';

    /**
     * The primary key for the model.
     * Database uses 'staffID' instead of default 'id'.
     * 
     * @var string
     */
    protected $primaryKey = 'staffID';

    /**
     * The attributes that are mass assignable.
     * These fields can be populated via fill() or create() methods.
     * 
     * @var array
     */
    protected $fillable = [
        'name', 'role', 'email', 'phoneNo', 'password', 'active'
    ];

    protected $hidden = [
        'password',
    ];
}