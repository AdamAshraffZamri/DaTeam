<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Feedback;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    public function store(Request $request, $id)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        $booking = Booking::where('customerID', Auth::id())->findOrFail($id);

        if ($booking->bookingStatus !== 'Completed') {
            return back()->with('error', 'You can only review completed bookings.');
        }

        $exists = Feedback::where('bookingID', $id)->exists();
        if($exists) {
            return back()->with('error', 'You have already provided feedback for this booking.');
        }

        Feedback::create([
            'bookingID' => $id,
            'customerID' => Auth::id(),
            'rating' => $request->rating,
            'comment' => $request->comment, 
            'type' => 'Review', // <--- FIXED: Provide a default type
            'created_at' => now(),
        ]);

        return back()->with('success', 'Thank you for your feedback!');
    }
}