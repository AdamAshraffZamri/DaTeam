@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>

{{-- SECTION 1: HERO & SEARCH (Your Code) --}}
<div class="relative min-h-[calc(100vh-64px)] flex flex-col justify-between bg-cover bg-center" style="background-image: url('{{ asset('hastabg.png') }}');">
    
    <div class="absolute inset-0 bg-gradient-to-b from-black/60 via-black/20 to-black/80"></div>

    <div class="relative z-10 container mx-auto px-4 flex flex-col h-full pt-8 pb-12">
        
        {{-- Navigation Pill --}}
        <div class="flex justify-center mb-auto">
            <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-full p-1.5 flex items-center shadow-2xl">
                <a href="{{ route('book.create') }}" class="px-8 py-2.5 text-white/80 font-bold hover:bg-white/10 rounded-full transition">
                    Book a Car
                </a>
                <a href="{{ route('book.index') }}" class="px-8 py-2.5 text-white/80 font-bold hover:bg-white/10 rounded-full transition">
                    My Bookings
                </a>
                <a href="{{ route('loyalty.index') }}" class="px-8 py-2.5 text-white/80 font-bold hover:bg-white/10 rounded-full transition">
                    Loyalty
                </a>
                <a href="{{ route('finance.index') }}" class="px-8 py-2.5 text-white/80 font-bold hover:bg-white/10 rounded-full transition">
                    Payments
                </a>
            </div>
        </div>

        {{-- Hero Text --}}
        <div class="text-center mt-20 mb-12">
            <h1 class="text-5xl md:text-7xl font-extrabold text-white drop-shadow-2xl mb-4 tracking-tight">Drive your adventure.</h1>
            <p class="text-xl text-gray-200 font-medium drop-shadow-md tracking-wide">Premium car rental services for UTM Students & Staff.</p>
        </div>

        {{-- Search Form --}}
        <div class="w-full max-w-6xl mx-auto mt-auto">
            <form action="{{ route('book.search') }}" method="GET">
                <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-[2.5rem] shadow-[0_8px_32px_0_rgba(0,0,0,0.3)] p-3 md:flex md:items-center md:space-x-4">
                    
                    {{-- Pickup Location --}}
                    <div class="flex-1 px-6 py-2 border-r border-white/10">
                        <label class="block text-[10px] font-bold text-white/60 uppercase tracking-wider mb-1">PICKUP POINT</label>
                        <div class="flex items-center">
                            <button type="button" onclick="openMapModal('pickup')" class="w-8 h-8 rounded-full bg-green-500/20 flex items-center justify-center mr-3 text-green-400 hover:bg-green-500 hover:text-white transition cursor-pointer" title="Pin Location">
                                <i class="fas fa-map-marker-alt"></i>
                            </button>
                            <input type="text" id="pickup_location" name="pickup_location" value="Student Mall, UTM" class="w-full font-bold text-white bg-transparent border-none p-0 focus:ring-0 text-base placeholder-white/50">
                        </div>
                    </div>

                    {{-- Return Location --}}
                    <div class="flex-1 px-6 py-2 border-r border-white/10">
                        <label class="block text-[10px] font-bold text-white/60 uppercase tracking-wider mb-1">RETURN POINT</label>
                        <div class="flex items-center">
                            <button type="button" onclick="openMapModal('return')" class="w-8 h-8 rounded-full bg-red-500/20 flex items-center justify-center mr-3 text-red-400 hover:bg-red-500 hover:text-white transition cursor-pointer" title="Pin Location">
                                <i class="fas fa-flag-checkered"></i>
                            </button>
                            <input type="text" id="return_location" name="return_location" value="Student Mall, UTM" class="w-full font-bold text-white bg-transparent border-none p-0 focus:ring-0 text-base placeholder-white/50">
                        </div>
                    </div>

                    {{-- Pickup Date/Time --}}
                    <div class="px-6 py-2 border-r border-white/10">
                        <label class="block text-[10px] font-bold text-white/60 uppercase tracking-wider mb-1">PICKUP DATE</label>
                        <div class="flex items-center space-x-2 bg-white/10 rounded-lg px-3 py-1.5 border border-white/10">
                            <input type="date" name="pickup_date" value="{{ date('Y-m-d') }}" class="bg-transparent border-none p-0 text-sm font-bold text-white focus:ring-0 [color-scheme:dark]">
                            <span class="text-white/30">|</span>
                            {{-- Using Select for Hours Only --}}
                            <select name="pickup_time" class="bg-transparent border-none p-0 text-sm font-bold text-white focus:ring-0 cursor-pointer appearance-none">
                                @for($i = 8; $i <= 22; $i++) 
                                    <option value="{{ sprintf('%02d:00', $i) }}" class="text-black" {{ $i == 10 ? 'selected' : '' }}>{{ sprintf('%02d:00', $i) }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    {{-- Return Date/Time --}}
                    <div class="px-6 py-2">
                        <label class="block text-[10px] font-bold text-white/60 uppercase tracking-wider mb-1">RETURN DATE</label>
                        <div class="flex items-center space-x-2 bg-white/10 rounded-lg px-3 py-1.5 border border-white/10">
                            <input type="date" name="return_date" value="{{ date('Y-m-d', strtotime('+1 day')) }}" class="bg-transparent border-none p-0 text-sm font-bold text-white focus:ring-0 [color-scheme:dark]">
                            <span class="text-white/30">|</span>
                            {{-- Using Select for Hours Only --}}
                            <select name="return_time" class="bg-transparent border-none p-0 text-sm font-bold text-white focus:ring-0 cursor-pointer appearance-none">
                                @for($i = 8; $i <= 22; $i++)
                                    <option value="{{ sprintf('%02d:00', $i) }}" class="text-black" {{ $i == 10 ? 'selected' : '' }}>{{ sprintf('%02d:00', $i) }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    {{-- Search Button --}}
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

{{-- SECTION 2: STATS --}}
<div class="bg-gray-900 py-10 border-b border-gray-800">
    <div class="container mx-auto px-4 flex flex-wrap justify-center gap-12 text-center">
        <div>
            <h3 class="text-4xl font-bold text-white">50+</h3>
            <p class="text-gray-400 text-sm uppercase tracking-wider mt-1">Premium Vehicles</p>
        </div>
        <div>
            <h3 class="text-4xl font-bold text-white">1k+</h3>
            <p class="text-gray-400 text-sm uppercase tracking-wider mt-1">Happy Students</p>
        </div>
        <div>
            <h3 class="text-4xl font-bold text-white">24/7</h3>
            <p class="text-gray-400 text-sm uppercase tracking-wider mt-1">Roadside Support</p>
        </div>
    </div>
</div>

{{-- SECTION 3: FEATURES --}}
<div class="py-20 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl font-black text-gray-900 mb-4">Why Choose Hasta?</h2>
            <p class="text-gray-600 max-w-2xl mx-auto">We provide the most reliable and student-friendly car rental service in UTM.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            {{-- Feature 1 --}}
            <div class="bg-white p-8 rounded-3xl shadow-xl shadow-gray-200/50 hover:-translate-y-2 transition duration-300">
                <div class="w-14 h-14 bg-orange-100 rounded-2xl flex items-center justify-center text-orange-600 text-2xl mb-6">
                    <i class="fas fa-wallet"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Student Friendly Prices</h3>
                <p class="text-gray-500 leading-relaxed">Affordable rates designed specifically for UTM students. No hidden fees, ever.</p>
            </div>

            {{-- Feature 2 --}}
            <div class="bg-white p-8 rounded-3xl shadow-xl shadow-gray-200/50 hover:-translate-y-2 transition duration-300">
                <div class="w-14 h-14 bg-blue-100 rounded-2xl flex items-center justify-center text-blue-600 text-2xl mb-6">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Fully Insured</h3>
                <p class="text-gray-500 leading-relaxed">Drive with peace of mind. All our vehicles come with comprehensive insurance coverage.</p>
            </div>

            {{-- Feature 3 --}}
            <div class="bg-white p-8 rounded-3xl shadow-xl shadow-gray-200/50 hover:-translate-y-2 transition duration-300">
                <div class="w-14 h-14 bg-green-100 rounded-2xl flex items-center justify-center text-green-600 text-2xl mb-6">
                    <i class="fas fa-bolt"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Instant Booking</h3>
                <p class="text-gray-500 leading-relaxed">Book in seconds using our digital platform. No paperwork, just drive.</p>
            </div>
        </div>
    </div>
</div>

{{-- SECTION 4: HOW IT WORKS --}}
<div class="py-20 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl font-black text-gray-900">How It Works</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-12 relative">
            {{-- Connector Line (Desktop Only) --}}
            <div class="hidden md:block absolute top-12 left-[20%] right-[20%] h-0.5 bg-gray-100 -z-10"></div>

            <div class="text-center">
                <div class="w-24 h-24 bg-white border-4 border-orange-500 rounded-full flex items-center justify-center text-3xl font-bold text-orange-500 mx-auto mb-6 shadow-lg">1</div>
                <h3 class="text-lg font-bold mb-2">Choose Car</h3>
                <p class="text-gray-500 text-sm">Select from our wide range of premium vehicles.</p>
            </div>
            
            <div class="text-center">
                <div class="w-24 h-24 bg-white border-4 border-orange-500 rounded-full flex items-center justify-center text-3xl font-bold text-orange-500 mx-auto mb-6 shadow-lg">2</div>
                <h3 class="text-lg font-bold mb-2">Pick Up</h3>
                <p class="text-gray-500 text-sm">Meet us at Student Mall or your preferred location.</p>
            </div>

            <div class="text-center">
                <div class="w-24 h-24 bg-white border-4 border-orange-500 rounded-full flex items-center justify-center text-3xl font-bold text-orange-500 mx-auto mb-6 shadow-lg">3</div>
                <h3 class="text-lg font-bold mb-2">Drive Away</h3>
                <p class="text-gray-500 text-sm">Enjoy your ride! Return it when you're done.</p>
            </div>
        </div>
    </div>
</div>

{{-- SECTION 5: FOOTER --}}
<footer class="bg-gray-900 text-white py-12 border-t border-gray-800">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row justify-between items-center">
            <div class="mb-6 md:mb-0">
                <h2 class="text-2xl font-black tracking-tight">HASTA<span class="text-orange-500">.</span></h2>
                <p class="text-gray-500 text-sm mt-2">© 2025 DaTeam. All rights reserved.</p>
            </div>
            <div class="flex space-x-6 text-gray-400">
                <a href="#" class="hover:text-white transition">Privacy Policy</a>
                <a href="#" class="hover:text-white transition">Terms of Service</a>
                <a href="#" class="hover:text-white transition">Contact Support</a>
            </div>
        </div>
    </div>
</footer>

{{-- MAP MODAL (Scripts included) --}}
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
            <button onclick="confirmLocation()" class="bg-orange-600 hover:bg-orange-700 text-white font-bold py-2 px-8 rounded-xl shadow-lg transition transform hover:scale-105">Confirm Location</button>
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
        const defaultLat = 1.5563;
        const defaultLng = 103.6375;
        map = L.map('leafletMap').setView([defaultLat, defaultLng], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap contributors' }).addTo(map);
        marker = L.marker([defaultLat, defaultLng], {draggable: true}).addTo(map);
        marker.on('dragend', function(event) {
            const position = marker.getLatLng();
            fetchAddress(position.lat, position.lng);
        });
    }

    function openMapModal(type) {
        activeInputId = type + '_location';
        document.getElementById('mapModal').classList.remove('hidden');
        if (!map) { setTimeout(initMap, 200); } else { setTimeout(() => { map.invalidateSize(); }, 200); }
    }

    function closeMapModal() {
        document.getElementById('mapModal').classList.add('hidden');
    }

    async function fetchAddress(lat, lng) {
        document.getElementById('addressPreview').innerText = "Detecting address...";
        try {
            const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`);
            const data = await response.json();
            if(data && data.display_name) {
                const parts = data.display_name.split(',');
                currentAddress = parts.slice(0, 3).join(', ');
                document.getElementById('addressPreview').innerText = currentAddress;
            } else { document.getElementById('addressPreview').innerText = "Address not found"; }
        } catch (error) { console.error(error); }
    }

    function confirmLocation() {
        if(currentAddress) {
            document.getElementById(activeInputId).value = currentAddress;
            closeMapModal();
        } else {
            const pos = marker.getLatLng();
            fetchAddress(pos.lat, pos.lng).then(() => {
                document.getElementById(activeInputId).value = currentAddress;
                closeMapModal();
            });
        }
    }
</script>
@endsection