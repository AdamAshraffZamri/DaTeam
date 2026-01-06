@extends('layouts.staff')

@section('content')
<div class="min-h-screen bg-gray-100 rounded-2xl p-6">
    <div class="max-w-7xl mx-auto">

        {{-- HEADER & ACTIONS --}}
        <div class="flex flex-col md:flex-row justify-between items-end md:items-center mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-black text-gray-900">Staff Management</h1>
                <p class="text-gray-500 mt-1 text-sm">Manage administrative access and roles.</p>
            </div>

            <div class="flex gap-3">
                {{-- Search Form --}}
                <form action="{{ route('staff.management.index') }}" method="GET" class="relative group w-full md:w-64">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Search staff..." 
                           class="w-full pl-10 pr-4 py-3.5 rounded-2xl border border-gray-200 bg-white text-sm font-bold text-gray-700 focus:ring-2 focus:ring-gray-900 focus:border-transparent transition-all shadow-sm group-hover:border-gray-300">
                    <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 group-hover:text-gray-600 transition-colors"></i>
                </form>

                {{-- Add Button --}}
                <a href="{{ route('staff.management.create') }}" class="bg-gray-900 hover:bg-orange-600 text-white px-6 py-3.5 rounded-2xl font-bold text-xs shadow-lg transition-all transform hover:scale-105 flex items-center gap-2 shrink-0 whitespace-nowrap">
                    <i class="fas fa-plus"></i>
                    <span>Add Staff</span>
                </a>
            </div>
        </div>

        {{-- ALERTS --}}
        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 rounded-2xl p-4 flex items-center gap-4 shadow-sm animate-fade-in">
                <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-600 shrink-0 border border-green-200">
                    <i class="fas fa-check text-sm"></i>
                </div>
                <div>
                    <h4 class="text-sm font-black text-green-900">Success</h4>
                    <p class="text-xs text-green-700 font-medium">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 rounded-2xl p-4 flex items-center gap-4 shadow-sm animate-fade-in">
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-red-600 shrink-0 border border-red-200">
                    <i class="fas fa-exclamation text-sm"></i>
                </div>
                <div>
                    <h4 class="text-sm font-black text-red-900">Error</h4>
                    <p class="text-xs text-red-700 font-medium">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        {{-- STAFF LIST --}}
        <div class="space-y-3">
            @forelse($staffMembers as $member)
            <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm hover:shadow-md transition-all group animate-fade-in hover:border-orange-200">
                <div class="flex flex-col md:flex-row items-center gap-4">
                    
                    {{-- 1. AVATAR & INFO --}}
                    <div class="flex items-center gap-4 w-full md:w-[35%] shrink-0">
                        <div class="w-12 h-12 rounded-xl bg-gray-50 border border-gray-200 flex items-center justify-center text-gray-600 font-black text-lg shadow-sm shrink-0">
                            {{ substr($member->name, 0, 1) }}
                        </div>
                        <div class="overflow-hidden">
                            <h4 class="text-sm font-bold text-gray-900 truncate">{{ $member->name }}</h4>
                            <p class="text-[11px] font-medium text-gray-400 truncate">{{ $member->email }}</p>
                        </div>
                    </div>

                    {{-- 2. ROLE --}}
                    <div class="w-full md:w-[20%] border-t md:border-t-0 md:border-l border-gray-100 pt-2 md:pt-0 md:pl-6 shrink-0">
                        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-1">Role</p>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wide border {{ $member->role === 'admin' ? 'bg-purple-50 text-purple-700 border-purple-100' : 'bg-blue-50 text-blue-700 border-blue-100' }}">
                            {{ ucfirst($member->role) }}
                        </span>
                    </div>

                    {{-- 3. STATUS --}}
                    <div class="w-full md:w-[20%] border-t md:border-t-0 md:border-l border-gray-100 pt-2 md:pt-0 md:pl-6 shrink-0">
                        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-1">Status</p>
                        @if($member->active)
                            <div class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                <span class="text-xs font-bold text-gray-700">Active</span>
                            </div>
                        @else
                            <div class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                <span class="text-xs font-bold text-gray-400">Inactive</span>
                            </div>
                        @endif
                    </div>

                    {{-- 4. ACTIONS --}}
                    <div class="w-full md:flex-1 flex justify-end items-center gap-2 pt-2 md:pt-0 border-t md:border-t-0 border-gray-100 md:pl-6">
                        <a href="{{ route('staff.management.edit', $member->staffID) }}" 
                           class="w-9 h-9 rounded-lg flex items-center justify-center text-gray-400 hover:text-blue-600 hover:bg-blue-50 border border-transparent hover:border-blue-100 transition-all"
                           title="Edit Staff">
                            <i class="fas fa-edit text-xs"></i>
                        </a>

                        @if(Auth::guard('staff')->id() !== $member->staffID)
                            <form action="{{ route('staff.management.destroy', $member->staffID) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this staff member?');">
                                @csrf @method('DELETE')
                                <button type="submit" 
                                        class="w-9 h-9 rounded-lg flex items-center justify-center text-gray-400 hover:text-red-600 hover:bg-red-50 border border-transparent hover:border-red-100 transition-all"
                                        title="Delete Staff">
                                    <i class="fas fa-trash-alt text-xs"></i>
                                </button>
                            </form>
                        @else
                            <div class="w-9 h-9 flex items-center justify-center text-gray-200 cursor-not-allowed" title="Current User">
                                <i class="fas fa-user-lock text-xs"></i>
                            </div>
                        @endif
                    </div>

                </div>
            </div>
            @empty
            <div class="flex flex-col items-center justify-center py-20 text-center animate-fade-in">
                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4 border border-gray-100">
                    <i class="fas fa-users-slash text-gray-300 text-2xl"></i>
                </div>
                <p class="text-gray-500 font-medium">No staff members found.</p>
                @if(request('search'))
                    <a href="{{ route('staff.management.index') }}" class="mt-4 text-xs font-bold text-orange-600 hover:text-orange-500">Clear Search</a>
                @endif
            </div>
            @endforelse
        </div>

        {{-- PAGINATION --}}
        <div class="mt-6 px-2">
            {{ $staffMembers->links() }}
        </div>

    </div>
</div>

<style>
    @keyframes fade-in { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
    .animate-fade-in { animation: fade-in 0.2s ease-out forwards; }
</style>
@endsection