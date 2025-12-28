<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    // --- LOGIN ---
    
    public function showLogin()
    {
        return view('auth.login', ['type' => 'customer']);
    }

    public function showStaffLogin()
    {
        return view('auth.login', ['type' => 'staff']);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $guard = $request->input('login_type') === 'staff' ? 'staff' : 'web';

        if (Auth::guard($guard)->attempt($credentials)) {
            $request->session()->regenerate();

            if ($guard === 'staff') {
                return redirect()->route('staff.dashboard');
            }

            return redirect()->route('book.create');
        }

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
        // 1. Validate
        $request->validate([
            'email' => 'required|email|unique:customers,email',
            'password' => 'required|confirmed|min:6',
            'phone' => 'nullable|string',
        ]);

        // 2. Create Customer
        $customer = Customer::create([
            'fullName' => $request->name ?? explode('@', $request->email)[0], 
            'email' => $request->email,
            'phoneNo' => $request->phone,
            'password' => Hash::make($request->password),
            'accountStat' => 'active',
        ]);

        // 3. Auto Login
        Auth::guard('web')->login($customer);

        // --- CHANGE HERE: Redirect to Profile Edit instead of Home ---
        return redirect()->route('profile.edit')
            ->with('warning', 'Account created! Please complete your profile details below to start booking.');
    }

    // --- PASSWORD RESET ---
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $status = Password::broker('customers')->sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('success', __($status));
        }

        return back()->withErrors(['email' => __($status)]);
    }

    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.passwords.reset')->with(
            ['token' => $token, 'email' => $request->email]
        );
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:6',
        ]);

        $status = Password::broker('customers')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();
                Auth::guard('web')->login($user);
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('home')->with('success', __($status));
        }

        return back()->withErrors(['email' => __($status)]);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        Auth::guard('staff')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}