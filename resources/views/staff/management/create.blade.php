@extends('layouts.staff')

@section('title', 'Add New Staff')

@section('content')
<div class="min-h-screen bg-slate-100 rounded-2xl p-6">

    {{-- HEADER --}}
    <div class="mb-8 animate-fade-in">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-black text-gray-900">Add New Staff Member</h1>
                <p class="text-gray-500 mt-1 text-sm">Create a new staff account with administrative access.</p>
            </div>
            
            {{-- Action Badge --}}
            <div class="px-4 py-2 rounded-xl border bg-blue-50 border-blue-100 text-blue-700 flex items-center gap-2 shadow-sm">
                <i class="fas fa-user-plus"></i>
                <span class="text-xs font-black uppercase tracking-wide">New Account</span>
            </div>
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

    {{-- FORM CARD --}}
    <div class="bg-white rounded-xl p-8 border border-gray-100 shadow-sm animate-fade-in">
        
        <form action="{{ route('staff.management.store') }}" method="POST" class="space-y-8">
            @csrf
            
            {{-- SECTION 1: PERSONAL DETAILS --}}
            <div>
                <h3 class="text-sm font-black text-gray-900 uppercase tracking-wider mb-6 flex items-center gap-2">
                    <i class="fas fa-user-check text-orange-600"></i> Personal Information
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Name --}}
                    <div class="group">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 group-focus-within:text-orange-600 transition-colors">Full Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" 
                               class="w-full px-4 py-3.5 rounded-xl border border-gray-200 bg-gray-50 text-sm font-bold text-gray-800 focus:bg-white focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all outline-none placeholder-gray-300" 
                               placeholder="Enter full name" required>
                    </div>

                    {{-- Email --}}
                    <div class="group">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 group-focus-within:text-orange-600 transition-colors">Email Address</label>
                        <input type="email" name="email" value="{{ old('email') }}" 
                               class="w-full px-4 py-3.5 rounded-xl border border-gray-200 bg-gray-50 text-sm font-bold text-gray-800 focus:bg-white focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all outline-none placeholder-gray-300" 
                               placeholder="Enter email address" required>
                    </div>

                    {{-- Phone --}}
                    <div class="group">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 group-focus-within:text-orange-600 transition-colors">Phone Number</label>
                        <input type="text" name="phoneNo" value="{{ old('phoneNo') }}" 
                               class="w-full px-4 py-3.5 rounded-xl border border-gray-200 bg-gray-50 text-sm font-bold text-gray-800 focus:bg-white focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all outline-none placeholder-gray-300"
                               placeholder="Enter phone number">
                    </div>

                    {{-- Role --}}
                    <div class="group">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 group-focus-within:text-orange-600 transition-colors">System Role</label>
                        <div class="relative">
                            <select name="role" class="w-full px-4 py-3.5 rounded-xl border border-gray-200 bg-gray-50 text-sm font-bold text-gray-800 focus:bg-white focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all outline-none appearance-none cursor-pointer" required>
                                <option value="">-- Select Role --</option>
                                <option value="staff" {{ old('role') == 'staff' ? 'selected' : '' }}>Normal Staff</option>
                                <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Administrator</option>
                            </select>
                            <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="h-px bg-gray-100"></div>

            {{-- SECTION 2: SECURITY & PASSWORD --}}
            <div>
                <h3 class="text-sm font-black text-gray-900 uppercase tracking-wider mb-6 flex items-center gap-2">
                    <i class="fas fa-lock text-orange-600"></i> Security & Password
                </h3>
                
                <div class="bg-orange-50/50 border border-orange-100 rounded-xl p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Password --}}
                        <div class="group">
                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 group-focus-within:text-orange-600 transition-colors">Password</label>
                            <input type="password" name="password" 
                                   class="w-full px-4 py-3.5 rounded-xl border border-gray-200 bg-white text-sm font-bold text-gray-800 focus:bg-white focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all outline-none placeholder-gray-300"
                                   placeholder="Minimum 8 characters" required>
                        </div>

                        {{-- Confirm Password --}}
                        <div class="group">
                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 group-focus-within:text-orange-600 transition-colors">Confirm Password</label>
                            <input type="password" name="password_confirmation" 
                                   class="w-full px-4 py-3.5 rounded-xl border border-gray-200 bg-white text-sm font-bold text-gray-800 focus:bg-white focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all outline-none placeholder-gray-300"
                                   placeholder="Re-type to confirm" required>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ACTIONS --}}
            <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-100 mt-8">
                <a href="{{ route('staff.management.index') }}" class="text-gray-600 hover:text-gray-900 px-8 py-3.5 rounded-2xl font-bold text-xs transition-colors flex items-center gap-2">
                    <i class="fas fa-times"></i>
                    <span>Cancel</span>
                </a>
                <button type="submit" class="bg-gray-900 hover:bg-orange-600 text-white px-8 py-3.5 rounded-2xl font-bold text-xs shadow-lg transition-all transform hover:scale-105 flex items-center gap-2">
                    <i class="fas fa-plus"></i>
                    <span>Create Staff Member</span>
                </button>
            </div>

        </form>
    </div>
</div>

<style>
    @keyframes fade-in { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
    .animate-fade-in { animation: fade-in 0.3s ease-out forwards; }
</style>
@endsection