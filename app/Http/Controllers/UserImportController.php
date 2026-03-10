<?php

namespace App\Http\Controllers;

use App\Imports\UsersImport;

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
}