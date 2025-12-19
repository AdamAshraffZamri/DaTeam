<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | We have changed the default password broker to 'customers'.
    |
    */

    'defaults' => [
        'guard' => 'web',
        'passwords' => 'customers', // CHANGED: 'users' -> 'customers'
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | 'web' guard now uses the 'customers' provider.
    | We added a new 'staff' guard for the admin side.
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'customers', // CHANGED: 'users' -> 'customers'
        ],

        'staff' => [ // NEW: Added Staff Guard
            'driver' => 'session',
            'provider' => 'staff',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | We removed 'users' and added 'customers' and 'staff' providers.
    | These point to your new Models.
    |
    */

    'providers' => [
        'customers' => [ // NEW: Customers Provider
            'driver' => 'eloquent',
            'model' => App\Models\Customer::class, // Points to Customer Model
        ],

        'staff' => [ // NEW: Staff Provider
            'driver' => 'eloquent',
            'model' => App\Models\Staff::class, // Points to Staff Model
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    |
    | We renamed the 'users' broker to 'customers'.
    |
    */

    'passwords' => [
        'customers' => [ // CHANGED: Renamed broker to 'customers'
            'provider' => 'customers',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
        
        // You can add a staff password broker here later if needed
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    */

    'password_timeout' => 10800,

];