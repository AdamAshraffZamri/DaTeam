@extends('layouts.app')

@section('content')

{{-- CUSTOM STYLES --}}
<style>
    /* Hide scrollbar */
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    
    /* GLASS AESTHETIC (For Fleet, Stats, Features) */
    .glass-section {
        background-color: #111; /* Pitch dark background */
        position: relative;
        overflow: hidden;
    }
    
    .glass-card {
        background: rgba(255, 255, 255, 0.05); /* 5% White opacity */
        backdrop-filter: blur(12px);            /* Blur effect */
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.1); /* Subtle white border */
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.5);  /* Deep shadow */
        transition: transform 0.5s ease, background 0.5s ease;
    }

    .glass-card:hover {
        background: rgba(255, 255, 255, 0.08); /* Slightly lighter on hover */
        transform: translateY(-10px);          /* Float up effect */
    }

    /* Animation Utilities */
    @keyframes fade-in-up {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in-up { animation: fade-in-up 1s ease-out forwards; }
    .delay-100 { animation-delay: 100ms; }
</style>

{{-- SECTION 1: HERO (With Custom Gradient Overlay + Bottom Fade) --}}
<div class="relative h-screen min-h-[600px] flex flex-col justify-center bg-gray-900 overflow-hidden">
    
    {{-- Background Image --}}
    <div class="absolute inset-0 w-full h-full">
        {{-- 1. Your Custom Gradient Overlay (For Text Readability) --}}
        <div class="absolute inset-0 bg-gradient-to-r from-black/60 via-black/35 to-black/65 z-10"></div>
        
        <img src="{{ asset('hastabg.png') }}" alt="Background" class="w-full h-full object-cover">
    </div>

    {{-- Content Container --}}
    <div class="relative z-20 container mx-auto px-6 md:px-12 flex flex-col h-full justify-center items-center gap-10 pt-10 pb-20">
        
        {{-- Navigation Pill --}}
        <div class="flex justify-center animate-fade-in-up">
            <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-full p-1.5 flex flex-wrap justify-center md:flex-nowrap items-center shadow-2xl">
                <a href="{{ route('book.create') }}" class="px-6 md:px-8 py-2.5 text-white/90 font-bold hover:bg-white/10 rounded-full transition text-sm md:text-base">
                    Book Now
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

        {{-- Hero Text --}}
        <div class="max-w-4xl mx-auto text-center animate-fade-in-up delay-100">
            <h1 class="text-5xl md:text-8xl font-black text-white mb-6 leading-tight tracking-tighter">
                ELEVATE <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-500 to-red-600">YOUR JOURNEY</span>
            </h1>
            <p class="text-lg md:text-xl text-gray-300 font-light mb-10 max-w-2xl mx-auto leading-relaxed">
                Experience the freedom of movement with HASTA. Affordable, reliable, and premium vehicles curated for UTM students.
            </p>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('book.create') }}" class="px-10 py-4 bg-orange-600 hover:bg-orange-700 text-white font-bold rounded-full transition transform hover:scale-105 shadow-lg shadow-orange-600/30 flex items-center justify-center">
                    Book a Vehicle <i class="fas fa-arrow-right ml-3"></i>
                </a>
                <a href="#fleet-showcase" class="px-10 py-4 bg-white/10 hover:bg-white/20 text-white font-bold rounded-full backdrop-blur-md border border-white/20 transition flex items-center justify-center">
                    View Fleet
                </a>
            </div>
        </div>
    </div>

    {{-- 2. Decorative Bottom Fade (Seamless transition to next section) --}}
    <div class="absolute bottom-0 left-0 w-full h-32 bg-gradient-to-t from-[#111] to-transparent z-20"></div>
</div>

