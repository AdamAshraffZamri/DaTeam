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
    
    /* Glass Card Style */
    .glass-card {
        background: rgba(20, 20, 20, 0.6); /* Set to 0.6 opacity as requested */
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
    {{-- Responsive widths: max-w-sm on mobile, max-w-md on desktop --}}
    <div class="relative z-10 w-full max-w-sm md:max-w-md px-4 py-8 md:py-12 mx-auto">
        
        {{-- Card --}}
        {{-- Responsive border radius and padding --}}
        <div class="glass-card w-full rounded-2xl md:rounded-[2rem] p-6 md:p-10 transform transition-all hover:scale-[1.01] duration-500 shadow-2xl">
            
            {{-- Logo --}}
            <div class="flex justify-center mb-6 md:mb-8">
                <div class="p-3 bg-white/10 rounded-2xl backdrop-blur-sm border border-white/20 shadow-lg">
                    <img src="{{ asset('hasta.jpeg') }}" alt="HASTA Logo" class="h-10 md:h-12 w-auto object-contain rounded-lg">
                </div>
            </div>

            {{-- Header --}}
            <div class="text-center mb-6 md:mb-8">
                <h2 class="text-2xl md:text-3xl font-black text-white tracking-tight">Forgot Password?</h2>
                <p class="text-gray-400 text-xs md:text-sm mt-2 font-medium">Enter your email for a recovery link</p>
            </div>

            {{-- Success Message --}}
            @if (session('success'))
                <div class="bg-green-500/20 border border-green-500/30 text-green-100 px-4 py-3 rounded-xl mb-6 text-xs flex items-center gap-3 font-medium backdrop-blur-sm">
                    <i class="fas fa-check-circle text-green-400"></i> {{ session('success') }}
                </div>
            @endif

            {{-- Form --}}
            <form action="{{ route('password.email') }}" method="POST" class="space-y-4 md:space-y-6">
                @csrf
                
                {{-- Email Input --}}
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-gray-300 uppercase tracking-wider ml-1">Email Address</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-envelope text-gray-500 group-focus-within:text-orange-500 transition-colors"></i>
                        </div>
                        <input type="email" name="email" required placeholder="name@example.com" 
                            class="glass-input w-full rounded-xl py-3 md:py-3.5 pl-11 pr-4 text-sm font-medium focus:ring-0">
                    </div>
                    @error('email') <span class="text-red-400 text-xs block mt-1 ml-1 font-medium">{{ $message }}</span> @enderror
                </div>

                {{-- Submit Button --}}
                {{-- Updated to Orange Gradient to match Login/Register pages --}}
                <button type="submit" class="w-full bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-500 hover:to-red-500 text-white font-bold py-3 md:py-4 rounded-xl shadow-lg shadow-orange-900/40 transition-all transform hover:-translate-y-0.5 active:translate-y-0 text-sm md:text-base">
                    Send Reset Link
                </button>
            </form>

            {{-- Footer Links --}}
            <div class="mt-6 md:mt-8 pt-6 border-t border-white/10 text-center">
                <a href="{{ route('login') }}" class="text-xs text-gray-400 hover:text-white transition flex items-center justify-center gap-2 font-medium">
                    <i class="fas fa-arrow-left"></i> Return to Login
                </a>
            </div>
        </div>
    </div>
</div>
@endsection