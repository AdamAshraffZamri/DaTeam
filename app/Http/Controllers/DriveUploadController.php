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
        // FIX: Use config() so this works in production after config:cache
        return Storage::build([
            'driver' => 'google',
            'clientId' => config('services.google.client_id'),
            'clientSecret' => config('services.google.client_secret'),
            'refreshToken' => config('services.google.refresh_token'),
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

        // 2. Connect to the "Reports" Folder using config
        $disk = $this->getDriveDisk(config('services.google.folder_reports'));

        // 3. Create Name: "January Report - 2026-01-05.pdf"
        $file = $request->file('report_file');
        $extension = $file->getClientOriginalExtension();
        $fileName = Carbon::now()->format('F " Report " - Y-m-d') . '.' . $extension;

        // 4. Upload
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

        // 2. Connect to the "Customer Info" Folder using config
        $disk = $this->getDriveDisk(config('services.google.folder_customer_info'));

        // 3. Define Folder Name: "101 - Ali"
        $staffId = $request->input('staff_id');
        $staffName = $request->input('name');
        $folderName = "{$staffId} - {$staffName}";

        // 4. Define File Name: "2026-01-05 - IC Photo.jpg"
        $description = $request->input('description');
        $file = $request->file('photo');
        $date = Carbon::now()->format('Y-m-d');
        
        $time = Carbon::now()->format('H-i-s'); 
        $fileName = "{$date} - {$description} ({$time})." . $file->getClientOriginalExtension();

        // 5. Upload
        $disk->putFileAs($folderName, $file, $fileName);

        return back()->with('success', 'Customer Info Uploaded Successfully!');
    }
}