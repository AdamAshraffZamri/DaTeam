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

        // 3. Insert 12 Hasta Travel & Tours Vehicles
        $vehicles = [
        [
            'plateNo' => 'JWD9496', 
            'brand' => 'Honda', 
            'model' => 'Vario 160 CC', 
            'vehicle_category' => 'bike', 
            'type' => 'Scooter', 
            'owner_name' => 'HASTA TRAVEL & TOURS', 
            'hourly_rates' => ['1'=>10, '3'=>18, '5'=>25, '7'=>31, '9'=>36, '12'=>40, '24'=>43], 
            'year' => 2023, 
            'color' => 'Yellow',
            'image' => 'vehicles/jwd9496.jpg',
            'mileage' => 1111809,
            'baseDepo' => 50,
            'fuelType' => 'Petrol RON95'
        ],
        [
            'plateNo' => 'CEX5224', 
            'brand' => 'Perodua', 
            'model' => 'Axia (2nd Gen)', 
            'vehicle_category' => 'car', 
            'type' => 'Compact', 
            'owner_name' => 'SITI ARINA BINTI MD LATER', 
            'hourly_rates' => ['1'=>40, '3'=>60, '5'=>70, '7'=>75, '9'=>85, '12'=>115, '24'=>140], 
            'year' => 2024, 
            'color' => 'Blue',
            'image' => 'vehicles/cex5224.jpg',
            'mileage' => 31368,
            'baseDepo' => 50,
            'fuelType' => 'Petrol RON95'
        ],
        [
            'plateNo' => 'MCP6113', 
            'brand' => 'Perodua', 
            'model' => 'Axia (1st Gen)', 
            'vehicle_category' => 'car', 
            'type' => 'Compact', 
            'owner_name' => 'HASTA TRAVEL & TOURS', 
            'hourly_rates' => ['1'=>35, '3'=>55, '5'=>65, '7'=>70, '9'=>85, '12'=>95, '24'=>120], 
            'year' => 2018, 
            'color' => 'White',
            'image' => 'vehicles/mcp6113.jpg',
            'mileage' => 82409,
            'baseDepo' => 50,
            'fuelType' => 'Petrol RON95'
        ],
        [
            'plateNo' => 'JQU1957', 
            'brand' => 'Perodua', 
            'model' => 'Axia (1st Gen)', 
            'vehicle_category' => 'car', 
            'type' => 'Compact', 
            'owner_name' => 'MUHAMMAD IZZUDIN', 
            'hourly_rates' => ['1'=>35, '3'=>55, '5'=>65, '7'=>70, '9'=>85, '12'=>95, '24'=>120], 
            'year' => 2015, 
            'color' => 'Green',
            'image' => 'vehicles/jqu1957.jpg',
            'mileage' => 316957,
            'baseDepo' => 50,
            'fuelType' => 'Petrol RON95'
        ],
        [
            'plateNo' => 'JPN1416', 
            'brand' => 'Perodua', 
            'model' => 'Myvi (2nd Gen)', 
            'vehicle_category' => 'car', 
            'type' => 'Hatchback', 
            'owner_name' => 'HASTA TRAVEL & TOURS', 
            'hourly_rates' => ['1'=>40, '3'=>60, '5'=>70, '7'=>75, '9'=>90, '12'=>100, '24'=>130], 
            'year' => 2013, 
            'color' => 'Purple',
            'image' => 'vehicles/jpn1416.jpg',
            'mileage' => 142857,
            'baseDepo' => 50,
            'fuelType' => 'Petrol RON95'
        ],
        [
            'plateNo' => 'NDD7803', 
            'brand' => 'Perodua', 
            'model' => 'Axia (1st Gen)', 
            'vehicle_category' => 'car', 
            'type' => 'Compact', 
            'owner_name' => 'HASTA TRAVEL & TOURS', 
            'hourly_rates' => ['1'=>35, '3'=>55, '5'=>65, '7'=>70, '9'=>85, '12'=>95, '24'=>120], 
            'year' => 2016, 
            'color' => 'Gold',
            'image' => 'vehicles/ndd7803.jpg',
            'mileage' => 93492,
            'baseDepo' => 50,
            'fuelType' => 'Petrol RON95'
        ],
        [
            'plateNo' => 'UTM137', 
            'brand' => 'Proton', 
            'model' => 'X70', 
            'vehicle_category' => 'car', 
            'type' => 'SUV', 
            'owner_name' => 'NOSHANIDAH BINTI ABDULLAH', 
            'hourly_rates' => ['1'=>80, '3'=>150, '5'=>200, '7'=>250, '9'=>300, '12'=>350, '24'=>450], 
            'year' => 2022, 
            'color' => 'White',
            'image' => 'vehicles/utm137.jpg',
            'mileage' => 102850,
            'baseDepo' => 50,
            'fuelType' => 'Petrol RON95'
        ],
        [
            'plateNo' => 'UTM9473', 
            'brand' => 'Perodua', 
            'model' => 'Axia (2nd Gen)', 
            'vehicle_category' => 'car', 
            'type' => 'Compact', 
            'owner_name' => 'HASTA TRAVEL & TOURS', 
            'hourly_rates' => ['1'=>40, '3'=>60, '5'=>70, '7'=>75, '9'=>85, '12'=>115, '24'=>140], 
            'year' => 2025, 
            'color' => 'Black',
            'image' => 'vehicles/utm9473.jpg',
            'mileage' => 19554,
            'baseDepo' => 50,
            'fuelType' => 'Petrol RON95'
        ],
        [
            'plateNo' => 'UTM3057', 
            'brand' => 'Perodua', 
            'model' => 'Bezza 1.3', 
            'vehicle_category' => 'car', 
            'type' => 'Sedan', 
            'owner_name' => 'HASTA TRAVEL & TOURS', 
            'hourly_rates' => ['1'=>50, '3'=>70, '5'=>80, '7'=>85, '9'=>95, '12'=>125, '24'=>150], 
            'year' => 2025, 
            'color' => 'Red',
            'image' => 'vehicles/utm3057.jpg',
            'mileage' => 20504,
            'baseDepo' => 50,
            'fuelType' => 'Petrol RON95'
        ],
        [
            'plateNo' => 'UTM3365', 
            'brand' => 'Perodua', 
            'model' => 'Axia (2nd Gen)', 
            'vehicle_category' => 'car', 
            'type' => 'Compact', 
            'owner_name' => 'HASTA TRAVEL & TOURS', 
            'hourly_rates' => ['1'=>40, '3'=>60, '5'=>70, '7'=>75, '9'=>85, '12'=>115, '24'=>140], 
            'year' => 2024, 
            'color' => 'Silver',
            'image' => 'vehicles/utm3365.jpg',
            'mileage' => 34313,
            'baseDepo' => 50,
            'fuelType' => 'Petrol RON95'
        ],
        [
            'plateNo' => 'UTM3655', 
            'brand' => 'Perodua', 
            'model' => 'Bezza 1.3', 
            'vehicle_category' => 'car', 
            'type' => 'Sedan', 
            'owner_name' => 'HASTA TRAVEL & TOURS', 
            'hourly_rates' => ['1'=>50, '3'=>70, '5'=>80, '7'=>85, '9'=>95, '12'=>125, '24'=>150], 
            'year' => 2023, 
            'color' => 'Black',
            'image' => 'vehicles/utm3655.jpg',
            'mileage' => 64447,
            'baseDepo' => 50,
            'fuelType' => 'Petrol RON95'
        ],
        [
            'plateNo' => 'VC6522', 
            'brand' => 'Perodua', 
            'model' => 'Myvi (2nd Gen)', 
            'vehicle_category' => 'car', 
            'type' => 'Hatchback', 
            'owner_name' => 'AKMAL HADIN JALALUDIN', 
            'hourly_rates' => ['1'=>40, '3'=>60, '5'=>70, '7'=>75, '9'=>90, '12'=>100, '24'=>130], 
            'year' => 2012, 
            'color' => 'Silver',
            'image' => 'vehicles/vc6522.jpg',
            'mileage' => 280108,
            'baseDepo' => 50,
            'fuelType' => 'Petrol RON95'
        ],
    ];

        foreach ($vehicles as $v) {
            \App\Models\Vehicle::create(array_merge($v, [
                'availability' => 1, 
                'priceHour' => $v['hourly_rates']['1']
            ]));
        }

        // Call LoyaltySeeder to populate loyalty points data
        $this->call(\Database\Seeders\LoyaltySeeder::class);
    }
}