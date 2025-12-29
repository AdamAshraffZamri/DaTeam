<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Vehicle extends Model
{
    use HasFactory;

    /**
     * Define the primary key if it's not 'id'.
     * Based on your system context, it is 'VehicleID'.
     */
    protected $primaryKey = 'VehicleID';

    /**
     * The attributes that are mass assignable.
     * Includes all fields required by the HASTA fleet list.
     */
    protected $fillable = [
        'plateNo', 
        'brand', 
        'model', 
        'vehicle_category', // 'car' or 'bike' [cite: 672, 723]
        'type',             // compact, sedan, scooter, etc. [cite: 723, 1102]
        'color', 
        'year', 
        'mileage', 
        'fuelType',
        'baseDepo', 
        'availability', 
        'priceHour',        // The base hourly rate (usually the 1H tier)
        'hourly_rates',     // JSON field for tiered pricing (1h, 3h, 24h, etc.)
        'image',            // Path to the vehicle photo
        'owner_name',       // [cite: 748, 852, 1014]
        'owner_phone', 
        'owner_nric',       // NRIC or Company Reg Number 
        // 'operating_state',  // Defaulted to 'Johor' per documents [cite: 764, 809]
        // 'date_register_jpj' // JPJ Registration Date [cite: 754, 808, 1026]
    ];

    /**
     * The attributes that should be cast.
     * Ensures hourly_rates is handled as an array, not a string.
     */
    protected $casts = [
        'hourly_rates' => 'array',
        'availability' => 'boolean',
        // 'date_register_jpj' => 'date',
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

    /**
     * Scope: Filter only available vehicles.
     */
    public function scopeAvailable($query)
    {
        return $query->where('availability', true);
    }
}