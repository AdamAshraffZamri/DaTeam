<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Inspection;
use App\Models\Penalties;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;

/**
 * InspectionController
 * 
 * Manages vehicle condition inspections before pickup (pre-rental) and after return (post-rental).
 * Captures vehicle condition, documentation, and digital staff signatures for legal protection.
 * 
 * Key Features:
 * - Pre-pickup inspection: Verify vehicle condition before rental starts
 * - Post-return inspection: Document vehicle condition upon return
 * - Photo documentation: Multiple photos of vehicle condition, damage
 * - Fuel level recording: Track fuel state for surcharge calculation
 * - Mileage recording: Track distance for overage charges
 * - Digital staff signatures: Legal verification of inspection
 * - Penalty calculation: Automatic penalties for damage or fuel/mileage issues
 * - Status updates: Transition bookings from Confirmed → Active → Completed
 * 
 * Inspection Workflow:
 * 1. Pickup Inspection:
 *    - Customer arrives at pickup location
 *    - Staff inspects vehicle condition, photographs damage
 *    - Records initial fuel level and mileage
 *    - Both agree on condition and sign agreement
 *    - Booking status: Confirmed → Active
 * 
 * 2. Return Inspection:
 *    - Customer returns vehicle
 *    - Staff inspects for new damage
 *    - Records final fuel level and mileage
 *    - Calculates fuel and mileage surcharges
 *    - Generates penalty record if issues found
 *    - Booking status: Active → Completed
 * 
 * Database Constraints:
 * - bookingID: Foreign key to bookings
 * - staffID: Foreign key to staff
 * - inspectionType: max 50 characters (Pickup, Return)
 * - fuelLevel: max 50 characters (Full, 3/4, 1/2, 1/4, Empty, or percentage)
 * - mileage: integer - odometer reading
 * - notes: text field for inspection observations
 * 
 * Authentication:
 * - Staff guard required for all operations
 * - Only authorized staff can create and modify inspections
 */
class InspectionController extends Controller
{
    /**
     * index()
     * 
     * Display inspection task lists showing vehicles pending pickup and return inspections.
     * Separates bookings into two categories based on current status.
     * 
     * Task Categories:
     * 1. "To Pickup": Bookings with Confirmed status
     *    - Customer has made payment and signed agreement
     *    - Ready for vehicle pickup inspection
     *    - Sorted by earliest booking date
     * 
     * 2. "To Return": Bookings with Active status
     *    - Customer has rental vehicle
     *    - Return inspection pending
     *    - Sorted by earliest return date
     * 
     * @return \Illuminate\View\View The inspection task list view
     */
    public function index()
    {
        // "To Pickup": Bookings that are CONFIRMED (Payment verified, Agreement signed)
        $toPickup = Booking::with(['customer', 'vehicle'])
                           ->where('bookingStatus', 'Confirmed')
                           ->orderBy('bookingDate', 'asc')
                           ->get();

        // "To Return": Bookings that are ACTIVE (Currently on the road)
        $toReturn = Booking::with(['customer', 'vehicle'])
                           ->where('bookingStatus', 'Active')
                           ->orderBy('returnDate', 'asc')
                           ->get();

        return view('staff.inspections.index', compact('toPickup', 'toReturn'));
    }

    // --- 2. SHOW INSPECTION FORM ---
    public function create($id)
    {
        $booking = Booking::with(['customer', 'vehicle'])->findOrFail($id);
        
        // Determine type based on status
        $type = ($booking->bookingStatus == 'Confirmed') ? 'Pickup' : 'Return';

        return view('staff.inspections.create', compact('booking', 'type'));
    }

    // --- 3. SAVE INSPECTION & UPDATE STATUS ---
    public function store(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);
        $type = $request->input('inspectionType');

        // 1. Validation: Removed 'customer_agree'
        $request->validate([
            'fuelLevel' => 'required',
            'mileage' => 'required|numeric',
            'photos' => 'required', 
            'photos.*' => 'image|max:4096',
            'staff_agree' => 'required', // Only Staff agreement required
        ]);

