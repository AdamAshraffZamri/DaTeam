@extends('layouts.staff')

@section('content')
{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

{{-- Alpine Data for Modal and Clock --}}
<div class="min-h-screen bg-slate-100 rounded-2xl p-6" 
     x-data="{ 
        currentTab: 'pickups', 
        showResultsModal: {{ isset($searchResults) ? 'true' : 'false' }},
        time: new Date().toLocaleTimeString('en-US', { hour: 'numeric', minute: 'numeric', second: 'numeric', hour12: true }),
        date: new Date().toLocaleDateString('en-US', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }),
        init() {
            setInterval(() => {
                this.time = new Date().toLocaleTimeString('en-US', { hour: 'numeric', minute: 'numeric', second: 'numeric', hour12: true });
            }, 1000);
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
                
                <div class="flex items-center gap-3">
                {{-- Live Date/Time Widget --}}
                <div class="hidden md:block text-right bg-white px-5 py-2.5 rounded-2xl border border-gray-200 shadow-sm">
                    <p class="text-sm font-bold-30 font-black text-gray-900" x-text="time"></p>
                    <p class="text-[12px] text-gray-400 uppercase tracking-wider" x-text="date"></p>
                </div>
                
                {{-- Quick Actions Dropdown --}}
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" @click.away="open = false" class="bg-gray-700 hover:bg-orange-600 text-white px-6 py-3.5 rounded-2xl font-bold text-xs shadow-lg transition-all transform hover:scale-105 flex items-center gap-2 shrink-0 whitespace-nowrap">
                        <i class="fas fa-bolt"></i> <span>Quick Actions</span> 
                        <i class="fas fa-chevron-down ml-1 text-[10px] opacity-70 transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                    </button>

                    {{-- Dropdown Menu --}}
                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 translate-y-2"
                         class="absolute right-0 mt-3 w-56 bg-white rounded-2xl shadow-xl border border-gray-100 z-50 overflow-hidden" 
                         style="display: none;">
                        
                        <div class="px-4 py-3 border-b border-gray-50 bg-gray-50/50">
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Operations</p>
                        </div>

                        <a href="{{ route('staff.fleet.create') }}" class="flex items-center gap-3 px-4 py-3 hover:bg-orange-50 transition-colors group">
                            <div class="w-8 h-8 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center group-hover:bg-orange-600 group-hover:text-white transition-all shadow-sm">
                                <i class="fas fa-car text-xs"></i>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-gray-700 group-hover:text-orange-700">Add Vehicle</span>
                                <span class="text-[9px] text-gray-400 group-hover:text-orange-600/70">Register new fleet</span>
                            </div>
                        </a>

                        <a href="{{ route('staff.bookings.index') }}" class="flex items-center gap-3 px-4 py-3 hover:bg-blue-50 transition-colors group">
                            <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition-all shadow-sm">
                                <i class="fas fa-calendar-check text-xs"></i>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-gray-700 group-hover:text-blue-700">Bookings</span>
                                <span class="text-[9px] text-gray-400 group-hover:text-blue-600/70">Process rentals</span>
                            </div>
                        </a>

                        <a href="{{ route('staff.customers.index') }}" class="flex items-center gap-3 px-4 py-3 hover:bg-purple-50 transition-colors group">
                            <div class="w-8 h-8 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center group-hover:bg-purple-600 group-hover:text-white transition-all shadow-sm">
                                <i class="fas fa-users text-xs"></i>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-gray-700 group-hover:text-purple-700">Customers</span>
                                <span class="text-[9px] text-gray-400 group-hover:text-purple-600/70">Verify users</span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            </div>
        </div>

        {{-- 2. METRICS GRID --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm hover:border-orange-200 transition-colors group">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Revenue</p>
                        <h3 class="text-2xl font-bold text-slate-800 mt-1">RM {{ number_format($totalRevenue) }}</h3>
                    </div>
                    <div class="p-2 bg-green-50 text-green-600 rounded-lg group-hover:bg-green-100 transition-colors">
                        <i class="fas fa-wallet text-lg"></i>
                    </div>
                </div>
                <div class="mt-3 text-[10px] font-medium text-green-600 flex items-center gap-1">
                    <i class="fas fa-arrow-up"></i> {{ $revenueGrowth }}% vs last month
                </div>
            </div>

            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm hover:border-orange-200 transition-colors group">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Active Rentals</p>
                        <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ $activeRentalsCount }}</h3>
                    </div>
                    <div class="p-2 bg-blue-50 text-blue-600 rounded-lg group-hover:bg-blue-100 transition-colors">
                        <i class="fas fa-car-side text-lg"></i>
                    </div>
                </div>
                <div class="mt-3 w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-blue-500 h-1.5 rounded-full" style="width: 60%"></div>
                </div>
            </div>

            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm hover:border-orange-200 transition-colors group">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Pending</p>
                        <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ $pendingBookingsCount }}</h3>
                    </div>
                    <div class="p-2 bg-orange-50 text-orange-600 rounded-lg group-hover:bg-orange-100 transition-colors">
                        <i class="fas fa-hourglass-half text-lg"></i>
                    </div>
                </div>
                <div class="mt-3 text-[10px] font-medium text-orange-600">
                    Needs verification
                </div>
            </div>

            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm hover:border-orange-200 transition-colors group">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Customers</p>
                        <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ $totalCustomers }}</h3>
                    </div>
                    <div class="p-2 bg-purple-50 text-purple-600 rounded-lg group-hover:bg-purple-100 transition-colors">
                        <i class="fas fa-users text-lg"></i>
                    </div>
                </div>
                <div class="mt-3 text-[10px] font-medium text-slate-400">
                    Total registered users
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            
            {{-- 3. MAIN CONTENT: CHARTS & OPS --}}
            <div class="xl:col-span-2 space-y-6">
                
                {{-- Chart Section --}}
                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-bold text-slate-800 truncate mr-5">
                                Weekly Performance
                            </h2>
                        <div class="flex gap-3">
                            <div class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                                <span class="text-[10px] font-bold text-slate-500 uppercase">Revenue</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-slate-800"></span> {{-- Changed color to match line --}}
                                <span class="text-[10px] font-bold text-slate-500 uppercase">Bookings</span>
                            </div>
                        </div>
                    </div>
                    <div class="h-64 w-full">
                        <canvas id="dashboardChart"></canvas>
                    </div>
                </div>

                {{-- Operational Lists --}}
                <div class="mt-8">
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                        {{-- Header Section --}}
                        <div class="flex items-center h-10 mb-5">
                            <h2 class="text-lg font-bold text-slate-800 truncate mr-5">Daily Operations</h2>
                        </div>

                        <div class="flex flex-col sm:flex-row items-center justify-between mb-6 gap-4">

                            {{-- Left: Summary Stat --}}
                            <div class="mr-auto w-full sm:w-auto">
                                <div class="flex items-center gap-3">
                                    <div class="flex items-baseline gap-2">
                                        <div class="text-2xl font-bold text-slate-800">
                                            {{ $pickupsToday->count() + $returnsToday->count() }}
                                        </div>
                                        <div class="text-xs font-medium text-green-600 flex items-center">
                                            <i class="fas fa-caret-up mr-1"></i> Scheduled
                                        </div>
                                    </div>
                                </div>
                                <div class="text-slate-500 text-xs mt-1">Total Actions Today</div>
                            </div>

                            {{-- Right: Pill Tabs --}}
                            <div class="w-full sm:w-auto">
                                <div class="bg-gray-100 rounded-full p-1.5 flex justify-between sm:justify-start">
                                    
                                    {{-- Pickup Tab --}}
                                    <button @click="currentTab = 'pickups'" 
                                        class="flex-1 sm:flex-none px-4 py-2 rounded-full text-xs font-bold transition-all flex items-center justify-center gap-2"
                                        :class="currentTab === 'pickups' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:bg-gray-200/50'">
                                        Pickups
                                        <span class="w-5 h-5 rounded-full flex items-center justify-center text-[9px]"
                                            :class="currentTab === 'pickups' ? 'bg-slate-100 text-slate-800' : 'bg-white text-slate-500'">
                                            {{ $pickupsToday->count() }}
                                        </span>
                                    </button>

                                    {{-- Return Tab --}}
                                    <button @click="currentTab = 'returns'" 
                                        class="flex-1 sm:flex-none px-4 py-2 rounded-full text-xs font-bold transition-all flex items-center justify-center gap-2"
                                        :class="currentTab === 'returns' ? 'bg-white text-orange-600 shadow-sm' : 'text-slate-500 hover:bg-gray-200/50'">
                                        Returns
                                        <span class="w-5 h-5 rounded-full flex items-center justify-center text-[9px]"
                                            :class="currentTab === 'returns' ? 'bg-orange-50 text-orange-600' : 'bg-white text-slate-500'">
                                            {{ $returnsToday->count() }}
                                        </span>
                                    </button>

                                    {{-- Recent Tab --}}
                                    <button @click="currentTab = 'recent'" 
                                        class="flex-1 sm:flex-none px-4 py-2 rounded-full text-xs font-bold transition-all flex items-center justify-center gap-2"
                                        :class="currentTab === 'recent' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:bg-gray-200/50'">
                                        Recent
                                        <span class="w-5 h-5 rounded-full flex items-center justify-center text-[9px]"
                                            :class="currentTab === 'recent' ? 'bg-blue-50 text-blue-600' : 'bg-white text-slate-500'">
                                            5
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- List Content --}}
                        <div class="mt-2 divide-y divide-gray-50">
                            
                            {{-- 1. PICKUPS --}}
                            <div x-show="currentTab === 'pickups'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
                                @forelse($pickupsToday as $booking)
                                    @include('staff.partials.dashboard-booking-row', ['booking' => $booking, 'type' => 'pickup'])
                                @empty
                                    <div class="flex flex-col items-center justify-center py-12 text-slate-400">
                                        <i class="fas fa-check-circle text-2xl mb-2 opacity-30"></i>
                                        <p class="text-xs font-medium">No pickups scheduled today.</p>
                                    </div>
                                @endforelse
                            </div>

                            {{-- 2. RETURNS --}}
                            <div x-show="currentTab === 'returns'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" style="display: none;">
                                @forelse($returnsToday as $booking)
                                    @include('staff.partials.dashboard-booking-row', ['booking' => $booking, 'type' => 'return'])
                                @empty
                                    <div class="flex flex-col items-center justify-center py-12 text-slate-400">
                                        <i class="fas fa-check-circle text-2xl mb-2 opacity-30"></i>
                                        <p class="text-xs font-medium">No returns scheduled today.</p>
                                    </div>
                                @endforelse
                            </div>

                            {{-- 3. RECENT --}}
                            <div x-show="currentTab === 'recent'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" style="display: none;">
                                @forelse($recentBookings as $booking)
                                    @include('staff.partials.dashboard-booking-row', ['booking' => $booking, 'type' => 'recent'])
                                @empty
                                    <div class="p-8 text-center text-slate-400 text-xs">No recent activity.</div>
                                @endforelse
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            {{-- 4. SIDEBAR: CHECKER & SHORTCUTS --}}
            <div class="xl:col-span-1 space-y-6">
                
                {{-- Availability Checker --}}
                <div class="bg-white rounded-xl p-6 text-slate-900 shadow-lg relative overflow-hidden">
                    <div class="absolute -top-10 -right-10 w-32 h-32 bg-orange-200 rounded-full blur-3xl opacity-60 pointer-events-none"></div>
                    <h2 class="text-lg font-bold text-slate-800 truncate mr-5">Check Vehicle Availability</h2>
                    <p class="text-slate-500 text-xs font-medium mb-5 relative z-10">Instant fleet search for walk-ins.</p>

                    <form action="{{ route('staff.dashboard') }}" method="GET" class="space-y-4 relative z-10">
                        <div>
                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest block mb-1.5">Date & Time Range</label>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <span class="text-[9px] text-slate-500 block mb-1">Pickup</span>
                                    <input type="date" name="pickup_date" value="{{ request('pickup_date') }}" class="w-full bg-white border border-slate-200 text-slate-800 text-xs font-bold rounded-lg px-3 py-2 focus:border-orange-500 focus:ring-0 outline-none transition-colors" required>
                                    <input type="time" name="pickup_time" value="{{ request('pickup_time', '09:00') }}" class="w-full bg-white border border-slate-200 text-slate-800 text-xs font-bold rounded-lg px-3 py-2 mt-1 focus:border-orange-500 focus:ring-0 outline-none transition-colors">
                                </div>
                                <div>
                                    <span class="text-[9px] text-slate-500 block mb-1">Return</span>
                                    <input type="date" name="return_date" value="{{ request('return_date') }}" class="w-full bg-white border border-slate-200 text-slate-800 text-xs font-bold rounded-lg px-3 py-2 focus:border-orange-500 focus:ring-0 outline-none transition-colors" required>
                                    <input type="time" name="return_time" value="{{ request('return_time', '09:00') }}" class="w-full bg-white border border-slate-200 text-slate-800 text-xs font-bold rounded-lg px-3 py-2 mt-1 focus:border-orange-500 focus:ring-0 outline-none transition-colors">
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest block mb-1.5">Vehicle Model</label>
                            <div class="relative">
                                <select name="model" class="w-full bg-white border border-slate-200 text-slate-800 text-xs font-bold rounded-lg px-3 py-2.5 focus:border-orange-500 focus:ring-0 outline-none appearance-none cursor-pointer transition-colors">
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

                {{-- Fleet Pulse --}}
                <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm flex flex-col gap-6">
                    <div class="flex justify-between items-center">
                        <h2 class="text-lg font-bold text-slate-800 truncate mr-5">Fleet Pulse</h2>
                        <span class="bg-slate-100 text-slate-600 px-2 py-1 rounded text-[10px] font-bold">{{ $activeRentalsCount }} / {{ $totalVehicles }} Rented</span>
                    </div>

                    {{-- 1. Utilization Bar --}}
                    <div>
                        <div class="flex justify-between text-[10px] font-bold text-slate-400 mb-1.5">
                            <span>Utilization Rate</span>
                            <span>{{ number_format($utilizationRate, 0) }}%</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden flex">
                            {{-- Rented Portion --}}
                            <div class="bg-blue-500 h-full shadow-[0_0_10px_rgba(59,130,246,0.5)]" style="width: {{ $utilizationRate }}%"></div>
                            {{-- Maintenance Portion --}}
                            <div class="bg-red-500 h-full striped-bar" style="width: {{ $maintenanceRate }}%"></div>
                        </div>
                        <div class="flex gap-3 mt-2">
                            <div class="flex items-center gap-1">
                                <div class="w-1.5 h-1.5 rounded-full bg-blue-500"></div>
                                <span class="text-[9px] text-slate-500 font-medium">Active</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <div class="w-1.5 h-1.5 rounded-full bg-red-500"></div>
                                <span class="text-[9px] text-slate-500 font-medium">Service</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <div class="w-1.5 h-1.5 rounded-full bg-slate-200"></div>
                                <span class="text-[9px] text-slate-500 font-medium">Idle</span>
                            </div>
                        </div>
                    </div>

                    <div class="h-px bg-gray-100"></div>

                    {{-- 2. Today's Financials --}}
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Collected Today</p>
                            <h4 class="text-xl font-black text-slate-800 mt-0.5">RM {{ number_format($todayRevenue) }}</h4>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-green-50 flex items-center justify-center text-green-600 border border-green-100">
                            <i class="fas fa-coins"></i>
                        </div>
                    </div>

                    {{-- 3. Critical Alerts --}}
                    @if($overdueCount > 0)
                        <div class="bg-red-50 border border-red-100 rounded-lg p-3 flex items-center justify-between animate-pulse">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-exclamation-circle text-red-600 text-xs"></i>
                                <span class="text-xs font-bold text-red-700">Overdue Returns</span>
                            </div>
                            <span class="bg-white text-red-700 px-2 py-0.5 rounded text-[10px] font-black shadow-sm">{{ $overdueCount }}</span>
                        </div>
                    @else
                        <div class="bg-slate-50 border border-slate-100 rounded-lg p-3 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-check-circle text-slate-400 text-xs"></i>
                                <span class="text-xs font-bold text-slate-500">Returns on Track</span>
                            </div>
                        </div>
                    @endif

                </div>

                <style>
                    .striped-bar {
                        background-image: linear-gradient(45deg,rgba(255,255,255,.15) 25%,transparent 25%,transparent 50%,rgba(255,255,255,.15) 50%,rgba(255,255,255,.15) 75%,transparent 75%,transparent);
                        background-size: 1rem 1rem;
                    }
                </style>

            </div>
        </div>

    </div>

    {{-- === AVAILABILITY RESULTS MODAL === --}}
    <div x-show="showResultsModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[80vh] flex flex-col overflow-hidden" @click.away="showResultsModal = false">
            <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <div>
                    <h3 class="text-lg font-black text-slate-800">Available Vehicles</h3>
                    <p class="text-xs text-slate-500 font-medium">
                        {{ request('pickup_date') }} to {{ request('return_date') }}
                    </p>
                </div>
                <button @click="showResultsModal = false" class="w-8 h-8 rounded-full bg-white border border-gray-200 flex items-center justify-center text-gray-400 hover:text-red-500 transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="flex-1 overflow-y-auto p-5 space-y-3 bg-gray-50/50">
                @if(isset($searchResults) && $searchResults->isNotEmpty())
                    @foreach($searchResults as $vehicle)
                        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between hover:border-orange-300 transition-all group">
                            <div class="flex items-center gap-4">
                                <div class="w-16 h-12 bg-gray-100 rounded-lg overflow-hidden flex items-center justify-center">
                                    @if($vehicle->image)
                                        <img src="{{ asset('storage/'.$vehicle->image) }}" class="w-full h-full object-cover">
                                    @else
                                        <i class="fas fa-car text-gray-300"></i>
                                    @endif
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900">{{ $vehicle->brand }} {{ $vehicle->model }}</h4>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-[10px] font-mono font-bold bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded">{{ $vehicle->plateNo }}</span>
                                        <span class="text-[10px] text-slate-500">{{ $vehicle->type }} • {{ $vehicle->fuelType }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-black text-slate-900">RM {{ number_format($vehicle->priceHour) }}<span class="text-[10px] text-gray-400 font-normal">/day</span></p>
                                <a href="{{ route('staff.fleet.show', $vehicle->VehicleID) }}" class="mt-1 inline-block bg-slate-900 hover:bg-orange-600 text-white text-[10px] font-bold px-3 py-1.5 rounded-lg transition-colors">
                                    View Details
                                </a>
                            </div>
                        </div>
                    @endforeach
                @elseif(isset($searchResults))
                    <div class="text-center py-12">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-search text-gray-300 text-2xl"></i>
                        </div>
                        <p class="text-sm font-bold text-slate-600">No vehicles available.</p>
                        <p class="text-xs text-gray-400 mt-1">Try adjusting your dates or model filter.</p>
                    </div>
                @endif
            </div>
            
            <div class="p-4 border-t border-gray-100 bg-white text-right">
                <button @click="showResultsModal = false" class="text-xs font-bold text-slate-500 hover:text-slate-800 px-4 py-2">Close</button>
            </div>
        </div>
    </div>

</div>

{{-- Chart Logic --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('dashboardChart').getContext('2d');
        
        let gradientSales = ctx.createLinearGradient(0, 0, 0, 300);
        gradientSales.addColorStop(0, 'rgba(249, 115, 22, 0.15)'); // Orange
        gradientSales.addColorStop(1, 'rgba(249, 115, 22, 0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($chartLabels),
                datasets: [
                    {
                        label: 'Revenue',
                        data: @json($chartRevenue),
                        borderColor: '#f97316', // Orange
                        backgroundColor: gradientSales,
                        borderWidth: 2,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        fill: true,
                        tension: 0.4,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Bookings',
                        data: @json($chartBookings),
                        borderColor: '#3b82f6', // Bright Blue
                        backgroundColor: '#3b82f6',
                        borderWidth: 3,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        fill: false,
                        tension: 0.4,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { 
                    legend: { display: false },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(255, 255, 255, 0.9)',
                        titleColor: '#1e293b',
                        bodyColor: '#1e293b',
                        borderColor: '#e2e8f0',
                        borderWidth: 1
                    }
                },
                interaction: { mode: 'index', intersect: false },
                scales: {
                    x: { 
                        grid: { display: false }, 
                        ticks: { font: { size: 10, family: 'sans-serif' }, color: '#94a3b8' } 
                    },
                    y: { 
                        display: true, // <--- CHANGED TO TRUE (SHOWS SCALES)
                        beginAtZero: true,
                        suggestedMin: -100, // Forces negative scale
                        grid: { 
                            borderDash: [4, 4],
                            color: '#f1f5f9', // Very light grid lines
                            drawBorder: false 
                        },
                        ticks: { 
                            font: { size: 10, family: 'sans-serif' }, 
                            color: '#94a3b8',
                            callback: function(value) { return 'RM ' + value; } // Adds 'RM' prefix
                        }
                    },
                    y1: { 
                        display: false, // Keep secondary axis hidden to avoid clutter
                        beginAtZero: true,
                        position: 'right',
                        min: 0, 
                        suggestedMax: 5, 
                        grid: { drawOnChartArea: false }
                    }
                }
            }
        });
    });
</script>
@endsection