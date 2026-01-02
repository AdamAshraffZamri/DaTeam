@extends('layouts.staff')
@section('title', 'Add New Staff')

@section('content')
<div class="max-w-2xl mx-auto bg-white p-8 rounded-xl shadow-sm border border-gray-100">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Create Account</h2>
    
    <form action="{{ route('staff.management.store') }}" method="POST" class="space-y-6">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Full Name</label>
                <input type="text" name="name" value="{{ old('name') }}" class="w-full border-gray-200 rounded-lg focus:ring-orange-500 focus:border-orange-500" required>
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" class="w-full border-gray-200 rounded-lg focus:ring-orange-500 focus:border-orange-500" required>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Phone Number</label>
                <input type="text" name="phoneNo" value="{{ old('phoneNo') }}" class="w-full border-gray-200 rounded-lg focus:ring-orange-500 focus:border-orange-500">
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Role</label>
                <select name="role" class="w-full border-gray-200 rounded-lg focus:ring-orange-500 focus:border-orange-500">
                    <option value="staff">Normal Staff</option>
                    <option value="admin">Administrator</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Password</label>
                <input type="password" name="password" class="w-full border-gray-200 rounded-lg focus:ring-orange-500 focus:border-orange-500" required>
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Confirm Password</label>
                <input type="password" name="password_confirmation" class="w-full border-gray-200 rounded-lg focus:ring-orange-500 focus:border-orange-500" required>
            </div>
        </div>

        <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-50">
            <a href="{{ route('staff.management.index') }}" class="text-gray-500 font-bold hover:text-gray-700">Cancel</a>
            <button type="submit" class="bg-orange-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-orange-700 transition shadow-lg shadow-orange-500/30">
                Create Account
            </button>
        </div>
    </form>
</div>
@endsection