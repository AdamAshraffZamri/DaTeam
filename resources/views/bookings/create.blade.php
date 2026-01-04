@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>

{{-- 1. BACKGROUND --}}
<div class="fixed inset-0 z-0">
    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('hastabg.png') }}');"></div>
    <div class="absolute inset-0 bg-gradient-to-r from-black/70 via-black/45 to-black/75"></div>
</div>

{{-- 2. MAIN CONTENT --}}
<div class="relative z-10 w-full min-h-screen flex flex-col justify-start items-center px-4 pt-20 md:pt-30">

    {{-- HEADER TEXT --}}
    <div class="text-center mb-8">
        <h1 class="text-5xl md:text-7xl font-black text-white drop-shadow-2xl tracking-tight mb-2">
            Drive your adventure.
        </h1>
        <p class="text-lg md:text-xl text-gray-200 font-medium drop-shadow-md tracking-wide">
            Premium car rental services for UTM Students & Staff.
        </p>
    </div>

    {{-- SEARCH FORM CONTAINER --}}
    <div class="w-full max-w-6xl">
        <form action="{{ route('book.search') }}" method="GET" id="searchForm">
            <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-[2rem] p-3 shadow-2xl flex flex-col md:flex-row items-center gap-2 md:gap-0">

                {{-- PICKUP LOCATION --}}
                <div class="flex-1 px-6 py-2 w-full md:border-r border-white/10">
                    <label class="block text-[12px] font-bold text-white uppercase tracking-wider mb-1">PICKUP POINT</label>
                    <div class="flex items-center group">
                        <button type="button" onclick="openMapModal('pickup')" class="w-8 h-8 rounded-full bg-green-500/20 flex items-center justify-center mr-3 text-green-400 hover:bg-green-500 hover:text-white transition cursor-pointer">
                            <i class="fas fa-map-marker-alt"></i>
                        </button>
                        <input type="text" id="pickup_location" name="pickup_location" value="Student Mall, UTM"
                               class="w-full bg-transparent font-bold text-white focus:outline-none border-none p-0 placeholder-gray-400 focus:ring-0">
                    </div>
                </div>

                {{-- RETURN LOCATION --}}
                <div class="flex-1 px-6 py-2 w-full md:border-r border-white/10">
                    <label class="block text-[12px] font-bold text-white uppercase tracking-wider mb-1">RETURN POINT</label>
                    <div class="flex items-center group">
                        <button type="button" onclick="openMapModal('return')" class="w-8 h-8 rounded-full bg-red-500/20 flex items-center justify-center mr-3 text-red-400 hover:bg-red-500 hover:text-white transition cursor-pointer">
                            <i class="fas fa-flag-checkered"></i>
                        </button>
                        <input type="text" id="return_location" name="return_location" value="Student Mall, UTM"
                               class="w-full bg-transparent font-bold text-white focus:outline-none border-none p-0 placeholder-gray-400 focus:ring-0">
                    </div>
                </div>

                {{-- PICKUP DATE & TIME --}}
                <div class="px-6 py-2 w-full md:w-auto md:border-r border-white/10">
                    <label class="block text-[12px] font-bold text-white uppercase tracking-wider mb-1">PICKUP DATE</label>
                    <div class="flex items-center bg-white/5 rounded-lg px-3 py-1 hover:bg-white/10 transition h-[42px]">

                        {{-- Date Input --}}
                        <input type="date" name="pickup_date" min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}"
                               class="bg-transparent border-none p-0 text-sm font-bold text-white focus:ring-0 [color-scheme:dark] cursor-pointer w-[110px]">

                        {{-- Divider --}}
                        <div class="w-px h-5 bg-white/20 mx-3"></div>

                        {{-- Time Input Group --}}
                        <div class="flex items-center">
                            {{-- Initial Value empty; JS will fill it --}}
                            <input type="hidden" name="pickup_time" id="pickup_time_hidden" value="">

                            <select id="pickup_hour" 
                                    onchange="updateHiddenTime('pickup')"
                                    class="bg-transparent text-white font-bold text-sm border-none p-0 focus:ring-0 cursor-pointer appearance-none text-center w-8">
                                @for ($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ $i == 10 ? 'selected' : '' }} class="text-black">
                                        {{ $i }}
                                    </option>
                                @endfor
                            </select>

                            <span class="text-white font-bold text-sm leading-none">:00</span>

                            <select id="pickup_ampm"
                                    class="bg-transparent text-white font-bold text-sm border-none p-0 focus:ring-0 cursor-pointer ml-1 leading-none"
                                    onchange="updateHiddenTime('pickup')">
                                <option value="AM" class="text-black">AM</option>
                                <option value="PM" class="text-black" selected>PM</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- RETURN DATE & TIME --}}
                <div class="px-6 py-2 w-full md:w-auto">
                    <label class="block text-[12px] font-bold text-white uppercase tracking-wider mb-1">RETURN DATE</label>
                    <div class="flex items-center bg-white/5 rounded-lg px-3 py-1 hover:bg-white/10 transition h-[42px]">

                        {{-- Date Input --}}
                        <input type="date" name="return_date" min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d', strtotime('+1 day')) }}"
                               class="bg-transparent border-none p-0 text-sm font-bold text-white focus:ring-0 [color-scheme:dark] cursor-pointer w-[110px]">

                        {{-- Divider --}}
                        <div class="w-px h-5 bg-white/20 mx-3"></div>

                        {{-- Time Input Group --}}
                        <div class="flex items-center">
                            {{-- Initial Value empty; JS will fill it --}}
                            <input type="hidden" name="return_time" id="return_time_hidden" value="">

                            <select id="return_hour" 
                                    onchange="updateHiddenTime('return')"
                                    class="bg-transparent text-white font-bold text-sm border-none p-0 focus:ring-0 cursor-pointer appearance-none text-center w-8">
                                @for ($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ $i == 10 ? 'selected' : '' }} class="text-black">
                                        {{ $i }}
                                    </option>
                                @endfor
                            </select>

                            <span class="text-white font-bold text-sm leading-none">:00</span>

                            <select id="return_ampm"
                                    class="bg-transparent text-white font-bold text-sm border-none p-0 focus:ring-0 cursor-pointer ml-1 leading-none"
                                    onchange="updateHiddenTime('return')">
                                <option value="AM" class="text-black">AM</option>
                                <option value="PM" class="text-black" selected>PM</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="p-1 w-full md:w-auto">
                    <button type="submit"
                            class="w-full md:w-16 h-14 bg-gradient-to-br from-orange-500 to-red-600 hover:to-red-700 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-orange-500/30 transition transform hover:scale-105 border border-white/10 group">
                        <i class="fas fa-search text-xl group-hover:scale-110 transition"></i>
                    </button>
                </div>

            </div>
        </form>
    </div>

