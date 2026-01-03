@extends('layouts.staff')
@section('title', 'My Profile')

@section('content')
<div class="max-w-2xl mx-auto bg-white p-8 rounded-xl shadow-sm border border-gray-100">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-800">My Profile</h2>
        <span class="px-3 py-1 bg-orange-100 text-orange-600 rounded-full text-xs font-bold uppercase tracking-wide">
            {{ Auth::guard('staff')->user()->role }} Account
        </span>
    </div>
    
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-700">
            <p class="font-bold">Success</p>
            <p class="text-sm">{{ session('success') }}</p>
        </div>
    @endif

    <form action="{{ route('staff.profile.update') }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Full Name</label>
                <input type="text" name="name" value="{{ old('name', $staff->name) }}" class="w-full border-gray-200 rounded-lg focus:ring-orange-500 focus:border-orange-500" required>
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Email Address</label>
                <input type="email" name="email" value="{{ old('email', $staff->email) }}" class="w-full border-gray-200 rounded-lg focus:ring-orange-500 focus:border-orange-500" required>
                @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-bold text-gray-700 mb-2">Phone Number</label>
            <input type="text" name="phoneNo" value="{{ old('phoneNo', $staff->phoneNo) }}" class="w-full border-gray-200 rounded-lg focus:ring-orange-500 focus:border-orange-500">
            @error('phoneNo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
    </form>
</div>
@endsection