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
    // --- 1. STAFF DASHBOARD ---
    public function dashboard(Request $request)
    {
        // ==========================================
        // 1. FLEET PULSE & UTILIZATION (For Pie/Bar Widget)
        // ==========================================
        $totalVehicles = \App\Models\Vehicle::count();
        
        $activeRentalsCount = Booking::where('bookingStatus', 'Active')->count();
        
        // Count vehicles currently in maintenance (Start <= Now <= End)
        $maintenanceCount = \App\Models\Maintenance::where('start_time', '<=', now())
                                           ->where('end_time', '>=', now())
                                           ->count();

        // Calculate Percentages (Prevent Division by Zero)
        $utilizationRate = $totalVehicles > 0 ? ($activeRentalsCount / $totalVehicles) * 100 : 0;
        $maintenanceRate = $totalVehicles > 0 ? ($maintenanceCount / $totalVehicles) * 100 : 0;


        // ==========================================
        // 2. CRITICAL ALERTS & FINANCIALS
        // ==========================================
        $pendingBookingsCount = Booking::whereIn('bookingStatus', ['Submitted', 'Deposit Paid'])->count();
        $totalCustomers = \App\Models\Customer::count();
        
        // Total Lifetime Revenue
        $totalRevenue = Payment::where('paymentStatus', 'Verified')->sum('amount');
        $revenueGrowth = 12; // Static placeholder for UI

        // Revenue Collected TODAY (Verified today)
        $todayRevenue = Payment::where('paymentStatus', 'Verified')
                               ->whereDate('updated_at', Carbon::today())
                               ->sum('amount');

        // Overdue Returns Logic
        $overdueCount = Booking::where('bookingStatus', 'Active')
            ->where(function($q) {
                // Return Date is in the past (Yesterday or before)
                $q->where('returnDate', '<', now()->toDateString())
                  // OR Return Date is Today BUT Time has passed
                  ->orWhere(function($sub) {
                      $sub->where('returnDate', '=', now()->toDateString())
                          ->where('returnTime', '<', now()->format('H:i:s'));
                  });
            })
            ->count();


        // ==========================================
        // 3. OPERATIONAL LISTS (Tabs)
        // ==========================================
        $today = Carbon::today();
        
        // Pickups Today: Confirmed/Paid bookings starting today
        $pickupsToday = Booking::with(['customer', 'vehicle'])
            ->whereDate('originalDate', $today)
            ->whereIn('bookingStatus', ['Confirmed', 'Paid']) 
            ->orderBy('bookingTime', 'asc')
            ->get();

        // Returns Today: Active bookings ending today
        $returnsToday = Booking::with(['customer', 'vehicle'])
            ->whereDate('returnDate', $today)
            ->where('bookingStatus', 'Active') 
            ->orderBy('returnTime', 'asc')
            ->get();

        // Recent Activity (Last 5)
        $recentBookings = Booking::with(['customer', 'vehicle'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();


        // ==========================================
        // 4. CHART DATA (Last 7 Days)
        // ==========================================
        $chartLabels = [];
        $chartRevenue = [];
        $chartBookings = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $chartLabels[] = $date->format('d M'); // "05 Jan"
            
            // Daily Revenue
            $chartRevenue[] = Payment::whereDate('transactionDate', $date)
                ->where('paymentStatus', 'Verified')
                ->sum('amount');
                
            // Daily Bookings
            $chartBookings[] = Booking::whereDate('created_at', $date)->count();
        }


        // ==========================================
        // 5. AVAILABILITY CHECKER LOGIC
        // ==========================================
        $searchResults = null;
        $searchParams = null;

        if ($request->filled(['pickup_date', 'return_date'])) {
            // Parse inputs with fallback time if not provided
            $pickupTimeStr = $request->pickup_time ?? '09:00';
            $returnTimeStr = $request->return_time ?? '09:00';

            $reqStart = Carbon::parse($request->pickup_date . ' ' . $pickupTimeStr);
            $reqEnd   = Carbon::parse($request->return_date . ' ' . $returnTimeStr);
            
            // Query Available Vehicles
            $searchResults = \App\Models\Vehicle::where('availability', true)
                ->where(function($q) use ($request) {
                    if($request->filled('model') && $request->model != 'all') {
                        $q->where('model', $request->model);
                    }
                })
                ->with(['bookings', 'maintenances'])
                ->get()
                ->filter(function($vehicle) use ($reqStart, $reqEnd) {
                    
                    // Define Requested Block with 3-hour buffer
                    $reqEndWithBuffer = $reqEnd->copy()->addHours(3);

                    // A. Check Bookings Overlap
                    foreach ($vehicle->bookings as $booking) {
                        if (in_array($booking->bookingStatus, ['Cancelled', 'Rejected'])) continue;
                        
                        $bookStart = Carbon::parse($booking->originalDate . ' ' . $booking->bookingTime);
                        $bookEnd   = Carbon::parse($booking->returnDate . ' ' . $booking->returnTime);
                        $bookEndWithBuffer = $bookEnd->copy()->addHours(3);

                        // 2-Way Conflict Check
                        if ($reqStart->lt($bookEndWithBuffer) && $reqEndWithBuffer->gt($bookStart)) {
                            return false; 
                        }
                    }

                    // B. Check Maintenance Overlap
                    foreach ($vehicle->maintenances as $maint) {
                        $mStart = Carbon::parse($maint->start_time);
                        $mEnd   = Carbon::parse($maint->end_time);
                        
                        if ($mStart->lt($reqEnd) && $mEnd->gt($reqStart)) {
                            return false; 
                        }
                    }

                    return true; // Vehicle Available
                });
            
            $searchParams = $request->all();
        }

        // Dropdown options
        $vehicleModels = \App\Models\Vehicle::select('model')->distinct()->pluck('model');

        return view('staff.dashboard', compact(
            'activeRentalsCount', 'pendingBookingsCount', 'totalCustomers', 
            'totalRevenue', 'revenueGrowth', 
            'totalVehicles', 'maintenanceCount', 'utilizationRate', 'maintenanceRate',
            'overdueCount', 'todayRevenue',
            'pickupsToday', 'returnsToday', 'recentBookings',
            'chartLabels', 'chartRevenue', 'chartBookings',
            'vehicleModels', 'searchResults', 'searchParams'
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
                          
        $allStaff = Staff::all();
        return view('staff.bookings.show', compact('booking', 'allStaff'));
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