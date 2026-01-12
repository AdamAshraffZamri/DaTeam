<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DriveUploadController extends Controller
{
    // ==========================================
    // HELPER: Connect to Google Drive
    // ==========================================
    private function getDriveDisk($folderId)
    {
        return Storage::build([
            'driver' => 'google',
            'clientId' => env('GOOGLE_DRIVE_CLIENT_ID'),
            'clientSecret' => env('GOOGLE_DRIVE_CLIENT_SECRET'),
            'refreshToken' => env('GOOGLE_DRIVE_REFRESH_TOKEN'),
            'folderId' => $folderId,
        ]);
    }

    // ==========================================
    // 1. UPLOAD MONTHLY REPORT
    // ==========================================
    public function uploadReport(Request $request)
    {
        // 1. Validate
        $request->validate([
            'report_file' => 'required|file'
        ]);

        // 2. Connect to the "Reports" Folder
        $disk = $this->getDriveDisk(env('GOOGLE_DRIVE_REPORTS'));

        // 3. Create Name: "January Report - 2026-01-05.pdf"
        $file = $request->file('report_file');
        $extension = $file->getClientOriginalExtension();
        $fileName = Carbon::now()->format('F " Report " - Y-m-d') . '.' . $extension;

        // 4. Upload
        // We leave the first argument empty '' because we are already IN the reports folder
        $disk->putFileAs('', $file, $fileName);

        return back()->with('success', 'Report Uploaded Successfully!');
    }

    // ==========================================
    // 2. UPLOAD CUSTOMER INFO
    // ==========================================
    public function uploadCustomer(Request $request)
    {
        // 1. Validate
        $request->validate([
            'photo' => 'required|image',
            'staff_id' => 'required|string',
            'name' => 'required|string',
            'description' => 'required|string', // e.g. "Passport", "IC"
        ]);

        // 2. Connect to the "Customer Info" Folder
        $disk = $this->getDriveDisk(env('GOOGLE_DRIVE_CUSTOMER_INFORMATION'));

        // 3. Define Folder Name: "101 - Ali"
        $staffId = $request->input('staff_id');
        $staffName = $request->input('name');
        $folderName = "{$staffId} - {$staffName}";

        // 4. Define File Name: "2026-01-05 - IC Photo.jpg"
        $description = $request->input('description');
        $file = $request->file('photo');
        $date = Carbon::now()->format('Y-m-d');
        
        // Add time to filename so we don't delete old versions if uploaded twice today
        $time = Carbon::now()->format('H-i-s'); 
        $fileName = "{$date} - {$description} ({$time})." . $file->getClientOriginalExtension();

        // 5. Upload
        // Laravel will AUTOMATICALLY create the folder "$folderName" if it doesn't exist.
        $disk->putFileAs($folderName, $file, $fileName);

        return back()->with('success', 'Customer Info Uploaded Successfully!');
    }
}