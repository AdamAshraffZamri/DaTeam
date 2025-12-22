@extends('layouts.app')

@section('content')
{{-- 1. FIXED BACKGROUND --}}
<div class="fixed inset-0 z-0">
    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('hastabg.png') }}');"></div>
    <div class="absolute inset-0 bg-gradient-to-b from-black/80 via-black/50 to-black/90"></div>
</div>

{{-- 2. CONTENT WRAPPER --}}
<div class="relative z-10 min-h-[calc(100vh-64px)] flex items-center justify-center py-12">
    <div class="container mx-auto px-4 max-w-2xl">
        
        <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-[2.5rem] p-8 shadow-2xl">
            
            <div class="text-center mb-8">
                <h1 class="text-3xl font-black text-white">Edit Booking</h1>
                <p class="text-gray-400 mt-2">Modify details for <span class="text-orange-500 font-bold">{{ $booking->vehicle->model ?? 'Vehicle' }}</span></p>
            </div>

            <form action="{{ route('book.update', $booking->bookingID) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    
                    {{-- LOCATIONS --}}
                    <div class="space-y-4">
                        <div class="bg-white/5 rounded-xl px-4 py-3 border border-white/10 flex items-center">
                            <i class="fas fa-map-marker-alt text-green-400 mr-4 text-xl"></i>
                            <div class="w-full">
                                <label class="block text-[10px] font-bold text-gray-500 uppercase">Pickup Location</label>
                                <input type="text" name="pickup_location" value="{{ old('pickup_location', $booking->pickupLocation) }}" 
                                       class="bg-transparent border-none w-full text-white font-bold p-0 focus:ring-0 placeholder-gray-600">
                            </div>
                        </div>

                        <div class="bg-white/5 rounded-xl px-4 py-3 border border-white/10 flex items-center">
                            <i class="fas fa-flag-checkered text-red-400 mr-4 text-xl"></i>
                            <div class="w-full">
                                <label class="block text-[10px] font-bold text-gray-500 uppercase">Return Location</label>
                                <input type="text" name="return_location" value="{{ old('return_location', $booking->returnLocation) }}" 
                                       class="bg-transparent border-none w-full text-white font-bold p-0 focus:ring-0 placeholder-gray-600">
                            </div>
                        </div>
                    </div>

                    {{-- DATES --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-white/5 rounded-xl px-4 py-3 border border-white/10">
                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Pickup Date</label>
                            <input type="date" name="pickup_date" value="{{ old('pickup_date', $booking->originalDate) }}" 
                                   class="bg-transparent border-none w-full text-white font-bold p-0 focus:ring-0 [color-scheme:dark]">
                        </div>

                        <div class="bg-white/5 rounded-xl px-4 py-3 border border-white/10">
                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Return Date</label>
                            <input type="date" name="return_date" value="{{ old('return_date', $booking->returnDate) }}" 
                                   class="bg-transparent border-none w-full text-white font-bold p-0 focus:ring-0 [color-scheme:dark]">
                        </div>
                    </div>
                    
                    {{-- INFO ALERT --}}
                    <div class="bg-orange-500/10 border border-orange-500/20 rounded-xl p-4 flex items-start space-x-3">
                        <i class="fas fa-info-circle text-orange-500 mt-1"></i>
                        <p class="text-xs text-orange-200 leading-relaxed">
                            Changing dates may affect the total rental price. The new price will be recalculated upon confirmation by staff.
                        </p>
                    </div>

                    {{-- ACTIONS --}}
                    <div class="grid grid-cols-2 gap-4 pt-4">
                        <a href="{{ route('book.index') }}" class="flex items-center justify-center bg-white/5 hover:bg-white/10 text-gray-300 font-bold py-4 rounded-xl transition border border-white/10">
                            Cancel Booking
                        </a>
                        <button type="submit" class="bg-[#ea580c] hover:bg-orange-600 text-white font-bold py-4 rounded-xl shadow-lg hover:shadow-orange-500/40 transition transform hover:scale-[1.02]">
                            Save Changes
                        </button>
                    </div>

                </div>
            </form>

        </div>
    </div>
</div>
@endsection