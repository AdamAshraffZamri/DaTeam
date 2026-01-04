@extends('layouts.app')

@section('content')
<style>
    @keyframes blob {
        0% { transform: translate(0px, 0px) scale(1); }
        33% { transform: translate(30px, -50px) scale(1.1); }
        66% { transform: translate(-20px, 20px) scale(0.9); }
        100% { transform: translate(0px, 0px) scale(1); }
    }
    .animate-blob { animation: blob 7s infinite; }
    .animation-delay-2000 { animation-delay: 2s; }
    .animation-delay-4000 { animation-delay: 4s; }
    
    .glass-card {
        background: rgba(43, 43, 43, 0.38); 
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow: 0 8px 32px 0 rgba(26, 26, 26, 0.37);
    }
    
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
    
    .glass-input::placeholder { color: #6b7280; }
</style>

<div class="w-full min-h-[calc(100vh-80px)] flex items-center justify-center relative bg-gray-900 overflow-hidden">

    {{-- Background --}}
    <div class="absolute inset-0 z-0">
        <img src="{{ asset('login.png') }}" alt="Background" class="w-full h-full object-cover opacity-60">
        <div class="absolute inset-0 bg-gradient-to-r from-black/60 via-black/35 to-black/65"></div>
    </div>

    {{-- Content --}}
    <div class="relative z-10 w-full max-w-md px-6 pt-8 py-12">
        <div class="glass-card w-full rounded-[2rem] p-8 md:p-10 transform transition-all hover:scale-[1.01] duration-500 shadow-2xl">
            
            {{-- Logo --}}
            <div class="flex justify-center mb-8">
                <div class="p-3 bg-white/10 rounded-2xl backdrop-blur-sm border border-white/20 shadow-lg">
                    <img src="{{ asset('hasta.jpeg') }}" alt="HASTA Logo" class="h-12 w-auto object-contain rounded-lg">
                </div>
            </div>

            <div class="text-center mb-8">
                <h2 class="text-2xl font-black text-white tracking-tight">Set New Password</h2>
                <p class="text-gray-400 text-sm mt-2 font-medium">Secure your account with a new password.</p>
            </div>

            <form action="{{ route('password.update') }}" method="POST" class="space-y-5">
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
                            class="glass-input w-full rounded-xl py-3.5 pl-11 pr-4 text-sm font-medium opacity-50 cursor-not-allowed">
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
                        <input type="password" name="password" required placeholder="New secure password" 
                            class="glass-input w-full rounded-xl py-3.5 pl-11 pr-4 text-sm font-medium">
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
                        <input type="password" name="password_confirmation" required placeholder="Repeat password" 
                            class="glass-input w-full rounded-xl py-3.5 pl-11 pr-4 text-sm font-medium">
                    </div>
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-500 hover:to-red-500 text-white font-bold py-4 rounded-xl shadow-lg shadow-orange-900/40 transition-all transform hover:-translate-y-0.5 active:translate-y-0 mt-2">
                    Update Password
                </button>
            </form>
        </div>
    </div>
</div>
@endsection