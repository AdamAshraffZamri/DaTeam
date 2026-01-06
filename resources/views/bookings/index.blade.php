@extends('layouts.app')

@section('content')
{{-- 1. FIXED BACKGROUND --}}
<div class="fixed inset-0 z-0">
    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('hastabg.png') }}');"></div>
    <div class="absolute inset-0 bg-gradient-to-r from-black/70 via-black/45 to-black/75"></div>
</div>

{{-- 2. MAIN CONTENT --}}
<div class="relative z-10 min-h-[calc(100vh-64px)] py-12">
    <div class="container mx-auto px-4 max-w-4xl">
        
        {{-- HEADER WITH FILTERS --}}
        <div class="flex flex-col md:flex-row justify-between items-end mb-10 gap-4">
            <div>
                <h1 class="text-4xl font-black text-white drop-shadow-[0_2px_4px_rgba(0,0,0,0.5)]">My Bookings</h1>
                <p class="text-gray-300 mt-2">Manage your active and past rentals.</p>
            </div>

            {{-- SERVER-SIDE FILTER BAR (MODERN STATUS DROPDOWN) --}}
            <form action="{{ route('book.index') }}" method="GET" id="filterForm" class="relative z-50">
                @php
                    $currentStatus = request('status', 'all');
                    
                    // Comprehensive list of booking and payment statuses
                    $statuses = [
                        'all'          => 'All Bookings',
                        'Submitted'    => 'Submitted', 
                        'Deposit Paid' => 'Deposit Paid',
                        'Paid'         => 'Paid (Full)',
                        'Confirmed'     => 'Confirmed',
                        'Active'       => 'Active',
                        'Completed'    => 'Completed',
                        'Cancelled'    => 'Cancelled',
                        'Rejected'     => 'Rejected'
                    ];
                    $currentLabel = $statuses[$currentStatus] ?? 'Select Status';
                @endphp

                <input type="hidden" name="status" id="statusInput" value="{{ $currentStatus }}">

                {{-- CUSTOM GLASS DROPDOWN --}}
                <div class="relative w-full md:w-[220px]" id="customDropdown">
                    
                    {{-- TRIGGER BUTTON --}}
                    <button type="button" onclick="toggleDropdown()" 
                        class="w-full flex items-center justify-between bg-black/40 backdrop-blur-md border border-white/15 text-white text-xs font-bold py-3.5 px-5 rounded-2xl hover:bg-white/10 hover:border-orange-500/50 transition-all shadow-xl group">
                        
                        <div class="flex items-center gap-3">
                            <i class="fas fa-filter text-orange-500"></i>
                            <span id="dropdownLabel">{{ $currentLabel }}</span>
                        </div>

                        <i class="fas fa-chevron-down text-[10px] text-gray-400 group-hover:text-white transition-transform duration-300" id="dropdownArrow"></i>
                    </button>

                    {{-- DROPDOWN MENU --}}
                    <div id="dropdownMenu" 
                        class="absolute top-full right-0 mt-2 w-full bg-[#1a1a1a] border border-white/10 rounded-2xl shadow-2xl overflow-hidden hidden transform origin-top transition-all duration-200 z-50 max-h-[350px] overflow-y-auto custom-scrollbar">
                        
                        @foreach($statuses as $value => $label)
                        <div onclick="selectStatus('{{ $value }}')" 
                             class="px-5 py-3 text-xs font-bold cursor-pointer transition-colors flex items-center justify-between border-b border-white/5 last:border-0
                             {{ $currentStatus == $value ? 'bg-orange-600 text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white' }}">
                            
                            <span>{{ $label }}</span>
                            
                            @if($currentStatus == $value)
                                <i class="fas fa-check"></i>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
            </form>

        </div>

        <div class="space-y-7 min-h-[300px]">
            {{-- LOOP: CARDS --}}
            @forelse($bookings as $booking)
                <div class="group bg-black/50 backdrop-blur-[2px] border border-white/15 rounded-3xl p-6 shadow-xl hover:shadow-orange-500/10 hover:bg-black/60 transition-all duration-300 relative overflow-hidden animate-fade-in">
                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/2 to-transparent opacity-0 group-hover:opacity-10 transition-opacity duration-500 pointer-events-none"></div>
                    
                    {{-- Clickable Area for Details --}}
                    <div onclick="document.getElementById('modal-{{ $booking->bookingID }}').classList.remove('hidden')" class="cursor-pointer">
                        {{-- Card Header --}}
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex items-center gap-4">
                                {{-- Numbering Badge --}}
                                <div class="bg-orange-500 text-white font-bold w-10 h-10 rounded-full flex items-center justify-center text-sm shadow-lg shrink-0">
                                    {{ $loop->iteration }}
                                </div>

                                {{-- BOOKING DATE --}}
                                <div>
                                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider leading-none mb-1">Your booking starts:</p>
                                    <p class="text-white text-sm font-bold leading-none">
                                        {{ \Carbon\Carbon::parse($booking->originalDate)->format('d M Y') }}
                                    </p>
                                </div>
                            </div>

                            {{-- Status Badge --}}
                            <span class="px-3 py-1.5 rounded-full text-[11px] font-bold border uppercase tracking-wide
                                {{ $booking->bookingStatus == 'Submitted' ? 'bg-blue-500/15 text-blue-300 border-blue-500/30' : 
                                  ($booking->bookingStatus == 'Cancelled' ? 'bg-red-500/15 text-red-300 border-red-500/30' : 
                                  'bg-emerald-500/15 text-emerald-300 border-emerald-500/30') }}">
                                {{ $booking->bookingStatus }}
                            </span>
                        </div>

                        {{-- [UPDATED] Vehicle Info with IMAGE --}}
                        <div class="flex items-center gap-4 mb-4">
                            {{-- Car Image Container --}}
                            <div class="h-16 w-24 rounded-xl bg-gray-800 overflow-hidden border border-white/10 shrink-0 relative shadow-md">
                                @if($booking->vehicle->image)
                                    <img src="{{ asset('storage/' . $booking->vehicle->image) }}" 
                                         alt="{{ $booking->vehicle->model }}" 
                                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <i class="fas fa-car text-2xl text-gray-600"></i>
                                    </div>
                                @endif
                            </div>

                            <div>
                                <h3 class="text-xl font-black text-white leading-tight">{{ $booking->vehicle->model }}</h3>
                                <p class="text-xs text-orange-400 font-bold mt-1 tracking-wide">{{ $booking->vehicle->plateNo }}</p>
                            </div>
                        </div>

                        {{-- AGENT INFO ON CARD --}}
                        @if($booking->staff)
                        <div class="flex items-center gap-2 mb-2 pb-2 border-b border-white/5">
                            <div class="w-5 h-5 rounded-full bg-orange-500/20 flex items-center justify-center text-orange-500 text-[10px]">
                                <i class="fas fa-user-tie"></i>
                            </div>
                            <p class="text-xs text-gray-400">Agent: <span class="text-gray-200 font-bold">{{ $booking->staff->name }}</span></p>
                        </div>
                        @endif

                        <div class="text-[11px] text-gray-400 font-medium text-center mt-2 opacity-90 mb-4">Click to view full details</div>
                    </div>

                    {{-- ACTIONS: INSPECTION & FEEDBACK --}}
                    
                    {{-- 1. INSPECTION BUTTON --}}
                    @php
                        // Check if customer has already uploaded for the current stage
                        $isReturn = ($booking->bookingStatus == 'Active');
                        $typeCheck = $isReturn ? 'Return' : 'Pickup';

                        $alreadySubmitted = $booking->inspections
                            ->where('inspectionType', $typeCheck)
                            ->whereNull('staffID')
                            ->isNotEmpty();
                    @endphp

                    @if(($booking->bookingStatus == 'Confirmed' || $booking->bookingStatus == 'Active') && !$alreadySubmitted)
                        <div class="border-t border-white/10 pt-4 mt-2">
                            <button onclick="document.getElementById('inspection-modal-{{ $booking->bookingID }}').classList.remove('hidden')" 
                                    class="w-full bg-[#ea580c] hover:bg-orange-600 text-white py-3 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 shadow-lg">
                                <i class="fas fa-camera"></i> 
                                {{ $booking->bookingStatus == 'Confirmed' ? 'Upload Pickup Photos' : 'Upload Return Photos' }}
                            </button>
                        </div>
                    @elseif($alreadySubmitted)
                        <div class="border-t border-white/10 pt-4 mt-2">
                            <div class="w-full bg-green-500/20 text-green-400 py-3 rounded-xl text-xs font-bold flex items-center justify-center gap-2 border border-green-500/30">
                                <i class="fas fa-check-circle"></i> 
                                {{ $typeCheck }} Inspection Submitted
                            </div>
                        </div>
                    @endif

                    {{-- 2. FEEDBACK BUTTON --}}
                    @if($booking->bookingStatus == 'Completed' && !$booking->feedback)
                        <div class="border-t border-white/10 pt-4 mt-2">
                            <button onclick="document.getElementById('feedback-modal-{{ $booking->bookingID }}').classList.remove('hidden')" 
                                    class="w-full bg-yellow-500 hover:bg-yellow-600 text-black py-3 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 shadow-lg">
                                <i class="fas fa-star"></i> Leave Feedback
                            </button>
                        </div>
                    @endif
                    
                    {{-- 3. INVOICE BUTTON (NEW) --}}
                    @if($booking->bookingStatus == 'Completed')
                    <div class="border-t border-white/10 pt-4 mt-2">
                        <td>
                            {{-- Existing Agreement/Receipt Buttons... --}}

                            {{-- NEW: View Invoice Button --}}
                            @if($booking->bookingStatus == 'Completed' && $booking->invoiceLink)
                                <a href="{{ $booking->invoiceLink }}" target="_blank" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 shadow-lg">
                                <i class="fas fa-file-invoice-dollar"></i> View Invoice
                                </a>
                            @elseif($booking->bookingStatus == 'Completed')
                                {{-- Fallback if generation failed (or for old bookings) --}}
                                <a href="{{ route('book.invoice', $booking->bookingID) }}" target="_blank" class="btn btn-sm btn-outline-secondary mt-1">
                                    Generate Invoice
                                </a>
                            @endif
                        </td>
                    </div>
                    @endif
                </div>
            @empty
                {{-- Empty State --}}
                <div class="text-center py-24 bg-white/8 backdrop-blur-sm rounded-3xl border border-white/15">
                    <div class="bg-white/10 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-5">
                        <i class="fas fa-filter text-3xl text-gray-500"></i>
                    </div>
                    <h3 class="text-xl font-black text-white mb-2">No bookings found</h3>
                    <p class="text-gray-400 max-w-md mx-auto mb-7">
                        @if(request('status') && request('status') != 'all')
                            No {{ request('status') }} bookings found.
                        @else
                            You haven't rented any cars yet.
                        @endif
                    </p>
                    @if(request('status') && request('status') != 'all')
                        <a href="{{ route('book.index') }}" class="inline-block px-6 py-2 bg-white/10 hover:bg-white/20 rounded-full text-white text-xs font-bold transition">Clear Filter</a>
                    @endif
                </div>
            @endforelse
        </div>
    </div>
