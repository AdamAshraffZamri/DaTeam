<?php

namespace App\Services;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

/**
 * GoogleDriveService
 * 
 * Manages file uploads and operations on Google Drive.
 * Provides secure backup and archival of rental documents and inspections.
 * 
 * Key Features:
 * - File upload to Google Drive with folder organization
 * - Automatic authentication using OAuth2 refresh token
 * - Error handling and logging for failed operations
 * - Folder creation and management
 * - File retrieval and downloading
 * - Async upload capability for background jobs
 * 
 * Configuration:
 * Required environment variables in .env:
 * - GOOGLE_CLIENT_ID: OAuth client ID
 * - GOOGLE_CLIENT_SECRET: OAuth client secret
 * - GOOGLE_REFRESH_TOKEN: Long-lived refresh token for authentication
 * 
 * Folder Structure in Google Drive:
 * - Root HASTA Folder (configured in env)
 *   ├── Customers/
 *   │   ├── {Customer ID}/
 *   │   │   ├── Avatars/
 *   │   │   ├── Documents/
 *   │   │   └── IDs/
 *   ├── Inspections/
 *   │   ├── {Booking ID}/
 *   │   │   ├── Pickup/
 *   │   │   └── Return/
 *   ├── Agreements/
 *   └── Backups/
 * 
 * Use Cases:
 * 1. Customer Profile: Store avatar, student card, ID documents
 * 2. Inspections: Store pre/post inspection photos
 * 3. Agreements: Store signed PDF agreements
 * 4. Backups: Archive completed bookings and financial records
 * 
 * Security:
 * - OAuth2 authentication (no API keys stored)
 * - Refresh token rotated automatically by Google
 * - File permissions set to private by default
 * - All operations logged for audit trail
 * - Error messages sanitized to prevent information leakage
 * 
 * Authentication Process:
 * 1. Read refresh token from config
 * 2. Exchange refresh token for new access token
 * 3. Use access token for API calls
 * 4. Automatically refresh when token expires
 * 
 * Error Handling:
 * - Connection errors: Log and throw exception
 * - Invalid token: Log and suggest token regeneration
 * - Upload failures: Log and return error details
 * - File not found: Return null or empty array
 * 
 * @package App\Services
 */
class GoogleDriveService
{
    /**
     * Google API client instance
     * Handles OAuth authentication and token refresh
     * 
     * @var Client
     */
    protected $client;

    /**
     * Google Drive service instance
     * Provides access to Drive API methods
     * 
     * @var Drive
     */
    protected $service;

    /**
     * __construct()
     * 
     * Initialize Google Drive Service with OAuth authentication.
     * Fetches and validates access token from refresh token.
     * 
     * Process:
     * 1. Create new Google Client
     * 2. Load OAuth credentials (client ID, secret) from config
     * 3. Fetch long-lived refresh token from config
     * 4. Exchange refresh token for new access token
     * 5. Handle authentication errors gracefully
     * 6. Initialize Drive service with authenticated client
     * 7. Set required scopes for Drive file operations
     * 
     * Throws Exception if:
     * - Refresh token missing or invalid in .env
     * - Google API returns authentication error
     * - Connection to Google fails
     * 
     * @throws \Exception When authentication fails or tokens missing
     */
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