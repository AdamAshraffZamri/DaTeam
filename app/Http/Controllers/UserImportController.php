<?php

namespace App\Http\Controllers;

use App\Imports\UsersImport;
use App\Imports\BookingsImport;

class UserImportController extends Controller
{
    public function import()
    {
        try {
            $import = new UsersImport();
            $result = $import->import(storage_path('app/users.csv'));

            return $result;
        } catch (\Exception $e) {
            return "Import failed: " . $e->getMessage();
        }
    }

    public function importBookings()
    {
        try {
            $import = new BookingsImport();
            $result = $import->import(storage_path('app/bookings.csv'));

            return $result;
        } catch (\Exception $e) {
            return "Booking Import failed: " . $e->getMessage();
        }
    }
}