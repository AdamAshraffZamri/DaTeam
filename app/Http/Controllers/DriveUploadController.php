<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Services\GoogleDriveService; // Import the service

class DriveUploadController extends Controller
{
    protected $driveService;

    public function __construct(GoogleDriveService $driveService)
    {
        $this->driveService = $driveService;
    }

    // 1. UPLOAD MONTHLY REPORT
    public function uploadReport(Request $request)
    {
        $request->validate(['report_file' => 'required|file']);

        $file = $request->file('report_file');
        // Create Name without extension for helper, or handle manually
        $customName = Carbon::now()->format('F " Report " - Y-m-d'); 
        
        // Upload using Service
        $this->driveService->uploadFile(
            $file, 
            env('GOOGLE_DRIVE_REPORTS'), 
            $customName
        );

        return back()->with('success', 'Report Uploaded Successfully!');
    }

    // 2. UPLOAD CUSTOMER INFO
    public function uploadCustomer(Request $request)
    {
        $request->validate([
            'photo' => 'required|image',
            'staff_id' => 'required|string',
            'name' => 'required|string',
            'description' => 'required|string',
        ]);

        // Note: Creating sub-folders dynamically (like "101 - Ali") is complex 
        // with the raw API. For now, we will upload to the main Customer folder 
        // with a descriptive filename to avoid errors.
        
        $folderId = env('GOOGLE_DRIVE_CUSTOMER_INFORMATION');
        
        $staffId = $request->input('staff_id');
        $staffName = $request->input('name');
        $description = $request->input('description');
        $date = Carbon::now()->format('Y-m-d');
        $time = Carbon::now()->format('H-i-s'); 

        // New Filename format: "101 - Ali - 2026-01-05 - IC Photo (12-30-00)"
        $customName = "{$staffId} - {$staffName} - {$date} - {$description} ({$time})";

        $this->driveService->uploadFile(
            $request->file('photo'),
            $folderId,
            $customName
        );

        return back()->with('success', 'Customer Info Uploaded Successfully!');
    }
}