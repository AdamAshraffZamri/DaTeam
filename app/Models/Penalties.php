<?php

namespace App\Models;

// 1. THIS LINE IS CRITICAL - It fixes the "Trait not found" error
use Illuminate\Database\Eloquent\Factories\HasFactory; 
use Illuminate\Database\Eloquent\Model;

class Penalties extends Model
{
    use HasFactory;

    protected $table = 'penalties';

    // 2. These fields are required for the Finance page logic to work
    protected $fillable = [
        'bookingID', 
        'amount', 
        'reason', 
        'status', 
        'date_imposed'
    ];

    // 3. Relationship to Booking (Required for "Outstanding" list)
    public function booking()
    {
        return $this->belongsTo(Booking::class, 'bookingID', 'bookingID');
    }
}