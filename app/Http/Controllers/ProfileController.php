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
            'name' => ['required', 'string', 'max:255'],
            
            // FIX: Check 'customers' table, ignore current 'customerID'
            'email' => [
                'required', 
                'email', 
                'max:255', 
                Rule::unique('customers', 'email')->ignore($user->customerID, 'customerID')
            ],

            'home_address' => ['nullable', 'string', 'max:500'],
            'student_staff_id' => ['nullable', 'string', 'max:50'],
            'ic_passport' => ['nullable', 'string', 'max:50'],
            'college_address' => ['nullable', 'string', 'max:500'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'dob' => ['nullable', 'date'],
            'phone' => ['nullable', 'string', 'max:20'],
            'driving_license_no' => ['nullable', 'string', 'max:50'],
            'emergency_contact_no' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:8'],
            'faculty' => ['nullable', 'string', 'max:255'],

            // Image Validation
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

        // 3. Update Database Fields (Mapping Form Name -> DB Column)
        // Since your form uses snake_case but DB uses camelCase, we map them manually:
        
        $user->fullName = $request->name;       // Maps 'name' -> 'fullName'
        $user->email = $request->email;
        $user->homeAddress = $request->home_address; // Maps 'home_address' -> 'homeAddress'
        $user->stustaffID = $request->student_staff_id;
        $user->ic_passport = $request->ic_passport;
        $user->collegeAddress = $request->college_address;
        $user->nationality = $request->nationality;
        $user->dob = $request->dob;
        $user->phoneNo = $request->phone;       // Maps 'phone' -> 'phoneNo'
        $user->drivingNo = $request->driving_license_no; // Maps 'driving_license_no' -> 'drivingNo'
        $user->emergency_contact_no = $request->emergency_contact_no;
        $user->faculty = $request->faculty;

        // 4. Handle Password Update
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        // 5. Save changes
        $user->save();

        return redirect()->route('profile.edit')->with('status', 'Profile updated successfully!');
    }
}