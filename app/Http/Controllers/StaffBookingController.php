<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Staff;
use App\Models\Customer;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\BookingStatusUpdated;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use App\Services\GoogleDriveService;
use Illuminate\Support\Facades\Log;

class StaffBookingController extends Controller
{
    protected $driveService;

    // 1. Inject the Service
    public function __construct(GoogleDriveService $driveService)
    {
        $this->driveService = $driveService;
    }

    public function dashboard(Request $request)
    {
        // 1. === GLOBAL METRICS ===
        $totalRevenue = Booking::where('bookingStatus', '!=', 'Cancelled')->sum('totalCost');
        
        // Revenue Growth
        $thisMonthRev = Booking::where('bookingStatus', '!=', 'Cancelled')
            ->whereMonth('created_at', Carbon::now()->month)
            ->sum('totalCost');
            
        $lastMonthRev = Booking::where('bookingStatus', '!=', 'Cancelled')
            ->whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->sum('totalCost');

        $revenueGrowth = ($lastMonthRev > 0) ? round((($thisMonthRev - $lastMonthRev) / $lastMonthRev) * 100, 1) : ($thisMonthRev > 0 ? 100 : 0);

        // Counts
        $activeRentalsCount = Booking::whereIn('bookingStatus', ['Active', 'Ongoing', 'Picked Up'])->count();
        $pendingBookingsCount = Booking::whereIn('bookingStatus', ['Pending', 'Submitted', 'Deposit Paid'])->count();
        $totalCustomers = \App\Models\Customer::count(); 

        // 2. === CHART DATA ===
        $period = $request->input('chart_period', 'daily'); 
        $chartLabels = [];
        $chartRevenue = [];
        $chartBookings = [];

        if ($period === 'monthly') {
            for ($i = 5; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $chartLabels[] = $date->format('M Y');
                $stats = Booking::whereYear('created_at', $date->year)->whereMonth('created_at', $date->month)->where('bookingStatus', '!=', 'Cancelled')
                    ->selectRaw('sum(totalCost) as total_money, count(*) as total_count')->first();
                $chartRevenue[] = $stats->total_money ?? 0;
                $chartBookings[] = $stats->total_count ?? 0;
            }
        } elseif ($period === 'weekly') {
            for ($i = 7; $i >= 0; $i--) {
                $date = Carbon::now()->subWeeks($i);
                $startOfWeek = $date->copy()->startOfWeek();
                $endOfWeek = $date->copy()->endOfWeek();
                $chartLabels[] = $startOfWeek->format('d M') . ' - ' . $endOfWeek->format('d M');
                $stats = Booking::whereBetween('created_at', [$startOfWeek, $endOfWeek])->where('bookingStatus', '!=', 'Cancelled')
                    ->selectRaw('sum(totalCost) as total_money, count(*) as total_count')->first();
                $chartRevenue[] = $stats->total_money ?? 0;
                $chartBookings[] = $stats->total_count ?? 0;
            }
        } else {
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i);
                $chartLabels[] = $date->format('d M');
                $stats = Booking::whereDate('created_at', $date)->where('bookingStatus', '!=', 'Cancelled')
                    ->selectRaw('sum(totalCost) as total_money, count(*) as total_count')->first();
                $chartRevenue[] = $stats->total_money ?? 0;
                $chartBookings[] = $stats->total_count ?? 0;
            }
        }

        // 3. === OPERATIONAL LISTS ===
        $today = Carbon::today();
        $pickupsToday = Booking::whereDate('originalDate', $today)->where('bookingStatus', '!=', 'Cancelled')->get();
        $returnsToday = Booking::whereDate('returnDate', $today)->where('bookingStatus', '!=', 'Cancelled')->get();
        $recentBookings = Booking::latest()->take(5)->get();
        
        // 4. === FLEET PULSE ===
        $totalVehicles = Vehicle::count();
        $activeVehicles = Booking::whereIn('bookingStatus', ['Active', 'Ongoing'])->count();
        $utilizationRate = $totalVehicles > 0 ? ($activeVehicles / $totalVehicles) * 100 : 0;
        $maintenanceRate = 5; 
        $todayRevenue = Booking::whereDate('created_at', $today)->where('bookingStatus', '!=', 'Cancelled')->sum('totalCost');
        $overdueCount = Booking::where('returnDate', '<', $today)->whereIn('bookingStatus', ['Active', 'Ongoing'])->count();

        // 5. === CALENDAR EVENTS ===
        $calendarEvents = [];
        $allBookings = Booking::with(['vehicle', 'customer'])
            ->where('bookingStatus', '!=', 'Cancelled')
            ->get();

        foreach ($allBookings as $b) {
            $calendarEvents[] = [
                'id' => $b->bookingID,
                'title' => $b->vehicle->plateNo ?? 'Unknown', // <--- JUST PLATE NO
                'start' => $b->originalDate . 'T' . $b->bookingTime,
                'end' => $b->returnDate . 'T' . $b->returnTime,
                'extendedProps' => [
                    'type' => 'booking',
                    'plate' => $b->vehicle->plateNo ?? 'Unknown',
                    'status' => $b->bookingStatus,
                    'customer_name' => $b->customer->fullName ?? 'Guest' // Store Name Here for Popup
                ]
            ];
        }

        // 6. === SEARCH AVAILABILITY ===
        $searchResults = null;
        $vehicleModels = Vehicle::select('model')->distinct()->pluck('model');

        if ($request->has('pickup_date') && $request->has('return_date')) {
            $pDate = $request->pickup_date . ' ' . ($request->pickup_time ?? '09:00:00');
            $rDate = $request->return_date . ' ' . ($request->return_time ?? '09:00:00');
            $reqModel = $request->model;

            // Note: 'originalDate' is used for pickup in your DB schema
            $searchResults = Vehicle::where('availability', 1) // Assuming 1 = available
                ->whereDoesntHave('bookings', function($q) use ($pDate, $rDate) {
                    $q->where(function($query) use ($pDate, $rDate) {
                        $query->whereBetween('originalDate', [$pDate, $rDate])
                              ->orWhereBetween('returnDate', [$pDate, $rDate])
                              ->orWhere(function($sub) use ($pDate, $rDate) {
                                  $sub->where('originalDate', '<', $pDate)
                                      ->where('returnDate', '>', $rDate);
                              });
                    })->where('bookingStatus', '!=', 'Cancelled'); // FIX
                })
                ->when($reqModel && $reqModel != 'all', function($q) use ($reqModel) {
                    return $q->where('model', $reqModel);
                })
                ->get();
        }

        return view('staff.dashboard', compact(
            'totalRevenue', 'revenueGrowth', 'activeRentalsCount', 'pendingBookingsCount',
            'totalCustomers', 'chartLabels', 'chartRevenue', 'chartBookings',
            'pickupsToday', 'returnsToday', 'recentBookings', 
            'totalVehicles', 'utilizationRate', 'maintenanceRate', 'todayRevenue', 'overdueCount',
            'vehicleModels', 'searchResults', 'calendarEvents' 
        ));
    }

    // --- 2. LIST ALL BOOKINGS ---
    public function index(Request $request, )
    {
        $query = Booking::with(['customer', 'vehicle', 'payment', 'payments']) 
                           ->orderBy('created_at', 'desc');
        
        if ($request->filled('status') && $request->status != 'all') {
            $query->where('bookingStatus', $request->status);
        }

        // Search Logic
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function($q) use ($term) {
                // Search by Booking ID
                $q->where('bookingID', 'like', "%$term%")
                  // Search by Customer Name or Email
                  ->orWhereHas('customer', function($c) use ($term) {
                      $c->where('fullName', 'like', "%$term%")
                        ->orWhere('email', 'like', "%$term%");
                  })
                  // Search by Vehicle Model or Plate
                  ->orWhereHas('vehicle', function($v) use ($term) {
                      $v->where('model', 'like', "%$term%")
                        ->orWhere('plateNo', 'like', "%$term%");
                  });
            });
        }

        $bookings = $query->get();

        return view('staff.bookings.index', compact('bookings'));
    }

    // --- 3. VERIFY PAYMENT (Handles Balance Payment too) ---
    public function verifyPayment($id)
    {
        $booking = Booking::findOrFail($id);
        
        // Find the latest pending payment (This catches the new balance receipt)
        $payment = Payment::where('bookingID', $id)
                          ->where('paymentStatus', 'Pending Verification')
                          ->latest()
                          ->first();

        if ($payment) {
            $payment->update(['paymentStatus' => 'Verified']);
            return back()->with('success', 'Payment verified. You can now Approve/Confirm the booking.');
        }

        return back()->with('error', 'No pending payment found.');
    }

    // --- 4. APPROVE / CONFIRM ---
    public function approveAgreement($id)
    {
        $booking = Booking::with('customer', 'vehicle')->findOrFail($id);
        // Check if there are any unverified payments
        $pendingPayments = Payment::where('bookingID', $id)
                                  ->where('paymentStatus', 'Pending Verification')
                                  ->exists();

        if ($pendingPayments) {
            return back()->with('error', 'Please verify all pending payments first.');
        }

        // Set status to Confirmed (This is what you wanted)
        $booking->update([
            'bookingStatus' => 'Confirmed', 
            'staffID' => $booking->staffID ?? Auth::guard('staff')->id(), 
            'aggreementDate' => now(),
        ]);
        // Notify Customer
        try {
            $booking->customer->notify(new BookingStatusUpdated(
                $booking, 
                "Great news! Your booking #{$booking->bookingID} has been CONFIRMED. Please arrive on time for pickup."
            ));
        } catch (\Exception $e) {
            \Log::error("Confirmation Email Failed: " . $e->getMessage());
        }
        
        return back()->with('success', 'Booking is CONFIRMED and email sent.');
    }

    // ... pickup, processReturn, processRefund, storeInspection, assignStaff, show ...
    public function show($id) {
        // FIX: Added 'feedback' to the with() list
        $booking = Booking::with(['customer', 'vehicle', 'payment', 'inspections', 'staff', 'feedback'])
                          ->findOrFail($id);

    // 2. [NEW] Find Available Alternatives (Same Model, Different Plate)
    // Only needed if booking is not yet completed
    $availableCars = collect(); // Default empty collection

    if (in_array($booking->bookingStatus, ['Submitted', 'Deposit Paid', 'Paid', 'Confirmed'])) {
        
        // Get Booking Dates
        $reqStart = \Carbon\Carbon::parse($booking->originalDate . ' ' . $booking->bookingTime);
        $reqEnd   = \Carbon\Carbon::parse($booking->returnDate . ' ' . $booking->returnTime);

        // Find cars: Same Model + Same Brand + Not the current one + Available
        $availableCars = \App\Models\Vehicle::where('model', $booking->vehicle->model)
            ->where('brand', $booking->vehicle->brand)
            ->where('availability', true)
            ->where('VehicleID', '!=', $booking->vehicleID) // Don't show the one already assigned
            ->get()
            ->filter(function($v) use ($reqStart, $reqEnd) {
                // Check if this specific car is free during the booking time
                return $this->isVehicleAvailable($v, $reqStart, $reqEnd);
            });
    }

        $allStaff = Staff::all();
        return view('staff.bookings.show', compact('booking', 'allStaff', 'availableCars'));
    }

    private function isVehicleAvailable($vehicle, $start, $end) {
    $endBuffer = $end->copy()->addHours(3); // 3 Hour Buffer
    
    foreach ($vehicle->bookings as $b) {
        if (in_array($b->bookingStatus, ['Cancelled', 'Rejected'])) continue;
        
        $bStart = \Carbon\Carbon::parse($b->originalDate . ' ' . $b->bookingTime);
        $bEnd = \Carbon\Carbon::parse($b->returnDate . ' ' . $b->returnTime)->addHours(3);
        
        // Check overlap
        if ($start->lt($bEnd) && $endBuffer->gt($bStart)) return false;
    }
    return true;
}

