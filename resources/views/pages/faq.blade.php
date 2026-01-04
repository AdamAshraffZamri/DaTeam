@extends('layouts.app')

@section('content')
{{-- 
    FAQ PAGE - Dark Theme with Accordion
    Matching the design concept with dark background and orange accents
--}}
<div class="w-screen relative left-[calc(-50vw+50%)] -mt-10 pt-32 min-h-screen bg-gray-900 overflow-hidden z-10">
    
    {{-- BACKGROUND EFFECTS --}}
    <div class="absolute inset-0 z-0">
        {{-- Abstract Background Shapes --}}
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden opacity-20">
            <div class="absolute -top-[20%] -left-[10%] w-[50%] h-[50%] bg-orange-600 rounded-full blur-[100px]"></div>
            <div class="absolute top-[40%] -right-[10%] w-[40%] h-[60%] bg-red-600 rounded-full blur-[100px]"></div>
        </div>
    </div>

    
    {{-- Navigation Pill --}}
        <div class="flex justify-center animate-fade-in-up">
            <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-full p-1.5 flex flex-wrap justify-center md:flex-nowrap items-center shadow-2xl">
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

    {{-- MAIN CONTENT --}}
    <div class="container mx-auto px-4 mb-20 relative z-10 max-w-7xl">
        <div class="flex flex-col lg:flex-row gap-12 items-start">

            {{-- FAQ Accordion Section --}}
            <div class="w-full">
                <div class="mb-10">
                    <h1 class="text-5xl md:text-6xl lg:text-7xl font-extrabold text-white leading-tight">FAQs</h1>
                    <div class="w-28 h-1 bg-gradient-to-r from-orange-500 to-pink-500 rounded-full mt-4"></div>
                    <p class="text-gray-300 mt-4 max-w-2xl">Answers to common questions about booking, payments, pickup, and returns. If you still need help, use the help button below.</p>
                </div>

                <section class="space-y-6">
                    {{-- Accordion wrapper --}}
                    <div class="space-y-4">
                        @php
                        $faqs = [
                            ['q' => 'How to book a car for rental?', 'a' => '<p>These are following steps you can follow:</a></p><ol class="list-decimal list-inside mt-3 ml-4 text-gray-300"><li>Complete your information and wait for approval</li><li>Navigate to "BOOK" tab</li><li>Choose your car rental details</li><li>Choose your car and make a payment</ol>'],
                            ['q' => 'When will my deposit be returned ?', 'a' => '<p>Your deposit will be returned within 3-5 business days after vehicle return and inspection. Deductions for damages will be applied if necessary.</p>'],
                            ['q' => 'Can I rent with a P license?', 'a' => '<p>Yes, P license holders may rent. Ensure the license is valid and you meet minimum experience and insurance requirements.</p>'],
                            ['q' => 'Can I pickup the car outside of UTM?', 'a' => '<p>Pickup is primarily at UTM Student Mall. Alternative pickup locations can be arranged for an extra fee.</p>'],
                            ['q' => 'Can I pickup the car at night?', 'a' => '<p>Night pickups are possible with prior arrangement; additional charges may apply.</p>']
                        ];
                        @endphp

                        @foreach($faqs as $i => $item)
                        <div class="bg-gradient-to-br from-black/30 to-black/50 border border-white/10 rounded-2xl overflow-hidden">
                            <button class="w-full flex items-center justify-between px-6 py-5 text-left hover:bg-white/5 transition" onclick="toggleFaq({{ $i+1 }})">
                                <span class="text-white font-semibold text-lg">{{ $item['q'] }}</span>
                                <i id="icon-{{ $i+1 }}" class="fas fa-chevron-down text-orange-400"></i>
                            </button>
                            <div id="answer-{{ $i+1 }}" class="faq-answer max-h-0 overflow-hidden transition-all duration-400 px-6" style="">
                                <div class="py-4 text-gray-300 leading-relaxed">{!! $item['a'] !!}</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </section>
            </div>

        </div>
    </div>
                                    <p class="mt-3">For pickups after 6:00 PM, please contact us at least 24 hours in advance. An additional service fee may apply for after-hours pickups.</p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- RIGHT: Contact Us Box --}}
            <div class="w-full lg:w-1/3 lg:sticky lg:top-24">
                <div class="bg-white border-2 border-purple-500/30 rounded-2xl p-8 shadow-2xl">
                    {{-- Phone Icon --}}
                    <div class="flex justify-center mb-6">
                        <div class="w-24 h-24 rounded-full bg-purple-100 flex items-center justify-center">
                            <i class="fas fa-phone text-purple-600 text-4xl"></i>
                        </div>
                    </div>
                    
                    {{-- Contact Us Text --}}
                    <h3 class="text-2xl font-bold text-gray-900 text-center mb-6">Contact us</h3>
                    
                    {{-- WhatsApp Section --}}
                    <div class="flex items-center justify-center gap-3 mb-4">
                        <i class="fab fa-whatsapp text-green-600 text-3xl"></i>
                        <span class="text-gray-900 font-bold text-lg">Whatsapp</span>
                    </div>
                    
                    {{-- WhatsApp Link --}}
                    <a href="https://wa.me/60123456789" target="_blank" class="block w-full bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-6 rounded-xl text-center transition duration-300 shadow-lg hover:shadow-xl">
                        <i class="fab fa-whatsapp mr-2"></i> Message Us Now
                    </a>
                    
                    {{-- Additional Contact Info --}}
                    <div class="mt-6 pt-6 border-t border-gray-200 space-y-3">
                        <div class="flex items-center gap-3 text-gray-700">
                            <i class="fas fa-phone text-purple-600"></i>
                            <span class="text-sm">+60 12-345 6789</span>
                        </div>
                        <div class="flex items-center gap-3 text-gray-700">
                            <i class="fas fa-clock text-purple-600"></i>
                            <span class="text-sm">9:00 AM - 6:00 PM</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- JavaScript for Accordion with Smooth Transitions --}}
