<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use App\Models\Staff;

class StaffProfileController extends Controller
{
    public function edit()
    {
        // Get the currently authenticated staff member
        // FIX: Renamed variable from $staff to $user to match the view's expectation
        $user = Auth::guard('staff')->user();
        
        return view('staff.profile', compact('user'));
    }

    public function update(Request $request)
    {
        /** @var \App\Models\Staff $user */
        $user = Auth::guard('staff')->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('staff')->ignore($user->staffID, 'staffID')],
            'phoneNo' => 'nullable|string|max:20',
            'password' => 'nullable|min:6|confirmed',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phoneNo' => $request->phoneNo,
        ];

        // Only update password if the field is filled
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        // Perform the update on the model
        $user->update($data);

        return redirect()->route('staff.profile.edit')
            ->with('success', 'Profile updated successfully.');
    }
}