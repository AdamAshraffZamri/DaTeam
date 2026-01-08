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
use App\Notifications\BookingStatusUpdated;
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

        if ($user instanceof \App\Models\Customer && $user->unpaidPenalties()) {
        
        // 2. Kalau ada saman, PAKSA dia pergi ke page Finance (Payment Tab)
        return redirect()->route('finance.index')
            ->with('error', '⛔ ACTION BLOCKED: You have unpaid penalties. Please settle them before making a new booking.');
    }

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
            empty($user->driving_license_expiry) || // License Number
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
                $q->whereIn('bookingStatus', ['Submitted', 'Deposit Paid', 'Paid', 'Confirmed', 'Active'])
                ->where(function ($sub) use ($today) {
                    $sub->whereDate('originalDate', '<=', $today)
                        ->whereDate('returnDate', '>=', $today);
                });
            })
            ->orderBy('priceHour', 'asc')
            ->get();


        return view('bookings.create', compact('vehicles')); 
    }

    // --- 3. SEARCH RESULTS (UPDATED WITH 2-WAY 3-HOUR BUFFER) ---
    public function search(Request $request)
    {
        // 1. Validate Date AND Time
        $request->validate([
            'pickup_date' => 'required|date|after_or_equal:today',
            'pickup_time' => 'required',
            'return_date' => 'required|date|after_or_equal:pickup_date',
            'return_time' => 'required',
        ]);

        // 2. Parse Requested Start & End Times
        try {
            $reqStart = Carbon::parse($request->pickup_date . ' ' . $request->pickup_time);
            $reqEnd   = Carbon::parse($request->return_date . ' ' . $request->return_time);
        } catch (\Exception $e) {
            return back()->withErrors(['date_error' => 'Invalid date or time format.']);
        }

        // Basic Check: Return must be after Pickup
        if ($reqEnd->lte($reqStart)) {
            return back()->withErrors(['return_time' => 'Return time must be after pickup time.']);
        }

        // 3. Start Query (Eager load Bookings & Maintenances)
        $query = Vehicle::where('availability', true)
            ->with(['bookings', 'maintenances']); 

        // 4. Apply Vehicle Category Filter (if selected)
        if ($request->filled('category')) {
            $query->whereIn('vehicle_category', $request->category);
        }

        // 5. Apply Vehicle Type Filter (if selected)
        if ($request->filled('types')) {
            $query->whereIn('type', $request->types);
        }

        // 6. Apply Price Range Filter (if selected)
        if ($request->filled('price_range')) {
            $query->where(function($subQuery) use ($request) {
                foreach ($request->price_range as $range) {
                    if ($range === '0-100') {
                        $subQuery->orWhereBetween('priceHour', [0, 100]);
                    } elseif ($range === '100-200') {
                        $subQuery->orWhereBetween('priceHour', [100, 200]);
                    } elseif ($range === '200-300') {
                        $subQuery->orWhereBetween('priceHour', [200, 300]);
                    } elseif ($range === '300-1000') {
                        $subQuery->orWhere('priceHour', '>=', 300);
                    }
                }
            });
        }

        // 7. Fetch & Filter in Memory
        $allAvailableVehicles = $query->get()->filter(function($vehicle) use ($reqStart, $reqEnd) {
            
            // Define the Requested "Blocked" Block (Includes its own 3-hour cooldown tail)
            // This represents: [Req Start] ------ [Req End] -- (3h Buffer) --|
            $reqEndWithBuffer = $reqEnd->copy()->addHours(3);

            // --- A. CHECK BOOKINGS ---
            foreach ($vehicle->bookings as $booking) {
                if (in_array($booking->bookingStatus, ['Cancelled', 'Rejected'])) {
                    continue;
                }

                // Parse Existing Booking Times
                $bookStart = Carbon::parse($booking->originalDate . ' ' . $booking->bookingTime);
                $bookEnd   = Carbon::parse($booking->returnDate . ' ' . $booking->returnTime);
                
                // Define Existing "Blocked" Block (Includes its own 3-hour cooldown tail)
                // This represents: [Book Start] ------ [Book End] -- (3h Buffer) --|
                $bookEndWithBuffer = $bookEnd->copy()->addHours(3);

                // --- 2-WAY COOLDOWN CHECK ---
                // Conflict exists if the two extended periods overlap.
                // 1. Check if New Request starts too soon after Existing Booking (Existing Cooldown violation)
                // 2. Check if New Request ends too close to Existing Booking Start (New Request Cooldown violation)
                
                // Logic: (Request Start < Existing End+Buffer) AND (Request End+Buffer > Existing Start)
                if ($reqStart->lt($bookEndWithBuffer) && $reqEndWithBuffer->gt($bookStart)) {
                    return false; // Unavailable
                }
            }

            // --- B. CHECK MAINTENANCE BLOCKS (Strict Start/End, usually no cooldown needed) ---
            foreach ($vehicle->maintenances as $maintenance) {
                $maintStart = \Carbon\Carbon::parse($maintenance->start_time);
                $maintEnd   = \Carbon\Carbon::parse($maintenance->end_time);

                // Standard overlap check for maintenance
                if ($maintStart->lt($reqEnd) && $maintEnd->gt($reqStart)) {
                    return false; // Unavailable
                }
            }

            return true; // Available
        });
        
        $vehicles = $allAvailableVehicles->unique(function ($item) {
            return $item->brand . $item->model;
        });

        return view('bookings.search_results', compact('vehicles'));
    }

    // 2. STORE: Silent Assignment
    public function store(Request $request)
    {
        $reqStart = Carbon::parse($request->pickup_date . ' ' . $request->pickup_time);
        $reqEnd   = Carbon::parse($request->return_date . ' ' . $request->return_time);

        // Find ANY free car of the requested model
        $targetVehicle = Vehicle::where('model', $request->model)
            ->where('brand', $request->brand)
            ->where('availability', true)
            ->get()
            ->filter(function($v) use ($reqStart, $reqEnd) {
                return $this->isVehicleAvailable($v, $reqStart, $reqEnd);
            })
            ->first();

        if (!$targetVehicle) {
            return redirect()->route('book.search')->with('error', 'Sorry, ' . $request->model . ' is fully booked.');
        }

        // Redirect to Payment with the specific ID (Hidden from user view)
        return redirect()->route('book.payment', [
            'id' => $targetVehicle->VehicleID,
            'pickup_date' => $request->pickup_date,
            'return_date' => $request->return_date,
            'pickup_time' => $request->pickup_time,
            'return_time' => $request->return_time,
            'pickup_location' => $request->pickup_location,
            'return_location' => $request->return_location,
        ]);
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
        // --- 1. VALIDATION ---
        $request->validate([
            'payment_proof' => 'required|mimes:jpeg,png,jpg|max:4048',
            'agreement_proof' => 'required|file|mimes:pdf|max:4048',
            'payment_type' => 'required|in:full,deposit',
        ]);

        $vehicle = Vehicle::findOrFail($id);

        // --- 2. CALCULATE BASIC PRICE ---
        // Kira harga asal sewa tanpa diskaun
        $rentalCharge = $this->calculateRentalPrice(
            $vehicle, 
            $request->input('pickup_date'), $request->input('pickup_time'), 
            $request->input('return_date'), $request->input('return_time')
        );

        $baseDepo = $vehicle->baseDepo ?? 50;
        $grossTotal = $rentalCharge + $baseDepo;
        $discountAmount = 0;
        
        // Variable untuk simpan voucher object (kita update status 'used' di hujung function)
        $voucherToRedeem = null; 
        $voucherID_ToSave = null;

        // --- 3. VOUCHER LOGIC (MERGED & FIXED) ---
        if ($request->filled('voucherID')) { 
            $inputVoucherID = $request->input('voucherID');
            
            // Cari voucher guna Primary Key (voucherID)
            $voucher = \App\Models\Voucher::find($inputVoucherID);

            // Check kewujudan voucher DAN pastikan belum digunakan
            if ($voucher && !$voucher->isUsed) {
                
                // A. TENTUKAN JENIS "FREE HALF DAY"
                // Kita check sama ada type dia 'Free Half Day' ATAU dalam conditions ada tulis perkataan tu
                $isFreeHalfDay = $voucher->voucherType == 'Free Half Day' || str_contains(strtoupper($voucher->conditions ?? ''), 'FREE HALF DAY');

                // B. VALIDATION HARI (Isnin - Khamis sahaja untuk Rental Discount biasa)
                // Pengecualian: Kalau "Free Half Day", check logic dia sendiri (kalau nak allow weekend, buang check ni)
                $pickupDay = Carbon::parse($request->input('pickup_date'));
                
                // Carbon: 1=Isnin ... 5=Jumaat, 6=Sabtu, 7=Ahad
                // Logic asal: Rental Discount & Free Half Day (Loyalty) tak boleh guna Jumaat-Ahad
                if (($voucher->voucherType == 'Rental Discount' || $isFreeHalfDay) && $pickupDay->dayOfWeekIso > 4) {
                    return back()->with('error', 'Loyalty vouchers are invalid for this date (Mon-Thu only). Transaction cancelled.');
                }

                // C. SIMPAN ID UNTUK BOOKING
                $voucherID_ToSave = $voucher->voucherID;

                // D. KIRA DISKAUN (LOGIC GABUNGAN)
                if ($isFreeHalfDay) {
                    // ---------------------------------------------------------
                    // LOGIC 1: FREE HALF DAY (DYNAMIC)
                    // ---------------------------------------------------------
                    $discountAmount = 0;
                    try {
                        // Cuba ambil rate 12 jam dari JSON hourly_rates
                        $rates = $vehicle->hourly_rates;
                        
                        // Decode JSON jika perlu
                        if (is_string($rates)) {
                            $rates = json_decode($rates, true);
                        }

                        // Check jika rate '12' wujud
                        if (is_array($rates) && isset($rates['12'])) {
                            $discountAmount = (float)$rates['12']; 
                        } else {
                            // Fallback: Harga Sejam * 12
                            $discountAmount = ($vehicle->priceHour ?? 0) * 12;
                        }
                    } catch (\Exception $e) {
                        // Fallback keselamatan
                        $discountAmount = ($vehicle->priceHour ?? 0) * 12;
                    }
                    
                    // Pastikan diskaun tak lebih dari harga sewa (tak boleh negatif)
                    $discountAmount = min($discountAmount, $rentalCharge);

                } elseif ($voucher->discount_percent > 0) {
                    // ---------------------------------------------------------
                    // LOGIC 2: PERCENTAGE DISCOUNT (20%, 50% etc)
                    // ---------------------------------------------------------
                    $discountAmount = ($rentalCharge * $voucher->discount_percent) / 100;

                } else {
                    // ---------------------------------------------------------
                    // LOGIC 3: FIXED AMOUNT (RM 10 OFF)
                    // ---------------------------------------------------------
                    $discountAmount = $voucher->voucherAmount;
                }
                
                // Simpan object voucher dalam variable, JANGAN update DB lagi
                $voucherToRedeem = $voucher;
            }
        }

        // --- 4. CALCULATE FINAL TOTAL ---
        $finalTotalCost = max(0, $grossTotal - $discountAmount);

        // --- 5. DETERMINE PAYMENT AMOUNT ---
        if ($request->input('payment_type') == 'deposit') {
            $amountToPayNow = $baseDepo;
            $bookingStatus = 'Deposit Paid';
            
            // Safety: Kalau lepas diskaun, total lagi rendah dari deposit
            if ($amountToPayNow >= $finalTotalCost) {
                $amountToPayNow = $finalTotalCost;
                $bookingStatus = 'Submitted'; 
            }
        } else {
            $amountToPayNow = $finalTotalCost;
            $bookingStatus = 'Submitted';
        }

        // --- 6. GOOGLE DRIVE UPLOAD ---
        $timestamp = now()->format('Y-m-d - H-i');
        $userName = Auth::user()->fullName; 
        $fileNameBase = "[{$userName} - {$timestamp}]";

        // Upload Receipt
        $receiptFile = $request->file('payment_proof');
        $localProofPath = $receiptFile->store('receipts', 'public'); 
        
        // Upload Agreement
        $agreementFile = $request->file('agreement_proof');
        $localAgreementPath = $agreementFile->store('agreements', 'public');

        // Try Upload to Drive
        try {
            $receiptLink = $this->driveService->uploadFile(
                $receiptFile, env('GOOGLE_DRIVE_RECEIPTS'), $fileNameBase . " - Receipt"
            );
            $agreementLink = $this->driveService->uploadFile(
                $agreementFile, env('GOOGLE_DRIVE_AGREEMENTS'), $fileNameBase . " - Agreement"
            );
        } catch (\Exception $e) {
            \Log::error("Drive Upload Failed: " . $e->getMessage());
            $receiptLink = null;
            $agreementLink = null;
        }

        $finalReceiptPath = $receiptLink ?? $localProofPath;
        $finalAgreementPath = $agreementLink ?? $localAgreementPath;

        // --- 7. CREATE BOOKING ---
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
            'voucherID' => $voucherID_ToSave, // Masukkan ID voucher (atau null)
            
            'aggreementDate' => now(),
            'aggreementLink' => $finalAgreementPath, 
            
            'bookingStatus' => $bookingStatus,
            'bookingType' => 'Standard',
            'remarks' => $request->input('remarks'),
        ]);

        // --- 8. CREATE PAYMENT RECORD ---
        Payment::create([
            'bookingID' => $booking->bookingID,
            'amount' => $amountToPayNow, 
            'depoAmount' => $baseDepo,
            'transactionDate' => now(),
            'paymentMethod' => 'QR Transfer',
            'paymentStatus' => 'Pending Verification',
            'depoStatus' => 'Holding',
            'depoRequestDate' => now(),
            'installmentDetails' => $finalReceiptPath 
        ]);
        
        // --- 9. REDEEM VOUCHER SEKARANG ---
        // Kita update status voucher HANYA bila booking & payment dah berjaya create.
        if ($voucherToRedeem) {
            $voucherToRedeem->update([
                'isUsed' => true
            ]);
        }

        // --- 10. NOTIFICATIONS ---
        try {
            $staff = Staff::all(); 
            Notification::send($staff, new NewBookingSubmitted($booking));

            $booking->customer->notify(new BookingStatusUpdated(
                $booking, 
                "Your booking #{$booking->bookingID} has been submitted successfully."
            ));

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
