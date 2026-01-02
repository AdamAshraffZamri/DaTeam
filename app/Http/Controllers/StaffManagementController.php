<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StaffManagementController extends Controller
{
    public function index()
    {
        // Fetch all staff, ordered by latest
        $staffMembers = Staff::orderBy('created_at', 'desc')->paginate(10);
        return view('staff.management.index', compact('staffMembers'));
    }

    public function create()
    {
        return view('staff.management.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:staff,email',
            'phoneNo' => 'nullable|string',
            'role' => 'required|in:admin,staff',
            'password' => 'required|min:6|confirmed',
        ]);

        Staff::create([
            'name' => $request->name,
            'email' => $request->email,
            'phoneNo' => $request->phoneNo,
            'role' => $request->role,
            'password' => Hash::make($request->password),
            'active' => true,
        ]);

        return redirect()->route('staff.management.index')
            ->with('success', 'New staff member added successfully.');
    }

    public function edit($id)
    {
        $staff = Staff::findOrFail($id);
        return view('staff.management.edit', compact('staff'));
    }

    public function update(Request $request, $id)
    {
        $staff = Staff::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('staff')->ignore($staff->staffID, 'staffID')],
            'role' => 'required|in:admin,staff',
            'password' => 'nullable|min:6|confirmed', // Nullable if they don't want to change it
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'phoneNo' => $request->phoneNo,
            'active' => $request->has('active'),
        ];

        // Only update password if provided
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $staff->update($data);

        return redirect()->route('staff.management.index')
            ->with('success', 'Staff details updated successfully.');
    }

    public function destroy($id)
    {
        $staff = Staff::findOrFail($id);

        // Prevent admin from deleting themselves
        if ($staff->staffID === auth()->guard('staff')->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $staff->delete();

        return redirect()->route('staff.management.index')
            ->with('success', 'Staff member removed.');
    }
}