{{-- SECTION: CURRENT DEALS (Smart Fit for Any Image Size) --}}
<div class="bg-[#111] py-16 relative overflow-hidden border-b border-white/5">
    <div class="container mx-auto px-4 relative z-10">
        {{-- Title --}}
        <div class="text-center mb-10">
            <h2 class="text-3xl md:text-5xl font-black text-white mb-2">Current Deals</h2>
            <p class="text-gray-400">Exclusive promotions tailored for you.</p>
        </div>

        {{-- Slideshow Container --}}
        <div class="relative w-full max-w-6xl mx-auto h-[350px] md:h-[500px] rounded-[2rem] overflow-hidden shadow-[0_0_40px_rgba(234,88,12,0.15)] border border-white/10 group bg-black">
            
            {{-- Slide 1: Example of a Poster/Flyer --}}
            <div class="deal-slide absolute inset-0 transition-opacity duration-1000 opacity-100" data-index="0">
                {{-- 1. Blurred Background (Fills empty space) --}}
                <div class="absolute inset-0">
                    <img src="{{ asset('iklan1.png') }}" class="w-full h-full object-cover blur-2xl scale-110 opacity-50">
                </div>
                {{-- 2. Main Poster Image (Fully Visible) --}}
                <img src="{{ asset('iklan1.png') }}" alt="Deal 1" class="relative z-10 w-full h-full object-contain p-4 md:p-0 drop-shadow-2xl">
                
                {{-- Optional Text Overlay (Delete this div if your image already has text) --}}
                <div class="absolute bottom-0 left-0 w-full bg-gradient-to-t from-black/90 to-transparent p-8 z-20 flex flex-col items-center md:items-start">
                    <span class="bg-orange-600 text-white px-3 py-1 rounded-full text-xs font-bold shadow-lg mb-2">LIMITED TIME</span>
                    <h3 class="text-2xl md:text-4xl font-black text-white">Diwali Special Promotion</h3>
                </div>
            </div>

            {{-- Slide 2 --}}
            <div class="deal-slide absolute inset-0 transition-opacity duration-1000 opacity-0" data-index="1">
                <div class="absolute inset-0">
                    <img src="{{ asset('iklan2.png') }}" class="w-full h-full object-cover blur-2xl scale-110 opacity-50">
                </div>
                <img src="{{ asset('iklan2.png') }}" alt="Deal 2" class="relative z-10 w-full h-full object-contain p-4 md:p-0 drop-shadow-2xl">
                
                <div class="absolute bottom-0 left-0 w-full bg-gradient-to-t from-black/90 to-transparent p-8 z-20 flex flex-col items-center md:items-start">
                     <span class="bg-blue-600 text-white px-3 py-1 rounded-full text-xs font-bold shadow-lg mb-2">Delivery</span>
                    <h3 class="text-2xl md:text-4xl font-black text-white">Deliver Vehicle to your preferred location</h3>
                </div>
            </div>

            {{-- Slide 3 --}}
            <div class="deal-slide absolute inset-0 transition-opacity duration-1000 opacity-0" data-index="2">
                <div class="absolute inset-0">
                    <img src="{{ asset('iklan3.png') }}" class="w-full h-full object-cover blur-2xl scale-110 opacity-50">
                </div>
                <img src="{{ asset('iklan3.png') }}" alt="Deal 3" class="relative z-10 w-full h-full object-contain p-4 md:p-0 drop-shadow-2xl">
                
                <div class="absolute bottom-0 left-0 w-full bg-gradient-to-t from-black/90 to-transparent p-8 z-20 flex flex-col items-center md:items-start">
                     <span class="bg-green-600 text-white px-3 py-1 rounded-full text-xs font-bold shadow-lg mb-2">REWARDS</span>
                    <h3 class="text-2xl md:text-4xl font-black text-white">Free 1 hour rental</h3>
                </div>
            </div>

            {{-- Indicators --}}
            <div class="absolute bottom-6 right-8 flex gap-3 z-30">
                <button class="w-12 h-1.5 rounded-full bg-orange-500 transition-all duration-300 deal-indicator" onclick="manualSetSlide(0)"></button>
                <button class="w-3 h-1.5 rounded-full bg-white/30 hover:bg-white transition-all duration-300 deal-indicator" onclick="manualSetSlide(1)"></button>
                <button class="w-3 h-1.5 rounded-full bg-white/30 hover:bg-white transition-all duration-300 deal-indicator" onclick="manualSetSlide(2)"></button>
            </div>
        </div>
    </div>
</div>

