@extends('layouts.app')

@section('content')
<div class="pt-20 pb-20 bg-[#0a0a0a] min-h-screen relative overflow-hidden">
    {{-- Decorative background glow --}}
    <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-orange-500/10 blur-[120px] rounded-full -mr-64 -mt-64"></div>
    
    <div class="container mx-auto px-6 relative z-10">
        <div class="max-w-4xl mb-16 animate-fade-in-up">
            <h1 class="text-6xl md:text-8xl font-black text-white mb-6 tracking-tighter uppercase">
                Drive to <span class="text-orange-500">Singapore</span>
            </h1>
            <p class="text-gray-400 text-xl leading-relaxed">
                Seamless cross-border rentals. Our fleet is fully permitted for entry into Singapore via the Causeway or Second Link.
            </p>
        </div>

        {{-- Requirement Banner --}}
        <div class="glass-card p-8 rounded-[2.5rem] mb-12 border-orange-500/20 flex flex-col md:flex-row items-center gap-8 animate-fade-in-up">
            <div class="w-20 h-20 bg-orange-500 rounded-3xl flex items-center justify-center shrink-0 shadow-lg shadow-orange-500/20">
                <i class="fas fa-id-card text-white text-3xl"></i>
            </div>
            <div>
                <h3 class="text-2xl font-bold text-white mb-2">Autopass Requirement</h3>
                <p class="text-gray-400">All drivers must possess a valid Autopass Card for Singapore entry. You will be prompted to provide your card number during the payment process for verification with LTA.</p>
            </div>
            <div class="shrink-0">
                <div class="px-6 py-3 bg-white/5 rounded-2xl border border-white/10 text-orange-500 font-bold uppercase text-xs tracking-widest">
                    Mandatory Step
                </div>
            </div>
        </div>

        {{-- Feature Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="glass-card p-10 rounded-[3rem] hover:bg-white/10 transition-all duration-500 group">
                <i class="fas fa-gas-pump text-4xl text-orange-500 mb-6 group-hover:scale-110 transition-transform"></i>
                <h4 class="text-xl font-bold text-white mb-4">3/4 Fuel Rule</h4>
                <p class="text-gray-500 text-sm leading-relaxed">We ensure all Singapore-bound rentals meet the mandatory 3/4 tank requirement before your journey starts.</p>
            </div>
            <div class="glass-card p-10 rounded-[3rem] hover:bg-white/10 transition-all duration-500 group">
                <i class="fas fa-shield-check text-4xl text-orange-500 mb-6 group-hover:scale-110 transition-transform"></i>
                <h4 class="text-xl font-bold text-white mb-4">S'pore Insurance</h4>
                <p class="text-gray-500 text-sm leading-relaxed">Every cross-border rental includes an extended insurance premium covering Third Party Liability in Singapore.</p>
            </div>
            <div class="glass-card p-10 rounded-[3rem] hover:bg-white/10 transition-all duration-500 group">
                <i class="fas fa-clock text-4xl text-orange-500 mb-6 group-hover:scale-110 transition-transform"></i>
                <h4 class="text-xl font-bold text-white mb-4">24/7 Support</h4>
                <p class="text-gray-500 text-sm leading-relaxed">Our dedicated roadside assistance is available on both sides of the border for your peace of mind.</p>
            </div>
        </div>
    </div>
</div>
@endsection