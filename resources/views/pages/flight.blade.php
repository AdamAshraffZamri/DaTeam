@extends('layouts.app')

@section('content')
<div class="pt-10 pb-20 bg-[#0a0a0a] min-h-screen">
    <div class="container mx-auto px-6">
        <div class="glass-card p-12 md:p-20 rounded-[4rem] border-white/5 relative overflow-hidden">
            {{-- Background Icon --}}
            <i class="fas fa-plane absolute -top-10 -right-10 text-[300px] text-white/5 -rotate-45"></i>
            
            <div class="relative z-10 max-w-2xl">
                <div class="inline-flex items-center gap-3 px-5 py-2 bg-white/5 rounded-full border border-white/10 text-orange-500 text-xs font-black uppercase tracking-widest mb-8">
                    <span class="w-2 h-2 bg-orange-500 rounded-full animate-pulse"></span>
                    Senai International Airport (JHB)
                </div>
                
                <h1 class="text-6xl md:text-8xl font-black text-white mb-8 tracking-tighter leading-[0.9]">
                    Fly High, <br><span class="text-orange-500">Drive Easy.</span>
                </h1>
                
                <p class="text-gray-400 text-lg mb-12 leading-relaxed">
                    Need a car waiting for you at the gate? Or a seamless ride to catch your flight? We specialize in Senai Airport transfers and vehicle drop-offs.
                </p>
                
                <div class="flex flex-wrap gap-4">
                    <button class="bg-orange-500 hover:bg-orange-600 text-white px-10 py-5 rounded-[2rem] font-bold text-lg transition-all shadow-xl shadow-orange-500/20 active:scale-95">
                        Book Airport Transfer
                    </button>
                    <button class="bg-white/5 hover:bg-white/10 text-white border border-white/10 px-10 py-5 rounded-[2rem] font-bold text-lg transition-all">
                        Check Flight Times
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection