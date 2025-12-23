@extends('layouts.app')

@section('content')
{{-- 1. FIXED BACKGROUND --}}
<div class="fixed inset-0 z-0">
    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('hastabg.png') }}');"></div>
    <div class="absolute inset-0 bg-gradient-to-b from-black/80 via-black/50 to-black/90"></div>
</div>

{{-- 2. MAIN CONTENT (CARDS ONLY) --}}
<div class="relative z-10 min-h-[calc(100vh-64px)] py-12">
    <div class="container mx-auto px-4 max-w-4xl">
        
        <div class="flex justify-between items-end mb-10">
            <div>
                <h1 class="text-4xl font-black text-white drop-shadow-[0_2px_4px_rgba(0,0,0,0.5)]">My Bookings</h1>
                <p class="text-gray-300 mt-2">Manage your active and past rentals.</p>
            </div>
            <a href="{{ route('book.create') }}" class="bg-[#ea580c] hover:bg-orange-600 text-white px-6 py-3 rounded-xl font-bold shadow-lg transition-all duration-200 transform hover:scale-105 active:scale-95">
                + New Booking
            </a>
        </div>

        <div class="space-y-7">
            {{-- LOOP 1: CARDS ONLY --}}
            @forelse($bookings as $booking)
                <div 
                    onclick="document.getElementById('modal-{{ $booking->bookingID }}').classList.remove('hidden')" 
                    class="group bg-black/50 backdrop-blur-[2px] border border-white/15 rounded-3xl p-6 shadow-xl hover:shadow-orange-500/10 hover:bg-white/15 transition-all duration-300 cursor-pointer relative overflow-hidden"
                >
                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/2 to-transparent opacity-0 group-hover:opacity-10 transition-opacity duration-500 pointer-events-none"></div>
                    
                    {{-- Card Header --}}
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Booking ID</span>
                            <p class="text-white font-black text-lg tracking-tight">#{{ $booking->bookingID }}</p>
                        </div>
                        <span class="px-3 py-1.5 rounded-full text-[11px] font-bold border uppercase tracking-wide
                            {{ $booking->bookingStatus == 'Submitted' 
                                ? 'bg-blue-500/15 text-blue-300 border-blue-500/30' 
                                : ($booking->bookingStatus == 'Cancelled' 
                                    ? 'bg-red-500/15 text-red-300 border-red-500/30' 
                                    : 'bg-emerald-500/15 text-emerald-300 border-emerald-500/30') }}">
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
                    <div class="text-[11px] text-gray-400 font-medium text-center mt-2 opacity-90">Click to view full details</div>
                </div>
            @empty
                <div class="text-center py-24 bg-white/8 backdrop-blur-sm rounded-3xl border border-white/15">
                    <div class="bg-white/10 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-5">
                        <i class="fas fa-calendar-times text-3xl text-gray-500"></i>
                    </div>
                    <h3 class="text-xl font-black text-white mb-2">No bookings found</h3>
                    <p class="text-gray-400 max-w-md mx-auto mb-7">You haven't rented any cars yet.</p>
                    <a href="{{ route('book.create') }}" class="inline-block px-6 py-2.5 text-orange-400 font-bold bg-orange-500/10 rounded-xl border border-orange-500/20 hover:bg-orange-500 hover:text-white transition-all duration-200">
                        Book a car now →
                    </a>
                </div>
            @endforelse
        </div>
    </div>
</div>

