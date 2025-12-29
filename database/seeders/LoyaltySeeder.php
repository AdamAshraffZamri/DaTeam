<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\LoyaltyPoint; // Pastikan nama model ni betul
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class LoyaltySeeder extends Seeder
{
    public function run(): void
    {
        // 1. Cipta Akaun Student UTM (Password: password123)
        $customer = Customer::create([
            'fullName' => 'Student UTM',
            'email' => 'student@utm.my',
            'password' => Hash::make('password123'),
            'phoneNo' => '0123456789',
            'accountStat' => 'active',
        ]);

        // 2. Masukkan Data Loyalty untuk user ni
        // Kita guna DB::table sebab model LoyaltyPoint kau mungkin belum set $table = 'loyalties'
        DB::table('loyalties')->insert([
            'customerID' => $customer->customerID,
            'tier' => 'Silver',
            'pointsEarned' => 1200,
            'pointsRedeemed' => 200,
            'totalPoints' => 1000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Tambah beberapa user lain untuk nampak Top Rankings
        $users = [
            ['name' => 'Ali Abu', 'email' => 'ali@example.com', 'points' => 2500, 'tier' => 'Gold'],
            ['name' => 'Siti Nur', 'email' => 'siti@example.com', 'points' => 1500, 'tier' => 'Silver'],
            ['name' => 'Ahmad Jais', 'email' => 'ahmad@example.com', 'points' => 500, 'tier' => 'Bronze'],
        ];

        foreach ($users as $u) {
            $newCust = Customer::create([
                'fullName' => $u['name'],
                'email' => $u['email'],
                'password' => Hash::make('password123'),
                'accountStat' => 'active',
            ]);

            DB::table('loyalties')->insert([
                'customerID' => $newCust->customerID,
                'tier' => $u['tier'],
                'pointsEarned' => $u['points'],
                'totalPoints' => $u['points'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->call([
            LoyaltySeeder::class,
        ]);
    }

   
}