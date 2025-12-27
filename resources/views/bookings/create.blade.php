@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>

{{-- 1. BACKGROUND --}}
<div class="fixed inset-0 z-0">
    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('hastabg.png') }}');"></div>
    <div class="absolute inset-0 bg-gradient-to-b from-black/80 via-black/50 to-black/90"></div>
</div>

{{-- 2. MAIN CONTENT --}}
<div class="relative z-10 w-full min-h-screen flex flex-col justify-start items-center px-4 pt-32 md:pt-48">

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
                    <label class="block text-[10px] font-bold text-gray-300 uppercase tracking-wider mb-1">PICKUP POINT</label>
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
                    <label class="block text-[10px] font-bold text-gray-300 uppercase tracking-wider mb-1">RETURN POINT</label>
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
                    <label class="block text-[10px] font-bold text-gray-300 uppercase tracking-wider mb-1">PICKUP DATE</label>
                    <div class="flex items-center bg-white/5 rounded-lg px-3 py-1 hover:bg-white/10 transition h-[42px]">

                        {{-- Date Input --}}
                        <input type="date" name="pickup_date" min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}"
                               class="bg-transparent border-none p-0 text-sm font-bold text-white focus:ring-0 [color-scheme:dark] cursor-pointer w-[110px]">

                        {{-- Divider --}}
                        <div class="w-px h-5 bg-white/20 mx-3"></div>

                        {{-- Time Input Group --}}
                        <div class="flex items-center">
                            <input type="hidden" name="pickup_time" id="pickup_time_hidden" value="10:00">

                            <input type="number" id="pickup_hour" min="1" max="12" value="10"
                                   class="w-10 bg-transparent text-center text-white font-bold text-sm p-0 border-none focus:ring-0 appearance-none leading-none"
                                   oninput="updateHiddenTime('pickup')">

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
                    <label class="block text-[10px] font-bold text-gray-300 uppercase tracking-wider mb-1">RETURN DATE</label>
                    <div class="flex items-center bg-white/5 rounded-lg px-3 py-1 hover:bg-white/10 transition h-[42px]">

                        {{-- Date Input --}}
                        <input type="date" name="return_date" min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d', strtotime('+1 day')) }}"
                               class="bg-transparent border-none p-0 text-sm font-bold text-white focus:ring-0 [color-scheme:dark] cursor-pointer w-[110px]">

                        {{-- Divider --}}
                        <div class="w-px h-5 bg-white/20 mx-3"></div>

                        {{-- Time Input Group --}}
                        <div class="flex items-center">
                            <input type="hidden" name="return_time" id="return_time_hidden" value="10:00">

                            <input type="number" id="return_hour" min="1" max="12" value="10"
                                   class="w-10 bg-transparent text-center text-white font-bold text-sm p-0 border-none focus:ring-0 appearance-none leading-none"
                                   oninput="updateHiddenTime('return')">

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
</div>

{{-- JAVASCRIPT --}}
<script>
function updateHiddenTime(type) {
    let hour = parseInt(document.getElementById(type + '_hour').value) || 10;
    let ampm = document.getElementById(type + '_ampm').value;
    const hiddenInput = document.getElementById(type + '_time_hidden');

    if (hour < 1) hour = 1;
    if (hour > 12) hour = 12;

    let hour24 = hour;
    if (ampm === 'PM' && hour < 12) hour24 += 12;
    if (ampm === 'AM' && hour === 12) hour24 = 0;

    hiddenInput.value = (hour24 < 10 ? '0' + hour24 : hour24) + ':00';
}
</script>

{{-- Spacing --}}
<div class="col-span-1 md:col-span-2 lg:col-span-3 flex flex-col items-center justify-center py-24 text-center"></div>
<div class="h-24 bg-gray-50"></div>

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