{{-- NEW SECTION: AVAILABLE TODAY --}}
    <div class="w-full max-w-7xl mt-16 mb-20">
        <div class="flex items-end justify-between mb-8 px-4">
            <div>
                <h2 class="text-3xl font-black text-white drop-shadow-md">Available Today</h2>
                <p class="text-gray-300 text-sm mt-1">Grab a car instantly for today's journey.</p>
            </div>
            
            {{-- Navigation Buttons --}}
            <div class="hidden md:flex gap-2">
                <button id="slidePrev" class="w-10 h-10 rounded-full bg-white/10 hover:bg-orange-600 text-white flex items-center justify-center transition border border-white/10">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button id="slideNext" class="w-10 h-10 rounded-full bg-white/10 hover:bg-orange-600 text-white flex items-center justify-center transition border border-white/10">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>

        {{-- SCROLLABLE LIST --}}
        <div class="relative w-full">
            @if(isset($vehicles) && $vehicles->count() > 0)
            <div class="flex overflow-x-auto gap-5 px-4 pb-8 scroll-smooth no-scrollbar" id="carouselTrack">
                @foreach($vehicles as $vehicle)
                <div class="min-w-[280px] md:min-w-[320px] bg-black/40 backdrop-blur-md rounded-3xl overflow-hidden border border-white/15 shadow-xl relative group hover:-translate-y-2 transition duration-300 flex-shrink-0">
                    
                    {{-- Image --}}
                    <div class="h-48 overflow-hidden relative">
                        {{-- Use specific asset logic based on your system, assuming public/storage --}}
                        <img src="{{ asset('storage/' . $vehicle->image) }}" alt="{{ $vehicle->model }}" 
                             class="w-full h-full object-cover transform group-hover:scale-110 transition duration-700">
                        <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent opacity-80"></div>
                        
                        <div class="absolute top-3 right-3 bg-black/50 backdrop-blur border border-white/20 text-white px-3 py-1 rounded-full text-[10px] font-bold uppercase">
                            {{ $vehicle->type }}
                        </div>
                    </div>

                    {{-- Details --}}
                    <div class="p-5">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <p class="text-orange-500 text-[10px] font-bold uppercase tracking-wider">{{ $vehicle->brand }}</p>
                                <h3 class="text-xl font-bold text-white truncate">{{ $vehicle->model }}</h3>
                            </div>
                            <div class="text-right">
                                <p class="text-white font-bold text-lg">RM {{ $vehicle->priceHour }}</p>
                                <p class="text-gray-400 text-[10px]">/ hour</p>
                            </div>
                        </div>

                        {{-- Features --}}
                        <div class="flex gap-3 text-gray-400 mb-5 text-xs border-t border-white/10 pt-3">
                            <span class="flex items-center gap-1"><i class="fas fa-gas-pump text-orange-500"></i> {{ $vehicle->fuelType }}</span>
                            <span class="flex items-center gap-1"><i class="fas fa-palette text-orange-500"></i> {{ $vehicle->color }}</span>
                        </div>

                        {{-- Quick Book Button (Auto-fills date to today) --}}
                        <a href="{{ route('book.search', [
                                'pickup_location' => 'Student Mall, UTM',
                                'return_location' => 'Student Mall, UTM',
                                'pickup_date' => date('Y-m-d'),
                                'return_date' => date('Y-m-d', strtotime('+1 day')),
                                'pickup_time' => '10:00', // Default
                                'return_time' => '10:00', // Default
                                'types[]' => $vehicle->type 
                            ]) }}" 
                           class="block w-full py-2.5 bg-white text-black font-bold text-center rounded-xl hover:bg-orange-500 hover:text-white transition text-sm">
                            Book This Car
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-10 bg-white/5 rounded-3xl border border-white/10 mx-4">
                <i class="fas fa-car-side text-4xl text-gray-600 mb-3"></i>
                <p class="text-gray-300">All cars are fully booked for today.</p>
                <p class="text-xs text-gray-500 mt-1">Try searching for a future date above.</p>
            </div>
            @endif
        </div>
    </div>

