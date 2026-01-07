@extends('layouts.app')

@section('content')

{{-- SweetAlert2 for Popups --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- CUSTOM STYLES --}}
<style>
    /* GLASS AESTHETIC */
    .glass-section {
        background-color: #050505; /* Deepest Black */
        position: relative;
        overflow: hidden;
    }
    
    .glass-card {
        background: rgba(255, 255, 255, 0.03); /* Lower opacity for darker feel */
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.08);
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.5);
        transition: transform 0.3s ease;
    }

    /* Form Input Styles */
    .glass-input {
        background: rgba(0, 0, 0, 0.3); /* Darker input background */
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: white;
        transition: all 0.3s ease;
    }
    .glass-input:focus {
        background: rgba(0, 0, 0, 0.5);
        border-color: #ea580c;
        outline: none;
    }

    @keyframes fade-in-up {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in-up { animation: fade-in-up 1s ease-out forwards; }
    .delay-100 { animation-delay: 100ms; }
    .delay-200 { animation-delay: 200ms; }
</style>

{{-- SECTION 1: HERO (Darker Version) --}}
<div class="relative h-screen min-h-[600px] flex flex-col justify-center bg-black overflow-hidden">
    
    {{-- Background Image --}}
    <div class="absolute inset-0 w-full h-full">
        {{-- 1. Darker Gradient Overlay --}}
        <div class="absolute inset-0 bg-gradient-to-r from-black/50 via-black/30 to-black/60 z-10"></div>
        
        {{-- 2. Lowered Image Opacity (opacity-40) --}}
        <img src="{{ asset('hastabg1.png') }}" alt="Contact Background" class="w-full h-full object-cover opacity-40">
    </div>

    {{-- Content Container --}}
    <div class="relative z-20 container mx-auto px-6 md:px-12 flex flex-col h-full justify-center items-center gap-10 pt-10 pb-20">
        
        {{-- Navigation Pill --}}
        <div class="flex justify-center animate-fade-in-up">
            <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-full p-1.5 flex flex-wrap justify-center md:flex-nowrap items-center shadow-2xl">
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
                LET'S <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-500 to-red-600">CONNECT</span>
            </h1>
            <p class="text-lg md:text-xl text-gray-400 font-light mb-10 max-w-2xl mx-auto leading-relaxed">
                Have a question? Need help with a booking? We are here to keep your journey moving forward.
            </p>
            
            <a href="#contact-section" class="px-10 py-4 bg-white/5 hover:bg-white/10 text-white font-bold rounded-full backdrop-blur-md border border-white/10 transition flex items-center justify-center w-fit mx-auto">
                Send a Message <i class="fas fa-arrow-down ml-3"></i>
            </a>
        </div>
    </div>

    {{-- Decorative Bottom Fade (Transitions to #050505) --}}
    <div class="absolute bottom-0 left-0 w-full h-32 bg-gradient-to-t from-[#050505] to-transparent z-20"></div>
</div>


{{-- SECTION 2: CONTACT FORM & INFO (Glass Aesthetic) --}}
<div id="contact-section" class="glass-section py-24 border-b border-white/5">
    
    {{-- Ambient Glow (Darker/Subtler) --}}
    <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-orange-900/10 blur-[120px] rounded-full pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-blue-900/10 blur-[120px] rounded-full pointer-events-none"></div>

    <div class="container mx-auto px-4 relative z-10">
        <div class="flex flex-col lg:flex-row gap-16 items-start">
            
            {{-- LEFT: Info Cards --}}
            <div class="w-full lg:w-5/12 space-y-8">
                <div>
                    <h2 class="text-4xl font-black text-white mb-4">Get in Touch</h2>
                    <p class="text-gray-400 text-lg">We are available 24/7 for UTM students and staff.</p>
                </div>

                {{-- Glass Contact Cards --}}
                <div class="space-y-4">
                    {{-- Phone --}}
                    <div class="glass-card p-6 rounded-2xl flex items-center gap-5 group hover:bg-white/5">
                        <div class="w-12 h-12 rounded-xl bg-orange-500/10 flex items-center justify-center text-orange-500 group-hover:scale-110 transition">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-white">Call Us</h3>
                            <p class="text-sm text-gray-400">+60 12-345 6789</p>
                        </div>
                    </div>

                    {{-- Whatsapp --}}
                    <div class="glass-card p-6 rounded-2xl flex items-center gap-5 group hover:bg-white/5">
                        <div class="w-12 h-12 rounded-xl bg-green-500/10 flex items-center justify-center text-green-500 group-hover:scale-110 transition">
                            <i class="fab fa-whatsapp text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-white">WhatsApp</h3>
                            <p class="text-sm text-gray-400">Fast Response (9am - 6pm)</p>
                        </div>
                    </div>

                    {{-- Location --}}
                    <div class="glass-card p-6 rounded-2xl flex items-center gap-5 group hover:bg-white/5">
                        <div class="w-12 h-12 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-500 group-hover:scale-110 transition">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-white">Student Mall, UTM</h3>
                            <p class="text-sm text-gray-400">81310 Skudai, Johor.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT: The Form --}}
            <div class="w-full lg:w-7/12">
                <div class="glass-card p-8 md:p-10 rounded-[2.5rem]">
                    <h3 class="text-2xl font-bold text-white mb-6">Send a Message</h3>
                    
                    <form id="contactForm" action="https://formspree.io/f/YOUR_FORMSPREE_ID" method="POST" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="group">
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Name</label>
                                <input type="text" name="name" required placeholder="Your Name"
                                    class="glass-input w-full rounded-xl px-5 py-4 placeholder-gray-600">
                            </div>
                            <div class="group">
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Email</label>
                                <input type="email" name="email" required placeholder="email@example.com"
                                    class="glass-input w-full rounded-xl px-5 py-4 placeholder-gray-600">
                            </div>
                        </div>

                        <div class="group">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Subject</label>
                            <select name="subject" class="glass-input w-full rounded-xl px-5 py-4 cursor-pointer appearance-none text-gray-400">
                                <option class="bg-gray-900" value="General Inquiry">General Inquiry</option>
                                <option class="bg-gray-900" value="Booking Help">Booking Help</option>
                                <option class="bg-gray-900" value="Feedback">Feedback</option>
                            </select>
                        </div>

                        <div class="group">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Message</label>
                            <textarea name="message" rows="4" required placeholder="How can we help?"
                                class="glass-input w-full rounded-xl px-5 py-4 placeholder-gray-600 resize-none"></textarea>
                        </div>

                        <button type="submit" id="submitBtn" class="w-full bg-gradient-to-r from-orange-600 to-orange-500 text-white font-bold py-4 rounded-xl shadow-lg shadow-orange-900/20 hover:shadow-orange-500/40 hover:scale-[1.01] active:scale-95 transition-all duration-300 flex justify-center items-center">
                            <span>Send Message</span>
                            <i class="fas fa-paper-plane ml-2"></i>
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- JAVASCRIPT --}}
<script>
    var form = document.getElementById("contactForm");
    
    async function handleSubmit(event) {
        event.preventDefault();
        var status = document.getElementById("submitBtn");
        var originalText = status.innerHTML;
        var data = new FormData(event.target);

        status.innerHTML = '<i class="fas fa-circle-notch fa-spin mr-2"></i> Sending...';
        status.disabled = true;
        status.classList.add('opacity-75', 'cursor-not-allowed');

        fetch(event.target.action, {
            method: form.method, body: data, headers: { 'Accept': 'application/json' }
        }).then(response => {
            if (response.ok) {
                Swal.fire({
                    title: 'Message Sent!',
                    text: 'We will get back to you shortly.',
                    icon: 'success',
                    background: '#1f2937', color: '#fff', confirmButtonColor: '#f97316'
                });
                form.reset();
            } else {
                Swal.fire('Oops!', 'Something went wrong.', 'error');
            }
        }).catch(error => {
            Swal.fire('Error', 'Please check your internet connection.', 'error');
        }).finally(() => {
            status.innerHTML = originalText;
            status.disabled = false;
            status.classList.remove('opacity-75', 'cursor-not-allowed');
        });
    }

    form.addEventListener("submit", handleSubmit);
</script>
@endsection