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
        $status = $request->get('status', 'not_updated'); // Default to Not Updated
        $search = $request->get('search');
        $date = $request->get('date');

        // 1. Updated Status Labels
        $statuses = [
            'not_updated' => 'Not Updated Yet',
            'updated'     => 'Updated'
        ];

        // 1. Base Query: Only bookings with a deposit amount
        $query = Booking::with(['customer', 'vehicle', 'payments'])
            ->where('bookingStatus', '!=', 'Deleted')
            ->whereHas('payments', function($q) {
                $q->where('depoAmount', '>', 0);
            });

        // 2. Search Logic
        if ($request->filled('search')) {
            $query->where(function($q) use ($search) {
                $q->where('bookingID', 'like', "%{$search}%")
                ->orWhereHas('customer', fn($c) => $c->where('fullName', 'like', "%{$search}%"))
                ->orWhereHas('vehicle', fn($v) => $v->where('plateNo', 'like', "%{$search}%"));
            });
        }

        // 2. NEW: Date Filter Logic (Filtering by Pickup Date)
        if ($request->filled('date')) {
            $query->whereDate('originalDate', $request->date);
        }

        // 2. Updated Filter Logic
        if ($status === 'updated') {
            // Show only what has been updated (Processed)
            $query->whereHas('payments', fn($q) => $q->where('depoStatus', 'Processed'));
        } else {
            // Show everything else that needs an update (Requested, Pending, Holding) but make sure bookings status is Completed/Cancelled/Rejected to avoid showing active bookings that are still in progress
            $query->whereHas('payments', function($q) {
                $q->whereIn('depoStatus', ['Requested', 'Pending', 'Holding']);
            })
            ->whereIn('bookingStatus', ['Completed', 'Cancelled', 'Rejected']);
        }
        
        // 3. Status Filter Logic
        if ($status === 'requested') {
            $query->where(function($mainQ) {
                // A: Explicitly Requested
                $mainQ->whereHas('payments', fn($q) => $q->where('depoStatus', 'Requested'))
                // B: OR Finished bookings that are still "Pending" or "Holding"
                ->orWhere(function($subQ) {
                    $subQ->whereIn('bookingStatus', ['Completed', 'Cancelled', 'Rejected'])
                        ->whereHas('payments', function($p) {
                            $p->where('depoAmount', '>', 0)
                            ->whereIn('depoStatus', ['Pending', 'Holding', 'Processed']);
                        });
                });
            });
        } elseif ($status === 'refunded') {
            $query->whereHas('payments', fn($q) => $q->where('depoStatus', 'Refunded'));
        }

        // 4. Sort by Pickup Date & List All (Removed pagination)
        $bookings = $query->orderBy('originalDate', 'asc')->get();

        // 3. Updated Count Badges
        $counts = [
            'not_updated' => Booking::whereHas('payments', fn($q) => $q->whereIn('depoStatus', ['Requested', 'Pending', 'Holding']))->count(),
            'updated'     => Booking::whereHas('payments', fn($q) => $q->where('depoStatus', 'Processed'))->count(),
        ];

        return view('staff.finance.deposits', compact('bookings', 'status', 'counts'));
    }

    public function updateDeposit(Request $request, $id)
    {
        $request->validate([
            'adjusted_amount' => 'required|numeric|min:0',
            'remarks' => 'required|string',
            'attachments.*'   => 'nullable|file|mimes:jpeg,png,jpg,pdf,doc,docx,xls,xlsx,zip|max:5120', // Up to 5MB
        ]);

        $booking = Booking::findOrFail($id);
        $payment = $booking->payments->where('depoAmount', '>', 0)->first();

        if (!$payment) {
            return back()->with('error', 'Deposit payment record not found.');
        }

        if ($payment) {
            // 1. Update Amount and Remarks
            $payment->depoAmount = $request->adjusted_amount;
            $payment->depoStatus = 'Processed'; // Status updates after action
            $payment->remarks = $request->remarks;

            if ($request->hasFile('attachments')) {
                $files = $payment->depo_evidence ?? [];
                
                foreach ($request->file('attachments') as $index => $file) {
                    // RENAME LOGIC: depo_UTM3057_1713852000_0.pdf
                    $extension = $file->getClientOriginalExtension();
                    $filename = 'depo_' . $id . '_' . time() . '_' . $index . '.' . $extension;
                    
                    // Store with the new name
                    $path = $file->storeAs('deposits', $filename, 'public');
                    $files[] = $path;
                }
                $payment->depo_evidence = $files;
            }

            $payment->save();

            return back()->with('success', 'Deposit updated successfully. Customer can now view the updates.');
        }

        return back()->with('error', 'Deposit record not found.');
    }

    public function deleteEvidence(Request $request)
    {
        // Find the payment that contains this specific file path in its evidence array
        $payment = \App\Models\Payment::where('depo_evidence', 'like', '%' . $request->path . '%')->first();

        if ($payment) {
            $currentFiles = $payment->depo_evidence;

            // Remove the file path from the array
            $updatedFiles = array_filter($currentFiles, function($file) use ($request) {
                return $file !== $request->path;
            });

            // Delete the physical file from storage
            \Illuminate\Support\Facades\Storage::disk('public')->delete($request->path);

            // Save the updated array (array_values resets the keys)
            $payment->depo_evidence = array_values($updatedFiles);
            $payment->save();

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'File not found.'], 404);
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