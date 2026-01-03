@extends('layouts.app')

@section('content')
{{-- SECTION 1: HERO (Professional Brand Focus) --}}
<div class="relative h-screen min-h-[600px] flex flex-col justify-center bg-cover bg-center overflow-hidden" style="background-image: url('{{ asset('hastabg.png') }}');">
    
    {{-- Dark Overlay --}}
    <div class="absolute inset-0 bg-gradient-to-r from-black/60 via-black/35 to-black/65"></div>

    {{-- Main Container: Added justify-center and gap-8 to bring elements closer --}}
    <div class="relative z-10 container mx-auto px-6 md:px-12 flex flex-col h-full justify-center items-center gap-10 pt-10 pb-20">
        
        {{-- Navigation Pill (Centered) --}}
        <div class="flex justify-center animate-fade-in-up">
            <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-full p-1.5 flex flex-wrap justify-center md:flex-nowrap items-center shadow-2xl">
                <a href="{{ route('book.create') }}" class="px-6 md:px-8 py-2.5 text-white/90 font-bold hover:bg-white/10 rounded-full transition text-sm md:text-base">
                    Book a Car
                </a>
                <a href="{{ route('book.index') }}" class="px-6 md:px-8 py-2.5 text-white/90 font-bold hover:bg-white/10 rounded-full transition text-sm md:text-base">
                    My Bookings
                </a>
                <a href="{{ route('loyalty.index') }}" class="px-6 md:px-8 py-2.5 text-white/90 font-bold hover:bg-white/10 rounded-full transition text-sm md:text-base">
                    Loyalty
                </a>
                <a href="{{ route('finance.index') }}" class="px-6 md:px-8 py-2.5 text-white/90 font-bold hover:bg-white/10 rounded-full transition text-sm md:text-base">
                    Payments
                </a>
            </div>
        </div>

        {{-- Main Hero Content --}}
        <div class="max-w-4xl mx-auto text-center animate-fade-in-up delay-100">
            <h1 class="text-5xl md:text-8xl font-black text-white mb-6 leading-tight tracking-tighter">
                ELEVATE <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-500 to-red-600">YOUR JOURNEY</span>
            </h1>
            <p class="text-lg md:text-xl text-gray-300 font-light mb-10 max-w-2xl mx-auto leading-relaxed">
                Experience the freedom of movement with HASTA. Affordable, reliable, and premium vehicles curated for UTM students and staff.
            </p>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('book.create') }}" class="px-10 py-4 bg-orange-600 hover:bg-orange-700 text-white font-bold rounded-full transition transform hover:scale-105 shadow-lg shadow-orange-600/30 flex items-center justify-center">
                    Book Now <i class="fas fa-arrow-right ml-3"></i>
                </a>
                <a href="#fleet-showcase" class="px-10 py-4 bg-white/10 hover:bg-white/20 text-white font-bold rounded-full backdrop-blur-md border border-white/20 transition flex items-center justify-center">
                    View Fleet
                </a>
            </div>
        </div>
    </div>

    {{-- Decorative Bottom Fade --}}
    <div class="absolute bottom-0 left-0 w-full h-32 bg-gradient-to-t from-gray-800 to-transparent"></div>
</div>


    {{-- SECTION 2: AUTOMATIC FLEET CAROUSEL --}}
