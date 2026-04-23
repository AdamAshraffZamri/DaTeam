<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Google\Client;
use Google\Service\Drive;
use Masbug\Flysystem\GoogleDriveAdapter; // FIXED: Correct Namespace
use League\Flysystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Customer;
use App\Models\Maintenance;
use App\Models\Vehicle;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Share pending counts with all staff views
        View::composer(['layouts.staff', 'layouts.customer'], function ($view) {
            if (Auth::guard('staff')->check()) {
                // 1. PENDING BOOKINGS - Bookings awaiting staff action
                $pendingBookingsCount = Booking::whereIn('bookingStatus', ['Pending', 'Submitted', 'Paid', 'Fully Paid', 'Refund Requested'])->count();
                
                // 2. PENDING DEPOSITS - Refunds awaiting processing
                $pendingDepositsCount = \App\Models\Booking::whereHas('payments', function($q) {
                    $q->where('depoAmount', '>', 0) // Only count if a deposit exists
                    ->where(function($sub) {
                        // 1. Statuses that are strictly "Not Updated"
                        $sub->whereIn('depoStatus', ['Requested', 'Pending', 'Holding'])
                            // 2. OR Processed but the staff forgot to add a remark (Still needs action)
                            ->orWhere(function($inner) {
                                $inner->where('depoStatus', 'Processed')
                                        ->where(function($rem) {
                                            $rem->whereNull('remarks')
                                                ->orWhere('remarks', '');
                                        });
                            });
                    });
                })->count();
                
                // 3. PENDING FLEET/MAINTENANCE - Vehicles with ongoing/incomplete maintenance
                $pendingFleetCount = Maintenance::whereNotNull('start_time')
                    ->whereNull('end_time')
                    ->count();
                
                // 4. PENDING CUSTOMERS - Customers pending verification
                $pendingCustomersCount = Customer::where('accountStat', 'pending')->count();
                
                $view->with([
                    'pendingBookingsCount' => $pendingBookingsCount,
                    'pendingDepositsCount' => $pendingDepositsCount,
                    'pendingFleetCount' => $pendingFleetCount,
                    'pendingCustomersCount' => $pendingCustomersCount,
                ]);
            }
        });

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