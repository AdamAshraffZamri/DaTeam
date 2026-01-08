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
        'customerID',
        'bookingID', 
        'penaltyFees',     // database has this
        'lateReturnHour',  // database has this
        'fuelSurcharge',   // database has this
        'mileageSurcharge',// database has this
        'penaltyStatus',   // database has this
        'status', 
        'date_imposed',
        'reason',
        'amount',
        'payment_proof', // <--- TAMBAH INI
        'paid_at'
    ];


    
    // Tambah relationship ke User melalui Booking
    public function user() {
        return $this->hasOneThrough(User::class, Booking::class, 'bookingID', 'id', 'bookingID', 'user_id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'bookingID', 'bookingID');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customerID', 'customerID');
    }

    // Helper to calculate total (optional but useful)
    public function getTotalAmountAttribute()
    {
        return $this->penaltyFees + $this->fuelSurcharge + $this->mileageSurcharge;
    }
}