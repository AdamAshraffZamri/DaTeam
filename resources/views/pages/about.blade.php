@extends('layouts.app')

@section('content')

{{-- STYLES --}}
<style>
    /* Glass Styles */
    .glass-section {
        background-color: #111;
        position: relative;
        overflow: hidden;
    }
    .glass-card {
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.3);
    }

    /* Animations */
    @keyframes fade-in-up {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in-up { animation: fade-in-up 1s ease-out forwards; }
    .delay-100 { animation-delay: 100ms; }
    .delay-300 { animation-delay: 300ms; }
</style>

{{-- SECTION 1: HERO --}}
<div class="relative h-screen min-h-[600px] flex flex-col justify-center bg-gray-900 overflow-hidden">
    
    {{-- Background --}}
    <div class="absolute top-0 left-0 w-full h-full bg-white/50 overflow-hidden opacity-10">
        <div class="absolute -top-[20%] -left-[10%] w-[50%] h-[50%] bg-orange-600 rounded-full blur-[100px]"></div>
        <div class="absolute top-[40%] -right-[10%] w-[40%] h-[60%] bg-red-600 rounded-full blur-[100px]"></div>
    </div>

    {{-- Main Container --}}
    <div class="relative z-10 container mx-auto px-6 md:px-12 flex flex-col h-full justify-center items-center gap-10 pt-10 pb-20">
        
        {{-- Navigation Pill --}}
        <div class="flex justify-center animate-fade-in-up">
            <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-full p-1.5 flex flex-wrap justify-center md:flex-nowrap items-center shadow-2xl">
                <a href="{{ route('book.create') }}" class="px-6 md:px-8 py-2.5 text-white/90 font-bold hover:bg-white/10 rounded-full transition text-sm md:text-base">Book a Car</a>
                <a href="{{ route('book.index') }}" class="px-6 md:px-8 py-2.5 text-white/90 font-bold hover:bg-white/10 rounded-full transition text-sm md:text-base">My Bookings</a>
                <a href="{{ route('loyalty.index') }}" class="px-6 md:px-8 py-2.5 text-white/90 font-bold hover:bg-white/10 rounded-full transition text-sm md:text-base">Loyalty</a>
                <a href="{{ route('finance.index') }}" class="px-6 md:px-8 py-2.5 text-white/90 font-bold hover:bg-white/10 rounded-full transition text-sm md:text-base">Payments</a>
            </div>
        </div>

        {{-- Hero Text --}}
        <div class="text-center max-w-4xl mx-auto animate-fade-in-up delay-100">
            <h1 class="text-5xl md:text-8xl font-black text-white mb-8 tracking-tight drop-shadow-2xl">
                Moving <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-400 to-red-500">UTM Forward.</span>
            </h1>
            <p class="text-xl md:text-2xl text-gray-300 max-w-3xl mx-auto leading-relaxed font-medium drop-shadow-md">
                HASTA is more than just a car rental service. We are a student-driven initiative making campus mobility safe and accessible.
            </p>
        </div>
    </div>

    {{-- >>> SCROLL INDICATOR (Moved to bottom-28) <<< --}}
    <a href="#our-story" class="absolute bottom-28 left-1/2 transform -translate-x-1/2 z-30 flex flex-col items-center gap-2 animate-fade-in-up delay-300 group opacity-70 hover:opacity-100 transition">
        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.3em] group-hover:text-white transition">Scroll</span>
        
        {{-- Bouncing Chevron --}}
        <div class="animate-bounce p-2 bg-white/5 rounded-full border border-white/10 backdrop-blur-sm">
            <i class="fas fa-chevron-down text-white text-xl"></i>
        </div>
    </a>

</div>

