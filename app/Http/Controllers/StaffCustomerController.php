<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class StaffCustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query();

        // Simple search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('fullName', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('stustaffID', 'like', "%{$search}%");
        }

        $customers = $query->paginate(10);

        return view('staff.customers.index', compact('customers'));
    }

    public function show($id)
    {
        $customer = Customer::findOrFail($id);
        // Assuming you have a relation defined in Customer model for bookings
        // $bookings = $customer->bookings()->latest()->get(); 
        return view('staff.customers.show', compact('customer'));
    }

    public function toggleBlacklist($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->blacklisted = !$customer->blacklisted;
        $customer->save();

        return back()->with('success', 'Customer status updated successfully.');
    }

    public function approve($id)
    {
        $customer = Customer::findOrFail($id);
        
        $customer->update([
            'accountStat' => 'active',
            'rejection_reason' => null
        ]);

        return back()->with('success', 'Customer profile verified and activated successfully.');
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000'
        ]);

        $customer = Customer::findOrFail($id);
        
        $customer->update([
            'accountStat' => 'rejected',
            'rejection_reason' => $request->rejection_reason
        ]);

        return back()->with('success', 'Customer profile rejected. Feedback sent.');
    }
}