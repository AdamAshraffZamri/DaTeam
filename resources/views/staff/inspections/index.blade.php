@extends('layouts.staff')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-black text-gray-900">Inspection Mode</h1>
        <a href="{{ route('staff.dashboard') }}" class="text-gray-500 hover:text-orange-600 font-bold text-sm">
            <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="bg-blue-50 px-6 py-4 border-b border-blue-100 flex justify-between items-center">
                <h3 class="font-bold text-blue-800 flex items-center">
                    <i class="fas fa-car-side mr-2"></i> To Pickup (Departures)
                </h3>
                <span class="bg-blue-200 text-blue-800 text-xs font-black px-2 py-1 rounded-lg">{{ $toPickup->count() }}</span>
            </div>
            
            <div class="divide-y divide-gray-100">
                @forelse($toPickup as $booking)
                <div class="p-6 hover:bg-gray-50 transition group">
                    <div class="flex justify-between items-start mb-2">
                        <span class="font-mono text-xs text-gray-400">#{{ $booking->bookingID }}</span>
                        <span class="text-xs font-bold text-gray-500">{{ \Carbon\Carbon::parse($booking->originalDate)->format('d M, h:i A') }}</span>
                    </div>
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <p class="font-bold text-gray-900">{{ $booking->customer->fullName }}</p>
                            <p class="text-sm text-gray-500">{{ $booking->vehicle->model }} <span class="text-gray-300">|</span> {{ $booking->vehicle->plateNo }}</p>
                        </div>
                    </div>
                    <a href="{{ route('staff.inspections.create', $booking->bookingID) }}" class="block w-full text-center bg-blue-600 text-white py-2 rounded-lg font-bold text-sm hover:bg-blue-700 transition shadow-lg shadow-blue-500/30">
                        Start Pickup Inspection
                    </a>
                </div>
                @empty
                <div class="p-8 text-center text-gray-400 text-sm">No pending pickups.</div>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="bg-orange-50 px-6 py-4 border-b border-orange-100 flex justify-between items-center">
                <h3 class="font-bold text-orange-800 flex items-center">
                    <i class="fas fa-undo mr-2"></i> To Return (Arrivals)
                </h3>
                <span class="bg-orange-200 text-orange-800 text-xs font-black px-2 py-1 rounded-lg">{{ $toReturn->count() }}</span>
            </div>

            <div class="divide-y divide-gray-100">
                @forelse($toReturn as $booking)
                <div class="p-6 hover:bg-gray-50 transition group">
                    <div class="flex justify-between items-start mb-2">
                        <span class="font-mono text-xs text-gray-400">#{{ $booking->bookingID }}</span>
                        <span class="text-xs font-bold text-orange-500">Due: {{ \Carbon\Carbon::parse($booking->returnDate)->format('d M, h:i A') }}</span>
                    </div>
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center text-orange-600">
                            <i class="fas fa-car"></i>
                        </div>
                        <div>
                            <p class="font-bold text-gray-900">{{ $booking->vehicle->model }} ({{ $booking->vehicle->plateNo }})</p>
                            <p class="text-sm text-gray-500">{{ $booking->customer->fullName }}</p>
                        </div>
                    </div>
                    <a href="{{ route('staff.inspections.create', $booking->bookingID) }}" class="block w-full text-center bg-gray-900 text-white py-2 rounded-lg font-bold text-sm hover:bg-gray-800 transition shadow-lg shadow-gray-500/30">
                        Start Return Inspection
                    </a>
                </div>
                @empty
                <div class="p-8 text-center text-gray-400 text-sm">No pending returns.</div>
                @endforelse
            </div>
        </div>

    </div>
</div>
@endsection