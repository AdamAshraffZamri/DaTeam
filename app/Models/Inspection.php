<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Inspection Model
 * 
 * Records vehicle condition inspections before pickup and after return.
 * Captures evidence for damage claims and provides legal documentation.
 * 
 * Key Features:
 * - Inspection type tracking (Pickup, Return)
 * - Vehicle condition documentation (photos before/after)
 * - Fuel level recording (for surcharge calculation)
 * - Mileage recording (for overage charges)
 * - Damage cost estimation
 * - Digital staff signature (staff name and verification)
 * - Timestamp of inspection
 * 
 * Inspection Types:
 * - Pickup: Pre-rental vehicle condition check
 *   * Verifies vehicle is in stated condition
 *   * Records initial mileage and fuel
 *   * Documents pre-existing damage
 *   * Staff and customer agreement required
 * 
 * - Return: Post-rental vehicle condition check
 *   * Documents vehicle condition upon return
 *   * Records final mileage and fuel level
 *   * Calculates mileage overage and fuel surcharge
 *   * Identifies new damage for penalty calculation
 *   * Staff inspection signature only
 * 
 * Database Constraints:
 * - inspectionType: max 50 characters (Pickup, Return)
 * - fuelBefore/fuelAfter: max 50 characters (Full, 3/4, 1/2, 1/4, Empty, or percentage)
 * - mileageBefore/mileageAfter: integer values
 * - damageCosts: decimal(10,2)
 * - photosBefore/photosAfter: JSON array of file paths
 * - inspectionDate: timestamp
 * 
 * Relationships:
 * - belongsTo: Booking (bookingID)
 * - belongsTo: Staff (staffID, implicitly via inspection history)
 * 
 * Workflow Integration:
 * 1. Booking Confirmed → Pickup Inspection scheduled
 * 2. Pickup Inspection → Vehicle inspected, mileage/fuel recorded
 * 3. Booking status: Confirmed → Active
 * 4. Booking returns → Return Inspection scheduled
 * 5. Return Inspection → Final mileage/fuel checked, damage assessed
 * 6. Penalties calculated from inspection data
 * 7. Booking status: Active → Completed
 * 
 * Penalty Triggers from Inspection:
 * - Fuel surcharge: If fuelAfter < fuelBefore (customer filled)
 * - Mileage surcharge: If mileageAfter > expectedMileage
 * - Damage penalty: If damageCosts > 0 (staff estimated)
 * - Late return fee: If returnDate exceeded
 * 
 * @property int $inspectionID Primary key
 * @property int $bookingID Foreign key to bookings
 * @property int $staffID Foreign key to staff performing inspection
 * @property string $inspectionType Inspection type (max 50)
 * @property timestamp $inspectionDate Date/time of inspection
 * @property string $fuelBefore Fuel level before rental (max 50)
 * @property string $fuelAfter Fuel level after return (max 50)
 * @property int $mileageBefore Initial odometer reading
 * @property int $mileageAfter Final odometer reading
 * @property decimal $damageCosts Estimated damage cost
 * @property array $photosBefore Photos before rental (JSON file paths)
 * @property array $photosAfter Photos after return (JSON file paths)
 */
class Inspection extends Model
{
    use HasFactory;

    /**
     * The primary key for the model.
     * 
     * @var string
     */
    protected $primaryKey = 'inspectionID';
    
    /**
     * The attributes that are mass assignable.
     * 
     * @var array
     */
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