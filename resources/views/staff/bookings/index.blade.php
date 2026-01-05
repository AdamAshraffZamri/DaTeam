@extends('layouts.staff')

@section('content')

<div class="min-h-screen bg-gray-100 rounded-2xl p-6">
    <div class="max-w-7xl mx-auto">

        {{-- HEADER WITH SEARCH AND FILTER --}}
        <div class="flex flex-col xl:flex-row justify-between items-end xl:items-center mb-8 gap-4">
            <div class="w-full xl:w-auto">
                <h1 class="text-3xl font-black text-gray-900">Booking Management</h1>
                <p class="text-gray-500 mt-1 text-sm">Monitor and manage all customer rentals.</p>
            </div>

            {{-- COMBINED FILTER FORM --}}
            <form action="{{ route('staff.bookings.index') }}" method="GET" id="filterForm" class="flex flex-col md:flex-row items-center gap-3 w-full xl:w-auto">
                
                {{-- SEARCH INPUT --}}
                <div class="relative group w-full md:w-64">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Search ID, Name, Plate..." 
                           class="w-full pl-10 pr-4 py-3.5 rounded-2xl border border-gray-200 bg-white text-sm font-bold text-gray-700 focus:ring-2 focus:ring-gray-900 focus:border-transparent transition-all shadow-sm group-hover:border-gray-300">
                    <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 group-hover:text-gray-600 transition-colors"></i>
                </div>

                {{-- STATUS DROPDOWN --}}
                @php
                    $currentStatus = request('status', 'all');
                    $statuses = [
                        'all'          => 'All Bookings',
                        'Submitted'    => 'Submitted', 
                        'Deposit Paid' => 'Deposit Paid',
                        'Paid'         => 'Paid (Full)',
                        'Confirmed'    => 'Confirmed',
                        'Active'       => 'Active',
                        'Completed'    => 'Completed',
                        'Cancelled'    => 'Cancelled',
                        'Rejected'     => 'Rejected'
                    ];
                    $currentLabel = $statuses[$currentStatus] ?? 'Select Status';
                @endphp

                <input type="hidden" name="status" id="statusInput" value="{{ $currentStatus }}">

                <div class="relative w-full md:w-[200px]" id="customDropdown">
                    {{-- TRIGGER --}}
                    <button type="button" onclick="toggleDropdown()" 
                        class="w-full flex items-center justify-between bg-white border border-gray-200 text-gray-700 text-xs font-bold py-3.5 px-5 rounded-2xl hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm group">
                        
                        <div class="flex items-center gap-2">
                            <i class="fas fa-filter text-orange-500"></i>
                            <span id="dropdownLabel">{{ $currentLabel }}</span>
                        </div>
                        <i class="fas fa-chevron-down text-[10px] text-gray-400 group-hover:text-gray-600 transition-transform duration-300" id="dropdownArrow"></i>
                    </button>

                    {{-- MENU --}}
                    <div id="dropdownMenu" 
                        class="absolute top-full right-0 mt-2 w-full bg-white border border-gray-100 rounded-2xl shadow-xl overflow-hidden hidden transform origin-top transition-all duration-200 z-50 max-h-[300px] overflow-y-auto">
                        
                        @foreach($statuses as $value => $label)
                        <div onclick="selectStatus('{{ $value }}')" 
                             class="px-5 py-3 text-xs font-bold cursor-pointer transition-colors flex items-center justify-between border-b border-gray-50 last:border-0
                             {{ $currentStatus == $value ? 'bg-orange-50 text-orange-600' : 'text-gray-600 hover:bg-gray-50' }}">
                            
                            <span>{{ $label }}</span>
                            @if($currentStatus == $value) <i class="fas fa-check"></i> @endif
                        </div>
                        @endforeach
                    </div>
                </div>

            </form>
        </div>

        {{-- BOOKING LIST --}}
        <div class="space-y-3" id="booking-list-container">
            @forelse($bookings as $booking)
            <div class="booking-row bg-white rounded-xl p-4 border border-gray-100 shadow-sm hover:shadow-md transition-all group cursor-pointer hover:border-gray-300 animate-fade-in" 
                 onclick="window.location='{{ route('staff.bookings.show', $booking->bookingID) }}'">
                
                <div class="flex flex-col lg:flex-row items-center">
                    
                    {{-- 1. NO & Customer --}}
                    <div class="flex items-center gap-4 w-full lg:w-[20%] shrink-0">
                        <div class="w-10 h-10 rounded-lg bg-gray-50 flex flex-col items-center justify-center border border-gray-200 shrink-0">
                            <span class="text-sm font-black text-gray-400 group-hover:text-gray-900 transition-colors">{{ $loop->iteration }}</span>
                        </div>
                        <div class="overflow-hidden">
                            <h4 class="text-sm font-bold text-gray-900 truncate" title="{{ $booking->customer->fullName ?? 'Guest' }}">
                                {{ $booking->customer->fullName ?? 'Guest' }}
                            </h4>
                            <p class="text-xs text-gray-400 truncate">{{ $booking->customer->email ?? 'No email' }}</p>
                        </div>
                    </div>

                    {{-- 2. Vehicle Info --}}
                    <div class="w-full lg:w-[18%] border-t lg:border-t-0 lg:border-l border-gray-100 pt-2 lg:pt-0 lg:pl-6 shrink-0">
                        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Vehicle</p>
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-bold text-gray-800 truncate">{{ $booking->vehicle->model ?? 'Unknown' }}</span>
                        </div>
                        <span class="text-[10px] font-mono font-black text-gray-600 bg-gray-50 px-1.5 py-0.5 rounded border border-gray-200 mt-0.5 inline-block uppercase">
                            {{ $booking->vehicle->plateNo ?? '-' }}
                        </span>
                    </div>

                    {{-- 3. Price --}}
                    <div class="w-full lg:w-[10%] border-t lg:border-t-0 lg:border-l border-gray-100 pt-2 lg:pt-0 lg:pl-6 shrink-0">
                        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Total</p>
                        <p class="text-sm font-black text-gray-900">RM {{ number_format($booking->totalCost, 2) }}</p>
                    </div>

                    {{-- 4. Docs --}}
                    <div class="w-full lg:w-[18%] border-t lg:border-t-0 lg:border-l border-gray-100 pt-2 lg:pt-0 lg:pl-6 lg:pr-6 shrink-0">
                        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-1">Documents</p>
                        <div class="flex flex-col gap-1 items-start">
                            @if($booking->payment && $booking->payment->installmentDetails)
                                <a href="{{ asset('storage/' . $booking->payment->installmentDetails) }}" target="_blank" onclick="event.stopPropagation()" class="text-[10px] font-bold text-blue-600 hover:text-blue-800 bg-blue-50 px-2 py-0.5 rounded border border-blue-100 flex items-center gap-1.5 transition-colors w-full">
                                    <i class="fas fa-receipt"></i> Receipt
                                </a>
                            @else
                                <span class="text-[10px] font-bold text-gray-300 px-2 py-0.5">-</span>
                            @endif
                        </div>
                    </div>

                    {{-- 5. Status --}}
                    <div class="w-full lg:w-[15%] border-t lg:border-t-0 lg:border-l border-gray-100 pt-2 lg:pt-0 lg:pl-10 shrink-0">
                        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-1">Status</p>
                        @php
                            $statusColor = match($booking->bookingStatus) {
                                'Submitted' => 'bg-orange-100 text-orange-700 border-orange-200',
                                'Confirmed' => 'bg-blue-100 text-blue-700 border-blue-200',
                                'Active'    => 'bg-purple-100 text-purple-700 border-purple-200',
                                'Completed' => 'bg-green-100 text-green-700 border-green-200',
                                'Cancelled' => 'bg-red-100 text-red-700 border-red-200',
                                'Rejected'  => 'bg-gray-100 text-gray-700 border-gray-200',
                                default     => 'bg-gray-50 text-gray-700 border-gray-100'
                            };
                        @endphp
                        <span class="block w-28 text-center px-0 py-1.5 rounded-full text-[10px] font-bold border uppercase tracking-wider {{ $statusColor }}">
                            {{ $booking->bookingStatus }}
                        </span>
                    </div>

                    {{-- 6. ACTION --}}
                    <div class="w-full lg:flex-1 flex justify-end items-center gap-2 pt-2 lg:pt-0 border-t lg:border-t-0 border-gray-100 lg:pl-6" onclick="event.stopPropagation()">
                         @if($booking->bookingStatus == 'Submitted')
                            @if(!$booking->payment || $booking->payment->paymentStatus !== 'Verified')
                                <form action="{{ route('staff.bookings.verify_payment', $booking->bookingID) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="bg-orange-100 hover:bg-orange-200 text-orange-700 px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-2 w-24 justify-center shadow-sm">
                                        <i class="fas fa-search-dollar"></i> <span>Verify</span>
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('staff.bookings.approve_agreement', $booking->bookingID) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="bg-green-100 hover:bg-green-200 text-green-700 px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-2 w-24 justify-center shadow-sm">
                                        <i class="fas fa-check"></i> <span>Approve</span>
                                    </button>
                                </form>
                            @endif
                        @elseif($booking->bookingStatus == 'Confirmed')
                            <a href="{{ route('staff.bookings.show', $booking->bookingID) }}" class="bg-blue-100 hover:bg-blue-200 text-blue-700 px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-2 w-24 justify-center shadow-sm">
                                <i class="fas fa-key"></i> <span>Handover</span>
                            </a>
                        @elseif($booking->bookingStatus == 'Active')
                            <a href="{{ route('staff.bookings.show', $booking->bookingID) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-2 w-24 justify-center shadow-sm">
                                <i class="fas fa-info-circle"></i> <span>Details</span>
                            </a>
                        @else
                            <div class="w-24"></div>
                        @endif
                    </div>

                </div>
            </div>
            @empty
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="bg-gray-50 rounded-full p-4 mb-4"><i class="fas fa-filter text-gray-300 text-3xl"></i></div>
                <p class="text-gray-500 font-medium">No bookings found.</p>
                @if(request('status') || request('search'))
                    <a href="{{ route('staff.bookings.index') }}" class="text-orange-500 font-bold text-xs mt-2 hover:underline">Clear Filters</a>
                @endif
            </div>
            @endforelse
        </div>

    </div>
</div>

<style>
@keyframes fade-in { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
.animate-fade-in { animation: fade-in 0.2s ease-out forwards; }
.animate-fade-in-down { animation: fade-in 0.15s ease-out forwards; }
</style>

<script>
    function toggleDropdown() {
        const menu = document.getElementById('dropdownMenu');
        const arrow = document.getElementById('dropdownArrow');
        
        if (menu.classList.contains('hidden')) {
            menu.classList.remove('hidden');
            menu.classList.add('animate-fade-in-down');
            if(arrow) arrow.style.transform = 'rotate(180deg)';
        } else {
            menu.classList.add('hidden');
            if(arrow) arrow.style.transform = 'rotate(0deg)';
        }
    }

    function selectStatus(value) {
        document.getElementById('statusInput').value = value;
        document.getElementById('filterForm').submit();
    }

    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('customDropdown');
        const menu = document.getElementById('dropdownMenu');
        const arrow = document.getElementById('dropdownArrow');

        if (dropdown && !dropdown.contains(event.target)) {
            menu.classList.add('hidden');
            if(arrow) arrow.style.transform = 'rotate(0deg)';
        }
    });
</script>
@endsection