@extends('layouts.app')

@section('content')
<div class="relative min-h-screen bg-[#0a0a0a]">
    
    {{-- Background Image --}}
    <div class="absolute inset-0 w-full h-[600px] md:h-[700px] overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/40 to-black/80 z-10"></div>
        <img src="{{ asset('airport-terminal.jpg') }}" alt="Background" class="w-full h-full object-cover">
    </div>

    <div class="container mx-auto px-6 pt-20 pb-20 relative z-20">
        {{-- Hero Content --}}
        <div class="p-12 md:p-20 rounded-[4rem] mb-12">
            <div class="max-w-2xl">
                <div class="inline-flex items-center gap-3 px-5 py-2 bg-white/10 backdrop-blur-md rounded-full border border-white/20 text-orange-500 text-xs font-black uppercase tracking-widest mb-8">
                    <span class="w-2 h-2 bg-orange-500 rounded-full animate-pulse"></span>
                    Flight Enquiry
                </div>
                
                <h1 class="text-6xl md:text-8xl font-black text-white mb-8 tracking-tighter leading-[0.9]">
                    Fly High, <br><span class="text-orange-500">Drive Easy.</span>
                </h1>
            </div>
        </div>

        {{-- Booking Form Section --}}
        <div class="max-w-4xl mx-auto -mt-10">
            <div class="bg-black/60 backdrop-blur-2xl border border-white/10 rounded-[3rem] p-8 md:p-12 shadow-2xl">
                <div class="mb-10">
                    <h2 class="text-3xl font-bold text-white mb-2">Booking Details</h2>
                    <p class="text-gray-400 text-sm">Fill in your travel details below. Our admin will assist you via WhatsApp.</p>
                </div>

                <form id="flightEnquiryForm" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Personal Info --}}
                        <div>
                            <label class="block text-gray-400 text-sm font-bold mb-2 ml-4">Full Name</label>
                            <input type="text" id="name" required placeholder="John Doe" 
                                class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-white focus:outline-none focus:border-orange-500 transition">
                        </div>
                        <div>
                            <label class="block text-gray-400 text-sm font-bold mb-2 ml-4">WhatsApp Number</label>
                            <input type="text" id="contact" required placeholder="+60 123 456 789" 
                                class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-white focus:outline-none focus:border-orange-500 transition">
                        </div>

                        {{-- Route Info (Side by Side) --}}
                        <div>
                            <label class="block text-gray-400 text-sm font-bold mb-2 ml-4">Departure From</label>
                            <input type="text" id="departure_loc" required placeholder="e.g. Senai (JHB)" 
                                class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-white focus:outline-none focus:border-orange-500 transition">
                        </div>
                        <div>
                            <label class="block text-gray-400 text-sm font-bold mb-2 ml-4">Destination To</label>
                            <input type="text" id="destination_loc" required placeholder="e.g. Penang (PEN)" 
                                class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-white focus:outline-none focus:border-orange-500 transition">
                        </div>

                        {{-- Travel Period --}}
                        <div>
                            <label class="block text-gray-400 text-sm font-bold mb-2 ml-4">Departure Date</label>
                            <input type="date" id="departure_date" required 
                                class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-white focus:outline-none focus:border-orange-500 transition [color-scheme:dark]">
                        </div>
                        <div>
                            <label class="block text-gray-400 text-sm font-bold mb-2 ml-4">Return Date</label>
                            <input type="date" id="return_date" 
                                class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-white focus:outline-none focus:border-orange-500 transition [color-scheme:dark]">
                        </div>

                        {{-- Pax --}}
                        <div class="md:col-span-2">
                            <label class="block text-gray-400 text-sm font-bold mb-2 ml-4">Number of Passengers (Pax)</label>
                            <input type="number" id="pax" min="1" required placeholder="e.g. 2" 
                                class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-white focus:outline-none focus:border-orange-500 transition">
                        </div>

                        {{-- Requirements --}}
                        <div class="md:col-span-2">
                            <label class="block text-gray-400 text-sm font-bold mb-2 ml-4">Other Requirements</label>
                            <textarea id="requirements" rows="3" placeholder="e.g. Extra baggage, preferred airline, window seat..." 
                                class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-white focus:outline-none focus:border-orange-500 transition"></textarea>
                        </div>
                    </div>

                    <div class="pt-6">
                        <button type="submit" 
                            class="w-full bg-orange-500 hover:bg-orange-600 text-white font-black py-5 rounded-2xl text-xl transition-all shadow-xl shadow-orange-500/20 active:scale-[0.98] flex items-center justify-center gap-3">
                            <i class="fab fa-whatsapp text-2xl"></i>
                            Enquire via WhatsApp
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const departureInput = document.getElementById('departure_date');
    const returnInput = document.getElementById('return_date');

    // 1. Set Departure Date Minimum to Tomorrow
    const today = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);
    const tomorrowStr = tomorrow.toISOString().split('T')[0];
    departureInput.setAttribute('min', tomorrowStr);

    // 2. Adjust Return Date Minimum based on Departure Date
    departureInput.addEventListener('change', function() {
        const selectedDeparture = new Date(this.value);
        const minReturnDate = new Date(selectedDeparture);
        minReturnDate.setDate(minReturnDate.getDate() + 1);
        const minReturnStr = minReturnDate.toISOString().split('T')[0];
        
        returnInput.setAttribute('min', minReturnStr);
        
        if (returnInput.value && returnInput.value <= this.value) {
            returnInput.value = '';
        }
    });

    // 3. WhatsApp Form Submission
    document.getElementById('flightEnquiryForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const adminPhone = "60137104487"; // Update to your admin number

        const name = document.getElementById('name').value;
        const contact = document.getElementById('contact').value;
        const depLoc = document.getElementById('departure_loc').value;
        const destLoc = document.getElementById('destination_loc').value;
        const departureDate = departureInput.value;
        const returnDate = returnInput.value || "One-way";
        const pax = document.getElementById('pax').value;
        const reqs = document.getElementById('requirements').value || "None";

        const message = `*NEW FLIGHT ENQUIRY*%0A` +
                        `----------------------------%0A` +
                        `*Name:* ${name}%0A` +
                        `*Contact:* ${contact}%0A` +
                        `*Route:* ${depLoc} to ${destLoc}%0A` +
                        `*Departure:* ${departureDate}%0A` +
                        `*Return:* ${returnDate}%0A` +
                        `*Pax:* ${pax} Person(s)%0A` +
                        `*Requirements:* ${reqs}`;

        window.open(`https://wa.me/${adminPhone}?text=${message}`, '_blank');
    });
</script>
@endsection