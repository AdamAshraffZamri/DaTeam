<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    // Seed the application's database.
    public function run(): void
    {
    // Create a Test User if not exists
    \App\Models\User::firstOrCreate([
        'email' => 'test@example.com'
    ], [
        'name' => 'Test Student',
        'password' => bcrypt('password'), // password is 'password'
        'phone' => '0123456789',
        // Add other required fields from your migration here if needed
    ]);

    // Create 3 Vehicles
    \App\Models\Vehicle::create([
        'plate_no' => 'UTM 8821',
        'model' => 'Perodua Axia 2ND GEN',
        'type' => 'Compact',
        'price_hour' => 5.00, // RM 120 per day
        'availability' => true,
        'mileage' => 10000,
        'fuel_pickup' => 'Full',
        'base_deposit' => 50.00
    ]);

    \App\Models\Vehicle::create([
        'plate_no' => 'JHR 3342',
        'model' => 'Perodua Myvi 3RD GEN',
        'type' => 'Hatchback',
        'price_hour' => 5.84, // ~RM 140 per day
        'availability' => true,
        'mileage' => 15000,
        'fuel_pickup' => 'Full',
        'base_deposit' => 50.00
    ]);
}
}
