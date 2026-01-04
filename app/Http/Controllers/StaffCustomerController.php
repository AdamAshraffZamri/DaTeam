<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Penalties;
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

<<<<<<< HEAD

=======
>>>>>>> origin/testmerge
    // ACTION 4: Impose Penalty
    public function imposePenalty(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);
        
        $request->validate([
            'penalty_reason' => 'required|string',
            'penalty_amount' => 'required|numeric|min:0.01',
            'penalty_custom' => 'nullable|string|max:500'
        ]);

        // Combine Reason + Custom Text
        $finalReason = $request->penalty_reason;
        if ($request->filled('penalty_custom')) {
            $finalReason .= " - " . $request->penalty_custom;
        }

        // Create penalty record
        Penalties::create([
            'customerID' => $customer->customerID,
            'bookingID' => null, // Customer-level penalty, not booking-specific
            'amount' => $request->penalty_amount,
            'penaltyFees' => $request->penalty_amount,
            'reason' => $finalReason,
            'status' => 'Pending',
            'penaltyStatus' => 'Unpaid',
            'date_imposed' => now(),
            'lateReturnHour' => 0,
            'fuelSurcharge' => 0,
            'mileageSurcharge' => 0,
        ]);

        return back()->with('success', 'Penalty imposed successfully. Customer must pay before making new bookings.');
    }
<<<<<<< HEAD

    // Show penalty history for a customer
    public function penaltyHistory($id)
    {
        $customer = Customer::findOrFail($id);
        
        // Get all penalties for this customer (both booking-based and customer-level)
        $penalties = Penalties::where('customerID', $customer->customerID)
            ->with(['booking.vehicle', 'customer'])
            ->orderBy('date_imposed', 'desc')
            ->paginate(15);
        
        // Calculate statistics
        $totalPenalties = Penalties::where('customerID', $customer->customerID)->count();
        $unpaidPenalties = Penalties::where('customerID', $customer->customerID)
            ->where(function($query) {
                $query->where('status', 'Pending')
                      ->orWhere('penaltyStatus', 'Unpaid');
            })
            ->count();
        $totalAmount = Penalties::where('customerID', $customer->customerID)
            ->where(function($query) {
                $query->where('status', 'Pending')
                      ->orWhere('penaltyStatus', 'Unpaid');
            })
            ->get()
            ->sum(function($penalty) {
                return $penalty->amount ?? ($penalty->penaltyFees + $penalty->fuelSurcharge + $penalty->mileageSurcharge);
            });
        
        return view('staff.customers.penalty-history', compact('customer', 'penalties', 'totalPenalties', 'unpaidPenalties', 'totalAmount'));
    }

    // Show penalty history for a customer
    public function penaltyHistory($id)
    {
        $customer = Customer::findOrFail($id);
        
        // Get all penalties for this customer (both booking-based and customer-level)
        $penalties = Penalties::where('customerID', $customer->customerID)
            ->with(['booking.vehicle', 'customer'])
            ->orderBy('date_imposed', 'desc')
            ->paginate(15);
        
        // Calculate statistics
        $totalPenalties = Penalties::where('customerID', $customer->customerID)->count();
        $unpaidPenalties = Penalties::where('customerID', $customer->customerID)
            ->where(function($query) {
                $query->where('status', 'Pending')
                      ->orWhere('penaltyStatus', 'Unpaid');
            })
            ->count();
        $totalAmount = Penalties::where('customerID', $customer->customerID)
            ->where(function($query) {
                $query->where('status', 'Pending')
                      ->orWhere('penaltyStatus', 'Unpaid');
            })
            ->get()
            ->sum(function($penalty) {
                return $penalty->amount ?? ($penalty->penaltyFees + $penalty->fuelSurcharge + $penalty->mileageSurcharge);
            });
        
        return view('staff.customers.penalty-history', compact('customer', 'penalties', 'totalPenalties', 'unpaidPenalties', 'totalAmount'));
    }

    // Show penalty history for a customer
    public function penaltyHistory($id)
    {
        $customer = Customer::findOrFail($id);
        
        // Get all penalties for this customer (both booking-based and customer-level)
        $penalties = Penalties::where('customerID', $customer->customerID)
            ->with(['booking.vehicle', 'customer'])
            ->orderBy('date_imposed', 'desc')
            ->paginate(15);
        
        // Calculate statistics
        $totalPenalties = Penalties::where('customerID', $customer->customerID)->count();
        $unpaidPenalties = Penalties::where('customerID', $customer->customerID)
            ->where(function($query) {
                $query->where('status', 'Pending')
                      ->orWhere('penaltyStatus', 'Unpaid');
            })
            ->count();
        $totalAmount = Penalties::where('customerID', $customer->customerID)
            ->where(function($query) {
                $query->where('status', 'Pending')
                      ->orWhere('penaltyStatus', 'Unpaid');
            })
            ->get()
            ->sum(function($penalty) {
                return $penalty->amount ?? ($penalty->penaltyFees + $penalty->fuelSurcharge + $penalty->mileageSurcharge);
            });
        
        return view('staff.customers.penalty-history', compact('customer', 'penalties', 'totalPenalties', 'unpaidPenalties', 'totalAmount'));
    }

=======
>>>>>>> origin/testmerge
}