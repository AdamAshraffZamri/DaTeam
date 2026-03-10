<?php

namespace App\Imports;

use App\Models\Customer;
use Illuminate\Support\Facades\Hash;

class UsersImport
{
    public function import($filePath)
    {
        if (!file_exists($filePath)) {
            throw new \Exception('File not found: ' . $filePath);
        }

        $file = fopen($filePath, 'r');
        if ($file === false) {
            throw new \Exception('Cannot open file: ' . $filePath);
        }

        $imported = 0;
        $skipped = 0;

        try {
            while (($row = fgetcsv($file)) !== false) {
                if (empty($row[0]) || empty($row[1]) || empty($row[2])) {
                    continue; // Skip empty rows
                }

                // Check if customer already exists
                $email = trim($row[1]);
                if (Customer::where('email', $email)->exists()) {
                    $skipped++;
                    continue;
                }

                Customer::create([
                    'fullName' => trim($row[0]),
                    'email' => $email,
                    'password' => Hash::make($row[2]),
                ]);
                $imported++;
            }
        } finally {
            fclose($file);
        }

        return "Imported: $imported, Skipped: $skipped";
    }
}