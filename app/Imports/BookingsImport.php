<?php

namespace App\Imports;

use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Inspection;

class BookingsImport
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

        // Skip the header row (Row 1)
        fgetcsv($file);

        $lastBooking = Booking::where('aggreementLink', 'LIKE', 'agreements/agreement_Test%.pdf')
        ->orderByRaw('CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(aggreementLink, "Test", -1), ".", 1) AS UNSIGNED) DESC')
        ->first();

        $testCounter = 1;
        if ($lastBooking) {
            // Regex to extract the number from the string
            preg_match('/Test(\d+)/', $lastBooking->aggreementLink, $matches);
            $testCounter = isset($matches[1]) ? (int)$matches[1] + 1 : 1;
        }

        try {
            while (($row = fgetcsv($file)) !== false) {
                // Map the 13 CSV columns
                $email = trim($row[0]);
                $plateNo = trim($row[1]);
                $requestDate = trim($row[2]);
                $pickupDate = trim($row[3]);
                $pickupTime = trim($row[4]);
                $pickupLocation = trim($row[5]);
                $returnDate = trim($row[6]);
                $returnTime = trim($row[7]);
                $returnLocation = trim($row[8]);
                $totalCost = trim($row[9]);

                // Force mileage to be a number (removes non-numeric characters)
                $mileageBefore = (float) filter_var(trim($row[10]), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                $mileageAfter  = (float) filter_var(trim($row[11]), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

                // If fuel is "6/8", you might need to handle it as a string or calculate the decimal
                $fuelBefore = trim($row[12]); 
                $fuelAfter  = trim($row[13]);

                // Find existing customer and vehicle
                $customer = Customer::where('email', $email)->first();
                $vehicle = Vehicle::where('plateNo', $plateNo)->first();

                $customer = Customer::where('email', $email)->first();

                if (!$customer) {
                    // Adjust these fields to match your 'customers' table schema
                    $customer = Customer::create([
                        'email' => $email,
                        'fullName' => 'New Customer',
                        'password' => bcrypt('8888'),  // Using bcrypt for Laravel security
                    ]);
                }

                $vehicle = Vehicle::where('plateNo', $plateNo)->first();

                // If vehicle still doesn't exist, we skip
                if (!$vehicle) {
                    $skipped++;
                    continue; 
                }

                // 1. Create the Booking
                $booking = Booking::create([
                    'customerID' => $customer->customerID,
                    'vehicleID' => $vehicle->VehicleID,
                    'bookingDate' => $requestDate,
                    'originalDate' => $pickupDate,
                    'bookingTime' => $pickupTime,
                    'returnDate' => $returnDate,
                    'returnTime' => $returnTime,
                    'actualReturnDate' => $returnDate,
                    'actualReturnTime' => $returnTime,
                    'pickupLocation' => $pickupLocation, 
                    'returnLocation' => $returnLocation, 
                    'totalCost' => $totalCost,
                    'bookingStatus' => 'Completed',
                    'bookingType' => 'Standard',
                ]);

                // 1.5 Auto-Generate Agreement and Invoice Links
                $testName = 'Test' . $testCounter; // Creates 'Test1', 'Test2', etc.
                
                $booking->update([
                    'aggreementDate' => $pickupDate,
                    'aggreementLink' => 'agreements/agreement_' . $testName . '.pdf',
                    'invoiceLink'    => 'invoices/invoice_' . $testName . '.pdf',
                ]);

                // 2. Create the Payment Record
                Payment::create([
                    'bookingID' => $booking->bookingID,
                    'amount' => $totalCost,
                    'depoAmount' => 50.00,
                    'transactionDate' => $pickupDate . ' ' . $pickupTime,
                    'paymentMethod' => 'Online Banking',
                    'paymentStatus' => 'Verified',
                    'depoStatus' => 'Pending'
                ]);

                // 3. Create Pickup Inspection (5 Photos)
                $pickupPhotos = json_encode([
                    'inspections/pickup/' . $testName . '_front.jpg',
                    'inspections/pickup/' . $testName . '_back.jpg',
                    'inspections/pickup/' . $testName . '_left.jpg',
                    'inspections/pickup/' . $testName . '_right.jpg',
                    'inspections/pickup/' . $testName . '_dashboard.jpg',
                ]);

                Inspection::create([
                    'bookingID' => $booking->bookingID,
                    'staffID' => null, 
                    'inspectionType' => 'Pickup',
                    'inspectionDate' => $pickupDate . ' ' . $pickupTime,
                    'damageCosts' => 0.00,
                    'photosBefore' => $pickupPhotos,
                    'fuelBefore' => $fuelBefore,
                    'mileageBefore' => $mileageBefore,
                ]);

                // 4. Create Return Inspection (6 Photos)
                $returnPhotos = json_encode([
                    'inspections/return/' . $testName . '_front.jpg',
                    'inspections/return/' . $testName . '_back.jpg',
                    'inspections/return/' . $testName . '_left.jpg',
                    'inspections/return/' . $testName . '_right.jpg',
                    'inspections/return/' . $testName . '_dashboard.jpg',
                    'inspections/return/' . $testName . '_key_location.jpg',
                ]);

                Inspection::create([
                    'bookingID' => $booking->bookingID,
                    'staffID' => null, 
                    'inspectionType' => 'Return',
                    'inspectionDate' => $returnDate . ' ' . $returnTime,
                    'damageCosts' => 0.00,
                    'photosAfter' => $returnPhotos, 
                    'fuelAfter' => $fuelAfter,
                    'mileageAfter' => $mileageAfter,
                ]);

                // 5. Update Vehicle Mileage
                $vehicle->update(['mileage' => $mileageAfter]);

                $imported++;
                $testCounter++;
            }
        } finally {
            fclose($file);
        }

        return "Successfully Imported: $imported bookings. Skipped: $skipped.";
    }
}