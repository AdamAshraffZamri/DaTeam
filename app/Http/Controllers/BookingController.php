<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Vehicle;
use App\Models\Penalties;
use App\Models\Voucher; // Required for Voucher Logic
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class BookingController extends Controller
{
    // --- 1. MY BOOKINGS PAGE ---
    public function index()
    {
        // Loaded 'payments' to fix the "RelationNotFound" error on the receipt button
        $bookings = Booking::where('customerID', Auth::id())
                           ->with(['vehicle', 'payments', 'penalties']) 
                           ->orderBy('bookingDate', 'desc')
                           ->get();

        return view('bookings.index', compact('bookings'));
    }

    // --- 2. LANDING / SEARCH FORM ---
    public function create() 
    {
        $user = auth()->user();

        // STRICT CHECK: Verify ALL profile fields are filled
        if (
            empty($user->fullName) ||
            empty($user->email) ||
            empty($user->phoneNo) ||
            empty($user->emergency_contact_no) ||
            empty($user->homeAddress) ||
            empty($user->collegeAddress) ||
            empty($user->stustaffID) || // Student/Staff ID
            empty($user->ic_passport) || // IC or Passport
            empty($user->drivingNo) || // License Number
            empty($user->nationality) ||
            empty($user->dob) ||
            empty($user->faculty) ||
            empty($user->bankName) ||
            empty($user->bankAccountNo)
        ) {
            // Redirect to Profile Edit with a specific warning
            return redirect()->route('profile.edit')
                ->with('error', '⚠️ Action Required: You must complete ALL profile details (including Bank Info, Addresses, and IDs) before you can book a car.');
        }

        // If checks pass, proceed to booking page
        return view('bookings.create'); 
    }

    // --- 3. SEARCH RESULTS ---
    public function search(Request $request)
    {
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
    
    // 1. Parse Dates
    $pickup = \Carbon\Carbon::parse($request->pickup_date . ' ' . $request->pickup_time);
    $return = \Carbon\Carbon::parse($request->return_date . ' ' . $request->return_time);
    
    // 2. Calculate Total Duration
    // We use ceil() on minutes to ensure 25 hours 1 min becomes 26 hours (standard rental practice)
    $totalHours = $pickup->diffInHours($return);
    if ($pickup->diffInMinutes($return) > $totalHours * 60) {
        $totalHours++;
    }

    // 3. Decode Hourly Rates (Safely)
    $rates = is_array($vehicle->hourly_rates) 
             ? $vehicle->hourly_rates 
             : json_decode($vehicle->hourly_rates, true);

    // 4. Calculate Price (Mixed Logic: Days + Remaining Hours)
    $days = floor($totalHours / 24);      // e.g., 61 hours = 2 Days
    $balanceHours = $totalHours % 24;     // Remaining 13 hours

    $dailyRate = $rates[24] ?? 0;         // Rate for 24H
    
    // Calculate Remainder Cost using Tiers (1, 3, 5, 7, 9, 12)
    $remainderCost = 0;
    if ($balanceHours > 0) {
        $tiers = [1, 3, 5, 7, 9, 12, 24]; // Standard Tiers
        $selectedTier = 24; // Default cap
        
        foreach ($tiers as $tier) {
            if ($balanceHours <= $tier) {
                $selectedTier = $tier;
                break;
            }
        }
        $remainderCost = $rates[$selectedTier] ?? 0;
    }

    // 5. Final Calculations
    $rentalCharge = ($days * $dailyRate) + $remainderCost;
    $grandTotal = $rentalCharge + $vehicle->baseDepo;

    return view('bookings.payment', [
        'vehicle' => $vehicle,
        'total' => $grandTotal,           // The final price to pay
        'rentalCharge' => $rentalCharge,  // Pure rental cost (no deposit)
        'days' => $days,                  // Integer (e.g., 2)
        'extraHours' => $balanceHours,    // Integer (e.g., 13)
        'totalHours' => $totalHours,      // Total duration in hours
        'pickupDate' => $request->pickup_date,
        'returnDate' => $request->return_date,
        'pickupLoc' => $request->pickup_location,
        'returnLoc' => $request->return_location
    ]);
}

    // --- 6. SUBMIT BOOKING (Handles Full/Deposit & Vouchers) ---
    public function submitPayment(Request $request, $id)
    {
        // 1. Validation
        $request->validate([
            'payment_proof' => 'required|image|max:2048',
            'agreement_proof' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048', // FIX: Added Agreement Validation
            'payment_type' => 'required', // 'full' or 'deposit'
        ]);

        $vehicle = Vehicle::findOrFail($id);

        // 2. Upload Proofs
        // A. Payment Receipt
        $proofPath = null;
        if ($request->hasFile('payment_proof')) {
            $proofPath = $request->file('payment_proof')->store('receipts', 'public');
        }

        // B. Agreement Form (FIX: Handle File Upload)
        $agreementPath = null;
        if ($request->hasFile('agreement_proof')) {
            $agreementPath = $request->file('agreement_proof')->store('agreements', 'public');
        }

        // 3. Recalculate Total (Security)
        $pickup = Carbon::parse($request->input('pickup_date'));
        $dropoff = Carbon::parse($request->input('return_date'));
        $days = $pickup->diffInDays($dropoff) ?: 1;
        
        $grandTotal = ($vehicle->priceHour * 24 * $days) + $vehicle->baseDepo;

        // Apply Voucher if present
        if ($request->filled('voucher_id')) {
            $voucher = Voucher::find($request->input('voucher_id'));
            if ($voucher && !$voucher->isUsed) {
                $grandTotal -= $voucher->voucherAmount;
                $voucher->update(['isUsed' => true]);
            }
        }
        
        if($grandTotal < 0) $grandTotal = 0;

        // 4. Determine Status
        $status = ($request->input('payment_type') == 'deposit') ? 'Deposit Paid' : 'Submitted';

        // 5. Create Booking
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
            
            'totalCost' => $grandTotal, 
            
            'aggreementDate' => now(),
            'aggreementLink' => $agreementPath, // FIX: Save the actual file path here
            'bookingStatus' => $status,
            'bookingType' => 'Standard',
        ]);

        // 6. Create Payment Record
        $amountPaid = ($request->input('payment_type') == 'deposit') ? $vehicle->baseDepo : $grandTotal;

        Payment::create([
            'bookingID' => $booking->bookingID,
            'amount' => $amountPaid,
            'depoAmount' => $vehicle->baseDepo, 
            'transactionDate' => now(),
            'paymentMethod' => 'QR Transfer',
            'paymentStatus' => 'Pending Verification',
            'depoStatus' => 'Holding',
            'depoRequestDate' => now(),
            'installmentDetails' => $proofPath 
        ]);

        return redirect()->route('book.index')->with('show_thank_you', true);
    }

    // --- 7. CANCEL BOOKING (Updated Strategy) ---
    public function cancel($id)
    {
        $booking = Booking::where('customerID', Auth::id())->findOrFail($id);

        // Allow cancelling even if Paid/Approved so they can claim refund
        $allowedStatuses = ['Submitted', 'Deposit Paid', 'Paid', 'Approved'];

        if (in_array($booking->bookingStatus, $allowedStatuses)) {
            
            $booking->update(['bookingStatus' => 'Cancelled']);
            
            // Redirect to Finance Center for refund
            return redirect()->route('finance.index')
                ->with('success', 'Booking cancelled. Please check "Claimable" section to request your refund.');
        }

        return back()->with('error', 'Cannot cancel this booking.');
    }

    // --- 8. SHOW AGREEMENT ---
    public function showAgreement($id)
    {
        $booking = Booking::with(['customer', 'vehicle'])->findOrFail($id);
        
        if (Auth::id() != $booking->customerID && !Auth::guard('staff')->check()) {
            abort(403);
        }

        return view('bookings.agreement', compact('booking'));
    }

    // --- ADD THIS NEW FUNCTION ---
    public function previewAgreement(Request $request)
    {
        $user = Auth::user();
        $vehicle = Vehicle::findOrFail($request->vehicle_id);
        
        // Create a "Fake" Booking Object for the view
        $booking = new Booking();
        $booking->bookingID = "PENDING"; // Placeholder
        $booking->aggreementDate = now();
        $booking->pickupLocation = $request->pickup_location;
        $booking->returnLocation = $request->return_location;
        $booking->returnDate = $request->return_date;
        $booking->returnTime = $request->return_time;
        
        // Manually attach relationships
        $booking->setRelation('customer', $user);
        $booking->setRelation('vehicle', $vehicle);

        return view('bookings.agreement', compact('booking'));
    }

    // --- 9. UPLOAD INSPECTION (Customer Side) ---
    public function uploadInspection(Request $request, $id)
    {
        $booking = Booking::where('customerID', Auth::id())->findOrFail($id);

        $request->validate([
            'photos' => 'required',
            'photos.*' => 'image|max:4048', // Allow multiple images
        ]);

        // Determine Inspection Stage
        $type = 'Pickup';
        if ($booking->bookingStatus == 'Active' || $booking->bookingStatus == 'Completed') {
            $type = 'Return';
        }

        // Store Photos
        $photoPaths = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $photoPaths[] = $photo->store('inspections', 'public');
            }
        }

        // Create Inspection Record (No Staff ID)
        \App\Models\Inspection::create([
            'bookingID' => $booking->bookingID,
            'staffID' => null, // Indicates Customer Submission
            'inspectionType' => $type,
            'inspectionDate' => now(),
            // Save as JSON array
            'photosBefore' => $type == 'Pickup' ? json_encode($photoPaths) : null,
            'photosAfter' => $type == 'Return' ? json_encode($photoPaths) : null,
            'fuelBefore' => $request->input('fuel_level'), // Optional
            'mileageBefore' => $request->input('mileage'), // Optional
        ]);

        return back()->with('success', 'Inspection photos uploaded successfully!');
    }
}