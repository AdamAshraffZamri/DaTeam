@extends('layouts.staff')

@section('content')
<div class="min-h-screen bg-slate-100 rounded-2xl p-6">
    <div class="max-w-7xl mx-auto">

        {{-- HEADER & ACTIONS --}}
        <div class="flex flex-col md:flex-row justify-between items-end mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-black text-gray-900">Deposit Management</h1>
                <p class="text-gray-500 mt-1 text-sm">Track and process customer security deposits and refunds.</p>
            </div>

            <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
                {{-- SEARCH & FILTER FORM --}}
                <form action="{{ route('staff.finance.deposits') }}" method="GET" id="filterForm" class="flex flex-col md:flex-row items-center gap-3 w-full xl:w-auto">
                    
                    {{-- 1. SEARCH INPUT --}}
                    <div class="relative group w-full md:w-64">
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Search ID, Customer..." 
                               class="w-full pl-10 pr-4 py-3.5 rounded-2xl border border-gray-200 bg-white text-sm font-bold text-gray-700 focus:ring-2 focus:ring-gray-900 focus:border-transparent transition-all shadow-sm group-hover:border-gray-300">
                        <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 group-hover:text-gray-600 transition-colors"></i>
                    </div>

                    {{-- 2. STATUS DROPDOWN --}}
                    @php
                        $currentStatus = request('status', 'requested'); // Default to 'requested' as per logic
                        $statuses = [
                            'requested' => 'Refund Requests',
                            'refunded'  => 'Refunded History'
                        ];
                        $currentLabel = $statuses[$currentStatus] ?? 'Refund Requests';
                        
                        // Count logic using the $counts array passed from controller
                        $currentCount = $counts[$currentStatus] ?? 0;
                    @endphp

                    <input type="hidden" name="status" id="statusInput" value="{{ $currentStatus }}">

                    <div class="relative w-full md:w-[220px]" id="customDropdown">
                        <button type="button" onclick="toggleDropdown()" 
                            class="w-full flex items-center justify-between bg-white border border-gray-200 text-gray-700 text-xs font-bold py-3.5 px-5 rounded-2xl hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm group">
                            
                            <div class="flex items-center gap-2">
                                <i class="fas fa-filter text-orange-500"></i>
                                <span id="dropdownLabel" class="truncate">{{ $currentLabel }}</span>
                                
                                {{-- MAIN BUTTON BADGE --}}
                                <span class="flex items-center justify-center w-5 h-5 rounded-full text-[9px] bg-orange-100 text-orange-700 ml-1">
                                    {{ $currentCount }}
                                </span>
                            </div>
                            <i class="fas fa-chevron-down text-[10px] text-gray-400 group-hover:text-gray-600 transition-transform duration-300" id="dropdownArrow"></i>
                        </button>

                        <div id="dropdownMenu" 
                            class="absolute top-full right-0 mt-2 w-full bg-white border border-gray-100 rounded-2xl shadow-xl overflow-hidden hidden transform origin-top transition-all duration-200 z-50">
                            
                            @foreach($statuses as $value => $label)
                                <div onclick="selectStatus('{{ $value }}')" 
                                     class="px-5 py-3 text-xs font-bold cursor-pointer transition-colors flex items-center justify-between border-b border-gray-50 last:border-0
                                     {{ $currentStatus == $value ? 'bg-orange-50 text-orange-600' : 'text-gray-600 hover:bg-gray-50' }}">
                                    
                                    <span>{{ $label }}</span>
                                    
                                    {{-- DROPDOWN LIST BADGE --}}
                                    <span class="flex items-center justify-center w-5 h-5 rounded-full text-[9px] {{ $currentStatus == $value ? 'bg-orange-200 text-orange-700' : 'bg-gray-100 text-gray-500' }}">
                                        {{ $counts[$value] ?? 0 }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- DEPOSIT LIST (CARD STYLE) --}}
        <div class="space-y-3">
            @forelse($bookings as $booking)
            <div class="animate-fade-in block bg-white rounded-xl p-2 pr-4 border border-gray-100 shadow-sm hover:shadow-md transition-all group cursor-pointer hover:border-orange-200"
                 onclick="window.location='{{ route('staff.bookings.show', $booking->bookingID) }}'">
                
                <div class="flex flex-col lg:flex-row items-center gap-4 lg:gap-0">
                    
                    {{-- 1. BOOKING INFO --}}
                    <div class="flex items-center gap-4 w-full lg:w-[30%] p-2 lg:p-0">
                        <div class="w-12 h-12 rounded-xl bg-orange-50 text-orange-600 border border-orange-100 flex items-center justify-center font-black text-xs shrink-0">
                            #{{ $booking->bookingID }}
                        </div>
                        <div>
                            <div class="text-sm font-bold text-slate-900 leading-tight">{{ $booking->vehicle->model }}</div>
                            <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mt-0.5">{{ $booking->vehicle->plateNo }}</div>
                            <div class="text-[10px] text-slate-600 font-medium uppercase tracking-wider mt-0.5">{{ \Carbon\Carbon::parse($booking->bookingDate)->format('d M Y') }}</div>
                            
                            @if(in_array($booking->bookingStatus, ['Cancelled', 'Rejected']))
                                <span class="inline-block mt-1 text-[9px] px-1.5 py-0.5 rounded bg-red-50 text-red-600 font-bold uppercase tracking-wide border border-red-100">
                                    {{ $booking->bookingStatus }}
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- 2. CUSTOMER INFO --}}
                    <div class="w-full lg:w-[25%] border-t lg:border-t-0 lg:border-l border-gray-100 pt-2 lg:pt-0 lg:pl-6 shrink-0">
                        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-1">Customer</p>
                        <div class="text-sm font-bold text-slate-800">{{ $booking->customer->fullName }}</div>
                        <div class="text-xs text-slate-400 font-medium">{{ $booking->customer->phoneNo }}</div>
                    </div>

                    {{-- 3. FINANCIALS --}}
                    <div class="w-full lg:w-[20%] border-t lg:border-t-0 lg:border-l border-gray-100 pt-2 lg:pt-0 lg:pl-6 shrink-0">
                        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-1">Refund Amount</p>
                        @php
                            $depositAmount = $booking->payments->sum('depoAmount');
                            $totalPaid = $booking->payments->whereNotIn('paymentStatus', ['Void', 'Rejected'])->sum('amount');
                            $isFullRefund = in_array($booking->bookingStatus, ['Cancelled', 'Rejected']);
                            $finalAmount = $isFullRefund ? $totalPaid : $depositAmount;
                            $label = $isFullRefund ? 'Full Refund' : 'Deposit Only';
                            
                            $depositPayment = $booking->payments->where('depoAmount', '>', 0)->first();
                            $currentDepoStatus = $depositPayment ? $depositPayment->depoStatus : 'Unknown';
                        @endphp
                        <div class="flex flex-col">
                            <span class="text-sm font-black text-slate-900">RM {{ number_format($finalAmount, 2) }}</span>
                            <span class="text-[9px] text-slate-400 font-bold uppercase tracking-wide mt-0.5">{{ $label }}</span>
                        </div>
                    </div>

                    {{-- 4. STATUS --}}
                    <div class="w-full lg:w-[15%] border-t lg:border-t-0 lg:border-l border-gray-100 pt-2 lg:pt-0 lg:pl-6 shrink-0">
                        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-1">Status</p>
                        @php
                            $statusClasses = match($currentDepoStatus) {
                                'Requested' => 'bg-orange-50 text-orange-600 border-orange-100 animate-pulse',
                                'Refunded'  => 'bg-green-50 text-green-600 border-green-100',
                                'Pending'   => 'bg-yellow-50 text-yellow-600 border-yellow-100',
                                'Forfeited', 'Void' => 'bg-slate-800 text-white border-slate-900',
                                default     => 'bg-gray-100 text-gray-500 border-gray-200'
                            };
                        @endphp
                        <span class="inline-block px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-widest border {{ $statusClasses }}">
                            {{ $currentDepoStatus }}
                        </span>
                    </div>

                    {{-- 5. ACTION --}}
                    <div class="w-full lg:flex-1 flex justify-end items-center gap-2 pt-2 lg:pt-0 border-t lg:border-t-0 border-gray-100 lg:pl-4">
                        @if($status === 'requested')
                            <button type="button"
                                onclick="event.stopPropagation(); openRefundModal('{{ route('staff.finance.refund', $booking->bookingID) }}', '{{ number_format($finalAmount, 2) }}')"
                                class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wide transition shadow-lg shadow-green-500/20 flex items-center gap-2 transform hover:-translate-y-0.5">
                                <i class="fas fa-check-circle"></i> Approve
                            </button>
                        @else
                            <div class="w-8 h-8 rounded-full bg-slate-50 flex items-center justify-center text-slate-300 border border-slate-200">
                                <i class="fas fa-check"></i>
                            </div>
                        @endif
                    </div>

                </div>
            </div>
            @empty
                <div class="flex flex-col items-center justify-center py-20 text-center animate-fade-in">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4"><i class="fas fa-inbox text-gray-300 text-2xl"></i></div>
                    <p class="text-gray-500 font-medium">No deposits found in this category.</p>
                </div>
            @endforelse
        </div>

        {{-- PAGINATION --}}
        @if($bookings->hasPages())
            <div class="mt-6 px-4">
                {{ $bookings->appends(['status' => $status, 'search' => request('search')])->links() }}
            </div>
        @endif

    </div>
