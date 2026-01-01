<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Staff; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
    public function index()
    {
        $bookings = Booking::with(['customer', 'vehicle', 'payment']) 
                           ->orderBy('created_at', 'desc')
                           ->get();

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

        return back()->with('success', 'Booking is CONFIRMED.');
    }

    // ... pickup, processReturn, processRefund, storeInspection, assignStaff, show ...
    public function show($id) {
        $booking = Booking::with(['customer', 'vehicle', 'payment', 'inspections', 'staff'])->findOrFail($id);
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
        return back()->with('success', 'Vehicle returned & Loyalty Points Awarded.');
    }
    public function processRefund($id) {
        $booking = Booking::findOrFail($id);
        if ($booking->payment && $booking->payment->depoStatus == 'Requested') {
            $booking->payment->update(['depoStatus' => 'Refunded', 'paymentStatus' => 'Refund Completed']);
            return back()->with('success', 'Refund issued.');
        }
        return back()->with('error', 'Error processing refund.');
    }
    public function storeInspection(Request $request, $id) {
        $request->validate(['photos' => 'required', 'type' => 'required']);
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

        return back()->with('success', $statusMessage);
    }
}