<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use Google\Client;
use Google\Service\Drive;
use Masbug\Flysystem\GoogleDriveAdapter; // FIXED: Correct Namespace
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
                $client->setClientId($config['clientId']);
                $client->setClientSecret($config['clientSecret']);
                $client->refreshToken($config['refreshToken']);
                
                $service = new Drive($client);
                $adapter = new GoogleDriveAdapter($service, $config['folderId'] ?? '/');
                
                $driver = new Filesystem($adapter);
                
                // Wrap the Flysystem driver in a Laravel FilesystemAdapter
                return new FilesystemAdapter($driver, $adapter);
            });
        } catch (\Exception $e) {
            // Log the error if needed, but don't crash the boot process
            // \Log::error('Google Drive Driver Error: ' . $e->getMessage());
        }
    }
}