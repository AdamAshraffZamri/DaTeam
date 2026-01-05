<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Staff;
use App\Models\Vehicle;
use App\Models\LoyaltyPoint;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Inspection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Staff (Now includes multiple staff members)
        $this->seedStaff();

        // 2. Seed The 4 Specific Customers
        $this->seedCustomersWithLoyalty();

        // 3. Seed Vehicles
        $this->seedVehicles();

        // 4. Generate Bookings for the 4 Customers
        $this->seedBookings();
    }

    /**
     * Seed Staff (Admin + Operations Team)
     */
    private function seedStaff(): void
    {
        // 1. Super Admin
        Staff::firstOrCreate([
            'email' => 'admin@hasta.com'
        ], [
            'name' => 'Hasta Admin',
            'role' => 'admin',
            'phoneNo' => '011-10900700',
            'password' => Hash::make('password'),
            'active' => true
        ]);

        // 2. Additional Staff Members
        $staffMembers = [
            [
                'email' => 'staff1@hasta.com',
                'name' => 'Haziq Staff',
                'role' => 'staff',
                'phoneNo' => '012-34567890',
            ],
            [
                'email' => 'staff2@hasta.com',
                'name' => 'Aisyah Operations',
                'role' => 'staff',
                'phoneNo' => '013-45678901',
            ],
            [
                'email' => 'staff3@hasta.com',
                'name' => 'Syed Manager',
                'role' => 'admin', // Another admin user
                'phoneNo' => '014-56789012',
            ]
        ];

        foreach ($staffMembers as $s) {
            Staff::firstOrCreate(['email' => $s['email']], [
                'name' => $s['name'],
                'role' => $s['role'],
                'phoneNo' => $s['phoneNo'],
                'password' => Hash::make('password'), // Password is 'password'
                'active' => true
            ]);
        }
    }

    /**
     * Seed 4 Customers with Specific Names
     */
    private function seedCustomersWithLoyalty(): void
    {
        $customers = [
            // 1. ADAM: The VIP (High usage)
            [
                'email' => 'adam@hasta.com',
                'fullName' => 'Muhammad Adam Ashraff Bin Zamri',
                'phoneNo' => '011-12345678',
                'drivingNo' => 'D-12345678',
                'ic_passport' => '990101-01-5555',
                'nationality' => 'Malaysia',
                'homeAddress' => 'Johor Bahru, Johor',
                'collegeAddress' => 'Kolej Tun Dr Ismail (KTDI), UTM',
                'accountStat' => 'active',
                'points' => 5500,
                'tier' => 'Platinum',
                'faculty' => 'Computing'
            ],
            // 2. WILDAN: The Regular (Medium usage)
            [
                'email' => 'wildan@hasta.com',
                'fullName' => 'Ahmad Wildan Bin Mazani',
                'phoneNo' => '012-23456789',
                'drivingNo' => 'D-23456789',
                'ic_passport' => '000202-02-6666',
                'nationality' => 'Malaysia',
                'homeAddress' => 'Skudai, Johor',
                'collegeAddress' => 'Kolej Tun Razak (KTR), UTM',
                'accountStat' => 'active',
                'points' => 3000,
                'tier' => 'Gold',
                'faculty' => 'Electrical'
            ],
            // 3. MIKAEL: The Casual (Low usage)
            [
                'email' => 'mikael@hasta.com',
                'fullName' => 'Mikael Haqimi Bin Nahar Junaidi',
                'phoneNo' => '013-34567890',
                'drivingNo' => 'D-34567890',
                'ic_passport' => '010303-03-7777',
                'nationality' => 'Malaysia',
                'homeAddress' => 'Kulai, Johor',
                'collegeAddress' => 'Kolej 9, UTM',
                'accountStat' => 'active',
                'points' => 800,
                'tier' => 'Silver',
                'faculty' => 'Mechanical'
            ],
            // 4. JOSHUA: The New User (Just started)
            [
                'email' => 'joshua@hasta.com',
                'fullName' => 'Joshua Ling Shang Yang',
                'phoneNo' => '014-45678901',
                'drivingNo' => 'D-45678901',
                'ic_passport' => '020404-04-8888',
                'nationality' => 'Malaysia',
                'homeAddress' => 'Kuching, Sarawak',
                'collegeAddress' => 'Kolej Perdana, UTM',
                'accountStat' => 'active', // Verified but new
                'points' => 0,
                'tier' => 'Bronze',
                'faculty' => 'Civil'
            ],
        ];

        foreach ($customers as $data) {
            $customer = Customer::firstOrCreate(
                ['email' => $data['email']],
                [
                    'fullName'      => $data['fullName'],
                    'phoneNo'       => $data['phoneNo'],
                    'password'      => Hash::make('password'),
                    'drivingNo'     => $data['drivingNo'],
                    'ic_passport'   => $data['ic_passport'],
                    'nationality'   => $data['nationality'],
                    'homeAddress'   => $data['homeAddress'],
                    'collegeAddress'=> $data['collegeAddress'],
                    'accountStat'   => $data['accountStat'],
                    'faculty'       => $data['faculty'],
                    'blacklisted'   => false,
                ]
            );

            LoyaltyPoint::updateOrCreate(
                ['user_id' => $customer->customerID],
                ['points' => $data['points'], 'tier' => $data['tier']]
            );
        }
    }

    /**
     * Seed Vehicles (UNCHANGED)
     */
    private function seedVehicles(): void
    {
        $vehicles = [
            ['plateNo' => 'JWD9496', 'brand' => 'Honda', 'model' => 'Vario 160 CC', 'vehicle_category' => 'bike', 'type' => 'Scooter', 'owner_name' => 'HASTA TRAVEL & TOURS', 'hourly_rates' => ['1'=>10, '3'=>18, '5'=>25, '7'=>31, '9'=>36, '12'=>40, '24'=>43], 'year' => 2023, 'color' => 'Yellow', 'image' => 'vehicles/JWD9496.jpg', 'mileage' => 1111809, 'baseDepo' => 50, 'fuelType' => 'Petrol RON95'],
            ['plateNo' => 'QRP5205', 'brand' => 'Honda', 'model' => 'Vario 160 CC', 'vehicle_category' => 'bike', 'type' => 'Scooter', 'owner_name' => 'HASTA TRAVEL & TOURS', 'hourly_rates' => ['1'=>10, '3'=>18, '5'=>25, '7'=>31, '9'=>36, '12'=>40, '24'=>43], 'year' => 2021, 'color' => 'Orange', 'image' => 'vehicles/QRP5205.jpg', 'mileage' => 0, 'baseDepo' => 50, 'fuelType' => 'Petrol RON95'],
            ['plateNo' => 'CEX5224', 'brand' => 'Perodua', 'model' => 'Axia (2nd Gen)', 'vehicle_category' => 'car', 'type' => 'Compact', 'owner_name' => 'SITI ARINA BINTI MD LATER', 'hourly_rates' => ['1'=>40, '3'=>60, '5'=>70, '7'=>75, '9'=>85, '12'=>115, '24'=>140], 'year' => 2024, 'color' => 'Blue', 'image' => 'vehicles/CEX5224.jpg', 'mileage' => 31368, 'baseDepo' => 50, 'fuelType' => 'Petrol RON95'],
            ['plateNo' => 'MCP6113', 'brand' => 'Perodua', 'model' => 'Axia (1st Gen)', 'vehicle_category' => 'car', 'type' => 'Compact', 'owner_name' => 'HASTA TRAVEL & TOURS', 'hourly_rates' => ['1'=>35, '3'=>55, '5'=>65, '7'=>70, '9'=>85, '12'=>95, '24'=>120], 'year' => 2018, 'color' => 'White', 'image' => 'vehicles/MCP6113.jpg', 'mileage' => 82409, 'baseDepo' => 50, 'fuelType' => 'Petrol RON95'],
            ['plateNo' => 'JQU1957', 'brand' => 'Perodua', 'model' => 'Axia (1st Gen)', 'vehicle_category' => 'car', 'type' => 'Compact', 'owner_name' => 'MUHAMMAD IZZUDIN', 'hourly_rates' => ['1'=>35, '3'=>55, '5'=>65, '7'=>70, '9'=>85, '12'=>95, '24'=>120], 'year' => 2015, 'color' => 'Green', 'image' => 'vehicles/JQU1957.jpg', 'mileage' => 316957, 'baseDepo' => 50, 'fuelType' => 'Petrol RON95'],
            ['plateNo' => 'JPN1416', 'brand' => 'Perodua', 'model' => 'Myvi (2nd Gen)', 'vehicle_category' => 'car', 'type' => 'Hatchback', 'owner_name' => 'HASTA TRAVEL & TOURS', 'hourly_rates' => ['1'=>40, '3'=>60, '5'=>70, '7'=>75, '9'=>90, '12'=>100, '24'=>130], 'year' => 2013, 'color' => 'Purple', 'image' => 'vehicles/JPN1416.jpg', 'mileage' => 142857, 'baseDepo' => 50, 'fuelType' => 'Petrol RON95'],
            ['plateNo' => 'NDD7803', 'brand' => 'Perodua', 'model' => 'Axia (1st Gen)', 'vehicle_category' => 'car', 'type' => 'Compact', 'owner_name' => 'HASTA TRAVEL & TOURS', 'hourly_rates' => ['1'=>35, '3'=>55, '5'=>65, '7'=>70, '9'=>85, '12'=>95, '24'=>120], 'year' => 2016, 'color' => 'Gold', 'image' => 'vehicles/NDD7803.jpg', 'mileage' => 93492, 'baseDepo' => 50, 'fuelType' => 'Petrol RON95'],
            ['plateNo' => 'UTM137', 'brand' => 'Proton', 'model' => 'X70', 'vehicle_category' => 'car', 'type' => 'SUV', 'owner_name' => 'NOSHANIDAH BINTI ABDULLAH', 'hourly_rates' => ['1'=>80, '3'=>150, '5'=>200, '7'=>250, '9'=>300, '12'=>350, '24'=>450], 'year' => 2022, 'color' => 'White', 'image' => 'vehicles/UTM137.jpg', 'mileage' => 102850, 'baseDepo' => 50, 'fuelType' => 'Petrol RON95'],
            ['plateNo' => 'UTM9473', 'brand' => 'Perodua', 'model' => 'Axia (2nd Gen)', 'vehicle_category' => 'car', 'type' => 'Compact', 'owner_name' => 'HASTA TRAVEL & TOURS', 'hourly_rates' => ['1'=>40, '3'=>60, '5'=>70, '7'=>75, '9'=>85, '12'=>115, '24'=>140], 'year' => 2025, 'color' => 'Black', 'image' => 'vehicles/UTM9473.jpg', 'mileage' => 19554, 'baseDepo' => 50, 'fuelType' => 'Petrol RON95'],
            ['plateNo' => 'UTM3057', 'brand' => 'Perodua', 'model' => 'Bezza 1.3', 'vehicle_category' => 'car', 'type' => 'Sedan', 'owner_name' => 'HASTA TRAVEL & TOURS', 'hourly_rates' => ['1'=>50, '3'=>70, '5'=>80, '7'=>85, '9'=>95, '12'=>125, '24'=>150], 'year' => 2025, 'color' => 'Red', 'image' => 'vehicles/UTM3057.jpg', 'mileage' => 20504, 'baseDepo' => 50, 'fuelType' => 'Petrol RON95'],
            ['plateNo' => 'UTM3365', 'brand' => 'Perodua', 'model' => 'Axia (2nd Gen)', 'vehicle_category' => 'car', 'type' => 'Compact', 'owner_name' => 'HASTA TRAVEL & TOURS', 'hourly_rates' => ['1'=>40, '3'=>60, '5'=>70, '7'=>75, '9'=>85, '12'=>115, '24'=>140], 'year' => 2024, 'color' => 'Silver', 'image' => 'vehicles/UTM3365.jpg', 'mileage' => 34313, 'baseDepo' => 50, 'fuelType' => 'Petrol RON95'],
            ['plateNo' => 'UTM3655', 'brand' => 'Perodua', 'model' => 'Bezza 1.3', 'vehicle_category' => 'car', 'type' => 'Sedan', 'owner_name' => 'HASTA TRAVEL & TOURS', 'hourly_rates' => ['1'=>50, '3'=>70, '5'=>80, '7'=>85, '9'=>95, '12'=>125, '24'=>150], 'year' => 2023, 'color' => 'Black', 'image' => 'vehicles/UTM3655.jpg', 'mileage' => 64447, 'baseDepo' => 50, 'fuelType' => 'Petrol RON95'],
            ['plateNo' => 'VC6522', 'brand' => 'Perodua', 'model' => 'Myvi (2nd Gen)', 'vehicle_category' => 'car', 'type' => 'Hatchback', 'owner_name' => 'AKMAL HADIN JALALUDIN', 'hourly_rates' => ['1'=>40, '3'=>60, '5'=>70, '7'=>75, '9'=>90, '12'=>100, '24'=>130], 'year' => 2012, 'color' => 'Silver', 'image' => 'vehicles/VC6522.jpg', 'mileage' => 280108, 'baseDepo' => 50, 'fuelType' => 'Petrol RON95'],
            ['plateNo' => 'UUM1095', 'brand' => 'Perodua', 'model' => 'Alza (2nd Gen)', 'vehicle_category' => 'car', 'type' => 'SUV', 'owner_name' => 'HASTA TRAVEL & TOURS', 'hourly_rates' => ['1'=>40, '3'=>60, '5'=>70, '7'=>75, '9'=>90, '12'=>100, '24'=>130], 'year' => 2023, 'color' => 'Silver', 'image' => 'vehicles/UUM1095.jpg', 'mileage' => 247879, 'baseDepo' => 50, 'fuelType' => 'Petrol RON95'],
        ];

        foreach ($vehicles as $v) {
            Vehicle::create(array_merge($v, [
                'availability' => 1, 
                'priceHour' => $v['hourly_rates']['1']
            ]));
        }
    }

    /**
     * Seed Bookings (Create different history for each user)
     */
    private function seedBookings(): void
    {
        // We use the Main Admin for these booking records
        $admin = Staff::where('email', 'admin@hasta.com')->first();
        if(!$admin) $admin = Staff::first(); // Fallback

        // Fetch the 4 Users
        $adam   = Customer::where('email', 'adam@hasta.com')->first();
        $wildan = Customer::where('email', 'wildan@hasta.com')->first();
        $mikael = Customer::where('email', 'mikael@hasta.com')->first();
        $joshua = Customer::where('email', 'joshua@hasta.com')->first();

        // ---------------------------------------------------------
        // 1. ADAM: 6 Bookings (5 Completed, 1 Active) - "VIP"
        // ---------------------------------------------------------
        if ($adam) {
            // Past Bookings (Loop 5 times with different cars)
            $pastCars = Vehicle::take(5)->get(); // Get first 5 cars
            foreach($pastCars as $index => $vehicle) {
                $daysAgo = ($index + 1) * 10; // 10, 20, 30... days ago
                $booking = Booking::create([
                    'customerID' => $adam->customerID,
                    'vehicleID' => $vehicle->VehicleID,
                    'staffID' => $admin->staffID,
                    'bookingDate' => now()->subDays($daysAgo)->toDateString(),
                    'originalDate' => now()->subDays($daysAgo)->toDateString(),
                    'bookingTime' => '10:00:00',
                    'returnDate' => now()->subDays($daysAgo - 2)->toDateString(),
                    'returnTime' => '10:00:00',
                    'actualReturnDate' => now()->subDays($daysAgo - 2)->toDateString(),
                    'actualReturnTime' => '10:00:00',
                    'pickupLocation' => 'KTDI',
                    'returnLocation' => 'KTDI',
                    'totalCost' => 100.00,
                    'bookingStatus' => 'Completed',
                    'bookingType' => 'Standard',
                ]);
                Payment::create([
                    'bookingID' => $booking->bookingID,
                    'amount' => 100.00, 'depoAmount' => 50.00,
                    'transactionDate' => now()->subDays($daysAgo),
                    'paymentMethod' => 'DuitNow', 'paymentStatus' => 'Paid', 'depoStatus' => 'Refunded'
                ]);
            }

            // 1 Active Booking (Currently Renting)
            $activeCar = Vehicle::skip(5)->first(); // 6th car
            if($activeCar) {
                $booking = Booking::create([
                    'customerID' => $adam->customerID,
                    'vehicleID' => $activeCar->VehicleID,
                    'staffID' => $admin->staffID,
                    'bookingDate' => now()->toDateString(),
                    'originalDate' => now()->toDateString(),
                    'bookingTime' => '09:00:00',
                    'returnDate' => now()->addDays(3)->toDateString(), // Future return
                    'returnTime' => '09:00:00',
                    'pickupLocation' => 'KTDI',
                    'returnLocation' => 'KTDI',
                    'totalCost' => 150.00,
                    'bookingStatus' => 'Active',
                    'bookingType' => 'Standard',
                ]);
                Payment::create([
                    'bookingID' => $booking->bookingID,
                    'amount' => 150.00, 'depoAmount' => 50.00,
                    'transactionDate' => now(),
                    'paymentMethod' => 'DuitNow', 'paymentStatus' => 'Paid', 'depoStatus' => 'Held'
                ]);
                // Set car unavailable
                $activeCar->update(['availability' => 0]);
            }
        }

        // ---------------------------------------------------------
        // 2. WILDAN: 3 Bookings (All Completed) - "Regular"
        // ---------------------------------------------------------
        if ($wildan) {
            $cars = Vehicle::skip(6)->take(3)->get(); // Get next 3 cars
            foreach($cars as $index => $vehicle) {
                $daysAgo = ($index + 1) * 15; 
                $booking = Booking::create([
                    'customerID' => $wildan->customerID,
                    'vehicleID' => $vehicle->VehicleID,
                    'staffID' => $admin->staffID,
                    'bookingDate' => now()->subDays($daysAgo)->toDateString(),
                    'originalDate' => now()->subDays($daysAgo)->toDateString(),
                    'bookingTime' => '14:00:00',
                    'returnDate' => now()->subDays($daysAgo - 1)->toDateString(),
                    'returnTime' => '14:00:00',
                    'actualReturnDate' => now()->subDays($daysAgo - 1)->toDateString(),
                    'actualReturnTime' => '14:00:00',
                    'pickupLocation' => 'KTR',
                    'returnLocation' => 'KTR',
                    'totalCost' => 80.00,
                    'bookingStatus' => 'Completed',
                    'bookingType' => 'Standard',
                ]);
                Payment::create([
                    'bookingID' => $booking->bookingID,
                    'amount' => 80.00, 'depoAmount' => 50.00,
                    'transactionDate' => now()->subDays($daysAgo),
                    'paymentMethod' => 'Online Banking', 'paymentStatus' => 'Paid', 'depoStatus' => 'Refunded'
                ]);
            }
        }

        // ---------------------------------------------------------
        // 3. MIKAEL: 1 Booking (Completed) - "Casual"
        // ---------------------------------------------------------
        if ($mikael) {
            $car = Vehicle::skip(9)->first(); // 10th car
            if($car) {
                $booking = Booking::create([
                    'customerID' => $mikael->customerID,
                    'vehicleID' => $car->VehicleID,
                    'staffID' => $admin->staffID,
                    'bookingDate' => now()->subDays(30)->toDateString(),
                    'originalDate' => now()->subDays(30)->toDateString(),
                    'bookingTime' => '12:00:00',
                    'returnDate' => now()->subDays(29)->toDateString(),
                    'returnTime' => '12:00:00',
                    'actualReturnDate' => now()->subDays(29)->toDateString(),
                    'actualReturnTime' => '12:00:00',
                    'pickupLocation' => 'Kolej 9',
                    'returnLocation' => 'Kolej 9',
                    'totalCost' => 60.00,
                    'bookingStatus' => 'Completed',
                    'bookingType' => 'Standard',
                ]);
                Payment::create([
                    'bookingID' => $booking->bookingID,
                    'amount' => 60.00, 'depoAmount' => 50.00,
                    'transactionDate' => now()->subDays(30),
                    'paymentMethod' => 'Cash', 'paymentStatus' => 'Paid', 'depoStatus' => 'Refunded'
                ]);
            }
        }

        // ---------------------------------------------------------
        // 4. JOSHUA: 1 Booking (Pending Approval) - "New"
        // ---------------------------------------------------------
        if ($joshua) {
            $car = Vehicle::skip(10)->first(); // 11th car
            if($car) {
                $booking = Booking::create([
                    'customerID' => $joshua->customerID,
                    'vehicleID' => $car->VehicleID,
                    // No Staff ID yet because it's Pending
                    'bookingDate' => now()->toDateString(),
                    'originalDate' => now()->addDays(2)->toDateString(), // Future booking
                    'bookingTime' => '08:00:00',
                    'returnDate' => now()->addDays(4)->toDateString(),
                    'returnTime' => '08:00:00',
                    'pickupLocation' => 'Kolej Perdana',
                    'returnLocation' => 'Kolej Perdana',
                    'totalCost' => 200.00,
                    'bookingStatus' => 'Pending', // Waiting for approval
                    'bookingType' => 'Standard',
                ]);
                Payment::create([
                    'bookingID' => $booking->bookingID,
                    'amount' => 200.00, 'depoAmount' => 50.00,
                    'transactionDate' => now(),
                    'paymentMethod' => 'DuitNow', 'paymentStatus' => 'Paid', 'depoStatus' => 'Pending'
                ]);
            }
        }
    }
}