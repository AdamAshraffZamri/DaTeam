@extends('layouts.app')

@section('content')
<style>
    /* Scoped Styles */
    @keyframes blob {
        0% { transform: translate(0px, 0px) scale(1); }
        33% { transform: translate(30px, -50px) scale(1.1); }
        66% { transform: translate(-20px, 20px) scale(0.9); }
        100% { transform: translate(0px, 0px) scale(1); }
    }
    .animate-blob { animation: blob 7s infinite; }
    
    /* Consistent Glass Card Style */
    .glass-card {
        background: rgba(20, 20, 20, 0.6); /* Darker background for consistency */
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.15);
        box-shadow: 0 10px 40px 0 rgba(0, 0, 0, 0.6);
    }
    
    /* Input Styles */
    .glass-input {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: white;
        transition: all 0.3s ease;
    }
    
    .glass-input:focus {
        background: rgba(255, 255, 255, 0.1);
        border-color: #ea580c;
        outline: none;
        box-shadow: 0 0 0 4px rgba(234, 88, 12, 0.1);
    }
    
    .glass-input::placeholder { color: #9ca3af; }
</style>

{{-- Main Wrapper --}}
<div class="w-full min-h-[calc(100vh-80px)] flex items-center justify-center relative bg-gray-900 overflow-hidden">

    {{-- Background --}}
    <div class="absolute inset-0 z-0">
        <img src="{{ asset('login.png') }}" alt="Background" class="w-full h-full object-cover opacity-60">
        <div class="absolute inset-0 bg-gradient-to-r from-black/60 via-black/35 to-black/65"></div>
    </div>

    {{-- Content Container --}}
    {{-- Adjusted size for mobile (max-w-sm) and desktop (md:max-w-md) --}}
    <div class="relative z-10 w-full max-w-sm md:max-w-md px-4 py-8 md:py-12 mx-auto">
        
        <div class="glass-card w-full rounded-2xl md:rounded-[2rem] p-6 md:p-10 transform transition-all hover:scale-[1.01] duration-500 shadow-2xl">
            
            {{-- Logo --}}
            <div class="flex justify-center mb-6 md:mb-8">
                <div class="p-3 bg-white/10 rounded-2xl backdrop-blur-sm border border-white/20 shadow-lg">
                    <img src="{{ asset('hasta.jpeg') }}" alt="HASTA Logo" class="h-10 md:h-12 w-auto object-contain rounded-lg">
                </div>
            </div>

            {{-- Header --}}
            <div class="text-center mb-6 md:mb-8">
                <h2 class="text-2xl md:text-3xl font-black text-white tracking-tight">Set New Password</h2>
                <p class="text-gray-400 text-xs md:text-sm mt-2 font-medium">Secure your account with a new password.</p>
            </div>

            <form action="{{ route('password.update') }}" method="POST" class="space-y-4 md:space-y-5">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                {{-- Email (Read-Only) --}}
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-gray-300 uppercase tracking-wider ml-1">Email</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-envelope text-gray-500"></i>
                        </div>
                        <input type="email" name="email" value="{{ $email ?? old('email') }}" required readonly
                            class="glass-input w-full rounded-xl py-3 md:py-3.5 pl-11 pr-4 text-sm font-medium opacity-50 cursor-not-allowed">
                    </div>
                    @error('email') <span class="text-red-400 text-xs block mt-1 ml-1 font-medium">{{ $message }}</span> @enderror
                </div>

                {{-- New Password --}}
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-gray-300 uppercase tracking-wider ml-1">New Password</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-500 group-focus-within:text-orange-500 transition-colors"></i>
                        </div>
                        
                        {{-- Input with ID and right padding --}}
                        <input type="password" name="password" id="new_password" required placeholder="New secure password" 
                            class="glass-input w-full rounded-xl py-3 md:py-3.5 pl-11 pr-12 text-sm font-medium focus:ring-0">

                        {{-- Eye Toggle Button --}}
                        <button type="button" onclick="togglePassword('new_password', this)" 
                                class="absolute inset-y-0 right-0 pr-4 flex items-center focus:outline-none group/eye"
                                title="Toggle Password">
                            {{-- Closed Eye --}}
                            <svg class="eye-closed w-5 h-5 text-gray-500 group-hover/eye:text-white transition-colors" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                            {{-- Open Eye --}}
                            <svg class="eye-open hidden w-5 h-5 text-orange-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </button>
                    </div>
                    @error('password') <span class="text-red-400 text-xs block mt-1 ml-1 font-medium">{{ $message }}</span> @enderror
                </div>

                {{-- Confirm Password --}}
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-gray-300 uppercase tracking-wider ml-1">Confirm Password</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-500 group-focus-within:text-orange-500 transition-colors"></i>
                        </div>
                        
                        {{-- Input with ID and right padding --}}
                        <input type="password" name="password_confirmation" id="confirm_password" required placeholder="Repeat password" 
                            class="glass-input w-full rounded-xl py-3 md:py-3.5 pl-11 pr-12 text-sm font-medium focus:ring-0">

                        {{-- Eye Toggle Button --}}
                        <button type="button" onclick="togglePassword('confirm_password', this)" 
                                class="absolute inset-y-0 right-0 pr-4 flex items-center focus:outline-none group/eye"
                                title="Toggle Password">
                            {{-- Closed Eye --}}
                            <svg class="eye-closed w-5 h-5 text-gray-500 group-hover/eye:text-white transition-colors" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                            {{-- Open Eye --}}
                            <svg class="eye-open hidden w-5 h-5 text-orange-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-500 hover:to-red-500 text-white font-bold py-3 md:py-4 rounded-xl shadow-lg shadow-orange-900/40 transition-all transform hover:-translate-y-0.5 active:translate-y-0 mt-2 text-sm md:text-base">
                    Update Password
                </button>
            </form>
        </div>
    </div>
</div>

{{-- Toggle Password Script --}}
<script>
    function togglePassword(inputId, btn) {
        const input = document.getElementById(inputId);
        const iconOpen = btn.querySelector('.eye-open');
        const iconClosed = btn.querySelector('.eye-closed');
        
        if (input.type === "password") {
            // SHOW PASSWORD
            input.type = "text";
            iconOpen.classList.remove('hidden');
            iconClosed.classList.add('hidden');
        } else {
            // HIDE PASSWORD
            input.type = "password";
            iconOpen.classList.add('hidden');
            iconClosed.classList.remove('hidden');
        }
    }
</script>
@endsection