<div id="fleet-showcase" class="bg-gray-800 py-24 border-b border-gray-800 overflow-hidden">
    <div class="container mx-auto px-4 mb-12 flex justify-between items-end">
        <div>
            <h2 class="text-4xl font-black text-white mb-2">Our Entire Fleet</h2>
            <p class="text-gray-400">Browsing {{ count($vehicles) }} available vehicles ready for you.</p>
        </div>
        
        <div class="hidden md:flex gap-2">
            <button id="slidePrev" class="w-12 h-12 rounded-full border border-gray-700 text-white flex items-center justify-center hover:bg-orange-600 hover:border-orange-600 transition">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button id="slideNext" class="w-12 h-12 rounded-full border border-gray-700 text-white flex items-center justify-center hover:bg-orange-600 hover:border-orange-600 transition">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </div>

    <div class="relative w-full">
        @if($vehicles->count() > 0)
        <div class="flex overflow-x-auto gap-6 px-4 pb-8 scroll-smooth no-scrollbar" id="carouselTrack">
            @foreach($vehicles as $vehicle)
            {{-- Adjusted to a muted dark gray with glass effect and subtle border --}}
            <div class="min-w-[300px] md:min-w-[400px] bg-gray-800/50 backdrop-blur-sm rounded-3xl overflow-hidden border border-white/10 shadow-2xl relative group hover:-translate-y-2 transition duration-300 flex-shrink-0">
                
                {{-- Image Area --}}
                <div class="h-64 overflow-hidden relative">
                    <img src="{{ $vehicle->image_url }}" alt="{{ $vehicle->model }}" class="w-full h-full object-cover transform group-hover:scale-110 transition duration-700">
                    <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-transparent to-transparent"></div>
                    
                    <div class="absolute top-4 right-4 bg-white/10 backdrop-blur-md border border-white/20 text-white px-3 py-1 rounded-full text-xs font-bold">
                        {{ strtoupper($vehicle->type) }}
                    </div>
                </div>

                {{-- Content Area --}}
                <div class="p-6 relative">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <p class="text-orange-500 text-xs font-bold tracking-wider uppercase mb-1">{{ $vehicle->brand }}</p>
                            <h3 class="text-2xl font-bold text-white truncate max-w-[200px]">{{ $vehicle->model }}</h3>
                        </div>
                        <div class="text-right">
                            <p class="text-white font-bold text-xl">RM {{ number_format($vehicle->priceHour, 0) }}</p>
                            <p class="text-gray-400 text-xs">/ hour</p>
                        </div>
                    </div>

                    {{-- Features Icons --}}
                    <div class="flex gap-4 text-gray-400 mb-6 text-sm border-t border-white/5 pt-4">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-gas-pump text-orange-500"></i> {{ $vehicle->fuelType }}
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fas fa-calendar-alt text-orange-500"></i> {{ $vehicle->year }}
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fas fa-palette text-orange-500"></i> {{ $vehicle->color }}
                        </div>
                    </div>

                    <a href="{{ route('book.create', ['vehicle_id' => $vehicle->VehicleID]) }}" class="block w-full py-3 bg-white text-gray-900 font-bold text-center rounded-xl hover:bg-orange-500 hover:text-white transition">
                        Rent Now
                    </a>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-12 text-gray-500">
            <p>No vehicles are currently available. Please check back later.</p>
        </div>
        @endif
    </div>
</div>

{{-- SECTION 3: STATS --}}
<div class="bg-gray-900/80 backdrop-blur-md py-20 border-b border-gray-800">
    <div class="container mx-auto px-4 flex flex-wrap justify-center gap-16 text-center">
        <div>
            <h3 class="text-5xl font-black text-white mb-2">50+</h3>
            <p class="text-gray-400 font-medium uppercase tracking-wider">Premium Vehicles</p>
        </div>
        <div>
            <h3 class="text-5xl font-black text-white mb-2">1k+</h3>
            <p class="text-gray-400 font-medium uppercase tracking-wider">Happy Students</p>
        </div>
        <div>
            <h3 class="text-5xl font-black text-white mb-2">24/7</h3>
            <p class="text-gray-400 font-medium uppercase tracking-wider">Support Team</p>
        </div>
    </div>
</div>

