@extends('layouts.staff')

@section('title', 'Overview')

@section('content')

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition">
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 bg-orange-50 rounded-2xl text-orange-500">
                    <i class="fas fa-car text-xl"></i>
                </div>
                <span class="bg-green-100 text-green-700 text-xs font-bold px-2 py-1 rounded-lg">+3</span>
            </div>
            <div>
                <h3 class="text-3xl font-black text-gray-800">{{ $activeRentals }}</h3>
                <p class="text-sm font-medium text-gray-400 mt-1">Cars Rented Out</p>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition">
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 bg-blue-50 rounded-2xl text-blue-500">
                    <i class="fas fa-clipboard-check text-xl"></i>
                </div>
                @if($pendingCount > 0)
                <span class="bg-red-100 text-red-700 text-xs font-bold px-2 py-1 rounded-lg">-2</span>
                @endif
            </div>
            <div>
                <h3 class="text-3xl font-black text-gray-800">{{ $pendingCount }}</h3>
                <p class="text-sm font-medium text-gray-400 mt-1">Pending Approvals</p>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition">
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 bg-green-50 rounded-2xl text-green-500">
                    <i class="fas fa-dollar-sign text-xl"></i>
                </div>
                <span class="bg-green-100 text-green-700 text-xs font-bold px-2 py-1 rounded-lg">+15%</span>
            </div>
            <div>
                <h3 class="text-3xl font-black text-gray-800">RM {{ number_format($revenue, 0) }}</h3>
                <p class="text-sm font-medium text-gray-400 mt-1">Total Revenue</p>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition">
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 bg-red-50 rounded-2xl text-red-500">
                    <i class="fas fa-exclamation-triangle text-xl"></i>
                </div>
                @if($overdueCount > 0)
                <span class="bg-red-100 text-red-700 text-xs font-bold px-2 py-1 rounded-lg">+1</span>
                @endif
            </div>
            <div>
                <h3 class="text-3xl font-black text-gray-800">{{ $overdueCount }}</h3>
                <p class="text-sm font-medium text-gray-400 mt-1">Overdue Returns</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-2">
            <h3 class="font-bold text-lg text-gray-800 mb-6">Quick Actions</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <a href="{{ route('staff.bookings.index') }}" class="bg-white p-6 rounded-2xl border border-gray-100 flex items-center hover:shadow-md transition cursor-pointer group">
                    <div class="w-12 h-12 bg-orange-50 rounded-xl flex items-center justify-center text-orange-500 group-hover:scale-110 transition">
                        <i class="fas fa-calendar-alt text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h4 class="font-bold text-gray-900">Manage Bookings</h4>
                        <p class="text-xs text-gray-400 mt-1">View and approve requests</p>
                    </div>
                </a>

                <div class="bg-white p-6 rounded-2xl border border-gray-100 flex items-center hover:shadow-md transition cursor-pointer group">
                    <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center text-blue-500 group-hover:scale-110 transition">
                        <i class="fas fa-plus text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h4 class="font-bold text-gray-900">Add Vehicle</h4>
                        <p class="text-xs text-gray-400 mt-1">Register new car to fleet</p>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <h3 class="font-bold text-lg text-gray-800 mb-6 flex items-center">
                <i class="far fa-clock text-orange-500 mr-2"></i> Pending Returns
            </h3>
            
            <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm h-full">
                <div class="space-y-4">
                    @forelse($dueReturns as $rental)
                        @php
                            $returnDate = \Carbon\Carbon::parse($rental->returnDate);
                            $isLate = $returnDate->isPast();
                        @endphp

                        <div class="p-4 border border-gray-50 rounded-xl flex justify-between items-center hover:bg-gray-50 transition">
                            <div>
                                <h4 class="font-bold text-sm text-gray-800">{{ $rental->vehicle->model }}</h4>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $rental->vehicle->plateNo }}</p>
                            </div>
                            
                            @if($isLate)
                                <span class="bg-red-100 text-red-700 text-[10px] font-bold px-2 py-1 rounded">Late 1h</span>
                            @else
                                <span class="bg-orange-100 text-orange-700 text-[10px] font-bold px-2 py-1 rounded">Today 5PM</span>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-6 text-gray-400 text-sm">
                            <i class="fas fa-check-circle mb-2 text-2xl text-green-100 block"></i>
                            No returns pending.
                        </div>
                    @endforelse
                </div>
                
                <a href="{{ route('staff.bookings.index') }}" class="block w-full mt-8 bg-orange-600 hover:bg-orange-700 text-white font-bold py-3 rounded-xl text-sm shadow-lg shadow-orange-500/30 transition text-center">
                    Process Return
                </a>
            </div>
        </div>

    </div>

@endsection