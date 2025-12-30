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
    public function index(Request $request)
{
    $query = Booking::where('customerID', Auth::id())
                    ->with(['vehicle', 'payments', 'penalties']);

    // Filter by Status
    if ($request->filled('status') && $request->status != 'all') {
        $query->where('bookingStatus', $request->status);
    }

    // Filter by Date (Month)
    if ($request->filled('date')) {
        $query->whereMonth('bookingDate', Carbon::parse($request->date)->month);
    }

    $bookings = $query->orderBy('bookingDate', 'desc')->get();

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
    // --- 1. Basic Validation ---
    $request->validate([
        'pickup_date' => 'required|date|after_or_equal:today',
        'return_date' => 'required|date|after_or_equal:pickup_date', 
    ]);

    $pickup = $request->pickup_date;
    $return = $request->return_date;

    // --- 2. Buffer Logic (1 Day Cooldown) ---
    // We expand the search range by 1 day before and after to ensure a gap
    $bufferPickup = \Carbon\Carbon::parse($pickup)->subDay();
    $bufferReturn = \Carbon\Carbon::parse($return)->addDay();

    // --- 3. Start Query Builder ---
    // We break the chain here to allow conditional filtering
    $query = Vehicle::where('availability', true);

    // --- 4. Apply Cooldown/Availability Check ---
    $query->whereDoesntHave('bookings', function ($q) use ($bufferPickup, $bufferReturn) {
        $q->whereIn('bookingStatus', ['Submitted', 'Deposit Paid', 'Paid', 'Approved', 'Active'])
          ->where(function ($subQ) use ($bufferPickup, $bufferReturn) {
              // Check for Overlap
              $subQ->whereBetween('originalDate', [$bufferPickup, $bufferReturn])
                   ->orWhereBetween('returnDate', [$bufferPickup, $bufferReturn])
                   ->orWhere(function ($inner) use ($bufferPickup, $bufferReturn) {
                       $inner->where('originalDate', '<', $bufferPickup)
                             ->where('returnDate', '>', $bufferReturn);
                   });
          });
    });

    // --- 5. Apply Vehicle Type Filter (The New Part) ---
    // This only runs if the user checked any boxes in the sidebar
    if ($request->filled('types')) {
        $query->whereIn('type', $request->types);
    }

    // --- 6. Execute Query ---
    $vehicles = $query->get();

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
        
        // Calculate using shared logic
        $rentalCharge = $this->calculateRentalPrice(
            $vehicle, 
            $request->pickup_date, $request->pickup_time, 
            $request->return_date, $request->return_time
        );

        $grandTotal = $rentalCharge + $vehicle->baseDepo;

        // We still need these for the display badge
        $pickup = Carbon::parse($request->pickup_date . ' ' . $request->pickup_time);
        $return = Carbon::parse($request->return_date . ' ' . $request->return_time);
        $totalHours = $pickup->diffInHours($return);
        
        return view('bookings.payment', [
            'vehicle' => $vehicle,
            'total' => $grandTotal,
            'rentalCharge' => $rentalCharge,
            'days' => floor($totalHours / 24),
            'extraHours' => $totalHours % 24,
            'totalHours' => $totalHours,
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
            'agreement_proof' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'payment_type' => 'required',
        ]);

        $vehicle = Vehicle::findOrFail($id);

        // 2. Upload Payment Receipt (Fixing the $proofPath variable)
        $proofPath = null; // Initialize variable
        if ($request->hasFile('payment_proof')) {
            // Store the file and assign the path to $proofPath
            $proofPath = $request->file('payment_proof')->store('receipts', 'public');
        }

        // 3. Upload Agreement Form
        $agreementPath = null;
        if ($request->hasFile('agreement_proof')) {
            $agreementPath = $request->file('agreement_proof')->store('agreements', 'public');
        }

        // ... (Your price calculation logic here) ...
        $grandTotal = $request->input('total'); // Or use the recalculation helper discussed previously

        // 4. Create Booking
        $booking = Booking::create([
            'customerID' => Auth::id(),
            'vehicleID' => $id,
            'bookingDate' => now(),
            'originalDate' => $request->input('pickup_date'),
            'bookingTime' => $request->input('pickup_time'),
            'returnDate' => $request->input('return_date'),
            'returnTime' => $request->input('return_time'),
            'pickupLocation' => $request->input('pickup_location'),
            'returnLocation' => $request->input('return_location'),
            'totalCost' => $grandTotal,
            'aggreementDate' => now(),
            'aggreementLink' => $agreementPath,
            'bookingStatus' => ($request->input('payment_type') == 'deposit') ? 'Deposit Paid' : 'Submitted',
            'bookingType' => 'Standard',
            'remarks' => $request->input('remarks'),
        ]);

        // 5. Create Payment Record (Using the now-defined $proofPath)
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
            'installmentDetails' => $proofPath // ✅ This will now work correctly
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

    /**
     * Shared logic to calculate the total rental price.
     */
    private function calculateRentalPrice($vehicle, $pickupDate, $pickupTime, $returnDate, $returnTime)
    {
        $pickup = Carbon::parse($pickupDate . ' ' . $pickupTime);
        $return = Carbon::parse($returnDate . ' ' . $returnTime);

        // Calculate Total Hours
        $totalHours = $pickup->diffInHours($return);
        if ($pickup->diffInMinutes($return) > $totalHours * 60) {
            $totalHours++; // Round up for partial hours
        }

        $rates = is_array($vehicle->hourly_rates) 
                ? $vehicle->hourly_rates 
                : json_decode($vehicle->hourly_rates, true);

        $days = floor($totalHours / 24);
        $balanceHours = $totalHours % 24;
        $dailyRate = $rates[24] ?? ($vehicle->priceHour * 24);
        
        $remainderCost = 0;
        if ($balanceHours > 0) {
            $tiers = [1, 3, 5, 7, 9, 12, 24];
            $selectedTier = 24;
            foreach ($tiers as $tier) {
                if ($balanceHours <= $tier) {
                    $selectedTier = $tier;
                    break;
                }
            }
            $remainderCost = $rates[$selectedTier] ?? ($vehicle->priceHour * $balanceHours);
        }

        return ($days * $dailyRate) + $remainderCost;
    }

    public function getRemainingBalanceAttribute()
    {
        // Sum all payments for this booking that are already verified or completed
        $totalPaid = $this->payments()->where('paymentStatus', 'Verified')->sum('amount');
        return max(0, $this->totalCost - $totalPaid); // Ensure balance never goes below 0
    }
}