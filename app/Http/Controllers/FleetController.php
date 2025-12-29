<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FleetController extends Controller
{
    /**
     * Display a listing of the fleet with status filters.
     */
    public function index(Request $request)
    {
        $status = $request->get('status', 'all');
        $query = Vehicle::query();

        // Filter by availability if requested
        if ($status === 'available') {
            $query->where('availability', 1);
        } elseif ($status === 'maintenance') {
            $query->where('availability', 0);
        }

        $vehicles = $query->orderBy('created_at', 'desc')->get();
        
        $stats = [
            'available' => Vehicle::where('availability', 1)->count(),
            'maintenance' => Vehicle::where('availability', 0)->count(),
        ];

        return view('staff.fleet.index', compact('vehicles', 'stats', 'status'));
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
        $vehicle = Vehicle::findOrFail($id);
        return view('staff.fleet.show', compact('vehicle'));
    }

    /**
     * Show the form for editing the specified vehicle.
     */
    public function edit($id)
    {
        $vehicle = Vehicle::findOrFail($id);
        return view('staff.fleet.edit', compact('vehicle'));
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

    /**
     * Remove the vehicle and its media from storage.
     */
    public function destroy($id)
    {
        $vehicle = Vehicle::findOrFail($id);

        // Remove photo from storage before deleting record
        if ($vehicle->image) {
            Storage::disk('public')->delete($vehicle->image);
        }

        $vehicle->delete();

        return redirect()->route('staff.fleet.index')
            ->with('success', 'Vehicle removed from fleet inventory.');
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