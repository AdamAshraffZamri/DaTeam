<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Staff;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create a Test Staff (Admin)
        Staff::firstOrCreate([
            'email' => 'admin@hasta.com'
        ], [
            'name' => 'Hasta Admin',
            'role' => 'admin',
            'phoneNo' => '011-10900700',
            'password' => Hash::make('password'), // Login with this password
            'active' => true
        ]);

        // 2. Create a Test Customer
        Customer::firstOrCreate([
            'email' => 'student@utm.my'
        ], [
            'fullName' => 'Ali Bin Abu',
            'phoneNo' => '012-3456789',
            'password' => Hash::make('password'),
            'drivingNo' => 'D-88213491',
            'homeAddress' => 'Kolej Tun Dr Ismail, UTM',
            'collegeAddress' => 'KTDI, UTM Skudai',
            'accountStat' => 'active'
        ]);

        // 3. Create 5 Dummy Vehicles (Using Correct Column Names)
        $vehicles = [
            [
                'plateNo' => 'JQV 8821',
                'model' => 'Perodua Axia 1.0 SE',
                'type' => 'Compact',
                'priceHour' => 4.50, // RM 108/day
                'availability' => true,
                'mileage' => 45000,
                'fuelType' => 'Petrol',
                'baseDepo' => 50.00
            ],
            [
                'plateNo' => 'VHG 1234',
                'model' => 'Perodua Myvi 1.5 AV',
                'type' => 'Hatchback',
                'priceHour' => 5.50, // RM 132/day
                'availability' => true,
                'mileage' => 22000,
                'fuelType' => 'Petrol',
                'baseDepo' => 50.00
            ],
            [
                'plateNo' => 'WYY 9988',
                'model' => 'Perodua Bezza 1.3 X',
                'type' => 'Sedan',
                'priceHour' => 6.00, // RM 144/day
                'availability' => true,
                'mileage' => 15000,
                'fuelType' => 'Petrol',
                'baseDepo' => 70.00
            ],
            [
                'plateNo' => 'BNC 5543',
                'model' => 'Proton Saga VVT',
                'type' => 'Sedan',
                'priceHour' => 5.00, // RM 120/day
                'availability' => true,
                'mileage' => 55000,
                'fuelType' => 'Petrol',
                'baseDepo' => 50.00
            ],
            [
                'plateNo' => 'JTA 7766',
                'model' => 'Perodua Alza (New)',
                'type' => 'MPV',
                'priceHour' => 8.00, // RM 192/day
                'availability' => true,
                'mileage' => 12000,
                'fuelType' => 'Petrol',
                'baseDepo' => 100.00
            ]
        ];

        foreach ($vehicles as $v) {
            Vehicle::create($v);
        }
    }
}