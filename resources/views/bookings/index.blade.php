@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-orange-50/50">
    <div class="container mx-auto px-4 py-8 max-w-7xl">
        
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">My Bookings</h1>
            <p class="text-gray-500 mt-2">Manage and track your car rentals</p>
        </div>

        <div class="flex justify-center mb-8 overflow-x-auto pb-4">
            <div class="bg-white rounded-full p-1 shadow-sm flex space-x-1 border border-gray-200 whitespace-nowrap">
                @foreach(['Draft', 'Submitted', 'To Pay', 'Confirmed', 'Active Rental', 'Completed', 'Cancelled'] as $status)
                <button class="px-6 py-2 rounded-full text-sm font-bold {{ $status == 'Submitted' ? 'bg-[#ff7f50] text-white shadow-md' : 'text-gray-500 hover:bg-gray-100 transition' }}">
                    {{ $status }}
                </button>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            
            @forelse($bookings as $booking)
            <div class="bg-[#8b8682] rounded-[30px] p-6 shadow-xl text-white relative flex flex-col h-full border-4 border-white/20 hover:border-orange-400/50 transition duration-300">
                
                <div class="bg-white rounded-2xl p-4 mb-4 h-48 flex items-center justify-center shadow-inner relative overflow-hidden group">
                    <img src="{{ $booking->vehicle->image }}" class="max-w-full max-h-full object-contain transform group-hover:scale-110 transition duration-500">
                </div>

                <div class="space-y-4 flex-grow text-sm">
                    <div class="flex justify-between items-center border-b border-white/30 pb-2">
                        <span class="font-medium text-white/80">Car Model:</span>
                        <span class="font-bold tracking-wide text-lg">{{ $booking->vehicle->model }}</span>
                    </div>

                    <div>
                        <div class="flex justify-between items-center">
                            <span class="font-medium text-white/80">Pickup Date:</span>
                            <span class="font-bold">{{ \Carbon\Carbon::parse($booking->start_date)->format('d-m-Y') }}</span>
                        </div>
                        <div class="flex justify-between items-start mt-1">
                            <span class="font-medium text-white/80 whitespace-nowrap mr-2">Pickup Location:</span>
                            <span class="font-bold text-right leading-tight text-orange-200">{{ $booking->pickup_location }}</span>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center">
                            <span class="font-medium text-white/80">Return Date:</span>
                            <span class="font-bold">{{ \Carbon\Carbon::parse($booking->end_date)->format('d-m-Y') }}</span>
                        </div>
                        <div class="flex justify-between items-start mt-1">
                            <span class="font-medium text-white/80 whitespace-nowrap mr-2">Return Location:</span>
                            <span class="font-bold text-right leading-tight text-orange-200">{{ $booking->return_location }}</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mt-6 pt-4 border-t border-white/20">
                    <button class="bg-[#d1d1d1] hover:bg-white text-gray-800 font-bold py-2 rounded-full shadow-md transition transform hover:scale-105 active:scale-95">
                        Edit
                    </button>
                    
                    @php
                        $statusColor = match($booking->booking_status) {
                            'Submitted' => 'bg-[#66ff66] hover:bg-[#52e052]',
                            'Confirmed' => 'bg-blue-400 hover:bg-blue-500 text-white',
                            'Cancelled' => 'bg-red-400 hover:bg-red-500 text-white',
                            'Completed' => 'bg-gray-800 text-white',
                            default => 'bg-yellow-400 hover:bg-yellow-500'
                        };
                    @endphp
                    
                    <button class="{{ $statusColor }} text-black font-bold py-2 rounded-full shadow-md transition transform hover:scale-105 cursor-default capitalize">
                        {{ $booking->booking_status }}
                    </button>
                </div>

            </div>
            @empty
            <div class="col-span-1 md:col-span-2 lg:col-span-3 flex flex-col items-center justify-center py-20 text-center">
                <div class="bg-orange-100 rounded-full p-6 mb-4">
                    <i class="fas fa-car-side text-4xl text-orange-400"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-800">No bookings yet</h3>
                <p class="text-gray-500 mb-6">You haven't made any car rental bookings yet.</p>
                <a href="{{ route('book.create') }}" class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 px-8 rounded-full shadow-lg transition transform hover:scale-105">
                    Book a Car Now
                </a>
            </div>
            @endforelse

        </div>
    </div>
</div>

@if(session('show_thank_you'))
<div class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm px-4" id="thankYouModal">
    <div class="bg-white rounded-[30px] p-8 max-w-md w-full text-center shadow-2xl transform scale-100 transition-all duration-300 animate-bounce-in">
        
        <div class="mx-auto mb-6 w-20 h-20 bg-green-100 rounded-full flex items-center justify-center ring-4 ring-green-50">
            <i class="fas fa-check text-4xl text-green-600"></i>
        </div>

        <h2 class="text-3xl font-bold text-gray-900 mb-2">Success!</h2>
        
        <p class="text-gray-600 mb-8 text-lg leading-relaxed">
            Your booking has been submitted.<br>
            Please wait for staff verification.
        </p>

        <button onclick="document.getElementById('thankYouModal').remove()" 
                class="w-full bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 px-10 rounded-xl shadow-lg transition transform hover:scale-105">
            Okay, got it!
        </button>
    </div>
</div>
@endif

@endsection