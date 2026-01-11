@extends('layouts.app')

{{-- Load Leaflet CSS --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
    @keyframes gradient-move {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }
    .animate-gradient {
        animation: gradient-move 8s ease infinite;
    }
</style>

@section('content')
{{-- 1. FIXED BACKGROUND --}}
<div class="fixed inset-0 z-0">
    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('hastabg.png') }}');"></div>
    <div class="absolute inset-0 bg-gradient-to-r from-black/70 via-black/45 to-black/75"></div>
</div>

{{-- 2. SCROLLABLE CONTENT --}}
<div class="relative z-10 min-h-[calc(100vh-64px)] pb-20">
    
    {{-- STICKY SEARCH BAR (Z-INDEX 30) --}}
    <div class="bg-black/25 backdrop-blur-xl border-b border-white/15 sticky top-0 z-30 shadow-2xl">
        <div class="container mx-auto px-4 py-4">
            <form action="{{ route('book.search') }}" method="GET" id="search-form">
    
                <div class="flex flex-col lg:flex-row items-center justify-center gap-4">
                    
                    {{-- Locations --}}
                    <div class="flex items-center space-x-2 w-full lg:w-auto lg:flex-1 max-w-2xl bg-white/5 rounded-xl px-4 py-2 border border-white/10">
                        <button type="button" onclick="openMapModal('pickup')" class="hover:bg-white/10 p-1.5 rounded-full transition">
                            <i class="fas fa-map-marker-alt text-green-400"></i>
                        </button>
                        <input type="text" name="pickup_location" value="{{ request('pickup_location', 'Student Mall, UTM') }}" class="bg-transparent border-none w-full text-sm font-bold text-white focus:ring-0 placeholder-white/40" placeholder="Pickup">
                        
                        <span class="text-white/20">|</span>
                        
                        <button type="button" onclick="openMapModal('return')" class="hover:bg-white/10 p-1.5 rounded-full transition">
                            <i class="fas fa-flag-checkered text-red-400"></i>
                        </button>
                        <input type="text" name="return_location" value="{{ request('return_location', 'Student Mall, UTM') }}" class="bg-transparent border-none w-full text-sm font-bold text-white focus:ring-0 placeholder-white/40" placeholder="Return">
                    </div>

                    {{-- Dates & Times --}}
                    <div class="flex items-center gap-2 w-full lg:w-auto">
                        
                        {{-- Pickup --}}
                        <div class="flex-1 bg-white/5 rounded-xl px-3 py-2 border border-white/10 flex items-center h-[50px]">
                            <span class="hidden xl:inline text-[10px] font-bold text-gray-400 mr-2 uppercase">Pickup</span>
                            
                            {{-- Added ID, MIN, and ONCHANGE --}}
                            <input type="date" 
                                id="pickup_date" 
                                name="pickup_date" 
                                min="{{ now()->format('Y-m-d') }}"
                                value="{{ request('pickup_date', now()->format('Y-m-d')) }}" 
                                onchange="validateDates()"
                                class="bg-transparent border-none text-sm font-bold text-white p-0 focus:ring-0 [color-scheme:dark] w-full md:w-auto">
                        
                            <div class="w-px h-4 bg-white/20 mx-2 hidden md:block"></div>
                            
                            <div class="flex items-center">
                                <input type="hidden" name="pickup_time" id="pickup_time_search_hidden" value="{{ request('pickup_time', '10:00') }}">
                                {{-- HOUR DROPDOWN --}}
                                <select id="pickup_hour_search" onchange="updateSearchTime('pickup')"
                                    class="bg-transparent text-white font-bold text-sm border-none p-0 focus:ring-0 cursor-pointer appearance-none text-center w-6">
                                    @for ($i = 1; $i <= 12; $i++)
                                        <option value="{{ $i }}" {{ request('pickup_time') && explode(':', request('pickup_time'))[0] == $i ? 'selected' : ($i == 10 ? 'selected' : '') }} class="text-black">
                                            {{ $i }}
                                        </option>
                                    @endfor
                                </select>
                                <span class="text-white font-bold text-sm leading-none">:00</span>
                                <select id="pickup_ampm_search" class="bg-transparent text-white font-bold text-xs border-none p-0 focus:ring-0 cursor-pointer ml-1"
                                        onchange="updateSearchTime('pickup')">
                                    <option value="AM" class="text-black">AM</option>
                                    <option value="PM" class="text-black" selected>PM</option>
                                </select>
                            </div>
                        </div>

                        {{-- Return --}}
                        <div class="flex-1 bg-white/5 rounded-xl px-3 py-2 border border-white/10 flex items-center h-[50px]">
                            <span class="hidden xl:inline text-[10px] font-bold text-gray-400 mr-2 uppercase">Return</span>
                            
                            {{-- Added ID, MIN, and ONCHANGE --}}
                            <input type="date" 
                                id="return_date" 
                                name="return_date" 
                                min="{{ now()->format('Y-m-d') }}"
                                value="{{ request('return_date', now()->addDay()->format('Y-m-d')) }}" 
                                onchange="updateAllSelectLinks()"
                                class="bg-transparent border-none text-sm font-bold text-white p-0 focus:ring-0 [color-scheme:dark] w-full md:w-auto">
                        
                            <div class="w-px h-4 bg-white/20 mx-2 hidden md:block"></div>

                            <div class="flex items-center">
                                <input type="hidden" name="return_time" id="return_time_search_hidden" value="{{ request('return_time', '10:00') }}">
                                {{-- HOUR DROPDOWN --}}
                                <select id="return_hour_search" onchange="updateSearchTime('return')"
                                    class="bg-transparent text-white font-bold text-sm border-none p-0 focus:ring-0 cursor-pointer appearance-none text-center w-6">
                                    @for ($i = 1; $i <= 12; $i++)
                                        <option value="{{ $i }}" {{ request('return_time') && explode(':', request('return_time'))[0] == $i ? 'selected' : ($i == 10 ? 'selected' : '') }} class="text-black">
                                            {{ $i }}
                                        </option>
                                    @endfor
                                </select>
                                <span class="text-white font-bold text-sm leading-none">:00</span>
                                <select id="return_ampm_search" class="bg-transparent text-white font-bold text-xs border-none p-0 focus:ring-0 cursor-pointer ml-1"
                                        onchange="updateSearchTime('return')">
                                    <option value="AM" class="text-black">AM</option>
                                    <option value="PM" class="text-black" selected>PM</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="w-full lg:w-auto px-6 py-3 bg-[#ea580c] hover:bg-orange-600 text-white rounded-xl font-bold transition-all shadow-lg hover:shadow-orange-500/40 whitespace-nowrap">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- FILTERS & RESULTS GRID --}}
    <div class="container mx-auto px-4 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            
            {{-- SIDEBAR FILTERS --}}
            <div class="lg:col-span-1 hidden lg:block">
                <div class="bg-black/25 backdrop-blur-[2px] border border-white/15 rounded-3xl p-6 shadow-xl space-y-8">
                    
                    {{-- 1. VEHICLE CATEGORY --}}
                    <div>
                        <h3 class="text-xs font-black text-gray-500 uppercase tracking-widest mb-4">Category</h3>
                        <div class="space-y-3">
                            @foreach(['car' => 'Cars', 'bike' => 'Motorcycles'] as $val => $label)
                            <label class="flex items-center space-x-3 cursor-pointer group">
                                <div class="w-5 h-5 rounded border border-white/20 flex items-center justify-center group-hover:border-orange-500 transition relative">
                                    <input type="checkbox" 
                                           form="search-form"
                                           name="category[]" 
                                           value="{{ $val }}" 
                                           class="peer appearance-none absolute inset-0 w-full h-full cursor-pointer z-10"
                                           onchange="document.getElementById('search-form').submit()"
                                           {{ in_array($val, request('category', [])) ? 'checked' : '' }}>
                                    <div class="w-2.5 h-2.5 rounded-sm bg-orange-500 opacity-0 peer-checked:opacity-100 transition"></div>
                                </div>
                                <span class="font-medium text-gray-300 group-hover:text-white transition text-sm">{{ $label }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="h-px bg-white/10 w-full"></div>

                    {{-- 2. VEHICLE TYPE (Existing) --}}
                    <div>
                        <h3 class="text-xs font-black text-gray-500 uppercase tracking-widest mb-4">Body Type</h3>
                        <div class="space-y-3">
                            @foreach(['Compact', 'Sedan', 'SUV', 'MPV', 'Luxury'] as $type)
                            <label class="flex items-center space-x-3 cursor-pointer group">
                                <div class="w-5 h-5 rounded border border-white/20 flex items-center justify-center group-hover:border-orange-500 transition relative">
                                    <input type="checkbox" 
                                           form="search-form"
                                           name="types[]" 
                                           value="{{ $type }}" 
                                           class="peer appearance-none absolute inset-0 w-full h-full cursor-pointer z-10"
                                           onchange="document.getElementById('search-form').submit()"
                                           {{ in_array($type, request('types', [])) ? 'checked' : '' }}>
                                    <div class="w-2.5 h-2.5 rounded-sm bg-orange-500 opacity-0 peer-checked:opacity-100 transition"></div>
                                </div>
                                <span class="font-medium text-gray-300 group-hover:text-white transition text-sm">{{ $type }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>


                    <div class="h-px bg-white/10 w-full"></div>

                    {{-- 5. PRICE RANGE --}}
                    <div>
                        <h3 class="text-xs font-black text-gray-500 uppercase tracking-widest mb-4">Daily Rate (RM)</h3>
                        <div class="space-y-3">
                            @foreach(['0-100' => '< 100', '100-200' => '100 - 200', '200-300' => '200 - 300', '300-1000' => '> 300'] as $val => $label)
                            <label class="flex items-center space-x-3 cursor-pointer group">
                                <div class="w-5 h-5 rounded border border-white/20 flex items-center justify-center group-hover:border-orange-500 transition relative">
                                    <input type="checkbox" 
                                           form="search-form"
                                           name="price_range[]" 
                                           value="{{ $val }}" 
                                           class="peer appearance-none absolute inset-0 w-full h-full cursor-pointer z-10"
                                           onchange="document.getElementById('search-form').submit()"
                                           {{ in_array($val, request('price_range', [])) ? 'checked' : '' }}>
                                    <div class="w-2.5 h-2.5 rounded-sm bg-orange-500 opacity-0 peer-checked:opacity-100 transition"></div>
                                </div>
                                <span class="font-medium text-gray-300 group-hover:text-white transition text-sm">{{ $label }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    
                </div>
            </div>

    {{-- RESULTS GRID --}}
    <div class="lg:col-span-3 grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($vehicles as $vehicle)
        
        @php
        // 1. Get Inputs
        $pDate = request('pickup_date', now()->format('Y-m-d'));
        $pTime = request('pickup_time', '10:00');
        $rDate = request('return_date', now()->addDay()->format('Y-m-d'));
        $rTime = request('return_time', '10:00');

        $totalPrice = 0;
        $durationLabel = '';

        try {
            $start = \Carbon\Carbon::parse("$pDate $pTime");
            $end = \Carbon\Carbon::parse("$rDate $rTime");
            
            // Prevent negative time
            if ($end->lt($start)) {
                $end = $start->copy()->addHour();
            }

            // 2. Get Total Duration in Hours
            $totalHours = ceil($start->floatDiffInHours($end));
            if ($totalHours < 1) $totalHours = 1;

            // 3. Get Rates
            $rates = is_array($vehicle->hourly_rates) ? $vehicle->hourly_rates : json_decode($vehicle->hourly_rates ?? '[]', true);
            
            // Define Base Rates
            $rate1h = $rates[1] ?? $vehicle->priceHour; 
            $rate24h = $rates[24] ?? ($rate1h * 24);   

            // 4. Base Price
            if ($totalHours < 24) {
                $basePrice = isset($rates[$totalHours]) ? $rates[$totalHours] : ($totalHours * $rate1h);
                if ($basePrice > $rate24h) $basePrice = $rate24h;
                $durationLabel = $totalHours . ' Hour' . ($totalHours > 1 ? 's' : '');
            } else {
                $days = floor($totalHours / 24);
                $rem = $totalHours % 24;
                $remCost = isset($rates[$rem]) ? $rates[$rem] : ($rem * $rate1h);
                if ($remCost > $rate24h) $remCost = $rate24h;
                $basePrice = ($days * $rate24h) + $remCost;
                $durationLabel = $days . ' Day' . ($days > 1 ? 's' : '') . ($rem > 0 ? ' + '.$rem.'h' : '');
            }

            $totalPrice = $vehicle->display_total_price ?? 0;

            } catch (\Exception $e) {
                $totalPrice = $vehicle->priceHour * 24;
                $durationLabel = "1 Day (Est)";
            }
    @endphp


    {{-- Vehicle Card --}}
    <div onclick="document.getElementById('details-modal-{{ $vehicle->VehicleID }}').classList.remove('hidden')" 
        class="group bg-black/25 backdrop-blur-[2px] border border-white/15 rounded-3xl p-6 shadow-xl hover:shadow-orange-500/10 hover:bg-black/60 transition-all duration-300 relative overflow-hidden flex flex-col h-full cursor-pointer">

        {{-- DYNAMIC PRICING BADGES (High Demand & Discount) --}}
        @if(isset($activeLabels) && count($activeLabels) > 0)
            <div class="absolute top-3 left-3 z-20 flex flex-col gap-2 items-start">
                @foreach($activeLabels as $rule)
                    @if($rule['percent'] > 0)
                        {{-- 1. SURCHARGE BADGE (Red/Orange) --}}
                        <div class="relative group">
                            {{-- 1. Ping Effect (The expanding ring animation) --}}
                            <div class="absolute inset-0 bg-orange-400 rounded-xl animate-ping opacity-75"></div>

                            {{-- 2. Main Badge --}}
                            <div class="relative bg-gradient-to-r from-red-600 via-red-500 to-red-600 bg-[length:200%_auto] animate-gradient 
                                        text-white text-[10px] font-bold px-3 py-1.5 rounded-xl 
                                        shadow-[0_0_15px_rgba(249,115,22,0.6)] flex items-center gap-1.5 border border-white/20">
                                
                                {{-- Fire Icon with Pulse --}}
                                <i class="fas fa-fire animate-pulse text-yellow-200"></i> 
                                                    
                                <span class="tracking-wide uppercase">
                                    HIGH DEMAND (+{{ $rule['percent'] }}%) - {{ strtoupper($rule['date']) }}
                                </span>
                            </div>
                        </div>
                    @else
                        {{-- 2. DISCOUNT BADGE (Green/Emerald) --}}
                        <div class="relative group">
                            {{-- Ping Effect --}}
                            <div class="absolute inset-0 bg-emerald-400 rounded-xl animate-ping opacity-75"></div>
                            
                            {{-- Badge Content --}}
                            <div class="relative bg-gradient-to-r from-emerald-800 via-green-700 to-emerald-800 bg-[length:200%_auto] animate-gradient 
                                        text-white text-[10px] font-bold px-3 py-1.5 rounded-xl 
                                        shadow-[0_0_15px_rgba(16,185,129,0.6)] flex items-center gap-1.5 border border-white/20">
                                
                                <i class="fas fa-tags animate-pulse text-white"></i> 
                                
                                <span class="tracking-wide uppercase">
                                    SPECIAL OFFER ({{ $rule['percent'] }}%) - {{ strtoupper($rule['date']) }}
                                </span>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif

        {{-- Image Area --}}
        <div class="h-60 flex items-center justify-center mb-4 relative overflow-hidden rounded-2xl bg-black/30 border border-white/5">
            <div class="absolute inset-0 bg-gradient-to-tr from-orange-500/10 to-transparent opacity-50"></div>
            @if($vehicle->image)
                <img src="{{ asset('storage/' . $vehicle->image) }}" alt="{{ $vehicle->model }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
            @else
                <i class="fas fa-{{ $vehicle->vehicle_category == 'bike' ? 'motorcycle' : 'car-side' }} text-6xl text-white/10 group-hover:text-white/20 transition-colors"></i>
            @endif
        </div>

        <div class="space-y-3 flex-grow">
            <div>
                <h3 class="text-xl font-black text-white leading-tight">{{ $vehicle->brand }} {{ $vehicle->model }}</h3>
                <div class="flex items-center space-x-2 mt-1">
                    <span class="text-[10px] text-gray-500 font-bold uppercase">{{ $vehicle->year }}</span>
                    <span class="text-white/20">|</span>
                    <span class="text-[10px] text-gray-500 font-bold uppercase">{{ $vehicle->color }}</span>
                </div>
            </div>

            {{-- Specs --}}
            <div class="grid grid-cols-2 gap-2 text-[10px] font-bold text-gray-400 uppercase tracking-tight">
                <div class="flex items-center">
                    <i class="fas fa-{{ $vehicle->vehicle_category == 'bike' ? 'user' : 'chair' }} w-5 text-orange-500"></i> 
                    {{ $vehicle->vehicle_category == 'bike' ? '2' : '5' }} Seats
                </div>
                <div class="flex items-center">
                    <i class="fas fa-cog w-5 text-orange-500"></i> 
                    Auto
                </div>
                <div class="flex items-center">
                    <i class="fas fa-gas-pump w-5 text-orange-500"></i> 
                    {{ $vehicle->fuelType ?? 'RON95' }}
                </div>
                <div class="flex items-center">
                    <i class="fas fa-{{ $vehicle->vehicle_category == 'bike' ? 'helmet-safety' : 'snowflake' }} w-5 text-orange-500"></i> 
                    {{ $vehicle->vehicle_category == 'bike' ? 'Helmet' : 'A/C' }}
                </div>
            </div>
        </div>

        {{-- Action Footer --}}
        <div class="pt-4 mt-4 border-t border-white/10 flex items-center justify-between">
            <div>
                <span class="block text-[10px] text-gray-500 font-black uppercase tracking-widest">
                    Total for {{ $durationLabel }}
                </span>
                <span class="text-xl font-black text-white tracking-tighter">
                    RM {{ (floor($totalPrice) == $totalPrice) ? number_format($totalPrice, 0) : number_format($totalPrice, 2) }}
                </span>
            </div>
            
            <div class="flex items-center space-x-2 relative z-10">
                {{-- Details Button (Optional now since card is clickable) --}}
                <button type="button" 
                    class="bg-white/10 hover:bg-white/20 text-white px-4 py-2 rounded-xl font-bold text-xs transition-all border border-white/10">
                    Details
                </button>

                {{-- Select Button (Stops propagation so it doesn't open modal when clicking Select) --}}
                <a href="{{ route('book.payment', [
                    'id' => $vehicle->VehicleID, 
                    'pickup_date' => request('pickup_date'), 
                    'return_date' => request('return_date'),
                    'pickup_time' => request('pickup_time'),
                    'return_time' => request('return_time'),
                    'pickup_location' => request('pickup_location'),
                    'return_location' => request('return_location')
                ]) }}" 
                onclick="event.stopPropagation();"
                class="bg-[#ea580c] hover:bg-orange-600 text-white px-5 py-2 rounded-xl font-bold text-xs shadow-lg transition-all transform hover:scale-105">
                    Select
                </a>
            </div>
        </div>
    </div>
    @empty
    <div class="col-span-full py-24 text-center">
        <i class="fas fa-car-crash text-5xl text-white/10 mb-4"></i>
        <h3 class="text-xl font-black text-white uppercase tracking-widest">No matching fleet</h3>
        <p class="text-gray-500 text-sm mt-2">Try adjusting your filters or search terms.</p>
    </div>
    @endforelse
</div>
        </div>
    </div>
</div>

{{-- 3. VEHICLE DETAILS MODALS (MOVED OUTSIDE SCROLL CONTAINER) --}}
@foreach($vehicles as $vehicle)
<div id="details-modal-{{ $vehicle->VehicleID }}" class="fixed inset-0 z-50 hidden" style="z-index: 100;" role="dialog" aria-modal="true">
    
    {{-- 1. Backdrop (Click to close) --}}
    <div class="absolute inset-0 bg-black/70 backdrop-blur-md transition-opacity" 
         onclick="document.getElementById('details-modal-{{ $vehicle->VehicleID }}').classList.add('hidden')"></div>

    {{-- 2. Centering Wrapper --}}
    <div class="relative z-10 flex items-center justify-center h-screen p-4 pointer-events-none">
        <div class="bg-[#151515] border border-white/10 w-full max-w-4xl rounded-[2.5rem] shadow-2xl overflow-hidden pointer-events-auto flex flex-col md:flex-row max-h-[90vh]">
            
            {{-- Left: Image & Technical Specs --}}
            <div class="w-full md:w-5/12 bg-white/5 relative flex flex-col p-8 border-r border-white/5">
                <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-20"></div>
                
                <div class="relative z-10 space-y-6">
                    <div class="h-56 md:h-64 flex items-center justify-center bg-black/40 rounded-[2rem] overflow-hidden border border-white/10 shadow-inner">
                        @if($vehicle->image)
                            <img src="{{ asset('storage/' . $vehicle->image) }}" class="w-full h-full object-cover">
                        @else
                            <i class="fas fa-{{ $vehicle->vehicle_category == 'bike' ? 'motorcycle' : 'car' }} text-7xl text-white/5"></i>
                        @endif
                    </div>
                    
                    {{-- Tech Grid --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-black/40 rounded-2xl p-4 border border-white/5 text-center">
                            <i class="fas fa-tachometer-alt text-orange-500 mb-1 text-xs"></i>
                            <p class="text-[9px] text-gray-500 uppercase font-black">Mileage</p>
                            <p class="text-white font-bold text-xs">{{ number_format($vehicle->mileage) }} KM</p>
                        </div>
                        <div class="bg-black/40 rounded-2xl p-4 border border-white/5 text-center">
                            <i class="fas fa-gas-pump text-orange-500 mb-1 text-xs"></i>
                            <p class="text-[9px] text-gray-500 uppercase font-black">Fuel</p>
                            <p class="text-white font-bold text-xs">{{ $vehicle->fuelType ?? 'RON95' }}</p>
                        </div>
                        <div class="bg-black/40 rounded-2xl p-4 border border-white/5 text-center">
                            <i class="fas fa-calendar-alt text-orange-500 mb-1 text-xs"></i>
                            <p class="text-[9px] text-gray-500 uppercase font-black">Year</p>
                            <p class="text-white font-bold text-xs">{{ $vehicle->year }}</p>
                        </div>
                        <div class="bg-black/40 rounded-2xl p-4 border border-white/5 text-center">
                            <i class="fas fa-palette text-orange-500 mb-1 text-xs"></i>
                            <p class="text-[9px] text-gray-500 uppercase font-black">Color</p>
                            <p class="text-white font-bold text-xs">{{ $vehicle->color }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right: Information & Pricing Architecture --}}
            <div class="w-full md:w-7/12 p-10 flex flex-col bg-gradient-to-br from-[#1a1a1a] to-black overflow-y-auto custom-scrollbar">
                <div class="flex justify-between items-start mb-8">
                    <div>
                        <p class="text-orange-500 text-[10px] font-black uppercase tracking-[0.3em] mb-1">{{ $vehicle->brand }}</p>
                        <h2 class="text-4xl font-black text-white tracking-tighter">{{ $vehicle->model }}</h2>
                        <div class="flex items-center space-x-2 mt-2">
                            <span class="bg-green-500/10 text-green-500 text-[9px] font-black px-2 py-0.5 rounded border border-green-500/20 uppercase">Available</span>
                        </div>
                    </div>
                    <button onclick="document.getElementById('details-modal-{{ $vehicle->VehicleID }}').classList.add('hidden')" class="bg-white/5 hover:bg-white/10 w-10 h-10 rounded-full flex items-center justify-center text-gray-500 hover:text-white transition-all">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                {{-- HOURLY RATE ARCHITECTURE --}}
                <div class="mb-10">
                    <h4 class="text-[10px] font-black text-gray-500 uppercase tracking-[0.2em] mb-4">Standard Price Rate Architecture (RM)</h4>
                    <div class="grid grid-cols-4 sm:grid-cols-7 gap-2">
                        @php
                            $rates = is_array($vehicle->hourly_rates) ? $vehicle->hourly_rates : json_decode($vehicle->hourly_rates ?? '[]', true);
                            $tiers = [1, 3, 5, 7, 9, 12, 24];
                        @endphp
                        @foreach($tiers as $h)
                        <div class="bg-white/5 border border-white/10 rounded-xl p-2 text-center">
                            <p class="text-[8px] text-gray-500 uppercase font-black">{{ $h }}H</p>
                            <p class="text-white font-black text-xs">{{ $rates[$h] ?? '-' }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="space-y-6 mb-10">
                    <div>
                        <h3 class="text-xs font-black text-gray-400 mb-3 uppercase tracking-widest">Key Features</h3>
                        <div class="grid grid-cols-2 gap-y-3 gap-x-4 text-xs text-gray-500">
                            <div class="flex items-center"><i class="fas fa-check text-orange-500 mr-2"></i> {{ $vehicle->vehicle_category == 'bike' ? 'Safety Helmet' : 'Air Conditioning' }}</div>
                            <div class="flex items-center"><i class="fas fa-check text-orange-500 mr-2"></i> {{ $vehicle->vehicle_category == 'bike' ? 'Fuel Efficient' : 'Power Steering' }}</div>
                            <div class="flex items-center"><i class="fas fa-check text-orange-500 mr-2"></i> Bluetooth Audio</div>
                            <div class="flex items-center"><i class="fas fa-check text-orange-500 mr-2"></i> 5-Star Safety</div>
                        </div>
                    </div>
                </div>

                {{-- Action Footer --}}
                <div class="pt-8 border-t border-white/10 flex items-center justify-between mt-auto">
                    <div>
                        <p class="text-[9px] text-gray-500 font-black uppercase tracking-widest">Standard Full Day Price (24H)</p>
                        <p class="text-3xl font-black text-white tracking-tighter">RM {{ number_format($rates[24] ?? 0, 0) }}</p>
                    </div>
                    <a href="{{ route('book.payment', [
                        'id' => $vehicle->VehicleID, 
                        'pickup_date' => request('pickup_date'), 
                        'return_date' => request('return_date'),
                        'pickup_time' => request('pickup_time'),
                        'return_time' => request('return_time'),
                        'pickup_location' => request('pickup_location'),
                        'return_location' => request('return_location')
                    ]) }}" class="bg-[#ea580c] hover:bg-orange-600 text-white px-10 py-4 rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl shadow-orange-500/20 transition-all transform hover:scale-105">
                        Book Now
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endforeach

{{-- 4. MAP MODAL (MOVED OUTSIDE SCROLL CONTAINER) --}}
<div id="mapModal" class="fixed inset-0 z-50 hidden" style="z-index: 100;" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-black/80 backdrop-blur-sm transition-opacity" onclick="closeMapModal()"></div>
    <div class="relative z-10 flex items-center justify-center min-h-screen p-4 pointer-events-none">
        <div class="bg-[#1a1a1a] border border-white/10 w-full max-w-4xl rounded-[2.5rem] shadow-2xl overflow-hidden pointer-events-auto flex flex-col h-[80vh]">
            <div class="px-8 py-6 border-b border-white/10 flex justify-between items-center bg-white/5">
                <div>
                    <h3 class="text-2xl font-black text-white tracking-tight">Select Location</h3>
                    <p class="text-sm text-gray-400 mt-1">Drag the marker to pin-point your location.</p>
                </div>
                <button type="button" onclick="closeMapModal()" class="w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="flex-grow relative bg-gray-900">
                <div id="map" class="absolute inset-0 z-0"></div>
                <div class="absolute bottom-6 left-6 right-6 z-10">
                    <div class="bg-black/60 backdrop-blur-md border border-white/10 text-white p-4 rounded-2xl shadow-xl flex flex-col md:flex-row items-center justify-between gap-4">
                        <div class="flex items-center w-full">
                            <i class="fas fa-map-pin text-orange-500 text-xl mr-4"></i>
                            <div class="w-full">
                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Selected Address</p>
                                <p id="current_address" class="font-bold text-sm md:text-base truncate w-full">Loading address...</p>
                            </div>
                        </div>
                        <button type="button" onclick="confirmLocation()" class="w-full md:w-auto px-8 py-3 bg-[#ea580c] hover:bg-orange-600 text-white font-bold rounded-xl shadow-lg transition transform hover:scale-105 whitespace-nowrap">
                            Confirm Location
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

{{-- JAVASCRIPT --}}
<script>
    // --- 1. LIVE LINK UPDATER ---
    function updateAllSelectLinks() {
        const pLoc = document.querySelector('input[name="pickup_location"]').value;
        const rLoc = document.querySelector('input[name="return_location"]').value;
        const pDate = document.querySelector('input[name="pickup_date"]').value;
        const rDate = document.querySelector('input[name="return_date"]').value;
        const pTime = document.getElementById('pickup_time_search_hidden').value;
        const rTime = document.getElementById('return_time_search_hidden').value;

        document.querySelectorAll('a[href*="payment"]').forEach(btn => {
            try {
                let url = new URL(btn.href);
                url.searchParams.set('pickup_location', pLoc);
                url.searchParams.set('return_location', rLoc);
                url.searchParams.set('pickup_date', pDate);
                url.searchParams.set('return_date', rDate);
                url.searchParams.set('pickup_time', pTime);
                url.searchParams.set('return_time', rTime);
                btn.href = url.toString();
            } catch (e) { console.error("Link Error", e); }
        });
    }

    // --- 2. TIME INPUT LOGIC (UPDATED FOR DROPDOWNS) ---
    function updateSearchTime(type) {
        // Pull value from the SELECT dropdown instead of NUMBER input
        let hour = parseInt(document.getElementById(type + '_hour_search').value) || 10;
        let ampm = document.getElementById(type + '_ampm_search').value;
        const hiddenInput = document.getElementById(type + '_time_search_hidden');

        let hour24 = hour;
        if (ampm === 'PM' && hour < 12) hour24 = hour + 12;
        if (ampm === 'AM' && hour === 12) hour24 = 0;

        hiddenInput.value = (hour24 < 10 ? '0' + hour24 : hour24) + ':00';
        
        // Update the "Select" buttons on the car cards immediately
        updateAllSelectLinks();
    }

    window.onload = function() {
        initSearchTime('pickup');
        initSearchTime('return');
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('input', updateAllSelectLinks);
            input.addEventListener('change', updateAllSelectLinks);
        });
    };

    function initSearchTime(type) {
        const hiddenVal = document.getElementById(type + '_time_search_hidden').value;
        if(hiddenVal) {
            let hour24 = parseInt(hiddenVal.split(':')[0]);
            let ampm = 'AM';
            let hour12 = hour24;

            if (hour24 >= 12) {
                ampm = 'PM';
                if (hour24 > 12) hour12 = hour24 - 12;
            }
            if (hour24 === 0) { hour12 = 12; ampm = 'AM'; }

            document.getElementById(type + '_hour_search').value = hour12;
            document.getElementById(type + '_ampm_search').value = ampm;
        }
    }

    // --- 3. MAP MODAL ---
    let map = null;
    let marker = null;
    let activeInputId = null; 
    const defaultLat = 1.5593; 
    const defaultLng = 103.6378;

    function openMapModal(type) {
        activeInputId = type;
        document.getElementById('mapModal').classList.remove('hidden');

        if (!map) {
            if(!document.getElementById('map')) return;
            map = L.map('map').setView([defaultLat, defaultLng], 15);
            L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; CARTO', subdomains: 'abcd', maxZoom: 20
            }).addTo(map);

            const orangeIcon = L.divIcon({
                className: 'custom-div-icon',
                html: "<div style='background-color:#ea580c; width: 1.5rem; height: 1.5rem; border-radius: 50%; border: 3px solid white; box-shadow: 0 0 10px rgba(234,88,12,0.5);'></div>",
                iconSize: [24, 24], iconAnchor: [12, 12]
            });

            marker = L.marker([defaultLat, defaultLng], { icon: orangeIcon, draggable: true }).addTo(map);
            marker.on('moveend', function(e) { fetchAddress(e.target.getLatLng()); });
            map.on('click', function(e) {
                marker.setLatLng(e.latlng);
                fetchAddress(e.latlng);
            });
        }
        setTimeout(() => { map.invalidateSize(); }, 100);
        if(marker) fetchAddress(marker.getLatLng());
    }

    function closeMapModal() {
        document.getElementById('mapModal').classList.add('hidden');
    }

    async function fetchAddress(latlng) {
        const textEl = document.getElementById('current_address');
        textEl.innerText = "Fetching address...";
        try {
            const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${latlng.lat}&lon=${latlng.lng}`;
            const response = await fetch(url, { headers: { 'User-Agent': 'HastaCarRental/1.0' } });
            if (!response.ok) throw new Error("Network error");
            const data = await response.json();
            let address = data.display_name.split(',').slice(0, 3).join(',');
            textEl.innerText = address || "Location Selected";
        } catch (error) {
            textEl.innerText = "Location Pin Selected";
        }
    }

    function confirmLocation() {
        const address = document.getElementById('current_address').innerText;
        if (activeInputId) {
            const input = document.querySelector(`input[name="${activeInputId}_location"]`);
            if (input) {
                input.value = address;
                updateAllSelectLinks(); 
            }
        }
        closeMapModal();
    }
</script>
<script>
    function validateDates() {
        const pickupInput = document.getElementById('pickup_date');
        const returnInput = document.getElementById('return_date');

        if(pickupInput && returnInput) {
            // 1. Set the minimum return date to the selected pickup date
            returnInput.min = pickupInput.value;

            // 2. If the current return date is BEFORE the new pickup date, 
            // automatically shift return date to match pickup date
            if(returnInput.value < pickupInput.value) {
                returnInput.value = pickupInput.value;
            }
        }
        
        // Trigger the link updater so the "Select" buttons get the new dates
        if(typeof updateAllSelectLinks === 'function') {
            updateAllSelectLinks();
        }
    }

    // Run on load to ensure logic is correct from the start
    document.addEventListener("DOMContentLoaded", function() {
        validateDates();
    });
</script>
<style>
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: rgba(255,255,255,0.05); }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 2px; }
</style>
@endsection