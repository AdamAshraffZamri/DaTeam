<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    // --- LOGIN ---
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // 1. Validate Inputs
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 2. Determine Guard (Customer vs Staff)
        // This comes from the hidden input <input type="hidden" name="login_type" ...>
        $guard = $request->input('login_type') === 'staff' ? 'staff' : 'web';

        // 3. Attempt Login
        if (Auth::guard($guard)->attempt($credentials)) {
            $request->session()->regenerate();

            // Redirect based on role
            if ($guard === 'staff') {
                return redirect()->route('staff.dashboard');
            }

            return redirect()->route('book.create'); // Redirect Customer
        }

        // 4. Login Failed
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    // --- REGISTRATION (Customers Only) ---
    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        // 1. Validate (Check 'customers' table for unique email)
        $request->validate([
            'email' => 'required|email|unique:customers,email',
            'password' => 'required|confirmed|min:6',
            'phone' => 'nullable|string',
        ]);

        // 2. Create Customer (Map form inputs to DB Columns)
        $customer = Customer::create([
            // Use email as name if name input is missing in form, or add name input to form
            'fullName' => $request->name ?? explode('@', $request->email)[0], 
            'email' => $request->email,
            'phoneNo' => $request->phone, // Map 'phone' to 'phoneNo'
            'password' => Hash::make($request->password),
            
            // Default Values required by DB
            'accountStat' => 'active',
            'drivingNo' => 'PENDING-' . time() . rand(100,999), // Placeholder needed for unique constraint
        ]);

        // 3. Auto Login
        Auth::guard('web')->login($customer);

        return redirect()->route('home')->with('success', 'Account created successfully!');
    }

    // --- PASSWORD RESET (EMAIL FLOW) ---

    // 1. Show the "Enter your email" form
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }

    // 2. Send the reset link to the email
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // We use the 'customers' broker defined in config/auth.php
        $status = Password::broker('customers')->sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('success', __($status));
        }

        return back()->withErrors(['email' => __($status)]);
    }

    // 3. Show the "Enter new password" form (User clicks link in email)
    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.passwords.reset')->with(
            ['token' => $token, 'email' => $request->email]
        );
    }

    // 4. Reset the password
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:6',
        ]);

        // Broker handles token verification automatically
        $status = Password::broker('customers')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                // Auto login after reset
                Auth::guard('web')->login($user);
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('home')->with('success', __($status));
        }

        return back()->withErrors(['email' => __($status)]);
    }

    // --- LOGOUT ---
    public function logout(Request $request)
    {
        // Logout both guards just in case
        Auth::guard('web')->logout();
        Auth::guard('staff')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}