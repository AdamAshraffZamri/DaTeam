@extends('layouts.app')

@section('content')

{{-- CUSTOM STYLES --}}
<style>
    /* Hide scrollbar */
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    
    /* Animation Utilities */
    @keyframes fade-in-up {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in-up { animation: fade-in-up 0.8s ease-out forwards; }
    .delay-100 { animation-delay: 100ms; }
    .delay-200 { animation-delay: 200ms; }

    /* Accordion Transition */
    .faq-answer {
        transition: max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.4s ease, padding 0.4s ease;
    }
    .faq-icon {
        transition: transform 0.3s ease;
    }
</style>

{{-- 1. BACKGROUND (Your Original Style) --}}
<div class="fixed inset-0 z-0 bg-gray-900">
    {{-- Abstract Background Shapes --}}
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden opacity-20 pointer-events-none">
        <div class="absolute -top-[20%] -left-[10%] w-[70%] h-[50%] bg-orange-600 rounded-full blur-[100px]"></div>
        <div class="absolute top-[40%] -right-[10%] w-[60%] h-[60%] bg-red-600 rounded-full blur-[100px]"></div>
    </div>
</div>

{{-- 2. MAIN CONTENT --}}
<div class="relative z-10 w-full min-h-[calc(100vh-64px)] flex flex-col items-center px-4 pt-24 md:pt-32 pb-20">

    {{-- NAVIGATION PILL (Mobile Optimized) --}}
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
                       class="px-4 sm:px-6 py-2 sm:py-2.5 rounded-full font-bold text-sm sm:text-[15px] transition-all duration-300 whitespace-nowrap active:scale-95
                       {{ (request()->routeIs('book.create') || request()->routeIs('book.search') || request()->routeIs('book.show') || request()->routeIs('book.payment') || request()->routeIs('book.payment.submit')) 
                           ? 'nav-link-active' 
                           : 'text-white hover:bg-white/10' }}">
                        Book Now
                    </a>
                    
                    {{-- My Bookings --}}
                    <a href="{{ route('book.index') }}" 
                       class="px-4 sm:px-6 py-2 sm:py-2.5 rounded-full font-bold text-sm sm:text-[15px] transition-all duration-300 whitespace-nowrap active:scale-95
                       {{ (request()->routeIs('book.index') || request()->routeIs('book.cancel')) 
                           ? 'nav-link-active' 
                           : 'text-white hover:bg-white/10' }}">
                        My Bookings
                    </a>
                    
                    {{-- Loyalty --}}
                    <a href="{{ route('loyalty.index') }}" 
                       class="px-4 sm:px-6 py-2 sm:py-2.5 rounded-full font-bold text-sm sm:text-[15px] transition-all duration-300 whitespace-nowrap active:scale-95
                       {{ (request()->routeIs('loyalty.index') || request()->routeIs('loyalty.redeem') || request()->routeIs('voucher.apply') || request()->routeIs('voucher.available')) 
                           ? 'nav-link-active' 
                           : 'text-white hover:bg-white/10' }}">
                        Loyalty
                    </a>
                    
                    {{-- Payments --}}
                    <a href="{{ route('finance.index') }}" 
                       class="px-4 sm:px-6 py-2 sm:py-2.5 rounded-full font-bold text-sm sm:text-[15px] transition-all duration-300 whitespace-nowrap active:scale-95
                       {{ (request()->routeIs('finance.index') || request()->routeIs('finance.claim') || request()->routeIs('finance.pay') || request()->routeIs('finance.submit_balance') || request()->routeIs('finance.pay_fine') || request()->routeIs('finance.submit_fine')) 
                           ? 'nav-link-active' 
                           : 'text-white hover:bg-white/10' }}">
                        Payments
                    </a>
                </div>
            </div>
        </div>

    {{-- HEADER SECTION --}}
    <div class="text-center mb-12 max-w-3xl animate-fade-in-up delay-100">
        {{-- Responsive Text Size: text-4xl on mobile, text-6xl on desktop --}}
        <h1 class="text-4xl md:text-6xl lg:text-7xl font-extrabold text-white leading-tight mb-4 drop-shadow-xl">
            FAQs
        </h1>
        <div class="w-20 md:w-28 h-1 bg-gradient-to-r from-orange-500 to-pink-500 rounded-full mx-auto mb-6"></div>
        <p class="text-gray-300 text-sm md:text-base leading-relaxed max-w-xl mx-auto">
            Answers to common questions about booking, payments, pickup, and returns. If you still need help, use the help button below.
        </p>
    </div>

    {{-- FAQ ACCORDION --}}
    <div class="w-full max-w-3xl space-y-4 animate-fade-in-up delay-200">
        @php
            $faqs = [
                ['q' => 'How to book a car for rental?', 'a' => '<p>These are following steps you can follow:</p><ol class="list-decimal list-inside mt-3 ml-2 text-gray-300 space-y-1"><li>Complete your information and wait for approval</li><li>Navigate to "BOOK" tab</li><li>Choose your car rental details</li><li>Choose your car and make a payment</li></ol>'],
                ['q' => 'When will my deposit be returned?', 'a' => '<p>Your deposit will be returned within 3-5 business days after vehicle return and inspection. Deductions for damages will be applied if necessary.</p>'],
                ['q' => 'Can I rent with a P license?', 'a' => '<p>Yes, P license holders may rent. Ensure the license is valid and you meet minimum experience and insurance requirements.</p>'],
                ['q' => 'Can I pickup the car outside of UTM?', 'a' => '<p>Pickup is primarily at UTM Student Mall. Alternative pickup locations can be arranged for an extra fee.</p>'],
                ['q' => 'Can I pickup the car at night?', 'a' => '<p>Night pickups are possible with prior arrangement; additional charges may apply.</p><p class="mt-2">For pickups after 6:00 PM, please contact us at least 24 hours in advance.</p>']
            ];
        @endphp

        @foreach($faqs as $i => $item)
            {{-- Your Original Style: Gradient Background --}}
            <div class="bg-gradient-to-br from-black/30 to-black/50 border border-white/10 rounded-2xl overflow-hidden shadow-lg transition-all duration-300 hover:border-orange-500/30">
                
                {{-- Question Button --}}
                <button class="w-full flex items-center justify-between px-5 py-4 md:px-6 md:py-5 text-left focus:outline-none group" onclick="toggleFaq({{ $i }})">
                    <span class="text-white font-semibold text-base md:text-lg group-hover:text-orange-400 transition-colors pr-4">
                        {{ $item['q'] }}
                    </span>
                    <i id="icon-{{ $i }}" class="fas fa-chevron-down text-orange-400 faq-icon"></i>
                </button>

                {{-- Answer Area --}}
                <div id="answer-{{ $i }}" class="faq-answer max-h-0 opacity-0 overflow-hidden">
                    <div class="px-5 pb-5 md:px-6 md:pb-6 text-gray-300 text-sm md:text-base leading-relaxed border-t border-white/5 pt-3">
                        {!! $item['a'] !!}
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- CONTACT CTA --}}
    <div class="mt-12 md:mt-16 text-center animate-fade-in-up delay-200">
        <p class="text-gray-400 text-sm mb-4">Still have questions?</p>
        <a href="{{ route('pages.contact') }}" class="inline-flex items-center gap-2 bg-white text-black font-bold px-8 py-3 rounded-full hover:bg-orange-500 hover:text-white transition-all shadow-lg hover:scale-105 active:scale-95">
            <i class="fas fa-envelope"></i> Contact Support
        </a>
    </div>

</div>

{{-- JavaScript for Accordion --}}
<script>
    function toggleFaq(id) {
        const answer = document.getElementById(`answer-${id}`);
        const icon = document.getElementById(`icon-${id}`);
        
        // Check if currently open
        const isOpen = !answer.classList.contains('max-h-0');
        
        // 1. Close ALL FAQs first (Accordion behavior)
        document.querySelectorAll('.faq-answer').forEach((item, index) => {
            // Reset styles for all items
            item.style.maxHeight = null;
            item.classList.add('max-h-0', 'opacity-0');
            
            // Reset icon
            const otherIcon = document.getElementById(`icon-${index}`);
            if(otherIcon) otherIcon.style.transform = 'rotate(0deg)';
            
            // Reset container style
            item.parentElement.classList.remove('bg-white/10');
        });
        
        // 2. If it wasn't open, open it now
        if (!isOpen) {
            answer.classList.remove('max-h-0', 'opacity-0');
            answer.style.maxHeight = answer.scrollHeight + "px";
            icon.style.transform = 'rotate(180deg)';
            
            // Optional: Highlight active container
            answer.parentElement.classList.add('bg-white/10');
        }
    }
</script>

@endsection