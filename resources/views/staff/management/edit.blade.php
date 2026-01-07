@extends('layouts.staff')

@section('content')
<div class="min-h-screen bg-slate-100 rounded-2xl p-6">

        {{-- HEADER --}}
        <div class="mb-8 animate-fade-in">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-black text-gray-900">Edit Account</h1>
                    <p class="text-gray-500 mt-1 text-sm">Update details for <span class="font-bold text-gray-800">{{ $staff->name }}</span>.</p>
                </div>
                
                {{-- Status Badge (Visual Only) --}}
                <div class="px-4 py-2 rounded-xl border {{ $staff->active ? 'bg-green-50 border-green-100 text-green-700' : 'bg-red-50 border-red-100 text-red-700' }} flex items-center gap-2 shadow-sm">
                    <span class="w-2 h-2 rounded-full {{ $staff->active ? 'bg-green-500' : 'bg-red-500' }}"></span>
                    <span class="text-xs font-black uppercase tracking-wide">{{ $staff->active ? 'Active Account' : 'Inactive Account' }}</span>
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
            
            <form action="{{ route('staff.management.update', $staff->staffID) }}" method="POST" class="space-y-8">
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
                            <input type="text" name="name" value="{{ old('name', $staff->name) }}" 
                                   class="w-full px-4 py-3.5 rounded-xl border border-gray-200 bg-gray-50 text-sm font-bold text-gray-800 focus:bg-white focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all outline-none placeholder-gray-300" 
                                   required>
                        </div>

                        {{-- Email --}}
                        <div class="group">
                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 group-focus-within:text-orange-600 transition-colors">Email Address</label>
                            <input type="email" name="email" value="{{ old('email', $staff->email) }}" 
                                   class="w-full px-4 py-3.5 rounded-xl border border-gray-200 bg-gray-50 text-sm font-bold text-gray-800 focus:bg-white focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all outline-none placeholder-gray-300" 
                                   required>
                        </div>

                        {{-- Phone --}}
                        <div class="group">
                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 group-focus-within:text-orange-600 transition-colors">Phone Number</label>
                            <input type="text" name="phoneNo" value="{{ old('phoneNo', $staff->phoneNo) }}" 
                                   class="w-full px-4 py-3.5 rounded-xl border border-gray-200 bg-gray-50 text-sm font-bold text-gray-800 focus:bg-white focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all outline-none placeholder-gray-300">
                        </div>

                        {{-- Role --}}
                        <div class="group">
                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 group-focus-within:text-orange-600 transition-colors">System Role</label>
                            <div class="relative">
                                <select name="role" class="w-full px-4 py-3.5 rounded-xl border border-gray-200 bg-gray-50 text-sm font-bold text-gray-800 focus:bg-white focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all outline-none appearance-none cursor-pointer">
                                    <option value="staff" {{ old('role', $staff->role) == 'staff' ? 'selected' : '' }}>Normal Staff</option>
                                    <option value="admin" {{ old('role', $staff->role) == 'admin' ? 'selected' : '' }}>Administrator</option>
                                </select>
                                <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="h-px bg-gray-100"></div>

                {{-- SECTION 2: SECURITY & STATUS --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    
                    {{-- Password Update --}}
                    <div>
                        <h3 class="text-sm font-black text-gray-900 uppercase tracking-wider mb-6 flex items-center gap-2">
                            <i class="fas fa-key text-orange-600"></i> Change Password
                        </h3>
                        <div class="space-y-6">
                            <div class="group">
                                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 group-focus-within:text-orange-600 transition-colors">New Password (Optional)</label>
                                <input type="password" name="password" 
                                       class="w-full px-4 py-3.5 rounded-xl border border-gray-200 bg-gray-50 text-sm font-bold text-gray-800 focus:bg-white focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all outline-none placeholder-gray-300" 
                                       placeholder="Leave blank to keep current">
                            </div>
                            <div class="group">
                                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 group-focus-within:text-orange-600 transition-colors">Confirm New Password</label>
                                <input type="password" name="password_confirmation" 
                                       class="w-full px-4 py-3.5 rounded-xl border border-gray-200 bg-gray-50 text-sm font-bold text-gray-800 focus:bg-white focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all outline-none placeholder-gray-300">
                            </div>
                        </div>
                    </div>

                    {{-- Account Status --}}
                    <div>
                        <h3 class="text-sm font-black text-gray-900 uppercase tracking-wider mb-6 flex items-center gap-2">
                            <i class="fas fa-toggle-on text-orange-600"></i> Account Status
                        </h3>
                        
                        <div class="bg-gray-50 border border-gray-200 rounded-xl p-5 flex items-start gap-4">
                            <div class="flex items-center h-5 mt-1">
                                <input id="active" name="active" value="1" type="checkbox" {{ old('active', $staff->active) ? 'checked' : '' }} 
                                       class="w-5 h-5 text-orange-600 border-gray-300 rounded focus:ring-orange-500 cursor-pointer">
                            </div>
                            <div>
                                <label for="active" class="font-bold text-gray-900 text-sm cursor-pointer select-none">Active Account</label>
                                <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                                    Unchecking this box will prevent the staff member from logging into the system. It does not delete their history.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ACTIONS --}}
                <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-100 mt-8">
                    <a href="{{ route('staff.management.index') }}" class="px-6 py-3.5 rounded-2xl text-xs font-bold text-gray-500 hover:text-gray-800 uppercase tracking-wider transition-colors">
                        Cancel
                    </a>
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
@endsection