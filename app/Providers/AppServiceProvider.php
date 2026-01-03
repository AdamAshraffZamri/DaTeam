<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use Google\Client;
use Google\Service\Drive;
use League\Flysystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    // Remove this line from the top if it exists:
// use Masbug\Flysystem\GoogleDrive\GoogleDriveAdapter; 

    public function boot(): void
    {
        
    }
}