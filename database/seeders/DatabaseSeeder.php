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

        // 3. Seed Customers (4 Specific + 46 Random = 50 Total)
        $this->seedCustomers();

        // 4. Seed Bookings (Specific History + Random = 100 Total)
        $this->seedBookings();

        // 5. Seed Rewards
        $this->seedRewards();
    }

    /**
     * Seed Staff (2 Admins, 2 Normal Staff)
     */
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
            // Admin 2 (Manager)
            [
                'email' => 'staff3@hasta.com',
                'name' => 'Syed Manager',
                'role' => 'admin',
                'phoneNo' => '014-56789012',
            ],
            // Staff 1
            [
                'email' => 'staff1@hasta.com',
                'name' => 'Haziq Staff',
                'role' => 'staff',
                'phoneNo' => '012-34567890',
            ],
            // Staff 2
            [
                'email' => 'staff2@hasta.com',
                'name' => 'Aisyah Operations',
                'role' => 'staff',
                'phoneNo' => '013-45678901',
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

    /**
     * Seed Customers (4 Specific + 46 Random)
     */
    private function seedCustomers(): void
    {
        $faker = Faker::create('ms_MY'); // Use Malaysian locale

        // 1. The 4 Specific Customers
        $specificCustomers = [
            // ADAM
            [
                'email' => 'adam@hasta.com',
                'fullName' => 'Muhammad Adam Ashraff Bin Zamri',
                'phoneNo' => '011-12345678',
                'driving_license_expiry' => '2030-12-31',
                'ic_passport' => '990101-01-5555',
                'stustaffID' => 'A12345',
                'nationality' => 'Malaysia',
                'homeAddress' => 'Johor Bahru, Johor',
                'collegeAddress' => 'Kolej Tun Dr. Ismail (KTDI)',
                'accountStat' => 'active',
                'faculty' => 'Faculty of Computing (FC)',
                'dob' => '1999-01-01',
                'bankName' => 'Maybank',
                'bankAccountNo' => '162234567890',
                'emergency_contact_name' => 'Zamri Bin Ahmad',
                'emergency_contact_no' => '011-87654321',
                'points' => 5500,
                'tier' => 'Platinum',
            ],
            // WILDAN
            [
                'email' => 'wildan@hasta.com',
                'fullName' => 'Ahmad Wildan Bin Mazani',
                'phoneNo' => '012-23456789',
                'driving_license_expiry' => '2029-05-20',
                'ic_passport' => '000202-02-6666',
                'stustaffID' => 'W23456',
                'nationality' => 'Malaysia',
                'homeAddress' => 'Skudai, Johor',
                'collegeAddress' => 'Kolej Tun Dr. Ismail (KTDI)',
                'accountStat' => 'active',
                'faculty' => 'Faculty of Computing (FC)',
                'dob' => '2000-02-02',
                'bankName' => 'CIMB',
                'bankAccountNo' => '172345678901',
                'emergency_contact_name' => 'Mazani Binti Hussein',
                'emergency_contact_no' => '012-76543210',
                'points' => 3000,
                'tier' => 'Gold',
            ],
            // MIKAEL
            [
                'email' => 'mikael@hasta.com',
                'fullName' => 'Mikael Haqimi Bin Nahar Junaidi',
                'phoneNo' => '013-34567890',
                'driving_license_expiry' => '2028-11-15',
                'ic_passport' => '010303-03-7777',
                'stustaffID' => 'M34567',
                'nationality' => 'Malaysia',
                'homeAddress' => 'Kulai, Johor',
                'collegeAddress' => 'Kolej Tun Dr. Ismail (KTDI)',
                'accountStat' => 'active',
                'faculty' => 'Faculty of Computing (FC)',
                'dob' => '2001-03-03',
                'bankName' => 'Public Bank',
                'bankAccountNo' => '182456789012',
                'emergency_contact_name' => 'Nahar Bin Junaidi',
                'emergency_contact_no' => '013-65432109',
                'points' => 800,
                'tier' => 'Silver',
            ],
            // JOSHUA
            [
                'email' => 'joshua@hasta.com',
                'fullName' => 'Joshua Ling Shang Yang',
                'phoneNo' => '014-45678901',
                'driving_license_expiry' => '2031-01-01',
                'ic_passport' => '020404-04-8888',
                'stustaffID' => 'J45678',
                'nationality' => 'Malaysia',
                'homeAddress' => 'Kuching, Sarawak',
                'collegeAddress' => 'Kolej Tun Dr. Ismail (KTDI)',
                'accountStat' => 'active',
                'faculty' => 'Civil',
                'dob' => '2002-04-04',
                'bankName' => 'RHB',
                'bankAccountNo' => '192567890123',
                'emergency_contact_name' => 'Ling Keng Seng',
                'emergency_contact_no' => '014-54321098',
                'points' => 0,
                'tier' => 'Bronze',
            ],
        ];

        // Create Specific Customers
        foreach ($specificCustomers as $data) {
            $customer = Customer::firstOrCreate(['email' => $data['email']], [
                'fullName' => $data['fullName'],
                'phoneNo' => $data['phoneNo'],
                'password' => Hash::make('password'),
                'driving_license_expiry' => $data['driving_license_expiry'],
                'ic_passport' => $data['ic_passport'],
                'stustaffID' => $data['stustaffID'],
                'nationality' => $data['nationality'],
                'homeAddress' => $data['homeAddress'],
                'collegeAddress' => $data['collegeAddress'],
                'accountStat' => $data['accountStat'],
                'faculty' => $data['faculty'],
                'dob' => $data['dob'],
                'bankName' => $data['bankName'],
                'bankAccountNo' => $data['bankAccountNo'],
                'emergency_contact_name' => $data['emergency_contact_name'],
                'emergency_contact_no' => $data['emergency_contact_no'],
                'blacklisted' => false,
            ]);

            LoyaltyPoint::updateOrCreate(
                ['user_id' => $customer->customerID],
                ['points' => $data['points'], 'tier' => $data['tier']]
            );
        }

        // 2. Create 46 Random Customers
        for ($i = 0; $i < 46; $i++) {
            $gender = $faker->randomElement(['male', 'female']);
            $fullName = $faker->name($gender);
            
            $customer = Customer::create([
                'email' => $faker->unique()->safeEmail,
                'fullName' => $fullName,
                'phoneNo' => $faker->phoneNumber,
                'password' => Hash::make('password'),
                'driving_license_expiry' => $faker->dateTimeBetween('now', '+5 years')->format('Y-m-d'),
                'ic_passport' => $faker->numerify('######-##-####'),
                'stustaffID' => strtoupper($faker->bothify('?#####')),
                'nationality' => 'Malaysia',
                'homeAddress' => $faker->address,
                'collegeAddress' => 'Kolej ' . $faker->randomElement(['Tun Dr. Ismail', 'Rahman Putra', 'Tuanku Canselor', 'Perdana', '9', '10']),
                'accountStat' => 'active',
                'faculty' => $faker->randomElement(['Computing', 'Civil', 'Mechanical', 'Electrical', 'Management', 'Science']),
                'dob' => $faker->dateTimeBetween('-25 years', '-18 years')->format('Y-m-d'),
                'bankName' => $faker->randomElement(['Maybank', 'CIMB', 'RHB', 'Public Bank', 'Bank Islam']),
                'bankAccountNo' => $faker->numerify('############'),
                'emergency_contact_name' => $faker->name,
                'emergency_contact_no' => $faker->phoneNumber,
                'blacklisted' => false,
            ]);

            // Assign random loyalty points
            $points = $faker->numberBetween(0, 1000);
            $tier = 'Bronze';
            if ($points > 5000) $tier = 'Platinum';
            elseif ($points > 2500) $tier = 'Gold';
            elseif ($points > 500) $tier = 'Silver';

            LoyaltyPoint::create([
                'user_id' => $customer->customerID,
                'points' => $points,
                'tier' => $tier
            ]);
        }
    }

    /**
     * Seed Bookings (Target: 100 Total)
     */
    private function seedBookings(): void
    {
        // Setup details
        $admin = Staff::where('email', 'admin@hasta.com')->first();
        if(!$admin) $admin = Staff::first();
        $staffIds = Staff::pluck('staffID')->toArray();
        $vehicleIds = Vehicle::pluck('VehicleID')->toArray();
        $faker = Faker::create();

        // Specific Users
        $adam   = Customer::where('email', 'adam@hasta.com')->first();
        $wildan = Customer::where('email', 'wildan@hasta.com')->first();
        $mikael = Customer::where('email', 'mikael@hasta.com')->first();
        $joshua = Customer::where('email', 'joshua@hasta.com')->first();

        // --- 1. PRESERVE SPECIFIC HISTORY (11 Bookings) ---

        // ADAM: 6 Bookings (5 Completed, 1 Active)
        if ($adam) {
            $pastCars = Vehicle::take(5)->get();
            foreach($pastCars as $index => $vehicle) {
                $daysAgo = ($index + 1) * 10; 
                $this->createBooking($adam->customerID, $vehicle->VehicleID, $admin->staffID, 
                    now()->subDays($daysAgo)->toDateString(), 
                    now()->subDays($daysAgo - 2)->toDateString(), 
                    'Completed', 100.00);
            }
            $activeCar = Vehicle::skip(5)->first(); 
            if($activeCar) {
                $this->createBooking($adam->customerID, $activeCar->VehicleID, $admin->staffID, 
                    now()->toDateString(), 
                    now()->addDays(3)->toDateString(), 
                    'Active', 150.00);
                $activeCar->update(['availability' => 0]);
            }
        }

        // WILDAN: 3 Bookings (Completed)
        if ($wildan) {
            $cars = Vehicle::skip(6)->take(3)->get();
            foreach($cars as $index => $vehicle) {
                $daysAgo = ($index + 1) * 15; 
                $this->createBooking($wildan->customerID, $vehicle->VehicleID, $admin->staffID, 
                    now()->subDays($daysAgo)->toDateString(), 
                    now()->subDays($daysAgo - 1)->toDateString(), 
                    'Completed', 80.00);
            }
        }

        // MIKAEL: 1 Booking (Completed)
        if ($mikael) {
            $car = Vehicle::skip(9)->first();
            if($car) {
                $this->createBooking($mikael->customerID, $car->VehicleID, $admin->staffID, 
                    now()->subDays(30)->toDateString(), 
                    now()->subDays(29)->toDateString(), 
                    'Completed', 60.00);
            }
        }

        // JOSHUA: 1 Booking (Pending)
        if ($joshua) {
            $car = Vehicle::skip(10)->first();
            if($car) {
                $this->createBooking($joshua->customerID, $car->VehicleID, null, 
                    now()->addDays(2)->toDateString(), 
                    now()->addDays(4)->toDateString(), 
                    'Pending', 200.00);
            }
        }

        // --- 2. GENERATE REMAINING BOOKINGS (89 Bookings) ---
        // We will distribute these among all customers
        
        $allCustomerIDs = Customer::pluck('customerID')->toArray();
        $totalToGenerate = 89;

        for ($i = 0; $i < $totalToGenerate; $i++) {
            $custID = $faker->randomElement($allCustomerIDs);
            $vehID = $faker->randomElement($vehicleIds);
            
            // Randomize Status: 70% Completed, 10% Active, 10% Pending, 5% Cancelled, 5% Unpaid
            $rand = $faker->numberBetween(1, 100);
            
            if ($rand <= 70) {
                // Completed (Past)
                $staffID = $faker->randomElement($staffIds);
                $daysAgo = $faker->numberBetween(5, 365);
                $duration = $faker->numberBetween(1, 7);
                $start = now()->subDays($daysAgo);
                $end = (clone $start)->addDays($duration);
                $this->createBooking($custID, $vehID, $staffID, $start->toDateString(), $end->toDateString(), 'Completed', $faker->randomFloat(2, 50, 300));
            
            } elseif ($rand <= 80) {
                // Active (Current) - Ensure car isn't already taken by Adam's active booking
                // For simplicity in seeder, we might double book unless we check. 
                // But for volume, we'll assume it's fine or just mark status.
                $staffID = $faker->randomElement($staffIds);
                $start = now()->subDays($faker->numberBetween(0, 2));
                $end = now()->addDays($faker->numberBetween(1, 5));
                $this->createBooking($custID, $vehID, $staffID, $start->toDateString(), $end->toDateString(), 'Active', $faker->randomFloat(2, 50, 300));
            
            } elseif ($rand <= 90) {
                // Pending (Future)
                $start = now()->addDays($faker->numberBetween(3, 30));
                $end = (clone $start)->addDays($faker->numberBetween(1, 3));
                $this->createBooking($custID, $vehID, null, $start->toDateString(), $end->toDateString(), 'Pending', $faker->randomFloat(2, 50, 300));
            
            } else {
                // Cancelled
                $staffID = $faker->randomElement($staffIds);
                $daysAgo = $faker->numberBetween(10, 100);
                $start = now()->subDays($daysAgo);
                $end = (clone $start)->addDays(1);
                $this->createBooking($custID, $vehID, $staffID, $start->toDateString(), $end->toDateString(), 'Cancelled', 0.00);
            }
        }
    }

    /**
     * Helper to create a booking and payment
     */
    private function createBooking($custID, $vehID, $staffID, $startDate, $endDate, $status, $cost)
    {
        $booking = Booking::create([
            'customerID' => $custID,
            'vehicleID' => $vehID,
            'staffID' => $staffID,
            'bookingDate' => Carbon::parse($startDate)->format('Y-m-d'),
            'originalDate' => Carbon::parse($startDate)->format('Y-m-d'),
            'bookingTime' => '10:00:00',
            'returnDate' => Carbon::parse($endDate)->format('Y-m-d'),
            'returnTime' => '10:00:00',
            'actualReturnDate' => ($status === 'Completed') ? Carbon::parse($endDate)->format('Y-m-d') : null,
            'actualReturnTime' => ($status === 'Completed') ? '10:00:00' : null,
            'pickupLocation' => 'KTDI',
            'returnLocation' => 'KTDI',
            'totalCost' => $cost,
            'bookingStatus' => $status,
            'bookingType' => 'Standard',
        ]);

        // Payment Logic
        $paymentStatus = 'Paid';
        if ($status === 'Pending' || $status === 'Cancelled') $paymentStatus = 'Pending';
        
        Payment::create([
            'bookingID' => $booking->bookingID,
            'amount' => $cost > 0 ? $cost : 50.00, 
            'depoAmount' => 50.00,
            'transactionDate' => Carbon::parse($startDate),
            'paymentMethod' => 'DuitNow', 
            'paymentStatus' => $paymentStatus, 
            'depoStatus' => ($status === 'Completed') ? 'Refunded' : 'Pending'
        ]);

        return $booking;
    }

    /**
     * Seed Rewards (UNCHANGED from original reference)
     */
    private function seedRewards(): void
    {
        $merchantRewards = [
            [
                'name' => 'SEDAP KITCHEN',
                'offer_description' => 'RM 5 OFF Discount',
                'points_required' => 200,
                'code_prefix' => 'SDKTN',
                'validity_months' => 3,
                'category' => 'Food',
                'icon_class' => 'fa-utensils',
                'color_class' => 'bg-yellow-600/20 border-yellow-500/30'
            ],
            [
                'name' => 'PAK ATONG',
                'offer_description' => 'RM 5 OFF Discount',
                'points_required' => 200,
                'code_prefix' => 'PKATNG',
                'validity_months' => 3,
                'category' => 'Food',
                'icon_class' => 'fa-utensils',
                'color_class' => 'bg-gray-600/20 border-gray-500/30'
            ],
            [
                'name' => 'RESTORAN RAFI',
                'offer_description' => 'RM 5 OFF Discount',
                'points_required' => 200,
                'code_prefix' => 'RSTRN',
                'validity_months' => 3,
                'category' => 'Food',
                'icon_class' => 'fa-utensils',
                'color_class' => 'bg-green-600/20 border-green-500/30'
            ],
            [
                'name' => 'PAK LAH',
                'offer_description' => 'RM 5 OFF Discount',
                'points_required' => 200,
                'code_prefix' => 'PKLH',
                'validity_months' => 3,
                'category' => 'Food',
                'icon_class' => 'fa-utensils',
                'color_class' => 'bg-red-600/20 border-red-500/30'
            ],
            [
                'name' => 'GRAB FOOD',
                'offer_description' => 'RM 10 Voucher',
                'points_required' => 750,
                'code_prefix' => 'GRB',
                'validity_months' => 4,
                'category' => 'Voucher',
                'icon_class' => 'fa-motorcycle',
                'color_class' => 'bg-green-500/20 border-green-400/30'
            ]
        ];

        foreach ($merchantRewards as $reward) {
            Reward::firstOrCreate(['code_prefix' => $reward['code_prefix']], array_merge($reward, ['is_active' => true]));
        }

        $milestones = [
            [3, 20, 'Bronze Tier Reward'],
            [6, 50, 'Silver Tier Reward'],
            [9, 70, 'Gold Tier Reward'],
            [12, 100, 'Platinum Tier Reward (Free Half Day)'],
        ];

        foreach ($milestones as $m) {
            Reward::firstOrCreate(
                ['code_prefix' => 'AUTO' . $m[0]], 
                [
                    'name' => $m[2],
                    'offer_description' => $m[1] . '% OFF Rental',
                    'points_required' => 0, 
                    'validity_months' => 2,
                    'category' => 'Milestone',
                    'icon_class' => 'fa-road',
                    'color_class' => 'bg-purple-600/20 border-purple-500/30',
                    'milestone_step' => $m[0],
                    'discount_percent' => $m[1],
                    'is_active' => true
                ]
            );
        }
    }
}