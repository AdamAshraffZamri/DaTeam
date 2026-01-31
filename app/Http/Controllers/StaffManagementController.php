<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * StaffManagementController
 * 
 * CRUD operations for staff member management (admin only).
 * Handles creation, reading, updating, and deletion of staff accounts.
 * 
 * Database Column Constraints (Optimized for Performance):
 * - name: max 100 characters
 * - email: max 100 characters (unique)
 * - phoneNo: max 20 characters
 * - role: max 50 characters (enum: admin, staff)
 * - password: hashed (255 characters stored)
 * 
 * Password Requirements:
 * - On Creation: Required, minimum 8 characters, confirmation required
 * - On Update: Optional (nullable), minimum 8 characters if provided
 * 
 * Roles Available:
 * - admin: Full administrative access
 * - staff: Limited staff access
 */
class StaffManagementController extends Controller
{
    /**
     * index()
     * 
     * Displays paginated list of all staff members.
     * Ordered by latest created first (descending).
     * 
     * @param  void
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Fetch all staff, ordered by creation date (newest first)
        // Paginate at 10 records per page
        $staffMembers = Staff::orderBy('created_at', 'desc')->paginate(10);
        return view('staff.management.index', compact('staffMembers'));
    }

    /**
     * create()
     * 
     * Displays the staff creation form view.
     * 
     * @param  void
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('staff.management.create');
    }

    /**
     * store()
     * 
     * Creates a new staff member with validated input.
     * Password is hashed before storage for security.
     * All newly created staff are set to active by default.
     * 
     * Validation Rules:
     * - name: Required, string, max 100 chars
     * - email: Required, valid email, max 100 chars, globally unique
     * - phoneNo: Optional, string, max 20 chars
     * - role: Required, must be 'admin' or 'staff'
     * - password: Required, min 8 chars, must be confirmed
     * 
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validate all input against database constraints
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100|unique:staff,email',
            'phoneNo' => 'nullable|string|max:20',
            'role' => 'required|in:admin,staff',
            'password' => 'required|min:8|confirmed',
        ]);

        // Create new staff record with hashed password
        Staff::create([
            'name' => $request->name,
            'email' => $request->email,
            'phoneNo' => $request->phoneNo,
            'role' => $request->role,
            'password' => Hash::make($request->password),
            'active' => true, // All new staff start as active
        ]);

        return redirect()->route('staff.management.index')
            ->with('success', 'New staff member added successfully.');
    }

    /**
     * edit()
     * 
     * Displays the staff editing form for a specific staff member.
     * 
     * @param  int $id Staff ID (staffID)
     * @return \Illuminate\View\View
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function edit($id)
    {
        // Fetch staff by ID or fail with 404
        $staff = Staff::findOrFail($id);
        return view('staff.management.edit', compact('staff'));
    }

    public function update(Request $request, $id)
    {
        // Fetch staff member to update
        $staff = Staff::findOrFail($id);

        // Validate input with database column constraints
        // Email must be unique across all staff except the current one
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => ['required', 'email', 'max:100', Rule::unique('staff')->ignore($staff->staffID, 'staffID')],
            'role' => 'required|in:admin,staff',
            'password' => 'nullable|min:8|confirmed', // Password is optional for updates
        ]);

        // Prepare data for update
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'phoneNo' => $request->phoneNo,
            'active' => $request->has('active'), // Checkbox: active or inactive
        ];

        // Only update password if provided by user (to support updates without password change)
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        // Perform update
        $staff->update($data);

        return redirect()->route('staff.management.index')
            ->with('success', 'Staff details updated successfully.');
    }

    /**
     * destroy()
     * 
     * Deletes a staff member from the system.
     * Includes safety check to prevent deletion of currently authenticated admin.
     * 
     * Security:
     * - Prevents admin from deleting their own account (circular deletion protection)
     * - Uses staffID comparison against authenticated guard
     * 
     * @param  int $id Staff ID (staffID)
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function destroy($id)
    {
        // Fetch staff member to delete
        $staff = Staff::findOrFail($id);

        // Prevent admin from deleting themselves - critical security measure
        if ($staff->staffID === auth()->guard('staff')->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        // Delete the staff record from database
        $staff->delete();

        return redirect()->route('staff.management.index')
            ->with('success', 'Staff member removed.');
    }
}