{{-- 3. MODALS (LOOP 2: COMPLETELY OUTSIDE MAIN CONTENT) --}}
@foreach($bookings as $booking)
    <div id="modal-{{ $booking->bookingID }}" class="fixed inset-0 z-[9999] hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-black/90 backdrop-blur-sm transition-opacity duration-300" 
             onclick="document.getElementById('modal-{{ $booking->bookingID }}').classList.add('hidden')"></div>

        {{-- Modal Panel --}}
        <div class="flex items-center justify-center min-h-screen p-4 sm:p-6 pointer-events-none">
            <div class="relative bg-[#1a1a1a] border border-white/15 rounded-[2rem] max-w-2xl w-full shadow-2xl overflow-hidden transform transition-all duration-300 scale-95 opacity-0 animate-fade-in pointer-events-auto">

                {{-- Modal Header --}}
                <div class="bg-white/7 p-6 border-b border-white/15 flex justify-between items-center">
                    <h3 class="text-xl font-black text-white tracking-tight">Booking Details #{{ $booking->bookingID }}</h3>
                    <button onclick="document.getElementById('modal-{{ $booking->bookingID }}').classList.add('hidden')" class="text-gray-400 hover:text-white transition-colors duration-200">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="p-7 sm:p-8 space-y-7">
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

                        {{-- Costs --}}
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
                                    <span>{{ Str::limit($penalty->reason, 18) }}</span>
                                    <span>+ RM {{ number_format($penalty->amount, 2) }}</span>
                                </div>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Documents & Receipt (MAINTAINED) --}}
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase font-bold mb-3 tracking-wider">Documents</p>
                        <div class="grid grid-cols-2 gap-3">
                            
                            {{-- View Contract --}}
                            <a href="{{ route('book.agreement', $booking->bookingID) }}" target="_blank" 
                               class="flex items-center justify-center gap-2 p-3.5 bg-blue-500/10 rounded-xl border border-blue-500/20 hover:bg-blue-500 hover:text-white text-blue-300 text-sm transition-all duration-200">
                                <i class="fas fa-file-contract"></i> 
                                <span>View Contract</span>
                            </a>

                            {{-- View Receipt --}}
                            @if($booking->payments && $booking->payments->count() > 0)
                                {{-- Link to the latest payment proof --}}
                                <a href="{{ asset('storage/'.$booking->payments->last()->installmentDetails) }}" target="_blank" 
                                   class="flex items-center justify-center gap-2 p-3.5 bg-black/25 rounded-xl border border-dashed border-white/20 hover:border-orange-500 hover:text-orange-400 text-gray-300 text-sm transition-colors duration-200">
                                    <i class="fas fa-receipt"></i> 
                                    <span>View Receipt</span>
                                </a>
                            @else
                                <div class="flex items-center justify-center gap-2 p-3.5 bg-red-500/10 rounded-xl border border-red-500/20 text-red-400 text-xs font-bold">
                                    <i class="fas fa-times-circle"></i> No Receipt
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Modal Footer (UPDATED: Cancel Only) --}}
                {{-- Show Cancel for: Submitted, Deposit Paid, Paid (Full), and Approved --}}
                @if(in_array($booking->bookingStatus, ['Submitted', 'Deposit Paid', 'Paid', 'Approved']))
                <div class="bg-white/7 p-6 border-t border-white/15">
                    <form action="{{ route('book.cancel', $booking->bookingID) }}" method="POST" onsubmit="return confirm('Cancel booking? If you have paid, you can claim a refund in the Finance Center.');" class="w-full">
                        @csrf
                        <button type="submit" class="w-full py-3.5 rounded-xl font-bold text-red-400 bg-red-500/10 border border-red-500/20 hover:bg-red-500 hover:text-white transition-all duration-200 flex items-center justify-center gap-2 group">
                            <i class="fas fa-ban group-hover:rotate-90 transition-transform"></i>
                            <span>Cancel Booking & Request Refund</span>
                        </button>
                    </form>
                    <p class="text-center text-[10px] text-gray-500 mt-3">
                        *For date changes, please cancel and re-book. Money will be refunded via Finance Center.
                    </p>
                </div>
                @endif

            </div>
        </div>
    </div>
@endforeach

<style scoped>
@keyframes fade-in {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}
.animate-fade-in {
    animation: fade-in 0.25s ease-out forwards;
}
</style>
@endsection