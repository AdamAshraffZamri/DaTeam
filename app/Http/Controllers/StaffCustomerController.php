<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Penalties;
use Illuminate\Http\Request;
use App\Services\GoogleDriveService;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class StaffCustomerController extends Controller
{
    // Show the list of customers
    public function index(Request $request)
    {
        $query = Customer::query();

        // Search logic
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('fullName', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('stustaffID', 'like', "%{$search}%");
        }

        $customers = $query->latest()->paginate(10);
        return view('staff.customers.index', compact('customers'));
    }

    // Show single customer details
    public function show($id)
    {
        $customer = Customer::findOrFail($id);
        return view('staff.customers.show', compact('customer'));
    }

    // ACTION 1: Approve
    public function approve($id)
    {
        $customer = Customer::findOrFail($id);
        
        $customer->update([
            'accountStat' => 'Confirmed',
            'rejection_reason' => null // Clear any previous rejection error
        ]);

        return back()->with('success', 'Customer has been verified and Confirmed.');
    }

    // ACTION 2: Reject
    public function reject(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);
        
        $request->validate([
            'rejection_reason' => 'required|array|min:1',
            'rejection_reason.*' => 'string',
            'rejection_custom' => 'nullable|string|max:500' // New validation
        ], [
            'rejection_reason.required' => 'Please select at least one reason.'
        ]);

        // 1. Convert Array to String
        $reasonString = implode(', ', $request->rejection_reason);

        // 2. Append Custom Notes if they exist
        if ($request->filled('rejection_custom')) {
            $reasonString .= " (" . $request->rejection_custom . ")";
        }

        $customer->update([
            'accountStat' => 'rejected',
            'rejection_reason' => $reasonString 
        ]);

        return back()->with('success', 'Customer application rejected.');
    }

    // ACTION 3: Blacklist (Toggle)
    public function toggleBlacklist(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        if ($customer->blacklisted) {
            // === ACTION: REMOVE FROM BLACKLIST ===
            
            // 1. Determine what the status should go back to
            // If we have a saved previous status, use it. Otherwise, default to 'unverified'.
            $newStatus = $customer->previous_account_stat ?? 'unverified';

            $customer->update([
                'blacklisted' => false,
                'blacklist_reason' => null,
                'accountStat' => $newStatus,      // Restore the old status
                'previous_account_stat' => null,  // Clear the memory
            ]);

            return back()->with('success', 'Customer removed from blacklist. Status reverted to ' . ucfirst($newStatus) . '.');

        } 
        else {
            // === ACTION: ADD TO BLACKLIST ===
            $request->validate([
                'blacklist_reason' => 'required|string',
                'blacklist_custom' => 'nullable|string|max:500' // New validation
            ]);
            
            // Combine Reason + Custom Text
            // Example: "Severe Vehicle Damage - Bumper completely destroyed"
            $finalReason = $request->blacklist_reason;
            if ($request->filled('blacklist_custom')) {
                $finalReason .= " - " . $request->blacklist_custom;
            }

            $customer->update([
                'blacklisted' => true,
                'blacklist_reason' => $finalReason,
                'previous_account_stat' => $customer->accountStat, 
                'accountStat' => 'rejected'
            ]);

            return back()->with('success', 'Customer has been blacklisted.');
            }
    }

    // ACTION 4: Impose Penalty
    public function imposePenalty(Request $request, $id, GoogleDriveService $driveService)
    {
        $customer = Customer::findOrFail($id);
        
        $request->validate([
            'penalty_reason' => 'required|string',
            'penalty_amount' => 'required|numeric|min:0.01',
            'penalty_custom' => 'nullable|string|max:500'
        ]);

        // Combine Reason + Custom Text
        $finalReason = $request->penalty_reason;
        if ($request->filled('penalty_custom')) {
            $finalReason .= " - " . $request->penalty_custom;
        }

        $evidenceId = null;
        if ($request->hasFile('evidence_photo')) {
            $folderId = env('GOOGLE_DRIVE_CUSTOMER_INFORMATION'); // or a new folder for Penalties
            $evidenceId = $driveService->uploadFile($request->file('evidence_photo'), $folderId);
        }

        // Create penalty record
        Penalties::create([
            'customerID' => $customer->customerID,
            'bookingID' => null, // Customer-level penalty, not booking-specific
            'amount' => $request->penalty_amount,
            'penaltyFees' => $request->penalty_amount,
            'reason' => $finalReason,
            'status' => 'Pending',
            'penaltyStatus' => 'Unpaid',
            'date_imposed' => now(),
            'lateReturnHour' => 0,
            'fuelSurcharge' => 0,
            'mileageSurcharge' => 0,
        ]);

        return back()->with('success', 'Penalty imposed successfully. Customer must pay before making new bookings.');
    }

    // Show penalty history for a customer
    public function penaltyHistory($id)
    {
        $customer = Customer::findOrFail($id);
        
        // Get all penalties for this customer (both booking-based and customer-level)
        $penalties = Penalties::where('customerID', $customer->customerID)
            ->with(['booking.vehicle', 'customer'])
            ->orderBy('date_imposed', 'desc')
            ->paginate(15);
        
        // Calculate statistics
        $totalPenalties = Penalties::where('customerID', $customer->customerID)->count();
        $unpaidPenalties = Penalties::where('customerID', $customer->customerID)
            ->where(function($query) {
                $query->where('status', 'Pending')
                      ->orWhere('penaltyStatus', 'Unpaid');
            })
            ->count();
        $totalAmount = Penalties::where('customerID', $customer->customerID)
            ->where(function($query) {
                $query->where('status', 'Pending')
                      ->orWhere('penaltyStatus', 'Unpaid');
            })
            ->get()
            ->sum(function($penalty) {
                return $penalty->amount ?? ($penalty->penaltyFees + $penalty->fuelSurcharge + $penalty->mileageSurcharge);
            });
        
        return view('staff.customers.penalty-history', compact('customer', 'penalties', 'totalPenalties', 'unpaidPenalties', 'totalAmount'));
    }

    public function store(Request $request, GoogleDriveService $driveService)
    {
        // 1. Validate the input
        $request->validate([
            'name' => 'required',
            'document' => 'required|file|mimes:pdf,jpg,png|max:2048', // Example validation
        ]);

        // 2. Upload to Google Drive
        if ($request->hasFile('document')) {
            try {
                // Get folder ID from .env
                $folderId = env('GOOGLE_DRIVE_CUSTOMER_INFORMATION'); 
                
                // Perform the upload
                $fileId = $driveService->uploadFile($request->file('document'), $folderId);
                
                // 3. Save the File ID to your database (Example)
                // Customer::create([
                //     'name' => $request->name,
                //     'document_id' => $fileId
                // ]);

                return back()->with('success', 'File uploaded successfully! Drive ID: ' . $fileId);

            } catch (\Exception $e) {
                return back()->with('error', 'Google Drive Upload Failed: ' . $e->getMessage());
            }
        }

        return back()->with('error', 'No file uploaded.');
    }

    // ACTION 5: Upload File to Drive
    public function uploadFileToDrive(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);
        $request->validate([
            'photo' => 'required|image|max:10240',
            'description' => 'required|string|max:50',
        ]);

        try {
            // 1. Connect
            $client = new \Google\Client();
            $client->setClientId(env('GOOGLE_DRIVE_CLIENT_ID'));
            $client->setClientSecret(env('GOOGLE_DRIVE_CLIENT_SECRET'));
            $client->refreshToken(env('GOOGLE_DRIVE_REFRESH_TOKEN'));
            $service = new \Google\Service\Drive($client);

            // 2. Find Folder
            $parentFolderId = env('GOOGLE_DRIVE_CUSTOMER_INFORMATION');
            $folderName = trim("{$customer->stustaffID} - {$customer->fullName}");
            
            $query = "mimeType='application/vnd.google-apps.folder' and name = '" . str_replace("'", "\'", $folderName) . "' and '$parentFolderId' in parents and trashed = false";
            $files = $service->files->listFiles(['q' => $query]);

            if (count($files->getFiles()) > 0) {
                $folderId = $files->getFiles()[0]->getId();
            } else {
                $folderMeta = new \Google\Service\Drive\DriveFile([
                    'name' => $folderName,
                    'mimeType' => 'application/vnd.google-apps.folder',
                    'parents' => [$parentFolderId]
                ]);
                $folderId = $service->files->create($folderMeta, ['fields' => 'id'])->id;
            }

            // 3. Upload
            $file = $request->file('photo');
            $fileName = Carbon::now()->format('Y-m-d') . " - " . $request->description . " (" . Carbon::now()->format('H-i-s') . ")." . $file->getClientOriginalExtension();

            $fileMetadata = new \Google\Service\Drive\DriveFile([
                'name' => $fileName,
                'parents' => [$folderId]
            ]);
            
            $content = file_get_contents($file->getRealPath());
            $service->files->create($fileMetadata, [
                'data' => $content,
                'mimeType' => $file->getMimeType(),
                'uploadType' => 'multipart'
            ]);

            return back()->with('success', 'File uploaded to Customer Folder on Drive!');

        } catch (\Exception $e) {
            return back()->with('error', 'Upload Failed: ' . $e->getMessage());
        }
    }
}