<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BookingController extends Controller
{
    // Show "Book a Car" Search Page
    public function create()
    {
        return view('bookings.create');
    }

    // Show "My Bookings" List
    public function index()
    {
        // In a real app, you would fetch these from the database:
        // $bookings = Booking::where('customer_id', auth()->id())->get();
        return view('bookings.index');
    }
}