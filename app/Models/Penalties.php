<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penalties extends Model
{
    use HasFactory;

    protected $table = 'penalties';
    protected $primaryKey = 'penaltyID'; // Fix: Define Primary Key

    // Fix: Match these to your migration columns!
    protected $fillable = [
        'bookingID', 
        'penaltyFees',     // database has this
        'lateReturnHour',  // database has this
        'fuelSurcharge',   // database has this
        'mileageSurcharge',// database has this
        'penaltyStatus',   // database has this
        'status', 
        'date_imposed'
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'bookingID', 'bookingID');
    }

    // Helper to calculate total (optional but useful)
    public function getTotalAmountAttribute()
    {
        return $this->penaltyFees + $this->fuelSurcharge + $this->mileageSurcharge;
    }
}