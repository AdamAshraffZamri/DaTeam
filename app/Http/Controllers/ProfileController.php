<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File; // Add this
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
/**
 * ProfileController
 * 
 * Manages customer profile operations including:
 * - Avatar/profile picture uploads (local + Google Drive backup)
 * - Personal information updates (name, email, contact details)
 * - Address information (home, college)
 * - Identity documents (IC/Passport, Student Card, Driving License)
 * - Bank account information
 * - Password management
 * 
 * Database Column Constraints (Optimized):
 * - fullName: max 100 characters
 * - email: max 100 characters
 * - phoneNo: max 20 characters
 * - stustaffID: max 50 characters
 * - ic_passport: max 50 characters
 * - driving_license_expiry: max 50 characters
 * - nationality: max 50 characters
 * - faculty: max 100 characters
 * - bankName: max 100 characters
 * - bankAccountNo: max 50 characters
 * - homeAddress, collegeAddress: text fields
 * - Image paths: max 255 characters
 * 
 * Password Requirements:
 * - Minimum: 8 characters
 * - Confirmation required
 * 
 * File Uploads:
 * - Avatar: 5MB max, JPEG/PNG format
 * - Documents: 5MB max, JPEG/PNG format
 * - Automatic local backup to public/storage/profilepic
 * - Optional Google Drive backup (async)
 */
class ProfileController extends Controller
{
    /**
     * edit()
     * 
     * Displays the customer profile editing page.
     * Shows current profile information and form fields.
     * 
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        return view('profile.edit', [
            'user' => auth()->user()
        ]);
    }

    /**
     * updateAvatar()
     * 
     * Handles profile picture upload with dual storage strategy:
     * 1. Local: Saves to public/storage/profilepic/ for immediate serving
     * 2. Google Drive: Async backup in customer's Drive folder
     * 
     * Process:
     * - Validates image file (5MB max)
     * - Generates unique filename with timestamp
     * - Creates storage directory if missing
     * - Deletes old avatar if exists
     * - Stores new avatar locally
     * - Uploads copy to Google Drive (non-blocking)
     * - Updates account status if previously rejected
     * 
     * Validation:
     * - File type: image (JPEG, PNG, GIF, etc.)
     * - Max size: 5120 KB (5MB)
     * 
     * @param  \Illuminate\Http\Request $request Must contain 'avatar' file
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateAvatar(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'avatar' => ['required', 'image', 'max:5120'],
        ]);

        try {
            $file = $request->file('avatar');
            $filename = 'profile_' . $user->customerID . '_' . time() . '.' . $file->getClientOriginalExtension();
            $destinationPath = public_path('storage/profilepic');
            
            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true);
            }

            if ($user->avatar && file_exists(public_path($user->avatar))) {
                unlink(public_path($user->avatar));
            }

            // 1. Instant Local Save
            $file->move($destinationPath, $filename);
            $localPath = 'storage/profilepic/' . $filename;
            $user->avatar = $localPath;

            // 2. Dispatch Background Job
            \App\Jobs\UploadToDrive::dispatch($user->customerID, public_path($localPath), 'Profile Picture');

            if (!$user->blacklisted && $user->accountStat == 'rejected') {
                $user->accountStat = 'pending';
                $user->rejection_reason = null;
            }
            
            $user->save();

            return back()->with('status', 'Profile picture updated successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Avatar Update Failed: ' . $e->getMessage());
        }
    }

    /**
     * update()
     * 
     * Updates all customer profile information including personal data,
     * address, identity documents, and bank account details.
     * 
     * Process:
     * 1. Validates all input fields against database constraints
     * 2. Uploads identity documents to Google Drive
     * 3. Updates text fields in database
     * 4. Resets account status to pending for staff verification
     * 
     * Validation Rules:
     * - name: Required, string, letters/spaces only, max 100 chars
     * - email: Required, valid email, max 100 chars, unique (except own)
     * - phone/emergency_contact_no: Required, numbers/hyphens/+ only, max 20 chars
     * - emergency_contact_name: Required, letters/spaces only, max 100 chars
     * - addresses: Required, max 500 chars
     * - student_staff_id: Required, max 50 chars, unique
     * - ic_passport: Required, max 50 chars, unique
     * - driving_license_expiry: Required, must be after today
     * - nationality: Required, max 50 chars
     * - dob: Required, valid date
     * - faculty: Required, max 100 chars
     * - bank info: Required, max 100/50 chars
     * - Documents: Optional, JPEG/PNG, max 5MB
     * 
     * Document Upload (Google Drive):
     * - Finds/creates "[StudentID] - [FullName]" folder in Drive
     * - Uploads: Student Card, IC/Passport, Driving License
     * - File naming: "[Date] - [DocumentType].[ext]"
     * 
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    /**
     * Show the profile edit form.
     * 
     * @return \Illuminate\View\View
     */
    public function showEdit()
    {
        $user = auth()->user();
        return view('profile.edit', compact('user'));
    }

