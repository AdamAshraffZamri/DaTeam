<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use App\Notifications\BookingStatusUpdated;
use App\Services\GoogleDriveService;

class StaffFinanceController extends Controller
{
    protected $driveService;

    // 3. Inject the service via the Constructor
    public function __construct(GoogleDriveService $driveService)
    {
        $this->driveService = $driveService;
    }

    // --- 1. LIST ALL DEPOSITS ---
    public function index(Request $request)
    {
        $status = $request->get('status', 'requested');

        // Base Query: Get bookings that HAVE a deposit paid
        $query = Booking::whereHas('payments', function($q) {
                            $q->where('depoAmount', '>', 0);
                        })
                        ->with(['customer', 'vehicle', 'payments']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('bookingID', 'like', "%{$search}%")
                ->orWhereHas('customer', fn($c) => $c->where('fullName', 'like', "%{$search}%"))
                ->orWhereHas('vehicle', fn($v) => $v->where('plateNo', 'like', "%{$search}%"));
            });
        }

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
                               ->where('depoStatus', 'Pending')
                               ->orWhere('depoStatus', 'Holding');
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
    public function processRefund(Request $request, $bookingID)
    {
        // 1. Check if the file exists in the request at all
        if (!$request->hasFile('refund_proof')) {
            return back()->with('error', 'Please upload a refund receipt image.');
        }

        $request->validate([
            'refund_proof' => 'required|image|max:10240',
            'remarks' => 'nullable|string'
        ]);
        
        $booking = Booking::with('payments', 'customer')->findOrFail($bookingID);
        $payment = $booking->payments()->where('depoAmount', '>', 0)->orderBy('paymentID', 'asc')->first();

        if (!$payment) {
            return back()->with('error', 'No payment record found.');
        }

        $fileName = "[Refund - #{$bookingID}] - {$booking->customer->fullName}";

        try {
            // Use config() instead of env() to avoid cPanel cache issues
            $folderId = config('services.google_refunds') ?? env('GOOGLE_DRIVE_REFUNDS_FOLDER');

            $receiptLink = $this->driveService->uploadFile(
                $request->file('refund_proof'), 
                $folderId, 
                $fileName
            );

            if (!$receiptLink) {
                throw new \Exception("Google Drive Service returned an empty link.");
            }

        } catch (\Exception $e) {
            \Log::error("Refund Drive Upload Failed: " . $e->getMessage());
            return back()->with('error', 'Google Drive Error: ' . $e->getMessage());
        }

        // UPDATE BOOKING REMARKS
        if ($request->filled('remarks')) {
            $oldRemarks = $booking->remarks ? $booking->remarks . "\n\n" : "";
            $refundNote = "[REFUND " . now()->format('d/m/y H:i') . "]: " . $request->remarks;
            $booking->update(['remarks' => $oldRemarks . $refundNote]);
        }

        // UPDATE PAYMENT
        $payment->update([
            'depoStatus' => 'Refunded',
            'refund_proof_link' => $receiptLink,
            'paymentStatus' => 'Refund Completed',
            'depoRefundedDate' => now()
        ]);

        // NOTIFY CUSTOMER
        try {
            $booking->customer->notify(new BookingStatusUpdated(
                $booking, 
                "Your deposit for booking #{$booking->bookingID} has been REFUNDED successfully."
            ));
        } catch (\Exception $e) {
            \Log::error("Finance Refund Notification Failed: " . $e->getMessage());
        }

        return back()->with('success', "Deposit for booking #{$booking->bookingID} marked as Refunded and Receipt uploaded.");
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