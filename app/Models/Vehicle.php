<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $primaryKey = 'vehicle_id'; // Match your DB schema
    
    protected $fillable = [
        'plate_no', 'model', 'type', 'price_hour', 
        'availability', 'mileage', 'fuel_pickup', 'base_deposit'
    ];

    // Helper to get daily price (Price per hour * 24)
    public function getPricePerDayAttribute()
    {
        return $this->price_hour * 24;
    }

    // Helper to assign images based on model name
    public function getImageAttribute()
    {
        if (str_contains($this->model, 'Axia')) {
            return 'https://perodua.com.my/assets/images/cars/axia/colors/white.png';
        } elseif (str_contains($this->model, 'Myvi')) {
            return 'https://perodua.com.my/assets/images/cars/myvi/colors/red.png';
        }
        return 'https://perodua.com.my/assets/images/cars/bezza/colors/brown.png';
    }
}