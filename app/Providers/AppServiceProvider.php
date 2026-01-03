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

    public function boot(): void
    {
        try {
            Storage::extend('google', function($app, $config) {
                $client = new Client();
                
                // Point to the file you saved in Step 1
                $client->setAuthConfig(storage_path('app/google-drive/service-account.json'));
                $client->addScope(Drive::DRIVE);
                
                $service = new Drive($client);
                
                // Create the adapter
                $adapter = new GoogleDriveAdapter($service, $config['folder'] ?? '/');
                $driver = new Filesystem($adapter);
                
                return new FilesystemAdapter($driver, $adapter);
            });
        } catch(\Exception $e) {
            // Handle initialization errors silently or log them
        }
    }
}