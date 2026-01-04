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
    
    /* Custom scrollbar for form in case height exceeds on small screens */
    .custom-scroll::-webkit-scrollbar { width: 4px; }
    .custom-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 4px; }
</style>

<div class="w-full min-h-[calc(100vh-80px)] flex items-center justify-center relative bg-gray-900 overflow-hidden">

    {{-- Abstract Background Image --}}
    <div class="absolute inset-0 z-0">
        <img src="{{ asset('login.png') }}" 
             alt="Background" 
             class="w-full h-full object-cover opacity-60">
        <div class="absolute inset-0 bg-gradient-to-r from-black/60 via-black/35 to-black/65"></div>
    </div>

    {{-- Content Container --}}
    <div class="relative z-10 w-full max-w-xl px-6 pt-8 py-12">
        
        <div class="glass-card w-full rounded-[2rem] p-8 md:p-10 transform transition-all hover:scale-[1.01] duration-500 shadow-2xl">
            
            {{-- Logo --}}
            <div class="flex justify-center mb-6">
                <div class="p-3 bg-white/10 rounded-2xl backdrop-blur-sm border border-white/20 shadow-lg">
                    <img src="{{ asset('hasta.jpeg') }}" alt="HASTA Logo" class="h-12 w-auto object-contain rounded-lg">
                </div>
            </div>

            <div class="text-center mb-6">
                <h2 class="text-3xl font-black text-white tracking-tight">Create Account</h2>
                <p class="text-gray-400 text-sm mt-1 font-medium">Sign up to HASTA for exclusive rewards!</p>
            </div>

            <form action="{{ route('register') }}" method="POST" class="space-y-4 max-h-[60vh] overflow-y-auto custom-scroll pr-2">
                @csrf
                <input type="hidden" name="role" value="customer">

                {{-- Name --}}
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-gray-300 uppercase tracking-wider ml-1">Full Name</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-user text-gray-500 group-focus-within:text-orange-500 transition-colors"></i>
                        </div>
                        <input type="text" name="name" required placeholder="John Doe" 
                            class="glass-input w-full rounded-xl py-3.5 pl-11 pr-4 text-sm font-medium">
                    </div>
                </div>

                {{-- Email --}}
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-gray-300 uppercase tracking-wider ml-1">Email</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-envelope text-gray-500 group-focus-within:text-orange-500 transition-colors"></i>
                        </div>
                        <input type="email" name="email" required placeholder="name@example.com" 
                            class="glass-input w-full rounded-xl py-3.5 pl-11 pr-4 text-sm font-medium">
                    </div>
                </div>

                {{-- Phone --}}
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-gray-300 uppercase tracking-wider ml-1">Phone</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-phone text-gray-500 group-focus-within:text-orange-500 transition-colors"></i>
                        </div>
                        <input type="text" name="phone" placeholder="+601..." 
                            class="glass-input w-full rounded-xl py-3.5 pl-11 pr-4 text-sm font-medium">
                    </div>
                </div>

                {{-- Password Grid --}}
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-300 uppercase tracking-wider ml-1">Password</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-500 group-focus-within:text-orange-500 transition-colors"></i>
                            </div>
                            <input type="password" name="password" required placeholder="••••••"
                                class="glass-input w-full rounded-xl py-3.5 pl-10 pr-4 text-sm font-medium">
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-300 uppercase tracking-wider ml-1">Confirm</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-500 group-focus-within:text-orange-500 transition-colors"></i>
                            </div>
                            <input type="password" name="password_confirmation" required placeholder="••••••" 
                                class="glass-input w-full rounded-xl py-3.5 pl-10 pr-4 text-sm font-medium">
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-500 hover:to-red-500 text-white font-bold py-4 rounded-xl shadow-lg shadow-orange-900/40 transition-all transform hover:-translate-y-0.5 active:translate-y-0 mt-4">
                    Sign Up
                </button>
            </form>

            <div class="mt-6 pt-6 border-t border-white/10 text-center">
                <p class="text-gray-400 text-sm">
                    Already a member? <a href="{{ route('login') }}" class="text-white font-bold hover:text-orange-400 transition hover:underline">Log in</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection