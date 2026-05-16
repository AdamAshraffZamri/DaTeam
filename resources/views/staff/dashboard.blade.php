@extends('layouts.staff')

@section('content')
{{-- External Scripts --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* --- Custom Timeline Styling --- */
    .timeline-wrapper { position: relative; width: 100%; overflow-x: auto; overflow-y: auto; max-height: 600px; }
    .timeline-cell { width: 90px; min-width: 90px; flex-shrink: 0; }    
    
    /* Sticky Elements & Colors */
    .header-row { position: sticky; top: 0; z-index: 30; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
    .vehicle-col { 
        width: 120px; 
        min-width: 120px; 
        position: sticky; 
        left: 0; /* Makes it stick to the left */
        z-index: 20; 
        border-right: 2px solid #fed7aa; /* orange-200 border */
    }
    .header-col-sticky { z-index: 40; }
    
    /* Consistent Plate Box */
    .plate-badge {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 86px;
        height: 32px;
        background: white;
        border: 2px solid #e2e8f0;
        border-radius: 6px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }

    /* Event Blocks - Taller & Stackable */
    .timeline-event { 
        position: absolute; 
        border-radius: 6px; 
        cursor: pointer; 
        transition: filter 0.1s, transform 0.1s;
        z-index: 10;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 4px 8px;
        border: 1px solid rgba(0,0,0,0.1);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .timeline-event:hover { filter: brightness(1.1); transform: translateY(-1px); z-index: 50; }

    /* --- Calendar Styling --- */
    .fc-theme-standard td, .fc-theme-standard th { border-color: #f1f5f9; }
    .fc-col-header-cell-cushion { text-transform: uppercase; font-size: 10px; font-weight: 800; color: #94a3b8; padding: 12px 0; letter-spacing: 0.05em; }
    .fc-daygrid-day-number { color: #475569; font-size: 11px; font-weight: 700; padding: 8px; }
    
    /* Today Highlight */
    .fc-day-today { background: #fff7ed !important; } /* Orange-50 */
    .fc-day-today .fc-daygrid-day-number { background: #f97316; color: white; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; margin: 4px; }
    
    /* Event Styling - UPDATED FOR LIGHTER TEXT */
    .fc-event { 
        border: none; 
        border-radius: 4px; 
        padding: 2px 4px; 
        font-size: 12px; 
        font-weight: 800; /* Reduced from 700 to 600 */
        box-shadow: 0 1px 2px rgba(0,0,0,0.05); 
        margin-bottom: 2px;
        cursor: pointer;
        transition: transform 0.1s;
        
        /* Force Colors */
        background-color: #eff6ff !important; /* Blue-50 */
        border-left: 3px solid #3b82f6 !important; /* Blue-500 */
        
        /* CHANGE THIS: Lighter Text Color */
        color: #475569 !important; /* Slate-600 (Softer Gray/Blue) */
    }
    
    /* Ensure inner text inherits the lighter color */
    .fc-event-title, .fc-event-time {
        color: #475569 !important; /* Slate-600 */
        font-weight: 600 !important;
    }

    .fc-event:hover { transform: scale(1.02); z-index: 50; }
    
    /* Custom Scrollbar */
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #9ca3af; }

    [x-cloak] { display: none !important; }
</style>

<div class="min-h-screen bg-slate-100 rounded-2xl p-6" 
     x-data="{ 
        currentTab: 'pickups', 
        {{-- Search Modal Logic --}}
        showResultsModal: {{ (request()->has('pickup_date') || (isset($searchResults) && $searchResults->count() > 0)) ? 'true' : 'false' }},
        showPricingModal: false,

        time: new Date().toLocaleTimeString('en-US', { hour: 'numeric', minute: 'numeric', second: 'numeric', hour12: true }),
        date: new Date().toLocaleDateString('en-US', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }),
        
        pDate: '{{ request('pickup_date') }}', 
        rDate: '{{ request('return_date') }}',
        pTime: '{{ request('pickup_time', '09:00') }}',
        rTime: '{{ request('return_time', '09:00') }}',

        init() {
            setInterval(() => {
                this.time = new Date().toLocaleTimeString('en-US', { hour: 'numeric', minute: 'numeric', second: 'numeric', hour12: true });
            }, 1000);
        },
        
        setToday() {
            const today = new Date();
            const formatted = today.toISOString().split('T')[0];
            this.pDate = formatted;
            this.rDate = formatted;
            this.pTime = '00:00';
            this.rTime = '23:00';
        }
     }">

    <div class="max-w-7xl mx-auto space-y-6">

        {{-- 1. HEADER BAR --}}
        <div class="flex flex-col md:flex-row justify-between items-end mb-8 gap-4">
            <div>
                <h1 class="text-4xl font-black text-gray-900">Dashboard</h1>
                <p class="text-gray-500 mt-1 text-sm">Welcome back, <span class="text-orange-600 font-bold">{{ Auth::guard('staff')->user()->name }}</span></p>
            </div>
            
            <div class="flex items-center gap-3">
                <div class="hidden md:block text-right bg-white px-5 py-2.5 rounded-2xl border border-gray-200 shadow-sm">
                    <p class="text-sm font-bold-30 font-black text-gray-900" x-text="time"></p>
                    <p class="text-[12px] text-gray-400 uppercase tracking-wider" x-text="date"></p>
                </div>
                
                {{-- Quick Actions --}}
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" @click.away="open = false" class="bg-gray-700 hover:bg-orange-600 text-white px-6 py-3.5 rounded-2xl font-bold text-xs shadow-lg transition-all transform hover:scale-105 flex items-center gap-2 shrink-0 whitespace-nowrap">
                        <i class="fas fa-bolt"></i> <span>Quick Actions</span> 
                        <i class="fas fa-chevron-down ml-1 text-[10px] opacity-70 transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open" 
                         class="absolute right-0 mt-3 w-56 bg-white rounded-2xl shadow-xl border border-gray-100 z-50 overflow-hidden" 
                         style="display: none;">
                        <div class="px-4 py-3 border-b border-gray-50 bg-gray-50/50">
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Operations</p>
                        </div>
                        <a href="{{ route('staff.fleet.create') }}" class="flex items-center gap-3 px-4 py-3 hover:bg-orange-50 transition-colors group">
                            <div class="w-8 h-8 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center"><i class="fas fa-car text-xs"></i></div>
                            <span class="text-xs font-bold text-gray-700 group-hover:text-orange-700">Add Vehicle</span>
                        </a>
                        <a href="{{ route('staff.bookings.index') }}" class="flex items-center gap-3 px-4 py-3 hover:bg-blue-50 transition-colors group">
                            <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center"><i class="fas fa-calendar-check text-xs"></i></div>
                            <span class="text-xs font-bold text-gray-700 group-hover:text-blue-700">Bookings</span>
                        </a>
                    </div>
                </div>

                {{-- [NEW] DYNAMIC PRICING BUTTON (Updated) --}}
                <button @click="showPricingModal = true" 
                    class="group flex items-center justify-center px-6 py-3.5 hover:pr-6 rounded-2xl 
                        bg-gradient-to-br from-red-700 via-red-600 to-orange-600 
                        text-white shadow-lg shadow-red-600/30 border border-white/10
                        transition-all duration-500 ease-out overflow-hidden">
                    
                    {{-- Icon --}}
                    <i class="fas fa-tags text-sm group-hover:rotate-12 transition-transform duration-300"></i>
                    
                    {{-- Text (Hidden by default, expands on hover) --}}
                    <span class="max-w-0 overflow-hidden opacity-0 group-hover:max-w-[150px] group-hover:opacity-100 group-hover:ml-3 
                                transition-all duration-500 ease-out whitespace-nowrap font-bold text-xs tracking-wide">
                        Dynamic Pricing
                    </span>
                </button>

            </div>

        </div>

        {{-- 2. METRICS GRID --}}
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
            
            {{-- Card 1: Pending Customers --}}
            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm hover:border-purple-200 transition-colors group relative">
                <a href="{{ route('staff.customers.index', ['search' => '', 'status' => 'pending']) }}" class="absolute inset-0 z-10"></a>
                
                {{-- Notification Trigger --}}
                @if($pendingCustomersCount > 0)
                    <span class="absolute top-3 right-3 flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                    </span>
                @endif

                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Pending Customers</p>
                        <h3 class="text-2xl font-black {{ $pendingCustomersCount > 0 ? 'text-red-600' : 'text-slate-800' }} mt-1">{{ $pendingCustomersCount }}</h3>
                    </div>
                    <div class="p-2.5 bg-purple-100 text-purple-700 rounded-xl group-hover:bg-purple-600 group-hover:text-white transition-all shadow-sm">
                        <i class="fas fa-user-clock text-lg"></i>
                    </div>
                </div>
                <div class="mt-3 text-sm font-bold {{ $pendingCustomersCount > 0 ? 'text-red-600 animate-pulse' : 'text-slate-400' }}">Needs verification</div>
            </div>

            {{-- Card 2: Deposit Paid Bookings --}}
            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm hover:border-blue-200 transition-colors group relative">
                <a href="{{ route('staff.bookings.index', ['search' => '', 'status' => 'Deposit Paid']) }}" class="absolute inset-0 z-10"></a>
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Deposit Paid</p>
                        <h3 class="text-2xl font-black text-slate-800 mt-1">{{ $depositPaidCount ?? 0 }}</h3>
                    </div>
                    <div class="p-2.5 bg-blue-100 text-blue-700 rounded-xl group-hover:bg-blue-600 group-hover:text-white transition-all shadow-sm">
                        <i class="fas fa-file-invoice-dollar text-lg"></i>
                    </div>
                </div>
                <div class="mt-3 text-sm font-bold text-blue-600">Awaiting full payment</div>
            </div>

            {{-- Card 3: Fully Paid Bookings --}}
            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm hover:border-green-200 transition-colors group relative">
                <a href="{{ route('staff.bookings.index', ['search' => '', 'status' => 'Confirmed']) }}" class="absolute inset-0 z-10"></a>
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Confirmed Booking</p>
                        <h3 class="text-2xl font-black text-slate-800 mt-1">{{ $fullyPaidCount ?? 0 }}</h3>
                    </div>
                    <div class="p-2.5 bg-green-100 text-green-700 rounded-xl group-hover:bg-green-600 group-hover:text-white transition-all shadow-sm">
                        <i class="fas fa-check-double text-lg"></i>
                    </div>
                </div>
                <div class="mt-3 text-sm font-bold text-green-600">Ready for pickup</div>
            </div>

            {{-- NEW CARD: Deposit Not Updated --}}
            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm hover:border-indigo-200 transition-colors group relative">
                <a href="{{ route('staff.finance.deposits', ['status' => 'not_updated']) }}" class="absolute inset-0 z-10"></a>
                
                {{-- Notification Trigger --}}
                @if($counts['not_updated'] > 0)
                    <span class="absolute top-3 right-3 flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
                    </span>
                @endif

                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Deposit Update</p>
                        <h3 class="text-2xl font-black {{ $counts['not_updated'] > 0 ? 'text-indigo-600' : 'text-slate-800' }} mt-1">{{ $counts['not_updated'] ?? 0 }}</h3>
                    </div>
                    <div class="p-2.5 bg-indigo-100 text-indigo-700 rounded-xl group-hover:bg-indigo-600 group-hover:text-white transition-all shadow-sm">
                        <i class="fas fa-hand-holding-usd text-lg"></i>
                    </div>
                </div>
                <div class="mt-3 text-sm font-bold {{ $counts['not_updated'] > 0 ? 'text-indigo-600 animate-pulse' : 'text-slate-400' }}">Pending update</div>
            </div>

            {{-- Card 4: Submitted Booking --}}
            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm hover:border-orange-200 transition-colors group relative">
                <a href="{{ route('staff.bookings.index', ['search' => '', 'status' => 'Submitted']) }}" class="absolute inset-0 z-10"></a>
                
                {{-- Notification Trigger --}}
                @if($pendingBookingsCount > 0)
                    <span class="absolute top-3 right-3 flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-orange-500"></span>
                    </span>
                @endif

                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Submitted Booking</p>
                        <h3 class="text-2xl font-black {{ $pendingBookingsCount > 0 ? 'text-orange-600' : 'text-slate-800' }} mt-1">{{ $pendingBookingsCount }}</h3>
                    </div>
                    <div class="p-2.5 bg-orange-100 text-orange-700 rounded-xl group-hover:bg-orange-600 group-hover:text-white transition-all shadow-sm">
                        <i class="fas fa-hourglass-half text-lg"></i>
                    </div>
                </div>
                <div class="mt-3 text-sm font-bold {{ $pendingBookingsCount > 0 ? 'text-orange-600 animate-pulse' : 'text-slate-400' }}">Needs verification</div>
            </div>
            
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 items-start">
            
            {{-- 3. LEFT COLUMN: CHARTS & CALENDAR --}}
            <div class="xl:col-span-2 space-y-6">
                
               {{-- Chart Section --}}
                <div class="bg-white p-4 sm:p-5 rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                        {{-- Left: Title & Filter --}}
                        <div class="flex items-center gap-3 w-full sm:w-auto">
                            <h2 class="text-lg font-bold text-slate-800 truncate">Performance</h2>
                            <form action="{{ route('staff.dashboard') }}" method="GET">
                                <select name="chart_period" onchange="this.form.submit()" 
                                        class="text-[12px] font-bold bg-slate-50 border border-slate-200 rounded-lg px-2 py-1 focus:border-orange-500 focus:ring-0 cursor-pointer text-slate-600 outline-none">
                                    <option value="daily" {{ request('chart_period') == 'daily' ? 'selected' : '' }}>Daily</option>
                                    <option value="weekly" {{ request('chart_period') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                    <option value="monthly" {{ request('chart_period') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                </select>
                            </form>
                        </div>

                        {{-- Right: Legend Labels --}}
                        <div class="flex gap-4 w-full sm:w-auto justify-start sm:justify-end border-t sm:border-t-0 pt-3 sm:pt-0 border-slate-100">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-orange-500 shadow-sm"></span>
                                <span class="text-[10px] font-bold text-slate-500 uppercase">Revenue</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-blue-500 shadow-sm"></span>
                                <span class="text-[10px] font-bold text-slate-500 uppercase">Bookings</span>
                            </div>
                        </div>
                    </div>

                    {{-- Chart Canvas: Increased height for mobile visibility --}}
                    <div class="relative h-64 sm:h-72 w-full">
                        <canvas id="dashboardChart"></canvas>
                    </div>
                </div>

                {{-- NEW TIMELINE SECTION --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm flex flex-col">
                    
                    {{-- Card Header --}}
                    <div class="p-5 md:p-6 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 shrink-0 z-50">
                        <div>
                            <h2 class="text-lg font-bold text-slate-800 truncate">Booking Calendar</h2>
                        </div>
                        
                        {{-- Month Filter --}}
                        <form action="{{ route('staff.dashboard') }}" method="GET" class="flex items-center gap-3">
                            <label class="text-sm font-bold text-slate-500">Month:</label>
                            <input type="month" name="calendar_month" 
                                   value="{{ request('calendar_month', date('Y-m')) }}" 
                                   onchange="this.form.submit()"
                                   class="bg-slate-50 border border-slate-200 text-slate-700 text-sm font-bold rounded-2xl px-3 py-1.5 focus:border-slate-400 outline-none cursor-pointer shadow-sm transition-colors hover:bg-slate-100 min-w-[140px]">
                        </form>
                    </div>

                    {{-- Inner Padded Timeline Area --}}
                    <div class="p-5"> 
                        {{-- Rectangular shape with curved border --}}
                        <div class="border border-slate-200 rounded-lg overflow-hidden shadow-sm bg-slate-50">
                            <div class="timeline-wrapper custom-scrollbar" id="timelineScroll">
                                <div id="timelineGrid" class="min-w-max relative flex flex-col">
                                    </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- 4. RIGHT COLUMN: SIDEBAR --}}
            <div class="xl:col-span-1 space-y-6">
                
                {{-- 1. AVAILABILITY CHECKER --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    {{-- Header: Consistent with Performance/Calendar Style --}}
                    <div class="p-5 border-b border-gray-100">
                        <h2 class="text-lg font-bold text-slate-800 truncate">Vehicle Availability</h2>
                    </div>

                    <div class="p-5">
                        <form id="availabilityForm" class="space-y-5">
                            {{-- Input Container: Colored Background --}}
                            <div class="bg-slate-50 border border-slate-100 rounded-2xl p-4 space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 block">Pickup Range</label>
                                        <input type="date" name="pickup_date" class="w-full bg-white border border-slate-200 text-sm font-bold rounded-xl px-3 py-2 focus:border-orange-500 outline-none shadow-sm" required>
                                        <select name="pickup_time" class="w-full mt-2 bg-white border border-slate-200 text-sm font-bold rounded-xl px-3 py-2 shadow-sm">
                                            @for($i = 0; $i < 24; $i++) <option value="{{ sprintf('%02d:00', $i) }}">{{ sprintf('%02d:00', $i) }}</option> @endfor
                                        </select>
                                    </div>
                                    <div>
                                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 block">Return Range</label>
                                        <input type="date" name="return_date" class="w-full bg-white border border-slate-200 text-sm font-bold rounded-xl px-3 py-2 focus:border-orange-500 outline-none shadow-sm" required>
                                        <select name="return_time" class="w-full mt-2 bg-white border border-slate-200 text-sm font-bold rounded-xl px-3 py-2 shadow-sm">
                                            @for($i = 0; $i < 24; $i++) <option value="{{ sprintf('%02d:00', $i) }}">{{ sprintf('%02d:00', $i) }}</option> @endfor
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- Results List --}}
                            <div id="availabilityResults" class="space-y-2 max-h-[400px] overflow-y-auto pr-1">
                                {{-- Results injected here --}}
                            </div>
                        </form>
                    </div>
                </div>

                {{-- 2. DAILY OPERATIONS (Card with Scrollable Content) --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm flex flex-col h-[600px] overflow-hidden">
                    
                    {{-- Fixed Header --}}
                    <div class="p-6 border-b border-gray-100 shrink-0 bg-white z-10 flex flex-col gap-4">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-bold text-slate-800 truncate">Daily Operations</h2>
                            <span class="bg-slate-100 text-slate-600 px-2 py-1 rounded text-[10px] font-bold">{{ $pickupsToday->count() + $returnsToday->count() }} Tasks</span>
                        </div>

                        {{-- Tabs --}}
                        <div class="bg-gray-100 rounded-full p-1 flex">
                            <button @click="currentTab = 'pickups'" :class="currentTab === 'pickups' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700'" class="flex-1 py-2 rounded-full text-xs font-bold transition-all">Pickups</button>
                            <button @click="currentTab = 'returns'" :class="currentTab === 'returns' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700'" class="flex-1 py-2 rounded-full text-xs font-bold transition-all">Returns</button>
                        </div>
                    </div>

                    {{-- Scrollable List Content --}}
                    <div class="flex-1 overflow-y-auto p-6 custom-scrollbar bg-white">
                        
                        {{-- Pickups --}}
                        <div x-show="currentTab === 'pickups'" class="space-y-3">
                            @forelse($pickupsToday as $booking)
                                @include('staff.partials.dashboard-booking-row', ['booking' => $booking, 'type' => 'pickup'])
                            @empty
                                <div class="text-center py-12 text-slate-400">
                                    <i class="fas fa-check-circle text-2xl mb-2 opacity-30"></i>
                                    <p class="text-xs">No pickups today.</p>
                                </div>
                            @endforelse
                        </div>

                        {{-- Returns --}}
                        <div x-show="currentTab === 'returns'" class="space-y-3" style="display: none;">
                            @forelse($returnsToday as $booking)
                                @include('staff.partials.dashboard-booking-row', ['booking' => $booking, 'type' => 'return'])
                            @empty
                                <div class="text-center py-12 text-slate-400">
                                    <i class="fas fa-check-circle text-2xl mb-2 opacity-30"></i>
                                    <p class="text-xs">No returns today.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Fixed Footer (Financials) --}}
                    <div class="p-6 border-t border-gray-100 shrink-0 bg-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Collected Today</p>
                                <h4 class="text-xl font-black text-slate-800 mt-0.5">RM {{ number_format($todayRevenue) }}</h4>
                            </div>
                            <div class="w-10 h-10 rounded-full bg-green-50 flex items-center justify-center text-green-600 border border-green-100">
                                <i class="fas fa-coins"></i>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- [NEW] DYNAMIC PRICING MODAL (PREMIUM & DELETE SUPPORT) --}}
    <div x-show="showPricingModal" 
        class="fixed inset-0 z-[80] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm px-4"
        x-transition.opacity
        x-cloak>
        
        <div class="bg-orange-50 rounded-[2rem] shadow-2xl w-full max-w-lg relative overflow-hidden flex flex-col max-h-[90vh]" @click.away="showPricingModal = false">
            
            {{-- Header --}}
            <div class="px-8 py-6 border-b border-gray-100 flex justify-between items-center bg-white sticky top-0 z-10">
                <div>
                    <h3 class="text-2xl font-black bg-gradient-to-r from-orange-600 to-red-600 bg-clip-text text-transparent">
                        Smart Pricing Manager
                    </h3>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">Configure Surcharges</p>
                </div>
                <button @click="showPricingModal = false" class="w-8 h-8 rounded-full bg-gray-50 text-gray-400 hover:bg-gray-100 hover:text-gray-600 flex items-center justify-center transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            {{-- Scrollable Content --}}
            <div class="p-8 overflow-y-auto custom-scrollbar space-y-6">
                
                {{-- 1. CREATE NEW RULE --}}
                <section>
                    <form action="{{ route('staff.dynamic_pricing.store') }}" method="POST" class="space-y-5">
                        @csrf
                        
                        {{-- Date Inputs --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="text-xs font-black text-gray-400 uppercase tracking-widest ml-1">Start Date</label>
                                <div class="relative">
                                    <i class="fas fa-calendar-alt absolute left-3 top-1/2 -translate-y-1/2 text-gray-300 text-xs"></i>
                                    <input type="date" name="start_date" class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-3 py-2.5 font-bold text-xs text-slate-700 focus:bg-white focus:border-orange-500 focus:ring-0 transition-colors outline-none cursor-pointer" required>
                                </div>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs font-black text-gray-400 uppercase tracking-widest ml-1">End Date</label>
                                <div class="relative">
                                    <i class="fas fa-calendar-check absolute left-3 top-1/2 -translate-y-1/2 text-gray-300 text-xs"></i>
                                    <input type="date" name="end_date" class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-3 py-2.5 font-bold text-xs text-slate-700 focus:bg-white focus:border-orange-500 focus:ring-0 transition-colors outline-none cursor-pointer" required>
                                </div>
                            </div>
                        </div>

                        {{-- Percentage Input --}}
                        <div class="space-y-1.5">
                            <label class="text-xs font-black text-gray-400 uppercase tracking-widest ml-1">Percentage Adjustment</label>
                            <div class="relative group">
                                <div class="absolute left-0 top-0 bottom-0 w-12 bg-orange-100 rounded-l-xl flex items-center justify-center border-y border-l border-orange-200 text-orange-600">
                                    <i class="fas fa-percentage text-sm"></i>
                                </div>
                                <input type="number" name="percentage" placeholder="20 / -15" min="-100" max="100" class="w-full bg-white border border-slate-200 rounded-xl pl-16 pr-4 py-3 font-black text-lg text-slate-800 focus:border-orange-500 focus:ring-0 outline-none transition-colors" required>
                                <div class="absolute right-8 top-1/2 -translate-y-1/2 text-[10px] font-bold text-gray-400 uppercase">Surcharge / Discount</div>
                            </div>
                            <p class="text-[10px] text-gray-400 ml-1">
                                Applied automatically to all vehicles during this period.
                            </p>
                        </div>

                        <button type="submit" class="w-full bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-500 hover:to-red-500 text-white py-3.5 rounded-xl font-bold text-xs uppercase tracking-widest shadow-lg shadow-orange-500/30 transition-all transform active:scale-[0.98]">
                            <i class="fas fa-plus-circle mr-2"></i> Add Pricing Rule
                        </button>
                    </form>
                </section>

                {{-- 2. ACTIVE RULES LIST --}}
                @php
                    $activeRules = \Illuminate\Support\Facades\Cache::get('dynamic_pricing_rules', []);
                @endphp

                @if(count($activeRules) > 0)
                    <div class="relative my-6">
                        <div class="absolute inset-0 flex items-center" aria-hidden="true">
                            <div class="w-full border-t border-gray-100"></div>
                        </div>
                        <div class="relative flex items-center justify-center">
                            <span class="bg-white border border-red-500 rounded-full px-3 py-1.5 text-[10px] font-black text-black uppercase tracking-widest">Active Rules</span>
                        </div>
                    </div>

                    <div class="space-y-2">
                        {{-- Loop through rules with Index Key --}}
                        {{-- array_reverse(..., true) preserves the original index keys for deletion --}}
                        @foreach(array_reverse($activeRules, true) as $index => $rule)
                        @php $isDiscount = $rule['percent'] < 0; @endphp
                        <div class="group flex items-center justify-between p-3 bg-white border border-red-300 rounded-2xl shadow-sm hover:shadow-md transition-all {{ $isDiscount ? 'hover:border-green-200' : 'hover:border-red-200' }}">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full {{ $isDiscount ? 'bg-green-50 text-green-600 border-green-100' : 'bg-red-50 text-red-600 border-red-100' }} flex items-center justify-center font-black text-[10px] border">
                                    {{ $rule['percent'] > 0 ? '+' : '' }}{{ $rule['percent'] }}%
                                </div>
                                <div>
                                    <div class="flex items-center gap-2 text-xs font-bold text-gray-800">
                                        <span>{{ \Carbon\Carbon::parse($rule['start'])->format('d M') }}</span>
                                        <i class="fas fa-arrow-right text-[9px] text-gray-600"></i>
                                        <span>{{ \Carbon\Carbon::parse($rule['end'])->format('d M') }}</span>
                                    </div>
                                    <p class="text-[9px] font-bold {{ $isDiscount ? 'text-green-400' : 'text-red-400' }} mt-0.5 uppercase tracking-wide">
                                        {{ $isDiscount ? 'Discount or Promotion' : 'High Demand Surcharge' }}
                                    </p>
                                </div>
                            </div>
                            {{-- Delete Button (Keep existing) --}}
                            <form action="{{ route('staff.dynamic_pricing.destroy', $index) }}" method="POST" onsubmit="return confirm('Remove rule?');">
                                @csrf @method('DELETE')
                                <button class="w-7 h-7 rounded-lg bg-gray-50 text-gray-400 hover:bg-red-50 hover:text-red-500 flex items-center justify-center transition"><i class="fas fa-trash-alt text-[10px]"></i></button>
                            </form>
                        </div>
                    @endforeach
                    </div>
                    
                    {{-- Clear All (Subtle at bottom) --}}
                    <div class="text-center pt-2">
                        <form action="{{ route('staff.dynamic_pricing.clear') }}" method="POST" onsubmit="return confirm('Are you sure you want to delete ALL rules?');">
                            @csrf
                            <button class="text-[11px] font-bold text-gray-400 hover:text-red-500 transition-colors decoration-dotted uppercase">
                                Reset all configurations
                            </button>
                        </form>
                    </div>
                @else
                    {{-- Empty State --}}
                    <div class="text-center py-6 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                        <i class="fas fa-tag text-slate-300 text-2xl mb-2"></i>
                        <p class="text-xs font-bold text-slate-400">No active surges.</p>
                        <p class="text-[10px] text-slate-400">Standard rates apply.</p>
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>

{{-- SCRIPT: CHART & CALENDAR --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('availabilityForm');
        const resultsContainer = document.getElementById('availabilityResults');

        // Trigger search whenever any input changes
        form.addEventListener('input', function() {
    const pD = form.querySelector('[name="pickup_date"]').value;
    const pT = form.querySelector('[name="pickup_time"]').value;
    const rD = form.querySelector('[name="return_date"]').value;
    const rT = form.querySelector('[name="return_time"]').value;

    if (!pD || !rD) return;

    // Construct full Date objects for comparison
    const pickupTotal = new Date(`${pD}T${pT}`);
    const returnTotal = new Date(`${rD}T${rT}`);
    const oneHourInMs = 60 * 60 * 1000;

    // VALIDATION: Ensure return is at least 1 hour after pickup
    if (returnTotal - pickupTotal < oneHourInMs) {
        resultsContainer.innerHTML = '<div class="p-3 text-[10px] font-bold text-red-500 bg-red-50 rounded-xl border border-red-100">Return must be at least 1 hour after pickup.</div>';
        return;
    }

    const formData = new URLSearchParams(new FormData(form)).toString();
    fetch(`{{ route('staff.dashboard') }}?${formData}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(r => r.json())
    .then(data => {
        resultsContainer.innerHTML = '';
        data.searchResults.forEach(v => {
            const div = document.createElement('div');
            div.className = `p-3 rounded-xl border flex justify-between items-center transition-all ${v.is_available ? 'bg-green-50 border-green-100' : 'bg-slate-50 border-slate-200 opacity-70'}`;
            
            div.innerHTML = `
                <div>
                    <div class="text-sm font-black ${v.is_available ? 'text-green-800' : 'text-slate-600'}">${v.plateNo}</div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase">${v.model}</div>
                </div>
                <div class="text-right">
                    ${v.is_available 
                        ? '<span class="text-[10px] font-black text-green-600 uppercase tracking-widest">Available</span>' 
                        : `<span class="text-[9px] font-black text-red-500 block leading-none mb-1 uppercase">Booked:</span>
                           <span class="text-[9px] font-bold text-slate-500 leading-tight block">${v.busy_time}</span>`
                    }
                </div>
            `;
            resultsContainer.appendChild(div);
        });
    });
});
    });

    document.addEventListener('DOMContentLoaded', function() {
        // 1. Chart Logic
        const ctx = document.getElementById('dashboardChart').getContext('2d');
        const rawRevenue = @json($chartRevenue);
        const rawBookings = @json($chartBookings);
        const cleanRevenue = rawRevenue.map(val => parseFloat(String(val).replace(/,/g, '')) || 0);
        const cleanBookings = rawBookings.map(val => parseFloat(String(val).replace(/,/g, '')) || 0);

        let gradientSales = ctx.createLinearGradient(0, 0, 0, 300);
        gradientSales.addColorStop(0, 'rgba(249, 115, 22, 0.15)');
        gradientSales.addColorStop(1, 'rgba(249, 115, 22, 0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($chartLabels),
                datasets: [
                    { label: 'Revenue', data: cleanRevenue, borderColor: '#f97316', backgroundColor: gradientSales, borderWidth: 2, pointRadius: 3, fill: true, tension: 0.4, yAxisID: 'y' },
                    { label: 'Bookings', data: cleanBookings, borderColor: '#3b82f6', backgroundColor: '#3b82f6', borderWidth: 3, pointRadius: 4, fill: false, tension: 0.4, yAxisID: 'y1' }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 10 }, color: '#94a3b8' } },
                    y: { display: true, beginAtZero: true, type: 'linear', position: 'left', grid: { borderDash: [4, 4], color: '#f1f5f9', drawBorder: false }, ticks: { font: { size: 10 }, color: '#f97316', callback: val => 'RM ' + val } },
                    y1: { display: true, type: 'linear', position: 'right', beginAtZero: true, suggestedMax: 5, grid: { drawOnChartArea: false }, ticks: { stepSize: 1, font: { size: 10 }, color: '#3b82f6' } }
                }
            }
        });

        // 2. TIMELINE LOGIC
        const rawEvents = @json($calendarEvents);
        
        // --- FIX: Filter out invalid bookings by status ---
        const timelineEvents = rawEvents.filter(ev => {
            const status = ev.extendedProps?.status?.toLowerCase();
            return status !== 'cancelled' && status !== 'invalid' && status !== 'rejected' && status !== 'deleted';
        });

        const container = document.getElementById('timelineGrid');
        const scrollContainer = document.getElementById('timelineScroll');

        // Configuration
        const CELL_WIDTH = 90; 
        
        // Month Setup based on filter
        const selectedMonthStr = '{{ request("calendar_month", date("Y-m")) }}';
        const [year, month] = selectedMonthStr.split('-');
        
        // --- FIX: Ensure we only show days for the specific month ---
        const startDate = new Date(year, month - 1, 1);
        const endDate = new Date(year, month, 0); // Day 0 of next month is the last day of this month
        const TOTAL_DAYS = endDate.getDate(); 

        const dates = [];
        for(let i=0; i < TOTAL_DAYS; i++) {
            let d = new Date(startDate);
            d.setDate(1 + i);
            dates.push(d);
        }

        // Group Events by Vehicle
        const vehicles = {};
        timelineEvents.forEach(ev => {
            const props = ev.extendedProps || {};
            const vId = props.vID || ev.title || 'unknown'; 
            
            if (!vehicles[vId]) {
                vehicles[vId] = {
                    id: props.vID,
                    model: props.model || 'Unknown Model',
                    plate: props.plate || 'Pending',
                    events: []
                };
            }
            vehicles[vId].events.push(ev);
        });

        // Build Header (Orange Theme & Fixed Sticky)
        let html = `<div class="flex header-row bg-orange-50 rounded-t-lg">
            <div class="vehicle-col p-4 flex items-center justify-center header-col-sticky bg-orange-500 border-r border-orange-700">
                <span class="text-[11px] font-black text-white uppercase tracking-widest">Plate No.</span>
            </div>
            <div class="flex border-b border-orange-200">`;

        dates.forEach((d, index) => {
            const isToday = d.getTime() === new Date().setHours(0,0,0,0);
            const dayNum = d.getDate();
            const dayName = d.toLocaleDateString('en-US', {weekday: 'short'});
            
            // Orange theme: Bright orange for today, light orange for normal days
            const bgClass = isToday ? 'bg-orange-500' : 'bg-orange-50';
            const borderClass = isToday ? 'border-orange-600' : 'border-orange-200';
            const textClassDay = isToday ? 'text-orange-100' : 'text-orange-400';
            const textClassNum = isToday ? 'text-white' : 'text-orange-900';

            html += `
                <div class="timeline-cell flex flex-col items-center justify-center border-r ${borderClass} ${bgClass} py-2">
                    <span class="text-[9px] font-bold ${textClassDay} uppercase tracking-wider">${dayName}</span>
                    <span class="text-sm font-black ${textClassNum} mt-0.5">${dayNum}</span>
                </div>`;
        });
        html += `</div></div>`;

        // Build Vehicle Rows
        Object.values(vehicles).forEach(v => {
            // Sort events by start date to handle overlapping correctly
            v.events.sort((a,b) => new Date(a.start) - new Date(b.start));
            
            const EVENT_HEIGHT = 48; 
            const EVENT_GAP = 8;
            
            // FIX: Track visual right-edge pixels instead of time to prevent overlap
            let rowEndPixels = []; 
            
            v.events.forEach(ev => {
                const safeStart = typeof ev.start === 'string' ? ev.start.replace(' ', 'T') : ev.start;
                const safeEnd = typeof ev.end === 'string' ? ev.end.replace(' ', 'T') : ev.end;
                const eStart = new Date(safeStart);
                const eEnd = ev.end ? new Date(safeEnd) : new Date(eStart.getTime() + 2 * 60 * 60 * 1000);

                // --- FIX: SNAP TO WHOLE DAYS FOR PIXELS ---
                const visualStart = new Date(eStart);
                visualStart.setHours(0, 0, 0, 0); // Snap left to start of day
                
                const visualEnd = new Date(eEnd);
                visualEnd.setHours(23, 59, 59, 999); // Snap width to end of day

                // Calculate visual positions relative to the month view
                const startDiffDays = Math.floor((visualStart.getTime() - startDate.getTime()) / (1000 * 60 * 60 * 24));
                const durationDays = Math.ceil((visualEnd.getTime() - visualStart.getTime()) / (1000 * 60 * 60 * 24));

                if (eEnd < startDate || eStart > endDate) return;

                // Visual X Coordinates
                const left = Math.max(0, startDiffDays * CELL_WIDTH);
                const actualWidth = durationDays * CELL_WIDTH;
                const rightPixel = left + actualWidth;

                // Determine row based on pixel availability (Collision Detection)
                let assignedRow = -1;
                for(let i=0; i < rowEndPixels.length; i++) {
                    if (left >= rowEndPixels[i]) { 
                        assignedRow = i; break;
                    }
                }
                
                if (assignedRow === -1) {
                    assignedRow = rowEndPixels.length;
                    rowEndPixels.push(rightPixel);
                } else {
                    rowEndPixels[assignedRow] = rightPixel;
                }
                
                // Store exact times for the label display
                ev._rowIndex = assignedRow;
                ev._eStart = eStart; // Keeping exact time for label
                ev._eEnd = eEnd;     // Keeping exact time for label
                ev._left = left;
                ev._width = Math.min(actualWidth, (TOTAL_DAYS * CELL_WIDTH) - left);
            });

            const rowCount = Math.max(1, rowEndPixels.length);
            const containerHeight = (rowCount * (EVENT_HEIGHT + EVENT_GAP)) + EVENT_GAP;

            html += `<div class="flex relative border-b border-orange-100 bg-white hover:bg-orange-50/40 transition-colors group" style="height: ${containerHeight}px">
                
                <div class="vehicle-col flex flex-col justify-center items-center bg-slate-50 group-hover:bg-orange-50 transition-colors border-r border-orange-200 z-20 py-2">
                    <a href="/staff/fleet/${v.id}" class="plate-badge border-orange-200 text-orange-900 shadow-sm bg-white hover:bg-orange-500 hover:text-white hover:border-orange-600 transition-all cursor-pointer decoration-none mb-1">
                        <span class="text-xs font-black tracking-wider">${v.plate}</span>
                    </a>
                    <div class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter text-center px-1">
                        ${v.model || 'Unknown Model'}
                    </div>
                </div>
                
                <div class="flex relative min-w-max z-10">`;

            dates.forEach((d) => {
                const isToday = d.getTime() === new Date().setHours(0,0,0,0);
                html += `<div class="timeline-cell border-r border-orange-100/60 ${isToday ? 'bg-orange-50/60' : 'bg-transparent'}"></div>`;
            });

            // Place Events (HTML Generation)
            v.events.forEach(ev => {
                if (!ev._eStart) return;
                
                // Exact times for the label
                const startTimeStr = ev._eStart.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                const endTimeStr = ev._eEnd.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                
                const rawCustomerName = ev.extendedProps?.customer_name || 'Customer';
                const customer = rawCustomerName.split(' ').slice(0, 2).join(' ');
                
                const topOffset = EVENT_GAP + (ev._rowIndex * (EVENT_HEIGHT + EVENT_GAP));
                const eventData = encodeURIComponent(JSON.stringify(ev));
                const isExternal = ev.extendedProps?.type === 'External' || ev.extendedProps?.source === 'External';
                const bgClass = isExternal ? 'bg-purple-50' : 'bg-[#eff6ff]';
                
                html += `
                    <div class="timeline-event ${bgClass} shadow-sm border border-slate-200"
                         style="left: ${ev._left}px; width: ${ev._width}px; top: ${topOffset}px; height: ${EVENT_HEIGHT}px; border-left: 3px solid; border-left-color: ${isExternal ? '#a855f7' : '#3b82f6'}; align-items: flex-start; padding-top: 6px;"
                         onclick="openEventPopup('${eventData}')">
                        
                        <span class="text-[11px] font-black text-slate-700 w-full truncate leading-none mb-0.5">
                            ${customer}
                        </span>
                        
                        <span class="text-[8.5px] font-bold text-slate-500 whitespace-normal leading-tight">
                            ${startTimeStr} - <br> ${endTimeStr}
                        </span>
                    </div>
                `;
            });

            html += `</div></div>`;
        });

        container.innerHTML = html;

        // Auto-scroll logic
        window.alignCalendarView = function() {
            const realToday = new Date();
            // Format today's date as YYYY-MM to compare with the filter
            const currentMonthStr = realToday.getFullYear() + '-' + String(realToday.getMonth() + 1).padStart(2, '0');
            
            if (selectedMonthStr === currentMonthStr) {
                // If viewing the current month, scroll to today (minus 1 cell for visual padding)
                const targetX = Math.max(0, (realToday.getDate() - 2) * CELL_WIDTH);
                scrollContainer.scrollTo({ left: targetX, behavior: 'smooth' });
            } else {
                // If viewing any other month, snap to the 1st day
                scrollContainer.scrollTo({ left: 0, behavior: 'smooth' });
            }
        };

        // Trigger scroll immediately after the calendar renders
        setTimeout(window.alignCalendarView, 100);
    });

    // Extract your existing SweetAlert popup logic to a global function
    window.openEventPopup = function(encodedData) {
        const ev = JSON.parse(decodeURIComponent(encodedData));
        const props = ev.extendedProps || {};
        
        // Formatting dates for the popup
        const start = new Date(ev.start).toLocaleString([], {month:'short', day:'numeric', hour:'2-digit', minute:'2-digit'});
        const end = ev.end ? new Date(ev.end).toLocaleString([], {month:'short', day:'numeric', hour:'2-digit', minute:'2-digit'}) : '-';

        // Your exact SweetAlert layout
        Swal.fire({
            title: `<div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600"><i class="fas fa-calendar-check"></i></div>
                        <div class="text-left">
                            <h3 class="text-lg font-bold text-slate-900 leading-tight">Booking #${ev.id || 'N/A'}</h3>
                            <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">${ev.title || 'Booking'}</p>
                        </div>
                    </div>`,
            html: `
                <div class="text-left font-sans mt-4 space-y-3">
                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                        <p class="text-xs font-bold text-slate-500 uppercase mb-1">Customer</p>
                        <p class="text-sm font-bold text-slate-900">${props.customer_name || 'Not specified'}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                            <p class="text-xs font-bold text-slate-500 uppercase mb-1">Start</p>
                            <p class="text-xs font-bold text-slate-800">${start}</p>
                        </div>
                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                            <p class="text-xs font-bold text-slate-500 uppercase mb-1">End</p>
                            <p class="text-xs font-bold text-slate-800">${end}</p>
                        </div>
                    </div>
                    <div class="flex justify-between items-center pt-2">
                        <span class="text-xs font-bold text-slate-500 uppercase">Status</span>
                        <span class="text-xs font-bold px-2 py-1 rounded bg-blue-100 text-blue-700">${props.status || 'Active'}</span>
                    </div>
                </div>`,
            showCancelButton: true,
            confirmButtonText: 'View Details',
            cancelButtonText: 'Close',
            customClass: {
                popup: 'rounded-3xl p-0 w-full max-w-sm overflow-hidden',
                actions: 'bg-slate-50 px-6 py-4 border-t border-slate-100 w-full flex flex-row-reverse gap-3 m-0',
                confirmButton: 'bg-blue-600 hover:bg-blue-700 text-white rounded-xl px-5 py-2.5 text-sm font-bold shadow-lg shadow-blue-200 transition-all w-full',
                cancelButton: 'bg-white hover:bg-slate-50 text-slate-500 hover:text-slate-700 border border-slate-200 rounded-xl px-5 py-2.5 text-sm font-bold transition-all w-full'
            }
        }).then((result) => {
            if (result.isConfirmed && ev.id) {
                window.location.href = `/staff/bookings/${ev.id}`;
            }
        });
    };
</script>
@endsection