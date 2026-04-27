<?php

namespace App\Jobs;

use App\Services\GoogleDriveService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UploadGenericFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $localPath;
    protected $fileName;
    protected $folderId;

    public function __construct($localPath, $fileName, $folderId)
    {
        $this->localPath = $localPath;
        $this->fileName = $fileName;
        $this->folderId = $folderId;
    }

    public function handle(GoogleDriveService $driveService)
    {
        if (file_exists($this->localPath)) {
            $driveService->uploadFromLocalPath($this->localPath, $this->fileName, $this->folderId);
        }
    }
}