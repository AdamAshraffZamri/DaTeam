@extends('layouts.app')

@section('content')
{{-- 1. FIXED BACKGROUND --}}
<div class="fixed inset-0 z-0">
    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('hastabg.png') }}');"></div>
    <div class="absolute inset-0 bg-gradient-to-b from-black/80 via-black/50 to-black/90"></div>
</div>

{{-- 2. SCROLLABLE CONTENT --}}
<div class="relative z-10 min-h-[calc(100vh-64px)] pb-20">
    
    {{-- STICKY SEARCH BAR (Glass Style) --}}
    <div class="bg-black/40 backdrop-blur-xl border-b border-white/10 sticky top-0 z-30 shadow-2xl">
        <div class="container mx-auto px-4 py-4">
            <form action="{{ route('book.search') }}" method="GET">
                <div class="flex flex-col lg:flex-row items-center gap-4">
                    {{-- Locations --}}
                    <div class="flex items-center space-x-2 w-full lg:w-1/3 bg-white/10 rounded-xl px-4 py-2 border border-white/10">
                        <i class="fas fa-map-marker-alt text-green-400"></i>
                        <input type="text" name="pickup_location" value="{{ request('pickup_location', 'Student Mall, UTM') }}" class="bg-transparent border-none w-full text-sm font-bold text-white focus:ring-0 placeholder-white/40" placeholder="Pickup">
                        <span class="text-white/20">|</span>
                        <i class="fas fa-flag-checkered text-red-400"></i>
                        <input type="text" name="return_location" value="{{ request('return_location', 'Student Mall, UTM') }}" class="bg-transparent border-none w-full text-sm font-bold text-white focus:ring-0 placeholder-white/40" placeholder="Return">
                    </div>

                    {{-- Dates & Times --}}
                    <div class="flex items-center space-x-2 w-full lg:w-1/2">
                        {{-- Pickup Date & Time --}}
                        <div class="flex-1 bg-white/10 rounded-xl px-3 py-2 border border-white/10 flex items-center">
                            <span class="text-[10px] font-bold text-gray-400 mr-2 uppercase">Pickup</span>
                            <input type="date" name="pickup_date" value="{{ request('pickup_date', now()->format('Y-m-d')) }}" 
                                class="bg-transparent border-none text-sm font-bold text-white p-0 focus:ring-0 [color-scheme:dark]">
                            <span class="text-white/20 mx-1">|</span>
                            <select name="pickup_time" class="bg-transparent border-none p-0 text-sm font-bold text-white focus:ring-0 cursor-pointer appearance-none">
                                @for($i = 8; $i <= 22; $i++) 
                                    <option value="{{ sprintf('%02d:00', $i) }}" class="text-black" {{ request('pickup_time', '10:00') == sprintf('%02d:00', $i) ? 'selected' : '' }}>
                                        {{ sprintf('%02d:00', $i) }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        {{-- Return Date & Time --}}
                        <div class="flex-1 bg-white/10 rounded-xl px-3 py-2 border border-white/10 flex items-center">
                            <span class="text-[10px] font-bold text-gray-400 mr-2 uppercase">Return</span>
                            <input type="date" name="return_date" value="{{ request('return_date', now()->addDay()->format('Y-m-d')) }}" 
                                class="bg-transparent border-none text-sm font-bold text-white p-0 focus:ring-0 [color-scheme:dark]">
                            <span class="text-white/20 mx-1">|</span>
                            <select name="return_time" class="bg-transparent border-none p-0 text-sm font-bold text-white focus:ring-0 cursor-pointer appearance-none">
                                @for($i = 8; $i <= 22; $i++)
                                    <option value="{{ sprintf('%02d:00', $i) }}" class="text-black" {{ request('return_time', '10:00') == sprintf('%02d:00', $i) ? 'selected' : '' }}>
                                        {{ sprintf('%02d:00', $i) }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="w-full lg:w-auto px-8 py-3 bg-[#ea580c] hover:bg-orange-600 text-white rounded-xl font-bold transition-all shadow-lg hover:shadow-orange-500/40">
                        Update Search
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="container mx-auto px-4 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            
            {{-- SIDEBAR FILTERS (Glass Style) --}}
            <div class="lg:col-span-1 hidden lg:block">
                <div class="bg-white/5 backdrop-blur-md rounded-2xl border border-white/10 p-6 sticky top-32">
                    <h3 class="text-lg font-extrabold text-white mb-4">Vehicle Type</h3>
                    <div class="space-y-3">
                        @foreach(['Compact', 'Sedan', 'SUV', 'MPV'] as $type)
                        <label class="flex items-center space-x-3 cursor-pointer group">
                            <div class="w-5 h-5 rounded border border-white/20 flex items-center justify-center group-hover:border-orange-500 transition">
                                <input type="checkbox" class="hidden">
                                <div class="w-2.5 h-2.5 rounded-sm bg-orange-500 opacity-0 group-hover:opacity-100 transition"></div>
                            </div>
                            <span class="font-medium text-gray-300 group-hover:text-white transition">{{ $type }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- RESULTS GRID --}}
            <div class="lg:col-span-3 grid grid-cols-1 md:grid-cols-2 gap-6">
                @forelse($vehicles as $vehicle)
                <div class="group bg-white/5 backdrop-blur-md rounded-3xl p-5 border border-white/10 transition-all duration-300 hover:-translate-y-2 hover:bg-white/10 relative">
                    
                    {{-- Plate Number Tag --}}
                    <div class="absolute top-5 right-5 z-10">
                        <span class="bg-black/40 text-gray-300 text-[10px] font-bold px-3 py-1 rounded-full border border-white/10 uppercase tracking-tighter">
                            {{ $vehicle->plateNo }}
                        </span>
                    </div>

                    {{-- Vehicle Image Placeholder --}}
                    <div class="h-48 flex items-center justify-center mb-4 relative overflow-hidden rounded-2xl bg-black/20">
                        {{-- Background Glow --}}
                        <div class="absolute inset-0 bg-gradient-to-tr from-orange-500/10 to-transparent opacity-50"></div>
                        <i class="fas fa-car-side text-6xl text-white/10 group-hover:text-white/20 transition-colors"></i>
                        {{-- If you have real images later: --}}
                        {{-- <img src="{{ asset('storage/'.$vehicle->image) }}" class="max-h-32 object-contain drop-shadow-2xl"> --}}
                    </div>

                    <div class="space-y-3">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-xl font-black text-white">{{ $vehicle->model }}</h3>
                                <div class="flex items-center space-x-1 text-yellow-500 text-sm mt-1">
                                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                                    <span class="text-gray-500 font-medium ml-1">(5.0)</span>
                                </div>
                            </div>
                        </div>

                        {{-- Specs Icons --}}
                        <div class="grid grid-cols-2 gap-2 text-xs text-gray-400">
                            <div class="flex items-center"><i class="fas fa-chair w-6 text-center text-orange-500"></i> 5 Seats</div>
                            <div class="flex items-center"><i class="fas fa-cogs w-6 text-center text-orange-500"></i> Auto</div>
                            <div class="flex items-center"><i class="fas fa-gas-pump w-6 text-center text-orange-500"></i> {{ $vehicle->fuelType ?? 'Petrol' }}</div>
                            <div class="flex items-center"><i class="fas fa-snowflake w-6 text-center text-orange-500"></i> A/C</div>
                        </div>

                        {{-- Pricing and Action --}}
                        <div class="pt-4 mt-4 border-t border-white/10 flex items-center justify-between">
                            <div>
                                <span class="block text-[10px] text-gray-500 font-bold uppercase tracking-widest">Daily Rate</span>
                                <span class="text-2xl font-black text-white">RM {{ number_format($vehicle->priceHour * 24, 0) }}</span>
                            </div>
                            
                            <a href="{{ route('book.payment', [
                                'id' => $vehicle->VehicleID, 
                                'pickup_date' => request('pickup_date', now()->format('Y-m-d')), 
                                'return_date' => request('return_date', now()->addDay()->format('Y-m-d')),
                                'pickup_location' => request('pickup_location', 'Student Mall, UTM'),
                                'return_location' => request('return_location', 'Student Mall, UTM')
                            ]) }}" class="bg-[#ea580c] hover:bg-orange-600 text-white px-8 py-3 rounded-xl font-bold shadow-lg transition-all transform hover:scale-105">
                                Select
                            </a>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-span-full py-24 text-center">
                    <div class="bg-white/5 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4 border border-white/10">
                        <i class="fas fa-car-crash text-3xl text-gray-600"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white">No cars available</h3>
                    <p class="text-gray-400">Try changing your dates or location.</p>
                </div>
                @endforelse
            </div>

        </div>
    </div>
</div>
@endsection