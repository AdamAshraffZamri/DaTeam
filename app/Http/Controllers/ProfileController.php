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

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.edit', [
            'user' => auth()->user()
        ]);
    }

    // NEW: Handle Avatar Upload (Direct to Public Folder)
    public function updateAvatar(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'avatar' => ['required', 'image', 'max:5120'], // Max 5MB
        ]);

        try {
            // 1. LOCAL SAVE (Direct Move to Public Folder)
            $file = $request->file('avatar');
            $filename = 'profile_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            
            // Define Path: C:\laragon\www\DaTeam\public\storage\profilepic
            $destinationPath = public_path('storage/profilepic');
            
            // Create folder if it doesn't exist
            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true);
            }

            // Remove old file if exists
            if ($user->avatar && file_exists(public_path($user->avatar))) {
                unlink(public_path($user->avatar));
            }

            // Move the file physically
            $file->move($destinationPath, $filename);

            // Save the relative path to Database (e.g. "storage/profilepic/profile_1_123.jpg")
            $user->avatar = 'storage/profilepic/' . $filename;

            // 2. GOOGLE DRIVE UPLOAD (Backup - Optional)
            // (We keep this running in the background)
            try {
                $client = new Client();
                $client->setClientId(env('GOOGLE_DRIVE_CLIENT_ID'));
                $client->setClientSecret(env('GOOGLE_DRIVE_CLIENT_SECRET'));
                $client->refreshToken(env('GOOGLE_DRIVE_REFRESH_TOKEN'));
                $service = new Drive($client);

                $parentFolderId = env('GOOGLE_DRIVE_CUSTOMER_INFORMATION');
                $folderName = trim("{$user->stustaffID} - {$user->fullName}");
                $escapedName = str_replace("'", "\'", $folderName);
                
                // Find/Create Folder
                $query = "mimeType='application/vnd.google-apps.folder' and name = '$escapedName' and '$parentFolderId' in parents and trashed = false";
                $files = $service->files->listFiles(['q' => $query]);
                
                if (count($files->getFiles()) > 0) {
                    $userFolderId = $files->getFiles()[0]->getId();
                } else {
                    $folderMeta = new DriveFile([
                        'name' => $folderName,
                        'mimeType' => 'application/vnd.google-apps.folder',
                        'parents' => [$parentFolderId]
                    ]);
                    $userFolderId = $service->files->create($folderMeta, ['fields' => 'id'])->id;
                }

                // Upload
                $driveFileName = Carbon::now()->format('Y-m-d') . " - Profile Picture." . $file->getClientOriginalExtension();
                $fileMetadata = new DriveFile([
                    'name' => $driveFileName,
                    'parents' => [$userFolderId]
                ]);
                
                // We read the file from its new public location
                $content = file_get_contents(public_path($user->avatar));
                
                $service->files->create($fileMetadata, [
                    'data' => $content,
                    'mimeType' => $file->getMimeType(),
                    'uploadType' => 'multipart'
                ]);

            } catch (\Exception $e) {
                // Ignore Drive errors so local save still succeeds
            }

            // 3. Update User
            // Reset status if rejected so they can be re-verified
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

    // FORM 1: Update Profile (Main Info)
    public function update(Request $request)
    {
        $user = auth()->user();

        // 1. VALIDATION
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('customers', 'email')->ignore($user->customerID, 'customerID')],
            'phone' => ['required', 'string', 'max:20'],
            'emergency_contact_no' => ['required', 'string', 'max:20'],
            'emergency_contact_name' => ['required', 'string', 'max:255'],
            'home_address' => ['required', 'string', 'max:500'],
            'college_address' => ['required', 'string', 'max:500'],
            'student_staff_id' => ['required', 'string', 'max:50', Rule::unique('customers', 'stustaffID')->ignore($user->customerID, 'customerID')],
            'ic_passport' => ['required', 'string', 'max:50', Rule::unique('customers', 'ic_passport')->ignore($user->customerID, 'customerID')],
            'driving_license_expiry' => ['required', 'date', 'after:today'],
            'nationality' => ['required', 'string', 'max:100'],
            'dob' => ['required', 'date'],
            'faculty' => ['required', 'string', 'max:255'],
            'bank_name' => ['required', 'string', 'max:100'],
            'bank_account_no' => ['required', 'string', 'max:50'],
            
            // Files
            'student_card_image' => ['nullable', 'file', 'mimes:jpeg,png,jpg', 'max:5120'],
            'ic_passport_image' => ['nullable', 'file', 'mimes:jpeg,png,jpg', 'max:5120'],
            'driving_license_image' => ['nullable', 'file', 'mimes:jpeg,png,jpg', 'max:5120'],
        ]);

        // 2. GOOGLE DRIVE UPLOADS (Documents)
        $documents = [
            'student_card_image'    => 'Student Card',
            'ic_passport_image'     => 'IC Passport',
            'driving_license_image' => 'Driving License'
        ];

        foreach ($documents as $inputKey => $fileLabel) {
            if ($request->hasFile($inputKey)) {
                try {
                    // Manual Drive Upload Logic (Simplified for brevity as per your previous requests)
                    $client = new Client();
                    $client->setClientId(env('GOOGLE_DRIVE_CLIENT_ID'));
                    $client->setClientSecret(env('GOOGLE_DRIVE_CLIENT_SECRET'));
                    $client->refreshToken(env('GOOGLE_DRIVE_REFRESH_TOKEN'));
                    $service = new Drive($client);

                    $parentFolderId = env('GOOGLE_DRIVE_CUSTOMER_INFORMATION');
                    $folderName = trim("{$request->student_staff_id} - {$request->name}");
                    $escapedName = str_replace("'", "\'", $folderName);
                    
                    // Find/Create Folder
                    $query = "mimeType='application/vnd.google-apps.folder' and name = '$escapedName' and '$parentFolderId' in parents and trashed = false";
                    $files = $service->files->listFiles(['q' => $query]);

                    if (count($files->getFiles()) > 0) {
                        $userFolderId = $files->getFiles()[0]->getId();
                    } else {
                        $folderMeta = new DriveFile([
                            'name' => $folderName,
                            'mimeType' => 'application/vnd.google-apps.folder',
                            'parents' => [$parentFolderId]
                        ]);
                        $userFolderId = $service->files->create($folderMeta, ['fields' => 'id'])->id;
                    }

                    // Upload File
                    $file = $request->file($inputKey);
                    $fileName = Carbon::now()->format('Y-m-d') . " - $fileLabel." . $file->getClientOriginalExtension();
                    
                    $fileMeta = new DriveFile(['name' => $fileName, 'parents' => [$userFolderId]]);
                    $service->files->create($fileMeta, [
                        'data' => file_get_contents($file->getRealPath()),
                        'mimeType' => $file->getMimeType(),
                        'uploadType' => 'multipart'
                    ]);

                    $user->$inputKey = $folderName . '/' . $fileName;

                } catch (\Exception $e) {
                     return back()->with('error', 'Drive Upload Failed: ' . $e->getMessage());
                }
            }
        }

        // 3. UPDATE TEXT FIELDS
        $user->fullName = strtoupper($request->name);       
        $user->stustaffID = strtoupper($request->student_staff_id); 
        $user->ic_passport = strtoupper($request->ic_passport); // Changed to Uppercase String
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

        if (!$user->blacklisted && $user->accountStat == 'rejected') {
            $user->accountStat = 'pending';
            $user->rejection_reason = null; 
        }

        $user->save();

        return redirect()->route('profile.edit')->with('status', 'Profile updated successfully!');
    }

    public function updatePassword(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
             throw ValidationException::withMessages([
                'current_password' => ['The provided password does not match your current password.'],
            ]);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->route('profile.edit')->with('status', 'Password updated successfully!');
    }
}