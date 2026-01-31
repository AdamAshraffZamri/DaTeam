<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Maintenance Model
 * 
 * Records vehicle maintenance activities and scheduling.
 * Tracks service history, costs, and vehicle availability impact.
 * 
 * Key Features:
 * - Maintenance type tracking (repair, service, inspection, cleaning)
 * - Cost tracking for budget management
 * - Staff assignment (who performed maintenance)
 * - Duration tracking (start/end times)
 * - Detailed description of work performed
 * - Reference ID for external documents (invoices, receipts)
 * 
 * Maintenance Types:
 * 1. Routine Service: Regular preventive maintenance (oil change, filters, etc.)
 *    * Frequency: Every 10,000 km or 3 months
 * 
 * 2. Repair: Fixing defects or damage
 *    * Triggered by: Inspection findings, customer reports, mechanical issues
 * 
 * 3. Inspection: Vehicle health check and safety verification
 *    * Frequency: Monthly or quarterly
 * 
 * 4. Cleaning: Deep cleaning and detailing
 *    * Frequency: Between rentals or monthly
 * 
 * 5. Part Replacement: Replacing worn or damaged parts
 *    * Examples: Tires, brakes, battery, windshield, etc.
 * 
 * Database Constraints:
 * - VehicleID: Foreign key to vehicles
 * - StaffID: Foreign key to staff performing maintenance
 * - type: max 50 characters (Service, Repair, Inspection, Cleaning)
 * - description: text field with detailed work description
 * - reference_id: External reference number (max 255)
 * - cost: decimal(10,2) - Maintenance expense
 * - date: Date of maintenance (legacy field)
 * - start_time, end_time: Precise timing for duration calculation
 * 
 * Relationships:
 * - belongsTo: Vehicle (VehicleID)
 * - belongsTo: Staff (StaffID)
 * 
 * Workflow Integration:
 * 1. Vehicle flagged for maintenance (damage, service due, etc.)
 * 2. Maintenance scheduled for specific date/time
 * 3. Staff assigned to perform maintenance
 * 4. Vehicle set to inactive during maintenance period
 * 5. Work performed and documented
 * 6. Cost recorded with receipts
 * 7. Maintenance marked complete
 * 8. Vehicle tested and returned to available status
 * 
 * Fleet Management:
 * - Maintenance history tracks vehicle reliability
 * - Cost analysis for fleet replacement decisions
 * - Service schedule optimization
 * - Staff performance tracking
 * - Budget forecasting
 * 
 * Impact on Booking:
 * - Vehicles in maintenance status: not available for booking
 * - Maintenance duration affects revenue loss
 * - Frequent maintenance indicates reliability issues
 * - Emergency repairs may cancel or delay bookings
 * 
 * @property int $MaintenanceID Primary key
 * @property int $VehicleID Foreign key to vehicles
 * @property int $StaffID Foreign key to staff
 * @property date $date Date of maintenance (legacy)
 * @property datetime $start_time Start time of maintenance
 * @property datetime $end_time End time of maintenance
 * @property string $type Type of maintenance (max 50)
 * @property string $description Work description
 * @property string $reference_id External reference number
 * @property decimal $cost Maintenance cost
 * @property timestamp $created_at Record creation date
 * @property timestamp $updated_at Last update date
 */
class Maintenance extends Model
{
    use HasFactory;

    /**
     * The primary key for the model.
     * 
     * @var string
     */
    protected $primaryKey = 'MaintenanceID';

    /**
     * The attributes that are mass assignable.
     * 
     * @var array
     */
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

    // [ADDED] Relationship to Staff
    public function staff()
    {
        return $this->belongsTo(Staff::class, 'StaffID', 'staffID');
    }
}