</div>

{{-- 3. INSPECTION MODALS (LOOP 2) --}}
@foreach($bookings as $booking)
    @if($booking->bookingStatus == 'Confirmed' || $booking->bookingStatus == 'Active')
    
    {{-- Determine Required Count --}}
    @php
        $requiredCount = ($booking->bookingStatus == 'Confirmed') ? 5 : 6;
    @endphp

    <div id="inspection-modal-{{ $booking->bookingID }}" class="fixed inset-0 z-[10000] hidden bg-black/70 backdrop-blur-md flex items-center justify-center p-4">
        <div class="bg-[#1a1a1a] border border-white/15 rounded-2xl w-full max-w-md p-6 relative shadow-2xl">
            <button onclick="document.getElementById('inspection-modal-{{ $booking->bookingID }}').classList.add('hidden')" class="absolute top-4 right-4 text-gray-400 hover:text-white">
                <i class="fas fa-times text-xl"></i>
            </button>
            
            <h3 class="text-xl font-bold text-white mb-2">
                {{ $booking->bookingStatus == 'Confirmed' ? 'Pre-Rental Inspection' : 'Post-Rental Inspection' }}
            </h3>

            {{-- Photo Requirement Instructions --}}
            <div class="p-3 bg-white/5 rounded-xl border border-white/10 mb-6">
                <p class="text-[10px] text-orange-500 font-black uppercase tracking-widest mb-1">Requirements:</p>
                <ul class="text-[11px] text-gray-300 space-y-1">
                    <li class="flex items-center gap-2"><i class="fas fa-check-circle text-[8px]"></i> 4 External Views (Front, Back, Left, Right)</li>
                    <li class="flex items-center gap-2"><i class="fas fa-check-circle text-[8px]"></i> 1 Dashboard View (Mileage & Fuel)</li>
                    @if($booking->bookingStatus == 'Active')
                        <li class="flex items-center gap-2 text-orange-400"><i class="fas fa-key text-[8px]"></i> 1 Car Key Location (Required for Return)</li>
                    @endif
                </ul>
            </div>

            <form action="{{ route('book.inspection.upload', $booking->bookingID) }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                {{-- FILE INPUT LABEL --}}
                <label class="block w-full h-32 border-2 border-dashed border-white/20 rounded-xl flex flex-col items-center justify-center cursor-pointer hover:border-orange-500 hover:bg-white/5 transition mb-4 group relative">
                    <i class="fas fa-images text-2xl text-gray-500 group-hover:text-orange-500 mb-2 transition"></i>
                    
                    {{-- Dynamic Text Span --}}
                    <span id="file-text-{{ $booking->bookingID }}" class="text-sm text-gray-300 group-hover:text-white font-medium transition-colors">
                        Tap to Select Photos
                    </span>
                    
                    <span class="text-[10px] text-orange-500 font-bold uppercase mt-1">
                        (Upload Exactly {{ $requiredCount }} Photos)
                    </span>

                    {{-- FILE INPUT --}}
                    {{-- Added 'id' and 'onchange' to trigger validation --}}
                    <input type="file" 
                           name="photos[]" 
                           multiple 
                           class="hidden" 
                           required 
                           id="file-input-{{ $booking->bookingID }}"
                           accept="image/*"
                           onchange="validateFiles({{ $booking->bookingID }}, {{ $requiredCount }})">
                </label>

                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="text-[10px] text-gray-500 uppercase font-bold">Fuel Level</label>
                        <select name="fuel_level" class="w-full bg-white/10 border border-white/20 rounded-lg text-white text-sm p-2 mt-1 focus:border-orange-500 focus:outline-none">
                            <option value="Full" class="text-gray-900">Full</option>
                            <option value="3/4" class="text-gray-900">3/4</option>
                            <option value="1/2" class="text-gray-900">1/2</option>
                            <option value="1/4" class="text-gray-900">1/4</option>
                            <option value="E" class="text-gray-900">Empty</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] text-gray-500 uppercase font-bold">Mileage (km)</label>
                        <input type="number" name="mileage" class="w-full bg-white/10 border border-white/20 rounded-lg text-white text-sm p-2 mt-1 focus:border-orange-500 focus:outline-none" placeholder="e.g. 12345" required>
                    </div>
                </div>

                {{-- SUBMIT BUTTON --}}
                {{-- Added 'id', 'disabled' state, and opacity classes --}}
                <button type="submit" 
                        id="submit-btn-{{ $booking->bookingID }}"
                        class="w-full bg-[#ea580c] hover:bg-orange-600 text-white font-bold py-3.5 rounded-xl shadow-lg transition transform hover:scale-[1.02] opacity-50 cursor-not-allowed"
                        disabled>
                    Submit {{ $booking->bookingStatus == 'Confirmed' ? 'Pickup' : 'Return' }} Inspection
                </button>
            </form>
        </div>
    </div>
    @endif
