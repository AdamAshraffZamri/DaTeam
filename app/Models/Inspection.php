<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inspection extends Model
{
    use HasFactory;

    protected $primaryKey = 'inspectionID';
    
    protected $fillable = [
        'bookingID', 'staffID', 'inspectionType', 'inspectionDate',
        'damageCosts', 'photosBefore', 'photosAfter',
        'fuelBefore', 'fuelAfter', 'mileageBefore', 'mileageAfter'
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'bookingID', 'bookingID');
    }
}