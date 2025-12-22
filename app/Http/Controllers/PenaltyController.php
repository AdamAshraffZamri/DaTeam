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
        $request->validate([
            'bookingID' => 'required|exists:bookings,id',
            'lateReturnHour' => 'required|integer',
            'penaltyFees' => 'required|numeric',
            'penaltyStatus' => 'required|string',
            'fuelSurcharge' => 'required|numeric',
            'mileageSurcharge' => 'required|numeric',
        ]);
        Penalty::create($request->all());
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
