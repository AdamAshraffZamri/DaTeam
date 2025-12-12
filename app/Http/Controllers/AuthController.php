<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // --- LOGIN ---
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // Validate inputs
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Attempt to log the user in
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // Optional: Redirect based on role (Customer vs Staff)
            if (auth()->user()->role === 'staff') {
                return redirect()->route('home'); // or a staff dashboard
            }

            return redirect()->intended(route('home'));
        }

        // Login failed
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    // --- REGISTRATION ---
    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:6', // expects 'password_confirmation' field in form
            'phone' => 'nullable|string',
            'role' => 'in:customer,staff', // Validates the toggle from the UI
            
            // Custom Security Question Fields
            'security_question' => 'required|string',
            'security_answer' => 'required|string',
        ]);

        User::create([
            'name' => 'New User', // Placeholder name until they update Profile
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => $request->role ?? 'customer', // Default to customer
            
            // Save Security Details
            'security_question' => $request->security_question,
            'security_answer' => $request->security_answer, 
        ]);

        // Auto login after reg or redirect to login
        return redirect()->route('login')->with('success', 'Account created successfully! Please login.');
    }

    // --- CUSTOM PASSWORD RESET (Security Question) ---
    public function showReset()
    {
        return view('auth.passwords.reset');
    }

    public function resetPassword(Request $request)
    {
        // 1. Validate the form data
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'security_question' => 'required',
            'security_answer' => 'required',
            'password' => 'required|confirmed|min:6'
        ]);

        // 2. Find the user
        $user = User::where('email', $request->email)->first();

        // 3. Verify the Security Answer (Case-insensitive check is often user-friendly)
        if (strtolower($user->security_answer) !== strtolower($request->security_answer)) {
             return back()->withErrors(['security_answer' => 'The security answer is incorrect.']);
        }

        // 4. Update the Password
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return redirect()->route('login')->with('success', 'Password has been reset successfully.');
    }

    // --- LOGOUT ---
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}