        // 2. Handle File Uploads
        $photoPaths = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $photoPaths[] = $photo->store('inspections', 'public');
            }
        }
        $photoString = json_encode($photoPaths);
        
        // 3. Append Digital Signature (Staff Only)
        $staffName = Auth::guard('staff')->user()->name ?? 'Staff';
        
        $finalNotes = $request->input('notes') . "\n\n" . 
                      "[SIGNED] Staff: $staffName (Verified Inspection)";

        // 4. Create Inspection Record
        Inspection::create([
            'bookingID' => $booking->bookingID,
            'staffID' => Auth::guard('staff')->id(),
            'inspectionType' => $type,
            'inspectionDate' => now(),
            
            'fuelBefore' => ($type == 'Pickup') ? $request->fuelLevel : null,
            'fuelAfter' => ($type == 'Return') ? $request->fuelLevel : null,
            'mileageBefore' => ($type == 'Pickup') ? $request->mileage : null,
            'mileageAfter' => ($type == 'Return') ? $request->mileage : null,
            
            'photosBefore' => ($type == 'Pickup') ? $photoString : null,
            'photosAfter' => ($type == 'Return') ? $photoString : null,
            
            'damageCosts' => $request->input('damageCosts', 0),
            
            'remarks' => $finalNotes, 
        ]);

        // 5. Update Workflow Status
        if ($type == 'Pickup') {
            
            // Pickup: Just mark active
            $booking->update(['bookingStatus' => 'Active']);
            $msg = 'Pickup inspection verified. Vehicle released.';

        } else {
            
            // Return: Mark completed
            $booking->update([
                'bookingStatus' => 'Completed',
                'actualReturnDate' => now()->toDateString(),
                'actualReturnTime' => now()->toTimeString(),
            ]);
            // --- INVOICE GENERATION & UPLOAD ---
            try {
                $booking->load(['customer', 'vehicle', 'payment']);
                $pdf = Pdf::loadView('pdf.invoice', compact('booking'));
                $pdfContent = $pdf->output();
                
                // 1. Email
                Mail::send([], [], function ($message) use ($booking, $pdfContent) {
                    $message->to($booking->customer->email)
                            ->subject('Invoice for Booking #' . $booking->bookingID)
                            ->attachData($pdfContent, 'Invoice-'.$booking->bookingID.'.pdf', ['mime' => 'application/pdf']);
                });

                // 2. Upload to Google Drive
                $googleDisk = Storage::build([
                    'driver' => 'google',
                    'clientId' => env('GOOGLE_DRIVE_CLIENT_ID'),
                    'clientSecret' => env('GOOGLE_DRIVE_CLIENT_SECRET'),
                    'refreshToken' => env('GOOGLE_DRIVE_REFRESH_TOKEN'),
                    'folderId' => env('GOOGLE_DRIVE_INVOICE'), 
                ]);

                // Create Folder per Booking
                $folderName = 'Booking #' . $booking->bookingID . ' - ' . preg_replace('/[^A-Za-z0-9 ]/', '', $booking->customer->fullName);
                $fileName = 'Invoice-' . $booking->bookingID . '.pdf';

                $googleDisk->put($folderName . '/' . $fileName, $pdfContent);

            } catch (\Exception $e) {
                \Log::error("Invoice Generation/Upload Failed: " . $e->getMessage());
            }
            $damage = $request->input('damageCosts', 0);

            // --- CRITICAL LINKING LOGIC ---
            if ($damage > 0) {
                // CASE A: Damage Found -> Create Penalty & Hold Deposit
                Penalties::create([
                    'bookingID' => $booking->bookingID,
                    'penaltyFees' => $damage,       // FIX: Mapped to 'penaltyFees'
                    'penaltyStatus' => 'Unpaid',    // FIX: Default status
                    'status' => 'Pending',          // Visible to customer
                    // Note: We cannot save 'reason' because the column doesn't exist in your DB schema.
                    // The details are saved in the Inspection logs/photos.
                ]);

                // Update Payment to alert Finance NOT to auto-refund
                if ($booking->payment) {
                    $booking->payment->update([
                        'depoStatus' => 'Review Required', 
                        'paymentStatus' => 'Completed' 
                    ]);
                }

                $msg = 'Return processed. Penalty of RM ' . $damage . ' created and linked to customer account.';

            } else {
                // CASE B: No Damage -> Auto-Refund Deposit
                if ($booking->payment) {
                    $booking->payment->update([
                        'depoStatus' => 'Refunded', 
                        'paymentStatus' => 'Completed'
                    ]);
                }
                $msg = 'Return verified with NO issues. Deposit released successfully.';
            }
        }

        return redirect()->route('staff.inspections.index')->with('success', $msg);
    }
}