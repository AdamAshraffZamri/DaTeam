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
            'plateNo' => 'required|unique:vehicles',
            'image'   => 'nullable|image|max:2048',
            'brand'   => 'required',
            'model'   => 'required',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('vehicles', 'public');
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

    /**
     * Display the glassy profile of a specific vehicle.
     */
    public function show($id)
    {
        $vehicle = Vehicle::with(['bookings.customer', 'bookings.inspections'])->findOrFail($id);
        $currentMileage = $vehicle->mileage;

        $events = [];

        foreach($vehicle->bookings as $booking) {
            if($booking->bookingStatus !== 'Cancelled') {
                
                // === 1. FIX THE NAME/TITLE ===
                $title = '';
                
                if ($booking->bookingStatus === 'Maintenance') {
                    $title = 'Maintenance';
                } elseif ($booking->customer) {
                    // If customer exists, show Name
                    $title = $booking->customer->fullName;
                } else {
                    // If customer is MISSING (NULL), show Booking ID instead
                    $title = 'Booking #' . $booking->bookingID;
                }

                $events[] = [
                    'id' => $booking->bookingID,
                    'title' => $title, // No more "null"
                    'start' => $booking->bookingDate,
                    'end'   => $booking->returnDate,
                    'color' => '#f97316', 
                    'type'  => 'booking',
                    
                    // === 2. PASS DETAILS FOR POPUP ===
                    'extendedProps' => [
                        'status' => $booking->bookingStatus,
                        'cost'   => number_format($booking->totalCost, 2),
                        'pickup' => $booking->pickupLocation ?? 'N/A',
                        'dropoff'=> $booking->returnLocation ?? 'N/A',
                        'time'   => ($booking->bookingTime ?? '00:00') . ' - ' . ($booking->returnTime ?? '00:00'),
                        'cust_name' => $booking->customer ? $booking->customer->name : 'Walk-in / Guest',
                        'cust_phone'=> $booking->customer ? $booking->customer->phoneNo : 'N/A'
                    ]
                ];
            }
        }

        // Add Blocked Dates (Maintenance from JSON)
        $blockedDates = $vehicle->blocked_dates ?? [];
        foreach($blockedDates as $date) {
            $events[] = [
                'id' => 'block_' . $date,
                'title' => 'Blocked',
                'start' => $date,
                'end'   => $date,
                'color' => '#ef4444',
                'type'  => 'maintenance',
                'extendedProps' => ['date_value' => $date]
            ];
        }

        return view('staff.fleet.show', compact('vehicle', 'currentMileage', 'events'));
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
        // if($vehicle->image) { Storage::disk('public')->delete($vehicle->image); }

        $vehicle->delete();
        return back()->with('success', 'Vehicle removed from fleet.');
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