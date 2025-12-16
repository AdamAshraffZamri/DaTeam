<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vehicle;
use App\Models\Booking;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BookingController extends Controller
{
    // 1. Home Page / Search Form
    public function create()
    {
        return view('bookings.create');
    }

    // 2. Search Results
    public function search(Request $request)
    {
        // Simple search: Get all vehicles (You can add filtering later)
        $vehicles = Vehicle::where('availability', true)->get();
        return view('bookings.results', compact('vehicles'));
    }

    // 3. Show Details (Capture Data & Calculate)
    public function show(Request $request, $id)
    {
        $vehicle = Vehicle::findOrFail($id);

        // Capture Dates (Default: Today & Tomorrow)
        $pickupDate = $request->query('pickup_date', now()->format('Y-m-d'));
        $returnDate = $request->query('return_date', now()->addDay()->format('Y-m-d'));
        
        // Capture Locations (Default: Student Mall, UTM)
        $pickupLoc = $request->query('pickup_location') ?: 'Student Mall, UTM';
        $returnLoc = $request->query('return_location') ?: 'Student Mall, UTM';

        // Calculation
        $start = Carbon::parse($pickupDate);
        $end = Carbon::parse($returnDate);
        $days = $start->diffInDays($end) ?: 1;
        $total = ($vehicle->price_per_day * $days) + $vehicle->base_deposit;

        return view('bookings.show', compact('vehicle', 'total', 'days', 'pickupDate', 'returnDate', 'pickupLoc', 'returnLoc'));
    }

    // 4. Payment Page
    public function payment(Request $request, $id)
    {
        $vehicle = Vehicle::findOrFail($id);
        
        $pickupDate = $request->query('pickup_date');
        $returnDate = $request->query('return_date');
        // Ensure defaults persist
        $pickupLoc = $request->query('pickup_location') ?: 'Student Mall, UTM';
        $returnLoc = $request->query('return_location') ?: 'Student Mall, UTM';
        
        $days = $request->query('days');
        $total = $request->query('total');

        return view('bookings.payment', compact('vehicle', 'total', 'days', 'pickupDate', 'returnDate', 'pickupLoc', 'returnLoc'));
    }

    // 5. Submit to Database
    public function submitPayment(Request $request, $id)
    {
        // Validate or fallback
        $pickupLoc = $request->query('pickup_location') ?: 'Student Mall, UTM';
        $returnLoc = $request->query('return_location') ?: 'Student Mall, UTM';

        Booking::create([
            'customer_id' => Auth::id(),
            'vehicle_id' => $id,
            'booking_date' => now(),
            'start_date' => $request->query('pickup_date'),
            'end_date' => $request->query('return_date'),
            'pickup_location' => $pickupLoc, // Saved!
            'return_location' => $returnLoc, // Saved!
            'total_cost' => $request->query('total'),
            'booking_status' => 'Submitted',
            'booking_type' => 'Standard'
        ]);

        return redirect()->route('book.index')->with('show_thank_you', true);
    }

    // 6. My Bookings List
    public function index()
    {
        $bookings = Booking::where('customer_id', Auth::id())->with('vehicle')->orderBy('created_at', 'desc')->get();
        return view('bookings.index', compact('bookings'));
    }
}