@endforeach

{{-- 4. FEEDBACK MODALS (LOOP 3) --}}
@foreach($bookings as $booking)
    @if($booking->bookingStatus == 'Completed' && !$booking->feedback)
    <div id="feedback-modal-{{ $booking->bookingID }}" class="fixed inset-0 z-[10000] hidden bg-black/70 backdrop-blur-md flex items-center justify-center p-4">
        <div class="bg-[#1a1a1a] border border-white/15 rounded-2xl w-full max-w-md p-6 relative shadow-2xl">
            <button onclick="document.getElementById('feedback-modal-{{ $booking->bookingID }}').classList.add('hidden')" class="absolute top-4 right-4 text-gray-400 hover:text-white">
                <i class="fas fa-times text-xl"></i>
            </button>
            <h3 class="text-xl font-bold text-white mb-2">Rate Your Experience</h3>
            
            {{-- Google Review Link Section --}}
            <div class="mb-6 p-4 bg-orange-500/10 border border-orange-500/30 rounded-xl">
                <p class="text-[10px] text-orange-500 font-black uppercase tracking-widest mb-2">Support Us on Google</p>
                <a href="https://share.google/VmSuaHx7XHvnaSiwU" target="_blank" class="flex items-center justify-between bg-white/5 hover:bg-white/10 p-3 rounded-lg transition group">
                    <div class="flex items-center gap-3">
                        <i class="fab fa-google text-white"></i>
                        <span class="text-sm text-white font-bold">Write a Google Review</span>
                    </div>
                    <i class="fas fa-external-link-alt text-xs text-gray-400 group-hover:text-white"></i>
                </a>
            </div>

            <p class="text-xs text-gray-400 mb-6">Alternatively, leave a private review for our team:</p>
            
            <form action="{{ route('feedback.store', $booking->bookingID) }}" method="POST">
                @csrf
                
                {{-- STAR RATING --}}
                <div class="flex flex-row-reverse justify-center gap-2 mb-6 group">
                    @for($i=5; $i>=1; $i--)
                        <input type="radio" id="star{{ $i }}-{{ $booking->bookingID }}" name="rating" value="{{ $i }}" class="hidden peer" required>
                        <label for="star{{ $i }}-{{ $booking->bookingID }}" class="text-3xl text-gray-600 cursor-pointer peer-checked:text-yellow-400 peer-hover:text-yellow-400 hover:text-yellow-400 transition">
                            <i class="fas fa-star"></i>
                        </label>
                    @endfor
                </div>

                {{-- NEW: DROPDOWN SUGGESTIONS --}}
                <div class="mb-4">
                    <label class="text-[10px] text-gray-500 uppercase font-bold mb-2 block">Quick Suggestions</label>
                    <div class="relative">
                        <select onchange="this.closest('form').querySelector('textarea[name=comment]').value = this.value" 
                                class="w-full bg-white/10 border border-white/20 rounded-xl text-white text-sm p-3 focus:border-yellow-500 focus:outline-none appearance-none cursor-pointer">
                            <option value="" class="bg-gray-800 text-gray-400">Select a phrase...</option>
                            <option value="Car was clean and comfortable." class="bg-gray-800">Car was clean and comfortable.</option>
                            <option value="Great service, very punctual!" class="bg-gray-800">Great service, very punctual!</option>
                            <option value="Smooth booking process." class="bg-gray-800">Smooth booking process.</option>
                            <option value="Friendly staff and good vehicle." class="bg-gray-800">Friendly staff and good vehicle.</option>
                            <option value="Will definitely rent again!" class="bg-gray-800">Will definitely rent again!</option>
                        </select>
                        <div class="absolute right-3 top-3.5 text-gray-400 pointer-events-none">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>

                {{-- COMMENT AREA --}}
                <div class="mb-6">
                    <label class="text-[10px] text-gray-500 uppercase font-bold mb-2 block">Comment (Optional)</label>
                    <textarea name="comment" rows="3" class="w-full bg-white/10 border border-white/20 rounded-xl text-white text-sm p-3 focus:border-yellow-500 focus:outline-none placeholder-gray-500" placeholder="Share your feedback..."></textarea>
                </div>

                <button type="submit" class="w-full bg-yellow-500 hover:bg-yellow-600 text-black font-bold py-3.5 rounded-xl shadow-lg transition transform hover:scale-[1.02]">
                    Submit Internal Feedback
                </button>
            </form>
        </div>
    </div>
    @endif
