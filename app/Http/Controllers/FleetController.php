<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class FleetController extends Controller
{
    /**
     * Display a listing of the fleet with status filters.
     */
    public function index(Request $request)
    {
        // Fetch vehicles with today's active bookings
        $vehicles = Vehicle::with(['bookings' => function($q) {
            $today = Carbon::now()->toDateString();
            
            // FIXED: Used 'originalDate' instead of 'pickupDate'
            $q->whereIn('bookingStatus', ['Confirmed', 'Active', 'Deposit Paid']) 
              ->whereDate('originalDate', '<=', $today) 
              ->whereDate('returnDate', '>=', $today);
        }])->orderBy('created_at', 'desc')->get();

        // Calculate counts
        $total = $vehicles->count();
        $activeCount = $vehicles->where('availability', 1)->count();
        $inactiveCount = $vehicles->where('availability', 0)->count();

        // Check if currently booked
        foreach($vehicles as $vehicle) {
            $vehicle->isBookedToday = $vehicle->bookings->isNotEmpty();
        }

        return view('staff.fleet.index', compact('vehicles', 'total', 'activeCount', 'inactiveCount'));
    }

    /**
     * Show the form for creating a new vehicle.
     */
    public function create()
    {
        return view('staff.fleet.create');
    }

    /**
     * Store a newly created vehicle in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'plateNo' => 'required|string|unique:vehicles,plateNo',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'brand'   => 'required',
            'model'   => 'required',
        ]);

        $imagePath = null;
        // 2. Handle Image Upload with Custom Filename
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            
            // Clean Plate No (Remove spaces, uppercase) -> "UTM9078"
            $plateNo = strtoupper(str_replace(' ', '', $request->plateNo));
            
            // Generate Filename -> "UTM9078.jpg"
            $filename = $plateNo . '.' . $file->getClientOriginalExtension();
            
            // Store in 'storage/app/public/vehicles'
            // This requires: php artisan storage:link
            $path = $file->storeAs('vehicles', $filename, 'public');
            
            $imagePath = 'vehicles/' . $filename; // Path to save in DB
        }

        Vehicle::create([
            'vehicle_category' => $request->vehicle_category,
            'brand'            => $request->brand,
            'model'            => $request->model,
            'plateNo'          => $request->plateNo,
            'type'             => $request->type,
            'year'             => $request->year,
            'color'            => $request->color,
            'mileage'          => $request->mileage ?? 0,
            'fuelType'         => $request->fuelType,
            'baseDepo'         => $request->baseDepo,
            'owner_name'       => $request->owner_name ?? 'Hasta Travel & Tours',
            'owner_phone'      => $request->owner_phone,
            'owner_nric'       => $request->owner_nric,
            'hourly_rates'     => $request->rates, 
            'image'            => $imagePath,
            'availability'     => 1,
            'priceHour'        => $request->rates[1] ?? 0,
        ]);

        return redirect()->route('staff.fleet.index')
            ->with('success', 'Vehicle registered to fleet successfully.');
    }

    public function show($id)
    {
        // 1. Load Data (Fixed: 'maintenances' relationship now exists)
        $vehicle = Vehicle::with(['bookings.customer', 'bookings.inspections', 'maintenances'])
            ->findOrFail($id);
            
        $currentMileage = $vehicle->mileage;

        // 2. Service Reminder Logic (Example: 10k km or 6 months)
        $lastMaint = $vehicle->maintenances->sortByDesc('date')->first();
        $serviceDue = false;
        $serviceMsg = "Healthy";
        
        if ($lastMaint) {
            // Check Mileage (Assuming service every 10,000km)
            // Ideally, you'd save 'mileage_at_service' in DB. Here we assume 10k intervals.
            $nextServiceDate = \Carbon\Carbon::parse($lastMaint->date)->addMonths(6);
            
            // Simple check: If current mileage is way higher than last service or date passed
            if (\Carbon\Carbon::now()->gt($nextServiceDate)) {
                $serviceDue = true;
                $serviceMsg = "Service Due (Time)";
            }
        } elseif ($currentMileage > 10000) {
            $serviceDue = true;
            $serviceMsg = "First Service Due";
        }

        // 3. Build Calendar Events
        $events = [];

        // A. Bookings
        foreach($vehicle->bookings as $booking) {
            if($booking->bookingStatus !== 'Cancelled') {
                $custName = $booking->customer ? ($booking->customer->name ?? 'Guest') : 'Guest';
                $events[] = [
                    'id' => $booking->bookingID,
                    'title' => $custName,
                    'start' => $booking->bookingDate,
                    'end'   => $booking->returnDate,
                    'color' => '#f97316', // Orange
                    'type'  => 'booking',
                    // Pass details for popup
                    'extendedProps' => [
                        'status' => $booking->bookingStatus,
                        'cost' => $booking->totalCost,
                        'pickup' => $booking->pickupLocation,
                        'dropoff' => $booking->returnLocation,
                        'time' => $booking->bookingTime . ' - ' . $booking->returnTime,
                        'cust_name' => $custName,
                        'cust_phone' => $booking->customer->phoneNo ?? 'N/A'
                    ]
                ];
            }
        }

        // B. Maintenance Records (The new part)
        foreach($vehicle->maintenances as $maint) {
            $events[] = [
                'id' => 'm_' . $maint->MaintenanceID,
                'title' => 'Maintenance', // Shows as blocked on calendar
                'start' => $maint->date,
                'end'   => $maint->date, // Or add duration if you have it
                'color' => '#ef4444', // Red
                'type'  => 'maintenance_log',
                'extendedProps' => [
                    'desc' => $maint->description,
                    'cost' => $maint->cost
                ]
            ];
        }

        // C. Manual Blocks (blocked_dates JSON)
        if($vehicle->blocked_dates) {
            foreach($vehicle->blocked_dates as $date) {
                $events[] = [
                    'id' => 'blk_' . $date,
                    'title' => 'Blocked',
                    'start' => $date,
                    'end'   => $date,
                    'color' => '#ef4444',
                    'type'  => 'manual_block',
                    'extendedProps' => ['date_value' => $date]
                ];
            }
        }

        return view('staff.fleet.show', compact('vehicle', 'currentMileage', 'events', 'serviceDue', 'serviceMsg'));
    }

    // --- ADD DATE TO JSON ARRAY ---
    public function blockDate(Request $request, $id)
    {
        $request->validate(['date' => 'required|date']);
        $vehicle = Vehicle::findOrFail($id);

        $dates = $vehicle->blocked_dates ?? []; // Get current array

        // Prevent duplicates
        if (!in_array($request->date, $dates)) {
            $dates[] = $request->date; // Add new date
            $vehicle->blocked_dates = $dates; // Update model
            $vehicle->save(); // Save to DB
            return back()->with('success', 'Date blocked successfully.');
        }

        return back()->with('info', 'Date is already blocked.');
    }

    // --- REMOVE DATE FROM JSON ARRAY ---
    public function unblockDate(Request $request, $id)
    {
        $request->validate(['date' => 'required|date']);
        $vehicle = Vehicle::findOrFail($id);

        $dates = $vehicle->blocked_dates ?? [];
        
        // Filter out the date to be removed
        $updatedDates = array_values(array_diff($dates, [$request->date]));

        $vehicle->blocked_dates = $updatedDates;
        $vehicle->save();

        return back()->with('success', 'Date unblocked.');
    }

    public function storeMaintenance(Request $request, $id)
    {
        $request->validate([
            'date' => 'required|date',
            'cost' => 'required|numeric|min:0',
            'description' => 'required|string|max:255',
        ]);

        \App\Models\Maintenance::create([
            'VehicleID' => $id,
            'StaffID' => \Illuminate\Support\Facades\Auth::guard('staff')->id() ?? 1, // Default to 1 if testing
            'date' => $request->date,
            'cost' => $request->cost,
            'description' => $request->description,
        ]);

        return back()->with('success', 'Service record logged successfully.');
    }

    public function destroyBooking($booking_id)
    {
        $booking = \App\Models\Booking::findOrFail($booking_id);
        $booking->delete(); // Or set to Cancelled
        return back()->with('success', 'Date unblocked.');
    }

    /**
     * Update the specified vehicle in storage.
     */
    public function update(Request $request, $id)
    {
        $vehicle = Vehicle::findOrFail($id);

        $request->validate([
            'plateNo' => 'required|unique:vehicles,plateNo,' . $id . ',VehicleID',
            'image' => 'nullable|image|max:2048',
        ]);

        $data = $request->all();

        // Handle Image Replacement
        if ($request->hasFile('image')) {
            // Delete old image if it exists
            if ($vehicle->image) {
                Storage::disk('public')->delete($vehicle->image);
            }
            $data['image'] = $request->file('image')->store('vehicles', 'public');
        }

        // Sync JSON rates and display price
        $data['hourly_rates'] = $request->rates;
        $data['priceHour'] = $request->rates[1] ?? 0;

        $vehicle->update($data);

        return redirect()->route('staff.fleet.index')
            ->with('success', 'Vehicle updated successfully.');
    }

    public function updateStatus($id)
    {
        $vehicle = Vehicle::findOrFail($id);
        
        // Toggle: If 1 make 0, if 0 make 1
        $vehicle->availability = !$vehicle->availability;
        $vehicle->save();

        $statusMsg = $vehicle->availability ? 'Active' : 'Inactive';
        return back()->with('success', "Vehicle marked as $statusMsg.");
    }

    // 6. DELETE VEHICLE
    public function destroy($id)
    {
        $vehicle = Vehicle::findOrFail($id);
        
        // Optional: Delete image if exists
        if($vehicle->image) { Storage::disk('public')->delete($vehicle->image); }

        $vehicle->delete();
        return redirect()->route('staff.fleet.index')
            ->with('success', 'Vehicle removed from fleet inventory.');
    }

    // 7. EDIT FORM (Placeholder for the Modify button)
    public function edit($id)
    {
        $vehicle = Vehicle::findOrFail($id);
        // You can reuse the create view or make a separate edit.blade.php
        return view('staff.fleet.edit', compact('vehicle')); 
    }

    /**
     * Update the availability status of a vehicle.
     */
    public function toggleAvailability($id)
    {
        $vehicle = Vehicle::findOrFail($id);
        $vehicle->availability = !$vehicle->availability;
        $vehicle->save();

        return redirect()->route('staff.fleet.index')
            ->with('success', 'Vehicle availability status updated.');
    }
}