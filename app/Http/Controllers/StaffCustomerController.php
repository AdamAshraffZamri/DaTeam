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
        $customer = Customer::findOrFail($id);
        
        $request->validate([
            'rejection_reason' => 'required|array|min:1',
            'rejection_reason.*' => 'string',
            'rejection_custom' => 'nullable|string|max:500' // New validation
        ], [
            'rejection_reason.required' => 'Please select at least one reason.'
        ]);

        // 1. Convert Array to String
        $reasonString = implode(', ', $request->rejection_reason);

        // 2. Append Custom Notes if they exist
        if ($request->filled('rejection_custom')) {
            $reasonString .= " (" . $request->rejection_custom . ")";
        }

        $customer->update([
            'accountStat' => 'rejected',
            'rejection_reason' => $reasonString 
        ]);

        return back()->with('success', 'Customer application rejected.');
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

        } 
        else {
            // === ACTION: ADD TO BLACKLIST ===
            $request->validate([
                'blacklist_reason' => 'required|string',
                'blacklist_custom' => 'nullable|string|max:500' // New validation
            ]);
            
            // Combine Reason + Custom Text
            // Example: "Severe Vehicle Damage - Bumper completely destroyed"
            $finalReason = $request->blacklist_reason;
            if ($request->filled('blacklist_custom')) {
                $finalReason .= " - " . $request->blacklist_custom;
            }

            $customer->update([
                'blacklisted' => true,
                'blacklist_reason' => $finalReason,
                'previous_account_stat' => $customer->accountStat, 
                'accountStat' => 'rejected'
            ]);

            return back()->with('success', 'Customer has been blacklisted.');
            }
    }
}