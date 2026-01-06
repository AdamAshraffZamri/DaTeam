<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Booking;
use App\Models\Maintenance;
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
        $query = Vehicle::query();

        // 1. Search Filter (Plate, Brand, Model)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('brand', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%")
                  ->orWhere('plateNo', 'like', "%{$search}%");
            });
        }

        // 2. Model Filter
        if ($request->filled('model') && $request->model !== 'all') {
            $query->where('model', $request->model);
        }

        // 3. Status Filter
        if ($request->filled('status') && $request->status !== 'all') {
            if ($request->status === 'active') {
                $query->where('availability', 1);
            } elseif ($request->status === 'inactive') {
                $query->where('availability', 0);
            }
        }

        // Fetch distinct models for the dropdown
        $vehicleModels = Vehicle::select('model')->distinct()->orderBy('model')->pluck('model');

        // Calculate Global Counts (for display/reference if needed, strictly speaking unrelated to current filter)
        $total = Vehicle::count();
        $activeCount = Vehicle::where('availability', 1)->count();
        $inactiveCount = Vehicle::where('availability', 0)->count();

        // Eager load active/future bookings to determine status and next booking
        $vehicles = $query->with(['bookings' => function($q) {
            $today = Carbon::now()->toDateString();
            $q->whereIn('bookingStatus', ['Confirmed', 'Active', 'Deposit Paid'])
              ->whereDate('returnDate', '>=', $today) // Active or Future
              ->orderBy('originalDate', 'asc');
        }])->orderBy('created_at', 'desc')->get();

        // Process derived attributes
        foreach($vehicles as $vehicle) {
            $today = Carbon::now()->toDateString();

            // Check if currently physically booked (Active intersection with Today)
            $vehicle->isBookedToday = $vehicle->bookings->contains(function($b) use ($today) {
                return $b->originalDate <= $today && $b->returnDate >= $today;
            });

            // Find Next Booking (First booking starting > today, or current one if relevant for display)
            // We want the *next* start date usually.
            $next = $vehicle->bookings->first(function($b) use ($today) {
                return $b->originalDate > $today;
            });
            
            // If no future booking, but is currently booked, maybe show current return date? 
            // The prompt asks for "Next Booking Date". I'll prioritize a future start date.
            $vehicle->nextBookingDate = $next ? Carbon::parse($next->originalDate) : null;
        }

        return view('staff.fleet.index', compact('vehicles', 'vehicleModels', 'total', 'activeCount', 'inactiveCount'));
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
            'road_tax_image' => 'nullable|mimes:jpeg,png,jpg,pdf|max:2048',
            'grant_image'    => 'nullable|mimes:jpeg,png,jpg,pdf|max:2048',
            'insurance_image'=> 'nullable|mimes:jpeg,png,jpg,pdf|max:2048',
        ]);

        // === 1. SANITIZE PLATE NO ===
        // "utm 123" -> "UTM123"
        $plateNo = strtoupper(str_replace(' ', '', $request->plateNo));

        // === 2. HANDLE FILES ===
        
        // A. Main Vehicle Image (Keeps original path 'vehicles/')
        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = $plateNo . '.' . $file->getClientOriginalExtension();
            $file->storeAs('vehicles', $filename, 'public');
            $imagePath = 'vehicles/' . $filename;
        }

        // B. Road Tax (Now in 'vehiclesDocs/roadtax/')
        $roadTaxPath = null;
        if ($request->hasFile('road_tax_image')) {
            $file = $request->file('road_tax_image');
            $filename = $plateNo . '_roadtax.' . $file->getClientOriginalExtension();
            
            // Auto-creates folder if missing
            $file->storeAs('vehiclesDocs/roadtax', $filename, 'public'); 
            $roadTaxPath = 'vehiclesDocs/roadtax/' . $filename;
        }

        // C. Grant (Now in 'vehiclesDocs/grant/')
        $grantPath = null;
        if ($request->hasFile('grant_image')) {
            $file = $request->file('grant_image');
            $filename = $plateNo . '_grant.' . $file->getClientOriginalExtension();
            
            // Auto-creates folder if missing
            $file->storeAs('vehiclesDocs/grant', $filename, 'public');
            $grantPath = 'vehiclesDocs/grant/' . $filename;
        }

        // D. Insurance (Now in 'vehiclesDocs/insurance/')
        $insurancePath = null;
        if ($request->hasFile('insurance_image')) {
            $file = $request->file('insurance_image');
            $filename = $plateNo . '_insurance.' . $file->getClientOriginalExtension();
            
            // Auto-creates folder if missing
            $file->storeAs('vehiclesDocs/insurance', $filename, 'public');
            $insurancePath = 'vehiclesDocs/insurance/' . $filename;
        }

        // === 3. CREATE RECORD ===
        Vehicle::create([
            'vehicle_category' => $request->vehicle_category,
            'brand'            => $request->brand,
            'model'            => $request->model,
            'plateNo'          => $plateNo,
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
            
            // File Paths
            'image'            => $imagePath,
            'road_tax_image'   => $roadTaxPath,
            'grant_image'      => $grantPath,
            'insurance_image'  => $insurancePath,
            
            'availability'     => 1,
            'priceHour'        => $request->rates[1] ?? 0,
        ]);

        return redirect()->route('staff.fleet.index')
            ->with('success', 'Vehicle registered successfully.');
    }

    public function show($id)
    {
        // [UPDATED] Eager load 'staff' for maintenance history
        $vehicle = Vehicle::with(['bookings.customer', 'maintenances.staff'])->findOrFail($id);
        $currentMileage = $vehicle->mileage;

        // Financials
        $validBookings = $vehicle->bookings->whereNotIn('bookingStatus', ['Cancelled', 'Rejected']);
        $totalEarnings = $validBookings->sum('totalCost');
        $totalMaintenanceCost = $vehicle->maintenances->sum('cost');
        $netProfit = $totalEarnings - $totalMaintenanceCost;

        $events = [];

        // --- 1. CUSTOMER BOOKINGS ---
        foreach($vehicle->bookings as $booking) {
            if(in_array($booking->bookingStatus, ['Cancelled', 'Rejected'])) continue;

            $startDate = \Carbon\Carbon::parse($booking->originalDate)->format('Y-m-d');
            $startTime = \Carbon\Carbon::parse($booking->bookingTime)->format('H:i:s');
            $endDate   = \Carbon\Carbon::parse($booking->returnDate)->format('Y-m-d');
            $endTime   = \Carbon\Carbon::parse($booking->returnTime)->format('H:i:s');

            $startIso = $startDate . 'T' . $startTime;
            $endIso   = $endDate . 'T' . $endTime;

            $events[] = [
                'id' => 'booking_' . $booking->bookingID,
                'title' => $booking->customer->fullName ?? 'Booked',
                'start' => $startIso,
                'end'   => $endIso,
                'color' => '#f97316', // Orange
                'textColor' => '#ffffff',
                'allDay' => false, // Bookings are specific times
                'type'  => 'booking',
                'extendedProps' => [ 'status' => $booking->bookingStatus ]
            ];
        }

        // --- 2. MAINTENANCE BLOCKS ---
        foreach($vehicle->maintenances as $block) {
            $color = '#ef4444'; // Red
            $title = 'Maintenance';
            if($block->type === 'holiday') { $color = '#a855f7'; $title = 'Holiday'; }
            if($block->type === 'delivery') { $color = '#3b82f6'; $title = 'Delivery'; }
            if($block->type === 'other') { $color = '#6b7280'; $title = 'Blocked'; }

            $s = \Carbon\Carbon::parse($block->start_time);
            $e = \Carbon\Carbon::parse($block->end_time);
            
            // STRICT CHECK: Only treat as All Day if explicitly 00:00 to 23:59:59
            $isAllDay = $s->format('H:i:s') === '00:00:00' && $e->format('H:i:s') === '23:59:59';

            if ($isAllDay) {
                // For All Day, FullCalendar needs YYYY-MM-DD format
                // End date must be exclusive (next day) for the visual bar to cover the full current day
                $startStr = $s->format('Y-m-d');
                $endStr   = $e->copy()->addDay()->startOfDay()->format('Y-m-d');
            } else {
                // FOR SPECIFIC TIMES:
                // Use strict format 'YYYY-MM-DDTHH:mm:ss' without timezone offset.
                // This forces the calendar to render exactly 09:00 if the DB says 09:00.
                $startStr = $s->format('Y-m-d\TH:i:s');
                $endStr   = $e->format('Y-m-d\TH:i:s');
            }

            $events[] = [
                'id' => $block->MaintenanceID,
                'title' => $title,
                'start' => $startStr,
                'end'   => $endStr,
                'color' => $color,
                'textColor' => '#ffffff',
                'allDay' => $isAllDay, // If false, the calendar will render the specific time label
                'type'  => 'block',
                'extendedProps' => [ 
                    'cost' => $block->cost, 
                    'desc' => $block->description,
                    'staff_name' => $block->staff->name ?? 'System',
                    'created_at' => $block->created_at->format('d M Y, h:i A')
                ]
            ];
        }

        return view('staff.fleet.show', compact('vehicle', 'currentMileage', 'events', 'netProfit', 'totalEarnings', 'totalMaintenanceCost'));
    }

    // ... [Rest of the controller methods destroyMaintenance, storeMaintenance, etc. remain unchanged]
    
    // This method handles the "Unblock Date" button
    public function destroyMaintenance($id)
    {
        $maintenance = Maintenance::findOrFail($id);
        $maintenance->delete();

        return back()->with('success', 'Schedule unblocked successfully.');
    }

    public function storeMaintenance(Request $request, $id)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'type' => 'required',
        ]);

        $isAllDay = filter_var($request->all_day, FILTER_VALIDATE_BOOLEAN);

        $startTime = $isAllDay ? '00:00:00' : ($request->start_time ? $request->start_time . ':00' : '00:00:00');
        $endTime   = $isAllDay ? '23:59:59' : ($request->end_time ? $request->end_time . ':00' : '23:59:59');

        $start = \Carbon\Carbon::parse($request->start_date . ' ' . $startTime);
        $end   = \Carbon\Carbon::parse($request->end_date . ' ' . $endTime);

        $description = $request->reason;
        if($request->type === 'maintenance') {
            $description = $request->maintenance_desc;
        }

        \App\Models\Maintenance::create([
            'VehicleID' => $id,
            'StaffID' => \Illuminate\Support\Facades\Auth::guard('staff')->id() ?? 1,
            'type' => $request->type,
            'start_time' => $start,
            'end_time' => $end,
            'date' => $start,
            'description' => $description,
            'reference_id' => $request->ref_id,
            'cost' => $request->maintenance_cost ?? 0,
        ]);

        return back()->with('success', 'Schedule updated successfully.');
    }

    /**
     * Update the specified vehicle in storage.
     */
    public function update(Request $request, $id)
    {
        $vehicle = Vehicle::findOrFail($id);

        $request->validate([
            // Unique validation ignores the current vehicle's ID
            'plateNo' => 'required|string|unique:vehicles,plateNo,' . $id . ',VehicleID',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'brand'   => 'required',
            'model'   => 'required',
            'road_tax_image' => 'nullable|mimes:jpeg,png,jpg,pdf|max:2048',
            'grant_image'    => 'nullable|mimes:jpeg,png,jpg,pdf|max:2048',
            'insurance_image'=> 'nullable|mimes:jpeg,png,jpg,pdf|max:2048',
        ]);

        // === 1. SANITIZE PLATE NO ===
        // "utm 123" -> "UTM123"
        $plateNo = strtoupper(str_replace(' ', '', $request->plateNo));

        // Prepare data for update (exclude files and rates initially)
        $data = $request->except(['image', 'road_tax_image', 'grant_image', 'insurance_image', 'rates']);
        
        // Ensure the sanitized plate number is used
        $data['plateNo'] = $plateNo;

        // === 2. HANDLE FILES (Matches Store Logic) ===

        // A. Main Image ('vehicles/')
        if ($request->hasFile('image')) {
            // Delete old file
            if ($vehicle->image && Storage::disk('public')->exists($vehicle->image)) {
                Storage::disk('public')->delete($vehicle->image);
            }
            
            $file = $request->file('image');
            $filename = $plateNo . '.' . $file->getClientOriginalExtension();
            $file->storeAs('vehicles', $filename, 'public');
            $data['image'] = 'vehicles/' . $filename;
        }

        // B. Road Tax ('vehiclesDocs/roadtax/')
        if ($request->hasFile('road_tax_image')) {
            if ($vehicle->road_tax_image && Storage::disk('public')->exists($vehicle->road_tax_image)) {
                Storage::disk('public')->delete($vehicle->road_tax_image);
            }

            $file = $request->file('road_tax_image');
            $filename = $plateNo . '_roadtax.' . $file->getClientOriginalExtension();
            $file->storeAs('vehiclesDocs/roadtax', $filename, 'public');
            $data['road_tax_image'] = 'vehiclesDocs/roadtax/' . $filename;
        }

        if ($request->input('delete_road_tax') == 1) {
            // Delete old file from storage
            if ($vehicle->road_tax_image) {
                Storage::disk('public')->delete($vehicle->road_tax_image);
            }
            // Set column to null
            $vehicle->road_tax_image = null;
        }

        // C. Grant ('vehiclesDocs/grant/')
        if ($request->hasFile('grant_image')) {
            if ($vehicle->grant_image && Storage::disk('public')->exists($vehicle->grant_image)) {
                Storage::disk('public')->delete($vehicle->grant_image);
            }

            $file = $request->file('grant_image');
            $filename = $plateNo . '_grant.' . $file->getClientOriginalExtension();
            $file->storeAs('vehiclesDocs/grant', $filename, 'public');
            $data['grant_image'] = 'vehiclesDocs/grant/' . $filename;
        }

        if ($request->input('delete_grant') == 1) {
            // Delete old file from storage
            if ($vehicle->grant_image) {
                Storage::disk('public')->delete($vehicle->grant_image);
            }
            // Set column to null
            $vehicle->grant_image = null;
        }

        // D. Insurance ('vehiclesDocs/insurance/')
        if ($request->hasFile('insurance_image')) {
            if ($vehicle->insurance_image && Storage::disk('public')->exists($vehicle->insurance_image)) {
                Storage::disk('public')->delete($vehicle->insurance_image);
            }

            $file = $request->file('insurance_image');
            $filename = $plateNo . '_insurance.' . $file->getClientOriginalExtension();
            $file->storeAs('vehiclesDocs/insurance', $filename, 'public');
            $data['insurance_image'] = 'vehiclesDocs/insurance/' . $filename;
        }

        if ($request->input('delete_insurance') == 1) {
            // Delete old file from storage
            if ($vehicle->insurance_image) {
                Storage::disk('public')->delete($vehicle->insurance_image);
            }
            // Set column to null
            $vehicle->insurance_image = null;
        }

        // === 3. HANDLE OTHER DATA ===
        
        // Default Owner Name
        if(empty($data['owner_name'])) {
            $data['owner_name'] = 'Hasta Travel & Tours';
        }

        // Sync JSON rates and display price
        $data['hourly_rates'] = $request->rates;
        $data['priceHour'] = $request->rates[1] ?? 0;

        $vehicle->update($data);

        return redirect()->route('staff.fleet.show', $vehicle->VehicleID)
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
            ->with('success', 'Vehicle removed from fleet inventory successfully.');
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