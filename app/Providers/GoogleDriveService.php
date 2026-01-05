<?php

namespace App\Services;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class GoogleDriveService
{
    protected $client;
    protected $service;

    public function __construct()
    {
        // 1. Initialize the Google Client
        $this->client = new Client();
        
        // 2. Set the path to your Service Account Credentials
        $credentialsPath = storage_path('app/google-drive/credentials.json');
        
        if (!file_exists($credentialsPath)) {
            throw new \Exception("Credentials file not found at: " . $credentialsPath);
        }

        $this->client->setAuthConfig($credentialsPath);
        
        // 3. Define the Scope (Permissions)
        $this->client->addScope(Drive::DRIVE);
        
        // 4. Initialize the Drive Service
        $this->service = new Drive($this->client);
    }

    /**
     * Upload a file to a specific Google Drive Folder.
     *
     * @param UploadedFile $file The file object from the request.
     * @param string|null $folderId The ID of the folder on Google Drive.
     * @return string The ID of the uploaded file.
     */
    public function uploadFile(UploadedFile $file, $folderId = null)
    {
        $fileMetadata = new DriveFile([
            'name' => $file->getClientOriginalName(),
            'parents' => $folderId ? [$folderId] : [] // Add to folder if ID is provided
        ]);

        $content = file_get_contents($file->getRealPath());

        $uploadedFile = $this->service->files->create($fileMetadata, [
            'data' => $content,
            'mimeType' => $file->getMimeType(),
            'uploadType' => 'multipart',
            'fields' => 'id'
        ]);

        return $uploadedFile->id;
    }
}