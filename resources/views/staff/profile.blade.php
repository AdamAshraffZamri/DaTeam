@extends('layouts.staff')

@section('content')
<div class="min-h-screen bg-slate-100 rounded-2xl p-6">

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 animate-fade-in">
        <div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight">My Profile</h1>
            <p class="text-slate-500 text-sm font-medium mt-1">Manage your account settings and preferences.</p>
        </div>

        {{-- Role Badge --}}
        <div class="flex items-center gap-3 px-4 py-2 bg-white rounded-xl border border-gray-100 shadow-sm">
            <div class="flex flex-col items-end">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Account Role</span>
                <span class="text-xs font-black text-orange-600 uppercase">{{ Auth::guard('staff')->user()->role }}</span>
            </div>
            <div class="w-8 h-8 rounded-lg bg-orange-50 text-orange-600 flex items-center justify-center border border-orange-100">
                <i class="fas fa-id-badge text-xs"></i>
            </div>
        </div>
    </div>

    {{-- ALERTS --}}
    @if(session('success'))
        <div class="mb-8 bg-green-50 border border-green-200 rounded-2xl p-4 flex items-center gap-4 shadow-sm animate-fade-in">
            <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-600 shrink-0 border border-green-200">
                <i class="fas fa-check text-sm"></i>
            </div>
            <div>
                <h4 class="text-sm font-black text-green-900">Success</h4>
                <p class="text-xs text-green-700 font-medium">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    {{-- MAIN FORM CARD --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden animate-fade-in">
        
        <form action="{{ route('staff.profile.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="p-8 space-y-10">

                {{-- SECTION: PERSONAL INFO --}}
                <section>
                    <div class="flex items-center gap-2 mb-6 border-b border-gray-50 pb-2">
                        <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center">
                            <i class="fas fa-user text-xs"></i>
                        </div>
                        <h3 class="text-sm font-black text-slate-800 uppercase tracking-wider">Personal Information</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                        {{-- Name --}}
                        <div class="space-y-1.5">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Full Name</label>
                            <input type="text" name="name" value="{{ old('name', $staff->name) }}" 
                                class="w-full bg-slate-50 border border-slate-100 text-slate-800 text-sm font-bold rounded-xl px-4 py-3 focus:bg-white focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 outline-none transition-all placeholder-gray-300"
                                required>
                            @error('name') <p class="text-red-500 text-[10px] font-bold mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Email --}}
                        <div class="space-y-1.5">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Email Address</label>
                            <input type="email" name="email" value="{{ old('email', $staff->email) }}" 
                                class="w-full bg-slate-50 border border-slate-100 text-slate-800 text-sm font-bold rounded-xl px-4 py-3 focus:bg-white focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 outline-none transition-all placeholder-gray-300"
                                required>
                            @error('email') <p class="text-red-500 text-[10px] font-bold mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Phone --}}
                        <div class="space-y-1.5 md:col-span-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Phone Number</label>
                            <input type="text" name="phoneNo" value="{{ old('phoneNo', $staff->phoneNo) }}" 
                                class="w-full bg-slate-50 border border-slate-100 text-slate-800 text-sm font-bold rounded-xl px-4 py-3 focus:bg-white focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 outline-none transition-all placeholder-gray-300">
                            @error('phoneNo') <p class="text-red-500 text-[10px] font-bold mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </section>

                {{-- SECTION: SECURITY (Optional Password Change) --}}
                <section>
                    <div class="flex items-center gap-2 mb-6 border-b border-gray-50 pb-2">
                        <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center">
                            <i class="fas fa-lock text-xs"></i>
                        </div>
                        <h3 class="text-sm font-black text-slate-800 uppercase tracking-wider">Security</h3>
                    </div>

                    <div class="p-5 rounded-xl border border-dashed border-gray-200 bg-gray-50/50 space-y-4">
                        <div>
                            <h4 class="text-xs font-black text-slate-800">Update Password</h4>
                            <p class="text-[10px] text-slate-400 mt-0.5">Leave blank if you don't want to change it.</p>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-bold text-slate-400 uppercase">New Password</label>
                                <input type="password" name="password" 
                                    class="w-full bg-white border border-gray-200 text-slate-800 text-sm rounded-lg px-3 py-2.5 focus:border-orange-500 focus:ring-0 outline-none transition-colors">
                                @error('password') <p class="text-red-500 text-[10px] font-bold mt-1">{{ $message }}</p> @enderror
                            </div>
                            
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-bold text-slate-400 uppercase">Confirm Password</label>
                                <input type="password" name="password_confirmation" 
                                    class="w-full bg-white border border-gray-200 text-slate-800 text-sm rounded-lg px-3 py-2.5 focus:border-orange-500 focus:ring-0 outline-none transition-colors">
                            </div>
                        </div>
                    </div>
                </section>

            </div>

            {{-- FOOTER ACTIONS --}}
            <div class="px-8 py-5 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3">
                <a href="{{ route('staff.dashboard') }}" class="px-6 py-2.5 rounded-xl text-xs font-bold text-slate-500 hover:text-slate-800 hover:bg-white hover:shadow-sm border border-transparent hover:border-gray-200 transition-all uppercase tracking-wide">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-slate-900 text-white text-xs font-bold uppercase tracking-wide hover:bg-orange-600 hover:shadow-lg hover:shadow-orange-500/30 transition-all transform hover:-translate-y-0.5 flex items-center gap-2">
                    <span>Update Profile</span>
                    <i class="fas fa-check"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    @keyframes fade-in { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .animate-fade-in { animation: fade-in 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
</style>
@endsection