{{-- SECTION 2: OUR STORY --}}
<div id="our-story" class="py-24 bg-white scroll-mt-10">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row items-center gap-16">
            {{-- Visual --}}
            <div class="w-full md:w-1/2 relative">
                <div class="absolute inset-0 bg-orange-500 rounded-[2rem] transform rotate-3 opacity-20"></div>
                <img src="{{ asset('hastabg1.png') }}" alt="Our Journey" class="relative rounded-[2rem] shadow-2xl object-cover h-[450px] w-full transform -rotate-2 transition hover:rotate-0 duration-500">
            </div>

            {{-- Text Content --}}
            <div class="w-full md:w-1/2">
                <h4 class="text-orange-600 font-bold uppercase tracking-widest text-sm mb-2">Our Story</h4>
                <h2 class="text-4xl md:text-5xl font-black text-gray-900 mb-6">Born from a Need.<br>Built for Students.</h2>
                <div class="space-y-6 text-gray-600 leading-relaxed text-lg">
                    <p>It started with a simple observation: transportation around Skudai can be a hassle. Late buses, expensive e-hailing rides, and the difficulty of maintaining a personal car.</p>
                    <p><strong>HASTA</strong> was founded to bridge that gap. We envisioned a platform where students could rent high-quality vehicles at student-friendly prices.</p>
                    
                    <div class="flex gap-8 pt-4 border-t border-gray-100 mt-6">
                        <div>
                            <span class="block text-4xl font-black text-gray-900">2024</span>
                            <span class="text-sm text-gray-500 uppercase font-bold tracking-wider">Founded</span>
                        </div>
                        <div>
                            <span class="block text-4xl font-black text-gray-900">100%</span>
                            <span class="text-sm text-gray-500 uppercase font-bold tracking-wider">Student Led</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- SECTION 3: OUR VALUES (Glass Section) --}}
<div class="glass-section py-24 relative">
    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-orange-600/10 blur-[120px] rounded-full pointer-events-none"></div>

    <div class="container mx-auto px-6 relative z-10">
        <div class="text-center mb-16">
            <h2 class="text-4xl md:text-5xl font-black text-white mb-4">What Drives Us</h2>
            <p class="text-gray-400 mt-2 text-lg">The core principles that guide every booking.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            {{-- Value 1 --}}
            <div class="glass-card p-10 rounded-[2.5rem] group hover:-translate-y-2 transition duration-500 relative">
                <div class="w-20 h-20 bg-gradient-to-br from-orange-500 to-red-600 rounded-2xl flex items-center justify-center text-white text-3xl mb-8 shadow-lg shadow-orange-500/30 group-hover:scale-110 transition">
                    <i class="fas fa-heart"></i>
                </div>
                <h3 class="text-2xl font-bold text-white mb-4">Community First</h3>
                <p class="text-gray-400 leading-relaxed group-hover:text-gray-200 transition">We aren't just a business; we are part of the UTM ecosystem. We prioritize the safety and well-being of our fellow students above profits.</p>
            </div>
            {{-- Value 2 --}}
            <div class="glass-card p-10 rounded-[2.5rem] group hover:-translate-y-2 transition duration-500 relative">
                <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-2xl flex items-center justify-center text-white text-3xl mb-8 shadow-lg shadow-blue-500/30 group-hover:scale-110 transition">
                    <i class="fas fa-hand-holding-dollar"></i>
                </div>
                <h3 class="text-2xl font-bold text-white mb-4">Transparency</h3>
                <p class="text-gray-400 leading-relaxed group-hover:text-gray-200 transition">No hidden charges. No confusing contracts. What you see on the screen is exactly what you pay. We believe trust is earned through honesty.</p>
            </div>
            {{-- Value 3 --}}
            <div class="glass-card p-10 rounded-[2.5rem] group hover:-translate-y-2 transition duration-500 relative">
                <div class="w-20 h-20 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl flex items-center justify-center text-white text-3xl mb-8 shadow-lg shadow-green-500/30 group-hover:scale-110 transition">
                    <i class="fas fa-leaf"></i>
                </div>
                <h3 class="text-2xl font-bold text-white mb-4">Sustainability</h3>
                <p class="text-gray-400 leading-relaxed group-hover:text-gray-200 transition">By sharing vehicles, we reduce the total number of cars needed on campus, contributing to a greener and less congested university environment.</p>
            </div>
        </div>
    </div>
</div>

{{-- SECTION 4: CTA --}}
<div class="py-24 bg-gray-900 relative overflow-hidden text-center border-t border-gray-800">
    <div class="absolute inset-0 bg-gradient-to-r from-orange-900/20 to-red-900/20 opacity-20"></div>
    <div class="container mx-auto px-4 relative z-10">
        <h2 class="text-4xl md:text-5xl font-black text-white mb-6">Ready to start?</h2>
        <p class="text-gray-300 mb-10 text-xl max-w-2xl mx-auto">Join hundreds of students who trust HASTA for their daily commute.</p>
        <a href="{{ route('book.create') }}" class="inline-block bg-white text-orange-600 font-bold py-4 px-12 rounded-full shadow-2xl hover:bg-orange-50 hover:scale-105 transition transform text-lg">
            Book a Car Now
        </a>
    </div>
</div>

@endsection