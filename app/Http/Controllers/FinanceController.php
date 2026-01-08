<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Booking;
use App\Models\Penalties;
use App\Models\Payment; 

class FinanceController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 1. GET CLAIMS (Refunds)
        $claims = Booking::where('customerID', $user->customerID)
                         ->where('bookingStatus', 'Cancelled')
                         ->with(['vehicle', 'payment']) 
                         ->get();

        // 2. GET OUTSTANDING ITEMS (Updated Query)
        // Kita tambah 'Pending Verification' supaya item tak hilang lepas upload resit
        $fines = Penalties::where(function($query) use ($user) {
                    // Customer-level penalties
                    $query->where('customerID', $user->customerID)
                    // OR booking-based penalties
                    ->orWhereHas('booking', function($q) use ($user) {
                        $q->where('customerID', $user->customerID);
                    });
                 })
                 ->where(function($query) {
                     // Kita check semua status yang relevan: Unpaid, Pending, dan Pending Verification
                     $query->whereIn('penaltyStatus', ['Unpaid', 'Pending', 'Pending Verification'])
                           ->orWhereIn('status', ['Unpaid', 'Pending', 'Pending Verification']);
                 })
                 ->with(['booking.vehicle', 'customer'])
                 ->orderBy('created_at', 'desc') // Susun ikut tarikh terkini
                 ->get();

        // 3. GET BALANCE PAYMENTS
        $balanceBookings = Booking::where('customerID', $user->customerID)
                              ->whereIn('bookingStatus', ['Deposit Paid'])
                              ->with(['vehicle', 'payments'])
                              ->get();

        return view('finance.index', compact('claims', 'fines', 'balanceBookings'));
    }
    // --- NEW: REQUEST REFUND ---
    public function requestRefund($id)
    {
        $booking = Booking::where('customerID', Auth::id())->findOrFail($id);

        if ($booking->payment) {
            // Update status to 'Requested' so staff can see it
            $booking->payment->update(['depoStatus' => 'Requested']);
            return back()->with('success', 'Refund request submitted to admin.');
        }

        return back()->with('error', 'No payment record found for this booking.');
    }

    // --- SHOW PAY BALANCE FORM (Keep existing) ---
    public function payBalance($id)
    {
        $user = Auth::user();
        $booking = Booking::where('customerID', $user->customerID)
                          ->with(['vehicle', 'payments'])
                          ->findOrFail($id);

        $totalPaid = $booking->payments->sum('amount');
        $balance = $booking->totalCost - $totalPaid;

        if ($balance <= 0) {
            return redirect()->route('finance.index')->with('success', 'This booking is already fully paid.');
        }

        return view('finance.pay_balance', compact('booking', 'balance', 'totalPaid'));
    }

    // --- SUBMIT BALANCE PAYMENT (Keep existing) ---
    public function submitBalance(Request $request, $id)
    {
        $request->validate([
            'payment_proof' => 'required|image|max:2048',
        ]);

        $booking = Booking::findOrFail($id);
        $totalPaid = $booking->payments->sum('amount');
        $balance = $booking->totalCost - $totalPaid;

        // Prevent paying if already paid
        if ($balance <= 0) {
            return redirect()->route('finance.index')->with('success', 'Booking is already fully paid.');
        }

        $path = $request->file('payment_proof')->store('receipts', 'public');

        \App\Models\Payment::create([
            'bookingID' => $booking->bookingID,
            'amount' => $balance, 
            'depoAmount' => 0,
            'transactionDate' => now(),
            'paymentMethod' => 'QR Transfer',
            'paymentStatus' => 'Pending Verification', 
            'installmentDetails' => $path
        ]);

        if (in_array($booking->bookingStatus, ['Deposit Paid', 'Confirmed'])) {
            $booking->update(['bookingStatus' => 'Paid']);
        }

        return redirect()->route('finance.index')->with('success', 'Balance payment submitted successfully!');
    }

    public function payFine($id)
    {
        // Find the penalty by its ID with relationships
        $penalty = Penalties::with(['booking.vehicle', 'customer'])->findOrFail($id);

        // Security check: ensure the penalty belongs to the logged-in user
        if ($penalty->bookingID) {
            // Booking-based penalty
            if ($penalty->booking && $penalty->booking->customerID !== Auth::id()) {
                abort(403, 'Unauthorized action.');
            }
        } else {
            // Customer-level penalty
            if ($penalty->customerID !== Auth::id()) {
                abort(403, 'Unauthorized action.');
            }
        }

        return view('finance.pay_fine', compact('penalty'));
    }

    public function submitFine(Request $request, $id)
    {
        $request->validate([
            'payment_proof' => 'required|mimes:jpg,jpeg,png,pdf|max:4048',
        ]);

        $penalty = Penalties::findOrFail($id);
        
        // Security check
        if ($penalty->bookingID) {
            if ($penalty->booking->customerID !== Auth::id()) {
                abort(403, 'Unauthorized action.');
            }
        } else {
            if ($penalty->customerID !== Auth::id()) {
                abort(403, 'Unauthorized action.');
            }
        }
        
        // CALCULATE TOTAL
        $totalFine = $penalty->amount ?? ($penalty->penaltyFees + $penalty->fuelSurcharge + $penalty->mileageSurcharge);

        // 1. Simpan Gambar ke Folder 'public/receipts'
        $path = $request->file('payment_proof')->store('receipts', 'public');

        // 2. Create Payment Record (Optional, untuk tracking kewangan)
        Payment::create([
            'bookingID' => $penalty->bookingID, 
            'amount' => $totalFine,
            'depoAmount' => 0,
            'transactionDate' => now(),
            'paymentMethod' => 'QR Transfer (Penalty)',
            'paymentStatus' => 'Pending Verification',
            'installmentDetails' => $path 
        ]);

        // 3. Update Penalty Record (PENTING)
        $penalty->update([
            'status' => 'Pending Verification',        // Status umum
            'penaltyStatus' => 'Pending Verification', // Status spesifik penalty
            'payment_proof' => $path,                  // Simpan path gambar
            'paid_at' => now()                         // Tarikh submit
        ]);

        return redirect()->route('finance.index')->with('success', 'Payment proof submitted! Waiting for staff verification.');
    }
}