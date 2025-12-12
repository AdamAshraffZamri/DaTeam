<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.edit', ['user' => Auth::user()]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'student_staff_id' => 'nullable|string|max:50',
            'ic_passport' => 'nullable|string|max:20',
            'dob' => 'nullable|date',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
            'home_address' => 'nullable|string',
            'college_address' => 'nullable|string',
            'driving_license_no' => 'nullable|string|max:50',
            'emergency_contact_no' => 'nullable|string|max:20',
            'nationality' => 'nullable|string',
        ]);

        $user->update($validated);

        return redirect()->route('profile.edit')->with('success', 'Profile updated successfully.');
    }
}