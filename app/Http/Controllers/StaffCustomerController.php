<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class StaffCustomerController extends Controller
{
    // Show the list of customers
    public function index(Request $request)
    {
        $query = Customer::query();

        // Search logic
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('fullName', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('stustaffID', 'like', "%{$search}%");
        }

        $customers = $query->latest()->paginate(10);
        return view('staff.customers.index', compact('customers'));
    }

    // Show single customer details
    public function show($id)
    {
        $customer = Customer::findOrFail($id);
        return view('staff.customers.show', compact('customer'));
    }

    // ACTION 1: Approve
    public function approve($id)
    {
        $customer = Customer::findOrFail($id);
        
        $customer->update([
            'accountStat' => 'approved',
            'rejection_reason' => null // Clear any previous rejection error
        ]);

        return back()->with('success', 'Customer has been verified and approved.');
    }

    // ACTION 2: Reject
    public function reject(Request $request, $id)
    {
        $request->validate(['rejection_reason' => 'required|string|max:500']);

        $customer = Customer::findOrFail($id);
        
        $customer->update([
            'accountStat' => 'rejected',
            'rejection_reason' => $request->rejection_reason
        ]);

        return back()->with('success', 'Customer rejected. Reason sent to user.');
    }

    // ACTION 3: Blacklist (Toggle)
    public function toggleBlacklist(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        if ($customer->blacklisted) {
            // === ACTION: REMOVE FROM BLACKLIST ===
            
            // 1. Determine what the status should go back to
            // If we have a saved previous status, use it. Otherwise, default to 'unverified'.
            $newStatus = $customer->previous_account_stat ?? 'unverified';

            $customer->update([
                'blacklisted' => false,
                'blacklist_reason' => null,
                'accountStat' => $newStatus,      // Restore the old status
                'previous_account_stat' => null,  // Clear the memory
            ]);

            return back()->with('success', 'Customer removed from blacklist. Status reverted to ' . ucfirst($newStatus) . '.');

        } else {
            // === ACTION: ADD TO BLACKLIST ===
            
            $request->validate(['blacklist_reason' => 'required|string|max:500']);
            
            $customer->update([
                'blacklisted' => true,
                'blacklist_reason' => $request->blacklist_reason,
                'previous_account_stat' => $customer->accountStat, // <--- SAVE CURRENT STATUS HERE
                'accountStat' => 'rejected' // Set to rejected/blacklisted state
            ]);

            return back()->with('success', 'Customer has been blacklisted.');
        }
    }
}