</div>

{{-- JAVASCRIPT --}}
<script>
    // --- 1. TIME UPDATER (Converts 12h to 24h for Backend) ---
    function updateHiddenTime(type) {
    // This correctly pulls the selected value from the new dropdown
        let hour = parseInt(document.getElementById(type + '_hour').value) || 10;
        let ampm = document.getElementById(type + '_ampm').value;
        const hiddenInput = document.getElementById(type + '_time_hidden');

        let hour24 = hour;
        if (ampm === 'PM' && hour < 12) hour24 += 12;
        if (ampm === 'AM' && hour === 12) hour24 = 0;

        // Format as HH:00:00 for the backend
        hiddenInput.value = (hour24 < 10 ? '0' + hour24 : hour24) + ':00';
        
        // If you are in search_results.blade.php, also call:
        if (typeof updateAllSelectLinks === "function") {
            updateAllSelectLinks();
        }
    }

    // --- 2. DATE SYNC (Ensures Return Date is never before Pickup Date) ---
    function syncDates() {
        const pickupInput = document.querySelector('input[name="pickup_date"]');
        const returnInput = document.querySelector('input[name="return_date"]');
        
        // 1. Set the minimum allowed return date to the pickup date
        returnInput.min = pickupInput.value;

        // 2. If the current return date is strictly BEFORE pickup date, reset it
        if (returnInput.value < pickupInput.value) {
            returnInput.value = pickupInput.value;
        }
    }

    // --- 3. FINAL SUBMISSION CHECK (Enforces 1 Hour Minimum) ---
    function validateForm(e) {
        // A. Ensure times are fresh
        updateHiddenTime('pickup');
        updateHiddenTime('return');

        // B. Get Values
        const pDate = document.querySelector('input[name="pickup_date"]').value;
        const rDate = document.querySelector('input[name="return_date"]').value;
        
        // Use the visible hour/ampm values for calculation to be 100% accurate to what user sees
        const pHour = parseInt(document.getElementById('pickup_hour').value);
        const pAmPm = document.getElementById('pickup_ampm').value;
        const rHour = parseInt(document.getElementById('return_hour').value);
        const rAmPm = document.getElementById('return_ampm').value;

        // C. Convert to 24H for Calculation
        let p24 = (pAmPm === 'PM' && pHour < 12) ? pHour + 12 : (pAmPm === 'AM' && pHour === 12 ? 0 : pHour);
        let r24 = (rAmPm === 'PM' && rHour < 12) ? rHour + 12 : (rAmPm === 'AM' && rHour === 12 ? 0 : rHour);

        // D. Create Date Objects
        const start = new Date(pDate);
        start.setHours(p24, 0, 0, 0);

        const end = new Date(rDate);
        end.setHours(r24, 0, 0, 0);

        // E. Calculate Difference in Hours
        const diffMs = end - start;
        const diffHours = diffMs / (1000 * 60 * 60);

        // F. LOGIC: If difference is less than 1 hour (e.g. 0 or negative)
        if (diffHours < 1) {
            e.preventDefault(); // STOP FORM SUBMISSION
            alert("⚠️ Invalid Duration!\n\nThe return time must be at least 1 hour after the pickup time.");
            return false;
        }

        // Allow Submit
        return true;
    }

    // --- 4. INITIALIZATION ---
    window.addEventListener('DOMContentLoaded', (event) => {
        // Attach listeners
        const pDateInput = document.querySelector('input[name="pickup_date"]');
        const rDateInput = document.querySelector('input[name="return_date"]');
        
        pDateInput.addEventListener('change', syncDates);
        rDateInput.addEventListener('change', syncDates);

        // Attach Form Submit Listener
        const form = document.getElementById('searchForm');
        if (form) {
            form.addEventListener('submit', validateForm);
        }

        // Run once on load to set defaults
        updateHiddenTime('pickup');
        updateHiddenTime('return');
        syncDates();
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const track = document.getElementById('carouselTrack');
        const nextBtn = document.getElementById('slideNext');
        const prevBtn = document.getElementById('slidePrev');

        if(track && nextBtn && prevBtn) {
            nextBtn.addEventListener('click', () => {
                track.scrollBy({ left: 320, behavior: 'smooth' });
            });
            prevBtn.addEventListener('click', () => {
                track.scrollBy({ left: -320, behavior: 'smooth' });
            });
        }
    });
