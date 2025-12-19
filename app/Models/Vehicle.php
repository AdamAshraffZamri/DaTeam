<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    // 1. Define Table Name (Optional if matches plural, but good for safety)
    protected $table = 'vehicles';

    // 2. IMPORTANT: Define Custom Primary Key
    protected $primaryKey = 'VehicleID'; // Case sensitive matching your DB

    // 3. Define Fillable Columns (Matching your Migration)
    protected $fillable = [
        'plateNo', 
        'model', 
        'type', 
        'priceHour', 
        'availability', 
        'mileage', 
        'fuelType', 
        'baseDepo',
        'image' // Add this if you plan to use images later
    ];

    // 4. Relationships (Optional, but good to have ready)
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'vehicleID', 'VehicleID');
    }
}