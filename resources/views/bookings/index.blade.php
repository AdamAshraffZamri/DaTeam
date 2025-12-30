@extends('layouts.app')

@section('content')
{{-- 1. FIXED BACKGROUND --}}
<div class="fixed inset-0 z-0">
    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('hastabg.png') }}');"></div>
    <div class="absolute inset-0 bg-gradient-to-b from-black/80 via-black/50 to-black/90"></div>
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

            {{-- SERVER-SIDE FILTER BAR (UPDATED) --}}
            {{-- We use a GET form so buttons act as links with parameters --}}
            <form action="{{ route('book.index') }}" method="GET" class="flex overflow-x-auto pb-1 md:pb-0 gap-1 no-scrollbar">
                
                @php
                    $currentStatus = request('status', 'all');
                    $statuses = [
                        'all' => 'All',
                        'Submitted' => 'Submitted', 
                        
                        'Approved' => 'Approved',
                        'Active' => 'Active',
                        'Completed' => 'Completed',
                        'Cancelled' => 'Cancelled'
                    ];
                @endphp

                @foreach($statuses as $value => $label)
                    <button type="submit" name="status" value="{{ $value }}" 
                        class="px-5 py-2 rounded-full text-xs font-bold border transition-all whitespace-nowrap 
                        {{ $currentStatus == $value ? 'bg-white text-black border-white' : 'border-white/20 text-gray-300 hover:bg-white/10' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </form>
        </div>

        <div class="space-y-7 min-h-[300px]">
            {{-- LOOP: CARDS --}}
            @forelse($bookings as $booking)
                <div class="group bg-black/50 backdrop-blur-[2px] border border-white/15 rounded-3xl p-6 shadow-xl hover:shadow-orange-500/10 hover:bg-white/15 transition-all duration-300 relative overflow-hidden animate-fade-in">
                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/2 to-transparent opacity-0 group-hover:opacity-10 transition-opacity duration-500 pointer-events-none"></div>
                    
                    {{-- Clickable Area for Details --}}
                    <div onclick="document.getElementById('modal-{{ $booking->bookingID }}').classList.remove('hidden')" class="cursor-pointer">
                        {{-- Card Header --}}
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex items-center gap-3">
                                {{-- Numbering Badge (Dynamic) --}}
                                <div class="bg-orange-500 text-white font-bold w-8 h-8 rounded-full flex items-center justify-center text-sm shadow-lg">
                                    {{ $loop->iteration }}
                                </div>
                                <!-- <div>
                                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Booking ID</span>
                                    <p class="text-white font-black text-lg">#{{ $booking->bookingID }}</p>
                                </div> -->
                            </div>

                            {{-- Status Badge --}}
                            <span class="px-3 py-1.5 rounded-full text-[11px] font-bold border uppercase tracking-wide
                                {{ $booking->bookingStatus == 'Submitted' ? 'bg-blue-500/15 text-blue-300 border-blue-500/30' : 
                                  ($booking->bookingStatus == 'Cancelled' ? 'bg-red-500/15 text-red-300 border-red-500/30' : 
                                  'bg-emerald-500/15 text-emerald-300 border-emerald-500/30') }}">
                                {{ $booking->bookingStatus }}
                            </span>
                        </div>

                        {{-- Vehicle Info --}}
                        <div class="flex items-center space-x-4 mb-4">
                            <div class="h-12 w-12 rounded-xl bg-black/30 flex items-center justify-center border border-white/10">
                                <i class="fas fa-car text-xl text-gray-300 group-hover:text-orange-400 transition-colors duration-200"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-black text-white leading-tight">{{ $booking->vehicle->model }}</h3>
                                <p class="text-xs text-orange-400 font-bold mt-1 tracking-wide">{{ $booking->vehicle->plateNo }}</p>
                            </div>
                        </div>
                        <div class="text-[11px] text-gray-400 font-medium text-center mt-2 opacity-90 mb-4">Click to view full details</div>
                    </div>

                    {{-- ACTIONS: INSPECTION & FEEDBACK --}}
                    
                    {{-- 1. INSPECTION BUTTON (Confirmed/Active Only) --}}
                    @if($booking->bookingStatus == 'Confirmed' || $booking->bookingStatus == 'Active')
                        <div class="border-t border-white/10 pt-4 mt-2">
                            <button onclick="document.getElementById('inspection-modal-{{ $booking->bookingID }}').classList.remove('hidden')" 
                                    class="w-full bg-[#ea580c] hover:bg-orange-600 text-white py-3 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 shadow-lg">
                                <i class="fas fa-camera"></i> 
                                {{ $booking->bookingStatus == 'Confirmed' ? 'Upload Pickup Photos' : 'Upload Return Photos' }}
                            </button>
                        </div>
                    @endif

                    {{-- 2. FEEDBACK BUTTON (Completed Only & No Prior Feedback) --}}
                    @if($booking->bookingStatus == 'Completed' && !$booking->feedback)
                        <div class="border-t border-white/10 pt-4 mt-2">
                            <button onclick="document.getElementById('feedback-modal-{{ $booking->bookingID }}').classList.remove('hidden')" 
                                    class="w-full bg-yellow-500 hover:bg-yellow-600 text-black py-3 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 shadow-lg">
                                <i class="fas fa-star"></i> Leave Feedback
                            </button>
                        </div>
                    @endif

                </div>
            @empty
                {{-- Empty State (Handled by Server Logic Now) --}}
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
    <div id="inspection-modal-{{ $booking->bookingID }}" class="fixed inset-0 z-[10000] hidden bg-black/90 backdrop-blur-md flex items-center justify-center p-4">
        <div class="bg-[#1a1a1a] border border-white/15 rounded-2xl w-full max-w-md p-6 relative shadow-2xl">
            <button onclick="document.getElementById('inspection-modal-{{ $booking->bookingID }}').classList.add('hidden')" class="absolute top-4 right-4 text-gray-400 hover:text-white">
                <i class="fas fa-times text-xl"></i>
            </button>
            <h3 class="text-xl font-bold text-white mb-2">
                {{ $booking->bookingStatus == 'Confirmed' ? 'Pre-Rental Inspection' : 'Post-Rental Inspection' }}
            </h3>
            <p class="text-xs text-gray-400 mb-6">Upload clear photos of the vehicle to document its condition.</p>
            <form action="{{ route('book.inspection.upload', $booking->bookingID) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <label class="block w-full h-32 border-2 border-dashed border-white/20 rounded-xl flex flex-col items-center justify-center cursor-pointer hover:border-orange-500 hover:bg-white/5 transition mb-4 group">
                    <i class="fas fa-images text-2xl text-gray-500 group-hover:text-orange-500 mb-2 transition"></i>
                    <span class="text-sm text-gray-300 group-hover:text-white">Tap to Select Photos</span>
                    <span class="text-[10px] text-gray-500 uppercase mt-1">(Max 4 photos)</span>
                    <input type="file" name="photos[]" multiple class="hidden" required onchange="this.parentElement.querySelector('span').innerText = this.files.length + ' files selected'">
                </label>
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="text-[10px] text-gray-500 uppercase font-bold">Fuel Level</label>
                        <select name="fuel_level" class="w-full bg-white/10 border border-white/20 rounded-lg text-white text-sm p-2 mt-1 focus:border-orange-500 focus:outline-none">
                            <option value="Full">Full</option>
                            <option value="3/4">3/4</option>
                            <option value="1/2">1/2</option>
                            <option value="1/4">1/4</option>
                            <option value="E">Empty</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] text-gray-500 uppercase font-bold">Mileage (km)</label>
                        <input type="number" name="mileage" class="w-full bg-white/10 border border-white/20 rounded-lg text-white text-sm p-2 mt-1 focus:border-orange-500 focus:outline-none" placeholder="e.g. 12345">
                    </div>
                </div>
                <button type="submit" class="w-full bg-[#ea580c] hover:bg-orange-600 text-white font-bold py-3.5 rounded-xl shadow-lg transition transform hover:scale-[1.02]">
                    Submit Inspection
                </button>
            </form>
        </div>
    </div>
    @endif
@endforeach

{{-- 4. FEEDBACK MODALS (LOOP 3) --}}
@foreach($bookings as $booking)
    @if($booking->bookingStatus == 'Completed' && !$booking->feedback)
    <div id="feedback-modal-{{ $booking->bookingID }}" class="fixed inset-0 z-[10000] hidden bg-black/90 backdrop-blur-md flex items-center justify-center p-4">
        <div class="bg-[#1a1a1a] border border-white/15 rounded-2xl w-full max-w-md p-6 relative shadow-2xl">
            <button onclick="document.getElementById('feedback-modal-{{ $booking->bookingID }}').classList.add('hidden')" class="absolute top-4 right-4 text-gray-400 hover:text-white">
                <i class="fas fa-times text-xl"></i>
            </button>
            <h3 class="text-xl font-bold text-white mb-2">Rate Your Experience</h3>
            <p class="text-xs text-gray-400 mb-6">How was your ride with the {{ $booking->vehicle->model }}?</p>
            <form action="{{ route('feedback.store', $booking->bookingID) }}" method="POST">
                @csrf
                <div class="flex flex-row-reverse justify-center gap-2 mb-6 group">
                    @for($i=5; $i>=1; $i--)
                        <input type="radio" id="star{{ $i }}-{{ $booking->bookingID }}" name="rating" value="{{ $i }}" class="hidden peer" required>
                        <label for="star{{ $i }}-{{ $booking->bookingID }}" class="text-3xl text-gray-600 cursor-pointer peer-checked:text-yellow-400 peer-hover:text-yellow-400 hover:text-yellow-400 transition">
                            <i class="fas fa-star"></i>
                        </label>
                    @endfor
                </div>
                <div class="mb-6">
                    <label class="text-[10px] text-gray-500 uppercase font-bold mb-2 block">Comment (Optional)</label>
                    <textarea name="comment" rows="3" class="w-full bg-white/10 border border-white/20 rounded-xl text-white text-sm p-3 focus:border-yellow-500 focus:outline-none placeholder-gray-500" placeholder="Share your feedback..."></textarea>
                </div>
                <button type="submit" class="w-full bg-yellow-500 hover:bg-yellow-600 text-black font-bold py-3.5 rounded-xl shadow-lg transition transform hover:scale-[1.02]">
                    Submit Feedback
                </button>
            </form>
        </div>
    </div>
    @endif
@endforeach

{{-- 5. DETAILS MODALS (LOOP 4) --}}
@foreach($bookings as $booking)
    <div id="modal-{{ $booking->bookingID }}" class="fixed inset-0 z-[9999] hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-black/90 backdrop-blur-sm transition-opacity duration-300" 
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
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-7">
                        {{-- Dates --}}
                        <div class="space-y-5">
                            <div class="relative pl-5 border-l-2 border-dashed border-white/25 space-y-5">
                                <div>
                                    <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wider">Pickup</p>
                                    <p class="text-white font-black text-lg">{{ \Carbon\Carbon::parse($booking->originalDate)->format('d M Y') }}</p>
                                    <p class="text-sm text-gray-300 mt-1">{{ $booking->pickupLocation }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wider">Return</p>
                                    <p class="text-white font-black text-lg">{{ \Carbon\Carbon::parse($booking->returnDate)->format('d M Y') }}</p>
                                    <p class="text-sm text-gray-300 mt-1">{{ $booking->returnLocation }}</p>
                                </div>
                            </div>
                        </div>
                        
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
                    </div>

                    {{-- Documents --}}
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase font-bold mb-3 tracking-wider">Documents</p>
                        <div class="grid grid-cols-2 gap-3">
                            <a href="{{ asset('storage/' . $booking->aggreementLink) }}" target="_blank" class="flex items-center justify-center gap-2 p-3.5 bg-blue-500/10 rounded-xl border border-blue-500/20 hover:bg-blue-500 hover:text-white text-blue-300 text-sm transition-all duration-200">
                                <i class="fas fa-file-signature"></i> <span>View Agreement</span>
                            </a>
                            @if($booking->payments && $booking->payments->count() > 0)
                                <a href="{{ asset('storage/'.$booking->payments->last()->installmentDetails) }}" target="_blank" class="flex items-center justify-center gap-2 p-3.5 bg-black/25 rounded-xl border border-dashed border-white/20 hover:border-orange-500 hover:text-orange-400 text-gray-300 text-sm transition-colors duration-200">
                                    <i class="fas fa-receipt"></i> <span>View Receipt</span>
                                </a>
                            @else
                                <div class="flex items-center justify-center gap-2 p-3.5 bg-red-500/10 rounded-xl border border-red-500/20 text-red-400 text-xs font-bold">
                                    <i class="fas fa-times-circle"></i> No Receipt
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                @if(in_array($booking->bookingStatus, ['Submitted', 'Deposit Paid', 'Paid', 'Approved']))
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

{{-- Note: JS Logic for filtering removed as it is now handled by the server --}}
@endsection