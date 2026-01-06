<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Staff; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\BookingStatusUpdated;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class StaffBookingController extends Controller
{
    // --- 1. STAFF DASHBOARD ---
    public function dashboard()
    {
        $activeRentals = Booking::where('bookingStatus', 'Active')->count(); 
        
        // Count anything that needs attention
        $pendingCount = Booking::whereIn('bookingStatus', ['Submitted', 'Deposit Paid'])->count();
        
        $revenue = Payment::sum('amount'); 
        
        $overdueCount = Booking::where('bookingStatus', 'Active')
                                ->where('returnDate', '<', now())
                                ->count();

        $dueReturns = Booking::with('vehicle')
                             ->where('bookingStatus', 'Active') 
                             ->orderBy('returnDate', 'asc')
                             ->take(3)
                             ->get();

        return view('staff.dashboard', compact('activeRentals', 'pendingCount', 'revenue', 'overdueCount', 'dueReturns'));
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