@endforeach

{{-- 5. DETAILS MODALS (LOOP 4) --}}
@foreach($bookings as $booking)
    <div id="modal-{{ $booking->bookingID }}" class="fixed inset-0 z-[9999] hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm transition-opacity duration-300" 
             onclick="document.getElementById('modal-{{ $booking->bookingID }}').classList.add('hidden')"></div>

        <div class="flex items-center justify-center min-h-screen p-4 sm:p-6 pointer-events-none">
            <div class="relative bg-[#1a1a1a] border border-white/15 rounded-[2rem] max-w-2xl w-full shadow-2xl overflow-hidden transform transition-all duration-300 scale-95 opacity-0 animate-fade-in pointer-events-auto">
                {{-- Header --}}
                <div class="bg-white/7 p-6 border-b border-white/15 flex justify-between items-center">
                    <h3 class="text-xl font-black text-white tracking-tight">Booking Details #{{ $booking->bookingID }}</h3>
                    <button onclick="document.getElementById('modal-{{ $booking->bookingID }}').classList.add('hidden')" class="text-gray-400 hover:text-white transition-colors duration-200">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                {{-- Body --}}
                <div class="p-7 sm:p-8 space-y-7">
                @if($booking->bookingStatus == 'Rejected' && $booking->remarks)
                    <div class="bg-red-500/10 border border-red-500/50 rounded-2xl p-4 flex items-start gap-4">
                        <div class="bg-red-500 text-white rounded-full p-2 mt-1">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div>
                            <h4 class="text-red-400 font-bold uppercase text-xs tracking-wider mb-1">Booking Rejected</h4>
                            <p class="text-gray-300 text-sm">{{ $booking->remarks }}</p>
                        </div>
                    </div>
                @endif    

                {{-- NOTES / REFUND REMARKS (NEW SECTION) --}}
                @if($booking->bookingStatus != 'Rejected' && $booking->remarks)
                    <div class="bg-white/5 border border-white/10 rounded-2xl p-4 flex items-start gap-4">
                        <div class="bg-blue-500/20 text-blue-400 rounded-full p-2 mt-0.5 shrink-0">
                            <i class="fas fa-info"></i>
                        </div>
                        <div>
                            <h4 class="text-blue-300 font-bold uppercase text-xs tracking-wider mb-1">Notes / Remarks</h4>
                            <div class="text-gray-300 text-sm leading-relaxed whitespace-pre-line">
                                {{ $booking->remarks }}
                            </div>
                        </div>
                    </div>
                @endif
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-7">
                        {{-- LEFT COLUMN: Dates & Person In Charge --}}
                        <div class="space-y-6">
                            {{-- Dates Timeline --}}
                            <div class="relative pl-5 border-l-2 border-dashed border-white/25 space-y-5">
                                {{-- PICKUP SECTION --}}
                                <div>
                                    <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wider">Pickup</p>
                                    <div class="flex items-baseline gap-2">
                                        <p class="text-white font-black text-lg">{{ \Carbon\Carbon::parse($booking->originalDate)->format('d M Y') }}</p>
                                        {{-- ADDED TIME --}}
                                        <p class="text-orange-400 font-bold text-sm">
                                            {{ \Carbon\Carbon::parse($booking->bookingTime)->format('h:i A') }}
                                        </p>
                                    </div>
                                    <p class="text-sm text-gray-300 mt-1 flex items-center gap-1">
                                        <i class="fas fa-map-marker-alt text-xs text-gray-500"></i> {{ $booking->pickupLocation }}
                                    </p>
                                </div>

                                {{-- RETURN SECTION --}}
                                <div>
                                    <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wider">Return</p>
                                    <div class="flex items-baseline gap-2">
                                        <p class="text-white font-black text-lg">{{ \Carbon\Carbon::parse($booking->returnDate)->format('d M Y') }}</p>
                                        {{-- ADDED TIME --}}
                                        <p class="text-orange-400 font-bold text-sm">
                                            {{ \Carbon\Carbon::parse($booking->returnTime)->format('h:i A') }}
                                        </p>
                                    </div>
                                    <p class="text-sm text-gray-300 mt-1 flex items-center gap-1">
                                        <i class="fas fa-map-marker-alt text-xs text-gray-500"></i> {{ $booking->returnLocation }}
                                    </p>
                                </div>
                            </div>

                            {{-- PERSON IN CHARGE SECTION --}}
                            <div>
                                <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wider mb-2">Person In Charge</p>
                                @if($booking->staff)
                                    <div class="flex items-center gap-3 bg-white/5 p-3 rounded-xl border border-white/10">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center text-white font-bold text-sm shadow-lg">
                                            {{ substr($booking->staff->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="text-white font-bold text-sm leading-tight">{{ $booking->staff->name }}</p>
                                            @if($booking->staff->phoneNo)
                                                <a href="tel:{{ $booking->staff->phoneNo }}" class="text-xs text-orange-400 hover:text-orange-300 transition flex items-center mt-0.5">
                                                    <i class="fas fa-phone-alt mr-1.5"></i> {{ $booking->staff->phoneNo }}
                                                </a>
                                            @else
                                                <p class="text-xs text-gray-500 mt-0.5">Staff Member</p>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <div class="bg-white/5 p-3 rounded-xl border border-white/10 flex items-center gap-3">
                                         <div class="w-10 h-10 rounded-full bg-gray-700 flex items-center justify-center text-gray-400 text-sm">
                                            <i class="fas fa-user-clock"></i>
                                        </div>
                                        <div>
                                            <p class="text-gray-400 text-sm font-medium italic">Pending Assignment</p>
                                            <p class="text-[10px] text-gray-600">Our team will assign an agent shortly.</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        {{-- RIGHT COLUMN: Financials & Docs --}}
                        <div class="space-y-6">
                            {{-- Financials --}}
                            <div class="bg-white/8 rounded-2xl p-5 border border-white/10">
                                <p class="text-[10px] text-gray-400 uppercase font-bold mb-3 tracking-wider">Financial Summary</p>
                                <div class="flex justify-between text-gray-300 text-sm mb-3">
                                    <span>Total Rent</span>
                                    <span class="font-black text-white tracking-tight">RM {{ number_format($booking->totalCost, 2) }}</span>
                                </div>
                                @if($booking->penalties && $booking->penalties->count() > 0)
                                <div class="border-t border-white/15 pt-3 mt-2">
                                    @foreach($booking->penalties as $penalty)
                                    <div class="flex justify-between text-xs text-red-400 mb-1 last:mb-0">
                                        <span>
                                            @if($penalty->lateReturnHour > 0) Late ({{ $penalty->lateReturnHour }}h) @endif
                                            @if($penalty->fuelSurcharge > 0) + Fuel @endif
                                            @if($penalty->mileageSurcharge > 0) + Mileage @endif
                                        </span>
                                        <span>+ RM {{ number_format($penalty->penaltyFees + $penalty->fuelSurcharge + $penalty->mileageSurcharge, 2) }}</span>
                                    </div>
                                    @endforeach
                                </div>
                                @endif
                            </div>

                            {{-- Documents --}}
                            <div>
                                <p class="text-[10px] text-gray-400 uppercase font-bold mb-3 tracking-wider">Documents</p>
                                <div class="grid grid-cols-2 gap-3">
                                    <td>
                                        {{-- Agreement Link --}}
                                        @if(str_contains($booking->aggreementLink, 'drive.google.com'))
                                            <a href="{{ $booking->aggreementLink }}" target="_blank" class="flex items-center justify-center gap-2 p-3.5 bg-blue-500/10 rounded-xl border border-blue-500/20 hover:bg-blue-500 hover:text-white text-blue-300 text-sm transition-all duration-200">
                                                <i class="bi bi-file-earmark-pdf"></i> View Agreement
                                            </a>
                                        @elseif($booking->aggreementLink)
                                            {{-- Fallback for old local files --}}
                                            <a href="{{ route('book.agreement', $booking->bookingID) }}" target="_blank" class="flex items-center justify-center gap-2 p-3.5 bg-blue-500/10 rounded-xl border border-blue-500/20 hover:bg-blue-500 hover:text-white text-blue-300 text-sm transition-all duration-200">
                                                View Agreement
                                            </a>
                                        @endif

                                        {{-- Receipt Link (Assuming the first payment contains the receipt) --}}
                                        @php
                                            $receipt = $booking->payments->first(); // Get the initial payment
                                        @endphp

                                        @if($receipt && str_contains($receipt->installmentDetails, 'drive.google.com'))
                                            <a href="{{ $receipt->installmentDetails }}" target="_blank" class="flex items-center justify-center gap-2 p-3.5 bg-black/25 rounded-xl border border-dashed border-white/20 hover:border-orange-500 hover:text-orange-400 text-gray-300 text-sm transition-colors duration-200">
                                                <i class="bi bi-receipt"></i> View Receipt
                                            </a>
                                        @elseif($receipt && $receipt->installmentDetails)
                                            <a href="{{ asset('storage/' . $receipt->installmentDetails) }}" target="_blank" class="flex items-center justify-center gap-2 p-3.5 bg-black/25 rounded-xl border border-dashed border-white/20 hover:border-orange-500 hover:text-orange-400 text-gray-300 text-sm transition-colors duration-200">
                                                View Receipt
                                            </a>
                                        @endif
                                    </td>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if(in_array($booking->bookingStatus, ['Submitted', 'Deposit Paid', 'Paid', 'Confirmed']))
                <div class="bg-white/7 p-6 border-t border-white/15">
                    <form action="{{ route('book.cancel', $booking->bookingID) }}" method="POST" onsubmit="return confirm('Cancel booking?');" class="w-full">
                        @csrf
                        <button type="submit" class="w-full py-3.5 rounded-xl font-bold text-red-400 bg-red-500/10 border border-red-500/20 hover:bg-red-500 hover:text-white transition-all duration-200 flex items-center justify-center gap-2 group">
                            <i class="fas fa-ban group-hover:rotate-90"></i>
                            <span>Cancel Booking</span>
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>
    </div>
@endforeach

{{-- STYLES --}}
<style scoped>
@keyframes fade-in {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}
.animate-fade-in {
    animation: fade-in 0.25s ease-out forwards;
}
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

/* Star Rating Hover Effect */
.flex.flex-row-reverse input[type="radio"]:hover ~ label,
.flex.flex-row-reverse input[type="radio"]:checked ~ label {
    color: #eab308; /* Tailwind yellow-500 */
}
</style>

<script>
    function validateFiles(bookingID, requiredCount) {
        const input = document.getElementById('file-input-' + bookingID);
        const textSpan = document.getElementById('file-text-' + bookingID);
        const submitBtn = document.getElementById('submit-btn-' + bookingID);
        const fileCount = input.files.length;

        // Update text
        if (fileCount === 0) {
            textSpan.innerText = 'Tap to Select Photos';
            textSpan.classList.remove('text-green-400', 'text-red-400');
            textSpan.classList.add('text-gray-300');
        } else {
            textSpan.innerText = fileCount + ' files selected';
        }

        // Validate Count
        if (fileCount === requiredCount) {
            // Success State
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            
            textSpan.classList.remove('text-red-400', 'text-gray-300');
            textSpan.classList.add('text-green-400');
            textSpan.innerHTML = '<i class="fas fa-check-circle mr-1"></i> ' + fileCount + ' photos selected (Ready)';
        } else {
            // Error State
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
            
            textSpan.classList.remove('text-green-400', 'text-gray-300');
            textSpan.classList.add('text-red-400');
            textSpan.innerHTML = '<i class="fas fa-times-circle mr-1"></i> Selected ' + fileCount + ' (Need ' + requiredCount + ')';
        }
    }
</script>

<script>
    // --- CUSTOM DROPDOWN LOGIC ---
    function toggleDropdown() {
        const menu = document.getElementById('dropdownMenu');
        const arrow = document.getElementById('dropdownArrow');
        
        if (menu.classList.contains('hidden')) {
            // Open
            menu.classList.remove('hidden');
            menu.classList.add('animate-fade-in-down'); // Optional animation class
            arrow.style.transform = 'rotate(180deg)';
        } else {
            // Close
            menu.classList.add('hidden');
            arrow.style.transform = 'rotate(0deg)';
        }
    }

    function selectStatus(value) {
        // 1. Set hidden input value
        document.getElementById('statusInput').value = value;
        
        // 2. Submit form immediately
        document.getElementById('filterForm').submit();
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('customDropdown');
        const menu = document.getElementById('dropdownMenu');
        const arrow = document.getElementById('dropdownArrow');

        if (!dropdown.contains(event.target)) {
            menu.classList.add('hidden');
            if(arrow) arrow.style.transform = 'rotate(0deg)';
        }
    });
</script>
@endsection