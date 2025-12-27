<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Inspection;
use App\Models\Staff; // Added Staff model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffBookingController extends Controller
{
    // --- 1. STAFF DASHBOARD ---
    public function dashboard()
    {
        $activeRentals = Booking::where('bookingStatus', 'Active')->count(); 
        $pendingCount = Booking::where('bookingStatus', 'Submitted')->count();
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

    // --- 3. VIEW DETAILS (CONSOLIDATED) ---
    public function show($id)
    {
        // Eager load inspections, payment, and currently assigned staff
        $booking = Booking::with(['customer', 'vehicle', 'payment', 'inspections', 'staff'])->findOrFail($id);
        
        // Get all staff for the assignment dropdown
        $allStaff = Staff::all();

        return view('staff.bookings.show', compact('booking', 'allStaff'));
    }

    // --- 4. STEP 1: VERIFY PAYMENT ---
    public function verifyPayment($id)
    {
        $booking = Booking::findOrFail($id);
        
        if ($booking->payment) {
            $booking->payment->update(['paymentStatus' => 'Verified']);
            return back()->with('success', 'Payment verified. Agreement is now released for approval.');
        }

        return back()->with('error', 'No payment record found.');
    }

    // --- 5. STEP 2: APPROVE AGREEMENT & CONFIRM ---
    public function approveAgreement($id)
    {
        $booking = Booking::findOrFail($id);

        if (!$booking->payment || $booking->payment->paymentStatus !== 'Verified') {
            return back()->with('error', 'Please verify the payment proof first.');
        }

        $booking->update([
            'bookingStatus' => 'Confirmed', // Ready for pickup
            // Auto-assign current staff if not already assigned
            'staffID' => $booking->staffID ?? Auth::guard('staff')->id(), 
            'aggreementDate' => now(),
        ]);

        return back()->with('success', 'Agreement approved. Booking is CONFIRMED.');
    }

    // --- 6. STEP 3: VEHICLE PICKUP (Handover) ---
    public function pickup(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);
        $booking->update(['bookingStatus' => 'Active']);

        return back()->with('success', 'Vehicle collected. Booking is now ACTIVE.');
    }

    // --- 7. STEP 4: VEHICLE RETURN (Settlement) ---
    public function processReturn(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);

        // 1. Mark as Completed
        $booking->update([
            'bookingStatus' => 'Completed',
            'actualReturnDate' => now()->toDateString(),
            'actualReturnTime' => now()->toTimeString(),
        ]);

        // 2. Process Deposit Refund Logic
        if ($booking->payment) {
            $booking->payment->update([
                'depoStatus' => 'Refunded', 
                'paymentStatus' => 'Completed'
            ]);
        }

        return back()->with('success', 'Vehicle returned. Booking settled and deposit released.');
    }

    // --- 8. MANUAL REFUND PROCESSING (For Cancelled Bookings) ---
    public function processRefund($id)
    {
        $booking = Booking::findOrFail($id);

        if ($booking->payment && $booking->payment->depoStatus == 'Requested') {
            $booking->payment->update([
                'depoStatus' => 'Refunded',
                'paymentStatus' => 'Refund Completed'
            ]);
            return back()->with('success', 'Refund has been issued to the customer.');
        }

        return back()->with('error', 'Refund cannot be processed (Status mismatch).');
    }

    // --- 9. STAFF INSPECTION UPLOAD ---
    public function storeInspection(Request $request, $id)
    {
        $request->validate(['photos' => 'required', 'type' => 'required']);
        $booking = Booking::findOrFail($id);

        $photoPaths = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $photoPaths[] = $photo->store('inspections', 'public');
            }
        }

        \App\Models\Inspection::create([
            'bookingID' => $booking->bookingID,
            'staffID' => Auth::guard('staff')->id(), // Tracks WHICH staff verified
            'inspectionType' => $request->type,
            'inspectionDate' => now(),
            'photosBefore' => $request->type == 'Pickup' ? json_encode($photoPaths) : null,
            'photosAfter' => $request->type == 'Return' ? json_encode($photoPaths) : null,
        ]);

        return back()->with('success', 'Staff inspection photos uploaded.');
    }

    // --- 10. MANUAL STAFF ASSIGNMENT ---
    public function assignStaff(Request $request, $id)
    {
        $request->validate([
            'staff_id' => 'required|exists:staff,staffID'
        ]);

        $booking = Booking::findOrFail($id);
        $booking->update(['staffID' => $request->staff_id]);

        $staffName = Staff::find($request->staff_id)->fullName ?? 'Staff Member';

        return back()->with('success', "Booking assigned to Agent: $staffName");
    }
}