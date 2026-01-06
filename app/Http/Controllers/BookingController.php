<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Vehicle;
use App\Models\Penalties;
use App\Models\Voucher;
use App\Models\Staff;
use App\Services\GoogleDriveService;
use App\Notifications\NewBookingSubmitted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class BookingController extends Controller
{
    protected $driveService;

    // Inject the GoogleDriveService
    public function __construct(GoogleDriveService $driveService)
    {
        $this->driveService = $driveService;
    }

    // --- 1. MY BOOKINGS PAGE ---
    public function index(Request $request)
    {
    $query = Booking::where('customerID', Auth::id())
                ->with(['vehicle', 'payments', 'penalties', 'inspections']); // Added 'inspections'
                
    // Filter by Status
    if ($request->filled('status') && $request->status != 'all') {
        $query->where('bookingStatus', $request->status);
    }

    // Filter by Date (Month)
    if ($request->filled('date')) {
        $query->whereMonth('bookingDate', Carbon::parse($request->date)->month);
    }

    $bookings = $query->orderBy('originalDate', 'desc')->get();

        return view('bookings.index', compact('bookings'));
    }

    // --- 2. LANDING / SEARCH FORM ---
    public function create() 
    {
        $user = auth()->user();

        // 1. BLACKLIST CHECK (NEW)
        // If the user is blacklisted, block them immediately.
        if ($user->blacklisted) {
            $reason = $user->blacklist_reason ?? 'Violation of terms and conditions.';
            
            return redirect()->route('profile.edit')
                ->with('error', '⛔ ACTION BLOCKED: Your account is blacklisted. You cannot make new bookings. Reason: ' . $reason);
        }

        // 2. STRICT CHECK: Verify ALL profile fields are filled
        if (
            empty($user->fullName) ||
            empty($user->email) ||
            empty($user->phoneNo) ||
            empty($user->emergency_contact_no) ||
            empty($user->emergency_contact_name) || // Added this new field
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
            return redirect()->route('profile.edit')
                ->with('error', '⚠️ Action Required: You must complete ALL profile details (including Bank Info, Addresses, and IDs) before you can book a car.');
        }
        
        // If checks pass, proceed to booking page
        $today = Carbon::today();
    
        $vehicles = Vehicle::where('availability', true)
            ->whereDoesntHave('bookings', function ($q) use ($today) {
                // Exclude cars that have active bookings overlapping with "today"
                $q->whereIn('bookingStatus', ['Submitted', 'Deposit Paid', 'Paid', 'Approved', 'Active'])
                ->where(function ($sub) use ($today) {
                    $sub->whereDate('originalDate', '<=', $today)
                        ->whereDate('returnDate', '>=', $today);
                });
            })
            ->orderBy('priceHour', 'asc')
            ->get();

        // Pass $vehicles to the view
        return view('bookings.create', compact('vehicles')); 
    }

    // --- 3. SEARCH RESULTS (UPDATED) ---
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
    $bufferPickup = Carbon::parse($pickup)->subDay();
    $bufferReturn = Carbon::parse($return)->addDay();

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
    $vehicles = $vehicles->filter(function($vehicle) use ($pickup, $return) {
            $blockedDates = $vehicle->blocked_dates ?? []; 
            $start = Carbon::parse($pickup);
            $end = Carbon::parse($return);

            foreach($blockedDates as $date) {
                $blocked = Carbon::parse($date);
                // If a blocked date falls inside the requested range, remove vehicle
                if($blocked->between($start, $end)) {
                    return false; 
                }
            }
            return true; 
        });
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
    // In App\Http\Controllers\BookingController.php

    // --- 6. SUBMIT BOOKING (Updated) ---
    public function submitPayment(Request $request, $id)
    {
        // 1. Validation
        $request->validate([
            'payment_proof' => 'required|mimes:jpeg,png,jpg|max:4048',
            'agreement_proof' => 'required|file|mimes:pdf|max:4048',
            'payment_type' => 'required|in:full,deposit',
        ]);

        $vehicle = Vehicle::findOrFail($id);

        // 2. Calculate Prices (Existing Logic)
        $rentalCharge = $this->calculateRentalPrice(
            $vehicle, 
            $request->input('pickup_date'), $request->input('pickup_time'), 
            $request->input('return_date'), $request->input('return_time')
        );

        $baseDepo = $vehicle->baseDepo;
        $grossTotal = $rentalCharge + $baseDepo;
        
        // Voucher Logic (Existing)
        $discountAmount = 0;
        $voucherID = null;
        if ($request->filled('voucher_id')) {
            $voucher = Voucher::find($request->input('voucher_id'));
            if ($voucher) {
                $voucherID = $voucher->id; 
                if ($voucher->discount_percentage > 0) {
                    $discountAmount = ($grossTotal * $voucher->discount_percentage) / 100;
                } else {
                    $discountAmount = $voucher->discount_amount;
                }
            }
        }
        $finalTotalCost = max(0, $grossTotal - $discountAmount);

        // 3. Determine Amount (Existing)
        if ($request->input('payment_type') == 'deposit') {
            $amountToPayNow = $baseDepo;
            $bookingStatus = 'Deposit Paid';
            if ($amountToPayNow >= $finalTotalCost) {
                 $amountToPayNow = $finalTotalCost;
                 $bookingStatus = 'Submitted'; 
            }
        } else {
            $amountToPayNow = $finalTotalCost;
            $bookingStatus = 'Submitted';
        }

        // --- 4. GOOGLE DRIVE UPLOAD LOGIC (NEW) ---
        
        // Format: [Name - Date - Time]
        $timestamp = now()->format('Y-m-d - H-i');
        $userName = Auth::user()->fullName; // Using fullName from Customer model
        $fileNameBase = "[{$userName} - {$timestamp}]";

        // A. Upload Receipt
        $receiptFile = $request->file('payment_proof');
        // Fallback to local storage if Drive fails, or use local as temp
        $localProofPath = $receiptFile->store('receipts', 'public'); 
        
        $receiptLink = $this->driveService->uploadFile(
            $receiptFile, 
            env('GOOGLE_DRIVE_RECEIPTS'), // Folder ID from .env
            $fileNameBase . " - Receipt"
        );

        // B. Upload Agreement
        $agreementFile = $request->file('agreement_proof');
        $localAgreementPath = $agreementFile->store('agreements', 'public');

        $agreementLink = $this->driveService->uploadFile(
            $agreementFile, 
            env('GOOGLE_DRIVE_AGREEMENTS'), // Folder ID from .env
            $fileNameBase . " - Agreement"
        );

        // Use Drive Link if available, otherwise fallback to local path
        $finalReceiptPath = $receiptLink ?? $localProofPath;
        $finalAgreementPath = $agreementLink ?? $localAgreementPath;

        // --- 5. Create Booking ---
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
            'totalCost' => $finalTotalCost, 
            'voucherID' => $voucherID,
            
            'aggreementDate' => now(),
            'aggreementLink' => $finalAgreementPath, // SAVED GOOGLE DRIVE LINK HERE
            
            'bookingStatus' => $bookingStatus,
            'bookingType' => 'Standard',
            'remarks' => $request->input('remarks'),
        ]);

        // --- 6. Create Payment Record ---
        Payment::create([
            'bookingID' => $booking->bookingID,
            'amount' => $amountToPayNow, 
            'depoAmount' => $baseDepo,
            'transactionDate' => now(),
            'paymentMethod' => 'QR Transfer',
            'paymentStatus' => 'Pending Verification',
            'depoStatus' => 'Holding',
            'depoRequestDate' => now(),
            
            'installmentDetails' => $finalReceiptPath // SAVED GOOGLE DRIVE LINK HERE
        ]);
        
        // 7. Notifications
        try {
            $staff = Staff::all(); 
            Notification::send($staff, new NewBookingSubmitted($booking));
        } catch (\Exception $e) {
            \Log::error("Notification failed: " . $e->getMessage());
        }

        return redirect()->route('book.index')->with('show_thank_you', true);
    }

    // --- 7. CANCEL BOOKING ---
    public function cancel($id)
    {
        $booking = Booking::where('customerID', Auth::id())->findOrFail($id);
        $allowedStatuses = ['Submitted', 'Deposit Paid', 'Paid', 'Confirmed'];

        if (in_array($booking->bookingStatus, $allowedStatuses)) {
            // 24-Hour Rule
            if ($booking->bookingStatus == 'Confirmed') {
                $pickupTime = Carbon::parse($booking->originalDate . ' ' . $booking->bookingTime);
                
                if (now()->addDay()->gt($pickupTime)) {
                    // This sends the 'error' session to the view
                    return back()->with('error', 'Too late to cancel! Confirmed bookings must be cancelled at least 24 hours before pickup.');
                }
            }
            
            $booking->update(['bookingStatus' => 'Cancelled']);
            
            // This sends the 'success' session
            return redirect()->route('finance.index')
                ->with('success', 'Booking cancelled. Please check "Claimable" section to request your refund.');
        }

        return back()->with('error', 'Cannot cancel this booking.');
    }

    // In showAgreement, handle external links vs local files if you wish
    public function showAgreement($id)
    {
        $booking = Booking::with(['customer', 'vehicle'])->findOrFail($id);
        
        // Check if it's a Google Drive Link
        if (str_contains($booking->aggreementLink, 'drive.google.com')) {
            return redirect($booking->aggreementLink);
        }

        // Fallback for old local files
        if (Auth::id() != $booking->customerID && !Auth::guard('staff')->check()) {
            abort(403);
        }
        return view('bookings.agreement', compact('booking'));
    }

    public function previewAgreement(Request $request)
    {
        $user = Auth::user();
        $vehicle = Vehicle::findOrFail($request->vehicle_id);
        
        $booking = new Booking();
        $booking->bookingID = "PENDING"; 
        $booking->aggreementDate = now();
        
        // Fill details from Request
        $booking->originalDate = $request->pickup_date; // Use originalDate column name
        $booking->bookingTime = $request->pickup_time;
        $booking->returnDate = $request->return_date;
        $booking->returnTime = $request->return_time;
        $booking->pickupLocation = $request->pickup_location;
        $booking->returnLocation = $request->return_location;
        
        // Set Relations
        $booking->setRelation('customer', $user);
        $booking->setRelation('vehicle', $vehicle);

        // --- FIX: CALCULATE PRICE FOR PREVIEW ---
        // We reuse the private method logic
        $rentalCharge = $this->calculateRentalPrice(
            $vehicle,
            $request->pickup_date,
            $request->pickup_time,
            $request->return_date,
            $request->return_time
        );
        
        // Don't forget to add Base Deposit for "Grand Total"
        // (Assuming Grand Total = Rental + Deposit, based on payment method)
        $booking->totalCost = $rentalCharge + $vehicle->baseDepo;

        return view('bookings.agreement', compact('booking'));
    }

    // --- 9. UPLOAD INSPECTION ---
    public function uploadInspection(Request $request, $id)
    {
        $booking = Booking::where('customerID', Auth::id())->findOrFail($id);

        // 1. Determine Inspection Type EARLY
        // If status is Active or Completed, it's a "Return" inspection. Otherwise "Pickup".
        $type = ($booking->bookingStatus == 'Active' || $booking->bookingStatus == 'Completed') 
                ? 'Return' 
                : 'Pickup';

        // 2. [NEW] STRICT CHECK: Stop if inspection already exists
        // We check for an inspection of this Booking ID, this Type, and where staffID is null (Customer)
        $exists = \App\Models\Inspection::where('bookingID', $booking->bookingID)
                                        ->where('inspectionType', $type)
                                        ->whereNull('staffID') // Ensures we check customer's submission
                                        ->exists();

        if ($exists) {
            return back()->with('error', "Action Failed: You have already submitted the $type inspection.");
        }

        // --- EXISTING LOGIC STARTS HERE ---

        // Determine expected count based on stage
        $requiredCount = ($type == 'Pickup') ? 5 : 6;
        $typeName = $type;

        $request->validate([
            'photos' => 'required',
            'photos.*' => 'image|max:4048', // Allow multiple images
            'fuel_level' => 'required',
                'mileage' => 'required|numeric',
            ], [
                'photos.size' => "You must upload exactly $requiredCount photos for $typeName."
        ]);

        

        $photoPaths = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $photoPaths[] = $photo->store('inspections', 'public');
            }
        }

        // Create Inspection Record
        \App\Models\Inspection::create([
            'bookingID' => $booking->bookingID,
            'staffID' => null, 
            'inspectionType' => $type,
            'inspectionDate' => now(),
            
            // Dynamic Columns
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

    public function markNotificationsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return back();
    }

    // --- 10. STREAM INVOICE PDF FOR CUSTOMER ---
    public function streamInvoice($id)
    {
        // 1. Find booking owned by this customer
        $booking = Booking::with(['customer', 'vehicle', 'payment', 'voucher'])
                    ->where('customerID', Auth::id())
                    ->findOrFail($id);

        // 2. Security Check: Only allow if Completed
        if ($booking->bookingStatus !== 'Completed') {
            return back()->with('error', 'Invoice is only generated for Completed bookings.');
        }

        // 3. Generate and Stream
        $pdf = Pdf::loadView('pdf.invoice', compact('booking'));
        return $pdf->stream('Invoice-' . $booking->bookingID . '.pdf');
    }
}