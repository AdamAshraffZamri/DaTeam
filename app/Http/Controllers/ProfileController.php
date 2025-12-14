<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    // Show the Edit Profile Form
    public function edit()
    {
        return view('profile.edit', ['user' => Auth::user()]);
    }

    // Update the Profile Data
    public function update(Request $request)
    {
        $user = Auth::user();

        // Validate all fields matching your screenshot
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'student_staff_id' => 'nullable|string|max:50',
            'dob' => 'nullable|date',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'ic_passport' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'home_address' => 'nullable|string',
            'college_address' => 'nullable|string',
            'driving_license_no' => 'nullable|string|max:50',
            'emergency_contact_no' => 'nullable|string|max:20',
            'nationality' => 'nullable|string',
            // 'faculty' can be added here if you add it to your database
        ]);

        // Save the updates
        $user->update($validated);

        // Optional: Update Password if provided
        if ($request->filled('password')) {
            $request->validate(['password' => 'confirmed|min:6']);
            $user->update(['password' => Hash::make($request->password)]);
        }

        return redirect()->route('profile.edit')->with('success', 'Profile updated successfully.');
    }
}