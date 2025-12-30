@extends('layouts.staff')

@section('content')
<div class="min-h-screen bg-gray-100 p-6">
    <div class="max-w-7xl mx-auto">

        <div class="flex flex-col md:flex-row justify-between items-end mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-black text-gray-900">Booking Management</h1>
                <p class="text-gray-500 mt-1">Monitor and manage all customer rentals.</p>
            </div>

            {{-- FILTER BAR --}}
            <div class="flex overflow-x-auto pb-2 md:pb-0 gap-2 no-scrollbar">
                <button onclick="filterTable('all')" class="filter-btn active-filter bg-gray-900 text-white px-5 py-2 rounded-full text-xs font-bold border border-transparent transition-all whitespace-nowrap shadow-lg shadow-gray-900/20">
                    All
                </button>
                <button onclick="filterTable('Submitted')" class="filter-btn bg-white text-gray-500 px-5 py-2 rounded-full text-xs font-bold border border-gray-200 hover:bg-gray-50 transition-all whitespace-nowrap">
                    Submitted
                </button>
                <button onclick="filterTable('Confirmed')" class="filter-btn bg-white text-gray-500 px-5 py-2 rounded-full text-xs font-bold border border-gray-200 hover:bg-gray-50 transition-all whitespace-nowrap">
                    Confirmed
                </button>
                <button onclick="filterTable('Active')" class="filter-btn bg-white text-gray-500 px-5 py-2 rounded-full text-xs font-bold border border-gray-200 hover:bg-gray-50 transition-all whitespace-nowrap">
                    Active
                </button>
                <button onclick="filterTable('Completed')" class="filter-btn bg-white text-gray-500 px-5 py-2 rounded-full text-xs font-bold border border-gray-200 hover:bg-gray-50 transition-all whitespace-nowrap">
                    Completed
                </button>
                <button onclick="filterTable('Cancelled')" class="filter-btn bg-white text-gray-500 px-5 py-2 rounded-full text-xs font-bold border border-gray-200 hover:bg-gray-50 transition-all whitespace-nowrap">
                    Cancelled
                </button>
            </div>
        </div>

        {{-- STATS GRID --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
                <h3 class="text-2xl font-bold text-gray-800">{{ $bookings->count() }}</h3>
                <p class="text-sm text-gray-400">Total Bookings</p>
            </div>
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
                <h3 class="text-2xl font-bold text-orange-500">{{ $bookings->where('bookingStatus', 'Submitted')->count() }}</h3>
                <p class="text-sm text-gray-400">Pending Approval</p>
            </div>
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
                <h3 class="text-2xl font-bold text-red-500">
                    {{ $bookings->where('bookingStatus', 'Cancelled')->where('payment.depoStatus', 'Requested')->count() }}
                </h3>
                <p class="text-sm text-gray-400">Refund Requests</p>
            </div>
        </div>

        {{-- TABLE CONTAINER --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden min-h-[400px]">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <h3 class="font-bold text-gray-800">Booking List</h3>
                <span id="showing-count" class="text-xs font-bold text-gray-400 uppercase tracking-wider">{{ $bookings->count() }} Records</span>
            </div>

            <table class="w-full text-left">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-6 py-4">ID</th>
                        <th class="px-6 py-4">Customer</th>
                        <th class="px-6 py-4">Vehicle</th>
                        <th class="px-6 py-4">Documents</th> 
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100" id="booking-table-body">
                    @foreach($bookings as $booking)
                    <tr class="booking-row hover:bg-gray-50 transition-colors" data-status="{{ $booking->bookingStatus }}">
                        <td class="px-6 py-4 font-mono text-sm text-gray-500">#{{ $booking->bookingID }}</td>
                        
                        <td class="px-6 py-4">
                            <p class="font-bold text-gray-900">{{ $booking->customer->fullName ?? 'Guest' }}</p>
                            <p class="text-xs text-gray-400">{{ $booking->customer->email ?? '' }}</p>
                        </td>
                        
                        <td class="px-6 py-4 text-sm">
                            <p class="font-bold text-gray-800">{{ $booking->vehicle->model ?? 'Unknown' }}</p>
                            <span class="text-gray-400 text-xs uppercase tracking-wide">{{ $booking->vehicle->plateNo ?? '-' }}</span>
                        </td>

                        {{-- DOCUMENTS COLUMN --}}
                        <td class="px-6 py-4 text-sm space-y-1">
                            {{-- 1. Receipt --}}
                            @if($booking->payment && $booking->payment->installmentDetails)
                                <a href="{{ asset('storage/' . $booking->payment->installmentDetails) }}" target="_blank" class="flex items-center text-blue-600 hover:text-blue-800 text-xs font-bold group">
                                    <i class="fas fa-receipt mr-1.5 opacity-70 group-hover:opacity-100"></i> Receipt
                                </a>
                            @else
                                <span class="text-gray-400 text-xs flex items-center"><i class="fas fa-times mr-1"></i> No Receipt</span>
                            @endif

                            {{-- 2. Agreement --}}
                            @if($booking->aggreementLink)
                                <a href="{{ asset('storage/' . $booking->aggreementLink) }}" target="_blank" class="flex items-center text-purple-600 hover:text-purple-800 text-xs font-bold group">
                                    <i class="fas fa-file-contract mr-1.5 opacity-70 group-hover:opacity-100"></i> Agreement
                                </a>
                            @else
                                <span class="text-gray-400 text-xs flex items-center"><i class="fas fa-times mr-1"></i> No Agreement</span>
                            @endif
                        </td>

                        <td class="px-6 py-4">
                            @php
                                $statusColor = match($booking->bookingStatus) {
                                    'Submitted' => 'bg-orange-100 text-orange-700 border-orange-200',
                                    'Confirmed' => 'bg-blue-100 text-blue-700 border-blue-200',
                                    'Active'    => 'bg-purple-100 text-purple-700 border-purple-200',
                                    'Completed' => 'bg-green-100 text-green-700 border-green-200',
                                    'Cancelled' => 'bg-red-100 text-red-700 border-red-200',
                                    default     => 'bg-gray-100 text-gray-700 border-gray-200'
                                };
                            @endphp
                            <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $statusColor }}">
                                {{ $booking->bookingStatus }}
                            </span>

                            {{-- Refund Status Tag --}}
                            @if($booking->bookingStatus == 'Cancelled' && $booking->payment)
                                @if($booking->payment->depoStatus == 'Requested')
                                    <div class="mt-1 text-[10px] font-bold text-red-500 uppercase animate-pulse">Refund Requested</div>
                                @elseif($booking->payment->depoStatus == 'Refunded')
                                    <div class="mt-1 text-[10px] font-bold text-green-500 uppercase">Refunded</div>
                                @endif
                            @endif

                            @if($booking->remarks)
                                <div class="mt-2 p-2 bg-gray-50 border border-gray-200 rounded-lg max-w-[200px]">
                                    <p class="text-[10px] font-bold text-gray-400 uppercase">Remarks:</p>
                                    <p class="text-xs text-gray-600 leading-tight line-clamp-2" title="{{ $booking->remarks }}">
                                        {{ $booking->remarks }}
                                    </p>
                                </div>
                            @endif
                        </td>

                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                {{-- VIEW DETAILS (Always visible) --}}
                                <a href="{{ route('staff.bookings.show', $booking->bookingID) }}" 
                                   class="bg-gray-100 text-gray-600 px-3 py-2 rounded-lg text-xs font-bold hover:bg-gray-200 transition" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>

                                {{-- ACTION BUTTONS BASED ON STATUS & PAYMENT --}}
                                @if($booking->bookingStatus == 'Submitted')
                                    
                                    {{-- LOGIC: Verify Payment First, Then Approve Agreement --}}
                                    @if(!$booking->payment || $booking->payment->paymentStatus !== 'Verified')
                                        {{-- Step 1: Verify Payment --}}
                                        <form action="{{ route('staff.bookings.verify_payment', $booking->bookingID) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="bg-orange-100 text-orange-700 px-3 py-2 rounded-lg text-xs font-bold hover:bg-orange-200 transition flex items-center" title="Verify Payment">
                                                <i class="fas fa-search-dollar mr-1"></i> Verify
                                            </button>
                                        </form>
                                    @else
                                        {{-- Step 2: Approve Agreement --}}
                                        <form action="{{ route('staff.bookings.approve_agreement', $booking->bookingID) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="bg-green-100 text-green-700 px-3 py-2 rounded-lg text-xs font-bold hover:bg-green-200 transition flex items-center" title="Approve Booking">
                                                <i class="fas fa-check mr-1"></i> Approve
                                            </button>
                                        </form>
                                    @endif

                                @elseif($booking->bookingStatus == 'Cancelled' && $booking->payment && $booking->payment->depoStatus == 'Requested')
                                    {{-- APPROVE CANCELLATION / REFUND --}}
                                    <form action="{{ route('staff.bookings.refund', $booking->bookingID) }}" method="POST" onsubmit="return confirm('Ensure you have viewed the Agreement and Receipt before refunding. Proceed?');">
                                        @csrf
                                        <button type="submit" class="bg-red-500 text-white px-3 py-2 rounded-lg text-xs font-bold hover:bg-red-600 transition shadow-md shadow-red-500/20 flex items-center">
                                            <i class="fas fa-hand-holding-usd mr-1"></i> Refund
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Empty State --}}
            <div id="empty-state" class="hidden flex-col items-center justify-center py-20 text-center">
                <div class="bg-gray-50 rounded-full p-4 mb-4">
                    <i class="fas fa-filter text-gray-300 text-3xl"></i>
                </div>
                <p class="text-gray-500 font-medium">No bookings found in this category.</p>
            </div>
        </div>
    </div>
</div>

<script>
    function filterTable(status) {
        // 1. Update Buttons
        const buttons = document.querySelectorAll('.filter-btn');
        buttons.forEach(btn => {
            btn.classList.remove('bg-gray-900', 'text-white', 'shadow-lg', 'shadow-gray-900/20');
            btn.classList.add('bg-white', 'text-gray-500', 'border-gray-200');
            
            if (btn.innerText.trim() === status || (status === 'all' && btn.innerText.trim() === 'All')) {
                btn.classList.remove('bg-white', 'text-gray-500', 'border-gray-200');
                btn.classList.add('bg-gray-900', 'text-white', 'shadow-lg', 'shadow-gray-900/20');
            }
        });

        // 2. Filter Rows
        const rows = document.querySelectorAll('.booking-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const rowStatus = row.getAttribute('data-status');
            if (status === 'all' || rowStatus === status) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        // 3. Update Count & Empty State
        document.getElementById('showing-count').innerText = visibleCount + ' Records';
        
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
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>
@endsection