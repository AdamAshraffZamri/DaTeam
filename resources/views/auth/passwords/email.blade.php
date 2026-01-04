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
                <h2 class="text-2xl font-black text-white tracking-tight">Forgot Password?</h2>
                <p class="text-gray-400 text-sm mt-2 font-medium">Enter your email for a recovery link</p>
            </div>

            @if (session('success'))
                <div class="bg-green-500/20 border border-green-500/30 text-green-100 px-4 py-3 rounded-xl mb-6 text-xs flex items-center gap-3 font-medium">
                    <i class="fas fa-check-circle text-green-400"></i> {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('password.email') }}" method="POST" class="space-y-6">
                @csrf
                
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-gray-300 uppercase tracking-wider ml-1">Email Address</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-envelope text-gray-500 group-focus-within:text-orange-500 transition-colors"></i>
                        </div>
                        <input type="email" name="email" required placeholder="name@example.com" 
                            class="glass-input w-full rounded-xl py-3.5 pl-11 pr-4 text-sm font-medium">
                    </div>
                    @error('email') <span class="text-red-400 text-xs block mt-1 ml-1 font-medium">{{ $message }}</span> @enderror
                </div>

                <button type="submit" class="w-full bg-white text-gray-900 hover:bg-gray-100 font-bold py-3.5 rounded-xl shadow-lg transition-all transform hover:-translate-y-0.5">
                    Send Reset Link
                </button>
            </form>

            <div class="mt-8 pt-6 border-t border-white/10 text-center">
                <a href="{{ route('login') }}" class="text-xs text-gray-400 hover:text-white transition flex items-center justify-center gap-2 font-medium">
                    <i class="fas fa-arrow-left"></i> Return to Login
                </a>
            </div>
        </div>
    </div>
</div>
@endsection