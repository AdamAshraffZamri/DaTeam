@extends('layouts.app')

@section('content')

{{-- CUSTOM STYLES (ORIGINAL RESTORED) --}}
<style>
    /* Hide scrollbar */
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    
    /* GLASS AESTHETIC */
    .glass-section {
        background-color: #111;
        position: relative;
        overflow: hidden;
    }
    
    .glass-card {
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.5);
        transition: transform 0.5s ease, background 0.5s ease;
    }

    .glass-card:hover {
        background: rgba(255, 255, 255, 0.08);
        transform: translateY(-10px);
    }

    /* Animation Utilities */
    @keyframes fade-in-up {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in-up { animation: fade-in-up 1s ease-out forwards; }
    .delay-100 { animation-delay: 100ms; }

    /* --- ORIGINAL GLOW BUTTON STYLE --- */
    .glow-on-hover {
        border: none;
        outline: none;
        color: #fff;
        background: #fb5901ff;
        cursor: pointer;
        position: relative;
        z-index: 0;
        border-radius: 9999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        text-decoration: none;
    }

    .glow-on-hover:before {
        content: '';
        background: linear-gradient(45deg, #ff0000, #ff7300, #fffb00, #48ff00, #00ffd5, #002bff, #7a00ff, #ff00c8, #ff0000);
        position: absolute;
        top: -2px;
        left: -2px;
        background-size: 400%;
        z-index: -1;
        filter: blur(5px);
        width: calc(100% + 4px);
        height: calc(100% + 4px);
        animation: glowing 20s linear infinite;
        opacity: 0;
        transition: opacity .3s ease-in-out;
        border-radius: 9999px;
    }

    .glow-on-hover:active { color: #000; }
    .glow-on-hover:active:after { background: transparent; }
    .glow-on-hover:hover:before { opacity: 1; }

    .glow-on-hover:after {
        z-index: -1;
        content: '';
        position: absolute;
        width: 100%;
        height: 100%;
        background: #fb5901ff;
        left: 0;
        top: 0;
        border-radius: 9999px;
    }

    @keyframes glowing {
        0% { background-position: 0 0; }
        50% { background-position: 400% 0; }
        100% { background-position: 0 0; }
    }

    /* --- ORIGINAL FLEET ANIMATED BUTTON --- */
    .fleet-anim-btn {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 180px;
        height: 55px;
        border: 2px solid #FFFFFF;
        border-radius: 100px;
        color: white;
        font-weight: bold;
        text-decoration: none;
        background: transparent;
        transition: all 0.3s ease;
        overflow: visible;
        cursor: pointer;
    }

    .fleet-anim-btn:hover {
        background: white;
        color: #2C3940;
        box-shadow: 0px 10px 25px -5px rgba(255, 255, 255, 0.4);
    }

    .fleet-anim-btn .btn-text {
        position: relative;
        transition: transform 0.4s ease;
        z-index: 10;
    }
    .fleet-anim-btn:hover .btn-text { transform: translateX(5px); }

    .fleet-spot {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 4px;
        height: 4px;
        border-radius: 50%;
        background-color: white;
        opacity: 0;
        pointer-events: none;
        z-index: 0;
    }

    .fleet-anim-btn:hover .fleet-spot:nth-child(odd) { background-color: #00C4FF; }
    .fleet-anim-btn:hover .fleet-spot:nth-child(even) { background-color: #FF5E00; }

    @keyframes spew {
        0% { opacity: 0; transform: translate(0, 0); }
        20% { opacity: 1; }
        100% { opacity: 0; transform: translate(var(--tx), var(--ty)); }
    }

    .fleet-anim-btn:hover .fleet-spot { animation: spew 0.8s ease-out infinite; }

    .fleet-spot:nth-child(1) { --tx: -20px; --ty: -30px; animation-delay: 0s; }
    .fleet-spot:nth-child(2) { --tx: 20px; --ty: -40px; animation-delay: 0.1s; }
    .fleet-spot:nth-child(3) { --tx: -30px; --ty: 10px; animation-delay: 0.2s; }
    .fleet-spot:nth-child(4) { --tx: 35px; --ty: 20px; animation-delay: 0.05s; }
    .fleet-spot:nth-child(5) { --tx: -10px; --ty: -50px; animation-delay: 0.3s; }
    .fleet-spot:nth-child(6) { --tx: 40px; --ty: -10px; animation-delay: 0.15s; }
    .fleet-spot:nth-child(7) { --tx: -40px; --ty: 30px; animation-delay: 0.25s; }
    .fleet-spot:nth-child(8) { --tx: 25px; --ty: 40px; animation-delay: 0.1s; }
    .fleet-spot:nth-child(9) { --tx: 0px; --ty: -60px; animation-delay: 0.4s; }
    .fleet-spot:nth-child(10) { --tx: -50px; --ty: 0px; animation-delay: 0.2s; }
    .fleet-spot:nth-child(11) { --tx: 50px; --ty: 5px; animation-delay: 0.35s; }
    .fleet-spot:nth-child(12) { --tx: 10px; --ty: 50px; animation-delay: 0.05s; }
</style>

{{-- SECTION 1: HERO --}}
<div class="relative h-screen min-h-[600px] flex flex-col justify-center bg-gray-900 overflow-hidden">
    
    {{-- Background Image --}}
    <div class="absolute inset-0 w-full h-full">
        <div class="absolute inset-0 bg-gradient-to-r from-black/60 via-black/35 to-black/65 z-10"></div>
        <img src="{{ asset('hastabg.png') }}" alt="Background" class="w-full h-full object-cover">
    </div>

    {{-- Content Container --}}
    <div class="relative z-20 container mx-auto px-4 md:px-12 flex flex-col h-full justify-center items-center gap-8 md:gap-10 pt-24 md:pt-10 pb-20">
        
        {{-- Navigation Pill --}}
        <div class="w-full flex justify-center py-4 md:py-6 relative z-40">
            {{-- 
                Mobile Fixes:
                1. w-fit + mx-auto: Centers the container.
                2. max-w-full: Prevents overflowing the screen width.
                3. px-4: Ensures a small gap from the screen edges.
            --}}
            <div class="w-fit max-w-full px-4 mx-auto overflow-x-auto no-scrollbar">
                
                {{-- Container --}}
                <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-full p-1 md:p-1.5 flex items-center shadow-2xl">
                    
                    {{-- Book Now --}}
                    {{-- Updated: text-xs (was text-[10px]) and px-4 (was px-3) for better mobile visibility --}}
                    <a href="{{ route('book.create') }}" 
                       class="px-4 sm:px-6 py-2 sm:py-2.5 rounded-full font-bold text-[10px] sm:text-[15px] transition-all duration-300 whitespace-nowrap active:scale-95
                       {{ (request()->routeIs('book.create') || request()->routeIs('book.search') || request()->routeIs('book.show') || request()->routeIs('book.payment') || request()->routeIs('book.payment.submit')) 
                           ? 'nav-link-active' 
                           : 'text-white hover:bg-white/10' }}">
                        Book Now
                    </a>
                    
                    {{-- My Bookings --}}
                    <a href="{{ route('book.index') }}" 
                       class="px-4 sm:px-6 py-2 sm:py-2.5 rounded-full font-bold text-[10px] sm:text-[15px] transition-all duration-300 whitespace-nowrap active:scale-95
                       {{ (request()->routeIs('book.index') || request()->routeIs('book.cancel')) 
                           ? 'nav-link-active' 
                           : 'text-white hover:bg-white/10' }}">
                        My Bookings
                    </a>
                    
                    {{-- Loyalty --}}
                    <a href="{{ route('loyalty.index') }}" 
                       class="px-4 sm:px-6 py-2 sm:py-2.5 rounded-full font-bold text-[10px] sm:text-[15px] transition-all duration-300 whitespace-nowrap active:scale-95
                       {{ (request()->routeIs('loyalty.index') || request()->routeIs('loyalty.redeem') || request()->routeIs('voucher.apply') || request()->routeIs('voucher.available')) 
                           ? 'nav-link-active' 
                           : 'text-white hover:bg-white/10' }}">
                        Loyalty
                    </a>
                    
                    {{-- Payments --}}
                    <a href="{{ route('finance.index') }}" 
                       class="px-4 sm:px-6 py-2 sm:py-2.5 rounded-full font-bold text-[10px] sm:text-[15px] transition-all duration-300 whitespace-nowrap active:scale-95
                       {{ (request()->routeIs('finance.index') || request()->routeIs('finance.claim') || request()->routeIs('finance.pay') || request()->routeIs('finance.submit_balance') || request()->routeIs('finance.pay_fine') || request()->routeIs('finance.submit_fine')) 
                           ? 'nav-link-active' 
                           : 'text-white hover:bg-white/10' }}">
                        Payments
                    </a>
                </div>
            </div>
        </div>

        {{-- Hero Text --}}
        <div class="max-w-4xl mx-auto text-center animate-fade-in-up delay-100 px-2">
            <h1 class="text-5xl md:text-8xl font-black text-white mb-4 md:mb-6 leading-tight tracking-tighter">
                ELEVATE <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-500 to-red-600">YOUR JOURNEY</span>
            </h1>
            <p class="text-base md:text-xl text-gray-300 font-light mb-8 md:mb-10 max-w-2xl mx-auto leading-relaxed">
                Experience the freedom of movement with HASTA. Affordable, reliable, and premium vehicles curated for UTM students.
            </p>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                {{-- RESTORED GLOW BUTTON --}}
                <a href="{{ route('book.create') }}"
                class="glow-on-hover px-10 py-4 transition transform hover:scale-105 shadow-lg flex items-center justify-center">
                    Book a Vehicle <i class="fas fa-arrow-right ml-3"></i>
                </a>

                {{-- RESTORED ANIMATED FLEET BUTTON --}}
                <a href="#fleet-showcase" class="fleet-anim-btn">
                    <span class="btn-text">View Fleet</span>
                    <span class="fleet-spot"></span><span class="fleet-spot"></span><span class="fleet-spot"></span>
                    <span class="fleet-spot"></span><span class="fleet-spot"></span><span class="fleet-spot"></span>
                    <span class="fleet-spot"></span><span class="fleet-spot"></span><span class="fleet-spot"></span>
                    <span class="fleet-spot"></span><span class="fleet-spot"></span><span class="fleet-spot"></span>
                </a>
            </div>
        </div>
    </div>

    {{-- Bottom Fade --}}
    <div class="absolute bottom-0 left-0 w-full h-32 bg-gradient-to-t from-[#111] to-transparent z-20"></div>
</div>

{{-- SECTION: CURRENT DEALS --}}
<div class="bg-[#111] py-12 md:py-16 relative overflow-hidden border-b border-white/5">
    <div class="container mx-auto px-4 relative z-10">
        <div class="text-center mb-8 md:mb-10">
            <h2 class="text-3xl md:text-5xl font-black text-white mb-2">Current Deals</h2>
            <p class="text-gray-400 text-sm md:text-base">Exclusive promotions tailored for you.</p>
        </div>

        <div class="relative w-full max-w-6xl mx-auto h-[300px] md:h-[500px] rounded-[2rem] overflow-hidden shadow-[0_0_40px_rgba(234,88,12,0.15)] border border-white/10 group bg-black">
            
            {{-- Slide 1 --}}
            <div class="deal-slide absolute inset-0 transition-opacity duration-1000 opacity-100" data-index="0">
                <div class="absolute inset-0">
                    <img src="{{ asset('iklan1.png') }}" class="w-full h-full object-cover blur-2xl scale-110 opacity-50">
                </div>
                <img src="{{ asset('iklan1.png') }}" alt="Deal 1" class="relative z-10 w-full h-full object-contain p-4 md:p-0 drop-shadow-2xl">
                
                <div class="absolute bottom-0 left-0 w-full bg-gradient-to-t from-black/90 to-transparent p-6 md:p-8 z-20 flex flex-col items-center md:items-start">
                    <span class="bg-orange-600 text-white px-3 py-1 rounded-full text-[10px] md:text-xs font-bold shadow-lg mb-2">LIMITED TIME</span>
                    <h3 class="text-xl md:text-4xl font-black text-white text-center md:text-left">Diwali Special Promotion</h3>
                </div>
            </div>

            {{-- Slide 2 --}}
            <div class="deal-slide absolute inset-0 transition-opacity duration-1000 opacity-0" data-index="1">
                <div class="absolute inset-0">
                    <img src="{{ asset('iklan2.png') }}" class="w-full h-full object-cover blur-2xl scale-110 opacity-50">
                </div>
                <img src="{{ asset('iklan2.png') }}" alt="Deal 2" class="relative z-10 w-full h-full object-contain p-4 md:p-0 drop-shadow-2xl">
                
                <div class="absolute bottom-0 left-0 w-full bg-gradient-to-t from-black/90 to-transparent p-6 md:p-8 z-20 flex flex-col items-center md:items-start">
                     <span class="bg-blue-600 text-white px-3 py-1 rounded-full text-[10px] md:text-xs font-bold shadow-lg mb-2">Delivery</span>
                    <h3 class="text-xl md:text-4xl font-black text-white text-center md:text-left">Vehicle Delivery Available</h3>
                </div>
            </div>

            {{-- Slide 3 --}}
            <div class="deal-slide absolute inset-0 transition-opacity duration-1000 opacity-0" data-index="2">
                <div class="absolute inset-0">
                    <img src="{{ asset('iklan3.png') }}" class="w-full h-full object-cover blur-2xl scale-110 opacity-50">
                </div>
                <img src="{{ asset('iklan3.png') }}" alt="Deal 3" class="relative z-10 w-full h-full object-contain p-4 md:p-0 drop-shadow-2xl">
                
                <div class="absolute bottom-0 left-0 w-full bg-gradient-to-t from-black/90 to-transparent p-6 md:p-8 z-20 flex flex-col items-center md:items-start">
                     <span class="bg-green-600 text-white px-3 py-1 rounded-full text-[10px] md:text-xs font-bold shadow-lg mb-2">REWARDS</span>
                    <h3 class="text-xl md:text-4xl font-black text-white text-center md:text-left">Free 1 Hour Rental</h3>
                </div>
            </div>

            {{-- Indicators --}}
            <div class="absolute bottom-4 right-4 md:bottom-6 md:right-8 flex gap-2 md:gap-3 z-30">
                <button class="w-8 md:w-12 h-1.5 rounded-full bg-orange-500 transition-all duration-300 deal-indicator" onclick="manualSetSlide(0)"></button>
                <button class="w-2 md:w-3 h-1.5 rounded-full bg-white/30 hover:bg-white transition-all duration-300 deal-indicator" onclick="manualSetSlide(1)"></button>
                <button class="w-2 md:w-3 h-1.5 rounded-full bg-white/30 hover:bg-white transition-all duration-300 deal-indicator" onclick="manualSetSlide(2)"></button>
            </div>
        </div>
    </div>
</div>

{{-- SECTION 2: FLEET SHOWCASE --}}
<div id="fleet-showcase" class="glass-section py-16 md:py-24 border-b border-white/5 w-full">
    <div class="absolute top-0 right-0 w-[300px] md:w-[500px] h-[300px] md:h-[500px] bg-orange-600/10 blur-[120px] rounded-full pointer-events-none"></div>

    <div class="container mx-auto px-4 mb-8 md:mb-12 flex justify-between items-end relative z-10">
        <div>
            <h2 class="text-3xl md:text-5xl font-black text-white mb-2">Our Fleet</h2>
            <p class="text-gray-400 text-sm md:text-base">Browsing {{ count($vehicles) }} premium vehicles.</p>
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
        <div class="flex overflow-x-auto gap-4 md:gap-8 px-4 pb-8 md:pb-12 scroll-smooth no-scrollbar" id="carouselTrack">
            @foreach($vehicles as $vehicle)
            <div class="glass-card w-[85vw] md:w-[400px] rounded-[2rem] md:rounded-[2.5rem] relative group flex-shrink-0">
                
                <div class="h-48 md:h-64 w-full flex items-center justify-center relative mt-6 perspective-1000">
                    <img src="{{ $vehicle->image_url }}" alt="{{ $vehicle->model }}" 
                         class="w-[85%] object-contain drop-shadow-2xl transform group-hover:scale-110 group-hover:-rotate-2 transition duration-500 ease-out z-10">
                    <div class="absolute top-0 left-6 bg-black/40 backdrop-blur-md border border-white/10 text-white px-3 md:px-4 py-1.5 rounded-full text-[10px] md:text-xs font-bold z-20">
                        {{ strtoupper($vehicle->type) }}
                    </div>
                </div>
                <div class="p-6 md:p-8 relative bg-gradient-to-b from-transparent to-black/40 rounded-b-[2rem] md:rounded-b-[2.5rem]">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <p class="text-orange-500 text-xs font-bold tracking-widest uppercase mb-1">{{ $vehicle->brand }}</p>
                            <h3 class="text-2xl md:text-3xl font-bold text-white truncate max-w-[150px] md:max-w-[200px]">{{ $vehicle->model }}</h3>
                        </div>
                        <div class="text-right">
                            <p class="text-white font-bold text-xl md:text-2xl">RM {{ number_format($vehicle->priceHour, 0) }}</p>
                            <p class="text-gray-400 text-[10px] md:text-xs">/ hour</p>
                        </div>
                    </div>
                    <div class="flex gap-2 md:gap-3 text-gray-300 mb-6 md:mb-8 text-xs md:text-sm overflow-x-auto no-scrollbar">
                        <div class="flex items-center gap-2 bg-white/5 px-3 py-2 rounded-xl border border-white/5 whitespace-nowrap">
                            <i class="fas fa-gas-pump text-orange-500"></i> {{ $vehicle->fuelType }}
                        </div>
                        <div class="flex items-center gap-2 bg-white/5 px-3 py-2 rounded-xl border border-white/5 whitespace-nowrap">
                            <i class="fas fa-calendar-alt text-orange-500"></i> {{ $vehicle->year }}
                        </div>
                        <div class="flex items-center gap-2 bg-white/5 px-3 py-2 rounded-xl border border-white/5 whitespace-nowrap">
                            <i class="fas fa-palette text-orange-500"></i> {{ $vehicle->color }}
                        </div>
                    </div>
                    <a href="{{ route('book.create', ['vehicle_id' => $vehicle->VehicleID]) }}" class="block w-full py-3 md:py-4 bg-white text-black font-bold text-center rounded-2xl hover:bg-orange-600 hover:text-white transition shadow-[0_0_20px_rgba(255,255,255,0.2)]">
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

{{-- SECTION 3: STATS --}}
<div class="bg-[#111] py-16 md:py-20 border-b border-white/5 w-full">
    <div class="container mx-auto px-4">
        <div class="glass-card rounded-[2rem] py-8 md:py-12 px-6 flex flex-col md:flex-row justify-center gap-8 md:gap-16 text-center border border-white/5 bg-white/5">
            <div>
                <h3 class="text-4xl md:text-5xl font-black text-white mb-2">10+</h3>
                <p class="text-gray-400 font-medium uppercase tracking-wider text-xs md:text-sm">Affordable Vehicles</p>
            </div>
            <div class="hidden md:block w-px bg-white/10 h-20"></div>
            <div class="block md:hidden w-20 h-px bg-white/10 mx-auto"></div>
            
            <div>
                <h3 class="text-4xl md:text-5xl font-black text-white mb-2">3k+</h3>
                <p class="text-gray-400 font-medium uppercase tracking-wider text-xs md:text-sm">Happy Students</p>
            </div>
            <div class="hidden md:block w-px bg-white/10 h-20"></div>
            <div class="block md:hidden w-20 h-px bg-white/10 mx-auto"></div>

            <div>
                <h3 class="text-4xl md:text-5xl font-black text-white mb-2">24/7</h3>
                <p class="text-gray-400 font-medium uppercase tracking-wider text-xs md:text-sm">Support Team</p>
            </div>
        </div>
    </div>
</div>

{{-- SECTION 4: FEATURES --}}
<div class="glass-section py-16 md:py-24 relative w-full">
    <div class="absolute bottom-0 left-0 w-[300px] md:w-[500px] h-[300px] md:h-[500px] bg-blue-600/10 blur-[120px] rounded-full pointer-events-none"></div>

    <div class="container mx-auto px-4 relative z-10">
        <div class="text-center mb-12 md:mb-16">
            <h2 class="text-3xl md:text-4xl font-black text-white mb-4">Why Choose Hasta?</h2>
            <p class="text-gray-400 max-w-2xl mx-auto text-base md:text-lg">We provide the most reliable and student-friendly car rental service in UTM.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8">
            <div class="glass-card p-8 md:p-10 rounded-[2.5rem] group hover:-translate-y-2 transition duration-500 relative">
                <div class="w-16 h-16 md:w-20 md:h-20 bg-gradient-to-br from-orange-500 to-red-600 rounded-2xl flex items-center justify-center text-white text-2xl md:text-3xl mb-6 md:mb-8 shadow-lg shadow-orange-500/30 group-hover:scale-110 transition">
                    <i class="fas fa-wallet"></i>
                </div>
                <h3 class="text-xl md:text-2xl font-bold text-white mb-3 md:mb-4">Student Prices</h3>
                <p class="text-gray-400 text-sm md:text-base leading-relaxed group-hover:text-gray-200 transition">Affordable rates designed specifically for UTM students. No hidden fees, ever.</p>
            </div>

            <div class="glass-card p-8 md:p-10 rounded-[2.5rem] group hover:-translate-y-2 transition duration-500 relative">
                <div class="w-16 h-16 md:w-20 md:h-20 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-2xl flex items-center justify-center text-white text-2xl md:text-3xl mb-6 md:mb-8 shadow-lg shadow-blue-500/30 group-hover:scale-110 transition">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3 class="text-xl md:text-2xl font-bold text-white mb-3 md:mb-4">Fully Insured</h3>
                <p class="text-gray-400 text-sm md:text-base leading-relaxed group-hover:text-gray-200 transition">Drive with peace of mind. All our vehicles come with comprehensive insurance coverage.</p>
            </div>

            <div class="glass-card p-8 md:p-10 rounded-[2.5rem] group hover:-translate-y-2 transition duration-500 relative">
                <div class="w-16 h-16 md:w-20 md:h-20 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl flex items-center justify-center text-white text-2xl md:text-3xl mb-6 md:mb-8 shadow-lg shadow-green-500/30 group-hover:scale-110 transition">
                    <i class="fas fa-bolt"></i>
                </div>
                <h3 class="text-xl md:text-2xl font-bold text-white mb-3 md:mb-4">Instant Access</h3>
                <p class="text-gray-400 text-sm md:text-base leading-relaxed group-hover:text-gray-200 transition">Book in seconds using our digital platform. Drive the car whenever you need it.</p>
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
                track.scrollBy({ left: 320, behavior: 'smooth' }); // Adjusted for mobile card width
            }
        }

        if(track && track.childElementCount > 1) {
             autoScrollInterval = setInterval(autoScroll, 3000);

            if(nextBtn) {
                nextBtn.addEventListener('click', () => {
                    clearInterval(autoScrollInterval);
                    track.scrollBy({ left: 320, behavior: 'smooth' });
                    autoScrollInterval = setInterval(autoScroll, 4000);
                });
            }

            if(prevBtn) {
                prevBtn.addEventListener('click', () => {
                    clearInterval(autoScrollInterval);
                    track.scrollBy({ left: -320, behavior: 'smooth' });
                    autoScrollInterval = setInterval(autoScroll, 4000);
                });
            }

            track.addEventListener('mouseenter', () => clearInterval(autoScrollInterval));
            track.addEventListener('mouseleave', () => autoScrollInterval = setInterval(autoScroll, 3000));
            track.addEventListener('touchstart', () => clearInterval(autoScrollInterval));
            track.addEventListener('touchend', () => autoScrollInterval = setInterval(autoScroll, 3000));
        }

        // --- DEALS SLIDESHOW LOGIC ---
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
            slides.forEach((slide) => {
                slide.classList.remove('opacity-100');
                slide.classList.add('opacity-0');
            });
            
            indicators.forEach((ind) => {
                ind.classList.remove('w-8', 'md:w-12', 'bg-orange-500');
                ind.classList.add('w-2', 'md:w-3', 'bg-white/30');
            });

            slides[index].classList.remove('opacity-0');
            slides[index].classList.add('opacity-100');
            
            indicators[index].classList.remove('w-2', 'md:w-3', 'bg-white/30');
            indicators[index].classList.add('w-8', 'md:w-12', 'bg-orange-500');

            currentSlide = index;
        }

        function nextSlide() {
            let next = (currentSlide + 1) % slides.length;
            showSlide(next);
        }

        function startDealAutoPlay() {
            dealInterval = setInterval(nextSlide, 5000);
        }

        if(slides.length > 0) {
            startDealAutoPlay();
        }
    });
</script>
@endsection