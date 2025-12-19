<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffBookingController extends Controller
{
    // --- 1. STAFF DASHBOARD ---
    public function dashboard()
{
    // 1. Top Card Stats (Numbers)
    $activeRentals = Booking::where('bookingStatus', 'Confirmed')->count();
    $pendingCount = Booking::where('bookingStatus', 'Submitted')->count();
    $revenue = Payment::sum('amount'); // Total revenue
    
    // 2. Overdue Count (Bonus)
    $overdueCount = Booking::where('bookingStatus', 'Confirmed')
                            ->where('returnDate', '<', now())
                            ->count();

    // 3. The "Pending Returns" List (For the sidebar)
    $dueReturns = Booking::with('vehicle')
                         ->where('bookingStatus', 'Confirmed')
                         ->orderBy('returnDate', 'asc') // Sooner returns first
                         ->take(3)
                         ->get();

    return view('staff.dashboard', compact('activeRentals', 'pendingCount', 'revenue', 'overdueCount', 'dueReturns'));
}

    // --- 2. LIST ALL BOOKINGS ---
    public function index()
    {
        // Get all bookings with customer, vehicle, and payment info
        // Ordered by newest first
        $bookings = Booking::with(['customer', 'vehicle', 'payment'])
                           ->orderBy('created_at', 'desc')
                           ->get();

        return view('staff.bookings.index', compact('bookings'));
    }

    // --- 3. APPROVE BOOKING (UC004) ---
    public function approve($id)
    {
        $booking = Booking::findOrFail($id);

        // Update status to Confirmed (This "activates" the digital agreement)
        $booking->update([
            'bookingStatus' => 'Confirmed',
            // 'staffID' => Auth::id() // Uncomment if you have a staffID column
        ]);

        // Mark payment as Verified
        if ($booking->payment) {
            $booking->payment->update(['paymentStatus' => 'Verified']);
        }

        return back()->with('success', 'Booking Approved! Digital Contract is now generated.');
    }

    // --- 4. FINALIZE / COMPLETE (UC005) ---
    public function finalize($id)
    {
        $booking = Booking::findOrFail($id);

        // Update status to Completed
        $booking->update(['bookingStatus' => 'Completed']);

        // Release the Deposit (Logic for UC005)
        if ($booking->payment) {
            $booking->payment->update([
                'depoStatus' => 'Refunded', // Release the hold
                'paymentStatus' => 'Completed'
            ]);
        }

        return back()->with('success', 'Rental Completed. Deposit has been released.');
    }
}