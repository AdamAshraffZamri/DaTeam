<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $primaryKey = 'bookingID'; // Important since you aren't using 'id'
    
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

    // 1. THIS WAS MISSING
    public function payments()
    {
        // A booking can have multiple payments (Deposit + Final Balance)
        return $this->hasMany(Payment::class, 'bookingID', 'bookingID');
    }

    // 2. CHECK THIS TOO (It is also in your controller)
    public function penalties()
    {
        return $this->hasMany(Penalties::class, 'bookingID', 'bookingID');
    }
    
    // Optional: If you use the Voucher relationship
    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'voucherID', 'voucherID');
    }
}