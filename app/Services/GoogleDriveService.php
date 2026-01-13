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
        $this->client = new Client();
        
        $clientId = config('services.google.client_id');
        $clientSecret = config('services.google.client_secret');
        $refreshToken = config('services.google.refresh_token');

        if (!$refreshToken) {
            throw new \Exception('Google Refresh Token is missing. Please check .env and config/services.php.');
        }

        $this->client->setClientId($clientId);
        $this->client->setClientSecret($clientSecret);
        
        // 1. Fetch the new Access Token
        $token = $this->client->fetchAccessTokenWithRefreshToken($refreshToken);

        // 2. CHECK FOR ERRORS: If the refresh token is invalid, Google returns an error key
        if (isset($token['error'])) {
            $errorMsg = 'Google Drive Auth Error: ' . json_encode($token);
            Log::error($errorMsg);
            throw new \Exception($errorMsg . " (Your Refresh Token is likely expired or revoked. Please generate a new one.)");
        }

        // 3. Explicitly set the token on the client
        $this->client->setAccessToken($token);

        // 4. Define Scope & Service
        $this->client->addScope(Drive::DRIVE_FILE);
        $this->service = new Drive($this->client);
    }

    public function uploadFile(UploadedFile $file, $folderId = null, $customName = null)
    {
        try {
            $name = $customName 
                ? $customName . '.' . $file->getClientOriginalExtension() 
                : $file->getClientOriginalName();

            $fileMetadata = new DriveFile([
                'name' => $name,
                'parents' => $folderId ? [$folderId] : [] 
            ]);

            $content = file_get_contents($file->getRealPath());

            $uploadedFile = $this->service->files->create($fileMetadata, [
                'data' => $content,
                'mimeType' => $file->getMimeType(),
                'uploadType' => 'multipart',
                'fields' => 'id, webViewLink'
            ]);

            return $uploadedFile->webViewLink;

        } catch (\Exception $e) {
            Log::error('Google Drive Upload Error: ' . $e->getMessage());
            throw $e; 
        }
    }

    public function uploadFromString($content, $fileName, $folderId = null)
    {
        try {
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
            throw $e;
        }
    }
}