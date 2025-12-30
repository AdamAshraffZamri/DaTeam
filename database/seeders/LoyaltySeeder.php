<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\LoyaltyPoint; // Pastikan nama model ni betul
use Illuminate\Support\Facades\Hash;

class LoyaltySeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ensure Student UTM account exists (Password: password123)
        $customer = Customer::firstOrCreate(
            ['email' => 'student@utm.my'],
            [
                'fullName' => 'Student UTM',
                'password' => Hash::make('password123'),
                'phoneNo' => '0123456789',
                'accountStat' => 'active',
            ]
        );

        // 2. Insert LoyaltyPoint record for this user using the model (matches migration)
        LoyaltyPoint::firstOrCreate(
            ['user_id' => $customer->customerID],
            ['points' => 1000, 'tier' => 'Silver']
        );

        // 3. Tambah beberapa user lain untuk nampak Top Rankings
        $users = [
            ['name' => 'Ali Abu', 'email' => 'ali@example.com', 'points' => 2500, 'tier' => 'Gold'],
            ['name' => 'Siti Nur', 'email' => 'siti@example.com', 'points' => 1500, 'tier' => 'Silver'],
            ['name' => 'Ahmad Jais', 'email' => 'ahmad@example.com', 'points' => 500, 'tier' => 'Bronze'],
        ];

        foreach ($users as $u) {
            $newCust = Customer::firstOrCreate(
                ['email' => $u['email']],
                [
                    'fullName' => $u['name'],
                    'password' => Hash::make('password123'),
                    'accountStat' => 'active',
                ]
            );

            LoyaltyPoint::firstOrCreate(
                ['user_id' => $newCust->customerID],
                ['points' => $u['points'], 'tier' => $u['tier']]
            );
        }

    }

   
}