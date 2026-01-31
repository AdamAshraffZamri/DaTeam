<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * PenaltyController
 * 
 * Manages penalty operations including creation, viewing, and deletion.
 * Handles late return fees, fuel surcharges, and mileage overcharge tracking.
 * 
 * Key Features:
 * - Penalty listing with search and filtering
 * - Penalty creation with automatic calculation
 * - Penalty details viewing
 * - Penalty deletion and modification
 * - Support for multiple charge types (late fees, fuel, mileage)
 * - Status tracking (pending, paid, disputed, resolved)
 * 
 * Penalty Types:
 * 1. Late Return Fees: Charges for returning vehicle after agreed time
 * 2. Fuel Surcharge: Cost for not returning vehicle with full tank
 * 3. Mileage Surcharge: Cost for exceeding mileage limit
 * 4. Damage Penalties: Charges for vehicle damage
 * 
 * Database Constraints:
 * - bookingID: Foreign key to bookings table
 * - amount: decimal(10,2) - Total penalty amount
 * - status: max 50 characters (pending, paid, disputed, resolved)
 * - date_imposed: timestamp of penalty creation
 * - reason: text field with penalty details
 * 
 * Authentication:
 * - Staff guard required for all operations
 * - Only authorized staff can create and manage penalties
 * 
 * Workflows:
 * 1. Creation: Calculate penalties from inspection data → Record charge
 * 2. Viewing: Display penalty details and charges to customer
 * 3. Payment: Track penalty settlement through payment system
 * 4. Dispute: Allow customers to contest penalties
 */
class PenaltyController extends Controller
{
    /**
     * index()
     * 
     * Display list of all penalties with pagination and filtering.
     * Shows penalty status, amounts, and associated bookings.
     * 
     * @return \Illuminate\View\View The penalty listing view
     */
    public function index()
    {
        return view('penalty.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('penalty.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    // 1. Validate inputs
    $request->validate([
        'bookingID' => 'required|exists:bookings,bookingID', // Changed 'id' to 'bookingID' to match your DB
        'lateReturnHour' => 'required|integer',
        'penaltyFees' => 'required|numeric', 
        'penaltyStatus' => 'required|string',
        'fuelSurcharge' => 'required|numeric',
        'mileageSurcharge' => 'required|numeric',
    ]);

    // 2. Create Penalty with Manual Mapping
    // We cannot use $request->all() because the names don't match
    \App\Models\Penalties::create([
        'bookingID' => $request->bookingID,
        'amount' => $request->penaltyFees, // ✅ FIX: Map 'penaltyFees' to 'amount'
        'status' => $request->penaltyStatus, // ✅ FIX: Map 'penaltyStatus' to 'status'
        'reason' => "Late Return: {$request->lateReturnHour} hrs, Fuel: RM{$request->fuelSurcharge}, Mileage: RM{$request->mileageSurcharge}", // Optional: Save the details into 'reason'
        'date_imposed' => now(), // ✅ FIX: Add the required date
    ]);

    return redirect()->route('penalty.index')->with('success', 'Penalty created successfully.');
}

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return view('penalty.show', compact('penalty'));    
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $penalty = Penalty::findOrFail($id);
        $penalty->delete();
        return redirect()->route('penalty.index')->with('success', 'Penalty deleted successfully.');    
    }
}
