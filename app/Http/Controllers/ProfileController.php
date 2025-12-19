<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    // Show the profile edit form
    public function edit()
    {
        // Get the currently logged-in customer
        $user = Auth::user(); 
        
        // Pass it to the view (view expects $user variable)
        return view('profile.edit', compact('user'));
    }

    // Update the profile
    public function update(Request $request)
    {
        $user = Auth::user();

        // 1. Validate Inputs (Check 'customers' table, not 'users')
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required', 
                'email', 
                'max:255', 
                Rule::unique('customers', 'email')->ignore($user->customerID, 'customerID') // Ignore current user
            ],
            'phone' => 'required|string|max:20',
            'home_address' => 'nullable|string|max:255',
            'college_address' => 'nullable|string|max:255',
            'driving_license_no' => 'nullable|string|max:50',
            
            // Password is optional (only validate if provided)
            'password' => 'nullable|string|min:6', 
        ]);

        // 2. Update Data (Map Form Inputs -> DB Columns)
        $user->fullName = $request->name;
        $user->email = $request->email;
        $user->phoneNo = $request->phone;
        $user->homeAddress = $request->home_address;
        $user->collegeAddress = $request->college_address;
        $user->drivingNo = $request->driving_license_no;

        // 3. Handle Password Update (Only if filled)
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('profile.edit')->with('success', 'Profile updated successfully.');
    }
}