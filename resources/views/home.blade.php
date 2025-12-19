@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>

<div class="relative min-h-[calc(100vh-64px)] flex flex-col justify-between bg-cover bg-center" style="background-image: url('{{ asset('hastabg.png') }}');">
    
    <div class="absolute inset-0 bg-gradient-to-b from-black/60 via-black/20 to-black/80"></div>

    <div class="relative z-10 container mx-auto px-4 flex flex-col h-full pt-8 pb-12">
        
        <div class="flex justify-center mb-auto">
            <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-full p-1.5 flex items-center shadow-2xl">
                <a href="{{ route('home') }}" class="px-8 py-2.5 bg-[#ea580c] text-white rounded-full font-bold shadow-lg transition transform scale-105">Book a Car</a>
                <a href="{{ route('book.index') }}" class="px-8 py-2.5 text-white/80 font-bold hover:bg-white/10 rounded-full transition">My Bookings</a>
                <a href="{{ route('loyalty.index') }}" class="px-8 py-2.5 text-white/80 font-bold hover:bg-white/10 rounded-full transition">Loyalty & Rewards</a>
                <a href="{{ route('finance.index') }}" class="px-8 py-2.5 text-white/80 font-bold hover:bg-white/10 rounded-full transition">Finance</a>
            </div>
        </div>

        <div class="text-center mt-20 mb-12">
            <h1 class="text-5xl md:text-7xl font-extrabold text-white drop-shadow-2xl mb-4 tracking-tight">Drive your adventure.</h1>
            <p class="text-xl text-gray-200 font-medium drop-shadow-md tracking-wide">Premium car rental services for UTM Students & Staff.</p>
        </div>

        <div class="w-full max-w-6xl mx-auto mt-auto">
            <form action="{{ route('book.search') }}" method="GET">
                <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-[2.5rem] shadow-[0_8px_32px_0_rgba(0,0,0,0.3)] p-3 md:flex md:items-center md:space-x-4">
                    
                    <div class="flex-1 px-6 py-2 border-r border-white/10">
                        <label class="block text-[10px] font-bold text-white/60 uppercase tracking-wider mb-1">PICKUP POINT</label>
                        <div class="flex items-center">
                            <button type="button" onclick="openMapModal('pickup')" class="w-8 h-8 rounded-full bg-green-500/20 flex items-center justify-center mr-3 text-green-400 hover:bg-green-500 hover:text-white transition cursor-pointer" title="Pin Location">
                                <i class="fas fa-map-marker-alt"></i>
                            </button>
                            <input type="text" id="pickup_location" name="pickup_location" value="Student Mall, UTM" class="w-full font-bold text-white bg-transparent border-none p-0 focus:ring-0 text-base placeholder-white/50">
                        </div>
                    </div>

                    <div class="flex-1 px-6 py-2 border-r border-white/10">
                        <label class="block text-[10px] font-bold text-white/60 uppercase tracking-wider mb-1">RETURN POINT</label>
                        <div class="flex items-center">
                            <button type="button" onclick="openMapModal('return')" class="w-8 h-8 rounded-full bg-red-500/20 flex items-center justify-center mr-3 text-red-400 hover:bg-red-500 hover:text-white transition cursor-pointer" title="Pin Location">
                                <i class="fas fa-flag-checkered"></i>
                            </button>
                            <input type="text" id="return_location" name="return_location" value="Student Mall, UTM" class="w-full font-bold text-white bg-transparent border-none p-0 focus:ring-0 text-base placeholder-white/50">
                        </div>
                    </div>

                    <div class="px-6 py-2 border-r border-white/10">
                        <label class="block text-[10px] font-bold text-white/60 uppercase tracking-wider mb-1">PICKUP DATE</label>
                        <div class="flex items-center space-x-2 bg-white/10 rounded-lg px-3 py-1.5 border border-white/10">
                            <input type="date" name="pickup_date" class="bg-transparent border-none p-0 text-sm font-bold text-white focus:ring-0 [color-scheme:dark]">
                            <span class="text-white/30">|</span>
                            <input type="time" name="pickup_time" class="bg-transparent border-none p-0 text-sm font-bold text-white focus:ring-0 [color-scheme:dark]">
                        </div>
                    </div>

                    <div class="px-6 py-2">
                        <label class="block text-[10px] font-bold text-white/60 uppercase tracking-wider mb-1">RETURN DATE</label>
                        <div class="flex items-center space-x-2 bg-white/10 rounded-lg px-3 py-1.5 border border-white/10">
                            <input type="date" name="return_date" class="bg-transparent border-none p-0 text-sm font-bold text-white focus:ring-0 [color-scheme:dark]">
                            <span class="text-white/30">|</span>
                            <input type="time" name="return_time" class="bg-transparent border-none p-0 text-sm font-bold text-white focus:ring-0 [color-scheme:dark]">
                        </div>
                    </div>

                    <div class="pl-2 pr-2">
                        <button type="submit" class="w-14 h-14 bg-[#ea580c] hover:bg-orange-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-orange-500/30 transition transform hover:scale-105 border border-white/10">
                            <i class="fas fa-search text-xl"></i>
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>
</div>

