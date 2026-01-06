<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB; // <--- Import this
use Illuminate\Support\Facades\Schema; // <--- Import this

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // Standard length fix (you likely already have this)
        Schema::defaultStringLength(191);

        // --- ADD THIS BLOCK ---
        // Fix for "General error: 1 near ')'" in SQLite tests
        if (DB::getConnection()->getDriverName() === 'sqlite') {
            DB::connection()
                ->getDoctrineSchemaManager()
                ->getDatabasePlatform()
                ->registerDoctrineTypeMapping('enum', 'string');
        }
        // ----------------------
    }
}