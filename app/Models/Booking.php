<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $primaryKey = 'booking_id';

    protected $fillable = [
        'customer_id',
        'vehicle_id',
        'booking_date',
        'start_date',
        'end_date',
        'pickup_location',
        'return_location',
        'total_cost',
        'booking_status',
        'booking_type',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
}