{{-- SECTION 2: FLEET (Glass Design) --}}
<div id="fleet-showcase" class="glass-section py-24 border-b border-white/5">
    
    {{-- Decorative Glow --}}
    <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-orange-600/10 blur-[120px] rounded-full pointer-events-none"></div>

    <div class="container mx-auto px-4 mb-12 flex justify-between items-end relative z-10">
        <div>
            <h2 class="text-4xl md:text-5xl font-black text-white mb-2">Our Fleet</h2>
            <p class="text-gray-400">Browsing {{ count($vehicles) }} premium vehicles.</p>
        </div>
        
        <div class="hidden md:flex gap-3">
            <button id="slidePrev" class="w-12 h-12 rounded-full border border-gray-600 text-white flex items-center justify-center hover:bg-white hover:text-black transition">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button id="slideNext" class="w-12 h-12 rounded-full bg-white text-black flex items-center justify-center hover:bg-orange-600 hover:text-white transition shadow-lg">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </div>

    <div class="relative w-full z-10">
        @if($vehicles->count() > 0)
        <div class="flex overflow-x-auto gap-8 px-4 pb-12 scroll-smooth no-scrollbar" id="carouselTrack">
            @foreach($vehicles as $vehicle)
            <div class="glass-card w-[320px] md:w-[400px] rounded-[2.5rem] relative group flex-shrink-0">                
                
                <div class="h-64 w-full flex items-center justify-center relative mt-6 perspective-1000">
                    <img src="{{ $vehicle->image_url }}" alt="{{ $vehicle->model }}" 
                         class="w-[85%] object-contain drop-shadow-2xl transform group-hover:scale-110 group-hover:-rotate-2 transition duration-500 ease-out z-10">
                    <div class="absolute top-0 left-6 bg-black/40 backdrop-blur-md border border-white/10 text-white px-4 py-1.5 rounded-full text-xs font-bold z-20">
                        {{ strtoupper($vehicle->type) }}
                    </div>
                </div>
                <div class="p-8 relative bg-gradient-to-b from-transparent to-black/40 rounded-b-[2.5rem]">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <p class="text-orange-500 text-xs font-bold tracking-widest uppercase mb-1">{{ $vehicle->brand }}</p>
                            <h3 class="text-3xl font-bold text-white truncate max-w-[200px]">{{ $vehicle->model }}</h3>
                        </div>
                        <div class="text-right">
                            <p class="text-white font-bold text-2xl">RM {{ number_format($vehicle->priceHour, 0) }}</p>
                            <p class="text-gray-400 text-xs">/ hour</p>
                        </div>
                    </div>
                    <div class="flex gap-3 text-gray-300 mb-8 text-sm">
                        <div class="flex items-center gap-2 bg-white/5 px-3 py-2 rounded-xl border border-white/5">
                            <i class="fas fa-gas-pump text-orange-500"></i> {{ $vehicle->fuelType }}
                        </div>
                        <div class="flex items-center gap-2 bg-white/5 px-3 py-2 rounded-xl border border-white/5">
                            <i class="fas fa-calendar-alt text-orange-500"></i> {{ $vehicle->year }}
                        </div>
                        <div class="flex items-center gap-2 bg-white/5 px-3 py-2 rounded-xl border border-white/5">
                            <i class="fas fa-palette text-orange-500"></i> {{ $vehicle->color }}
                        </div>
                    </div>
                    <a href="{{ route('book.create', ['vehicle_id' => $vehicle->VehicleID]) }}" class="block w-full py-4 bg-white text-black font-bold text-center rounded-2xl hover:bg-orange-600 hover:text-white transition shadow-[0_0_20px_rgba(255,255,255,0.2)]">
                        Rent Now
                    </a>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-12 text-gray-500">
            <p>No vehicles are currently available.</p>
        </div>
        @endif
    </div>
</div>

{{-- SECTION 3: STATS (Glass Style) --}}
<div class="bg-[#111] py-20 border-b border-white/5">
    <div class="container mx-auto px-4">
        <div class="glass-card rounded-[2rem] py-12 px-6 flex flex-wrap justify-center gap-16 text-center border border-white/5 bg-white/5">
            <div>
                <h3 class="text-5xl font-black text-white mb-2">10+</h3>
                <p class="text-gray-400 font-medium uppercase tracking-wider text-sm">Affordable Vehicles</p>
            </div>
            {{-- Vertical Divider (Hidden on mobile) --}}
            <div class="hidden md:block w-px bg-white/10 h-20"></div>
            <div>
                <h3 class="text-5xl font-black text-white mb-2">3k+</h3>
                <p class="text-gray-400 font-medium uppercase tracking-wider text-sm">Happy Students</p>
            </div>
            {{-- Vertical Divider --}}
            <div class="hidden md:block w-px bg-white/10 h-20"></div>
            <div>
                <h3 class="text-5xl font-black text-white mb-2">24/7</h3>
                <p class="text-gray-400 font-medium uppercase tracking-wider text-sm">Support Team</p>
            </div>
        </div>
    </div>
</div>

