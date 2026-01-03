<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Vehicle;
use App\Models\Penalties;
use App\Models\Voucher; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Notifications\NewBookingSubmitted;
use App\Models\Staff;
use Illuminate\Support\Facades\Notification;

class BookingController extends Controller
{
    // --- 1. MY BOOKINGS PAGE ---
    public function index(Request $request)
    {
        $query = Booking::where('customerID', Auth::id())
                ->with(['vehicle', 'payments', 'penalties', 'inspections']); 
                
        if ($request->filled('status') && $request->status != 'all') {
            $query->where('bookingStatus', $request->status);
        }

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

        // 1. BLACKLIST CHECK
        if ($user->blacklisted) {
            $reason = $user->blacklist_reason ?? 'Violation of terms and conditions.';
            return redirect()->route('profile.edit')
                ->with('error', '⛔ ACTION BLOCKED: Your account is blacklisted. Reason: ' . $reason);
        }

        // 2. STRICT CHECK: Verify ALL profile fields
        if (
            empty($user->fullName) ||
            empty($user->email) ||
            empty($user->phoneNo) ||
            empty($user->emergency_contact_no) ||
            empty($user->emergency_contact_name) || 
            empty($user->homeAddress) ||
            empty($user->collegeAddress) ||
            empty($user->stustaffID) || 
            empty($user->ic_passport) || 
            empty($user->drivingNo) || 
            empty($user->nationality) ||
            empty($user->dob) ||
            empty($user->faculty) ||
            empty($user->bankName) ||
            empty($user->bankAccountNo)
        ) {
            return redirect()->route('profile.edit')
                ->with('error', '⚠️ Action Required: You must complete ALL profile details before booking.');
        }
        
        // 3. FETCH AVAILABLE VEHICLES FOR TODAY (Default View)
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
        // 1. Basic Validation
        $request->validate([
            'pickup_date' => 'required|date|after_or_equal:today',
            'return_date' => 'required|date|after_or_equal:pickup_date', 
        ]);

        $pickup = $request->pickup_date;
        $return = $request->return_date;

        // 2. Buffer Logic (1 Day Cooldown for turnover)
        $bufferPickup = \Carbon\Carbon::parse($pickup)->subDay();
        $bufferReturn = \Carbon\Carbon::parse($return)->addDay();

        // 3. Start Query Builder
        $query = Vehicle::where('availability', true);

        // --- A. BOOKING CHECK (Existing Logic) ---
        // Exclude vehicles booked during this time (Confirmed/Active bookings)
        $query->whereDoesntHave('bookings', function ($q) use ($bufferPickup, $bufferReturn) {
            $q->whereIn('bookingStatus', ['Submitted', 'Deposit Paid', 'Paid', 'Approved', 'Active', 'Confirmed']) // Added 'Confirmed' just in case
              ->where(function ($subQ) use ($bufferPickup, $bufferReturn) {
                  $subQ->whereBetween('originalDate', [$bufferPickup, $bufferReturn])
                       ->orWhereBetween('returnDate', [$bufferPickup, $bufferReturn])
                       ->orWhere(function ($inner) use ($bufferPickup, $bufferReturn) {
                           $inner->where('originalDate', '<', $bufferPickup)
                                 ->where('returnDate', '>', $bufferReturn);
                       });
              });
        });

        // --- B. MAINTENANCE CHECK (Added) ---
        // Exclude vehicles under maintenance during this time
        // Note: Using strict dates ($pickup/$return) instead of buffer for maintenance precision
        $query->whereDoesntHave('maintenances', function($q) use ($pickup, $return) {
            $q->whereDate('date', '>=', $pickup)
              ->whereDate('date', '<=', $return);
        });

        // Filter by Category (Car/Bike)
        if ($request->filled('category')) {
            $query->whereIn('vehicle_category', $request->category);
        }

        // Filter by Body Type
        if ($request->filled('types')) {
            $query->whereIn('type', $request->types);
        }

        // Filter by Price Range
        if ($request->filled('price_range')) {
            $query->where(function($q) use ($request) {
                foreach ($request->price_range as $range) {
                    [$min, $max] = explode('-', $range);
                    $q->orWhereBetween('priceHour', [(int)$min / 24, (int)$max / 24]);
                }
            });
        }

        // 5. Execute Query
        $vehicles = $query->get();

        // --- C. BLOCKED DATES CHECK (Added) ---
        // Filter out vehicles that have specific dates blocked in the calendar (JSON column)
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
        
        $rentalCharge = $this->calculateRentalPrice(
            $vehicle, 
            $request->pickup_date, $request->pickup_time, 
            $request->return_date, $request->return_time
        );

        $grandTotal = $rentalCharge + $vehicle->baseDepo;

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

    // --- 6. SUBMIT BOOKING ---
    public function submitPayment(Request $request, $id)
    {
        $request->validate([
            'payment_proof' => 'required|image|max:2048',
            'agreement_proof' => 'required|file|mimes:pdf|max:2048',
            'payment_type' => 'required|in:full,deposit',
        ]);

        $vehicle = Vehicle::findOrFail($id);

        $rentalCharge = $this->calculateRentalPrice(
            $vehicle, 
            $request->input('pickup_date'), $request->input('pickup_time'), 
            $request->input('return_date'), $request->input('return_time')
        );

        $baseDepo = $vehicle->baseDepo;
        $grossTotal = $rentalCharge + $baseDepo;
        
        // Voucher Logic
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

        // Payment Logic
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

        $proofPath = $request->file('payment_proof')->store('receipts', 'public');
        $agreementPath = $request->file('agreement_proof')->store('agreements', 'public');

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
            'aggreementLink' => $agreementPath,
            'bookingStatus' => $bookingStatus,
            'bookingType' => 'Standard',
            'remarks' => $request->input('remarks'),
        ]);

