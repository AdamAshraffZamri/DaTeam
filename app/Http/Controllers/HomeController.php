<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vehicle;
use App\Models\Deal;
use App\Models\StaffSetting;

class HomeController extends Controller
{
    public function index()
    {
        // Fetch ALL available vehicles for the fleet showcase
        $vehicles = Vehicle::where('availability', true)
                            ->orderBy('priceHour', 'asc')
                            ->get();

        // Fetch active deals (multiple) - get from first available staff or latest deals
        $deals = Deal::where('active', true)
                     ->orderBy('order')
                     ->orderBy('created_at', 'desc')
                     ->limit(3)
                     ->get();

        // Fetch staff settings (Johor highlights only)
        $staffSettings = StaffSetting::first() ?? null;

        return view('home', compact('vehicles', 'deals', 'staffSettings'));
    }
}