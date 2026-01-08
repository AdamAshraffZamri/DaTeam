@extends('layouts.staff')

@section('content')
{{-- External Scripts --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
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
            </div>

        </div>

        {{-- 2. METRICS GRID --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm hover:border-orange-200 transition-colors group">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Revenue</p>
                        <h3 class="text-2xl font-black text-slate-800 mt-1">RM {{ number_format($totalRevenue) }}</h3>
                    </div>
                    <div class="p-2.5 bg-green-100 text-green-700 rounded-xl group-hover:bg-green-600 group-hover:text-white transition-all shadow-sm">
                        <i class="fas fa-wallet text-lg"></i>
                    </div>
                </div>
                <div class="mt-3 text-[10px] font-bold text-green-600 flex items-center gap-1">
                    <i class="fas fa-arrow-up"></i> {{ $revenueGrowth }}% vs last month
                </div>
            </div>

            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm hover:border-orange-200 transition-colors group">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Active Rentals</p>
                        <h3 class="text-2xl font-black text-slate-800 mt-1">{{ $activeRentalsCount }}</h3>
                    </div>
                    <div class="p-2.5 bg-blue-100 text-blue-700 rounded-xl group-hover:bg-blue-600 group-hover:text-white transition-all shadow-sm">
                        <i class="fas fa-car-side text-lg"></i>
                    </div>
                </div>
                <div class="mt-3 w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-blue-600 h-1.5 rounded-full" style="width: {{ $utilizationRate }}%"></div>
                </div>
            </div>

            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm hover:border-orange-200 transition-colors group">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Pending</p>
                        <h3 class="text-2xl font-black text-slate-800 mt-1">{{ $pendingBookingsCount }}</h3>
                    </div>
                    <div class="p-2.5 bg-orange-100 text-orange-700 rounded-xl group-hover:bg-orange-600 group-hover:text-white transition-all shadow-sm">
                        <i class="fas fa-hourglass-half text-lg"></i>
                    </div>
                </div>
                <div class="mt-3 text-[10px] font-bold text-orange-600">Needs verification</div>
            </div>

            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm hover:border-orange-200 transition-colors group">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Customers</p>
                        <h3 class="text-2xl font-black text-slate-800 mt-1">{{ $totalCustomers }}</h3>
                    </div>
                    <div class="p-2.5 bg-purple-100 text-purple-700 rounded-xl group-hover:bg-purple-600 group-hover:text-white transition-all shadow-sm">
                        <i class="fas fa-users text-lg"></i>
                    </div>
                </div>
                <div class="mt-3 text-[10px] font-bold text-slate-400">Total registered users</div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 items-start">
            
            {{-- 3. LEFT COLUMN: CHARTS & CALENDAR --}}
            <div class="xl:col-span-2 space-y-6">
                
                {{-- Chart Section --}}
                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                    <div class="flex justify-between items-center mb-4">
                        <div class="flex items-center gap-3">
                            <h2 class="text-lg font-bold text-slate-800 truncate">Performance</h2>
                            <form action="{{ route('staff.dashboard') }}" method="GET">
                                <select name="chart_period" onchange="this.form.submit()" class="text-xs font-bold bg-gray-50 border border-gray-200 rounded-lg px-2 py-1 focus:border-orange-500 focus:ring-0 cursor-pointer text-slate-600">
                                    <option value="daily" {{ request('chart_period') == 'daily' ? 'selected' : '' }}>Daily</option>
                                    <option value="weekly" {{ request('chart_period') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                    <option value="monthly" {{ request('chart_period') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                </select>
                            </form>
                        </div>
                        <div class="flex gap-3">
                            <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-orange-500"></span><span class="text-[10px] font-bold text-slate-500 uppercase">Revenue</span></div>
                            <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-blue-500"></span><span class="text-[10px] font-bold text-slate-500 uppercase">Bookings</span></div>
                        </div>
                    </div>
                    <div class="h-64 w-full">
                        <canvas id="dashboardChart"></canvas>
                    </div>
                </div>

                {{-- CALENDAR SECTION --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h2 class="text-lg font-bold text-slate-800">Booking Calendar</h2>
                            <p class="text-xs text-slate-500 font-medium" id="calendarTitle"></p>
                        </div>
                        <div class="flex items-center gap-1 bg-gray-50 p-1 rounded-lg border border-gray-100">
                            <button id="prevBtn" class="w-7 h-7 rounded hover:bg-white flex items-center justify-center text-gray-500 shadow-sm transition"><i class="fas fa-chevron-left text-xs"></i></button>
                            <button id="nextBtn" class="w-7 h-7 rounded hover:bg-white flex items-center justify-center text-gray-500 shadow-sm transition"><i class="fas fa-chevron-right text-xs"></i></button>
                        </div>
                    </div>
                    <div id="dashboardCalendar" class="apple-calendar text-xs"></div>
                </div>
            </div>

            {{-- 4. RIGHT COLUMN: SIDEBAR --}}
            <div class="xl:col-span-1 space-y-6">
                
                {{-- 1. AVAILABILITY CHECKER (Top) --}}
                <div class="bg-white rounded-xl p-6 text-slate-900 shadow-lg relative overflow-hidden">
                    
                    <div class="absolute -top-10 -right-10 w-32 h-32 bg-orange-200 rounded-full blur-3xl opacity-60 pointer-events-none"></div>
                    
                    <div class="flex justify-between items-start relative z-10 mb-4">
                        <div>
                            <h2 class="text-lg font-bold text-slate-800 truncate">Check Availability</h2>
                            <p class="text-slate-500 text-xs font-medium">Instant fleet search.</p>
                        </div>
                        <button @click="setToday()" type="button" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-[10px] font-bold px-3 py-1.5 rounded-lg transition-all flex items-center gap-1 cursor-pointer hover:shadow-md active:scale-95 transform">
                            <i class="fas fa-calendar-day text-orange-500"></i> Today
                        </button>
                    </div>

                    <form action="{{ route('staff.dashboard') }}" method="GET" class="space-y-4 relative z-10">
                        <div>
                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest block mb-1.5">Date & Time Range</label>
                            <div class="grid grid-cols-2 gap-2">
                                {{-- Pickup --}}
                                <div>
                                    <span class="text-[9px] text-slate-500 block mb-1">Pickup</span>
                                    <input type="date" name="pickup_date" x-model="pDate" class="w-full bg-white border border-slate-200 text-slate-800 text-xs font-bold rounded-lg px-3 py-2 focus:border-orange-500 focus:ring-0 outline-none transition-colors cursor-pointer" required>
                                    <div class="relative mt-1">
                                        <select name="pickup_time" x-model="pTime" class="w-full bg-white border border-slate-200 text-slate-800 text-xs font-bold rounded-lg px-3 py-2 focus:border-orange-500 focus:ring-0 outline-none appearance-none cursor-pointer">
                                            @for($i = 0; $i < 24; $i++)
                                                <option value="{{ sprintf('%02d:00', $i) }}">{{ sprintf('%02d:00', $i) }}</option>
                                            @endfor
                                        </select>
                                        <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-[9px] text-slate-400 pointer-events-none"></i>
                                    </div>
                                </div>
                                {{-- Return --}}
                                <div>
                                    <span class="text-[9px] text-slate-500 block mb-1">Return</span>
                                    <input type="date" name="return_date" x-model="rDate" class="w-full bg-white border border-slate-200 text-slate-800 text-xs font-bold rounded-lg px-3 py-2 focus:border-orange-500 focus:ring-0 outline-none transition-colors cursor-pointer" required>
                                    <div class="relative mt-1">
                                        <select name="return_time" x-model="rTime" class="w-full bg-white border border-slate-200 text-slate-800 text-xs font-bold rounded-lg px-3 py-2 focus:border-orange-500 focus:ring-0 outline-none appearance-none cursor-pointer">
                                            @for($i = 0; $i < 24; $i++)
                                                <option value="{{ sprintf('%02d:00', $i) }}">{{ sprintf('%02d:00', $i) }}</option>
                                            @endfor
                                        </select>
                                        <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-[9px] text-slate-400 pointer-events-none"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest block mb-1.5">Vehicle Model</label>
                            <div class="relative">
                                <select name="model" class="w-full bg-white border border-slate-200 text-slate-800 text-xs font-bold rounded-lg px-3 py-2.5 focus:border-orange-500 outline-none appearance-none cursor-pointer">
                                    <option value="all">Any Model</option>
                                    @foreach($vehicleModels as $model)
                                        <option value="{{ $model }}" {{ request('model') == $model ? 'selected' : '' }}>{{ $model }}</option>
                                    @endforeach
                                </select>
                                <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-[10px] text-slate-400 pointer-events-none"></i>
                            </div>
                        </div>

                        <button type="submit" class="w-full bg-orange-600 hover:bg-orange-500 text-white py-3 rounded-lg font-bold text-xs uppercase tracking-widest shadow-lg shadow-orange-200 transition-all flex items-center justify-center gap-2 transform active:scale-[0.98]">
                            <i class="fas fa-search"></i> Find Vehicles
                        </button>
                    </form>
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
    
    {{-- === PROFESSIONAL SEARCH RESULT MODAL === --}}
    <div x-show="showResultsModal" 
         class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/40 backdrop-blur-sm px-4 transition-opacity duration-300"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         x-cloak>

        <div class="bg-white rounded-[2rem] shadow-2xl w-full max-w-2xl flex flex-col overflow-hidden transform transition-all duration-300 scale-100 h-[80vh]"
             @click.away="window.location.href = '{{ route('staff.dashboard') }}'">
            
            {{-- Header & Timeline Context --}}
            <div class="px-8 py-6 border-b border-gray-100 bg-white z-10">
                <div class="flex justify-between items-start mb-6">
                    <h3 class="text-2xl font-black text-gray-900 tracking-tight">Available Vehicles</h3>
                    <button onclick="window.location.href='{{ route('staff.dashboard') }}'" class="w-8 h-8 rounded-full bg-gray-50 text-gray-400 hover:bg-gray-100 hover:text-gray-600 flex items-center justify-center transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                {{-- Timeline Search Summary (Styled like reference) --}}
                <div class="bg-gray-50 rounded-2xl p-5 border border-gray-100 relative">
                    {{-- Vertical Dotted Line --}}
                    <div class="absolute left-[29px] top-7 bottom-7 w-0.5 border-l-2 border-dashed border-gray-300"></div>

                    <div class="flex flex-col gap-4">
                        {{-- Pickup --}}
                        <div class="flex items-start gap-4 relative z-10">
                            <div class="w-2.5 h-2.5 rounded-full bg-orange-500 mt-1.5 ring-4 ring-white"></div>
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-0.5">Pickup</p>
                                <p class="text-sm font-bold text-gray-900">
                                    {{ \Carbon\Carbon::parse(request('pickup_date'))->format('d M Y') }} 
                                    <span class="text-gray-500 font-medium ml-1">{{ request('pickup_time') }}</span>
                                </p>
                            </div>
                        </div>

                        {{-- Return --}}
                        <div class="flex items-start gap-4 relative z-10">
                            <div class="w-2.5 h-2.5 rounded-full bg-blue-500 mt-1.5 ring-4 ring-white"></div>
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-0.5">Return</p>
                                <p class="text-sm font-bold text-gray-900">
                                    {{ \Carbon\Carbon::parse(request('return_date'))->format('d M Y') }} 
                                    <span class="text-gray-500 font-medium ml-1">{{ request('return_time') }}</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Vehicle List --}}
            <div class="flex-1 overflow-y-auto p-6 space-y-4 bg-white custom-scrollbar">
                @if(isset($searchResults) && $searchResults->isNotEmpty())
                    @foreach($searchResults as $vehicle)
                        <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-sm hover:shadow-md hover:border-orange-200 transition-all group flex flex-col sm:flex-row gap-5 items-center">
                            
                            {{-- Image --}}
                            <div class="w-full sm:w-28 h-20 bg-gray-50 rounded-xl overflow-hidden flex items-center justify-center border border-gray-100 shrink-0">
                                @if($vehicle->image)
                                    <img src="{{ asset('storage/'.$vehicle->image) }}" class="w-full h-full object-cover">
                                @else
                                    <i class="fas fa-car text-gray-300 text-2xl"></i>
                                @endif
                            </div>

                            {{-- Details --}}
                            <div class="flex-1 text-center sm:text-left">
                                <div class="flex items-center justify-center sm:justify-start gap-2 mb-1">
                                    <h4 class="text-base font-black text-gray-900">{{ $vehicle->brand }} {{ $vehicle->model }}</h4>
                                    <span class="px-2 py-0.5 rounded-md bg-gray-100 text-gray-600 text-[10px] font-bold font-mono border border-gray-200">{{ $vehicle->plateNo }}</span>
                                </div>
                                <p class="text-xs text-gray-500 font-medium">{{ $vehicle->type }} • {{ $vehicle->fuelType }} • {{ $vehicle->year }}</p>
                            </div>

                            {{-- Financial/Action (Styled like reference financial box but lighter) --}}
                            <div class="w-full sm:w-auto bg-gray-50 rounded-xl p-3 border border-gray-100 text-right min-w-[140px]">
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Rate</p>
                                <p class="text-lg font-black text-gray-900 mb-2">RM {{ number_format($vehicle->priceHour) }}</p>
                                <a href="{{ route('staff.fleet.show', $vehicle->VehicleID) }}" class="block w-full text-center bg-gray-700 hover:bg-orange-600 text-white text-[10px] font-bold py-2 rounded-lg transition-colors">
                                    View Vehicle
                                </a>
                            </div>
                        </div>
                    @endforeach
                @elseif(isset($searchResults))
                    <div class="flex flex-col items-center justify-center h-full text-gray-400 pb-10">
                        <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-search text-3xl text-gray-300"></i>
                        </div>
                        <h4 class="text-lg font-bold text-gray-600">No vehicles available</h4>
                        <p class="text-sm font-medium mt-1">Try adjusting your dates.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>

{{-- SCRIPT: CHART & CALENDAR --}}
<script>
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

        // 2. Calendar Logic
        var calendarEl = document.getElementById('dashboardCalendar');
        var events = @json($calendarEvents); 

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: false,
            height: 'auto',
            dayMaxEvents: 2,
            events: events,
            
            // --- FIX: HIDE TIME FROM LABEL ---
            displayEventTime: false, 
            // ---------------------------------

            eventDidMount: function(info) {
                // Style: Blue light background, Dark Text
                info.el.style.backgroundColor = '#eff6ff'; 
                info.el.style.borderLeft = '3px solid #3b82f6'; 
                info.el.style.color = '#475569'; // Slate-600
                info.el.style.fontSize = '10px';
                info.el.style.fontWeight = '700';
                
                // Ensure internal elements inherit color
                const content = info.el.querySelector('.fc-event-main-frame') || info.el;
                if(content) content.style.color = '#475569';
            },
            eventClick: function(info) {
                // (Keep existing popup logic...)
                var props = info.event.extendedProps;
                let start = info.event.start.toLocaleString([], {month:'short', day:'numeric', hour:'2-digit', minute:'2-digit'});
                let end = info.event.end ? info.event.end.toLocaleString([], {month:'short', day:'numeric', hour:'2-digit', minute:'2-digit'}) : '-';

                Swal.fire({
                    title: `<div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600"><i class="fas fa-calendar-check"></i></div>
                                <div class="text-left">
                                    <h3 class="text-lg font-bold text-slate-900 leading-tight">Booking #${info.event.id}</h3>
                                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">${info.event.title}</p>
                                </div>
                            </div>`,
                    html: `
                        <div class="text-left font-sans mt-4 space-y-3">
                            <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                                <p class="text-xs font-bold text-slate-500 uppercase mb-1">Customer</p>
                                <p class="text-sm font-bold text-slate-900">${props.customer_name}</p>
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
                                <span class="text-xs font-bold px-2 py-1 rounded bg-blue-100 text-blue-700">${props.status}</span>
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
                    if (result.isConfirmed) {
                        window.location.href = `/staff/bookings/${info.event.id}`;
                    }
                });
            }
        });
        
        calendar.render();
        
        // Sync Title & Custom Buttons
        document.getElementById('calendarTitle').innerText = calendar.view.title;
        document.getElementById('prevBtn').addEventListener('click', function() { calendar.prev(); document.getElementById('calendarTitle').innerText = calendar.view.title; });
        document.getElementById('nextBtn').addEventListener('click', function() { calendar.next(); document.getElementById('calendarTitle').innerText = calendar.view.title; });
    });
</script>
@endsection