// [NEW] APPROVE
    public function approve($id)
    {
        $booking = Booking::findOrFail($id);
        $booking->bookingStatus = 'Confirmed';
        $booking->save();
        
        // Notification logic here...

        return back()->with('success', 'Booking Confirmed!');
    }

    public function pickup(Request $request, $id) {
        $booking = Booking::findOrFail($id);
        $booking->update(['bookingStatus' => 'Active']);
        return back()->with('success', 'Vehicle collected.');
    }
    public function processReturn(Request $request, $id) {
        $booking = Booking::findOrFail($id);
        
        // 1. Update Status to Completed
        $booking->update([
            'bookingStatus' => 'Completed',
            'actualReturnDate' => now()->toDateString(),
            'actualReturnTime' => now()->toTimeString(),
        ]);

        // 2. Refund Deposit & Complete Payment
        if ($booking->payment) {
            $booking->payment->update(['depoStatus' => 'Refunded', 'paymentStatus' => 'Completed']);
        }

        // 3. Trigger Loyalty Points
        $loyaltyController = new \App\Http\Controllers\LoyaltyController();
        $loyaltyController->bookingCompleted($id);

        try {
            // A. Invoice Generation & Email
            $booking->loadMissing(['customer', 'vehicle', 'payment']);
            $pdf = Pdf::loadView('pdf.invoice', compact('booking'));
            $pdfContent = $pdf->output(); 

            // Send Invoice via System Mailer
            Mail::send([], [], function ($message) use ($booking, $pdfContent) {
                $message->to($booking->customer->email)
                        ->subject('Invoice for Booking #' . $booking->bookingID)
                        ->attachData($pdfContent, 'Invoice-'.$booking->bookingID.'.pdf', [
                            'mime' => 'application/pdf',
                        ]);
            });

            // B. Send Explicit Notification about Completion & Deposit Refund
            // This triggers the BookingStatusUpdated notification email
            $booking->customer->notify(new BookingStatusUpdated(
                $booking, 
                "Your vehicle return is verified. Booking #{$booking->bookingID} is COMPLETED and your deposit has been REFUNDED."
            ));

            // C. Upload to Google Drive & Save Link (NEW)
            // Use app() to resolve the service without constructor injection
            $driveService = app(\App\Services\GoogleDriveService::class);
            
            $timestamp = now()->format('Ymd_Hi');
            $safeName = preg_replace('/[^A-Za-z0-9 ]/', '', $booking->customer->fullName);
            $fileName = "Invoice_{$booking->bookingID}_{$safeName}_{$timestamp}.pdf";

            // Upload using the raw content method
            // Uses GOOGLE_DRIVE_INVOICES from your .env
            $invoiceLink = $driveService->uploadFromString(
                $pdfContent, 
                $fileName, 
                env('GOOGLE_DRIVE_INVOICES') 
            );

            // D. Save Link to Database for Customer Dashboard
            if ($invoiceLink) {
                $booking->invoiceLink = $invoiceLink;
                $booking->save();
            }
            
        } catch (\Exception $e) {
            // Log error but allow the return process to finish
            \Log::error("Invoice Generation/Upload Failed: " . $e->getMessage());
        }

        return back()->with('success', 'Vehicle returned, Loyalty Points Awarded & Invoice Sent.');
    }
    public function processRefund(Request $request, $id) {
        $booking = Booking::findOrFail($id);
        
        if ($booking->payment && $booking->payment->depoStatus == 'Requested') {
            
            // 1. Handle Remarks (if provided)
            if ($request->filled('refund_remarks')) {
                $oldRemarks = $booking->remarks ? $booking->remarks . "\n\n" : "";
                // Add a timestamped note
                $refundNote = "[REFUND " . now()->format('d/m/y H:i') . "]: " . $request->refund_remarks;
                $booking->update(['remarks' => $oldRemarks . $refundNote]);
            }

            // 2. Update Status
            $booking->payment->update([
                'depoStatus' => 'Refunded', 
                'paymentStatus' => 'Refund Completed',
                'depoRefundedDate' => now()
            ]);
            
            // 3. Notify Customer (Optional: Pass remarks to notification if your notification class supports it)
            try {
                $booking->customer->notify(new BookingStatusUpdated(
                    $booking, 
                    "Your refund request for booking #{$booking->bookingID} has been processed successfully."
                ));
            } catch (\Exception $e) {
                \Log::error("Refund Email Failed: " . $e->getMessage());
            }
            
            return back()->with('success', 'Refund issued successfully.');
        }
        
        return back()->with('error', 'Error processing refund.');
    }

    // INSPECTION UPLOAD
    public function storeInspection(Request $request, $id) {
        $booking = Booking::with('vehicle')->findOrFail($id);
        $type = $request->input('type');

        // 1. Determine photo requirements
        $requiredCount = ($type == 'Pickup') ? 5 : 6;
        
        // 2. Validate inputs (Matches your Modal fields)
        $request->validate([
            'type' => 'required',
            'photos' => "required|array|size:$requiredCount",
            'photos.*' => 'image|max:4048',
            'mileage' => 'required|numeric',
            'fuel_level' => 'required',
        ], [
            'photos.size' => "Exactly $requiredCount photos are required for $type inspection."
        ]);

        // 3. Handle File Uploads
        $photoPaths = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $photoPaths[] = $photo->store('inspections', 'public');
            }
        }
        $photoString = json_encode($photoPaths);

        // 4. PREPARE REMARKS & UPDATE FLEET
        $remarks = "";
        
        // Only update vehicle mileage if it is a "Return" inspection
        if ($type == 'Return') {
            // A. Update the Master Vehicle Record
            $booking->vehicle->update([
                'mileage' => $request->mileage
            ]);

            // B. Calculate Mileage Used (History Lookup)
            // We look for the "Pickup" inspection for this specific booking
            $pickupInspection = \App\Models\Inspection::where('bookingID', $booking->bookingID)
                ->where('inspectionType', 'Pickup')
                ->latest()
                ->first();

            // Determine start mileage: usage from Pickup Inspection, fallback to 0 if missing
            $startMileage = $pickupInspection ? ($pickupInspection->mileageBefore ?? $pickupInspection->mileageAfter) : 0;

            if ($startMileage > 0) {
                $diff = $request->mileage - $startMileage;
                $remarks = "[MILEAGE REPORT]\nStart: {$startMileage} km\nEnd: {$request->mileage} km\nTotal Used: {$diff} km";
            }
        }

        // 5. Create Inspection Record
        \App\Models\Inspection::create([
            'bookingID' => $booking->bookingID,
            'staffID' => Auth::guard('staff')->id(), 
            'inspectionType' => $type,
            'inspectionDate' => now(),
            
            // Map the new fields
            'fuelBefore' => ($type == 'Pickup') ? $request->fuel_level : null,
            'fuelAfter' => ($type == 'Return') ? $request->fuel_level : null,
            'mileageBefore' => ($type == 'Pickup') ? $request->mileage : null,
            'mileageAfter' => ($type == 'Return') ? $request->mileage : null,
            
            'photosBefore' => ($type == 'Pickup') ? $photoString : null,
            'photosAfter' => ($type == 'Return') ? $photoString : null,

            'remarks' => $remarks, // Save the calculated mileage text here
        ]);

        return back()->with('success', 'Inspection uploaded & vehicle mileage updated.');
    }

    // ASSIGN STAFF TO BOOKING
    public function assignStaff(Request $request, $id) {
        $request->validate(['staff_id' => 'required']);
        Booking::findOrFail($id)->update(['staffID' => $request->staff_id]);
        return back()->with('success', "Agent assigned.");
    }

    public function storeDynamicPricing(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'percentage' => 'required|numeric|min:-100|max:100', // [CHANGED] Allow negative for discounts
        ]);

        $rules = Cache::get('dynamic_pricing_rules', []);

        $rules[] = [
            'start' => $request->start_date,
            'end'   => $request->end_date,
            'percent' => $request->percentage,
            'created_at' => now(),
            'author' => Auth::guard('staff')->user()->name ?? 'Staff'
        ];

        Cache::put('dynamic_pricing_rules', $rules);

        $type = $request->percentage > 0 ? "Surcharge (+{$request->percentage}%)" : "Discount ({$request->percentage}%)";
        return back()->with('success', "Rule Added: $type from {$request->start_date} to {$request->end_date}.");
    }

    public function deleteDynamicPricingRule($index)
    {
        $rules = Cache::get('dynamic_pricing_rules', []);
        
        if (isset($rules[$index])) {
            unset($rules[$index]);
            Cache::put('dynamic_pricing_rules', array_values($rules));
            return back()->with('success', 'Pricing rule removed.');
        }
        
        return back()->with('error', 'Rule not found.');
    }

    public function clearDynamicPricing()
    {
        Cache::forget('dynamic_pricing_rules');
        return back()->with('success', 'All dynamic pricing rules have been reset.');
    }

    // [NEW] UPDATE VEHICLE (Swap Plate No)
    public function updateVehicle(Request $request, $id)
    {
        // 1. Find the booking
        $booking = Booking::findOrFail($id);
        
        // 2. Validate the new Vehicle ID exists in your database
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,VehicleID'
        ]);

        // 3. FORCE UPDATE the vehicle ID
        // We set it directly and call save() to ensure it persists
        $booking->vehicleID = $request->vehicle_id;
        
        // Optional: If you want this action to also Confirm the booking immediately
        // Uncomment the line below. Otherwise, it just swaps the car.
        // $booking->bookingStatus = 'Confirmed'; 

        $booking->save();

        // 4. (Optional) Notify Customer about the change
        try {
            // Reload the vehicle relation so the email shows the NEW plate
            $booking->refresh(); 
            
            $booking->customer->notify(new \App\Notifications\BookingStatusUpdated(
                $booking, 
                "Vehicle update: Your booking #{$booking->bookingID} is now assigned to plate {$booking->vehicle->plateNo}."
            ));
        } catch (\Exception $e) {
            // Ignore notification errors
        }

        return back()->with('success', 'Vehicle successfully swapped to ' . $booking->vehicle->plateNo);
    }

    // [NEW] REJECT BOOKING (Handle Fraud vs Refund)
    public function reject(Request $request, $id)
    {
        $request->validate([
            'reject_action' => 'required|in:fraud,refund',
            'reason' => 'required|string|max:255',
        ]);

        $booking = Booking::with('payment')->findOrFail($id);
        $payment = $booking->payment;

        // 1. Prepare the new Remarks string
        // We keep the old remarks (if any) and add the Rejection Reason on a new line
        $oldRemarks = $booking->remarks ? $booking->remarks . "\n\n" : "";
        $rejectionNote = "[REJECTED]: " . $request->reason;
        
        $finalRemarks = $oldRemarks . $rejectionNote;

        $statusMessage = "";

        if ($request->reject_action === 'fraud') {
            // CASE 1: FRAUD
            $booking->update([
                'bookingStatus' => 'Rejected',
                'remarks' => $finalRemarks // <--- Uses the combined string
            ]);

            if ($payment) {
                $payment->update([
                    'paymentStatus' => 'Rejected',
                    'depoStatus' => 'Void',
                ]);
            }
            $statusMessage = "Booking rejected as Fraud.";

        } elseif ($request->reject_action === 'refund') {
            // CASE 2: REFUND
            $booking->update([
                'bookingStatus' => 'Rejected',
                'remarks' => $finalRemarks // <--- Uses the combined string
            ]);

            if ($payment) {
                $payment->update([
                    'paymentStatus' => 'Refund Completed',
                    'depoStatus' => 'Refunded',
                    'depoRefundedDate' => now(),
                ]);
            }
            $statusMessage = "Booking rejected & Refunded.";
        }

        // Notify Customer
        try {
            $booking->customer->notify(new BookingStatusUpdated(
                $booking, 
                "Your booking #{$booking->bookingID} was REJECTED. Reason: " . $request->reason
            ));
        } catch (\Exception $e) {
            \Log::error("Rejection Email Failed: " . $e->getMessage());
        }
        return back()->with('success', $statusMessage);
    }

    // mark notification as read
    public function markAsRead($id) {
        $notification = auth()->guard('staff')->user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        return back();
    }

    // STREAM INVOICE AS PDF
    public function streamInvoice($id)
    {
        // 1. Find booking (No customerID check needed for staff)
        $booking = Booking::with(['customer', 'vehicle', 'payment', 'voucher'])
                    ->findOrFail($id);

        // 2. Generate and Stream
        // Staff can preview it even if not strictly "Completed" yet if needed, 
        // but typically invoice is for completed/paid jobs.
        $pdf = Pdf::loadView('pdf.invoice', compact('booking'));
        return $pdf->stream('Invoice-' . $booking->bookingID . '.pdf');
    }
}