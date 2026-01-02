@extends('layouts.app')

@section('content')
{{-- SweetAlert2 for Popups --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- 
    FULL-SCREEN "BREAKOUT" HERO
    -mt-28 / pt-32: Finely tuned to hide the layout's white bar 
    while giving the correct gap for the Red Header.
--}}
<div class="w-screen relative left-[calc(-50vw+50%)] -mt-10 pt-32 min-h-screen flex flex-col items-center justify-center bg-gray-900 overflow-hidden z-10">
    
    {{-- 1. BACKGROUND IMAGE (Elegant Car/Rental Theme) --}}
    <div class="absolute inset-0 z-0">
        {{-- Image: Elegant Dashboard/Driving View --}}
        <img src="https://images.unsplash.com/photo-1502877338535-766e1452684a?q=80&w=2072&auto=format&fit=crop" 
             alt="Luxury Drive" 
             class="w-full h-full object-cover opacity-40">
        
        {{-- Dark Gradient Overlay for "Calm" Contrast --}}
        <div class="absolute inset-0 bg-gradient-to-b from-gray-900/90 via-gray-900/60 to-gray-900"></div>
    </div>

    {{-- 2. NAVIGATION PILL (Exact Match to Home Page) --}}
    <div class="relative z-20 mb-16 mt-16 animate-fade-in-up">
        <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-full p-1.5 flex flex-wrap justify-center items-center shadow-2xl">
            <a href="{{ route('book.create') }}" class="px-6 md:px-8 py-2.5 text-white/90 font-bold hover:bg-white/10 rounded-full transition text-sm md:text-base">Book a Car</a>
            <a href="{{ route('book.index') }}" class="px-6 md:px-8 py-2.5 text-white/90 font-bold hover:bg-white/10 rounded-full transition text-sm md:text-base">My Bookings</a>
            <a href="{{ route('loyalty.index') }}" class="px-6 md:px-8 py-2.5 text-white/90 font-bold hover:bg-white/10 rounded-full transition text-sm md:text-base">Loyalty</a>
            <a href="{{ route('finance.index') }}" class="px-6 md:px-8 py-2.5 text-white/90 font-bold hover:bg-white/10 rounded-full transition text-sm md:text-base">Payments</a>
        </div>
    </div>

    {{-- 3. MAIN CONTENT --}}
    <div class="container mx-auto mb-60 px-4 relative z-10 max-w-6xl">
        <div class="flex flex-col lg:flex-row gap-16 items-start">
            
            {{-- LEFT: Text & Info --}}
            <div class="w-full lg:w-5/12 text-white space-y-8 animate-fade-in-up delay-100 pt-4">
                <div>
                    <span class="inline-block py-1 px-4 rounded-full bg-orange-500/20 border border-orange-500 text-orange-400 text-xs font-bold tracking-widest uppercase mb-6 backdrop-blur-sm">
                        Contact Us
                    </span>
                    <h1 class="text-5xl font-black leading-tight mb-6 drop-shadow-lg">
                        Drive with <br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-400 to-amber-200">Confidence.</span>
                    </h1>
                    <p class="text-lg text-gray-300 font-light leading-relaxed">
                        Experience the smoothest rental journey. From booking inquiries to roadside assistance, we are always here to keep you moving forward.
                    </p>
                </div>

                {{-- Contact Cards --}}
                <div class="space-y-4 mt-8">
                    <div class="bg-black/40 backdrop-blur-lg border border-white/10 p-5 rounded-2xl flex items-center gap-5 hover:bg-black/50 transition duration-300 group">
                        <div class="w-12 h-12 rounded-full bg-orange-500/20 flex items-center justify-center text-orange-400 group-hover:scale-110 transition">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-white">Call Us</h3>
                            <p class="text-sm text-gray-400">+60 12-345 6789</p>
                        </div>
                    </div>

                    <div class="bg-black/40 backdrop-blur-lg border border-white/10 p-5 rounded-2xl flex items-center gap-5 hover:bg-black/50 transition duration-300 group">
                        <div class="w-12 h-12 rounded-full bg-green-500/20 flex items-center justify-center text-green-400 group-hover:scale-110 transition">
                            <i class="fab fa-whatsapp text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-white">WhatsApp</h3>
                            <p class="text-sm text-gray-400">Fast Response (9am - 6pm)</p>
                        </div>
                    </div>

                    <div class="bg-black/40 backdrop-blur-lg border border-white/10 p-5 rounded-2xl flex items-center gap-5 hover:bg-black/50 transition duration-300 group">
                        <div class="w-12 h-12 rounded-full bg-blue-500/20 flex items-center justify-center text-blue-400 group-hover:scale-110 transition">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-white">Student Mall, UTM</h3>
                            <p class="text-sm text-gray-400">81310 Skudai, Johor.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT: Glass Form --}}
            <div class="w-full lg:w-7/12 animate-fade-in-up delay-200">
                <div class="bg-black/40 backdrop-blur-xl border border-white/10 p-8 md:p-10 rounded-[2rem] shadow-2xl relative overflow-hidden">
                    
                    {{-- Soft Glow Effects for "Calm" feel --}}
                    <div class="absolute -top-32 -right-32 w-64 h-64 bg-orange-500 rounded-full blur-[100px] opacity-20 pointer-events-none"></div>
                    <div class="absolute -bottom-32 -left-32 w-64 h-64 bg-blue-600 rounded-full blur-[100px] opacity-20 pointer-events-none"></div>

                    <h2 class="text-3xl font-bold text-white mb-2 relative z-10">Send a Message</h2>
                    <p class="text-gray-400 mb-8 relative z-10 text-sm">Fill out the form below and we'll get back to you.</p>

                    <form id="contactForm" action="https://formspree.io/f/YOUR_FORMSPREE_ID" method="POST" class="space-y-5 relative z-10">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="group">
                                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Name</label>
                                <input type="text" name="name" required placeholder="Your Name"
                                    class="w-full bg-white/5 border border-white/10 text-white rounded-xl px-5 py-4 outline-none focus:border-orange-500 focus:bg-white/10 transition placeholder-gray-600">
                            </div>

                            <div class="group">
                                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Email</label>
                                <input type="email" name="email" required placeholder="email@example.com"
                                    class="w-full bg-white/5 border border-white/10 text-white rounded-xl px-5 py-4 outline-none focus:border-orange-500 focus:bg-white/10 transition placeholder-gray-600">
                            </div>
                        </div>

                        <div class="group">
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Subject</label>
                            <select name="subject" class="w-full bg-white/5 border border-white/10 text-white rounded-xl px-5 py-4 outline-none focus:border-orange-500 focus:bg-white/10 transition appearance-none cursor-pointer">
                                <option class="text-gray-900" value="General Inquiry">General Inquiry</option>
                                <option class="text-gray-900" value="Booking Help">Booking Help</option>
                                <option class="text-gray-900" value="Feedback">Feedback</option>
                            </select>
                        </div>

                        <div class="group">
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Message</label>
                            <textarea name="message" rows="4" required placeholder="How can we help?"
                                class="w-full bg-white/5 border border-white/10 text-white rounded-xl px-5 py-4 outline-none focus:border-orange-500 focus:bg-white/10 transition placeholder-gray-600 resize-none"></textarea>
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

{{-- SCRIPT --}}
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

<style>
    @keyframes fade-in-up {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in-up {
        animation: fade-in-up 1s ease-out forwards;
    }
    .delay-100 { animation-delay: 100ms; }
    .delay-200 { animation-delay: 200ms; }
</style>
@endsection