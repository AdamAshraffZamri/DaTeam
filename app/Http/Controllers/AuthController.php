<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

/**
 * AuthController
 * 
 * Handles authentication operations for both customers and staff members.
 * Supports dual-guard system (web for customers, staff for staff members).
 * 
 * Features:
 * - Customer/Staff login with guard differentiation
 * - Customer registration with auto-login
 * - Password reset functionality
 * - Account logout
 * 
 * Database Column Constraints:
 * - email: max 100 characters
 * - password: hashed (255 characters stored)
 * 
 * Password Policy:
 * - Minimum: 1 character, Maximum: 8 characters
 * - Confirmation required on registration and reset
 */
class AuthController extends Controller
{
    /**
     * ============================================
     * LOGIN SECTION
     * ============================================
     */
    
    /**
     * showLogin()
     * 
     * Displays the customer login form.
     * 
     * @return \Illuminate\View\View
     */
    public function showLogin()
    {
        return view('auth.login', ['type' => 'customer']);
    }

    /**
     * showStaffLogin()
     * 
     * Displays the staff login form.
     * 
     * @return \Illuminate\View\View
     */
    public function showStaffLogin()
    {
        return view('auth.login', ['type' => 'staff']);
    }

    /**
     * login()
     * 
     * Authenticates user credentials and establishes session.
     * Supports both customer and staff authentication via guard selection.
     * 
     * Guard Selection Logic:
     * - If login_type == 'staff' → uses 'staff' guard
     * - Otherwise → uses 'web' guard (customer)
     * 
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Determine which guard to use based on login type
        $guard = $request->input('login_type') === 'staff' ? 'staff' : 'web';

        // Attempt authentication with selected guard
        if (Auth::guard($guard)->attempt($credentials)) {
            // Regenerate session ID to prevent session fixation attacks
            $request->session()->regenerate();

            // Redirect to appropriate dashboard based on guard type
            if ($guard === 'staff') {
                return redirect()->route('staff.dashboard');
            }

            return redirect()->route('book.create');
        }

        // Authentication failed - return error
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * ============================================
     * CUSTOMER REGISTRATION SECTION
     * ============================================
     */

    /**
     * showRegister()
     * 
     * Displays the customer registration form.
     * 
     * @return \Illuminate\View\View
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * register()
     * 
     * Creates a new customer account and auto-logs them in.
     * Redirects to profile completion page.
     * 
     * Account Creation Flow:
     * 1. Validate email (unique), password (confirmed, min 8), phone (optional)
     * 2. Create customer with basic info
     * 3. Auto-login to web guard
     * 4. Redirect to profile edit (required before booking)
     * 
     * Default Values:
     * - fullName: Uses email prefix if name not provided
     * - accountStat: 'unverified' (requires staff approval)
     * - blacklisted: false
     * 
     * Validation Rules:
     * - email: Required, valid format, unique across customers
     * - password: Required, min 8 chars, confirmed
     * - phone: Optional, string
     * 
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(Request $request)
    {
        // Validate input
        $request->validate([
            'email' => 'required|email|unique:customers,email',
            'password' => 'required|confirmed|min:8',
            'phone' => 'nullable|string',
        ]);

        // Create new customer record with hashed password
        $customer = Customer::create([
            'fullName' => $request->name ?? explode('@', $request->email)[0], 
            'email' => $request->email,
            'phoneNo' => $request->phone,
            'password' => Hash::make($request->password),
            'accountStat' => 'unverified', // Requires staff verification
            'blacklisted' => false,
        ]);

        // Auto-login with web guard
        Auth::guard('web')->login($customer);

        // Redirect to profile completion (mandatory before booking)
        return redirect()->route('profile.edit')
            ->with('warning', 'Account created! Please complete your profile details below to start booking.');
    }

    /**
     * ============================================
     * PASSWORD RESET SECTION
     * ============================================
     */

    /**
     * showLinkRequestForm()
     * 
     * Displays the password reset request form.
     * User enters email to receive reset link.
     * 
     * @return \Illuminate\View\View
     */
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }

    /**
     * sendResetLinkEmail()
     * 
     * Sends password reset link to customer's email.
     * Uses Laravel's built-in password reset broker.
     * 
     * @param  \Illuminate\Http\Request $request Must contain 'email'
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendResetLinkEmail(Request $request)
    {
        // Validate email format
        $request->validate(['email' => 'required|email']);
        // Send reset link using customers password broker
        $status = Password::broker('customers')->sendResetLink($request->only('email'));

        // Check if reset link was sent successfully
        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('success', __($status));
        }

        // Return error if email not found or other issue
        return back()->withErrors(['email' => __($status)]);
    }

    /**
     * showResetForm()
     * 
     * Displays the password reset form with token and email pre-filled.
     * Accessed via link in password reset email.
     * 
     * @param  \Illuminate\Http\Request $request
     * @param  string|null $token Reset token from URL
     * @return \Illuminate\View\View
     */
    public function showResetForm(Request $request, $token = null)
    {
        // Display form with token and email (email passed in URL)
        return view('auth.passwords.reset')->with(
            ['token' => $token, 'email' => $request->email]
        );
    }

    /**
     * reset()
     * 
     * Processes password reset with token validation.
     * Verifies reset token is valid before updating password.
     * 
     * Security:
     * - Validates reset token (prevents invalid/expired tokens)
     * - Hashes new password using Laravel's hash facade
     * - Auto-logs in user after password change
     * - Generates new remember token for security
     * 
     * Validation Rules:
     * - token: Required, must be valid reset token
     * - email: Required, must match customer record
     * - password: Required, min 8 chars, confirmed
     * 
     * Process:
     * 1. Validate all input
     * 2. Verify token using password broker
     * 3. If valid: Hash password, update user, auto-login
     * 4. Return success or error
     * 
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reset(Request $request)
    {
        // Validate reset request
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        // Attempt password reset using broker (handles token validation)
        $status = Password::broker('customers')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                // Update user password and regenerate remember token
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                // Save updated user to database
                $user->save();
                // Auto-login with web guard
                Auth::guard('web')->login($user);
            }
        );

        // Check if password reset was successful
        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('home')->with('success', __($status));
        }

        // Return error if reset failed (invalid token, expired, etc.)
        return back()->withErrors(['email' => __($status)]);
    }

    /**
     * logout()
     * 
     * Logs out the currently authenticated user.
     * Handles logout for both customer and staff guards.
     * Destroys session data for security.
     * 
     * Security:
     * - Invalidates entire session (prevents session reuse)
     * - Regenerates CSRF token
     * - Logs out from both guards (customer and staff)
     * 
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        // Logout from both guards to ensure complete session termination
        Auth::guard('web')->logout();
        Auth::guard('staff')->logout();

        // Invalidate all session data
        $request->session()->invalidate();
        // Regenerate CSRF token for next session
        $request->session()->regenerateToken();

        // Redirect to home page
        return redirect('/');
    }
}