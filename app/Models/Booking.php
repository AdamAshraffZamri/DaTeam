<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $primaryKey = 'bookingID'; 
    
    protected $fillable = [
        'customerID', 'vehicleID', 'staffID', 'voucherID',
        'bookingDate', 'originalDate', 'bookingTime',
        'returnDate', 'returnTime', 'actualReturnDate', 'actualReturnTime',
        'pickupLocation', 'returnLocation', 'totalCost',
        'aggreementDate', 'aggreementLink',
        'bookingStatus', 'bookingType'
    ];

    // --- RELATIONSHIPS ---

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customerID', 'customerID');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicleID', 'VehicleID');
    }

    // 1. ALLOWS ACCESSING $booking->payment (Single Latest Payment)
    public function payment()
    {
        return $this->hasOne(Payment::class, 'bookingID', 'bookingID')->latest();
    }

    // 2. KEEPS HISTORY OF ALL PAYMENTS
    public function payments()
    {
        return $this->hasMany(Payment::class, 'bookingID', 'bookingID');
    }

    // 3. THIS WAS MISSING -> FIXES THE ERROR
    public function inspections()
    {
        return $this->hasMany(Inspection::class, 'bookingID', 'bookingID');
    }

    public function penalties()
    {
        return $this->hasMany(Penalties::class, 'bookingID', 'bookingID');
    }
    
    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'voucherID', 'voucherID');
    }

    public function staff()
    {
        // A booking belongs to one staff member (assigned agent)
        return $this->belongsTo(Staff::class, 'staffID', 'staffID');
    }

    public function feedback()
    {
        // A booking has one feedback
        return $this->hasOne(Feedback::class, 'bookingID', 'bookingID');
    }   
}