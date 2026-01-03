@extends('layouts.staff')

@section('content')
<div class="min-h-screen bg-gray-100 rounded-2xl p-6">
<style>
    /* ANIMATED BACKGROUND BLOBS */
    @keyframes blob {
        0% { transform: translate(0px, 0px) scale(1); }
        33% { transform: translate(30px, -50px) scale(1.1); }
        66% { transform: translate(-20px, 20px) scale(0.9); }
        100% { transform: translate(0px, 0px) scale(1); }
    }
    .animate-blob {
        animation: blob 7s infinite;
    }
    .animation-delay-2000 {
        animation-delay: 2s;
    }
    .animation-delay-4000 {
        animation-delay: 4s;
    }
    .glass-card1 {
        background: rgba(255, 169, 88, 0.35);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.5);
    }
    .glass-card {
        background: rgba(255, 255, 255, 0.65);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.5);
    }
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
</style>

{{-- BACKGROUND ELEMENTS --}}
<div class="fixed inset-0 -z-10 overflow-hidden pointer-events-none">
    <div class="absolute top-0 left-1/4 w-96 h-96 bg-orange-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob"></div>
    <div class="absolute top-0 right-1/4 w-96 h-96 bg-purple-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000"></div>
    <div class="absolute -bottom-32 left-1/3 w-96 h-96 bg-pink-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-4000"></div>
</div>

