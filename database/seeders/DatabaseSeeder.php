<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Staff;
use App\Models\Vehicle;
use App\Models\LoyaltyPoint;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Reward; 
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Faker\Factory as Faker;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Staff (4 Members: 2 Admin, 2 Staff)
        $this->seedStaff();

        // 2. Seed Vehicles (Fixed list)
        $this->seedVehicles();

    }

    private function seedStaff(): void
    {
        $staffMembers = [
            // Admin 1 (Super Admin)
            [
                'email' => 'admin@hasta.com',
                'name' => 'Hasta Admin',
                'role' => 'admin',
                'phoneNo' => '011-10900700',
            ],
        ];

        foreach ($staffMembers as $s) {
            Staff::firstOrCreate(['email' => $s['email']], [
                'name' => $s['name'],
                'role' => $s['role'],
                'phoneNo' => $s['phoneNo'],
                'password' => Hash::make('password'),
                'active' => true
            ]);
        }
    }

    /**
     * Seed Vehicles (UNCHANGED)
     */
    private function seedVehicles(): void
    {
        $vehicles = [
            ['plateNo' => 'JWD9496', 'brand' => 'Honda', 'model' => 'Vario 160 CC', 'vehicle_category' => 'bike', 'type' => 'Scooter', 'owner_name' => 'HASTA TRAVEL & TOURS', 'owner_nric' => '202001003057(1359376T)', 'hourly_rates' => ['1'=>10, '3'=>18, '5'=>25, '7'=>31, '9'=>36, '12'=>40, '24'=>43], 'year' => 2023, 'color' => 'Yellow', 'image' => 'vehicles/JWD9496.jpg', 'mileage' => 1111809, 'baseDepo' => 50, 'fuelType' => 'Petrol RON95'],
            ['plateNo' => 'QRP5205', 'brand' => 'Honda', 'model' => 'Vario 160 CC', 'vehicle_category' => 'bike', 'type' => 'Scooter', 'owner_name' => 'HASTA TRAVEL & TOURS', 'owner_nric' => '202001003057(1359376T)','hourly_rates' => ['1'=>10, '3'=>18, '5'=>25, '7'=>31, '9'=>36, '12'=>40, '24'=>43], 'year' => 2021, 'color' => 'Orange', 'image' => 'vehicles/QRP5205.jpg', 'mileage' => 0, 'baseDepo' => 50, 'fuelType' => 'Petrol RON95'],
            ['plateNo' => 'CEX5224', 'brand' => 'Perodua', 'model' => 'Axia (2nd Gen)', 'vehicle_category' => 'car', 'type' => 'Compact', 'owner_name' => 'SITI ARINA BINTI MD LATER', 'owner_nric' => '980122065328', 'hourly_rates' => ['1'=>40, '3'=>60, '5'=>70, '7'=>75, '9'=>85, '12'=>115, '24'=>140], 'year' => 2024, 'color' => 'Blue', 'image' => 'vehicles/CEX5224.jpg', 'mileage' => 31368, 'baseDepo' => 50, 'fuelType' => 'Petrol RON95'],
            ['plateNo' => 'MCP6113', 'brand' => 'Perodua', 'model' => 'Axia (1st Gen)', 'vehicle_category' => 'car', 'type' => 'Compact', 'owner_name' => 'HASTA TRAVEL & TOURS', 'owner_nric' => '202001003057(1359376T)', 'hourly_rates' => ['1'=>35, '3'=>55, '5'=>65, '7'=>70, '9'=>85, '12'=>95, '24'=>120], 'year' => 2018, 'color' => 'White', 'image' => 'vehicles/MCP6113.jpg', 'mileage' => 82409, 'baseDepo' => 50, 'fuelType' => 'Petrol RON95'],
            ['plateNo' => 'JQU1957', 'brand' => 'Perodua', 'model' => 'Axia (1st Gen)', 'vehicle_category' => 'car', 'type' => 'Compact', 'owner_name' => 'MUHAMMAD IZZUDIN', 'owner_nric' => '000606012111', 'hourly_rates' => ['1'=>35, '3'=>55, '5'=>65, '7'=>70, '9'=>85, '12'=>95, '24'=>120], 'year' => 2015, 'color' => 'Green', 'image' => 'vehicles/JQU1957.jpg', 'mileage' => 316957, 'baseDepo' => 50, 'fuelType' => 'Petrol RON95'],
            ['plateNo' => 'JPN1416', 'brand' => 'Perodua', 'model' => 'Myvi (2nd Gen)', 'vehicle_category' => 'car', 'type' => 'Hatchback', 'owner_name' => 'HASTA TRAVEL & TOURS', 'owner_nric' => '202001003057(1359376T)', 'hourly_rates' => ['1'=>40, '3'=>60, '5'=>70, '7'=>75, '9'=>90, '12'=>100, '24'=>130], 'year' => 2013, 'color' => 'Purple', 'image' => 'vehicles/JPN1416.jpg', 'mileage' => 142857, 'baseDepo' => 50, 'fuelType' => 'Petrol RON95'],
            ['plateNo' => 'NDD7803', 'brand' => 'Perodua', 'model' => 'Axia (1st Gen)', 'vehicle_category' => 'car', 'type' => 'Compact', 'owner_name' => 'MUHAMMAD IZZUDIN', 'owner_nric' => '000606012111', 'hourly_rates' => ['1'=>35, '3'=>55, '5'=>65, '7'=>70, '9'=>85, '12'=>95, '24'=>120], 'year' => 2016, 'color' => 'Gold', 'image' => 'vehicles/NDD7803.jpg', 'mileage' => 93492, 'baseDepo' => 50, 'fuelType' => 'Petrol RON95'],
            ['plateNo' => 'UTM137', 'brand' => 'Proton', 'model' => 'X70', 'vehicle_category' => 'car', 'type' => 'SUV', 'owner_name' => 'NOSHANIDAH BINTI ABDULLAH', 'owner_nric' => '870303105248', 'hourly_rates' => ['1'=>80, '3'=>150, '5'=>200, '7'=>250, '9'=>300, '12'=>350, '24'=>450], 'year' => 2022, 'color' => 'White', 'image' => 'vehicles/UTM137.jpg', 'mileage' => 102850, 'baseDepo' => 50, 'fuelType' => 'Petrol RON95'],
            ['plateNo' => 'UTM9473', 'brand' => 'Perodua', 'model' => 'Axia (2nd Gen)', 'vehicle_category' => 'car', 'type' => 'Compact', 'owner_name' => 'HASTA TRAVEL & TOURS', 'owner_nric' => '202001003057(1359376T)', 'hourly_rates' => ['1'=>40, '3'=>60, '5'=>70, '7'=>75, '9'=>85, '12'=>115, '24'=>140], 'year' => 2025, 'color' => 'Black', 'image' => 'vehicles/UTM9473.jpg', 'mileage' => 19554, 'baseDepo' => 50, 'fuelType' => 'Petrol RON95'],
            ['plateNo' => 'UTM3057', 'brand' => 'Perodua', 'model' => 'Bezza 1.3', 'vehicle_category' => 'car', 'type' => 'Sedan', 'owner_name' => 'HASTA TRAVEL & TOURS', 'owner_nric' => '202001003057(1359376T)', 'hourly_rates' => ['1'=>50, '3'=>70, '5'=>80, '7'=>85, '9'=>95, '12'=>125, '24'=>150], 'year' => 2025, 'color' => 'Red', 'image' => 'vehicles/UTM3057.jpg', 'mileage' => 20504, 'baseDepo' => 50, 'fuelType' => 'Petrol RON95'],
            ['plateNo' => 'UTM3365', 'brand' => 'Perodua', 'model' => 'Axia (2nd Gen)', 'vehicle_category' => 'car', 'type' => 'Compact', 'owner_name' => 'HASTA TRAVEL & TOURS', 'owner_nric' => '202001003057(1359376T)', 'hourly_rates' => ['1'=>40, '3'=>60, '5'=>70, '7'=>75, '9'=>85, '12'=>115, '24'=>140], 'year' => 2024, 'color' => 'Silver', 'image' => 'vehicles/UTM3365.jpg', 'mileage' => 34313, 'baseDepo' => 50, 'fuelType' => 'Petrol RON95'],
            ['plateNo' => 'UTM3655', 'brand' => 'Perodua', 'model' => 'Bezza 1.3', 'vehicle_category' => 'car', 'type' => 'Sedan', 'owner_name' => 'HASTA TRAVEL & TOURS', 'owner_nric' => '202001003057(1359376T)', 'hourly_rates' => ['1'=>50, '3'=>70, '5'=>80, '7'=>85, '9'=>95, '12'=>125, '24'=>150], 'year' => 2023, 'color' => 'Black', 'image' => 'vehicles/UTM3655.jpg', 'mileage' => 64447, 'baseDepo' => 50, 'fuelType' => 'Petrol RON95'],
            ['plateNo' => 'VC6522', 'brand' => 'Perodua', 'model' => 'Myvi (2nd Gen)', 'vehicle_category' => 'car', 'type' => 'Hatchback', 'owner_name' => 'AKMAL HADIN JALALUDIN', 'owner_nric' => '840922055385', 'hourly_rates' => ['1'=>40, '3'=>60, '5'=>70, '7'=>75, '9'=>90, '12'=>100, '24'=>130], 'year' => 2012, 'color' => 'Silver', 'image' => 'vehicles/VC6522.jpg', 'mileage' => 280108, 'baseDepo' => 50, 'fuelType' => 'Petrol RON95'],
            ['plateNo' => 'UUM1095', 'brand' => 'Perodua', 'model' => 'Alza (2nd Gen)', 'vehicle_category' => 'car', 'type' => 'SUV', 'owner_name' => 'HASTA TRAVEL & TOURS', 'owner_nric' => '202001003057(1359376T)','hourly_rates' => ['1'=>40, '3'=>60, '5'=>70, '7'=>75, '9'=>90, '12'=>100, '24'=>130], 'year' => 2023, 'color' => 'Silver', 'image' => 'vehicles/UUM1095.jpg', 'mileage' => 247879, 'baseDepo' => 50, 'fuelType' => 'Petrol RON95'],
        ];
        
        foreach ($vehicles as $v) {
            Vehicle::firstOrCreate(['plateNo' => $v['plateNo']], array_merge($v, [
                'availability' => 1, 
                'priceHour' => $v['hourly_rates']['1'],
                'road_tax_image' => 'vehiclesDocs/roadtax/' . $v['plateNo'] . '_roadtax.jpg',
                'grant_image' => 'vehiclesDocs/grant/' . $v['plateNo'] . '_grant.jpg',
                'insurance_image' => 'vehiclesDocs/insurance/' . $v['plateNo'] . '_insurance.jpg',
            ]));
        }
    }

}