</script>

{{-- MAP MODAL --}}
<div id="mapModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" onclick="closeMapModal()"></div>
    
    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-4xl bg-white rounded-3xl shadow-2xl overflow-hidden">
        
        <div class="bg-gray-900 px-6 py-4 flex justify-between items-center">
            <h3 class="text-white font-bold text-lg"><i class="fas fa-map-marked-alt text-orange-500 mr-2"></i> Pin Location</h3>
            <button onclick="closeMapModal()" class="text-gray-400 hover:text-white transition"><i class="fas fa-times text-xl"></i></button>
        </div>

        <div id="leafletMap" class="w-full h-[500px] bg-gray-200 relative z-0"></div>

        <div class="px-6 py-4 bg-gray-50 flex justify-between items-center border-t border-gray-200">
            <p class="text-sm text-gray-500 font-medium" id="addressPreview">Drag marker to detect address...</p>
            <button onclick="confirmLocation()" class="bg-orange-600 hover:bg-orange-700 text-white font-bold py-2 px-8 rounded-xl shadow-lg transition transform hover:scale-105">
                Confirm Location
            </button>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
    let map;
    let marker;
    let activeInputId = '';
    let currentAddress = '';

    function initMap() {
        // UTM Skudai Default
        const defaultLat = 1.5563;
        const defaultLng = 103.6375;

        // 1. Create Map
        map = L.map('leafletMap').setView([defaultLat, defaultLng], 15);

        // 2. Add Tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        // 3. Add Marker
        marker = L.marker([defaultLat, defaultLng], {draggable: true}).addTo(map);

        // 4. Drag Event
        marker.on('dragend', function(event) {
            const position = marker.getLatLng();
            fetchAddress(position.lat, position.lng);
        });
    }

    function openMapModal(type) {
        activeInputId = type + '_location';
        document.getElementById('mapModal').classList.remove('hidden');

        // Logic to Initialize or Refresh Map
        if (!map) {
            // Delay slightly to ensure Modal DOM is rendered before Leaflet calculates size
            setTimeout(initMap, 200); 
        } else {
            setTimeout(() => { map.invalidateSize(); }, 200);
        }
    }

    function closeMapModal() {
        document.getElementById('mapModal').classList.add('hidden');
    }

    async function fetchAddress(lat, lng) {
        document.getElementById('addressPreview').innerText = "Detecting address...";
        try {
            // Using OSM Nominatim (Reverse Geocoding)
            const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`);
            const data = await response.json();
            
            if(data && data.display_name) {
                // Formatting: Take first 3 parts of address for brevity
                const parts = data.display_name.split(',');
                currentAddress = parts.slice(0, 3).join(', ');
                document.getElementById('addressPreview').innerText = currentAddress;
            } else {
                document.getElementById('addressPreview').innerText = "Address not found";
            }
        } catch (error) {
            console.error(error);
            document.getElementById('addressPreview').innerText = "Error fetching address";
        }
    }

    function confirmLocation() {
        if(currentAddress) {
            document.getElementById(activeInputId).value = currentAddress;
            closeMapModal();
        } else {
            // If user opened map and clicked confirm without dragging, get default pos
            const pos = marker.getLatLng();
            fetchAddress(pos.lat, pos.lng).then(() => {
                // Ensure state is updated before closing
                if(currentAddress) {
                    document.getElementById(activeInputId).value = currentAddress;
                }
                closeMapModal();
            });
        }
    }
</script>

@endsection