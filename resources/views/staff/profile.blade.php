@extends('layouts.staff')

@section('title', 'My Profile')

@section('content')

{{-- SweetAlert2 for Validation Popups --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="min-h-screen bg-slate-100 rounded-2xl p-6">

    {{-- HEADER WITH STATUS BADGE --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8 bg-white p-6 rounded-2xl shadow-sm border border-gray-100 animate-fade-in">
        <div class="flex items-center gap-6">
            <div class="w-20 h-20 bg-orange-100 rounded-full flex items-center justify-center text-orange-600 font-black text-3xl shadow-inner">
                {{ substr($user->name, 0, 1) }}
            </div>
            <div>
                <h1 class="text-2xl font-black text-gray-900">My Profile</h1>
                <p class="text-gray-500 text-sm font-medium">Manage your personal details</p>
            </div>
        </div>
        
        {{-- Role Badge --}}
        <div class="px-4 py-2 rounded-xl border bg-orange-50 border-orange-100 text-orange-700 flex items-center gap-2 shadow-sm self-start md:self-center">
            <i class="fas fa-id-badge"></i>
            <span class="text-xs font-black uppercase tracking-wide">{{ $user->role }} Account</span>
        </div>
    </div>

    {{-- VALIDATION ERRORS --}}
    @if ($errors->any())
    <div class="mb-6 bg-red-50 border border-red-200 rounded-2xl p-4 flex items-start gap-4 shadow-sm animate-fade-in">
        <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-red-600 shrink-0 border border-red-200 mt-1">
            <i class="fas fa-exclamation text-sm"></i>
        </div>
        <div>
            <h4 class="text-sm font-black text-red-900">Action Required</h4>
            <ul class="mt-1 list-disc list-inside text-xs text-red-700 font-medium">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    {{-- SUCCESS MESSAGE --}}
    @if(session('success'))
    <div class="mb-6 bg-green-50 border border-green-200 rounded-2xl p-4 flex items-start gap-4 shadow-sm animate-fade-in">
        <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-600 shrink-0 border border-green-200 mt-1">
            <i class="fas fa-check text-sm"></i>
        </div>
        <div>
            <h4 class="text-sm font-black text-green-900">Success</h4>
            <p class="text-xs text-green-700 font-medium mt-1">{{ session('success') }}</p>
        </div>
    </div>
    @endif

    {{-- FORM CARD --}}
    <div class="bg-white rounded-xl p-8 border border-gray-100 shadow-sm animate-fade-in">
        
        <form action="{{ route('staff.profile.update') }}" method="POST" class="space-y-8">
            @csrf
            @method('PUT')
            
            {{-- SECTION 1: PERSONAL DETAILS --}}
            <div>
                <h3 class="text-sm font-black text-gray-900 uppercase tracking-wider mb-6 flex items-center gap-2">
                    <i class="fas fa-user-edit text-orange-600"></i> Personal Information
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Name --}}
                    <div class="group">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 group-focus-within:text-orange-600 transition-colors">Full Name</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" 
                               class="w-full px-4 py-3.5 rounded-xl border border-gray-200 bg-gray-50 text-sm font-bold text-gray-800 focus:bg-white focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all outline-none placeholder-gray-300" 
                               required>
                    </div>

                    {{-- Email --}}
                    <div class="group">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 group-focus-within:text-orange-600 transition-colors">Email Address</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" 
                               class="w-full px-4 py-3.5 rounded-xl border border-gray-200 bg-gray-50 text-sm font-bold text-gray-800 focus:bg-white focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all outline-none placeholder-gray-300" 
                               required>
                    </div>

                    {{-- Phone --}}
                    <div class="group">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 group-focus-within:text-orange-600 transition-colors">Phone Number</label>
                        <input type="text" name="phoneNo" value="{{ old('phoneNo', $user->phoneNo) }}" 
                               class="w-full px-4 py-3.5 rounded-xl border border-gray-200 bg-gray-50 text-sm font-bold text-gray-800 focus:bg-white focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all outline-none placeholder-gray-300">
                    </div>
                </div>
            </div>

            {{-- SECTION 2: SECURITY (ADMIN ONLY) --}}
            @if($user->role === 'admin')
            <div class="h-px bg-gray-100"></div>

            <div>
                <h3 class="text-sm font-black text-gray-900 uppercase tracking-wider mb-6 flex items-center gap-2">
                    <i class="fas fa-lock text-orange-600"></i> Security & Access (Admin Only)
                </h3>
                
                <div class="bg-orange-50/50 border border-orange-100 rounded-xl p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- New Password --}}
                        <div class="group">
                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 group-focus-within:text-orange-600 transition-colors">New Password</label>
                            <div class="relative">
                                <input type="password" name="password" id="password" 
                                       class="w-full px-4 py-3.5 rounded-xl border border-gray-200 bg-white text-sm font-bold text-gray-800 focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all outline-none placeholder-gray-300 pr-10"
                                       placeholder="Leave blank to keep current">
                                <button type="button" onclick="togglePassword('password', 'icon_pass')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-orange-600 transition">
                                    <i class="fas fa-eye" id="icon_pass"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Confirm Password --}}
                        <div class="group">
                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 group-focus-within:text-orange-600 transition-colors">Confirm New Password</label>
                            <div class="relative">
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                       class="w-full px-4 py-3.5 rounded-xl border border-gray-200 bg-white text-sm font-bold text-gray-800 focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all outline-none placeholder-gray-300 pr-10">
                                <button type="button" onclick="togglePassword('password_confirmation', 'icon_confirm')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-orange-600 transition">
                                    <i class="fas fa-eye" id="icon_confirm"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- ACTIONS --}}
            <div class="flex items-center justify-end pt-4 border-t border-gray-100 mt-8">
                <button type="submit" class="bg-gray-900 hover:bg-orange-600 text-white px-8 py-3.5 rounded-2xl font-bold text-xs shadow-lg transition-all transform hover:scale-105 flex items-center gap-2">
                    <span>Save Changes</span>
                    <i class="fas fa-check"></i>
                </button>
            </div>

        </form>
    </div>
</div>

<style>
    @keyframes fade-in { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
    .animate-fade-in { animation: fade-in 0.3s ease-out forwards; }
</style>

<script>
    function togglePassword(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        
        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = "password";
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>

@endsection