{{-- SECTION 4: FEATURES --}}
<div class="py-24 bg-gray-800">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            {{-- Updated headings to white --}}
            <h2 class="text-4xl font-black text-white mb-4">Why Choose Hasta?</h2>
            <p class="text-gray-400 max-w-2xl mx-auto text-lg">We provide the most reliable and student-friendly car rental service in UTM.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
            {{-- Card 1: Updated to white background with dark text --}}
            <div class="group p-8 rounded-[2rem] bg-gray-300 hover:bg-orange-600 transition duration-500 shadow-xl">
                <div class="w-16 h-16 bg-orange-100 rounded-2xl flex items-center justify-center text-orange-600 text-3xl mb-8 group-hover:bg-white group-hover:text-orange-600 transition">
                    <i class="fas fa-wallet"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-4 group-hover:text-white">Student Prices</h3>
                <p class="text-gray-600 leading-relaxed group-hover:text-white/90">Affordable rates designed specifically for UTM students. No hidden fees, ever.</p>
            </div>

            {{-- Card 2: Updated to white background with dark text --}}
            <div class="group p-8 rounded-[2rem] bg-gray-300 hover:bg-blue-600 transition duration-500 shadow-xl">
                <div class="w-16 h-16 bg-blue-100 rounded-2xl flex items-center justify-center text-blue-600 text-3xl mb-8 group-hover:bg-white group-hover:text-blue-600 transition">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-4 group-hover:text-white">Fully Insured</h3>
                <p class="text-gray-600 leading-relaxed group-hover:text-white/90">Drive with peace of mind. All our vehicles come with comprehensive insurance coverage.</p>
            </div>

            {{-- Card 3: Updated to white background with dark text --}}
            <div class="group p-8 rounded-[2rem] bg-gray-300 hover:bg-green-600 transition duration-500 shadow-xl">
                <div class="w-16 h-16 bg-green-100 rounded-2xl flex items-center justify-center text-green-600 text-3xl mb-8 group-hover:bg-white group-hover:text-green-600 transition">
                    <i class="fas fa-bolt"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-4 group-hover:text-white">Instant Access</h3>
                <p class="text-gray-600 leading-relaxed group-hover:text-white/90">Book in seconds using our digital platform. No paperwork, just drive.</p>
            </div>
        </div>
    </div>
</div>

<style>
    /* Hide scrollbar for Chrome, Safari and Opera */
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
    /* Hide scrollbar for IE, Edge and Firefox */
    .no-scrollbar {
        -ms-overflow-style: none;  /* IE and Edge */
        scrollbar-width: none;  /* Firefox */
    }
    
    @keyframes fade-in-up {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in-up {
        animation: fade-in-up 1s ease-out forwards;
    }
    .delay-100 {
        animation-delay: 100ms;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const track = document.getElementById('carouselTrack');
        const nextBtn = document.getElementById('slideNext');
        const prevBtn = document.getElementById('slidePrev');
        let autoScrollInterval;

        // Auto Scroll Function
        function autoScroll() {
            if (!track) return;
            // If we've reached the end (roughly), scroll back to start
            if (track.scrollLeft + track.clientWidth >= track.scrollWidth - 10) {
                track.scrollTo({ left: 0, behavior: 'smooth' });
            } else {
                track.scrollBy({ left: 350, behavior: 'smooth' });
            }
        }

        // Initialize Auto Scroll
        if(track && track.childElementCount > 1) {
             autoScrollInterval = setInterval(autoScroll, 3000); // Change every 3 seconds

            // Manual Navigation
            if(nextBtn) {
                nextBtn.addEventListener('click', () => {
                    clearInterval(autoScrollInterval);
                    track.scrollBy({ left: 350, behavior: 'smooth' });
                    autoScrollInterval = setInterval(autoScroll, 4000);
                });
            }

            if(prevBtn) {
                prevBtn.addEventListener('click', () => {
                    clearInterval(autoScrollInterval);
                    track.scrollBy({ left: -350, behavior: 'smooth' });
                    autoScrollInterval = setInterval(autoScroll, 4000);
                });
            }

            // Pause on hover
            track.addEventListener('mouseenter', () => clearInterval(autoScrollInterval));
            track.addEventListener('mouseleave', () => autoScrollInterval = setInterval(autoScroll, 3000));
        }
    });
</script>
@endsection