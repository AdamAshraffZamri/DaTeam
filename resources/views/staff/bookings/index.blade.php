@extends('layouts.staff')

@section('content')

<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css' rel='stylesheet' />
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<style>
    .fc { font-family: inherit; }
    .fc .fc-button-primary { background-color: #f97316; border-color: #f97316; }
    .fc .fc-button-primary:hover { background-color: #ea580c; }
    .fc .fc-button-primary:not(:disabled):active,
    .fc .fc-button-primary:not(:disabled).fc-button-active { background-color: #ea580c; border-color: #ea580c; }
    .fc-daygrid-day.fc-day-disabled { background-color: #fecaca; opacity: 0.6; }
    .fc-highlight { background-color: #dbeafe; }
    .fc-daygrid-day:not(.fc-day-disabled):hover { background-color: #f0f9ff; }
</style>

<div class="min-h-screen bg-slate-100 rounded-2xl p-6">
    <div class="max-w-7xl mx-auto">

        {{-- HEADER WITH SEARCH AND FILTER --}}
        <div class="flex flex-col xl:flex-row justify-between items-end xl:items-center mb-8 gap-4">
            <div class="w-full xl:w-auto">
                <h1 class="text-3xl font-black text-gray-900">Booking Management</h1>
                <p class="text-gray-500 mt-1 text-sm">Monitor and manage all customer and external bookings.</p>
            </div>

            <div class="flex flex-col md:flex-row items-center gap-3 w-full xl:w-auto">
                {{-- CREATE BOOKING BUTTON --}}
                <button onclick="openCreateBookingModal()" 
                    class="bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700 text-white px-6 py-3.5 rounded-2xl font-bold text-sm shadow-lg hover:shadow-xl transition-all flex items-center gap-2 whitespace-nowrap">
                    <i class="fas fa-plus-circle"></i> Create Booking
                </button>
            </div>
        </div>

        {{-- COMBINED FILTER FORM (With Counts) --}}
        <div class="flex flex-col xl:flex-row gap-4 mb-8">
            <form action="{{ route('staff.bookings.index') }}" method="GET" id="filterForm" class="flex flex-col md:flex-row items-center gap-3 w-full">
                
                {{-- SEARCH INPUT --}}
                <div class="relative group w-full md:w-64">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Search ID, Name, Plate..." 
                           class="w-full pl-10 pr-4 py-3.5 rounded-2xl border border-gray-200 bg-white text-sm font-bold text-gray-700 focus:ring-2 focus:ring-gray-900 focus:border-transparent transition-all shadow-sm group-hover:border-gray-300">
                    <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 group-hover:text-gray-600 transition-colors"></i>
                </div>

                {{-- BOOKING TYPE FILTER --}}
                @php
                    $currentType = request('type', 'all');
                    $types = [
                        'all'      => 'All Bookings',
                        'customer' => 'Customer Bookings',
                        'external' => 'External Bookings'
                    ];
                @endphp
                <input type="hidden" name="type" id="typeInput" value="{{ $currentType }}">
                <div class="relative w-full md:w-[180px]" id="typeDropdown">
                    <button type="button" onclick="toggleTypeDropdown()" 
                        class="w-full flex items-center justify-between bg-white border border-gray-200 text-gray-700 text-xs font-bold py-3.5 px-5 rounded-2xl hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-building text-blue-500"></i>
                            <span id="typeLabel">{{ $types[$currentType] }}</span>
                        </div>
                        <i class="fas fa-chevron-down text-[10px] text-gray-400" id="typeArrow"></i>
                    </button>
                    <div id="typeMenu" class="absolute top-full right-0 mt-2 w-full bg-white border border-gray-100 rounded-2xl shadow-xl overflow-hidden hidden z-50">
                        @foreach($types as $value => $label)
                            <div onclick="selectType('{{ $value }}')" 
                                class="px-5 py-3 text-xs font-bold cursor-pointer transition-colors flex items-center justify-between border-b border-gray-50 last:border-0
                                {{ $currentType == $value ? 'bg-blue-50 text-blue-600' : 'text-gray-600 hover:bg-gray-50' }}">
                                <span>{{ $label }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- STATUS DROPDOWN --}}
                @php
                    $currentStatus = request('status', 'all');
                    $statuses = [
                        'all'          => 'All Statuses',
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

                    // Count Logic
                    $getCount = function($status) {
                        return $status === 'all' 
                            ? \App\Models\Booking::count() 
                            : \App\Models\Booking::where('bookingStatus', $status)->count();
                    };
                    $currentCount = $getCount($currentStatus);
                @endphp

                <input type="hidden" name="status" id="statusInput" value="{{ $currentStatus }}">

                <div class="relative w-full md:w-[200px]" id="customDropdown">
                    {{-- TRIGGER --}}
                    <button type="button" onclick="toggleDropdown()" 
                        class="w-full flex items-center justify-between bg-white border border-gray-200 text-gray-700 text-xs font-bold py-3.5 px-5 rounded-2xl hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm group">
                        
                        <div class="flex items-center gap-2 truncate">
                            <i class="fas fa-filter text-orange-500"></i>
                            <span id="dropdownLabel" class="truncate">{{ $currentLabel }}</span>
                            
                            {{-- COUNT BADGE ON BUTTON --}}
                            <span class="flex items-center justify-center w-5 h-5 rounded-full text-[9px] bg-orange-100 text-orange-700 ml-1 shrink-0">
                                {{ $currentCount }}
                            </span>
                        </div>
                        <i class="fas fa-chevron-down text-[10px] text-gray-400 group-hover:text-gray-600 transition-transform duration-300" id="dropdownArrow"></i>
                    </button>

                    {{-- MENU --}}
                    <div id="dropdownMenu" 
                        class="absolute top-full right-0 mt-2 w-full bg-white border border-gray-100 rounded-2xl shadow-xl overflow-hidden hidden transform origin-top transition-all duration-200 z-50 max-h-[300px] overflow-y-auto">
                        
                        @foreach($statuses as $value => $label)
                            @php $count = $getCount($value); @endphp
                            <div onclick="selectStatus('{{ $value }}')" 
                                class="px-5 py-3 text-xs font-bold cursor-pointer transition-colors flex items-center justify-between border-b border-gray-50 last:border-0
                                {{ $currentStatus == $value ? 'bg-orange-50 text-orange-600' : 'text-gray-600 hover:bg-gray-50' }}">
                                
                                <span>{{ $label }}</span>
                                
                                {{-- COUNT BADGE IN LIST --}}
                                <span class="flex items-center justify-center w-5 h-5 rounded-full text-[9px] {{ $currentStatus == $value ? 'bg-orange-200 text-orange-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $count }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- PICKUP DATE FILTER --}}
                <div class="relative group w-full md:w-[180px]">
                    <input type="date" name="pickup_date" value="{{ request('pickup_date') }}" 
                           title="Filter bookings by pickup date"
                           class="w-full px-5 py-3.5 rounded-2xl border border-gray-200 bg-white text-sm font-bold text-gray-700 focus:ring-2 focus:ring-gray-900 focus:border-transparent transition-all shadow-sm hover:border-gray-300"
                           onchange="document.getElementById('filterForm').submit()">
                    {{-- Tooltip --}}
                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-1.5 bg-gray-900 text-white text-xs font-bold rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-50">
                        Filter by Pickup Date
                        <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
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
                    <div class="flex items-center gap-4 w-full lg:w-[18%] shrink-0">
                        <div class="w-10 h-10 rounded-lg bg-orange-50 flex flex-col items-center justify-center border border-orange-200 shrink-0">
                            <span class="text-xs font-black text-gray-500 group-hover:text-orange-600 transition-colors">#{{ $booking->bookingID }}</span>
                        </div>
                        <div class="overflow-hidden">
                            <h4 class="text-sm font-bold text-gray-900 truncate" title="{{ $booking->customer->fullName ?? 'Guest' }}">
                                {{ $booking->customer->fullName ?? 'Guest' }}
                            </h4>
                            <p class="text-xs text-gray-400 truncate">{{ $booking->customer->email ?? 'No email' }}</p>
                            <!-- <div class="text-[10px] text-slate-600 font-medium uppercase tracking-wider mt-0.5">{{ \Carbon\Carbon::parse($booking->bookingDate)->format('d M Y') }}</div> -->
                        </div>
                    </div>

                    {{-- 2. Booking Source --}}
                    <div class="w-full lg:w-[12%] border-t lg:border-t-0 lg:border-l border-gray-100 pt-2 lg:pt-0 lg:pl-6 shrink-0">
                        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Source</p>
                        @php
                            $isExternal = isset($booking->externalCompany) || !empty($booking->external_company);
                            $companyName = $booking->external_company ?? 'Customer';
                        @endphp
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold px-2.5 py-1 rounded-lg border
                            {{ $isExternal 
                                ? 'bg-purple-50 text-purple-700 border-purple-200' 
                                : 'bg-blue-50 text-blue-700 border-blue-200' }}">
                            <i class="fas {{ $isExternal ? 'fa-building' : 'fa-user' }} text-[10px]"></i>
                            {{ $isExternal ? 'External' : 'Direct' }}
                        </span>
                    </div>

                    {{-- 3. Vehicle Info --}}
                    <div class="w-full lg:w-[15%] border-t lg:border-t-0 lg:border-l border-gray-100 pt-2 lg:pt-0 lg:pl-6 shrink-0">
                        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Vehicle</p>
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-bold text-gray-800 truncate">{{ $booking->vehicle->model ?? 'Unknown' }}</span>
                        </div>
                        <span class="text-[10px] font-mono font-black text-gray-600 bg-gray-50 px-1.5 py-0.5 rounded border border-gray-200 mt-0.5 inline-block uppercase">
                            {{ $booking->vehicle->plateNo ?? '-' }}
                        </span>
                    </div>

                    {{-- 4. Price --}}
                    <div class="w-full lg:w-[9%] border-t lg:border-t-0 lg:border-l border-gray-100 pt-2 lg:pt-0 lg:pl-6 shrink-0">
                        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Total</p>
                        <p class="text-sm font-black text-gray-900">RM {{ number_format($booking->totalCost, 2) }}</p>
                    </div>

                    {{-- 5. Pickup & Return DateTime --}}
                    <div class="w-full lg:w-[16%] border-t lg:border-t-0 lg:border-l border-gray-100 pt-2 lg:pt-0 lg:pl-6 lg:pr-6 shrink-0">
                        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-2">Pickup & Return</p>
                        <div class="flex flex-col gap-2 items-start text-xs">
                            <div class="flex items-start gap-2">
                                <i class="fas fa-sign-out-alt text-green-600 mt-0.5 text-[10px] flex-shrink-0"></i>
                                <div>
                                    <p class="font-bold text-gray-900">{{ \Carbon\Carbon::parse($booking->originalDate)->format('d M Y') }}</p>
                                    <p class="text-[10px] text-gray-500">{{ \Carbon\Carbon::createFromFormat('H:i:s', $booking->bookingTime)->format('h:i A') }}</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-2">
                                <i class="fas fa-sign-in-alt text-red-600 mt-0.5 text-[10px] flex-shrink-0"></i>
                                <div>
                                    <p class="font-bold text-gray-900">{{ \Carbon\Carbon::parse($booking->returnDate)->format('d M Y') }}</p>
                                    <p class="text-[10px] text-gray-500">{{ \Carbon\Carbon::createFromFormat('H:i:s', $booking->returnTime)->format('h:i A') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 6. Status --}}
                    <div class="w-full lg:w-[13%] border-t lg:border-t-0 lg:border-l border-gray-100 pt-2 lg:pt-0 lg:pl-10 shrink-0">
                        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-1">Status</p>
                        @php
                            $statusColor = match($booking->bookingStatus) {
                                'Submitted' => 'bg-orange-100 text-orange-700 border-orange-200',
                                'Confirmed' => 'bg-blue-100 text-blue-700 border-blue-200',
                                'Active'    => 'bg-purple-100 text-purple-700 border-purple-200',
                                'Completed' => 'bg-green-100 text-green-700 border-green-200',
                                'Cancelled' => 'bg-red-300 text-red-700 border-red-200',
                                'Rejected'  => 'bg-red-100 text-red-700 border-red-200',
                                'Paid'      => 'bg-orange-50 text-red-700 border-orange-200',
                                'Deposit Paid' => 'bg-orange-50 text-orange-700 border-orange-200',
                                default     => 'bg-gray-50 text-gray-700 border-gray-100'
                            };
                        @endphp
                        <span class="block w-28 text-center px-0 py-1.5 rounded-full text-[10px] font-bold border uppercase tracking-wider {{ $statusColor }}">
                            {{ $booking->bookingStatus }}
                        </span>

                        {{-- [ADDED] REFUND STATUS INDICATOR --}}
                        @php
                            $depositPayment = $booking->payments->where('depoAmount', '>', 0)->first();
                        @endphp

                        @if($depositPayment && in_array($depositPayment->depoStatus, ['Refunded', 'Requested']))
                            <div class="mt-1.5 w-28 flex justify-center">
                                <span class="text-[9px] font-bold px-2 py-0.5 rounded border flex items-center gap-1 uppercase tracking-wide
                                    {{ $depositPayment->depoStatus == 'Refunded' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-red-50 text-red-600 border-red-100 animate-pulse' }}">
                                    <i class="fas fa-hand-holding-usd"></i> {{ $depositPayment->depoStatus }}
                                </span>
                            </div>
                        @endif

                    </div>

                    {{-- 7. ACTION --}}
                    <div class="w-full lg:flex-1 flex justify-end items-center gap-2 pt-2 lg:pt-0 border-t lg:border-t-0 border-gray-100 lg:pl-6" onclick="event.stopPropagation()">
                         @if($booking->bookingStatus == 'Submitted' || $booking->bookingStatus == 'Paid')
                            @if(!$booking->payment || $booking->payment->paymentStatus !== 'Verified')
                                <form action="{{ route('staff.bookings.verify_payment', $booking->bookingID) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="bg-orange-100 hover:bg-orange-200 text-orange-700 px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-2 w-24 justify-center shadow-sm">
                                        <i class="fas fa-search-dollar"></i> <span>Verify</span>
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('staff.bookings.approve_agreement', $booking->bookingID) }}" method="POST" onsubmit="return confirm('Confirm approve? Make sure all details are correct, payment received fully and verified and plate number assigned correctly. This action cannot be undone.');">
                                    @csrf
                                    <button type="submit" class="bg-green-100 hover:bg-green-200 text-green-700 px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-2 w-24 justify-center shadow-sm">
                                        <i class="fas fa-check"></i> <span>Approve</span>
                                    </button>
                                </form>
                            @endif
                            
                        @elseif($booking->bookingStatus == 'Confirmed')
                        @php
                            $pickupTime = \Carbon\Carbon::parse($booking->originalDate . ' ' . $booking->bookingTime);
                            $now = \Carbon\Carbon::now();
                            $isTimeReached = $now->greaterThanOrEqualTo($pickupTime);
                        @endphp

                        <div class="bg-blue-50 border border-blue-100 text-blue-700 px-4 py-2 rounded-lg text-xs font-bold flex flex-col items-center gap-0.5 w-24 justify-center shadow-sm" 
                            title="Auto-activates at {{ $pickupTime->format('h:i A') }}">
                            
                            @if(!$isTimeReached)
                                <span class="text-[8px] uppercase tracking-tighter opacity-70">Pickup In</span>
                                <span class="font-black leading-none" id="timer-{{ $booking->bookingID }}">
                                    {{ $now->diff($pickupTime)->format('%H:%I:%S') }}
                                </span>

                                <script>
                                    (function() {
                                        const target = new Date("{{ $pickupTime->toIso8601String() }}").getTime();
                                        const timerEl = document.getElementById("timer-{{ $booking->bookingID }}");
                                        
                                        const interval = setInterval(() => {
                                            const now = new Date().getTime();
                                            const diff = target - now;

                                            if (diff <= 0) {
                                                clearInterval(interval);
                                                window.location.reload(); 
                                            } else {
                                                const h = Math.floor((diff / (1000 * 60 * 60)));
                                                const m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                                                const s = Math.floor((diff % (1000 * 60)) / 1000);
                                                timerEl.innerHTML = `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
                                            }
                                        }, 1000);
                                    })();
                                </script>
                            @else
                                <i class="fas fa-sync fa-spin text-[10px]"></i>
                                <span class="text-[9px] uppercase">Activating</span>
                            @endif
                        </div>

                        @elseif($booking->bookingStatus == 'Active')
                            @php
                                $returnTime = \Carbon\Carbon::parse($booking->returnDate . ' ' . $booking->returnTime);
                                $now = \Carbon\Carbon::now();
                                $isTimeReached = $now->greaterThanOrEqualTo($returnTime);
                            @endphp

                            <div class="w-24 flex flex-col items-center justify-center transition-all duration-300 {{ !$isTimeReached ? 'bg-purple-50 border border-purple-100 text-purple-700 text-xs font-bold gap-0.5 px-4 py-2 rounded-lg shadow-sm' : '' }}" 
                                title="Scheduled return: {{ $returnTime->format('d M h:i A') }}">
                                
                                @if(!$isTimeReached)
                                    {{-- COUNTDOWN VIEW --}}
                                    <span class="text-[8px] uppercase tracking-tighter opacity-70">Return In</span>
                                    <span class="font-black leading-none text-purple-700" id="timer-{{ $booking->bookingID }}">
                                        {{ $now->diff($returnTime)->format('%H:%I:%S') }}
                                    </span>

                                    <script>
                                        (function() {
                                            const target = new Date("{{ $returnTime->toIso8601String() }}").getTime();
                                            const timerEl = document.getElementById("timer-{{ $booking->bookingID }}");
                                            
                                            const interval = setInterval(() => {
                                                const now = new Date().getTime();
                                                const diff = target - now;

                                                if (diff <= 0) {
                                                    clearInterval(interval);
                                                    window.location.reload(); 
                                                } else {
                                                    const h = Math.floor((diff / (1000 * 60 * 60)));
                                                    const m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                                                    const s = Math.floor((diff % (1000 * 60)) / 1000);
                                                    timerEl.innerHTML = `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
                                                }
                                            }, 1000);
                                        })();
                                    </script>
                                @else
                                    {{-- COMPLETE BUTTON VIEW --}}
                                    <form action="{{ route('staff.bookings.return', $booking->bookingID) }}" method="POST" onsubmit="return confirm('Complete rental? Deposit will be processed if any.');" class="w-full">
                                        @csrf
                                        <button type="submit" class="w-full bg-purple-100 hover:bg-purple-200 text-purple-700 py-2.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 justify-center">
                                            <i class="fas fa-pen-alt text-xs"></i>
                                            <span>Complete</span>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        
                        @elseif($booking->bookingStatus == 'Completed')
                            <a href="{{ route('staff.bookings.show', $booking->bookingID) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-2 w-24 justify-center shadow-sm">
                                <i class="fas fa-info-circle"></i> <span>Details</span>
                            </a>
                        @elseif(in_array($booking->bookingStatus, ['Pending', 'Submitted', 'Rejected', 'Cancelled']))
                            <form action="{{ route('staff.bookings.destroy', $booking->bookingID) }}" method="POST" onsubmit="return confirm('Delete this booking? This action cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-gray-100 hover:bg-red-100 text-gray-700 hover:text-red-700 px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-2 w-24 justify-center shadow-sm">
                                    <i class="fas fa-trash"></i> <span>Delete</span>
                                </button>
                            </form>
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

{{-- CREATE BOOKING MODAL --}}
<div id="create-booking-modal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-md flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl w-full max-w-2xl shadow-2xl overflow-hidden my-8">
        {{-- Modal Header (Sticky) --}}
        <div class="bg-gradient-to-r from-orange-500 to-red-600 text-white px-8 py-6 sticky top-0 z-10">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-black">Create New Booking</h2>
                    <p class="text-sm text-white/80 mt-1">Create a booking for customer or external company</p>
                </div>
                <button onclick="document.getElementById('create-booking-modal').classList.add('hidden')" class="text-white/80 hover:text-white text-2xl">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        {{-- Modal Body (Scrollable) --}}
        <div class="p-8 space-y-6 max-h-[calc(100vh-250px)] overflow-y-auto">
            
            {{-- Form --}}
            <form id="create-booking-form" action="{{ route('staff.bookings.store') }}" method="POST" class="space-y-6" onsubmit="handleFormSubmit(event)">
                @csrf

                {{-- Booking Type Selection --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-3">Booking Type</label>
                    <div class="grid grid-cols-1 gap-4">
                        <label class="flex items-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-purple-500 hover:bg-purple-50 transition-all" onclick="setBookingType('external')">
                            <input type="radio" name="booking_type" value="external" id="type_external" class="form-radio" checked onchange="updateBookingForm()">
                            <div class="ml-3">
                                <span class="font-bold text-gray-900">External Company</span>
                                <p class="text-xs text-gray-500">From agencies like Wahdah</p>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- External Company Details (For External Bookings) --}}
                <div id="external-field" class="block space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Company Name <span class="text-red-500">*</span></label>
                            <input type="text" name="external_company" id="external_company" placeholder="e.g. Wahdah, TourTech, TravelPlus" class="w-full border border-gray-300 rounded-xl p-3 font-semibold text-gray-700 focus:ring-2 focus:ring-purple-500 focus:border-transparent" required>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Contact Person</label>
                            <input type="text" name="contact_person" id="contact_person" placeholder="e.g. Ahmad Karim" class="w-full border border-gray-300 rounded-xl p-3 text-gray-700 focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Company Phone</label>
                            <input type="tel" name="company_phone" id="company_phone" placeholder="012-3456789" class="w-full border border-gray-300 rounded-xl p-3 text-gray-700 focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        </div>
                    </div>
                </div>

                {{-- Vehicle Selection --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Select Vehicle <span class="text-red-500">*</span></label>
                    <select name="vehicle_id" id="vehicle_select" class="w-full border border-gray-300 rounded-xl p-3 font-semibold text-gray-700 focus:ring-2 focus:ring-orange-500 focus:border-transparent" required onchange="initializeCalendars()">
                        <option value="">-- Select Vehicle --</option>
                        @foreach(\App\Models\Vehicle::get() as $vehicle)
                            <option value="{{ $vehicle->VehicleID }}" data-price="{{ $vehicle->priceHour }}" data-rates="{{ json_encode($vehicle->hourly_rates ?? ['1'=>10,'3'=>18,'5'=>25,'7'=>31,'9'=>36,'12'=>40,'24'=>43]) }}">{{ $vehicle->model }} - {{ $vehicle->plateNo }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Rental Dates with Calendar --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- Pickup Date Calendar --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Pickup Date <span class="text-red-500">*</span></label>
                        <div id="pickup-calendar" style="height: 300px;" class="border border-gray-300 rounded-xl overflow-hidden"></div>
                        <input type="hidden" name="pickup_date" id="pickup_date_value">
                        <p class="text-xs text-gray-500 mt-2" id="pickup_text">Select a date</p>
                    </div>

                    {{-- Return Date Calendar --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Return Date <span class="text-red-500">*</span></label>
                        <div id="return-calendar" style="height: 300px;" class="border border-gray-300 rounded-xl overflow-hidden"></div>
                        <input type="hidden" name="return_date" id="return_date_value">
                        <p class="text-xs text-gray-500 mt-2" id="return_text">Select a date</p>
                    </div>
                </div>

                {{-- Pickup and Return Times (Hour Only) --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Pickup Time (Hour) <span class="text-red-500">*</span></label>
                        <select name="pickup_time" id="pickup_time" class="w-full border border-gray-300 rounded-xl p-3 text-gray-700 focus:ring-2 focus:ring-orange-500 focus:border-transparent" required onchange="calculateTotalCost()" oninput="calculateTotalCost()">
                            <option value="">-- Select Hour --</option>
                            <option value="00:00">00:00 (Midnight)</option>
                            <option value="01:00">01:00</option>
                            <option value="02:00">02:00</option>
                            <option value="03:00">03:00</option>
                            <option value="04:00">04:00</option>
                            <option value="05:00">05:00</option>
                            <option value="06:00">06:00</option>
                            <option value="07:00">07:00</option>
                            <option value="08:00">08:00</option>
                            <option value="09:00">09:00</option>
                            <option value="10:00">10:00</option>
                            <option value="11:00">11:00</option>
                            <option value="12:00">12:00 (Noon)</option>
                            <option value="13:00">13:00</option>
                            <option value="14:00">14:00</option>
                            <option value="15:00">15:00</option>
                            <option value="16:00">16:00</option>
                            <option value="17:00">17:00</option>
                            <option value="18:00">18:00</option>
                            <option value="19:00">19:00</option>
                            <option value="20:00">20:00</option>
                            <option value="21:00">21:00</option>
                            <option value="22:00">22:00</option>
                            <option value="23:00">23:00</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Return Time (Hour) <span class="text-red-500">*</span></label>
                        <select name="return_time" id="return_time" class="w-full border border-gray-300 rounded-xl p-3 text-gray-700 focus:ring-2 focus:ring-orange-500 focus:border-transparent" required onchange="calculateTotalCost()" oninput="calculateTotalCost()">
                            <option value="">-- Select Hour --</option>
                            <option value="00:00">00:00 (Midnight)</option>
                            <option value="01:00">01:00</option>
                            <option value="02:00">02:00</option>
                            <option value="03:00">03:00</option>
                            <option value="04:00">04:00</option>
                            <option value="05:00">05:00</option>
                            <option value="06:00">06:00</option>
                            <option value="07:00">07:00</option>
                            <option value="08:00">08:00</option>
                            <option value="09:00">09:00</option>
                            <option value="10:00">10:00</option>
                            <option value="11:00">11:00</option>
                            <option value="12:00">12:00 (Noon)</option>
                            <option value="13:00">13:00</option>
                            <option value="14:00">14:00</option>
                            <option value="15:00">15:00</option>
                            <option value="16:00">16:00</option>
                            <option value="17:00">17:00</option>
                            <option value="18:00">18:00</option>
                            <option value="19:00">19:00</option>
                            <option value="20:00">20:00</option>
                            <option value="21:00">21:00</option>
                            <option value="22:00">22:00</option>
                            <option value="23:00">23:00</option>
                        </select>
                    </div>
                </div>

                {{-- Cost Display --}}
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-4 rounded-xl border-2 border-blue-200">
                    <div class="grid grid-cols-2 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs font-bold text-gray-700">Duration</p>
                            <p class="text-lg font-bold text-blue-600" id="duration_display">-- hours</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-700">Rental Cost</p>
                            <p class="text-lg font-bold text-blue-600" id="cost_display">RM 0.00</p>
                        </div>
                    </div>
                </div>

                {{-- Total Amount (Manual Entry) --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Total Amount <span class="text-red-500">*</span></label>
                    <input type="number" name="total_amount" id="total_amount" min="0" step="0.01" placeholder="Enter total amount" class="w-full border border-gray-300 rounded-xl p-3 text-gray-700 focus:ring-2 focus:ring-orange-500 focus:border-transparent" required>
                </div>

                {{-- Hidden location fields (default to Student Mall for external bookings) --}}
                <input type="hidden" name="pickup_location" value="Student Mall">
                <input type="hidden" name="return_location" value="Student Mall">

                {{-- Remarks --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Special Remarks (Optional)</label>
                    <textarea name="remarks" rows="3" placeholder="Add any special notes..." class="w-full border border-gray-300 rounded-xl p-3 text-gray-700 focus:ring-2 focus:ring-orange-500 focus:border-transparent resize-none"></textarea>
                </div>

                {{-- Form Actions --}}
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                    <button type="button" onclick="document.getElementById('create-booking-modal').classList.add('hidden')" class="px-6 py-3 border border-gray-300 rounded-xl font-bold text-gray-700 hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-6 py-3 bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700 text-white rounded-xl font-bold shadow-lg transition flex items-center gap-2">
                        <i class="fas fa-plus-circle"></i> Create Booking
                    </button>
                </div>
            </form>
        </div>

        {{-- Modal Footer (Sticky) --}}
        <div class="bg-gray-50 border-t border-gray-200 px-8 py-4 sticky bottom-0">
            <div class="flex justify-end gap-3">
                <button onclick="document.getElementById('create-booking-modal').classList.add('hidden')" class="px-6 py-2 border border-gray-300 rounded-lg font-semibold text-gray-700 hover:bg-gray-50 transition">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
    </div>
</div>

<style>
@keyframes fade-in { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
.animate-fade-in { animation: fade-in 0.2s ease-out forwards; }
.animate-fade-in-down { animation: fade-in 0.15s ease-out forwards; }
</style>

<script>
    let bookedDatesInfo = {}; // Store booking info by date
    
    // Initialize FullCalendar for date selection
    function initializeCalendars() {
        console.log('=== initializeCalendars called ===');
        
        // Check if FullCalendar is loaded
        if (typeof FullCalendar === 'undefined') {
            console.error('ERROR: FullCalendar library not loaded!');
            alert('Calendar library not loaded. Please refresh the page.');
            return;
        }
        
        const vehicleId = document.getElementById('vehicle_select').value;
        console.log('Vehicle ID:', vehicleId);
        
        if (!vehicleId) {
            console.log('No vehicle selected');
            return;
        }

        // Get booked dates
        const apiUrl = `/staff/api/vehicle/${vehicleId}/booked-dates`;
        console.log('Fetching booked dates from:', apiUrl);
        
        fetch(apiUrl)
            .then(r => {
                console.log('API response status:', r.status, r.ok);
                if (!r.ok) throw new Error(`API returned ${r.status}`);
                return r.json();
            })
            .then(data => {
                console.log('Booked dates data:', data);
                const bookedDates = data.dates || [];
                bookedDatesInfo = data.dateInfo || {}; // Store detailed booking info
                console.log('Booked dates array:', bookedDates);
                console.log('Booked dates info:', bookedDatesInfo);
                
                // Initialize Pickup Calendar
                const pickupCalel = document.getElementById('pickup-calendar');
                console.log('Pickup calendar element:', pickupCalel);
                
                if (!pickupCalel) {
                    console.error('ERROR: pickup-calendar element not found!');
                    return;
                }
                
                // Destroy existing calendar if it exists
                if (pickupCalel._calendar) {
                    console.log('Destroying existing pickup calendar');
                    pickupCalel._calendar.destroy();
                }
                
                const pickupCal = new FullCalendar.Calendar(pickupCalel, {
                    initialView: 'dayGridMonth',
                    headerToolbar: { left: 'prev,next', center: 'title', right: '' },
                    height: 'auto',
                    contentHeight: 'auto',
                    selectable: true,
                    selectMirror: true,
                    dayCellDidMount: function(info) {
                        const dateStr = info.date.toISOString().split('T')[0];
                        if (bookedDates.includes(dateStr)) {
                            info.el.style.backgroundColor = '#fca5a5';
                            info.el.style.opacity = '0.6';
                            info.el.style.pointerEvents = 'none';
                            info.el.style.cursor = 'not-allowed';
                        }
                    },
                    dateClick: function(info) {
                        const dateStr = info.dateStr;
                        if (bookedDates.includes(dateStr)) {
                            const bookingInfo = bookedDatesInfo[dateStr];
                            if (bookingInfo) {
                                alert('📅 BOOKED DATE\n\nPerson: ' + bookingInfo.customer + '\nDuration: ' + bookingInfo.duration + '\nStatus: ' + bookingInfo.status);
                            } else {
                                alert('This date is booked.');
                            }
                        }
                    },
                    select: function(info) {
                        const dateStr = info.startStr;
                        if (!bookedDates.includes(dateStr)) {
                            console.log('Pickup date selected:', info.startStr);
                            const pickupInput = document.getElementById('pickup_date_value');
                            pickupInput.value = info.startStr;
                            console.log('✅ Pickup date input value set:', pickupInput.value);
                            console.log('Verify: ' + (pickupInput.value === dateStr ? 'SUCCESS' : 'FAILED'));
                            document.getElementById('pickup_text').innerText = new Date(info.startStr).toLocaleDateString();
                            console.log('Calling calculateTotalCost from pickup select');
                            setTimeout(() => {
                                calculateTotalCost();
                            }, 50);
                        }
                    }
                });
                pickupCal.render();
                console.log('Pickup calendar rendered successfully');
                pickupCalel._calendar = pickupCal;

                // Initialize Return Calendar
                const returnCalel = document.getElementById('return-calendar');
                console.log('Return calendar element:', returnCalel);
                
                if (!returnCalel) {
                    console.error('ERROR: return-calendar element not found!');
                    return;
                }
                
                // Destroy existing calendar if it exists
                if (returnCalel._calendar) {
                    console.log('Destroying existing return calendar');
                    returnCalel._calendar.destroy();
                }
                
                console.log('Creating new return calendar');
                const returnCal = new FullCalendar.Calendar(returnCalel, {
                    initialView: 'dayGridMonth',
                    headerToolbar: { left: 'prev,next', center: 'title', right: '' },
                    height: 'auto',
                    contentHeight: 'auto',
                    selectable: true,
                    selectMirror: true,
                    dayCellDidMount: function(info) {
                        const dateStr = info.date.toISOString().split('T')[0];
                        if (bookedDates.includes(dateStr)) {
                            info.el.style.backgroundColor = '#fca5a5';
                            info.el.style.opacity = '0.6';
                            info.el.style.pointerEvents = 'none';
                            info.el.style.cursor = 'not-allowed';
                        }
                    },
                    dateClick: function(info) {
                        const dateStr = info.dateStr;
                        if (bookedDates.includes(dateStr)) {
                            const bookingInfo = bookedDatesInfo[dateStr];
                            if (bookingInfo) {
                                alert('📅 BOOKED DATE\n\nPerson: ' + bookingInfo.customer + '\nDuration: ' + bookingInfo.duration + '\nStatus: ' + bookingInfo.status);
                            } else {
                                alert('This date is booked.');
                            }
                        }
                    },
                    select: function(info) {
                        const dateStr = info.startStr;
                        if (!bookedDates.includes(dateStr)) {
                            console.log('Return date selected:', info.startStr);
                            const returnInput = document.getElementById('return_date_value');
                            returnInput.value = info.startStr;
                            console.log('✅ Return date input value set:', returnInput.value);
                            console.log('Verify: ' + (returnInput.value === dateStr ? 'SUCCESS' : 'FAILED'));
                            document.getElementById('return_text').innerText = new Date(info.startStr).toLocaleDateString();
                            console.log('Calling calculateTotalCost from return select');
                            setTimeout(() => {
                                calculateTotalCost();
                            }, 50);
                        }
                    }
                });
                returnCal.render();
                console.log('Return calendar rendered successfully');
                returnCalel._calendar = returnCal;
                console.log('=== Calendars initialized successfully ===');
                
                // Trigger calculation in case dates were already set
                setTimeout(() => {
                    console.log('🔄 Triggering delayed calculation after calendar init');
                    calculateTotalCost();
                }, 500);
            })
            .catch(error => {
                console.error('ERROR fetching booked dates:', error);
                alert('Failed to load calendar: ' + error.message);
            });
    }

    function calculateTotalCost() {
        console.log('⚙️ calculateTotalCost STARTED');
        
        // Get vehicle and its tiered rates
        const vehicleSelect = document.getElementById('vehicle_select');
        if (!vehicleSelect) {
            console.log('❌ Vehicle select not found');
            return;
        }
        
        // Get tiered rates from data attribute, fallback to defaults
        const selectedOption = vehicleSelect.options[vehicleSelect.selectedIndex];
        const hourlyRatesJSON = selectedOption?.dataset.rates || '{"1":10,"3":18,"5":25,"7":31,"9":36,"12":40,"24":43}';
        let hourlyRates = {};
        
        try {
            hourlyRates = JSON.parse(hourlyRatesJSON);
        } catch (e) {
            hourlyRates = {"1":10,"3":18,"5":25,"7":31,"9":36,"12":40,"24":43};
        }
        
        console.log('📊 Hourly Rates:', hourlyRates);
        
        // Get dates and times - use hidden input values from calendar
        const pickupDate = document.getElementById('pickup_date_value')?.value || '';
        const pickupTime = document.getElementById('pickup_time')?.value || '';
        const returnDate = document.getElementById('return_date_value')?.value || '';
        const returnTime = document.getElementById('return_time')?.value || '';
        
        console.log('📋 Inputs:', { pickupDate, pickupTime, returnDate, returnTime });
        
        // If no vehicle selected
        if (!selectedOption || !selectedOption.value) {
            console.log('⏳ Waiting: No vehicle selected');
            const costDisplay = document.getElementById('cost_display');
            if (costDisplay) costDisplay.innerText = 'Select vehicle';
            return;
        }
        
        // If missing dates, show waiting message
        if (!pickupDate || !returnDate) {
            console.log('⏳ Waiting: Missing dates - pickup:', pickupDate, 'return:', returnDate);
            const durationDisplay = document.getElementById('duration_display');
            if (durationDisplay) durationDisplay.innerText = 'Select dates';
            return;
        }
        
        // If missing times, show waiting message
        if (!pickupTime || !returnTime) {
            console.log('⏳ Waiting: Missing times - pickup:', pickupTime, 'return:', returnTime);
            const costDisplay = document.getElementById('cost_display');
            if (costDisplay) costDisplay.innerText = 'Select times';
            return;
        }
        
        // All fields present - calculate
        console.log('✅ All fields present, calculating...');
        
        // Create datetime objects (using ISO 8601 format)
        const pickupDateTime = new Date(pickupDate + 'T' + pickupTime);
        const returnDateTime = new Date(returnDate + 'T' + returnTime);
        
        console.log('📅 DateTime objects:', { pickupDateTime, returnDateTime });
        
        // Calculate hours difference (in milliseconds)
        const timeDiff = returnDateTime - pickupDateTime;
        
        console.log('⏱️ Time difference (ms):', timeDiff);
        
        if (timeDiff <= 0) {
            console.warn('⚠️ Return must be after pickup');
            const durationDisplay = document.getElementById('duration_display');
            const costDisplay = document.getElementById('cost_display');
            
            if (durationDisplay) durationDisplay.innerText = '-- hours';
            if (costDisplay) costDisplay.innerText = 'Invalid dates';
            return;
        }
        
        // Convert to hours (full hours only, rounded up)
        let totalHours = Math.ceil(timeDiff / (1000 * 60 * 60));
        if (totalHours < 1) totalHours = 1;
        
        console.log('🕐 Calculated hours:', totalHours);
        
        // Calculate rental cost based on tiered pricing
        let rentalCost = 0;
        let remainingHours = totalHours;
        
        // Full days (24 hours)
        if (remainingHours >= 24) {
            const fullDays = Math.floor(remainingHours / 24);
            rentalCost += fullDays * (hourlyRates['24'] || 43);
            remainingHours = remainingHours % 24;
            console.log(`📦 ${fullDays} full day(s): RM ${fullDays * (hourlyRates['24'] || 43)}`);
        }
        
        // Remaining hours tier
        if (remainingHours > 0) {
            let tierCost = 0;
            if (remainingHours <= 1) {
                tierCost = hourlyRates['1'] || 10;
            } else if (remainingHours <= 3) {
                tierCost = hourlyRates['3'] || 18;
            } else if (remainingHours <= 5) {
                tierCost = hourlyRates['5'] || 25;
            } else if (remainingHours <= 7) {
                tierCost = hourlyRates['7'] || 31;
            } else if (remainingHours <= 9) {
                tierCost = hourlyRates['9'] || 36;
            } else if (remainingHours <= 12) {
                tierCost = hourlyRates['12'] || 40;
            } else {
                tierCost = hourlyRates['24'] || 43;
            }
            rentalCost += tierCost;
            console.log(`⏱️ ${remainingHours} remaining hour(s): RM ${tierCost}`);
        }
        
        // Display results
        const durationDisplay = document.getElementById('duration_display');
        const costDisplay = document.getElementById('cost_display');
        
        if (durationDisplay) durationDisplay.innerText = totalHours + ' hour(s)';
        if (costDisplay) costDisplay.innerText = 'RM ' + rentalCost.toFixed(2);
        
        console.log('✅ FINAL RESULT: Hours:', totalHours, 'Rental Cost:', rentalCost);
    }

    function openCreateBookingModal() {
        console.log('=== openCreateBookingModal called ===');
        document.getElementById('create-booking-modal').classList.remove('hidden');
        
        // Check if modal is now visible
        const modal = document.getElementById('create-booking-modal');
        console.log('Modal element:', modal);
        console.log('Modal classes:', modal.className);
        console.log('Modal display:', window.getComputedStyle(modal).display);
        
        // Initialize the form
        document.getElementById('create-booking-form').reset();
        // Ensure external is selected
        document.getElementById('type_external').checked = true;
        updateBookingForm();
        // Reset cost display
        document.getElementById('duration_display').innerText = '-- hours';
        document.getElementById('cost_display').innerText = 'RM 0.00';
        document.getElementById('total_amount').value = '';
        
        // Check if calendar divs exist
        const pickupCal = document.getElementById('pickup-calendar');
        const returnCal = document.getElementById('return-calendar');
        console.log('Pickup calendar div:', pickupCal);
        console.log('Return calendar div:', returnCal);
        console.log('Pickup calendar computed display:', pickupCal ? window.getComputedStyle(pickupCal).display : 'N/A');
        console.log('Return calendar computed display:', returnCal ? window.getComputedStyle(returnCal).display : 'N/A');
        
        // Test calculation function
        console.log('🧪 Testing calculateTotalCost function exists and can be called');
        setTimeout(() => {
            console.log('✅ calculateTotalCost function is callable');
        }, 100);
        
        console.log('Create booking modal opened');
    }

    // Booking Type Management
    function setBookingType(type) {
        if (type === 'external') {
            document.getElementById('type_external').checked = true;
            updateBookingForm();
        }
    }

    function updateBookingForm() {
        // Always show external company fields (it's the only option)
        const externalField = document.getElementById('external-field');
        externalField.classList.remove('hidden');
    }

    function handleFormSubmit(event) {
        event.preventDefault();
        console.log('Form submit handler called');
        
        const bookingType = document.querySelector('input[name="booking_type"]:checked').value;
        const form = document.getElementById('create-booking-form');
        let isValid = true;
        let errorMsg = '';
        
        console.log('Booking type:', bookingType);
        
        // Validate external company (only option)
        const externalCompany = document.getElementById('external_company').value;
        console.log('External company:', externalCompany);
        if (!externalCompany) {
            errorMsg = 'Please select an external company';
            isValid = false;
        }

        if (!isValid) {
            alert(errorMsg);
            return false;
        }

        // Validate vehicle
        const vehicleSelect = document.querySelector('select[name="vehicle_id"]');
        console.log('Vehicle select:', vehicleSelect);
        if (!vehicleSelect || !vehicleSelect.value) {
            alert('Please select a vehicle');
            return false;
        }

        // Validate dates and times - use hidden input values
        const pickupDate = document.getElementById('pickup_date_value')?.value;
        const pickupTime = document.querySelector('select[name="pickup_time"]')?.value;
        const returnDate = document.getElementById('return_date_value')?.value;
        const returnTime = document.querySelector('select[name="return_time"]')?.value;

        if (!pickupDate || !pickupTime) {
            alert('Please fill in pickup date and time');
            return false;
        }

        if (!returnDate || !returnTime) {
            alert('Please fill in return date and time');
            return false;
        }

        // Create datetime objects for validation
        const pickupDateTime = new Date(pickupDate + 'T' + pickupTime);
        const returnDateTime = new Date(returnDate + 'T' + returnTime);

        // Validate dates logic
        if (pickupDateTime >= returnDateTime) {
            alert('Return date/time must be after pickup date/time');
            return false;
        }

        // Validate total amount
        const totalAmountInput = document.querySelector('input[name="total_amount"]');
        if (!totalAmountInput || !totalAmountInput.value.trim()) {
            alert('Please enter total amount');
            return false;
        }

        // Set the hidden values into the form (they should already be set from calendar)
        const pickupDateInput = document.querySelector('input[name="pickup_date"]');
        const returnDateInput = document.querySelector('input[name="return_date"]');
        
        if (pickupDateInput) pickupDateInput.value = pickupDate;
        if (returnDateInput) returnDateInput.value = returnDate;

        // All validations passed, submit the form via AJAX
        console.log('All validations passed, submitting form via AJAX');
        
        const formData = new FormData(form);
        
        // Log all form data
        console.log('Form data being sent:');
        for (let [key, value] of formData.entries()) {
            console.log(`${key}: ${value}`);
        }
        
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        })
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response ok:', response.ok);
            
            if (response.status === 422 || response.status === 500) {
                // Error response - try to parse as JSON
                return response.json().then(data => {
                    console.log('Error response data:', data);
                    if (data.errors) {
                        // Validation error
                        let errorMsg = 'Validation errors:\n';
                        for (let field in data.errors) {
                            errorMsg += `${field}: ${data.errors[field].join(', ')}\n`;
                        }
                        alert(errorMsg);
                    } else {
                        // Server error
                        alert('Error: ' + (data.message || data.error || 'Unknown error'));
                    }
                    return false;
                }).catch(e => {
                    console.log('Failed to parse error as JSON:', e);
                    return response.text().then(text => {
                        alert('Error (could not parse): ' + text.substring(0, 300));
                        return false;
                    });
                });
            } else if (response.ok) {
                return response.json().then(data => {
                    console.log('Success response:', data);
                    if (data.success) {
                        alert('Booking created successfully!');
                        document.getElementById('create-booking-modal').classList.add('hidden');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        alert('Error: ' + (data.message || 'Unknown error'));
                    }
                    return data.success;
                }).catch(e => {
                    console.log('Failed to parse success as JSON:', e);
                    alert('Booking may have been created but response parsing failed');
                    setTimeout(() => location.reload(), 1000);
                    return true;
                });
            } else {
                // Other status codes
                return response.text().then(text => {
                    console.log('Unexpected response:', text.substring(0, 300));
                    alert('Server error (Status ' + response.status + '): ' + text.substring(0, 200));
                    return false;
                });
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            alert('Error submitting form: ' + error.message);
        });
    }

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

    function toggleTypeDropdown() {
        const menu = document.getElementById('typeMenu');
        const arrow = document.getElementById('typeArrow');
        
        if (menu.classList.contains('hidden')) {
            menu.classList.remove('hidden');
            menu.classList.add('animate-fade-in-down');
            if(arrow) arrow.style.transform = 'rotate(180deg)';
        } else {
            menu.classList.add('hidden');
            if(typeArrow) arrow.style.transform = 'rotate(0deg)';
        }
    }

    function selectStatus(value) {
        document.getElementById('statusInput').value = value;
        document.getElementById('filterForm').submit();
    }

    function selectType(value) {
        document.getElementById('typeInput').value = value;
        document.getElementById('typeLabel').innerText = {
            'all': 'All Bookings',
            'customer': 'Customer Bookings',
            'external': 'External Bookings'
        }[value] || 'All Bookings';
        document.getElementById('filterForm').submit();
    }

    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('customDropdown');
        const typeDropdown = document.getElementById('typeDropdown');
        const menu = document.getElementById('dropdownMenu');
        const typeMenu = document.getElementById('typeMenu');
        const arrow = document.getElementById('dropdownArrow');
        const typeArrow = document.getElementById('typeArrow');

        if (dropdown && !dropdown.contains(event.target)) {
            menu?.classList.add('hidden');
            if(arrow) arrow.style.transform = 'rotate(0deg)';
        }

        if (typeDropdown && !typeDropdown.contains(event.target)) {
            typeMenu?.classList.add('hidden');
            if(typeArrow) typeArrow.style.transform = 'rotate(0deg)';
        }
    });
</script>
@endsection