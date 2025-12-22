<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Vehicle;
use App\Models\Penalties; // Required for Finance/Debt logic
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon; // Required for date calculations

class BookingController extends Controller
{
    // --- 1. MY BOOKINGS PAGE ---
    public function index()
    {
        $bookings = Booking::where('customerID', Auth::id())
                           ->with(['vehicle', 'payment', 'penalties']) 
                           ->orderBy('bookingDate', 'desc')
                           ->get();

        return view('bookings.index', compact('bookings'));
    }

    // --- 2. LANDING / SEARCH FORM ---
    public function create()
    {
        return view('bookings.create'); 
    }

    // --- 3. SEARCH RESULTS ---
    public function search(Request $request)
    {
        // Basic logic: return all available vehicles for now
        // You can add date filtering logic here later if needed
        $vehicles = Vehicle::where('availability', true)->get();
        
        return view('bookings.search_results', compact('vehicles'));
    }

    // --- 4. VEHICLE DETAILS ---
    public function show(Request $request, $id)
    {
        $vehicle = Vehicle::findOrFail($id);
        
        $pickup = Carbon::parse($request->query('pickup_date', now()));
        $dropoff = Carbon::parse($request->query('return_date', now()->addDay()));
        $days = $pickup->diffInDays($dropoff) ?: 1;
        
        return view('bookings.show', compact('vehicle', 'days'));
    }

    // --- 5. PAYMENT PAGE ---
    public function payment(Request $request, $id)
    {
        $vehicle = Vehicle::findOrFail($id);
        
        $pickupDate = $request->query('pickup_date', now()->format('Y-m-d'));
        $returnDate = $request->query('return_date', now()->addDay()->format('Y-m-d'));
        $pickupLoc = $request->query('pickup_location', 'Student Mall');
        $returnLoc = $request->query('return_location', 'Student Mall');
        
        // Calculate Total
        $pickup = Carbon::parse($pickupDate);
        $dropoff = Carbon::parse($returnDate);
        $days = $pickup->diffInDays($dropoff) ?: 1;
        
        $total = ($vehicle->priceHour * 24 * $days) + $vehicle->baseDepo;

        return view('bookings.payment', compact('vehicle', 'days', 'total', 'pickupDate', 'returnDate', 'pickupLoc', 'returnLoc'));
    }

    // --- 6. SUBMIT BOOKING (Create & Pay) ---
    public function submitPayment(Request $request, $id)
    {
        // 1. Validation
        $request->validate([
            'payment_proof' => 'required|image|max:2048',
        ]);

        // 2. Upload Proof
        $proofPath = null;
        if ($request->hasFile('payment_proof')) {
            $proofPath = $request->file('payment_proof')->store('receipts', 'public');
        }

        // 3. Create Booking Record
        $booking = Booking::create([
            'customerID' => Auth::id(),
            'vehicleID' => $id,
            'bookingDate' => now(),
            
            'originalDate' => $request->input('pickup_date'), 
            'bookingTime' => $request->input('pickup_time', '10:00:00'),
            'returnDate' => $request->input('return_date'), 
            'returnTime' => $request->input('return_time', '10:00:00'),
            
            'pickupLocation' => $request->input('pickup_location'), 
            'returnLocation' => $request->input('return_location'), 
            'totalCost' => $request->input('total'), 
            
            'aggreementDate' => now(),
            'aggreementLink' => 'agreement_' . Auth::id() . '.pdf', // Placeholder
            'bookingStatus' => 'Submitted',
            'bookingType' => 'Standard',
        ]);

        // 4. Create Payment Record
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

    // --- 7. CANCEL BOOKING (Redirects to Finance for Refund) ---
    public function cancel($id)
    {
        $booking = Booking::where('customerID', Auth::id())->findOrFail($id);

        if ($booking->bookingStatus == 'Submitted') {
            $booking->update(['bookingStatus' => 'Cancelled']);
            
            // Send user to Finance page to claim their money
            return redirect()->route('finance.index')->with('success', 'Booking cancelled. You can claim your refund below.');
        }

        return back()->with('error', 'Cannot cancel a booking that is already processed.');
    }

    // --- 8. SHOW AGREEMENT (Optional) ---
    public function showAgreement($id)
    {
        $booking = Booking::with(['customer', 'vehicle'])->findOrFail($id);
        
        if (Auth::id() != $booking->customerID && !Auth::guard('staff')->check()) {
            abort(403);
        }

        return view('bookings.agreement', compact('booking'));
    }

    // --- 9. EDIT FORM ---
    public function edit($id)
    {
        $booking = Booking::where('customerID', Auth::id())->findOrFail($id);

        if ($booking->bookingStatus !== 'Submitted') {
            return redirect()->route('book.index')->with('error', 'Cannot edit a confirmed booking.');
        }

        return view('bookings.edit', compact('booking'));
    }

    // --- 10. UPDATE BOOKING (Calculates Price Difference & Penalties) ---
    public function update(Request $request, $id)
    {
        $booking = Booking::where('customerID', Auth::id())->findOrFail($id);
        $vehicle = $booking->vehicle;

        // 1. Validate
        $request->validate([
            'pickup_date' => 'required|date|after_or_equal:today',
            'return_date' => 'required|date|after:pickup_date',
            'pickup_location' => 'required|string',
            'return_location' => 'required|string',
        ]);

        // 2. Calculate New Price
        $pickup = Carbon::parse($request->pickup_date);
        $dropoff = Carbon::parse($request->return_date);
        $newDays = $pickup->diffInDays($dropoff) ?: 1;
        
        $newTotal = ($vehicle->priceHour * 24 * $newDays) + $vehicle->baseDepo;
        $oldTotal = $booking->totalCost;
        $difference = $newTotal - $oldTotal;

        // 3. Update the Booking
        $booking->update([
            'originalDate' => $request->pickup_date,
            'returnDate' => $request->return_date,
            'pickupLocation' => $request->pickup_location,
            'returnLocation' => $request->return_location,
            'totalCost' => $newTotal, 
        ]);

        // 4. Handle Finances (Debt or Refund Logic)
        if ($difference > 0) {
            // Price increased -> Create a Debt (Penalty)
            Penalties::create([
                'bookingID' => $booking->bookingID,
                'amount' => $difference,
                'reason' => 'Booking Modification (Price Increase)',
                'status' => 'Pending',
            ]);

            return redirect()->route('finance.index')
                ->with('warning', 'Booking updated. Additional payment of RM ' . number_format($difference, 2) . ' is required.');
        
        } elseif ($difference < 0) {
            // Price decreased -> Notify user
            return redirect()->route('book.index')
                ->with('success', 'Booking updated. Your total price has decreased by RM ' . number_format(abs($difference), 2));
        }

        return redirect()->route('book.index')->with('success', 'Booking updated successfully.');
    }
}