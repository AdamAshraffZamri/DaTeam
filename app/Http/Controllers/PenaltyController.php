<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PenaltyController extends Controller
{
    /**
     * Display a listing of the resource.
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