<div id="mapModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" onclick="closeMapModal()"></div>
    
    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-4xl bg-white rounded-3xl shadow-2xl overflow-hidden">
        
        <div class="bg-gray-900 px-6 py-4 flex justify-between items-center">
            <h3 class="text-white font-bold text-lg"><i class="fas fa-map-marked-alt text-orange-500 mr-2"></i> Pin Location</h3>
            <button onclick="closeMapModal()" class="text-gray-400 hover:text-white transition"><i class="fas fa-times text-xl"></i></button>
        </div>

        <div id="leafletMap" class="w-full h-[500px] bg-gray-200 relative z-0"></div>

        <div class="px-6 py-4 bg-gray-50 flex justify-between items-center border-t border-gray-200">
            <p class="text-sm text-gray-500" id="addressPreview">Drag marker to detect address...</p>
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

    // Initialize Map
    function initMap() {
        // UTM Skudai Coordinates
        const defaultLat = 1.5563;
        const defaultLng = 103.6375;

        // Create Map
        map = L.map('leafletMap').setView([defaultLat, defaultLng], 15);

        // Add OpenStreetMap Tiles (Free)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        // Add Draggable Marker
        marker = L.marker([defaultLat, defaultLng], {draggable: true}).addTo(map);

        // Event: When marker is dragged
        marker.on('dragend', function(event) {
            const position = marker.getLatLng();
            fetchAddress(position.lat, position.lng);
        });
    }

    // Open Modal
    function openMapModal(type) {
        activeInputId = type + '_location';
        document.getElementById('mapModal').classList.remove('hidden');

        if (!map) {
            setTimeout(initMap, 200); // Initialize if not exists
        } else {
            setTimeout(() => { map.invalidateSize(); }, 200); // Refresh fix
        }
    }

    // Close Modal
    function closeMapModal() {
        document.getElementById('mapModal').classList.add('hidden');
    }

    // Reverse Geocoding (Convert Lat/Lng to Text) using Nominatim (Free)
    async function fetchAddress(lat, lng) {
        document.getElementById('addressPreview').innerText = "Detecting address...";
        try {
            const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`);
            const data = await response.json();
            
            if(data && data.display_name) {
                // Simplify address (take first 3 parts)
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

    // Confirm Button Click
    function confirmLocation() {
        if(currentAddress) {
            document.getElementById(activeInputId).value = currentAddress;
            closeMapModal();
        } else {
            // If user didn't drag, fetch current position
            const pos = marker.getLatLng();
            fetchAddress(pos.lat, pos.lng).then(() => {
                document.getElementById(activeInputId).value = currentAddress;
                closeMapModal();
            });
        }
    }
</script>
@endsection