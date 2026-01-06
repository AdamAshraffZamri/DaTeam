<?php

namespace App\Services;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class GoogleDriveService
{
    protected $client;
    protected $service;

    public function __construct()
    {
        // 1. Initialize Client with .env credentials
        $this->client = new Client();
        $this->client->setClientId(env('GOOGLE_DRIVE_CLIENT_ID'));
        $this->client->setClientSecret(env('GOOGLE_DRIVE_CLIENT_SECRET'));
        $this->client->refreshToken(env('GOOGLE_DRIVE_REFRESH_TOKEN'));
        
        // 2. Define Scope
        $this->client->addScope(Drive::DRIVE_FILE);
        
        // 3. Initialize Drive Service
        $this->service = new Drive($this->client);
    }

    /**
     * Upload a file to Google Drive.
     *
     * @param UploadedFile $file
     * @param string|null $folderId
     * @param string|null $customName
     * @return string The Web View Link (URL) of the uploaded file.
     */
    public function uploadFile(UploadedFile $file, $folderId = null, $customName = null)
    {
        try {
            // Determine filename: Use custom name if provided, else original
            $name = $customName 
                ? $customName . '.' . $file->getClientOriginalExtension() 
                : $file->getClientOriginalName();

            $fileMetadata = new DriveFile([
                'name' => $name,
                'parents' => $folderId ? [$folderId] : [] 
            ]);

            $content = file_get_contents($file->getRealPath());

            // Upload
            $uploadedFile = $this->service->files->create($fileMetadata, [
                'data' => $content,
                'mimeType' => $file->getMimeType(),
                'uploadType' => 'multipart',
                'fields' => 'id, webViewLink' // Request the View Link immediately
            ]);

            // Return the clickable link
            return $uploadedFile->webViewLink;

        } catch (\Exception $e) {
            Log::error('Google Drive Upload Error: ' . $e->getMessage());
            return null; // Return null if upload fails so we can fallback
        }
    }

    /**
     * Upload raw content (string) to Google Drive.
     * Useful for generated PDFs.
     */
    public function uploadFromString($content, $fileName, $folderId = null)
    {
        try {
            if ($this->client->isAccessTokenExpired()) {
                $this->client->fetchAccessTokenWithRefreshToken();
            }

            $fileMetadata = new DriveFile([
                'name' => $fileName,
                'parents' => $folderId ? [$folderId] : []
            ]);

            $uploadedFile = $this->service->files->create($fileMetadata, [
                'data' => $content,
                'mimeType' => 'application/pdf',
                'uploadType' => 'multipart',
                'fields' => 'id, webViewLink'
            ]);

            return $uploadedFile->webViewLink;

        } catch (\Exception $e) {
            Log::error('Drive Upload Error: ' . $e->getMessage());
            return null;
        }
    }
}