@extends('layouts.app')

@section('content')
{{-- SweetAlert2 for Validation Popups --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* Scoped Styles */
    
    /* Animation for background elements (if used) */
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
       - Consistent darker background (0.85 opacity) for readability on mobile.
       - Stronger shadow and border for depth.
    */
    .glass-card {
        background: rgba(20, 20, 20, 0.6);
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
    
    /* Custom scrollbar for form (useful on small landscape screens) */
    .custom-scroll::-webkit-scrollbar { width: 4px; }
    .custom-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 4px; }
</style>

{{-- Main Wrapper --}}
{{-- min-h-[calc(100vh-80px)] ensures vertical centering relative to the navbar --}}
<div class="w-full min-h-[calc(100vh-80px)] flex items-center justify-center relative bg-gray-900 overflow-hidden">

    {{-- Abstract Background Image --}}
    <div class="absolute inset-0 z-0">
        <img src="{{ asset('login.png') }}" 
             alt="Background" 
             class="w-full h-full object-cover opacity-60">
        {{-- Gradient Overlay --}}
        <div class="absolute inset-0 bg-gradient-to-r from-black/60 via-black/35 to-black/65"></div>
    </div>

    {{-- Content Container --}}
    {{-- Adjusted width: max-w-sm on mobile, md:max-w-lg on desktop (slightly wider than login for the extra fields) --}}
    <div class="relative z-10 w-full max-w-sm md:max-w-lg px-4 py-8 md:py-12 mx-auto">
        
        {{-- Register Card --}}
        {{-- Mobile: rounded-2xl, p-6. Desktop: rounded-[2rem], p-10 --}}
        <div class="glass-card w-full rounded-2xl md:rounded-[2rem] p-6 md:p-10 transform transition-all hover:scale-[1.01] duration-500 shadow-2xl">
            
            {{-- Logo --}}
            <div class="flex justify-center mb-6">
                <div class="p-3 bg-white/10 rounded-2xl backdrop-blur-sm border border-white/20 shadow-lg">
                    {{-- Logo: h-10 mobile, h-12 desktop --}}
                    <img src="{{ asset('hasta.jpeg') }}" alt="HASTA Logo" class="h-10 md:h-12 w-auto object-contain rounded-lg">
                </div>
            </div>

            {{-- Header Text --}}
            <div class="text-center mb-6">
                <h2 class="text-2xl md:text-3xl font-black text-white tracking-tight">Create Account</h2>
                <p class="text-gray-400 text-xs md:text-sm mt-1 font-medium">Sign up to HASTA for exclusive rewards!</p>
            </div>

            {{-- Form Container --}}
            {{-- Removed max-h and overflow classes so the card expands to fit all fields --}}
            <form action="{{ route('register') }}" method="POST" class="space-y-4 pr-2">
                @csrf
                <input type="hidden" name="role" value="customer">

                {{-- Full Name --}}
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-gray-300 uppercase tracking-wider ml-1">Full Name</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-user text-gray-500 group-focus-within:text-orange-500 transition-colors"></i>
                        </div>
                        <input type="text" name="name" required placeholder="John Doe" 
                            class="glass-input w-full rounded-xl py-3 md:py-3.5 pl-11 pr-4 text-sm font-medium focus:ring-0">
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
                            class="glass-input w-full rounded-xl py-3 md:py-3.5 pl-11 pr-4 text-sm font-medium focus:ring-0">
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
                            class="glass-input w-full rounded-xl py-3 md:py-3.5 pl-11 pr-4 text-sm font-medium focus:ring-0">
                    </div>
                </div>

                {{-- Password Grid --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Password --}}
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-300 uppercase tracking-wider ml-1">Password</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-500 group-focus-within:text-orange-500 transition-colors"></i>
                            </div>
                            
                            <input type="password" name="password" id="reg_password" required placeholder="••••••"
                                class="glass-input w-full rounded-xl py-3 md:py-3.5 pl-10 pr-10 text-sm font-medium focus:ring-0">

                            {{-- Eye Icon --}}
                            <button type="button" onclick="togglePassword('reg_password', this)" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-white transition-colors focus:outline-none"
                                    title="Show Password">
                                <i class="fas fa-eye-slash"></i>
                            </button>
                        </div>
                    </div>
                    
                    {{-- Confirm Password --}}
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-300 uppercase tracking-wider ml-1">Confirm</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-500 group-focus-within:text-orange-500 transition-colors"></i>
                            </div>

                            <input type="password" name="password_confirmation" id="reg_confirm" required placeholder="••••••" 
                                class="glass-input w-full rounded-xl py-3 md:py-3.5 pl-10 pr-10 text-sm font-medium focus:ring-0">

                            {{-- Eye Icon --}}
                            <button type="button" onclick="togglePassword('reg_confirm', this)" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-white transition-colors focus:outline-none"
                                    title="Show Password">
                                <i class="fas fa-eye-slash"></i>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Submit Button --}}
                <button type="submit" class="w-full bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-500 hover:to-red-500 text-white font-bold py-3 md:py-4 rounded-xl shadow-lg shadow-orange-900/40 transition-all transform hover:-translate-y-0.5 active:translate-y-0 mt-4 text-sm md:text-base">
                    Sign Up
                </button>
            </form>

            {{-- Footer Links --}}
            <div class="mt-6 md:mt-8 pt-6 border-t border-white/10 text-center">
                <p class="text-gray-400 text-xs md:text-sm">
                    Already a member? <a href="{{ route('login') }}" class="text-white font-bold hover:text-orange-400 transition hover:underline">Log in</a>
                </p>
            </div>
        </div>
    </div>
</div>

{{-- VALIDATION ERROR POPUP (Unchanged) --}}
@if ($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const errors = @json($errors->messages());
        let errorMessages = '<ul style="text-align: left;">';
        
        for (const [field, messages] of Object.entries(errors)) {
            messages.forEach(message => {
                errorMessages += `<li style="margin: 8px 0;">${message}</li>`;
            });
        }
        errorMessages += '</ul>';
        
        Swal.fire({
            icon: 'error',
            title: '❌ Invalid Information',
            html: errorMessages,
            confirmButtonColor: '#ea580c',
            confirmButtonText: 'Try Again',
            allowOutsideClick: false,
            allowEscapeKey: false,
            background: '#353639ff',
            color: '#fff',
            customClass: {
                popup: 'border border-red-500/30 backdrop-blur-md',
                confirmButton: 'bg-orange-600 hover:bg-orange-700 text-white px-6 py-2 rounded-lg font-bold'
            }
        });
    });
</script>
@endif

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