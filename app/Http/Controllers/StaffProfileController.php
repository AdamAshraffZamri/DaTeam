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
        $staff = Auth::guard('staff')->user();
        return view('staff.profile', compact('staff'));
    }

    public function update(Request $request)
    {
        /** @var \App\Models\Staff $staff */
        $staff = Auth::guard('staff')->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('staff')->ignore($staff->staffID, 'staffID')],
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
        $staff->update($data);

        return redirect()->route('staff.profile.edit')
            ->with('success', 'Profile updated successfully.');
    }
}