    /**
     * update()
     * 
     * Update customer profile information including personal details, addresses, and banking information.
     * Handles profile field updates with validation and status management.
     * 
     * Validation Rules:
     * - name: max 100, letters and spaces only
     * - email: max 100, unique per customer
     * - phone: max 20, numbers/hyphens/plus only
     * - emergency_contact_name: max 100, letters and spaces
     * - emergency_contact_no: max 20, numbers/hyphens/plus only
     * - faculty: max 100 characters
     * - stustaffID: max 50, unique per customer
     * - ic_passport: max 50, unique per customer
     * - bankName: max 100 characters
     * - bankAccountNo: max 50 characters
     * - nationality: max 50 characters
     * 
     * Process:
     * 1. Validate all input fields
     * 2. Map form fields to database columns (e.g., phone → phoneNo)
     * 3. Upload documents to Google Drive if provided
     * 4. Update customer record
     * 5. Reset status if previously rejected
     * 6. Return success/error message
     * 
     * @param Request $request The HTTP request with profile updates
     * @return \Illuminate\Http\RedirectResponse Redirect back with success/error message
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        // 1. VALIDATION
        $request->validate([
            'name' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z\s]+$/'],
            'email' => ['required', 'email', 'max:100', Rule::unique('customers', 'email')->ignore($user->customerID, 'customerID')],
            'phone' => ['required', 'regex:/^[0-9\-\+\s]+$/', 'max:20'],
            'emergency_contact_no' => ['required', 'regex:/^[0-9\-\+\s]+$/', 'max:20'],
            'emergency_contact_name' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z\s]+$/'],
            'home_address' => ['required', 'string', 'max:500'],
            'college_address' => ['required', 'string', 'max:500'],
            'student_staff_id' => ['required', 'string', 'max:50', Rule::unique('customers', 'stustaffID')->ignore($user->customerID, 'customerID')],
            'ic_passport' => ['required', 'string', 'max:50', Rule::unique('customers', 'ic_passport')->ignore($user->customerID, 'customerID')],
            'driving_license_expiry' => ['required', 'date', 'after_or_equal:today'],
            'nationality' => ['required', 'string', 'max:50'],
            'dob' => ['required', 'date'],
            'faculty' => ['required', 'string', 'max:100'],
            'bank_name' => ['required', 'string', 'max:100'],
            'bank_account_no' => ['required', 'string', 'max:50'],
            
            // Files (optional on updates)
            'student_card_image' => [$user->student_card_image ? 'nullable' : 'required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'ic_passport_image' => [$user->ic_passport_image ? 'nullable' : 'required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'driving_license_image' => [$user->driving_license_image ? 'nullable' : 'required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ], [
            'name.regex' => 'Full name can only contain letters and spaces.',
            'phone.regex' => 'Phone number can only contain numbers, hyphens, and plus signs.',
            'emergency_contact_no.regex' => 'Emergency contact number can only contain numbers, hyphens, and plus signs.',
            'emergency_contact_name.regex' => 'Emergency contact name can only contain letters and spaces.',
        ]);

        // 2. FILE PROCESSING & BACKGROUND UPLOAD
        $documents = [
            'student_card_image'    => 'Student_Card',
            'ic_passport_image'     => 'IC_Passport',
            'driving_license_image' => 'Driving_License'
        ];

        foreach ($documents as $inputKey => $fileLabel) {
            if ($request->hasFile($inputKey)) {
                $file = $request->file($inputKey);
                $localFileName = $user->customerID . '_' . $inputKey . '_' . time() . '.jpg';
                $destinationPath = public_path('storage/documents');

                // Create directory if it doesn't exist
                if (!File::exists($destinationPath)) {
                    File::makeDirectory($destinationPath, 0755, true);
                }

                // Define the full path for saving
                $path = 'storage/documents/' . $localFileName;

                // 1. Initialize the manager
                $manager = new ImageManager(new Driver());

                // 2. Use decode() with the current file from the loop
                $image = $manager->decode($file->get());

                // 3. Process and Save
                $image->scale(width: 800); 
                $image->save(public_path($path));

                // 4. Update the user record
                $user->$inputKey = $path;

                // Dispatch Background Job
                \App\Jobs\UploadToDrive::dispatch(
                    $user->customerID, 
                    public_path($path), 
                    now()->format('Y-m-d') . " - " . $fileLabel
                );
            }
        }

        // 3. UPDATE TEXT FIELDS
        $user->fullName = strtoupper($request->name);       
        $user->stustaffID = strtoupper($request->student_staff_id); 
        $user->ic_passport = strtoupper($request->ic_passport);
        $user->email = $request->email;
        $user->phoneNo = $request->phone;
        $user->driving_license_expiry = $request->driving_license_expiry; 
        $user->homeAddress = $request->home_address;
        $user->collegeAddress = $request->college_address;
        $user->nationality = $request->nationality;
        $user->dob = $request->dob;
        $user->emergency_contact_no = $request->emergency_contact_no;
        $user->emergency_contact_name = $request->emergency_contact_name;
        $user->faculty = $request->faculty;
        $user->bankName = $request->bank_name;
        $user->bankAccountNo = $request->bank_account_no;

        // 4. STATUS MANAGEMENT
        if (!$user->blacklisted) {
            $user->accountStat = 'pending';
            $user->rejection_reason = null; 
        }

        $user->save();
        return redirect()->route('profile.edit')->with('status', 'Profile updated successfully!');
    }

    /**
     * updatePassword()
     * 
     * Handles customer password change with verification of current password.
     * 
     * Security:
     * - Requires current password verification (prevents unauthorized changes)
     * - Uses Hash::check() for secure password comparison
     * - New password must be confirmed (matching password fields)
     * 
     * Validation Rules:
     * - current_password: Required, must match user's existing password
     * - password: Required, min 8 chars, must be confirmed
     * 
     * Error Handling:
     * - Throws ValidationException if current password doesn't match
     * - Exception prevents password change and displays error message
     * 
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function updatePassword(Request $request)
    {
        $user = auth()->user();
        // Validate input with database constraints
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // Verify current password using Hash::check (compares against bcrypt hash)
        if (!Hash::check($request->current_password, $user->password)) {
             // Throw validation exception with custom error message
             throw ValidationException::withMessages([
                'current_password' => ['The provided password does not match your current password.'],
            ]);
        }

        // Hash new password before storing
        $user->password = Hash::make($request->password);
        // Save to database
        $user->save();

        return redirect()->route('profile.edit')->with('status', 'Password updated successfully!');
    }

    public function previewDocument($type)
    {
        $user = auth()->user();
        $path = $user->{$type . '_image'}; // e.g., student_card_image

        // 1. Check Local Storage first
        if ($path && File::exists(public_path($path))) {
            return response()->file(public_path($path));
        }

        // 2. FALLBACK: Redirect to Google Drive
        // Note: This requires you to store the Drive File ID in your DB, 
        // or search for the file by name using your existing Drive logic.
        try {
            // Logic to get the 'webViewLink' from Google Drive API for this file
            // For now, we can redirect to a search or a stored Drive URL
            $driveLink = $user->{$type . '_drive_link'}; 
            
            if ($driveLink) {
                return redirect()->away($driveLink);
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Document could not be retrieved from local or cloud storage.');
        }

        abort(404, 'File not found locally or on Drive.');
    }
}