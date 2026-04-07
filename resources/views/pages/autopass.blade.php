@extends('layouts.app')

@section('content')
<style>
    /* 1. HIDE GLOBAL FLOATING ELEMENTS (AI BOT / HELP) */
    #chatbot-button, #help-button, .floating-chat, [class*="ai-bot"], [class*="instruction"] {
        display: none !important;
    }

    /* 2. DISABLE ALL ANIMATIONS FOR STABILITY */
    * {
        animation: none !important;
        transition: background-color 0.2s ease, border-color 0.2s ease !important;
    }

    /* 3. STABLE FIXED BACKGROUND (Mirroring payment.blade.php logic) */
    .fixed-bg {
        position: fixed;
        inset: 0;
        z-index: 0;
    }
    .bg-overlay {
        position: absolute;
        inset: 0;
        background-image: url('{{ asset("mbs-merlion-day-1500x930.jpg") }}');
        background-size: cover;
        background-position: center;
    }
    .dark-gradient {
        position: absolute;
        inset: 0;
        /* Same gradient as payment.blade.php */
        background: linear-gradient(to right, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.45) 50%, rgba(0,0,0,0.8) 100%);
    }
</style>

{{-- Background Layer --}}
<div class="fixed-bg">
    <div class="bg-overlay"></div>
    <div class="dark-gradient"></div>
</div>

{{-- Main Content --}}
<div class="relative z-10 pb-20">
    <div class="container mx-auto px-4 py-12 max-w-4xl">
        
        {{-- HEADER SECTION (Mirrored from payment.blade.php) --}}
        <a href="{{ route('pages.singapore') }}" class="inline-flex items-center text-white/70 hover:text-white mb-6 transition">
            <i class="fas fa-arrow-left mr-2"></i> Back to Singapore Guide
        </a>

        <div class="mb-10 text-white">
            <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight drop-shadow-lg uppercase">
                Autopass <span class="text-orange-500">Payment</span>
            </h1>
            <p class="text-gray-400 mt-1 text-sm font-medium">Secure entry supplement for Singapore</p>
        </div>

        {{-- FORM CARD (Themed like payment.blade.php) --}}
        <div class="bg-[#111]/90 border border-white/10 rounded-[2.5rem] overflow-hidden shadow-2xl backdrop-blur-none">
            
            {{-- Action Banner --}}
            <div class="bg-orange-500 px-6 py-2">
                <p class="text-white text-[10px] font-black uppercase tracking-widest text-center">
                    Cross-Border Processing Fee: RM 50.00
                </p>
            </div>

            <form action="{{ route('autopass.submit') }}" method="POST" enctype="multipart/form-data" class="p-8 md:p-12 space-y-8">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    {{-- Booking ID --}}
                    <div class="space-y-2">
                        <label class="block text-gray-400 text-[10px] font-black uppercase tracking-widest ml-1">Booking Reference</label>
                        <input type="text" name="booking_id" required placeholder="e.g. BOK-12345"
                               class="w-full bg-white/5 border border-white/10 rounded-2xl p-4 text-white focus:border-orange-500 focus:outline-none">
                    </div>

                    {{-- Autopass Number --}}
                    <div class="space-y-2">
                        <label class="block text-gray-400 text-[10px] font-black uppercase tracking-widest ml-1">Autopass Card Number</label>
                        <input type="text" name="autopass_number" required placeholder="10 Digits"
                               class="w-full bg-white/5 border border-white/10 rounded-2xl p-4 text-white focus:border-orange-500 focus:outline-none">
                    </div>
                </div>

                {{-- Bank Details (Themed Box) --}}
                <div class="bg-white/5 rounded-2xl p-6 border border-white/5 flex flex-col md:flex-row justify-between items-center gap-4">
                    <div>
                        <p class="text-[10px] text-orange-500 font-black uppercase tracking-widest mb-1">Payment Instructions</p>
                        <p class="text-white font-mono text-xl">Maybank: 5512 3456 7890</p>
                        <p class="text-gray-500 text-xs font-bold uppercase">Hasta Travel & Tours Sdn Bhd</p>
                    </div>
                    <div class="text-right">
                        <span class="block text-gray-500 text-[10px] font-black uppercase tracking-widest mb-1 text-right">Amount Due</span>
                        <span class="text-3xl font-black text-white">RM 50</span>
                    </div>
                </div>

                {{-- Upload Proof (Mirroring upload style) --}}
                <div class="space-y-2">
                    <label class="block text-gray-400 text-[10px] font-black uppercase tracking-widest ml-1">Upload Receipt</label>
                    <div class="relative border-2 border-dashed border-white/10 rounded-2xl p-8 bg-white/5 group transition-colors hover:border-orange-500/50">
                        <input type="file" name="payment_proof" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20">
                        <div class="text-center">
                            <i class="fas fa-cloud-upload-alt text-3xl text-gray-600 mb-2"></i>
                            <p class="text-gray-400 text-xs font-bold uppercase tracking-tighter">Attach Bank Transaction Proof</p>
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 text-white font-black py-5 rounded-2xl text-xl shadow-xl shadow-orange-500/20 active:scale-[0.98]">
                    CONFIRM AUTOPASS PAYMENT
                </button>
            </form>
        </div>

        <div class="mt-8 text-center">
            <p class="text-gray-500 text-xs">
                Need help? Contact our cross-border support team at <span class="text-orange-500 font-bold">+60 7-123 4567</span>
            </p>
        </div>
    </div>
</div>
@endsection