{{-- SECTION 4: FEATURES (Glass Style) --}}
<div class="glass-section py-24 relative">
    {{-- Ambient Glow --}}
    <div class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-blue-600/10 blur-[120px] rounded-full pointer-events-none"></div>

    <div class="container mx-auto px-4 relative z-10">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-black text-white mb-4">Why Choose Hasta?</h2>
            <p class="text-gray-400 max-w-2xl mx-auto text-lg">We provide the most reliable and student-friendly car rental service in UTM.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            {{-- Card 1 --}}
            <div class="glass-card p-10 rounded-[2.5rem] group hover:-translate-y-2 transition duration-500 relative">
                <div class="w-20 h-20 bg-gradient-to-br from-orange-500 to-red-600 rounded-2xl flex items-center justify-center text-white text-3xl mb-8 shadow-lg shadow-orange-500/30 group-hover:scale-110 transition">
                    <i class="fas fa-wallet"></i>
                </div>
                <h3 class="text-2xl font-bold text-white mb-4">Student Prices</h3>
                <p class="text-gray-400 leading-relaxed group-hover:text-gray-200 transition">Affordable rates designed specifically for UTM students. No hidden fees, ever.</p>
            </div>

            {{-- Card 2 --}}
            <div class="glass-card p-10 rounded-[2.5rem] group hover:-translate-y-2 transition duration-500 relative">
                <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-2xl flex items-center justify-center text-white text-3xl mb-8 shadow-lg shadow-blue-500/30 group-hover:scale-110 transition">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3 class="text-2xl font-bold text-white mb-4">Fully Insured</h3>
                <p class="text-gray-400 leading-relaxed group-hover:text-gray-200 transition">Drive with peace of mind. All our vehicles come with comprehensive insurance coverage.</p>
            </div>

            {{-- Card 3 --}}
            <div class="glass-card p-10 rounded-[2.5rem] group hover:-translate-y-2 transition duration-500 relative">
                <div class="w-20 h-20 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl flex items-center justify-center text-white text-3xl mb-8 shadow-lg shadow-green-500/30 group-hover:scale-110 transition">
                    <i class="fas fa-bolt"></i>
                </div>
                <h3 class="text-2xl font-bold text-white mb-4">Instant Access</h3>
                <p class="text-gray-400 leading-relaxed group-hover:text-gray-200 transition">Book in seconds using our digital platform. Drive the car whenever you need it.</p>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // --- FLEET CAROUSEL LOGIC ---
        const track = document.getElementById('carouselTrack');
        const nextBtn = document.getElementById('slideNext');
        const prevBtn = document.getElementById('slidePrev');
        let autoScrollInterval;

        function autoScroll() {
            if (!track) return;
            if (track.scrollLeft + track.clientWidth >= track.scrollWidth - 10) {
                track.scrollTo({ left: 0, behavior: 'smooth' });
            } else {
                track.scrollBy({ left: 420, behavior: 'smooth' });
            }
        }

        if(track && track.childElementCount > 1) {
             autoScrollInterval = setInterval(autoScroll, 3000);

            if(nextBtn) {
                nextBtn.addEventListener('click', () => {
                    clearInterval(autoScrollInterval);
                    track.scrollBy({ left: 420, behavior: 'smooth' });
                    autoScrollInterval = setInterval(autoScroll, 4000);
                });
            }

            if(prevBtn) {
                prevBtn.addEventListener('click', () => {
                    clearInterval(autoScrollInterval);
                    track.scrollBy({ left: -420, behavior: 'smooth' });
                    autoScrollInterval = setInterval(autoScroll, 4000);
                });
            }

            track.addEventListener('mouseenter', () => clearInterval(autoScrollInterval));
            track.addEventListener('mouseleave', () => autoScrollInterval = setInterval(autoScroll, 3000));
        }

        // --- NEW: DEALS SLIDESHOW LOGIC ---
        const slides = document.querySelectorAll('.deal-slide');
        const indicators = document.querySelectorAll('.deal-indicator');
        let currentSlide = 0;
        let dealInterval;

        window.manualSetSlide = function(index) {
            clearInterval(dealInterval);
            showSlide(index);
            startDealAutoPlay();
        };

        function showSlide(index) {
            // Reset all slides
            slides.forEach((slide) => {
                slide.classList.remove('opacity-100');
                slide.classList.add('opacity-0');
            });
            
            // Reset indicators
            indicators.forEach((ind) => {
                ind.classList.remove('w-12', 'bg-orange-500');
                ind.classList.add('w-3', 'bg-white/30');
            });

            // Activate current
            slides[index].classList.remove('opacity-0');
            slides[index].classList.add('opacity-100');
            
            indicators[index].classList.remove('w-3', 'bg-white/30');
            indicators[index].classList.add('w-12', 'bg-orange-500');

            currentSlide = index;
        }

        function nextSlide() {
            let next = (currentSlide + 1) % slides.length;
            showSlide(next);
        }

        function startDealAutoPlay() {
            dealInterval = setInterval(nextSlide, 5000); // Change every 5 seconds
        }

        if(slides.length > 0) {
            startDealAutoPlay();
        }
    });
</script>
@endsection