<script>
    function toggleFaq(id) {
        const answer = document.getElementById(`answer-${id}`);
        const button = answer.previousElementSibling;
        const icon = document.getElementById(`icon-${id}`);
        const isOpen = answer.style.maxHeight && answer.style.maxHeight !== '0px';
        
        // Close all other FAQs with smooth animation
        document.querySelectorAll('.faq-answer').forEach((item, index) => {
            const itemId = index + 1;
            if (itemId !== id) {
                item.style.maxHeight = '0px';
                item.parentElement.style.opacity = '1';
                const otherIcon = document.getElementById(`icon-${itemId}`);
                const otherBtn = item.previousElementSibling;
                if (otherIcon) {
                    otherIcon.classList.remove('fa-chevron-up');
                    otherIcon.classList.add('fa-chevron-down');
                    otherIcon.style.transform = 'rotate(0deg)';
                }
                if (otherBtn) {
                    otherBtn.style.backgroundColor = 'transparent';
                }
            }
        });
        
        // Toggle current FAQ with smooth animation
        if (isOpen) {
            answer.style.maxHeight = '0px';
            button.style.backgroundColor = 'transparent';
            icon.classList.remove('fa-chevron-up');
            icon.classList.add('fa-chevron-down');
            icon.style.transform = 'rotate(0deg)';
        } else {
            answer.style.maxHeight = answer.scrollHeight + 'px';
            button.style.backgroundColor = 'rgba(255, 255, 255, 0.05)';
            icon.classList.remove('fa-chevron-down');
            icon.classList.add('fa-chevron-up');
            icon.style.transform = 'rotate(180deg)';
        }
    }
</script>

{{-- Styles --}}
<style>
    @keyframes fade-in-up {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .animate-fade-in-up {
        animation: fade-in-up 1s ease-out forwards;
    }
    
    .faq-item {
        transition: all 0.3s ease;
    }
    
    .faq-item:hover {
        border-color: rgba(251, 146, 60, 0.3);
    }
    
    .faq-icon {
        flex-shrink: 0;
        transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .faq-answer {
        transition: max-height 0.5s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.3s ease;
    }
    
    button {
        transition: background-color 0.3s ease;
    }
</style>
@endsection

