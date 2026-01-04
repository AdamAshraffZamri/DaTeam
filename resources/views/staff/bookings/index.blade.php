@extends('layouts.staff')

@section('content')
{{-- PRE-CALCULATE COUNTS --}}
@php
    $rejectedCount = $bookings->where('bookingStatus', 'Rejected')->count();
    $completedRealCount = $bookings->where('bookingStatus', 'Completed')->count();
    
    $counts = [
        'all'       => $bookings->count(),
        'submitted' => $bookings->where('bookingStatus', 'Submitted')->count(),
        'confirmed' => $bookings->where('bookingStatus', 'Confirmed')->count(),
        'active'    => $bookings->where('bookingStatus', 'Active')->count(),
        'completed' => $completedRealCount + $rejectedCount, 
        'cancelled' => $bookings->where('bookingStatus', 'Cancelled')->count(),
        'refunds'   => $bookings->filter(fn($b) => $b->bookingStatus == 'Cancelled' && optional($b->payment)->depoStatus == 'Requested')->count()
    ];
@endphp

<div class="min-h-screen bg-gray-100 rounded-2xl p-6">
    <div class="max-w-7xl mx-auto">

        {{-- HEADER --}}
        <div class="flex flex-col md:flex-row justify-between items-end mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-black text-gray-900">Booking Management</h1>
                <p class="text-gray-500 mt-1 text-sm">Monitor and manage all customer rentals.</p>
            </div>

            {{-- FILTER BAR --}}
            <div class="flex overflow-x-auto pb-2 md:pb-0 gap-2 no-scrollbar p-1">
                @foreach(['All', 'Submitted', 'Confirmed', 'Active', 'Completed', 'Cancelled'] as $filter)
                    @php $key = strtolower($filter); @endphp
                    <button onclick="filterBookings('{{ $filter }}')" data-filter="{{ $filter }}" 
                        class="filter-btn {{ $filter == 'All' ? 'active-filter bg-gray-900 text-white shadow-lg shadow-gray-900/20 border-transparent' : 'bg-white text-gray-500 border-gray-200 hover:bg-gray-50' }} px-5 py-2.5 rounded-full text-xs font-bold border transition-all whitespace-nowrap shadow-sm flex items-center gap-2 group shrink-0">
                        {{ $filter }} 
                        <span class="{{ $filter == 'All' ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-400' }} px-1.5 py-0.5 rounded-full text-[10px] transition-colors">
                            {{ $counts[$key] ?? 0 }}
                        </span>
                    </button>
                @endforeach
            </div>
        </div>

        {{-- STATS GRID (ORANGE-50) --}}
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-8">
            <div class="bg-orange-50 p-4 rounded-2xl border border-orange-100 shadow-sm flex flex-col justify-center h-24 relative overflow-hidden group transition-all">
                <div class="flex justify-between items-center z-10">
                    <div><p class="text-[10px] font-bold text-orange-800/60 uppercase tracking-widest mb-1">Total</p><h3 class="text-2xl font-black text-gray-900">{{ $counts['all'] }}</h3></div>
                    <div class="w-10 h-10 rounded-xl bg-white/60 flex items-center justify-center text-orange-600"><i class="fas fa-folder"></i></div>
                </div>
            </div>
            <div class="bg-orange-50 p-4 rounded-2xl border border-orange-100 shadow-sm flex flex-col justify-center h-24 relative overflow-hidden group transition-all">
                <div class="flex justify-between items-center z-10">
                    <div><p class="text-[10px] font-bold text-orange-800/60 uppercase tracking-widest mb-1">Active</p><h3 class="text-2xl font-black text-gray-900">{{ $counts['active'] }}</h3></div>
                    <div class="w-10 h-10 rounded-xl bg-white/60 flex items-center justify-center text-orange-600"><i class="fas fa-road"></i></div>
                </div>
            </div>
            <div class="bg-orange-50 p-4 rounded-2xl border border-orange-100 shadow-sm flex flex-col justify-center h-24 relative overflow-hidden group transition-all">
                <div class="flex justify-between items-center z-10">
                    <div><p class="text-[10px] font-bold text-orange-800/60 uppercase tracking-widest mb-1">Pending</p><h3 class="text-2xl font-black text-orange-600">{{ $counts['submitted'] }}</h3></div>
                    <div class="w-10 h-10 rounded-xl bg-white/60 flex items-center justify-center text-orange-600 relative">
                        <i class="fas fa-bell"></i>
                        @if($counts['submitted'] > 0) <span class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full animate-ping"></span> @endif
                    </div>
                </div>
            </div>
            <div class="bg-orange-50 p-4 rounded-2xl border border-orange-100 shadow-sm flex flex-col justify-center h-24 relative overflow-hidden group transition-all">
                <div class="flex justify-between items-center z-10">
                    <div><p class="text-[10px] font-bold text-orange-800/60 uppercase tracking-widest mb-1">Finished</p><h3 class="text-2xl font-black text-gray-900">{{ $counts['completed'] }}</h3></div>
                    <div class="w-10 h-10 rounded-xl bg-white/60 flex items-center justify-center text-orange-600"><i class="fas fa-check-circle"></i></div>
                </div>
            </div>
            <div class="bg-orange-50 p-4 rounded-2xl border border-orange-100 shadow-sm flex flex-col justify-center h-24 relative overflow-hidden group transition-all">
                <div class="flex justify-between items-center z-10">
                    <div><p class="text-[10px] font-bold text-orange-800/60 uppercase tracking-widest mb-1">Refunds</p><h3 class="text-2xl font-black text-red-500">{{ $counts['refunds'] }}</h3></div>
                    <div class="w-10 h-10 rounded-xl bg-white/60 flex items-center justify-center text-red-500"><i class="fas fa-hand-holding-usd"></i></div>
                </div>
            </div>
        </div>

        {{-- BOOKING LIST --}}
        <div class="space-y-3" id="booking-list-container">
            @foreach($bookings as $booking)
            <div class="booking-row bg-white rounded-xl p-4 border border-gray-100 shadow-sm hover:shadow-md transition-all group cursor-pointer hover:border-gray-300" 
                 data-status="{{ $booking->bookingStatus }}"
                 onclick="window.location='{{ route('staff.bookings.show', $booking->bookingID) }}'">
                
                <div class="flex flex-col lg:flex-row items-center">
                    
                    {{-- 1. NO & Customer (20%) --}}
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

                    {{-- 2. Vehicle Info (18%) --}}
                    <div class="w-full lg:w-[18%] border-t lg:border-t-0 lg:border-l border-gray-100 pt-2 lg:pt-0 lg:pl-6 shrink-0">
                        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Vehicle</p>
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-bold text-gray-800 truncate">{{ $booking->vehicle->model ?? 'Unknown' }}</span>
                        </div>
                        <span class="text-[10px] font-mono font-black text-gray-600 bg-gray-50 px-1.5 py-0.5 rounded border border-gray-200 mt-0.5 inline-block uppercase">
                            {{ $booking->vehicle->plateNo ?? '-' }}
                        </span>
                    </div>

                    {{-- 3. Price (10%) - ALONE --}}
                    <div class="w-full lg:w-[10%] border-t lg:border-t-0 lg:border-l border-gray-100 pt-2 lg:pt-0 lg:pl-6 shrink-0">
                        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Total</p>
                        <p class="text-sm font-black text-gray-900">RM {{ number_format($booking->totalCost, 2) }}</p>
                    </div>

                    {{-- 4. Docs (15%) - MOVED RIGHT & STACKED --}}
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

                            @if($booking->aggreementLink)
                                <a href="{{ asset('storage/' . $booking->aggreementLink) }}" target="_blank" onclick="event.stopPropagation()" class="text-[10px] font-bold text-purple-600 hover:text-purple-800 bg-purple-50 px-2 py-0.5 rounded border border-purple-100 flex items-center gap-1.5 transition-colors w-full">
                                    <i class="fas fa-file-contract"></i> Agreement
                                </a>
                            @else
                                <span class="text-[10px] font-bold text-gray-300 px-2 py-0.5">-</span>
                            @endif
                        </div>
                    </div>

                    {{-- 5. Status (15%) - SEPARATED & MOVED RIGHT --}}
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

                    {{-- 6. ACTION (Remaining Space) --}}
                    <div class="w-full lg:flex-1 flex justify-end items-center gap-2 pt-2 lg:pt-0 border-t lg:border-t-0 border-gray-100 lg:pl-6" onclick="event.stopPropagation()">
                        
                        {{-- SUBMITTED --}}
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

                        {{-- CONFIRMED --}}
                        @elseif($booking->bookingStatus == 'Confirmed')
                            <a href="{{ route('staff.bookings.show', $booking->bookingID) }}" class="bg-blue-100 hover:bg-blue-200 text-blue-700 px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-2 w-24 justify-center shadow-sm">
                                <i class="fas fa-key"></i> <span>Handover</span>
                            </a>

                        {{-- ACTIVE --}}
                        @elseif($booking->bookingStatus == 'Active')
                            <a href="{{ route('staff.bookings.show', $booking->bookingID) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-2 w-24 justify-center shadow-sm">
                                <i class="fas fa-info-circle"></i> <span>Details</span>
                            </a>

                        {{-- COMPLETED --}}
                        @elseif($booking->bookingStatus == 'Completed')
                            <div class="flex items-center gap-0.5 justify-end px-2">
                                @for($i=0; $i<5; $i++) <i class="fas fa-star text-sm text-yellow-400 drop-shadow-sm"></i> @endfor
                            </div>

                        {{-- REJECTED --}}
                        @elseif($booking->bookingStatus == 'Rejected')
                            <div class="text-right">
                                <div class="bg-red-50 border border-red-100 px-3 py-2 rounded-lg max-w-[120px] inline-block text-left shadow-sm">
                                    <p class="text-[9px] font-bold text-red-400 uppercase mb-0.5">Reason</p>
                                    <p class="text-xs font-bold text-red-700 leading-tight line-clamp-2" title="{{ $booking->remarks }}">{{ $booking->remarks ?? '-' }}</p>
                                </div>
                            </div>

                        {{-- CANCELLED --}}
                        @elseif($booking->bookingStatus == 'Cancelled')
                            @if($booking->payment && $booking->payment->depoStatus == 'Requested')
                                <form action="{{ route('staff.bookings.refund', $booking->bookingID) }}" method="POST" onsubmit="return confirm('Refund deposit?');">
                                    @csrf
                                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-xs font-bold transition shadow-md shadow-red-500/20 flex items-center gap-2 w-24 justify-center">
                                        <i class="fas fa-hand-holding-usd"></i> <span>Refund</span>
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('staff.bookings.show', $booking->bookingID) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-2 w-24 justify-center">
                                    <i class="fas fa-file-alt"></i> <span>Details</span>
                                </a>
                            @endif
                        @endif

                    </div>

                </div>
            </div>
            @endforeach
        </div>

        {{-- Empty State --}}
        <div id="empty-state" class="hidden flex-col items-center justify-center py-20 text-center">
            <div class="bg-gray-50 rounded-full p-4 mb-4"><i class="fas fa-filter text-gray-300 text-3xl"></i></div>
            <p class="text-gray-500 font-medium">No bookings found in this category.</p>
        </div>

    </div>
