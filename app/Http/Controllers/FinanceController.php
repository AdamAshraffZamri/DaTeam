<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Penalties;
use Illuminate\Support\Facades\Auth;

class FinanceController extends Controller
{
    public function index()
    {
        // FIX: Use Auth::id() to get the correct customerID
        $customerID = Auth::id();

        // 1. Get Refundable Items
        $claims = Booking::where('customerID', $customerID)
                         ->whereIn('bookingStatus', ['Cancelled', 'Completed']) // Check logic here based on your flow
                         ->with(['vehicle', 'payment'])
                         ->get();

        // 2. Get Payable Items
        $fines = Penalties::whereHas('booking', function($q) use ($customerID) {
                            $q->where('customerID', $customerID);
                        })
                        ->where('status', 'Pending')
                        ->with('booking.vehicle')
                        ->get();

        return view('finance.index', compact('claims', 'fines'));
    }
}