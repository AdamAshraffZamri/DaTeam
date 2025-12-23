@extends('layouts.app')

{{-- Load Leaflet CSS --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>

@section('content')
{{-- 1. FIXED BACKGROUND --}}
<div class="fixed inset-0 z-0">
    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('hastabg.png') }}');"></div>
    <div class="absolute inset-0 bg-gradient-to-b from-black/80 via-black/50 to-black/90"></div>
</div>

{{-- 2. SCROLLABLE CONTENT --}}
<div class="relative z-10 min-h-[calc(100vh-64px)] pb-20">
    
    {{-- STICKY SEARCH BAR --}}
    <div class="bg-black/25 backdrop-blur-xl border-b border-white/15 sticky top-0 z-30 shadow-2xl">
        <div class="container mx-auto px-4 py-4">
            <form action="{{ route('book.search') }}" method="GET">
                
                {{-- Flex container with 'justify-center' to fix alignment --}}
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
                            <input type="date" name="pickup_date" value="{{ request('pickup_date', now()->format('Y-m-d')) }}" 
                                class="bg-transparent border-none text-sm font-bold text-white p-0 focus:ring-0 [color-scheme:dark] w-full md:w-auto">
                            
                            <div class="w-px h-4 bg-white/20 mx-2 hidden md:block"></div>
                            
                            <div class="flex items-center">
                                <input type="hidden" name="pickup_time" id="pickup_time_search_hidden" value="{{ request('pickup_time', '10:00') }}">
                                <input type="number" id="pickup_hour_search" min="1" max="12" value="10" 
                                    class="w-10 bg-transparent text-center text-white font-bold text-sm p-0 border-none focus:ring-0 appearance-none"
                                    oninput="updateSearchTime('pickup')">
                                <span class="text-white font-bold text-sm -ml-0.5">:00</span>
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
                            <input type="date" name="return_date" value="{{ request('return_date', now()->addDay()->format('Y-m-d')) }}" 
                                class="bg-transparent border-none text-sm font-bold text-white p-0 focus:ring-0 [color-scheme:dark] w-full md:w-auto">
                            
                            <div class="w-px h-4 bg-white/20 mx-2 hidden md:block"></div>

                            <div class="flex items-center">
                                <input type="hidden" name="return_time" id="return_time_search_hidden" value="{{ request('return_time', '10:00') }}">
                                <input type="number" id="return_hour_search" min="1" max="12" value="10" 
                                    class="w-10 bg-transparent text-center text-white font-bold text-sm p-0 border-none focus:ring-0 appearance-none"
                                    oninput="updateSearchTime('return')">
                                <span class="text-white font-bold text-sm -ml-0.5">:00</span>
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
                <div class="bg-black/25 backdrop-blur-[2px] border border-white/15 rounded-3xl p-6 sticky top-32 shadow-xl">
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
                {{-- Vehicle Card --}}
                <div class="group bg-black/25 backdrop-blur-[2px] border border-white/15 rounded-3xl p-6 shadow-xl hover:shadow-orange-500/10 hover:bg-white/15 transition-all duration-300 relative overflow-hidden flex flex-col h-full">
                    
                    {{-- Plate Tag --}}
                    <div class="absolute top-5 right-5 z-10">
                        <span class="bg-black/60 text-gray-300 text-[10px] font-bold px-3 py-1 rounded-full border border-white/10 uppercase tracking-tighter backdrop-blur-md">
                            {{ $vehicle->plateNo }}
                        </span>
                    </div>

                    {{-- Image Area --}}
                    <div class="h-48 flex items-center justify-center mb-4 relative overflow-hidden rounded-2xl bg-black/30 border border-white/5">
                        <div class="absolute inset-0 bg-gradient-to-tr from-orange-500/10 to-transparent opacity-50"></div>
                        <i class="fas fa-car-side text-6xl text-white/10 group-hover:text-white/20 transition-colors"></i>
                    </div>

                    <div class="space-y-3 flex-grow">
                        <div>
                            <h3 class="text-xl font-black text-white">{{ $vehicle->model }}</h3>
                            <div class="flex items-center space-x-1 text-yellow-500 text-sm mt-1">
                                <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                                <span class="text-gray-500 font-medium ml-1">(5.0)</span>
                            </div>
                        </div>

                        {{-- Specs (Brief) --}}
                        <div class="grid grid-cols-2 gap-2 text-xs text-gray-400">
                            <div class="flex items-center"><i class="fas fa-chair w-6 text-center text-orange-500"></i> 5 Seats</div>
                            <div class="flex items-center"><i class="fas fa-cogs w-6 text-center text-orange-500"></i> Auto</div>
                            <div class="flex items-center"><i class="fas fa-gas-pump w-6 text-center text-orange-500"></i> {{ $vehicle->fuelType ?? 'Petrol' }}</div>
                            <div class="flex items-center"><i class="fas fa-snowflake w-6 text-center text-orange-500"></i> A/C</div>
                        </div>
                    </div>

                    {{-- Action Footer --}}
                    <div class="pt-4 mt-4 border-t border-white/10 flex items-center justify-between">
                        <div>
                            <span class="block text-[10px] text-gray-500 font-bold uppercase tracking-widest">Daily Rate</span>
                            <span class="text-xl font-black text-white">RM {{ number_format($vehicle->priceHour * 24, 0) }}</span>
                        </div>
                        
                        <div class="flex items-center space-x-2">
                            {{-- VIEW DETAILS BUTTON --}}
                            <button onclick="document.getElementById('details-modal-{{ $vehicle->VehicleID }}').classList.remove('hidden')" 
                                class="bg-white/10 hover:bg-white/20 text-white px-4 py-2 rounded-xl font-bold text-sm transition-all border border-white/10">
                                Details
                            </button>

                            {{-- NEW CODE: Added 'select-btn' class --}}
                            <a href="{{ route('book.payment', [
                                'id' => $vehicle->VehicleID, 
                                'pickup_date' => request('pickup_date'), 
                                'return_date' => request('return_date'),
                                'pickup_time' => request('pickup_time'),
                                'return_time' => request('return_time'),
                                'pickup_location' => request('pickup_location'),
                                'return_location' => request('return_location')
                            ]) }}" class="select-btn bg-[#ea580c] hover:bg-orange-600 text-white px-5 py-2 rounded-xl font-bold text-sm shadow-lg transition-all transform hover:scale-105 border border-white/10">
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

{{-- 3. VEHICLE DETAILS MODALS (Loop) --}}
@foreach($vehicles as $vehicle)
<div id="details-modal-{{ $vehicle->VehicleID }}" class="fixed inset-0 z-[60] hidden" role="dialog" aria-modal="true">
    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/90 backdrop-blur-md transition-opacity" 
         onclick="document.getElementById('details-modal-{{ $vehicle->VehicleID }}').classList.add('hidden')"></div>

    <div class="relative z-10 flex items-center justify-center min-h-screen p-4 pointer-events-none">
        <div class="bg-[#151515] border border-white/10 w-full max-w-3xl rounded-[2.5rem] shadow-2xl overflow-hidden pointer-events-auto flex flex-col md:flex-row max-h-[85vh] md:max-h-[600px]">
            
            {{-- Left: Image & Stats --}}
            <div class="w-full md:w-5/12 bg-white/5 relative flex flex-col justify-between p-6">
                <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-20"></div>
                
                <div class="relative z-10">
                    <div class="h-40 md:h-56 flex items-center justify-center bg-black/20 rounded-2xl mb-6 border border-white/5">
                         <i class="fas fa-car text-8xl text-white/10"></i>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-black/40 rounded-xl p-3 border border-white/5 text-center">
                            <i class="fas fa-tachometer-alt text-orange-500 mb-1"></i>
                            <p class="text-[10px] text-gray-400">Mileage</p>
                            <p class="text-white font-bold text-sm">Unlimited</p>
                        </div>
                        <div class="bg-black/40 rounded-xl p-3 border border-white/5 text-center">
                            <i class="fas fa-gas-pump text-orange-500 mb-1"></i>
                            <p class="text-[10px] text-gray-400">Fuel</p>
                            <p class="text-white font-bold text-sm">{{ $vehicle->fuelType ?? 'Petrol' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right: Description & Action --}}
            <div class="w-full md:w-7/12 p-8 flex flex-col bg-gradient-to-br from-[#1a1a1a] to-black">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h2 class="text-3xl font-black text-white tracking-tight">{{ $vehicle->model }}</h2>
                        <div class="flex items-center space-x-2 mt-1">
                            <span class="bg-green-500/20 text-green-400 text-[10px] font-bold px-2 py-0.5 rounded border border-green-500/30">AVAILABLE</span>
                            <span class="text-gray-500 text-xs">{{ $vehicle->plateNo }}</span>
                        </div>
                    </div>
                    <button onclick="document.getElementById('details-modal-{{ $vehicle->VehicleID }}').classList.add('hidden')" class="text-gray-500 hover:text-white transition">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <div class="flex-grow overflow-y-auto pr-2 mb-6 custom-scrollbar">
                    <h3 class="text-sm font-bold text-gray-300 mb-2 uppercase tracking-wide">Description</h3>
                    <p class="text-gray-400 text-sm leading-relaxed">
                        Experience the comfort and performance of the {{ $vehicle->model }}. 
                        Perfect for students and staff needing reliable transportation around UTM. 
                        Features modern amenities, fuel efficiency, and a clean interior.
                    </p>

                    <h3 class="text-sm font-bold text-gray-300 mt-6 mb-3 uppercase tracking-wide">Features</h3>
                    <div class="grid grid-cols-2 gap-y-2 gap-x-4 text-sm text-gray-400">
                        <div class="flex items-center"><i class="fas fa-check text-green-500 mr-2 text-xs"></i> Air Conditioning</div>
                        <div class="flex items-center"><i class="fas fa-check text-green-500 mr-2 text-xs"></i> Power Steering</div>
                        <div class="flex items-center"><i class="fas fa-check text-green-500 mr-2 text-xs"></i> Bluetooth Audio</div>
                        <div class="flex items-center"><i class="fas fa-check text-green-500 mr-2 text-xs"></i> 5-Star Safety</div>
                    </div>
                </div>

                <div class="pt-6 border-t border-white/10 flex items-center justify-between">
                    <div>
                        <p class="text-[10px] text-gray-500 font-bold uppercase">Total for 1 Day</p>
                        <p class="text-2xl font-black text-white">RM {{ number_format($vehicle->priceHour * 24, 0) }}</p>
                    </div>
                    <a href="{{ route('book.payment', [
                        'id' => $vehicle->VehicleID, 
                        'pickup_date' => request('pickup_date'), 
                        'return_date' => request('return_date'),
                        'pickup_time' => request('pickup_time'),
                        'return_time' => request('return_time'),
                        'pickup_location' => request('pickup_location'),
                        'return_location' => request('return_location')
                    ]) }}" class="bg-[#ea580c] hover:bg-orange-600 text-white px-8 py-3 rounded-xl font-bold shadow-lg transition-all transform hover:scale-105">
                        Book Now
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endforeach

{{-- 4. MAP MODAL --}}
<div id="mapModal" class="fixed inset-0 z-[60] hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
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

<script>
    // --- 1. LIVE LINK UPDATER (The Fix) ---
    function updateAllSelectLinks() {
        // Get current values from the visible inputs
        const pLoc = document.querySelector('input[name="pickup_location"]').value;
        const rLoc = document.querySelector('input[name="return_location"]').value;
        const pDate = document.querySelector('input[name="pickup_date"]').value;
        const rDate = document.querySelector('input[name="return_date"]').value;
        
        // Get hidden time values (e.g. "14:00")
        const pTime = document.getElementById('pickup_time_search_hidden').value;
        const rTime = document.getElementById('return_time_search_hidden').value;

        // Loop through every "Select" button and update its URL
        document.querySelectorAll('.select-btn').forEach(btn => {
            try {
                let url = new URL(btn.href);
                url.searchParams.set('pickup_location', pLoc);
                url.searchParams.set('return_location', rLoc);
                url.searchParams.set('pickup_date', pDate);
                url.searchParams.set('return_date', rDate);
                url.searchParams.set('pickup_time', pTime);
                url.searchParams.set('return_time', rTime);
                btn.href = url.toString();
            } catch (e) {
                console.error("Error updating link:", e);
            }
        });
    }

    // --- 2. TIME INPUT LOGIC ---
    function updateSearchTime(type) {
        let hour = parseInt(document.getElementById(type + '_hour_search').value) || 10;
        let ampm = document.getElementById(type + '_ampm_search').value;
        const hiddenInput = document.getElementById(type + '_time_search_hidden');

        if (hour < 1) hour = 1;
        if (hour > 12) hour = 12;

        let hour24 = hour;
        if (ampm === 'PM' && hour < 12) hour24 = hour + 12;
        if (ampm === 'AM' && hour === 12) hour24 = 0;

        const formattedTime = (hour24 < 10 ? '0' + hour24 : hour24) + ':00';
        hiddenInput.value = formattedTime;

        // TRIGGER UPDATE
        updateAllSelectLinks();
    }

    // --- 3. INIT & EVENT LISTENERS ---
    window.onload = function() {
        initSearchTime('pickup');
        initSearchTime('return');
        
        // Add listeners to text/date inputs so typing updates links immediately
        const inputs = document.querySelectorAll('input[name="pickup_location"], input[name="return_location"], input[name="pickup_date"], input[name="return_date"]');
        inputs.forEach(input => {
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
            if (hour24 === 0) {
                hour12 = 12;
                ampm = 'AM';
            }

            document.getElementById(type + '_hour_search').value = hour12;
            document.getElementById(type + '_ampm_search').value = ampm;
        }
    }

    // --- 4. MAP MODAL LOGIC ---
    let map = null;
    let marker = null;
    let activeInputId = null; 
    const defaultLat = 1.5593; 
    const defaultLng = 103.6378;

    function openMapModal(type) {
        activeInputId = type;
        const modal = document.getElementById('mapModal');
        modal.classList.remove('hidden');

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
                // TRIGGER UPDATE IMMEDIATELY AFTER MAP SELECTION
                updateAllSelectLinks(); 
            }
        }
        closeMapModal();
    }
</script>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: rgba(255,255,255,0.05); }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 2px; }
</style>
@endsection