<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use Google\Client;
use Google\Service\Drive;
use Masbug\Flysystem\GoogleDrive\GoogleDriveAdapter;
use League\Flysystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    // In app/Providers/AppServiceProvider.php

    public function boot(): void
    {
        try {
            // Use the full path string to check existence first
            if (class_exists('Masbug\Flysystem\GoogleDrive\GoogleDriveAdapter')) {
                
                Storage::extend('google', function($app, $config) {
                    $client = new Client(); // Use backslash \Google
                    
                    $credentialsPath = storage_path('app/google-drive/service-account.json');
                    
                    if (!file_exists($credentialsPath)) {
                        throw new \Exception("Missing Google Drive credentials: " . $credentialsPath);
                    }

                    $client->setAuthConfig($credentialsPath);
                    $client->addScope(Drive::DRIVE);
                    
                    $service = new Drive($client);
                    
                    // Instantiate using the FULL namespace path
                    $adapter = new GoogleDriveAdapter($service, $config['folder'] ?? '/');
                    
                    return new FilesystemAdapter(
                        new Filesystem($adapter), 
                        $adapter
                    );
                });
            }
        } catch(\Exception $e) {
            \Log::error("Google Drive Boot Error: " . $e->getMessage());
        }
    }
}