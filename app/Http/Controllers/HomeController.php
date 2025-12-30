<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vehicle;

class HomeController extends Controller
{
    public function index()
    {
        // Fetch ALL available vehicles for the fleet showcase
        $vehicles = Vehicle::where('availability', true)
                            ->orderBy('priceHour', 'asc') // Optional: Order by price or creation
                            ->get();

        return view('home', compact('vehicles'));
    }
}