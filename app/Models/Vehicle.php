<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Vehicle Model
 * 
 * Represents a rental vehicle in the fleet.
 * Manages vehicle specifications, availability, pricing, and relationships to bookings.
 * 
 * Key Features:
 * - Vehicle specifications (brand, model, year, vehicle type)
 * - Availability tracking (active, booked, maintenance, inactive)
 * - Tiered pricing system (hourly rates for 1h, 3h, 12h, 24h rentals)
 * - Security deposit management (baseDepo)
 * - Document storage (photos, insurance, permits)
 * - Maintenance tracking and scheduling
 * - Blocked dates for maintenance/unavailability
 * - Owner information for co-ownership scenarios
 * 
 * Vehicle Categories:
 * - car: Four-wheel vehicles
 * - motorcycle: Two-wheel vehicles
 * - van: Commercial vehicles
 * 
 * Vehicle Types:
 * - compact, sedan, SUV, van (for cars)
 * - scooter, motorcycle, sports bike (for motorcycles)
 * 
 * Database Constraints:
 * - plateNo: max 20 characters (license plate, unique)
 * - brand: max 100 characters (manufacturer)
 * - model: max 100 characters (model name)
 * - vehicle_category: max 50 characters (car, motorcycle)
 * - type: max 50 characters (sedan, compact, scooter)
 * - color: max 50 characters
 * - fuelType: max 50 characters (petrol, diesel, electric)
 * - image, road_tax_image, grant_image, insurance_image: file paths (up to 255)
 * - hourly_rates: JSON field {1: 15.00, 3: 40.00, 12: 80.00, 24: 120.00}
 * - baseDepo: decimal(10,2) - security deposit
 * - priceHour: decimal(10,2) - base hourly rate
 * - blocked_dates: JSON array of date strings when vehicle unavailable
 * 
 * Relationships:
 * - hasMany: Bookings (vehicle has many bookings)
 * - hasMany: Maintenances (vehicle maintenance history)
 * 
 * Availability States:
 * - true: Available for booking
 * - false: Inactive/not available
 * 
 * Pricing Example:
 * hourly_rates: {
 *   "1": 15.00,    // 1 hour rate
 *   "3": 40.00,    // 3 hour rate (better per-hour)
 *   "12": 80.00,   // 12 hour rate (overnight)
 *   "24": 120.00   // 24 hour rate (full day)
 * }
 * 
 * @property int $VehicleID Primary key
 * @property string $plateNo License plate number (max 20)
 * @property string $brand Vehicle brand/manufacturer (max 100)
 * @property string $model Vehicle model (max 100)
 * @property string $vehicle_category Category (max 50)
 * @property string $type Vehicle type (max 50)
 * @property string $color Color (max 50)
 * @property int $year Manufacturing year
 * @property int $mileage Current odometer reading
 * @property string $fuelType Fuel type (max 50)
 * @property decimal $baseDepo Security deposit amount
 * @property boolean $availability Availability status
 * @property decimal $priceHour Base hourly rate
 * @property array $hourly_rates Tiered pricing rates (JSON)
 * @property string $image Vehicle photo path
 * @property string $owner_name Owner name
 * @property string $owner_phone Owner phone
 * @property string $owner_nric Owner NRIC/company reg
 * @property string $road_tax_image Road tax image path
 * @property string $grant_image Grant/ownership image path
 * @property string $insurance_image Insurance document path
 * @property array $blocked_dates Unavailable dates (JSON)
 */
class Vehicle extends Model
{
    use HasFactory;

    /**
     * Define the primary key if it's not 'id'.
     * Based on your system context, it is 'VehicleID'.
     * 
     * @var string
     */
    protected $primaryKey = 'VehicleID';

    /**
     * The attributes that are mass assignable.
     * Includes all fields required by the HASTA fleet list.
     * 
     * @var array
     */
    protected $fillable = [
        'plateNo',          // License plate number
        'brand',            // Vehicle brand
        'model',            // Vehicle model
        'vehicle_category', // Car or Bike
        'type',             // Vehicle type (e.g., sedan, scooter)
        'color',            // Vehicle color
        'year',             // Manufacturing year
        'mileage',          // Current mileage
        'fuelType',         // Fuel type (petrol, diesel, electric)
        'baseDepo',         // Security deposit amount
        'availability',     // Availability status
        'priceHour',        // The base hourly rate (usually the 1H tier)
        'hourly_rates',     // JSON field for tiered pricing (1h, 3h, 24h, etc.)
        'image',            // Path to the vehicle photo
        'owner_name',       // Owner name
        'owner_phone',      // Owner phone number
        'owner_nric',       // NRIC or Company Reg Number 
        'road_tax_image',   // Path to road tax image
        'grant_image',      // Path to grant/ownership image
        'insurance_image',  // Path to insurance document image
        'blocked_dates',    // JSON array of blocked dates
    ];

    /**
     * The attributes that should be cast.
     * Ensures hourly_rates is handled as an array, not a string.
     */
    protected $casts = [
        'hourly_rates' => 'array',
        'availability' => 'boolean',
        // 'date_register_jpj' => 'date',
        'blocked_dates' => 'array',
        'baseDepo' => 'decimal:2',
        'priceHour' => 'decimal:2',
    ];

    /**
     * Helper: Get the full URL for the vehicle image.
     * Returns a placeholder if no image exists.
     */
    public function getImageUrlAttribute()
    {
        if ($this->image && Storage::disk('public')->exists($this->image)) {
            return asset('storage/' . $this->image);
        }

        // Return a category-based placeholder
        return $this->vehicle_category === 'bike' 
            ? asset('images/placeholder-bike.png') 
            : asset('images/placeholder-car.png');
    }

    /**
     * Helper: Get a specific hourly rate tier.
     * Example: $vehicle->getRateFor(24) returns the daily rate.
     */
    public function getRateFor($hours)
    {
        return $this->hourly_rates[$hours] ?? $this->priceHour;
    }

    // Scope: Filter available vehicles
    public function scopeAvailable($query)
    {
        return $query->where('availability', true);
    }

    // Relationships
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'vehicleID', 'VehicleID');
    }

    // Maintenance relationship
    public function maintenances()
    {
        return $this->hasMany(Maintenance::class, 'VehicleID', 'VehicleID');
    }
}