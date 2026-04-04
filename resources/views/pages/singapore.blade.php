@extends('layouts.app')

@section('content')
{{-- BACKGROUND IMAGE --}}
{{-- Background Image --}}
    <div class="absolute inset-0 w-full h-full">
        <div class="absolute inset-0 bg-gradient-to-r from-black/60 via-black/35 to-black/65 z-10"></div>
        <img src="{{ asset('mbs-merlion-day-1500x930.jpg') }}" alt="Background" class="w-full h-full object-cover">
    </div>

<div class="pt-32 pb-20 min-h-screen relative z-10">
    {{-- Decorative background glow (kept for aesthetic consistency) --}}
    <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-orange-500/10 blur-[120px] rounded-full -mr-64 -mt-64"></div>
    
    <div class="container mx-auto px-6 relative z-10">
        <div class="max-w-4xl mb-16 animate-fade-in-up">
            <h1 class="text-6xl md:text-8xl font-black text-white mb-6 tracking-tighter uppercase drop-shadow-2xl">
                Drive to <span class="text-orange-500">Singapore</span>
            </h1>
            <p class="text-gray-200 text-xl leading-relaxed font-medium">
                Seamless cross-border rentals. Our fleet is fully permitted for entry into Singapore via the Causeway or Second Link.
            </p>
        </div>

        {{-- NEW: Autopass Payment Section --}}
        <div class="glass-card p-8 rounded-[2.5rem] mb-12 border-orange-500/30 flex flex-col md:flex-row items-center gap-8 animate-fade-in-up bg-black/40">
            <div class="w-20 h-20 bg-orange-500 rounded-3xl flex items-center justify-center shrink-0 shadow-lg shadow-orange-500/20">
                <i class="fas fa-credit-card text-white text-3xl"></i>
            </div>
            <div class="flex-grow">
                <h3 class="text-2xl font-bold text-white mb-2">Autopass Payment</h3>
                <p class="text-gray-300">
                    Already booked your car? Please settle the **Singapore Entry Supplement** separately here. 
                    This fee covers VEP processing and mandatory cross-border documentation.
                </p>
            </div>
            <div class="shrink-0">
                <a href="{{ route('pages.autopass') }}" class="inline-block px-8 py-4 bg-white text-black hover:bg-orange-500 hover:text-white rounded-2xl font-black transition-all shadow-xl active:scale-95">
                    PAY NOW
                </a>
            </div>
        </div>

        {{-- Feature Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="glass-card p-10 rounded-[3rem] hover:bg-white/10 transition-all duration-500 group bg-black/30">
                <i class="fas fa-gas-pump text-4xl text-orange-500 mb-6 group-hover:scale-110 transition-transform"></i>
                <h4 class="text-xl font-bold text-white mb-4">3/4 Fuel Rule</h4>
                <p class="text-gray-400 text-sm leading-relaxed">We ensure all Singapore-bound rentals meet the mandatory 3/4 tank requirement before your journey starts.</p>
            </div>
            <div class="glass-card p-10 rounded-[3rem] hover:bg-white/10 transition-all duration-500 group bg-black/30">
                <i class="fas fa-shield-check text-4xl text-orange-500 mb-6 group-hover:scale-110 transition-transform"></i>
                <h4 class="text-xl font-bold text-white mb-4">S'pore Insurance</h4>
                <p class="text-gray-400 text-sm leading-relaxed">Every cross-border rental includes an extended insurance premium covering Third Party Liability in Singapore.</p>
            </div>
            <div class="glass-card p-10 rounded-[3rem] hover:bg-white/10 transition-all duration-500 group bg-black/30">
                <i class="fas fa-clock text-4xl text-orange-500 mb-6 group-hover:scale-110 transition-transform"></i>
                <h4 class="text-xl font-bold text-white mb-4">24/7 Support</h4>
                <p class="text-gray-400 text-sm leading-relaxed">Our dedicated roadside assistance is available on both sides of the border for your peace of mind.</p>
            </div>
        </div>
    </div>
</div>
@endsection