@extends('layouts.app')

@section('content')
{{-- SECTION 1: HERO --}}
{{-- Updated to match Home Page exact structure (h-screen, justify-center) --}}
<div class="relative h-screen min-h-[600px] flex flex-col justify-center bg-gray-900 overflow-hidden">
    
    {{-- Abstract Background Shapes --}}
    <div class="absolute top-0 left-0 w-full h-full bg-white/50 overflow-hidden opacity-20">
        <div class="absolute -top-[20%] -left-[10%] w-[50%] h-[50%] bg-orange-600 rounded-full blur-[100px]"></div>
        <div class="absolute top-[40%] -right-[10%] w-[40%] h-[60%] bg-red-600 rounded-full blur-[100px]"></div>
    </div>

    {{-- Content Container: Exact match with Home for alignment --}}
    <div class="relative z-10 container mx-auto px-6 md:px-12 flex flex-col h-full justify-center items-center gap-10 pt-10 pb-20">
        
        {{-- Navigation Pill (Identical to Home) --}}
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

        {{-- Hero Text --}}
        <div class="text-center max-w-4xl mx-auto animate-fade-in-up delay-100">
            <h1 class="text-5xl md:text-8xl font-black text-white mb-8 tracking-tight drop-shadow-2xl">
                Moving <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-400 to-red-500">UTM Forward.</span>
            </h1>
            <p class="text-xl md:text-2xl text-gray-300 max-w-3xl mx-auto leading-relaxed font-medium drop-shadow-md">
                HASTA is more than just a car rental service. We are a student-driven initiative dedicated to making campus mobility safe, affordable, and accessible for everyone.
            </p>
        </div>
    </div>
</div>

{{-- SECTION 2: OUR STORY & MISSION --}}
<div class="py-20 bg-white">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row items-center gap-16">
            {{-- Image / Visual --}}
            <div class="w-full md:w-1/2 relative">
                <div class="absolute inset-0 bg-orange-500 rounded-3xl transform rotate-3 opacity-20"></div>
                <img src="{{ asset('hastabg1.png') }}" alt="Our Journey" class="relative rounded-3xl shadow-2xl object-cover h-[400px] w-full transform -rotate-2 transition hover:rotate-0 duration-500">
            </div>

            {{-- Text Content --}}
            <div class="w-full md:w-1/2">
                <h4 class="text-orange-600 font-bold uppercase tracking-widest text-sm mb-2">Our Story</h4>
                <h2 class="text-4xl font-black text-gray-900 mb-6">Born from a Need.<br>Built for Students.</h2>
                <div class="space-y-6 text-gray-600 leading-relaxed">
                    <p>
                        It started with a simple observation: transportation around Skudai can be a hassle. Late buses, expensive e-hailing rides, and the difficulty of maintaining a personal car on campus.
                    </p>
                    <p>
                        <strong>HASTA</strong> was founded to bridge that gap. We envisioned a platform where students could rent high-quality vehicles at student-friendly prices, without the bureaucratic headaches of traditional rental agencies.
                    </p>
                    
                    <div class="flex gap-8 pt-4">
                        <div>
                            <span class="block text-3xl font-black text-gray-900">2024</span>
                            <span class="text-sm text-gray-500 uppercase">Founded</span>
                        </div>
                        <div>
                            <span class="block text-3xl font-black text-gray-900">100%</span>
                            <span class="text-sm text-gray-500 uppercase">Student Led</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- SECTION 3: OUR VALUES --}}
<div class="py-20 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl font-black text-gray-900">What Drives Us</h2>
            <p class="text-gray-500 mt-2">The core principles that guide every booking.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            {{-- Value 1 --}}
            <div class="bg-white p-8 rounded-2xl shadow-lg border-b-4 border-orange-500 hover:-translate-y-1 transition duration-300">
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center text-orange-600 text-xl mb-6">
                    <i class="fas fa-heart"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Community First</h3>
                <p class="text-gray-500">We aren't just a business; we are part of the UTM ecosystem. We prioritize the safety and well-being of our fellow students above profits.</p>
            </div>

            {{-- Value 2 --}}
            <div class="bg-white p-8 rounded-2xl shadow-lg border-b-4 border-blue-500 hover:-translate-y-1 transition duration-300">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600 text-xl mb-6">
                    <i class="fas fa-hand-holding-dollar"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Transparency</h3>
                <p class="text-gray-500">No hidden charges. No confusing contracts. What you see on the screen is exactly what you pay. We believe trust is earned through honesty.</p>
            </div>

            {{-- Value 3 --}}
            <div class="bg-white p-8 rounded-2xl shadow-lg border-b-4 border-green-500 hover:-translate-y-1 transition duration-300">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center text-green-600 text-xl mb-6">
                    <i class="fas fa-leaf"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Sustainability</h3>
                <p class="text-gray-500">By sharing vehicles, we reduce the total number of cars needed on campus, contributing to a greener and less congested university environment.</p>
            </div>
        </div>
    </div>
</div>

{{-- SECTION 5: CTA --}}
<div class="py-20 bg-gray-900 relative overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-r from-orange-600 to-red-600 opacity-20"></div>
    <div class="container mx-auto px-4 relative z-10 text-center">
        <h2 class="text-4xl font-black text-white mb-6">Ready to start your journey?</h2>
        <p class="text-gray-300 mb-8 text-lg">Join hundreds of students who trust HASTA for their daily commute.</p>
        <a href="{{ route('book.create') }}" class="inline-block bg-white text-orange-600 font-bold py-4 px-10 rounded-full shadow-2xl hover:bg-orange-50 hover:scale-105 transition transform">
            Book a Car Now
        </a>
    </div>
</div>

<style>
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
@endsection