<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
// Google Drive Imports
use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use GuzzleHttp\Client as GuzzleClient;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.edit', [
            'user' => auth()->user()
        ]);
    }

    // FORM 1: Update Profile Info & Upload Docs + Avatar to Google Drive
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
            'driving_license_no' => ['required', 'string', 'max:50', Rule::unique('customers', 'drivingNo')->ignore($user->customerID, 'customerID')],
            
            'nationality' => ['required', 'string', 'max:100'],
            'dob' => ['required', 'date'],
            'faculty' => ['required', 'string', 'max:255'],
            'bank_name' => ['required', 'string', 'max:100'],
            'bank_account_no' => ['required', 'string', 'max:50'],
            
            // IMAGES
            'avatar' => ['nullable', 'image', 'max:2048'], 
            'student_card_image' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:5120'],
            'ic_passport_image' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:5120'],
            'driving_license_image' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:5120'],
        ]);

        // 2. HANDLE AVATAR (Local Save for Website Display)
        // We MUST save this locally first so the user can see their picture on the website
        if ($request->hasFile('avatar')) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            // Save path to DB (e.g., "uploads/xyz.jpg")
            $user->avatar = $request->file('avatar')->store('uploads', 'public');
        }

        // 3. HANDLE GOOGLE DRIVE UPLOADS (Docs + Avatar Copy)
        // We define the files we want to upload to Drive
        $filesToUpload = [
            'student_card_image'    => 'Student_Card',
            'ic_passport_image'     => 'IC_Passport',
            'driving_license_image' => 'License',
            'avatar'                => 'Profile_Picture' // Added Avatar here
        ];

        // Check if ANY of these files were uploaded
        $hasFiles = false;
        foreach (array_keys($filesToUpload) as $key) {
            if ($request->hasFile($key)) {
                $hasFiles = true;
                break;
            }
        }

        if ($hasFiles) {
            try {
                // A. Initialize Drive Service
                $service = $this->getDriveService();

                // B. Define Folder Name: "Student/Staff ID - Customer Name"
                $folderName = "{$request->student_staff_id} - {$request->name}";

                // C. Find or Create the specific User Folder
                $userFolderId = $this->findOrCreateFolder($service, $folderName);

                // D. Loop through files and upload
                foreach ($filesToUpload as $inputName => $fileLabel) {
                    if ($request->hasFile($inputName)) {
                        $file = $request->file($inputName);

                        // File Naming: YYYY-MM-DD_HH-mm_Label.ext
                        $timestamp = now()->format('Y-m-d_H-i');
                        $extension = $file->getClientOriginalExtension();
                        $filename = "{$timestamp}_{$fileLabel}.{$extension}";

                        // Upload to Drive
                        $fileId = $this->uploadFileToFolder($service, $file, $filename, $userFolderId);

                        // E. SAVE DRIVE ID TO DATABASE
                        // Important: We only save the Drive ID for documents.
                        // For 'avatar', we KEEP the local path ($user->avatar) so the website image doesn't break.
                        if ($inputName !== 'avatar') {
                            $user->$inputName = $fileId; 
                        }
                    }
                }

            } catch (\Exception $e) {
                return back()->with('error', 'Drive Upload Failed: ' . $e->getMessage())->withInput();
            }
        }

        // 4. MANUAL MAPPING
        $user->fullName = $request->name;       
        $user->email = $request->email;
        $user->phoneNo = $request->phone;
        $user->stustaffID = $request->student_staff_id; 
        $user->ic_passport = $request->ic_passport;     
        $user->drivingNo = $request->driving_license_no; 
        $user->homeAddress = $request->home_address;
        $user->collegeAddress = $request->college_address;
        $user->nationality = $request->nationality;
        $user->dob = $request->dob;
        $user->emergency_contact_no = $request->emergency_contact_no;
        $user->emergency_contact_name = $request->emergency_contact_name;
        $user->faculty = $request->faculty;
        $user->bankName = $request->bank_name;
        $user->bankAccountNo = $request->bank_account_no;

        // 5. RESET STATUS (If not blacklisted)
        if (!$user->blacklisted) {
            $user->accountStat = 'pending';
            $user->rejection_reason = null; 
        }

        $user->save();

        return redirect()->route('profile.edit')->with('status', 'Profile updated and files saved to Drive successfully!');
    }

    // FORM 2: Update Password ONLY
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

    // =========================================================
    // GOOGLE DRIVE HELPER FUNCTIONS
    // =========================================================

    private function getDriveService()
    {
        $client = new Client();
        
        // SSL Fix for Localhost
        $httpClient = new GuzzleClient([
            'verify' => false,
            'curl' => [
                CURLOPT_CAINFO => __FILE__, // Hack to point to existing file to bypass empty check
                CURLOPT_SSL_VERIFYPEER => false,
            ]
        ]);
        $client->setHttpClient($httpClient);
        
        $client->setClientId(env('GOOGLE_DRIVE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_DRIVE_CLIENT_SECRET'));
        $client->refreshToken(env('GOOGLE_DRIVE_REFRESH_TOKEN'));

        return new Drive($client);
    }

    private function findOrCreateFolder($service, $folderName)
    {
        // TARGET FOLDER ID from .env (Customer Information Folder)
        $parentFolderId = env('GOOGLE_DRIVE_CUSTOMER_INFORMATION'); 

        // 1. Check if folder exists inside the parent folder
        $query = "mimeType='application/vnd.google-apps.folder' and name = '$folderName' and '$parentFolderId' in parents and trashed = false";
        
        $files = $service->files->listFiles([
            'q' => $query,
            'spaces' => 'drive'
        ]);

        if (count($files->getFiles()) > 0) {
            return $files->getFiles()[0]->getId();
        }

        // 2. Create if not exists
        $fileMetadata = new DriveFile([
            'name' => $folderName,
            'mimeType' => 'application/vnd.google-apps.folder',
            'parents' => [$parentFolderId]
        ]);

        $folder = $service->files->create($fileMetadata, ['fields' => 'id']);
        return $folder->id;
    }

    private function uploadFileToFolder($service, $file, $filename, $folderId)
    {
        $fileMetadata = new DriveFile([
            'name' => $filename,
            'parents' => [$folderId]
        ]);

        $content = file_get_contents($file->getRealPath());

        $uploadedFile = $service->files->create($fileMetadata, [
            'data' => $content,
            'mimeType' => $file->getMimeType(),
            'uploadType' => 'multipart',
            'fields' => 'id'
        ]);

        return $uploadedFile->id;
    }
}