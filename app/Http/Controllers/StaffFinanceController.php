<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use App\Notifications\BookingStatusUpdated;

class StaffFinanceController extends Controller
{
    // --- 1. LIST ALL DEPOSITS ---
    public function index(Request $request)
    {
        $status = $request->get('status', 'requested');

        // Base Query: Get bookings that HAVE a deposit paid
        $query = Booking::whereHas('payments', function($q) {
                            $q->where('depoAmount', '>', 0);
                        })
                        ->with(['customer', 'vehicle', 'payments']);

        // --- FILTER LOGIC ---
        if ($status === 'requested') {
            // SHOW: 
            // 1. Explicit 'Requested' status
            // 2. OR 'Completed/Cancelled/Rejected' bookings where deposit is still 'Pending' (Needs action)
            $query->where(function($mainQ) {
                // Condition A: Customer requested it
                $mainQ->whereHas('payments', function($q) {
                    $q->where('depoStatus', 'Requested');
                })
                // Condition B: Booking ended but deposit not yet processed
                ->orWhere(function($subQ) {
                    $subQ->whereIn('bookingStatus', ['Completed', 'Cancelled', 'Rejected'])
                         ->whereHas('payments', function($p) {
                             $p->where('depoAmount', '>', 0)
                               ->where('depoStatus', 'Pending');
                         });
                });
            });

        } elseif ($status === 'refunded') {
            $query->whereHas('payments', function($q) {
                $q->where('depoStatus', 'Refunded');
            });
        }

        $bookings = $query->orderBy('updated_at', 'desc')->paginate(10);

        // --- COUNTS ---
        // We need to replicate the complex "Requested + Pending Completed" logic for the count
        $requestedCount = Booking::whereHas('payments', function($q) {
                            $q->where('depoAmount', '>', 0);
                        })
                        ->where(function($mainQ) {
                            $mainQ->whereHas('payments', function($q) {
                                $q->where('depoStatus', 'Requested');
                            })
                            ->orWhere(function($subQ) {
                                $subQ->whereIn('bookingStatus', ['Completed', 'Cancelled', 'Rejected'])
                                     ->whereHas('payments', function($p) {
                                         $p->where('depoAmount', '>', 0)
                                           ->where('depoStatus', 'Pending');
                                     });
                            });
                        })->count();

        $counts = [
            'requested' => $requestedCount,
            'refunded'  => Payment::where('depoStatus', 'Refunded')->count(),
        ];

        return view('staff.finance.deposits', compact('bookings', 'status', 'counts'));
    }

    // --- 2. PROCESS REFUND ---
    public function processRefund(Request $request, $id)
    {
        $request->validate([
            'refund_proof' => 'nullable|image|max:2048',
            'remarks' => 'nullable|string'
        ]);

        $booking = Booking::with('payments')->findOrFail($id);
        // Find the payment with deposit
        $payment = $booking->payments->where('depoAmount', '>', 0)->first();
        if (!$payment) {
            return back()->with('error', 'No payment record found.');
        }

        // 1. Handle Remarks
        if ($request->filled('remarks')) {
            $oldRemarks = $booking->remarks ? $booking->remarks . "\n\n" : "";
            $refundNote = "[REFUND " . now()->format('d/m/y H:i') . "]: " . $request->remarks;
            $booking->update(['remarks' => $oldRemarks . $refundNote]);
        }

        // 2. Handle Proof Image (Optional)
        // If you want to store a refund receipt, you might need a new column or reuse installmentDetails
        // For now, we'll just update the status.

        // 3. Update Status
        $payment->update([
            'depoStatus' => 'Refunded',
            'paymentStatus' => 'Refund Completed', // Or keep 'Paid' if you want to separate rental fee status
            'depoRefundedDate' => now()
        ]);

        // 4. Notify Customer
        try {
            $booking->customer->notify(new BookingStatusUpdated(
                $booking, 
                "Your deposit for booking #{$booking->bookingID} has been REFUNDED successfully."
            ));
        } catch (\Exception $e) {
            \Log::error("Finance Refund Email Failed: " . $e->getMessage());
        }
        return back()->with('success', 'Deposit marked as Refunded.');
    }

    // --- 3. FORFEIT DEPOSIT (Optional) ---
    public function forfeit(Request $request, $id)
    {
        $booking = Booking::with('payment')->findOrFail($id);
        
        $booking->payment->update([
            'depoStatus' => 'Forfeited',
            'depoRefundedDate' => now() // Technically closed date
        ]);

        try {
            $booking->customer->notify(new BookingStatusUpdated(
                $booking, 
                "Alert: Your deposit for booking #{$booking->bookingID} has been FORFEITED due to damages or violations."
            ));
        } catch (\Exception $e) {
             \Log::error("Forfeit Email Failed: " . $e->getMessage());
        }
        return back()->with('success', 'Deposit marked as Forfeited.');
    }
}