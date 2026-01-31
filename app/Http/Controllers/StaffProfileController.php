<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use App\Models\Staff;

/**
 * StaffProfileController
 * 
 * Handles staff member profile management including viewing and updating staff information.
 * Uses 'staff' guard for authentication to distinguish from customer authentication.
 * 
 * Column Size Limits (Database Optimization):
 * - name: max 100 characters
 * - email: max 100 characters
 * - phoneNo: max 20 characters
 * - password: hashed (255 characters stored)
 * 
 * Password Requirements:
 * - Minimum: 8 characters
 * - Confirmation required on update
 */
class StaffProfileController extends Controller
{
    /**
     * edit()
     * 
     * Displays the staff profile editing form.
     * Authenticates via 'staff' guard to ensure only staff can access.
     * 
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        // Get the currently authenticated staff member using staff guard
        $user = Auth::guard('staff')->user();
        
        return view('staff.profile', compact('user'));
    }

    /**
     * update()
     * 
     * Updates staff profile information with proper validation.
     * Password update is optional - only hashed if provided.
     * 
     * Validation Rules:
     * - name: Required, string, max 100 chars
     * - email: Required, valid email format, max 100 chars, unique (except own)
     * - phoneNo: Optional, string, max 20 chars
     * - password: Optional, minimum 8 chars, must be confirmed
     * 
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        /** @var \App\Models\Staff $user */
        $user = Auth::guard('staff')->user();

        // Validate input with database column size constraints
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => ['required', 'email', 'max:100', Rule::unique('staff')->ignore($user->staffID, 'staffID')],
            'phoneNo' => 'nullable|string|max:20',
            'password' => 'nullable|min:8|confirmed',
        ]);

        // Prepare data array for update
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phoneNo' => $request->phoneNo,
        ];

        // Only update password if the field is filled (to support optional updates)
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        // Perform the update on the authenticated staff model
        $user->update($data);

        return redirect()->route('staff.profile.edit')
            ->with('success', 'Profile updated successfully.');
    }
}