<div class="min-h-screen p-6">
    <div class="max-w-7xl mx-auto space-y-8">

        {{-- 1. HEADER SECTION --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
            <div>
                <h1 class="text-4xl font-black text-gray-900 tracking-tight">
                    Welcome back, <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-600 to-red-600">{{ Auth::user()->name }}</span>
                </h1>
                <p class="text-gray-500 mt-2 font-medium flex items-center gap-2">
                    <i class="far fa-calendar-alt"></i> {{ now()->format('l, d F Y') }}
                </p>
            </div>
            
            {{-- Quick Profile Action (Optional) --}}
            <div class="glass-card px-4 py-2 rounded-full shadow-sm flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-gray-900 text-white flex items-center justify-center font-bold text-xs">
                    {{ substr(Auth::user()->name, 0, 1) }}
                </div>
                <span class="text-xs font-bold text-gray-700 uppercase tracking-wider">{{ Auth::user()->role ?? 'Staff' }}</span>
            </div>
        </div>

        {{-- 2. KEY METRICS GRID --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            
            {{-- Active Rentals --}}
            <div class="glass-card1 p-6 rounded-[2rem] shadow-sm hover:shadow-lg transition-all duration-300 group">
                <div class="flex justify-between items-start mb-4">
                    <div class="p-3 bg-orange-100 rounded-2xl text-orange-600 group-hover:scale-110 transition-transform">
                        <i class="fas fa-car text-xl"></i>
                    </div>
                    <span class="bg-white/80 text-gray-500 text-[10px] font-bold px-2 py-1 rounded-full border border-gray-100 shadow-sm">Live</span>
                </div>
                <h3 class="text-4xl font-black text-gray-900 mb-1">{{ $activeRentals }}</h3>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Active Rentals</p>
            </div>

            {{-- Pending Approvals --}}
            <div class="glass-card1 p-6 rounded-[2rem] shadow-sm hover:shadow-lg transition-all duration-300 group">
                <div class="flex justify-between items-start mb-4">
                    <div class="p-3 bg-blue-100 rounded-2xl text-blue-600 group-hover:scale-110 transition-transform">
                        <i class="fas fa-clipboard-list text-xl"></i>
                    </div>
                    @if($pendingCount > 0)
                        <span class="bg-red-500 text-white text-[10px] font-bold px-2 py-1 rounded-full shadow-md animate-pulse">Action Needed</span>
                    @endif
                </div>
                <h3 class="text-4xl font-black text-gray-900 mb-1">{{ $pendingCount }}</h3>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Pending Approvals</p>
            </div>

            {{-- Total Revenue --}}
            <div class="glass-card1 p-6 rounded-[2rem] shadow-sm hover:shadow-lg transition-all duration-300 group">
                <div class="flex justify-between items-start mb-4">
                    <div class="p-3 bg-green-100 rounded-2xl text-green-600 group-hover:scale-110 transition-transform">
                        <i class="fas fa-wallet text-xl"></i>
                    </div>
                </div>
                <h3 class="text-3xl font-black text-gray-900 mb-1">RM {{ number_format($revenue, 0) }}</h3>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Revenue</p>
            </div>

            {{-- Overdue Returns --}}
            <div class="glass-card1 p-6 rounded-[2rem] shadow-sm hover:shadow-lg transition-all duration-300 group border-red-100">
                <div class="flex justify-between items-start mb-4">
                    <div class="p-3 bg-red-100 rounded-2xl text-red-600 group-hover:scale-110 transition-transform">
                        <i class="fas fa-exclamation-circle text-xl"></i>
                    </div>
                    @if($overdueCount > 0)
                        <span class="bg-red-100 text-red-600 text-[10px] font-bold px-2 py-1 rounded-full border border-red-200">Late</span>
                    @endif
                </div>
                <h3 class="text-4xl font-black text-gray-900 mb-1">{{ $overdueCount }}</h3>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Overdue Returns</p>
            </div>
        </div>

        {{-- 3. MAIN DASHBOARD CONTENT --}}
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            
            {{-- LEFT COLUMN: QUICK ACTIONS GRID (2/3) --}}
            <div class="xl:col-span-2 space-y-6">
                <h3 class="text-lg font-black text-gray-900 flex items-center gap-2">
                    <i class="fas fa-rocket text-orange-500"></i> Quick Management
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    
                    {{-- 1. Manage Bookings --}}
                    <a href="{{ route('staff.bookings.index') }}" class="glass-card p-6 rounded-[2rem] hover:bg-white transition-all group relative overflow-hidden">
                        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                            <i class="fas fa-calendar-check text-6xl text-gray-900"></i>
                        </div>
                        <div class="w-12 h-12 bg-gray-900 rounded-2xl flex items-center justify-center text-white mb-4 shadow-lg shadow-gray-900/20 group-hover:scale-110 transition-transform">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <h4 class="text-lg font-bold text-gray-900">Booking Requests</h4>
                        <p class="text-xs text-gray-500 mt-1">Review & approve customer bookings</p>
                    </a>

                    {{-- 2. Fleet Management --}}
                    <a href="{{ route('staff.fleet.index') }}" class="glass-card p-6 rounded-[2rem] hover:bg-white transition-all group relative overflow-hidden">
                        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                            <i class="fas fa-car text-6xl text-orange-600"></i>
                        </div>
                        <div class="w-12 h-12 bg-orange-500 rounded-2xl flex items-center justify-center text-white mb-4 shadow-lg shadow-orange-500/30 group-hover:scale-110 transition-transform">
                            <i class="fas fa-car-side"></i>
                        </div>
                        <h4 class="text-lg font-bold text-gray-900">Fleet Inventory</h4>
                        <p class="text-xs text-gray-500 mt-1">Manage vehicles & availability status</p>
                    </a>

                    {{-- 3. Customers --}}
                    <a href="{{ route('staff.customers.index') }}" class="glass-card p-6 rounded-[2rem] hover:bg-white transition-all group relative overflow-hidden">
                        <div class="w-12 h-12 bg-white border-2 border-gray-100 rounded-2xl flex items-center justify-center text-gray-600 mb-4 group-hover:border-blue-200 group-hover:text-blue-600 transition-colors">
                            <i class="fas fa-users"></i>
                        </div>
                        <h4 class="text-base font-bold text-gray-900">Customer Database</h4>
                        <p class="text-xs text-gray-500 mt-1">View profiles & history</p>
                    </a>

                    {{-- 4. Loyalty & Vouchers --}}
                    <a href="{{ route('staff.loyalty.index') }}" class="glass-card p-6 rounded-[2rem] hover:bg-white transition-all group relative overflow-hidden">
                        <div class="w-12 h-12 bg-white border-2 border-gray-100 rounded-2xl flex items-center justify-center text-gray-600 mb-4 group-hover:border-purple-200 group-hover:text-purple-600 transition-colors">
                            <i class="fas fa-gift"></i>
                        </div>
                        <h4 class="text-base font-bold text-gray-900">Rewards & Vouchers</h4>
                        <p class="text-xs text-gray-500 mt-1">Manage points & promos</p>
                    </a>

                    {{-- 5. Inspections --}}
                    <a href="{{ route('staff.bookings.index') }}" class="glass-card p-6 rounded-[2rem] hover:bg-white transition-all group relative overflow-hidden sm:col-span-2 flex items-center gap-4">
                        <div class="w-12 h-12 bg-white border-2 border-gray-100 rounded-2xl flex items-center justify-center text-gray-600 group-hover:border-green-200 group-hover:text-green-600 transition-colors shrink-0">
                            <i class="fas fa-camera"></i>
                        </div>
                        <div>
                            <h4 class="text-base font-bold text-gray-900">Vehicle Inspections</h4>
                            <p class="text-xs text-gray-500 mt-0.5">Upload photos for Pickup & Return conditions</p>
                        </div>
                    </a>

                </div>

                {{-- Primary Action Button --}}
                <a href="{{ route('staff.fleet.create') }}" class="block w-full bg-gray-900 hover:bg-black text-white font-bold py-5 rounded-[2rem] text-center shadow-xl hover:shadow-2xl hover:shadow-gray-900/20 transition-all transform hover:scale-[1.01]">
                    <i class="fas fa-plus-circle mr-2"></i> Register New Vehicle
                </a>
            </div>

            {{-- RIGHT COLUMN: PENDING RETURNS (1/3) --}}
            <div class="xl:col-span-1">
                <div class="glass-card rounded-[2.5rem] p-8 h-full flex flex-col">
                    <h3 class="font-bold text-lg text-gray-900 mb-6 flex items-center justify-between">
                        <span>Pending Returns</span>
                        <span class="bg-white px-2 py-1 rounded-lg text-xs font-bold border border-gray-100 shadow-sm">{{ $dueReturns->count() }}</span>
                    </h3>

                    <div class="space-y-4 flex-1 overflow-y-auto no-scrollbar max-h-[500px]">
                        @forelse($dueReturns as $rental)
                            @php
                                $returnDate = \Carbon\Carbon::parse($rental->returnDate);
                                $isLate = $returnDate->isPast();
                            @endphp

                            <div class="bg-white/80 p-4 rounded-2xl border border-white shadow-sm hover:shadow-md transition-all group">
                                <div class="flex justify-between items-start mb-2">
                                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Booking #{{ $rental->bookingID }}</span>
                                    @if($isLate)
                                        <span class="bg-red-50 text-red-600 text-[10px] font-bold px-2 py-0.5 rounded border border-red-100 animate-pulse">LATE</span>
                                    @else
                                        <span class="bg-orange-50 text-orange-600 text-[10px] font-bold px-2 py-0.5 rounded border border-orange-100">Due Soon</span>
                                    @endif
                                </div>
                                
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center text-gray-500">
                                        <i class="fas fa-car"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-gray-900 text-sm leading-tight">{{ $rental->vehicle->model ?? 'Unknown' }}</h4>
                                        <p class="text-xs font-mono font-bold text-gray-500 mt-0.5">{{ $rental->vehicle->plateNo ?? '---' }}</p>
                                    </div>
                                </div>

                                <div class="flex justify-between items-end border-t border-gray-100 pt-3">
                                    <div>
                                        <p class="text-[9px] text-gray-400 font-bold uppercase">Customer</p>
                                        <p class="text-xs font-bold text-gray-700 truncate max-w-[100px]">{{ $rental->customer->fullName ?? 'Guest' }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-[9px] text-gray-400 font-bold uppercase">Return Time</p>
                                        <p class="text-xs font-bold {{ $isLate ? 'text-red-600' : 'text-gray-900' }}">
                                            {{ $returnDate->format('d M, h:i A') }}
                                        </p>
                                    </div>
                                </div>
                                
                                {{-- Quick Action to View Booking --}}
                                <a href="{{ route('staff.bookings.show', $rental->bookingID) }}" class="mt-3 block w-full py-2 bg-gray-50 hover:bg-gray-900 hover:text-white text-gray-600 text-xs font-bold rounded-xl text-center transition-colors">
                                    View Details
                                </a>
                            </div>
                        @empty
                            <div class="flex flex-col items-center justify-center py-10 text-center opacity-60">
                                <div class="w-16 h-16 bg-green-50 rounded-full flex items-center justify-center mb-3">
                                    <i class="fas fa-check text-green-500 text-xl"></i>
                                </div>
                                <p class="text-sm font-bold text-gray-500">All caught up!</p>
                                <p class="text-xs text-gray-400 mt-1">No pending returns at the moment.</p>
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-6 pt-6 border-t border-gray-100/50">
                        <a href="{{ route('staff.bookings.index') }}" class="flex items-center justify-center gap-2 w-full py-4 bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-2xl shadow-lg shadow-orange-500/30 transition-all transform hover:scale-[1.02]">
                            <span>Process A Return</span>
                            <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
</div>
@endsection