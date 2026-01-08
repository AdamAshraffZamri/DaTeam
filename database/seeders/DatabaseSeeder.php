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

        // 3. Seed Customers (4 Specific + 96 Random = 100 Total)
        $this->seedCustomers();

        // 4. Seed Bookings (Specific History + Random = 1000 Total)
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
     * Seed Customers (4 Specific + 96 Random)
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

        // 2. Create 96 Random Customers (Total 100)
        for ($i = 0; $i < 96; $i++) {
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
     * Seed Bookings (Target: 1000 Total)
     */
    private function seedBookings(): void
    {
        $admin = Staff::where('email', 'admin@hasta.com')->first();
        $staffIds = Staff::pluck('staffID')->toArray();
        $allCustomerIDs = Customer::pluck('customerID')->toArray();
        $faker = Faker::create();

        // specific users
        $adam   = Customer::where('email', 'adam@hasta.com')->first();
        $wildan = Customer::where('email', 'wildan@hasta.com')->first();
        $mikael = Customer::where('email', 'mikael@hasta.com')->first();
        $joshua = Customer::where('email', 'joshua@hasta.com')->first();

        // --- 1. PRESERVE SPECIFIC HISTORY (Recent) ---
        
        // ADAM: Multiple Future Bookings (Testing 3-hour cooldown rule)
        $activeCar = Vehicle::where('plateNo', 'JPN1416')->first(); // Purple Myvi
        if ($adam && $activeCar) {
            // First Active booking: Now to 3 days later
            $this->createBooking($adam->customerID, $activeCar->VehicleID, $admin->staffID, 
                now()->toDateString(), 
                now()->addDays(3)->toDateString(), 
                'Active', 150.00, 'Future');
            $activeCar->update(['availability' => 0]);

            // Second booking after cooldown: 3 hours + 3 days + 3 hours = 3 days 6 hours later
            $this->createBooking($adam->customerID, $activeCar->VehicleID, $admin->staffID, 
                now()->addDays(3)->addHours(3)->toDateString(), 
                now()->addDays(6)->addHours(3)->toDateString(), 
                'Confirmed', 150.00, 'Future');

            // Third booking respecting cooldown: Another 3 days + 3 hours after second ends
            $this->createBooking($adam->customerID, $activeCar->VehicleID, $admin->staffID, 
                now()->addDays(6)->addHours(6)->toDateString(), 
                now()->addDays(9)->addHours(6)->toDateString(), 
                'Confirmed', 150.00, 'Future');
        }

        // JOSHUA: Multiple Future Bookings with Different Vehicle (No conflict with Adam)
        $joshuaCar = Vehicle::where('plateNo', 'UTM3365')->first(); 
        if ($joshua && $joshuaCar) {
            // First booking: Starts 2 days from now
            $this->createBooking($joshua->customerID, $joshuaCar->VehicleID, $admin->staffID, 
                now()->addDays(2)->toDateString(), 
                now()->addDays(4)->toDateString(), 
                'Confirmed', 200.00, 'Future');

            // Second booking after 3-hour cooldown
            $this->createBooking($joshua->customerID, $joshuaCar->VehicleID, $admin->staffID, 
                now()->addDays(4)->addHours(3)->toDateString(), 
                now()->addDays(6)->addHours(3)->toDateString(), 
                'Confirmed', 200.00, 'Future');

            // Third booking - different vehicle to avoid conflict
            $thirdVehicle = Vehicle::where('plateNo', 'CTR2020')->first(); 
            if ($thirdVehicle) {
                $this->createBooking($joshua->customerID, $thirdVehicle->VehicleID, $admin->staffID, 
                    now()->addDays(5)->toDateString(), 
                    now()->addDays(7)->toDateString(), 
                    'Confirmed', 200.00, 'Future');
            }
        }

        // WILDAN: Future bookings to test same-customer, different-vehicle scenario
        $wildanCar1 = Vehicle::where('plateNo', 'ABC1234')->first();
        if ($wildan && $wildanCar1) {
            $this->createBooking($wildan->customerID, $wildanCar1->VehicleID, $admin->staffID,
                now()->addDays(1)->toDateString(),
                now()->addDays(2)->toDateString(),
                'Confirmed', 120.00, 'Future');

            // Another car, overlapping time (same customer, different vehicle = allowed)
            $wildanCar2 = Vehicle::where('plateNo', 'JPN1416')->first();
            if ($wildanCar2 && $wildanCar2->VehicleID != $wildanCar1->VehicleID) {
                $this->createBooking($wildan->customerID, $wildanCar2->VehicleID, $admin->staffID,
                    now()->addDays(1)->toDateString(),
                    now()->addDays(3)->toDateString(),
                    'Confirmed', 150.00, 'Future');
            }
        }

        // MIKAEL: Test cooldown validation - bookings that respect 3-hour gaps
        $mikaelCar = Vehicle::where('plateNo', 'XYZ5678')->first();
        if ($mikael && $mikaelCar) {
            // Booking 1
            $this->createBooking($mikael->customerID, $mikaelCar->VehicleID, $admin->staffID,
                now()->addDays(7)->toDateString(),
                now()->addDays(8)->toDateString(),
                'Confirmed', 100.00, 'Future');

            // Booking 2 - exactly 3 hours after Booking 1 ends
            $this->createBooking($mikael->customerID, $mikaelCar->VehicleID, $admin->staffID,
                now()->addDays(8)->addHours(3)->toDateString(),
                now()->addDays(9)->addHours(3)->toDateString(),
                'Confirmed', 100.00, 'Future');
        }

        // --- 2. MASS GENERATION (Backwards Chaining) ---
        // This loops every vehicle and fills history backwards to prevent overlaps.

        $vehicles = Vehicle::all();

        foreach ($vehicles as $vehicle) {
            // Start the timeline from yesterday (so we don't clash with today's Active bookings)
            $currentDate = now()->subDay();

            // Check if this vehicle is currently used by Adam or Joshua (Future bookings)
            // If so, start the history BEFORE their booking starts
            $futureBooking = Booking::where('vehicleID', $vehicle->VehicleID)->orderBy('bookingDate', 'asc')->first();
            if ($futureBooking) {
                $currentDate = Carbon::parse($futureBooking->bookingDate)->subHours(4);
            }

            // We want roughly 60-80 bookings per vehicle to fill up history
            $bookingsPerCar = rand(50, 80); 

            for ($i = 0; $i < $bookingsPerCar; $i++) {
                // 1. Determine End Date (matches current timeline cursor)
                $endDate = $currentDate->copy();

                // 2. Determine Duration (1 to 3 days)
                $duration = rand(1, 3);
                $startDate = $endDate->copy()->subDays($duration);

                // 3. Determine Status (NO PENDING)
                // 90% Completed, 5% Cancelled, 5% Confirmed (old uncollected)
                $rand = rand(1, 100);
                if ($rand <= 90) $status = 'Completed';
                elseif ($rand <= 95) $status = 'Cancelled';
                else $status = 'Confirmed';

                // 4. Random Customer & Cost
                $custID = $faker->randomElement($allCustomerIDs);
                $staffID = $faker->randomElement($staffIds);
                $cost = $duration * $vehicle->priceHour * 24; // Rough calc
                if ($status == 'Cancelled') $cost = 0;

                // 5. Create Booking
                $this->createBooking(
                    $custID, 
                    $vehicle->VehicleID, 
                    $staffID, 
                    $startDate->toDateString(), 
                    $endDate->toDateString(), 
                    $status, 
                    $cost,
                    'Standard' // Ensure historical bookings are Standard type
                );

                // 6. MOVE CURSOR BACKWARDS
                // Gap: 3 hours + duration of the booking we just made
                // We move back to the start of this booking, then subtract 3 hours for the next gap
                $currentDate = $startDate->copy()->subHours(3);
                
                // Safety break if we go back too far (e.g. > 2 years)
                if ($currentDate->year < 2023) break;
            }
        }
    }

    private function createBooking($custID, $vehID, $staffID, $startDate, $endDate, $status, $cost, $bookingType = 'Standard')
    {
        // Parse dates to handle potentially passed Carbon objects or Strings
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        $booking = Booking::create([
            'customerID' => $custID,
            'vehicleID' => $vehID,
            'staffID' => $staffID,
            'bookingDate' => $start->format('Y-m-d'),
            'originalDate' => $start->format('Y-m-d'),
            'bookingTime' => '10:00:00',
            'returnDate' => $end->format('Y-m-d'),
            'returnTime' => '10:00:00',
            'actualReturnDate' => ($status === 'Completed') ? $end->format('Y-m-d') : null,
            'actualReturnTime' => ($status === 'Completed') ? '10:00:00' : null,
            'pickupLocation' => 'KTDI',
            'returnLocation' => 'KTDI',
            'totalCost' => $cost,
            'bookingStatus' => $status,
            'bookingType' => $bookingType,
            // IMPORTANT: Set created_at to bookingDate so reports look correct!
            'created_at' => $start, 
            'updated_at' => $start,
        ]);

        // Payment Logic - NO PENDING
        $paymentStatus = 'Paid';
        if ($status === 'Cancelled') $paymentStatus = 'Refunded'; // Or Void
        
        Payment::create([
            'bookingID' => $booking->bookingID,
            'amount' => $cost > 0 ? $cost : 50.00, 
            'depoAmount' => 50.00,
            'transactionDate' => $start,
            'paymentMethod' => 'DuitNow', 
            'paymentStatus' => $paymentStatus, 
            'depoStatus' => ($status === 'Completed') ? 'Refunded' : 'Held',
            'created_at' => $start,
            'updated_at' => $start,
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
            [12, 100, 'Free Half Day'],
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