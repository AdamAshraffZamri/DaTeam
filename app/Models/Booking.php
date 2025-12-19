<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $table = 'bookings';
    protected $primaryKey = 'bookingID'; // Matches your ERD

    // THIS IS THE MISSING PART CAUSING YOUR ERROR
    protected $fillable = [
        'customerID', 
        'vehicleID', 
        'staffID', 
        'bookingDate', 
        'originalDate', 
        'bookingTime',
        'returnDate', 
        'returnTime', 
        'actualReturnDate', 
        'actualReturnTime',
        'pickupLocation', 
        'returnLocation',
        'totalCost', 
        'aggreementDate', 
        'aggreementLink', 
        'bookingStatus', 
        'bookingType'
    ];

    // Relationships
    public function customer() {
        return $this->belongsTo(Customer::class, 'customerID', 'customerID');
    }

    public function vehicle() {
        return $this->belongsTo(Vehicle::class, 'vehicleID', 'VehicleID'); 
    }

    public function payment() {
        return $this->hasOne(Payment::class, 'bookingID', 'bookingID');
    }
}