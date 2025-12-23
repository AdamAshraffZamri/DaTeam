<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Booking;
use App\Models\Penalties;

class FinanceController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 1. GET CLAIMS (Refunds)
        // Logic: Bookings that are 'Cancelled' but might have paid something
        $claims = Booking::where('customerID', $user->customerID)
                         ->where('bookingStatus', 'Cancelled')
                         ->with('vehicle')
                         ->get();

        // 2. GET OUTSTANDING ITEMS
        
        // A. Fines (from Penalties table)
        $fines = Penalties::whereHas('booking', function($q) use ($user) {
                    $q->where('customerID', $user->customerID);
                 })
                 ->where('status', 'Pending')
                 ->get();

        // B. Booking Balances (Where status is 'Deposit Paid')
        $balanceBookings = Booking::where('customerID', $user->customerID)
                                  ->where('bookingStatus', 'Deposit Paid')
                                  ->with(['vehicle', 'payments'])
                                  ->get();

        return view('finance.index', compact('claims', 'fines', 'balanceBookings'));
    }

    // --- SHOW PAY BALANCE FORM ---
    public function payBalance($id)
    {
        $user = Auth::user();
        
        // Find booking belonging to this user
        $booking = Booking::where('customerID', $user->customerID)
                          ->with(['vehicle', 'payments'])
                          ->findOrFail($id);

        // Calculate Balance
        $totalPaid = $booking->payments->sum('amount');
        $balance = $booking->totalCost - $totalPaid;

        if ($balance <= 0) {
            return redirect()->route('finance.index')->with('success', 'This booking is already fully paid.');
        }

        return view('finance.pay_balance', compact('booking', 'balance', 'totalPaid'));
    }

    // --- SUBMIT BALANCE PAYMENT ---
    public function submitBalance(Request $request, $id)
    {
        $request->validate([
            'payment_proof' => 'required|image|max:2048',
        ]);

        $booking = Booking::findOrFail($id);
        $totalPaid = $booking->payments->sum('amount');
        $balance = $booking->totalCost - $totalPaid;

        $path = $request->file('payment_proof')->store('receipts', 'public');

        \App\Models\Payment::create([
            'bookingID' => $booking->bookingID,
            'amount' => $balance, 
            'depoAmount' => 0, // FIX: Added this line (0 because it's not a deposit)
            'transactionDate' => now(),
            'paymentMethod' => 'QR Transfer',
            'paymentStatus' => 'Pending Verification', 
            'installmentDetails' => $path
        ]);

        $booking->update(['bookingStatus' => 'Paid']);

        return redirect()->route('finance.index')->with('success', 'Balance payment submitted successfully!');
    }
}