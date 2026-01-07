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

class StaffBookingController extends Controller
{
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
        $booking = Booking::findOrFail($id);

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
        $booking->customer->notify(new BookingStatusUpdated($booking, "Your booking #{$booking->bookingID} has been CONFIRMED!"));
        
        return back()->with('success', 'Booking is CONFIRMED.');
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
        $booking->update([
            'bookingStatus' => 'Completed',
            'actualReturnDate' => now()->toDateString(),
            'actualReturnTime' => now()->toTimeString(),
        ]);
        if ($booking->payment) {
            $booking->payment->update(['depoStatus' => 'Refunded', 'paymentStatus' => 'Completed']);
        }
        $loyaltyController = new \App\Http\Controllers\LoyaltyController();
        $loyaltyController->bookingCompleted($id);

        try {
            $pdf = Pdf::loadView('pdf.invoice', compact('booking'));
            
            // 1. (Optional) Save to storage
            // Storage::put('public/invoices/INV-' . $booking->bookingID . '.pdf', $pdf->output());

            // 2. Email to Customer
            Mail::send([], [], function ($message) use ($booking, $pdf) {
                $message->to($booking->customer->email)
                        ->subject('Invoice for Booking #' . $booking->bookingID)
                        ->attachData($pdf->output(), 'Invoice-'.$booking->bookingID.'.pdf', [
                            'mime' => 'application/pdf',
                        ]);
            });
           // 3. Upload to Google Drive (NEW LOGIC)
            // Connect to the specific Invoice Folder
            $googleDisk = Storage::build([
                'driver' => 'google',
                'clientId' => env('GOOGLE_DRIVE_CLIENT_ID'),
                'clientSecret' => env('GOOGLE_DRIVE_CLIENT_SECRET'),
                'refreshToken' => env('GOOGLE_DRIVE_REFRESH_TOKEN'),
                'folderId' => env('GOOGLE_DRIVE_INVOICE'), // Uses the ID you provided
            ]);

            // Define Folder Name (Creates new folder automatically if it doesn't exist)
            // Format: "Booking #123 - Customer Name"
            $folderName = 'Booking #' . $booking->bookingID . ' - ' . preg_replace('/[^A-Za-z0-9 ]/', '', $booking->customer->fullName);
            $fileName = 'Invoice-' . $booking->bookingID . '.pdf';

            // Upload the file
            $googleDisk->put($folderName . '/' . $fileName, $pdfContent);

        } catch (\Exception $e) {
            \Log::error("Invoice Generation/Upload Failed: " . $e->getMessage());
        }
        return back()->with('success', 'Vehicle returned & Loyalty Points Awarded.');
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
            // $booking->customer->notify(new \App\Notifications\BookingStatusUpdated($booking, "Your refund has been processed."));

            return back()->with('success', 'Refund issued successfully.');
        }
        
        return back()->with('error', 'Error processing refund.');
    }
    public function storeInspection(Request $request, $id) {
        $requiredCount = ($request->type == 'Pickup') ? 5 : 6;
        $request->validate([
                'type' => 'required',
                'photos' => "required|array|size:$requiredCount",
                'photos.*' => 'image|max:4048',
            ], [
                'photos.size' => "Exactly $requiredCount photos are required for $request->type inspection."
            ]);        
        $booking = Booking::findOrFail($id);
        $photoPaths = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) $photoPaths[] = $photo->store('inspections', 'public');
        }
        \App\Models\Inspection::create([
            'bookingID' => $booking->bookingID,
            'staffID' => Auth::guard('staff')->id(), 
            'inspectionType' => $request->type,
            'inspectionDate' => now(),
            'photosBefore' => $request->type == 'Pickup' ? json_encode($photoPaths) : null,
            'photosAfter' => $request->type == 'Return' ? json_encode($photoPaths) : null,
        ]);
        return back()->with('success', 'Inspection uploaded.');
    }
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
        $booking->customer->notify(new BookingStatusUpdated($booking, "Your booking #{$booking->bookingID} was rejected. Reason: " . $request->reason));

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