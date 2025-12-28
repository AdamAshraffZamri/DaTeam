<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.edit', [
            'user' => auth()->user()
        ]);
    }

    // FORM 1: Update Profile Info ONLY
    public function update(Request $request)
    {
        $user = auth()->user();

        // 1. Validate Profile Fields (Removed Password Validation)
        $validated = $request->validate([
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
            
            'avatar' => ['nullable', 'image', 'max:2048'], 
            'student_card_image' => ['nullable', 'image', 'max:2048'],
            'ic_passport_image' => ['nullable', 'image', 'max:2048'],
            'driving_license_image' => ['nullable', 'image', 'max:2048'],
        ]);

        // 2. Handle Images
        $fileFields = ['avatar', 'student_card_image', 'ic_passport_image', 'driving_license_image'];
        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                if ($user->$field && Storage::disk('public')->exists($user->$field)) {
                    Storage::disk('public')->delete($user->$field);
                }
                $user->$field = $request->file($field)->store('uploads', 'public');
            }
        }

        // 3. Update Info
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
        $user->bankName = $request->bank_name;
        $user->bankAccountNo = $request->bank_account_no;

        $user->save();

        return redirect()->route('profile.edit')->with('status', 'Profile information updated successfully!');
    }

    // FORM 2: Update Password ONLY
    public function updatePassword(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
             throw ValidationException::withMessages([
                'current_password' => ['The provided password does not match your current password.'],
            ]);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->route('profile.edit')->with('status', 'Password updated successfully!');
    }
}