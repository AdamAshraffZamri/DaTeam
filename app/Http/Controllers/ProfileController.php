<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    // Show the profile form
    public function edit()
    {
        return view('profile.edit', [
            'user' => auth()->user()
        ]);
    }

    // Handle the update request
    public function update(Request $request)
    {
        $user = auth()->user();

        // 1. Validate the incoming data
        $validated = $request->validate([
            // ALL FIELDS SET TO REQUIRED
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('customers', 'email')->ignore($user->customerID, 'customerID')],
            
            'phone' => ['required', 'string', 'max:20'],
            'emergency_contact_no' => ['required', 'string', 'max:20'],
            'home_address' => ['required', 'string', 'max:500'],
            'college_address' => ['required', 'string', 'max:500'],
            
            'student_staff_id' => ['required', 'string', 'max:50'],
            'ic_passport' => ['required', 'string', 'max:50'],
            'driving_license_no' => ['required', 'string', 'max:50'],
            
            'nationality' => ['required', 'string', 'max:100'],
            'dob' => ['required', 'date'],
            'faculty' => ['required', 'string', 'max:255'],
            
            'bank_name' => ['required', 'string', 'max:100'],
            'bank_account_no' => ['required', 'string', 'max:50'],

            // Images can remain nullable if you don't want to force re-upload on every edit
            'avatar' => ['nullable', 'image', 'max:2048'], 
            'student_card_image' => ['nullable', 'image', 'max:2048'],
            'ic_passport_image' => ['nullable', 'image', 'max:2048'],
            'driving_license_image' => ['nullable', 'image', 'max:2048'],
        ]);

        // 2. Handle Image Uploads
        $fileFields = ['avatar', 'student_card_image', 'ic_passport_image', 'driving_license_image'];

        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                // Delete old file if exists
                if ($user->$field && Storage::disk('public')->exists($user->$field)) {
                    Storage::disk('public')->delete($user->$field);
                }
                // Store new file
                $path = $request->file($field)->store('uploads', 'public');
                $user->$field = $path;
            }
        }

        // 3. Update Database Fields
        $user->fullName = $request->name;       
        $user->email = $request->email;
        $user->homeAddress = $request->home_address;
        $user->stustaffID = $request->student_staff_id;
        $user->ic_passport = $request->ic_passport;
        $user->collegeAddress = $request->college_address;
        $user->nationality = $request->nationality;
        $user->dob = $request->dob;
        $user->phoneNo = $request->phone;       
        $user->drivingNo = $request->driving_license_no; 
        $user->emergency_contact_no = $request->emergency_contact_no;
        $user->faculty = $request->faculty;

        // Save New Bank Details
        $user->bankName = $request->bank_name;
        $user->bankAccountNo = $request->bank_account_no;

        // 4. Handle Password Update
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        // 5. Save changes
        $user->save();

        // Redirect back to profile view (edit) instead of show
        return redirect()->route('profile.edit')->with('status', 'Profile updated successfully!');
    }
}