@extends('layouts.staff')
@section('title', 'Staff Management')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h3 class="text-xl font-bold text-gray-800">All Staff Members</h3>
    <a href="{{ route('staff.management.create') }}" class="bg-orange-600 text-white px-4 py-2 rounded-lg font-bold hover:bg-orange-700 transition shadow-lg shadow-orange-500/30">
        <i class="fas fa-plus mr-2"></i> Add New Staff
    </a>
</div>

@if(session('success'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
        <p>{{ session('success') }}</p>
    </div>
@endif

@if(session('error'))
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
        <p>{{ session('error') }}</p>
    </div>
@endif

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-left text-sm text-gray-600">
        <thead class="bg-gray-50 text-gray-800 font-bold uppercase text-xs border-b border-gray-100">
            <tr>
                <th class="px-6 py-4">Name</th>
                <th class="px-6 py-4">Role</th>
                <th class="px-6 py-4">Status</th>
                <th class="px-6 py-4 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @foreach($staffMembers as $member)
            <tr class="hover:bg-gray-50 transition">
                <td class="px-6 py-4">
                    <div class="font-bold text-gray-900">{{ $member->name }}</div>
                    <div class="text-xs text-gray-400">{{ $member->email }}</div>
                </td>
                <td class="px-6 py-4">
                    <span class="px-3 py-1 rounded-full text-xs font-bold {{ $member->role === 'admin' ? 'bg-purple-100 text-purple-600' : 'bg-blue-100 text-blue-600' }}">
                        {{ ucfirst($member->role) }}
                    </span>
                </td>
                <td class="px-6 py-4">
                    <span class="inline-flex items-center">
                        <span class="h-2 w-2 rounded-full {{ $member->active ? 'bg-green-500' : 'bg-red-500' }} mr-2"></span>
                        {{ $member->active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td class="px-6 py-4 text-right space-x-2">
                    <a href="{{ route('staff.management.edit', $member->staffID) }}" class="text-blue-500 hover:text-blue-700 font-bold">Edit</a>
                    
                    @if(Auth::guard('staff')->id() !== $member->staffID)
                    <form action="{{ route('staff.management.destroy', $member->staffID) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this staff member?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-500 hover:text-red-700 font-bold">Delete</button>
                    </form>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="p-4">
        {{ $staffMembers->links() }}
    </div>
</div>
@endsection