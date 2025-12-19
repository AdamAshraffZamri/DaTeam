@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50/50 relative">
    <div class="absolute top-0 left-0 w-full h-96 bg-gradient-to-b from-orange-100/50 to-transparent -z-10"></div>

    <div class="container mx-auto px-4 py-12 max-w-7xl">
        
        <div class="flex flex-col md:flex-row justify-between items-end mb-10 gap-4">
            <div>
                <h1 class="text-4xl font-extrabold text-gray-900 tracking-tight">My Bookings</h1>
                <p class="text-gray-500 mt-2 text-lg">Track your current and past rentals</p>
            </div>
            
            <a href="{{ route('home') }}" class="group flex items-center bg-gray-900 hover:bg-orange-600 text-white px-6 py-3 rounded-full font-bold transition-all duration-300 shadow-lg hover:shadow-orange-500/30">
                <i class="fas fa-plus mr-2 transition-transform group-hover:rotate-90"></i>
                New Booking
            </a>
        </div>

        <div class="mb-10 overflow-x-auto pb-2 -mx-4 px-4 scrollbar-hide">
            <div class="inline-flex space-x-2 bg-white p-1.5 rounded-full shadow-sm border border-gray-200">
                @foreach(['Submitted', 'Confirmed', 'Active', 'Completed', 'Cancelled'] as $status)
                <button class="px-5 py-2.5 rounded-full text-sm font-bold whitespace-nowrap transition-all duration-200 text-gray-500 hover:bg-gray-100 hover:text-gray-900">
                    {{ $status }}
                </button>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            
            @forelse($bookings as $booking)
            <div class="group bg-white rounded-[2rem] p-6 shadow-sm hover:shadow-2xl border border-gray-100 transition-all duration-300 hover:-translate-y-1 relative overflow-hidden">
                
                @php
                    $badgeColor = match($booking->bookingStatus) {
                        'Submitted' => 'bg-blue-50 text-blue-600 border-blue-100',
                        'Confirmed' => 'bg-green-50 text-green-600 border-green-100',
                        'Cancelled' => 'bg-red-50 text-red-600 border-red-100',
                        'Completed' => 'bg-gray-100 text-gray-600 border-gray-200',
                        default => 'bg-orange-50 text-orange-600 border-orange-100'
                    };
                @endphp
                <div class="absolute top-6 right-6">
                    <span class="{{ $badgeColor }} border px-4 py-1.5 rounded-full text-xs font-extrabold uppercase tracking-wider">
                        {{ $booking->bookingStatus }}
                    </span>
                </div>

                <div class="mt-4 mb-6 relative h-40 flex items-center justify-center">
                    <div class="absolute inset-0 bg-gradient-to-tr from-gray-50 to-transparent rounded-2xl -z-10"></div>
                    @if($booking->vehicle && $booking->vehicle->image)
                        <img src="{{ asset('storage/' . $booking->vehicle->image) }}" class="max-h-full max-w-full object-contain drop-shadow-xl transform group-hover:scale-110 transition duration-500 ease-in-out">
                    @else
                        <i class="fas fa-car text-6xl text-gray-200"></i>
                    @endif
                </div>

                <div class="mb-6">
                    <h3 class="text-xl font-black text-gray-900 leading-tight">{{ $booking->vehicle->model ?? 'Unknown Vehicle' }}</h3>
                    <p class="text-sm font-medium text-gray-400 mt-1 flex items-center">
                        <i class="fas fa-hashtag text-xs mr-1 opacity-50"></i> {{ $booking->vehicle->plateNo ?? 'N/A' }}
                    </p>
                </div>

                <div class="relative pl-4 border-l-2 border-dashed border-gray-200 space-y-8 mb-8">
                    <div class="relative">
                        <div class="absolute -left-[21px] top-1 h-4 w-4 rounded-full bg-white border-4 border-green-500 shadow-sm"></div>
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-0.5">Pickup</p>
                            <p class="text-sm font-bold text-gray-900">
                                {{ \Carbon\Carbon::parse($booking->originalDate)->format('D, d M Y') }}
                            </p>
                            <p class="text-xs font-medium text-gray-500 mt-0.5 truncate max-w-[200px]">
                                <i class="fas fa-map-marker-alt mr-1 text-gray-300"></i> {{ $booking->pickupLocation }}
                            </p>
                        </div>
                    </div>

                    <div class="relative">
                        <div class="absolute -left-[21px] top-1 h-4 w-4 rounded-full bg-white border-4 border-red-500 shadow-sm"></div>
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-0.5">Return</p>
                            <p class="text-sm font-bold text-gray-900">
                                {{ \Carbon\Carbon::parse($booking->returnDate)->format('D, d M Y') }}
                            </p>
                            <p class="text-xs font-medium text-gray-500 mt-0.5 truncate max-w-[200px]">
                                <i class="fas fa-flag-checkered mr-1 text-gray-300"></i> {{ $booking->returnLocation }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 pt-6 border-t border-gray-50">
                    
                    @if($booking->bookingStatus == 'Submitted')
                        <form action="{{ route('book.cancel', $booking->bookingID) }}" method="POST" onsubmit="return confirm('Cancel this booking?')" class="w-full">
                            @csrf
                            <button type="submit" class="flex items-center justify-center w-full py-3 rounded-xl font-bold text-sm text-red-600 bg-red-50 hover:bg-red-100 transition">
                                Cancel
                            </button>
                        </form>

                    @elseif($booking->bookingStatus == 'Confirmed')
                        <a href="{{ route('book.agreement', $booking->bookingID) }}" target="_blank" class="flex items-center justify-center w-full py-3 rounded-xl font-bold text-sm text-green-700 bg-green-50 hover:bg-green-100 transition shadow-sm border border-green-100">
                            <i class="fas fa-file-contract mr-2"></i> Agreement
                        </a>

                    @else
                        <button disabled class="flex items-center justify-center w-full py-3 rounded-xl font-bold text-sm text-gray-400 bg-gray-50 cursor-not-allowed">
                            Edit
                        </button>
                    @endif

                    <button class="flex items-center justify-center w-full py-3 rounded-xl font-bold text-sm text-white bg-black hover:bg-orange-600 transition shadow-lg hover:shadow-orange-500/25">
                        RM {{ $booking->totalCost }}
                    </button>
                </div>

            </div>
            @empty
            <div class="col-span-1 md:col-span-2 lg:col-span-3 flex flex-col items-center justify-center py-24 text-center">
                <div class="w-24 h-24 bg-orange-50 rounded-full flex items-center justify-center mb-6 animate-pulse">
                    <i class="fas fa-car text-4xl text-orange-400"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-900">No bookings yet</h3>
                <p class="text-gray-500 mt-2 mb-8 max-w-sm mx-auto">Ready to hit the road? Start your journey by finding the perfect car for your trip.</p>
                <a href="{{ route('home') }}" class="bg-orange-500 hover:bg-orange-600 text-white px-8 py-4 rounded-full font-bold shadow-xl hover:shadow-2xl hover:-translate-y-1 transition-all duration-300">
                    Book a Car Now
                </a>
            </div>
            @endforelse

        </div>
    </div>
</div>

@if(session('show_thank_you'))
<div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm px-4" id="thankYouModal">
    <div class="bg-white rounded-[2rem] p-10 max-w-md w-full text-center shadow-2xl transform scale-100 transition-all animate-bounce-in relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-32 bg-gradient-to-b from-green-50 to-transparent -z-10"></div>
        <div class="mx-auto mb-6 w-20 h-20 bg-green-100 rounded-full flex items-center justify-center shadow-inner">
            <i class="fas fa-check text-4xl text-green-600"></i>
        </div>
        <h2 class="text-3xl font-extrabold text-gray-900 mb-3">All Set!</h2>
        <p class="text-gray-500 mb-8 text-lg leading-relaxed">
            Your booking has been successfully submitted. We're reviewing it now.
        </p>
        <button onclick="document.getElementById('thankYouModal').remove()" 
                class="w-full bg-gray-900 hover:bg-gray-800 text-white font-bold py-4 px-10 rounded-xl shadow-xl transition transform hover:scale-[1.02]">
            Awesome
        </button>
    </div>
</div>
@endif

@endsection