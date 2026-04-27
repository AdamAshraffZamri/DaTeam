<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Services\GoogleDriveService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UploadToDrive implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $customerId;
    public $localPath;
    public $fileName;

    public function __construct($customerId, $localPath, $fileName)
    {
        $this->customerId = $customerId;
        $this->localPath = $localPath;
        $this->fileName = $fileName;
    }

    public function handle(GoogleDriveService $driveService)
    {
        try {
            $customer = Customer::find($this->customerId);
            if (!$customer) {
                Log::error("UploadJob: Customer {$this->customerId} not found.");
                return;
            }

            // 1. Resolve Folder ID
            if (!$customer->drive_folder_id) {
                $folderName = "{$customer->stustaffID} - {$customer->fullName}";
                // Ensure this method exists in your GoogleDriveService
                $customer->drive_folder_id = $driveService->getOrCreateFolder($folderName);
                $customer->save();
            }

            // 2. Perform Upload
            if (file_exists($this->localPath)) {
                $driveService->uploadFromLocalPath(
                    $this->localPath, 
                    $this->fileName, 
                    $customer->drive_folder_id
                );
            } else {
                Log::warning("UploadJob: Local file missing at {$this->localPath}");
            }
        } catch (\Exception $e) {
            Log::error("UploadToDrive Job Failed: " . $e->getMessage());
            throw $e; // Re-throw so the queue knows it failed
        }
    }
}