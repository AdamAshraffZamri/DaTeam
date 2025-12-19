<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BookingController extends Controller
{
    // --- 1. MY BOOKINGS PAGE (Fixes your Error) ---
    public function index()
    {
        // Get bookings for the logged-in customer
        $bookings = Booking::where('customerID', Auth::id())
                           ->with(['vehicle', 'payment']) // Load vehicle and payment info
                           ->orderBy('bookingDate', 'desc')
                           ->get();

        // Make sure this view exists at resources/views/bookings/index.blade.php
        return view('bookings.index', compact('bookings'));
    }

    // --- 2. SEARCH FORM (Step 1) ---
    public function create()
    {
        return view('bookings.create'); 
    }

    // --- 3. SEARCH RESULTS (Step 2) ---
    public function search(Request $request)
    {
        // Simple search logic
        $vehicles = Vehicle::where('availability', true)->get();
        
        return view('bookings.search_results', compact('vehicles'));
    }

    // --- 4. DETAILS PAGE (Step 3) ---
    public function show(Request $request, $id)
    {
        $vehicle = Vehicle::findOrFail($id);
        
        // Calculate days if dates are provided
        $pickup = \Carbon\Carbon::parse($request->query('pickup_date', now()));
        $dropoff = \Carbon\Carbon::parse($request->query('return_date', now()->addDay()));
        $days = $pickup->diffInDays($dropoff) ?: 1;
        
        return view('bookings.show', compact('vehicle', 'days'));
    }

    // --- 5. PAYMENT PAGE (Step 4) ---
    public function payment(Request $request, $id)
    {
        $vehicle = Vehicle::findOrFail($id);
        
        // Pass data to view
        $pickupDate = $request->query('pickup_date');
        $returnDate = $request->query('return_date');
        $pickupLoc = $request->query('pickup_location');
        $returnLoc = $request->query('return_location');
        
        // Calculate Total
        $pickup = \Carbon\Carbon::parse($pickupDate);
        $dropoff = \Carbon\Carbon::parse($returnDate);
        $days = $pickup->diffInDays($dropoff) ?: 1;
        
        $total = ($vehicle->priceHour * 24 * $days) + $vehicle->baseDepo; // Simplified calc

        return view('bookings.payment', compact('vehicle', 'days', 'total', 'pickupDate', 'returnDate', 'pickupLoc', 'returnLoc'));
    }

    // --- 6. SUBMIT (Step 5 - Logic from your previous message) ---
    public function submitPayment(Request $request, $id)
    {
        // 1. Validation
        $request->validate([
            'payment_proof' => 'required|image|max:2048',
            // 'agreement' => 'accepted', 
        ]);

        // 2. Upload Proof
        $proofPath = null;
        if ($request->hasFile('payment_proof')) {
            $proofPath = $request->file('payment_proof')->store('receipts', 'public');
        }

        // 3. Create Booking
        // FIX: Changed $request->query() to $request->input()
        $booking = Booking::create([
            'customerID' => Auth::id(),
            'vehicleID' => $id,
            'bookingDate' => now(),
            
            'originalDate' => $request->input('pickup_date'), 
            'bookingTime' => '10:00:00',
            'returnDate' => $request->input('return_date'), 
            'returnTime' => '10:00:00',
            
            'pickupLocation' => $request->input('pickup_location'), 
            'returnLocation' => $request->input('return_location'), 
            'totalCost' => $request->input('total'), 
            
            'aggreementDate' => now(),
            'aggreementLink' => 'agreement_' . Auth::id() . '.pdf',
            'bookingStatus' => 'Submitted',
            'bookingType' => 'Standard',
        ]);

        // 4. Create Payment
        // FIX: Changed $request->query() to $request->input()
        Payment::create([
            'bookingID' => $booking->bookingID,
            'amount' => $request->input('total'), 
            'depoAmount' => 50.00, 
            'transactionDate' => now(),
            'paymentMethod' => 'QR Transfer',
            'paymentStatus' => 'Pending Verification',
            'depoStatus' => 'Holding',
            'depoRequestDate' => now(),
            'installmentDetails' => $proofPath 
        ]);

        return redirect()->route('book.index')->with('show_thank_you', true);
    }

    // --- 7. CANCEL BOOKING (UC004) ---
    public function cancel($id)
    {
        // Find the booking belonging to the logged-in user
        $booking = Booking::where('customerID', Auth::id())->findOrFail($id);

        // Only allow cancellation if status is 'Submitted'
        if ($booking->bookingStatus == 'Submitted') {
            $booking->update(['bookingStatus' => 'Cancelled']);
            
            return back()->with('success', 'Booking cancelled successfully.');
        }

        return back()->with('error', 'Cannot cancel a booking that is already processed.');
    }

    public function showAgreement($id)
    {
        $booking = Booking::with(['customer', 'vehicle'])->findOrFail($id);
        
        // Security check: Only owner or staff can view
        if (Auth::id() != $booking->customerID && !Auth::guard('staff')->check()) {
            abort(403);
        }

        return view('bookings.agreement', compact('booking'));
    }
}