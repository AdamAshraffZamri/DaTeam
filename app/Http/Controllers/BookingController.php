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
        
        $pickupDate = $request->query('pickup_date', now()->format('Y-m-d'));
        $returnDate = $request->query('return_date', now()->addDay()->format('Y-m-d'));
        $pickupLoc = $request->query('pickup_location', 'Student Mall');
        $returnLoc = $request->query('return_location', 'Student Mall');
        
        $pickup = Carbon::parse($pickupDate);
        $dropoff = Carbon::parse($returnDate);
        $days = $pickup->diffInDays($dropoff) ?: 1;
        
        $total = ($vehicle->priceHour * 24 * $days) + $vehicle->baseDepo;

        return view('bookings.payment', compact('vehicle', 'days', 'total', 'pickupDate', 'returnDate', 'pickupLoc', 'returnLoc'));
    }

    // --- 6. SUBMIT BOOKING (Handles Full/Deposit & Vouchers) ---
    public function submitPayment(Request $request, $id)
    {
        // 1. Validation
        $request->validate([
            'payment_proof' => 'required|image|max:2048',
            'payment_type' => 'required', // 'full' or 'deposit'
        ]);

        $vehicle = Vehicle::findOrFail($id);

        // 2. Upload Proof
        $proofPath = null;
        if ($request->hasFile('payment_proof')) {
            $proofPath = $request->file('payment_proof')->store('receipts', 'public');
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
            'aggreementLink' => 'agreement_' . Auth::id() . '.pdf', 
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

    // --- DELETED: edit() and update() functions ---
    // We removed these to simplify logic. Users must Cancel & Rebook.
}