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
        'date', // Keeping for legacy/backup
        'start_time', // New
        'end_time',   // New
        'type',       // New
        'description', 
        'reference_id', // New
        'cost'
    ];
    
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'date' => 'date',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'VehicleID', 'VehicleID');
    }
}