</div>

{{-- REFUND MODAL --}}
<div id="refund-modal" class="fixed inset-0 z-50 hidden bg-slate-900/40 flex items-center justify-center p-4 backdrop-blur-sm transition-opacity duration-300">
    <div class="bg-white rounded-[2rem] w-full max-w-md p-8 shadow-2xl border border-white/50 transform transition-all scale-100">
        
        <div class="flex justify-between items-center mb-6">
            <h3 class="font-black text-slate-900 text-xl flex items-center gap-2">
                <i class="fas fa-hand-holding-usd text-green-500"></i> Approve Refund
            </h3>
            <button type="button" onclick="closeRefundModal()" class="w-8 h-8 rounded-full bg-slate-50 text-slate-400 hover:bg-slate-100 hover:text-slate-600 flex items-center justify-center transition">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form id="refund-form" action="" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="bg-green-50 border border-green-100 rounded-2xl p-6 mb-6 flex flex-col items-center text-center">
                <p class="text-[10px] text-green-600 font-bold uppercase tracking-widest mb-1">Total Refund Amount</p>
                <p class="text-3xl font-black text-green-700 tracking-tight" id="refund-modal-amount">RM 0.00</p>
            </div>

            <div class="mb-6">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Remarks / Reference No.</label>
                <textarea name="remarks" rows="3" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-4 text-sm font-bold text-slate-800 focus:bg-white focus:border-green-500 focus:ring-0 outline-none transition-all placeholder-slate-400" placeholder="e.g. Transferred via Maybank (Ref: 123456)..."></textarea>
            </div>

            <button type="submit" class="w-full bg-green-600 hover:bg-green-500 text-white font-bold py-4 rounded-xl shadow-lg shadow-green-500/30 transition transform active:scale-[0.98] uppercase tracking-wide text-xs flex items-center justify-center gap-2">
                Confirm Refund <i class="fas fa-arrow-right"></i>
            </button>
        </form>
    </div>
</div>

<script>
    // --- DROPDOWN LOGIC ---
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

    // --- MODAL LOGIC ---
    function openRefundModal(actionUrl, amount) {
        const modal = document.getElementById('refund-modal');
        document.getElementById('refund-form').action = actionUrl;
        document.getElementById('refund-modal-amount').innerText = 'RM ' + amount;
        
        modal.classList.remove('hidden');
    }

    function closeRefundModal() {
        document.getElementById('refund-modal').classList.add('hidden');
    }
</script>

<style>
    @keyframes fade-in { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
    .animate-fade-in { animation: fade-in 0.2s ease-out forwards; }
    .animate-fade-in-down { animation: fade-in 0.15s ease-out forwards; }
</style>

@endsection