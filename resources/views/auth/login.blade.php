@extends('layouts.app')

@section('content')
<style>
    /* Scoped Styles for Login Page */

    /* Animation definition for background blobs (if used in future/details) */
    @keyframes blob {
        0% { transform: translate(0px, 0px) scale(1); }
        33% { transform: translate(30px, -50px) scale(1.1); }
        66% { transform: translate(-20px, 20px) scale(0.9); }
        100% { transform: translate(0px, 0px) scale(1); }
    }
    .animate-blob { animation: blob 7s infinite; }
    .animation-delay-2000 { animation-delay: 2s; }
    .animation-delay-4000 { animation-delay: 4s; }
    
    /* Glass Card Style 
       - Increased background opacity (0.85) to make it stand out from the background image.
       - Stronger shadow for depth.
    */
    .glass-card {
        background: rgba(20, 20, 20, 0.6); /* Darker background for better contrast */
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.15); /* Slightly stronger border */
        box-shadow: 0 10px 40px 0 rgba(0, 0, 0, 0.6); /* Deeper shadow */
    }
    
    /* Input Field Styles */
    .glass-input {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: white;
        transition: all 0.3s ease;
    }
    
    /* Focus State for Inputs */
    .glass-input:focus {
        background: rgba(255, 255, 255, 0.1);
        border-color: #ea580c;
        outline: none;
        box-shadow: 0 0 0 4px rgba(234, 88, 12, 0.1);
    }
    
    .glass-input::placeholder { color: #9ca3af; } /* Slightly lighter placeholder for readability */
</style>

{{-- Main Wrapper --}}
{{-- min-h-[calc(100vh-80px)] calculates height based on navbar height to center content vertically --}}
<div class="w-full min-h-[calc(100vh-80px)] flex items-center justify-center relative bg-gray-900 overflow-hidden">

    {{-- Abstract Background Image --}}
    <div class="absolute inset-0 z-0">
        <img src="{{ asset('login.png') }}" 
             alt="Background" 
             class="w-full h-full object-cover opacity-60">
        {{-- Gradient Overlay to ensure text readability --}}
        <div class="absolute inset-0 bg-gradient-to-r from-black/60 via-black/35 to-black/65"></div>
    </div>

    {{-- Content Container --}}
    {{-- Adjusted padding (px-4) and sizing (max-w-sm) for mobile optimization --}}
    <div class="relative z-10 w-full max-w-sm md:max-w-md px-4 py-8 md:py-12 mx-auto">
        
        {{-- Login Card --}}
        {{-- 
             Mobile Adjustments: 
             1. rounded-2xl (smaller radius on mobile) -> md:rounded-[2rem] 
             2. p-6 (less padding on mobile) -> md:p-10
        --}}
        <div class="glass-card w-full rounded-2xl md:rounded-[2rem] p-6 md:p-10 transform transition-all hover:scale-[1.01] duration-500 shadow-2xl">
            
            {{-- Logo Section --}}
            <div class="flex justify-center mb-6 md:mb-8">
                <div class="p-3 bg-white/10 rounded-2xl backdrop-blur-sm border border-white/20 shadow-lg">
                    {{-- Logo size: h-10 on mobile, h-12 on desktop --}}
                    <img src="{{ asset('hasta.jpeg') }}" alt="HASTA Logo" class="h-10 md:h-12 w-auto object-contain rounded-lg">
                </div>
            </div>

            {{-- Header Text --}}
            <div class="text-center mb-6 md:mb-8">
                {{-- Heading size: text-2xl on mobile, text-3xl on desktop --}}
                <h2 class="text-2xl md:text-3xl font-black text-white tracking-tight">
                    @if(($type ?? '') === 'staff') Staff Portal @else Welcome Back! @endif
                </h2>
                <p class="text-gray-400 text-xs md:text-sm mt-2 font-medium">Enter your credentials to access your account</p>
            </div>

            {{-- Login Form --}}
            <form action="{{ route('login') }}" method="POST" class="space-y-4 md:space-y-5">
                @csrf
                <input type="hidden" name="login_type" value="{{ $type ?? 'customer' }}">

                {{-- Email Input Group --}}
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-gray-300 uppercase tracking-wider ml-1">Email</label>
                    <div class="relative group">
                        {{-- Icon --}}
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-envelope text-gray-500 group-focus-within:text-orange-500 transition-colors"></i>
                        </div>
                        {{-- Input --}}
                        <input type="email" name="email" required placeholder="hello@example.com" 
                            class="glass-input w-full rounded-xl py-3 md:py-3.5 pl-11 pr-4 text-sm font-medium focus:ring-0">
                    </div>
                </div>
                
                {{-- Password Input Group --}}
                <div class="space-y-1.5">
                    <div class="flex justify-between items-center ml-1">
                        <label class="text-xs font-bold text-gray-300 uppercase tracking-wider">Password</label>
                        @if(($type ?? '') === 'customer')
                            <a href="{{ route('password.request') }}" class="text-xs text-orange-400 hover:text-orange-300 transition">Forgot?</a>
                        @endif
                    </div>
                    <div class="relative group">
                        {{-- Lock Icon (Left) --}}
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-500 group-focus-within:text-orange-500 transition-colors"></i>
                        </div>
                        
                        {{-- Input Field --}}
                        {{-- Added id="login_password" and changed pr-4 to pr-12 --}}
                        <input type="password" name="password" id="login_password" required placeholder="••••••••" 
                            class="glass-input w-full rounded-xl py-3 md:py-3.5 pl-11 pr-12 text-sm font-medium focus:ring-0">
                        
                        {{-- Eye Icon (Right) --}}
                        <button type="button" onclick="togglePassword('login_password', this)" 
                                class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-500 hover:text-orange-500 transition-colors focus:outline-none">
                            <i class="fas fa-eye-slash"></i>
                        </button>
                    </div>
                </div>

                {{-- Submit Button --}}
                <button type="submit" class="w-full bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-500 hover:to-red-500 text-white font-bold py-3 md:py-4 rounded-xl shadow-lg shadow-orange-900/40 transition-all transform hover:-translate-y-0.5 active:translate-y-0 mt-2 text-sm md:text-base">
                    Log In
                </button>
            </form>

            {{-- Footer Links --}}
            <div class="mt-6 md:mt-8 pt-6 border-t border-white/10 text-center space-y-4">
                @if(($type ?? '') === 'customer')
                    <p class="text-gray-400 text-xs md:text-sm">
                        Don't have an account? <a href="{{ route('register') }}" class="text-white font-bold hover:text-orange-400 transition hover:underline">Sign up</a>
                    </p>
                    <a href="{{ route('staff.login') }}" class="inline-flex items-center text-xs text-gray-500 hover:text-gray-300 transition mt-2">
                        <i class="fas fa-user-shield mr-1.5"></i> Staff Access
                    </a>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center text-xs text-gray-500 hover:text-gray-300 transition">
                        <i class="fas fa-arrow-left mr-1.5"></i> Back to Customer Login
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    function togglePassword(inputId, button) {
        const input = document.getElementById(inputId);
        const icon = button.querySelector('i');
        
        if (input.type === "password") {
            // SHOW PASSWORD
            input.type = "text";
            icon.classList.remove('fa-eye-slash'); // Remove closed eye
            icon.classList.add('fa-eye');          // Add open eye
            icon.classList.add('text-orange-500'); // Highlight active state
        } else {
            // HIDE PASSWORD
            input.type = "password";
            icon.classList.remove('fa-eye');       // Remove open eye
            icon.classList.add('fa-eye-slash');    // Add closed eye
            icon.classList.remove('text-orange-500');
        }
    }
</script>

@endsection