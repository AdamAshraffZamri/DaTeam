<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Maintenance extends Model
{
    use HasFactory;

    protected $primaryKey = 'MaintenanceID';

    protected $fillable = [
        'VehicleID', 
        'StaffID', 
        'date', 
        'description', 
        'cost'
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'VehicleID', 'VehicleID');
    }
}