        Payment::create([
            'bookingID' => $booking->bookingID,
            'amount' => $amountToPayNow, 
            'depoAmount' => $baseDepo,
            'transactionDate' => now(),
            'paymentMethod' => 'QR Transfer',
            'paymentStatus' => 'Pending Verification',
            'depoStatus' => 'Holding',
            'depoRequestDate' => now(),
            'installmentDetails' => $proofPath
        ]);
        
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
        $allowedStatuses = ['Submitted', 'Deposit Paid', 'Paid', 'Approved'];

        if (in_array($booking->bookingStatus, $allowedStatuses)) {
            $booking->update(['bookingStatus' => 'Cancelled']);
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

    public function previewAgreement(Request $request)
    {
        $user = Auth::user();
        $vehicle = Vehicle::findOrFail($request->vehicle_id);
        
        $booking = new Booking();
        $booking->bookingID = "PENDING"; 
        $booking->aggreementDate = now();
        $booking->pickupLocation = $request->pickup_location;
        $booking->returnLocation = $request->return_location;
        $booking->returnDate = $request->return_date;
        $booking->returnTime = $request->return_time;
        
        $booking->setRelation('customer', $user);
        $booking->setRelation('vehicle', $vehicle);

        return view('bookings.agreement', compact('booking'));
    }

    // --- 9. UPLOAD INSPECTION ---
    public function uploadInspection(Request $request, $id)
    {
        $booking = Booking::where('customerID', Auth::id())->findOrFail($id);

        $type = ($booking->bookingStatus == 'Active' || $booking->bookingStatus == 'Completed') 
                ? 'Return' 
                : 'Pickup';

        $exists = \App\Models\Inspection::where('bookingID', $booking->bookingID)
                                        ->where('inspectionType', $type)
                                        ->whereNull('staffID') 
                                        ->exists();

        if ($exists) {
            return back()->with('error', "Action Failed: You have already submitted the $type inspection.");
        }

        $requiredCount = ($type == 'Pickup') ? 5 : 6;
        $typeName = $type;

        $request->validate([
            'photos' => "required|array|size:$requiredCount",
            'photos.*' => 'image|mimes:jpeg,png,jpg|max:2048',
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

        \App\Models\Inspection::create([
            'bookingID' => $booking->bookingID,
            'staffID' => null, 
            'inspectionType' => $type,
            'inspectionDate' => now(),
            'photosBefore' => $type == 'Pickup' ? json_encode($photoPaths) : null,
            'photosAfter' => $type == 'Return' ? json_encode($photoPaths) : null,
            'fuelBefore' => $type == 'Pickup' ? $request->input('fuel_level') : null,
            'fuelAfter' => $type == 'Return' ? $request->input('fuel_level') : null,
            'mileageBefore' => $type == 'Pickup' ? $request->input('mileage') : null,
            'mileageAfter' => $type == 'Return' ? $request->input('mileage') : null,
        ]);

        return back()->with('success', 'Inspection photos uploaded successfully!');
    }

    // --- HELPER METHODS ---
    private function calculateRentalPrice($vehicle, $pickupDate, $pickupTime, $returnDate, $returnTime)
    {
        $pickup = Carbon::parse($pickupDate . ' ' . $pickupTime);
        $return = Carbon::parse($returnDate . ' ' . $returnTime);

        $totalHours = $pickup->diffInHours($return);
        if ($pickup->diffInMinutes($return) > $totalHours * 60) {
            $totalHours++; 
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
        $totalPaid = $this->payments()->where('paymentStatus', 'Verified')->sum('amount');
        return max(0, $this->totalCost - $totalPaid); 
    }

    public function markNotificationsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return back()->with('success', 'Notifications cleared');
    }
}