<a href="{{ route('staff.bookings.show', $booking->bookingID) }}" class="flex items-center justify-between p-4 hover:bg-gray-50 transition-colors group">
    <div class="flex items-center gap-4">
        {{-- Status Icon --}}
        <div class="w-10 h-10 rounded-xl flex items-center justify-center 
            {{ $type == 'return' ? 'bg-orange-100 text-orange-600' : ($type == 'pickup' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-500') }}">
            <i class="fas {{ $type == 'return' ? 'fa-undo' : ($type == 'pickup' ? 'fa-key' : 'fa-history') }}"></i>
        </div>
        
        <div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-black text-gray-900">#{{ $booking->bookingID }}</span>
                <span class="text-[10px] font-bold text-gray-500 uppercase">{{ $booking->vehicle->plateNo ?? '---' }}</span>
            </div>
            <p class="text-xs text-gray-500 font-medium mt-0.5">
                {{ $booking->customer->fullName ?? 'Guest' }}
            </p>
        </div>
    </div>

    <div class="text-right">
        @if($type == 'pickup')
            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">Pickup At</p>
            <p class="text-xs font-bold text-gray-900">
                {{ \Carbon\Carbon::parse($booking->bookingTime)->format('h:i A') }}
            </p>
        @elseif($type == 'return')
            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">Due At</p>
            <p class="text-xs font-bold {{ \Carbon\Carbon::parse($booking->returnDate . ' ' . $booking->returnTime)->isPast() ? 'text-red-600' : 'text-gray-900' }}">
                {{ \Carbon\Carbon::parse($booking->returnTime)->format('h:i A') }}
            </p>
        @else
            <span class="px-2 py-1 rounded text-[9px] font-bold uppercase tracking-wider border
                {{ $booking->bookingStatus == 'Completed' ? 'bg-green-50 text-green-600 border-green-100' : 'bg-gray-50 text-gray-500 border-gray-100' }}">
                {{ $booking->bookingStatus }}
            </span>
        @endif
    </div>
</a>