</div>

<script>
    function filterBookings(status) {
        const buttons = document.querySelectorAll('.filter-btn');
        buttons.forEach(btn => {
            btn.classList.remove('bg-gray-900', 'text-white', 'shadow-lg', 'shadow-gray-900/20', 'border-transparent', 'active-filter');
            btn.classList.add('bg-white', 'text-gray-500', 'border-gray-200', 'hover:bg-gray-50');
            
            const pill = btn.querySelector('span');
            pill.classList.remove('bg-white/20', 'text-white');
            pill.classList.add('bg-gray-100', 'text-gray-400');

            if (btn.dataset.filter === status) {
                btn.classList.remove('bg-white', 'text-gray-500', 'border-gray-200', 'hover:bg-gray-50');
                btn.classList.add('bg-gray-900', 'text-white', 'shadow-lg', 'shadow-gray-900/20', 'border-transparent', 'active-filter');
                pill.classList.remove('bg-gray-100', 'text-gray-400');
                pill.classList.add('bg-white/20', 'text-white');
            }
        });

        const rows = document.querySelectorAll('.booking-row');
        let visibleCount = 0;
        rows.forEach(row => {
            const rowStatus = row.getAttribute('data-status');
            let show = false;
            if (status === 'All') {
                show = true;
            } else if (status === 'Completed') {
                if (rowStatus === 'Completed' || rowStatus === 'Rejected') show = true;
            } else {
                if (rowStatus === status) show = true;
            }
            if (show) {
                row.style.display = 'block'; 
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const emptyState = document.getElementById('empty-state');
        if (visibleCount === 0) {
            emptyState.classList.remove('hidden');
            emptyState.classList.add('flex');
        } else {
            emptyState.classList.add('hidden');
            emptyState.classList.remove('flex');
        }
    }
</script>

<style>
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endsection