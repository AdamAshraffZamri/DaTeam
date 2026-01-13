@extends('layouts.staff')

@section('content')

{{-- Alert Block --}}
@if(session('success'))
    <div class="p-4 mb-6 text-sm text-green-800 rounded-2xl bg-green-50 border border-green-100 shadow-sm" role="alert">
        <span class="font-bold">Success!</span> {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="p-4 mb-6 text-sm text-red-800 rounded-2xl bg-red-50 border border-red-100 shadow-sm" role="alert">
        <span class="font-bold">Error!</span> {{ session('error') }}
    </div>
@endif

{{-- Main Dashboard Container --}}
<div class="min-h-screen bg-slate-100 rounded-2xl p-6">
    <div class="max-w-7xl mx-auto space-y-6">

        {{-- 1. HEADER BAR --}}
        <div class="flex flex-col md:flex-row justify-between items-end gap-4">
            <div>
                <h1 class="text-4xl font-black text-gray-900">Reporting & Analysis</h1>
                <p class="text-gray-500 mt-1 text-sm">Financial insights and operational metrics</p>
            </div>
            
            <button type="button" onclick="openExportModal()" class="bg-gray-900 hover:bg-orange-600 text-white px-6 py-3.5 rounded-2xl font-bold text-xs shadow-lg transition-all transform hover:scale-105 flex items-center gap-2">
                <i class="fab fa-google-drive text-sm"></i> 
                <span>Save Report to Drive</span>
            </button>
        </div>

        {{-- 2. INCOME ANALYSIS SECTION --}}
        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
            <div class="flex flex-col md:flex-row justify-between items-end md:items-center gap-4 mb-6 border-b border-gray-100 pb-6">
                <div>
                    <h2 class="text-lg font-bold text-slate-800">Income Analysis</h2>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">{{ $chartTitle }}</p>
                </div>
                
                {{-- Filter Form --}}
                <form method="GET" action="{{ route('staff.reports.index') }}" class="flex flex-wrap items-center gap-2">
                    <input type="hidden" name="booking_period" value="{{ $bookingPeriod }}">
                    
                    <select name="filter_type" id="filter_type" class="bg-white border border-slate-200 text-slate-800 text-xs font-bold rounded-lg px-3 py-2 focus:border-orange-500 focus:ring-0 outline-none transition-colors" onchange="this.form.submit()">
                        <option value="daily" {{ $filterType == 'daily' ? 'selected' : '' }}>Daily</option>
                        <option value="weekly" {{ $filterType == 'weekly' ? 'selected' : '' }}>Weekly</option>
                        <option value="monthly" {{ $filterType == 'monthly' ? 'selected' : '' }}>Monthly</option>
                        <option value="yearly" {{ $filterType == 'yearly' ? 'selected' : '' }}>Yearly</option>
                    </select>

                    @if($filterType == 'daily' || $filterType == 'weekly')
                        <input type="date" name="date" value="{{ $selectedDate }}" class="bg-white border border-slate-200 text-slate-800 text-xs font-bold rounded-lg px-3 py-2 focus:border-orange-500 focus:ring-0 outline-none transition-colors" onchange="this.form.submit()">
                    @endif

                    @if($filterType == 'monthly')
                        <select name="month" class="bg-white border border-slate-200 text-slate-800 text-xs font-bold rounded-lg px-3 py-2 focus:border-orange-500 focus:ring-0 outline-none transition-colors" onchange="this.form.submit()">
                            @for($m=1; $m<=12; $m++)
                                <option value="{{ $m }}" {{ $selectedMonth == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                            @endfor
                        </select>
                        <select name="year" class="bg-white border border-slate-200 text-slate-800 text-xs font-bold rounded-lg px-3 py-2 focus:border-orange-500 focus:ring-0 outline-none transition-colors" onchange="this.form.submit()">
                            @for($y=date('Y'); $y>=2024; $y--)
                                <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    @endif

                    @if($filterType == 'yearly')
                        <select name="year" class="bg-white border border-slate-200 text-slate-800 text-xs font-bold rounded-lg px-3 py-2 focus:border-orange-500 focus:ring-0 outline-none transition-colors" onchange="this.form.submit()">
                            @for($y=date('Y'); $y>=2024; $y--)
                                <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    @endif
                </form>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                {{-- Total Revenue Metric Card --}}
                <div class="lg:col-span-1 bg-white p-5 rounded-xl border border-gray-200 shadow-sm hover:border-indigo-200 transition-colors group flex flex-col justify-center">
                    <div class="flex justify-between items-start mb-2">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Revenue</p>
                        <div class="p-2 bg-indigo-50 text-indigo-600 rounded-lg group-hover:bg-indigo-100 transition-colors">
                            <i class="fas fa-wallet text-lg"></i>
                        </div>
                    </div>
                    <h3 class="text-3xl font-black text-slate-800">RM{{ number_format($totalIncome, 2) }}</h3>
                    <div class="mt-2">
                        <span class="inline-block bg-indigo-50 text-indigo-700 text-[10px] font-bold px-2 py-1 rounded">
                            {{ ucfirst($filterType) }} View
                        </span>
                    </div>
                </div>

                {{-- Chart --}}
                <div class="lg:col-span-3">
                    <div id="incomeBarChart" class="h-80 w-full"></div>
                </div>
            </div>
        </div>

        {{-- 3. VEHICLE PROFITS SECTION --}}
        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
            <div class="flex flex-col md:flex-row justify-between items-end md:items-center gap-4 mb-6 border-b border-gray-100 pb-6">
                <div>
                    <h2 class="text-lg font-bold text-slate-800">Vehicle Profits</h2>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Earnings vs Costs</p>
                </div>
                
                <form method="GET" action="{{ route('staff.reports.index') }}" class="flex items-center gap-2">
                    {{-- Hidden inputs to preserve state --}}
                    <input type="hidden" name="filter_type" value="{{ $filterType }}">
                    <input type="hidden" name="date" value="{{ $selectedDate }}">
                    <input type="hidden" name="month" value="{{ $selectedMonth }}">
                    <input type="hidden" name="year" value="{{ $selectedYear }}">
                    <input type="hidden" name="booking_period" value="{{ $bookingPeriod }}">
                    <input type="hidden" name="status_period" value="{{ $statusPeriod }}">
                    <input type="hidden" name="vehicle_period" value="{{ $vehiclePeriod }}">
                    <input type="hidden" name="faculty_period" value="{{ $facultyPeriod }}">
                    <input type="hidden" name="address_period" value="{{ $addressPeriod }}">
                    
                    <select name="vehicle_profit_filter" class="bg-white border border-slate-200 text-slate-800 text-xs font-bold rounded-lg px-3 py-2 focus:border-orange-500 focus:ring-0 outline-none transition-colors w-full md:w-64" onchange="this.form.submit()">
                        <option value="all" {{ $vehicleProfitFilter == 'all' ? 'selected' : '' }}>All Vehicles</option>
                        @foreach($allVehicles as $vehicle)
                            <option value="{{ $vehicle->VehicleID }}" {{ $vehicleProfitFilter == $vehicle->VehicleID ? 'selected' : '' }}>
                                {{ $vehicle->model }} ({{ $vehicle->plateNo }})
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- Earnings Card --}}
                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm hover:border-green-200 transition-colors group">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Earnings</p>
                            <h3 class="text-2xl font-bold text-slate-800 mt-1">RM{{ number_format($vehicleEarnings, 2) }}</h3>
                        </div>
                        <div class="p-2 bg-green-50 text-green-600 rounded-lg group-hover:bg-green-100 transition-colors">
                            <i class="fas fa-arrow-up text-lg"></i>
                        </div>
                    </div>
                    <p class="text-[10px] text-green-600 font-medium mt-2">From Bookings</p>
                </div>

                {{-- Costs Card --}}
                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm hover:border-red-200 transition-colors group">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Costs</p>
                            <h3 class="text-2xl font-bold text-slate-800 mt-1">RM{{ number_format($vehicleCosts, 2) }}</h3>
                        </div>
                        <div class="p-2 bg-red-50 text-red-600 rounded-lg group-hover:bg-red-100 transition-colors">
                            <i class="fas fa-arrow-down text-lg"></i>
                        </div>
                    </div>
                    <p class="text-[10px] text-red-600 font-medium mt-2">Maintenance & Repairs</p>
                </div>

                {{-- Net Profit Card --}}
                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm hover:border-blue-200 transition-colors group">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Net Profit</p>
                            <h3 class="text-2xl font-bold {{ $vehicleNetProfit >= 0 ? 'text-blue-600' : 'text-orange-600' }} mt-1">
                                RM{{ number_format($vehicleNetProfit, 2) }}
                            </h3>
                        </div>
                        <div class="p-2 {{ $vehicleNetProfit >= 0 ? 'bg-blue-50 text-blue-600' : 'bg-orange-50 text-orange-600' }} rounded-lg transition-colors">
                            <i class="fas fa-chart-line text-lg"></i>
                        </div>
                    </div>
                    <span class="inline-block mt-2 px-2 py-0.5 rounded text-[10px] font-bold {{ $vehicleNetProfit >= 0 ? 'bg-blue-50 text-blue-700' : 'bg-orange-50 text-orange-700' }}">
                        {{ $vehicleNetProfit >= 0 ? 'Profitable' : 'Loss' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- 4. WIDGETS GRID --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- Booking Status --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 lg:col-span-1">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-slate-800">Booking Status</h3>
                    <form method="GET" action="{{ route('staff.reports.index') }}">
                         {{-- Preserve inputs --}}
                        <input type="hidden" name="filter_type" value="{{ $filterType }}">
                        <input type="hidden" name="date" value="{{ $selectedDate }}">
                        <input type="hidden" name="month" value="{{ $selectedMonth }}">
                        <input type="hidden" name="year" value="{{ $selectedYear }}">
                        <input type="hidden" name="booking_period" value="{{ $bookingPeriod }}">
                        
                        <select name="status_period" class="bg-white border border-slate-200 text-slate-800 text-[10px] font-bold rounded-lg px-2 py-1 focus:border-orange-500 outline-none" onchange="this.form.submit()">
                            <option value="1month" {{ $statusPeriod == '1month' ? 'selected' : '' }}>1 Month</option>
                            <option value="3months" {{ $statusPeriod == '3months' ? 'selected' : '' }}>3 Months</option>
                        </select>
                    </form>
                </div>
                <div id="statusChart" class="h-64"></div> 
                <div class="mt-4 space-y-2">
                    @foreach($bookingStatus as $status => $count)
                        <div class="flex justify-between items-center text-xs">
                            <span class="capitalize font-medium text-slate-600">{{ $status }}</span>
                            <span class="font-bold text-slate-800">{{ $count }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Top Vehicles --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden lg:col-span-1">
                <div class="p-5 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                    <h3 class="text-lg font-bold text-slate-800">Top Vehicles</h3>
                    <form method="GET" action="{{ route('staff.reports.index') }}">
                        {{-- Preserve inputs --}}
                        <input type="hidden" name="filter_type" value="{{ $filterType }}">
                        <input type="hidden" name="date" value="{{ $selectedDate }}">
                        <input type="hidden" name="month" value="{{ $selectedMonth }}">
                        <input type="hidden" name="year" value="{{ $selectedYear }}">
                        <input type="hidden" name="booking_period" value="{{ $bookingPeriod }}">
                        <input type="hidden" name="status_period" value="{{ $statusPeriod }}">
                        <input type="hidden" name="faculty_period" value="{{ $facultyPeriod }}">
                        <input type="hidden" name="address_period" value="{{ $addressPeriod }}">
                        <select name="vehicle_period" class="bg-white border border-slate-200 text-slate-800 text-[10px] font-bold rounded-lg px-2 py-1 focus:border-orange-500 outline-none" onchange="this.form.submit()">
                            <option value="1week" {{ $vehiclePeriod == '1week' ? 'selected' : '' }}>1 Week</option>
                            <option value="1month" {{ $vehiclePeriod == '1month' ? 'selected' : '' }}>1 Month</option>
                            <option value="3months" {{ $vehiclePeriod == '3months' ? 'selected' : '' }}>3 Months</option>
                        </select>
                    </form>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="px-5 py-3 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Model</th>
                                <th class="px-5 py-3 text-right text-[10px] font-bold text-slate-400 uppercase tracking-widest">Qty</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($topVehicles as $vehicle)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-5 py-3 text-xs font-bold text-slate-700 truncate" title="{{ $vehicle['model'] }}">{{ $vehicle['model'] }}</td>
                                    <td class="px-5 py-3 text-right">
                                        <span class="inline-flex items-center justify-center px-2 py-0.5 rounded text-[10px] font-black bg-blue-50 text-blue-600">{{ $vehicle['total_bookings'] }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="px-5 py-6 text-center text-xs text-slate-400">No data available.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Recent Activity (Booking Overview) --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden lg:col-span-1 flex flex-col">
                <div class="p-5 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                    <h3 class="text-lg font-bold text-slate-800 truncate pr-2">{{ $bookingTitle }}</h3>
                    <form method="GET" action="{{ route('staff.reports.index') }}">
                        <input type="hidden" name="filter_type" value="{{ $filterType }}">
                        <input type="hidden" name="date" value="{{ $selectedDate }}">
                        <input type="hidden" name="month" value="{{ $selectedMonth }}">
                        <input type="hidden" name="year" value="{{ $selectedYear }}">
                        <select name="booking_period" class="bg-white border border-slate-200 text-slate-800 text-[10px] font-bold rounded-lg px-2 py-1 focus:border-orange-500 outline-none" onchange="this.form.submit()">
                            <option value="7days" {{ $bookingPeriod == '7days' ? 'selected' : '' }}>Last 7 Days</option>
                            <option value="30days" {{ $bookingPeriod == '30days' ? 'selected' : '' }}>Last 30 Days</option>
                        </select>
                    </form>
                </div>
                
                <div class="p-5 overflow-y-auto max-h-[400px]">
                    <div class="grid grid-cols-3 gap-2">
                        @forelse($bookingOverview as $date => $dayBookings)
                            <div onclick="showBookings('{{ $date }}')" 
                                 class="flex flex-col items-center justify-center p-2 bg-white rounded-lg border border-slate-100 hover:border-indigo-300 hover:shadow-sm cursor-pointer transition-all duration-200 group">
                                <span class="text-[9px] font-bold text-slate-400 uppercase group-hover:text-indigo-600">
                                    {{ \Carbon\Carbon::parse($date)->format('D, d M') }}
                                </span>
                                <span class="text-lg font-black text-slate-800 mt-0.5 group-hover:scale-110 transition-transform">
                                    {{ $dayBookings->count() }}
                                </span>
                                <span class="text-[8px] text-slate-400 group-hover:text-indigo-500">Bookings</span>
                            </div>
                        @empty
                            <div class="col-span-full py-8 text-center text-xs text-slate-400">No bookings recorded.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- 5. DEMOGRAPHICS SECTION --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            {{-- Faculty Stats --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                    <h3 class="text-lg font-bold text-slate-800">Bookings by Faculty</h3>
                    <form method="GET" action="{{ route('staff.reports.index') }}">
                        {{-- Preserve inputs --}}
                        <input type="hidden" name="filter_type" value="{{ $filterType }}">
                        <input type="hidden" name="date" value="{{ $selectedDate }}">
                        <input type="hidden" name="month" value="{{ $selectedMonth }}">
                        <input type="hidden" name="year" value="{{ $selectedYear }}">
                        <input type="hidden" name="booking_period" value="{{ $bookingPeriod }}">
                        <input type="hidden" name="status_period" value="{{ $statusPeriod }}">
                        <input type="hidden" name="address_period" value="{{ $addressPeriod }}">
                        <select name="faculty_period" class="bg-white border border-slate-200 text-slate-800 text-[10px] font-bold rounded-lg px-2 py-1 focus:border-orange-500 outline-none" onchange="this.form.submit()">
                            <option value="1month" {{ $facultyPeriod == '1month' ? 'selected' : '' }}>1 Month</option>
                            <option value="3months" {{ $facultyPeriod == '3months' ? 'selected' : '' }}>3 Months</option>
                        </select>
                    </form>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <tbody class="divide-y divide-gray-50">
                            @forelse($bookingsByFaculty as $faculty => $count)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-3 text-xs font-medium text-slate-700">{{ $faculty }}</td>
                                    <td class="px-6 py-3 text-right">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black bg-indigo-50 text-indigo-600">
                                            {{ $count }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="px-6 py-6 text-center text-xs text-slate-400">No data available</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- College Stats --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                    <h3 class="text-lg font-bold text-slate-800">Bookings by College</h3>
                    <form method="GET" action="{{ route('staff.reports.index') }}">
                         {{-- Preserve inputs --}}
                        <input type="hidden" name="filter_type" value="{{ $filterType }}">
                        <input type="hidden" name="date" value="{{ $selectedDate }}">
                        <input type="hidden" name="month" value="{{ $selectedMonth }}">
                        <input type="hidden" name="year" value="{{ $selectedYear }}">
                        <input type="hidden" name="booking_period" value="{{ $bookingPeriod }}">
                        <input type="hidden" name="status_period" value="{{ $statusPeriod }}">
                        <input type="hidden" name="faculty_period" value="{{ $facultyPeriod }}">
                        <select name="address_period" class="bg-white border border-slate-200 text-slate-800 text-[10px] font-bold rounded-lg px-2 py-1 focus:border-orange-500 outline-none" onchange="this.form.submit()">
                            <option value="1month" {{ $addressPeriod == '1month' ? 'selected' : '' }}>1 Month</option>
                            <option value="3months" {{ $addressPeriod == '3months' ? 'selected' : '' }}>3 Months</option>
                        </select>
                    </form>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <tbody class="divide-y divide-gray-50">
                            @forelse($bookingsByAddress as $address => $count)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-3 text-xs font-medium text-slate-700">{{ $address }}</td>
                                    <td class="px-6 py-3 text-right">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black bg-green-50 text-green-600">
                                            {{ $count }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="px-6 py-6 text-center text-xs text-slate-400">No data available</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- 6. REVIEWS SECTION --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-8">
            <div class="p-5 border-b border-gray-100 bg-gray-50/50">
                <h3 class="text-lg font-bold text-slate-800">Latest Reviews</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <tbody class="divide-y divide-gray-50">
                        @forelse($reviews as $review)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="text-xs font-bold text-slate-800">{{ $review->booking->customer->fullName ?? 'Guest' }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-xs text-slate-500 font-mono bg-slate-100 px-2 py-1 rounded inline-block">
                                        {{ $review->booking->vehicle->model ?? 'N/A' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex text-orange-400 text-[10px] gap-0.5">
                                        @for($i = 0; $i < 5; $i++)
                                            <i class="fas fa-star {{ $i < $review->rating ? '' : 'text-gray-200' }}"></i>
                                        @endfor
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-600 italic">
                                    "{{ Str::limit($review->comment, 60) }}"
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-6 py-6 text-center text-xs text-slate-400">No reviews found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- MODAL (Style Update) --}}
<div id="bookingModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-black text-slate-800" id="modal-title">
                        Bookings: <span id="modalDate" class="text-indigo-600"></span>
                    </h3>
                    <button onclick="closeModal()" class="text-slate-400 hover:text-red-500 transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="mt-2 border rounded-xl border-gray-100 overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Customer</th>
                                <th class="px-4 py-3 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Vehicle</th>
                                <th class="px-4 py-3 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Status</th>
                                <th class="px-4 py-3 text-right text-[10px] font-bold text-slate-400 uppercase tracking-widest">Action</th>
                            </tr>
                        </thead>
                        <tbody id="modalContent" class="divide-y divide-gray-100 bg-white">
                            {{-- Content filled via JS --}}
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex flex-row-reverse">
                <button type="button" class="inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-4 py-2 bg-white text-xs font-bold text-slate-700 hover:bg-gray-100 focus:outline-none" onclick="closeModal()">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    // --- Store Bookings Data for JS Access ---
    var bookingsData = @json($bookingOverview);

    // --- Modal Functions ---
    function showBookings(date) {
        var dayBookings = bookingsData[date];
        var modal = document.getElementById('bookingModal');
        var modalContent = document.getElementById('modalContent');
        var modalDate = document.getElementById('modalDate');

        // Format Date
        modalDate.innerText = date; 

        // Clear previous content
        modalContent.innerHTML = '';

        if (dayBookings && dayBookings.length > 0) {
            dayBookings.forEach(function(booking) {
                // Determine Status Color
                var statusColor = 'text-slate-600';
                if(booking.bookingStatus === 'confirmed') statusColor = 'text-green-600 font-bold';
                if(booking.bookingStatus === 'pending') statusColor = 'text-orange-600 font-bold';
                if(booking.bookingStatus === 'cancelled') statusColor = 'text-red-600 font-bold';
                if(booking.bookingStatus === 'completed') statusColor = 'text-blue-600 font-bold';

                // Handle Null Checks safely
                var customerName = booking.customer ? booking.customer.fullName : 'Guest/Deleted';
                var vehicleModel = booking.vehicle ? booking.vehicle.model : 'Unknown';

                var row = `
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 text-xs font-medium text-slate-800">${customerName}</td>
                        <td class="px-4 py-3 text-xs text-slate-500 font-mono">${vehicleModel}</td>
                        <td class="px-4 py-3 text-xs ${statusColor} capitalize">${booking.bookingStatus}</td>
                        <td class="px-4 py-3 text-right text-xs">
                            <a href="/staff/bookings/${booking.id}" class="text-indigo-600 hover:text-indigo-900 font-bold hover:underline">View</a>
                        </td>
                    </tr>
                `;
                modalContent.innerHTML += row;
            });
        } else {
            modalContent.innerHTML = '<tr><td colspan="4" class="px-4 py-8 text-center text-xs text-slate-400">No details available.</td></tr>';
        }

        // Show Modal
        modal.classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('bookingModal').classList.add('hidden');
    }

    // --- Charts (Existing) ---
    document.addEventListener('DOMContentLoaded', function () {
        
        var incomeLabels = @json($chartLabels);
        var incomeData = @json($chartData);

        var incomeOptions = {
            series: [{ name: 'Income', data: incomeData }],
            chart: { 
                type: 'bar', 
                height: 320, 
                toolbar: { show: false },
                fontFamily: 'sans-serif' 
            },
            plotOptions: { 
                bar: { 
                    borderRadius: 6, 
                    columnWidth: '50%',
                    colors: {
                        ranges: [{ from: 0, to: 1000000000, color: '#4F46E5' }] // Indigo-600
                    }
                } 
            },
            dataLabels: { enabled: false },
            xaxis: { 
                categories: incomeLabels, 
                labels: { style: { fontSize: '11px', colors: '#64748b' } },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: { 
                title: { text: 'Revenue (RM)', style: { fontSize: '11px', fontWeight: 600, color: '#94a3b8' } },
                labels: { style: { colors: '#64748b' } }
            },
            grid: {
                borderColor: '#f1f5f9',
                strokeDashArray: 4,
            },
            colors: ['#4F46E5'],
            tooltip: { 
                theme: 'light',
                y: { formatter: function (val) { return "RM " + val.toFixed(2) } } 
            }
        };

        if(document.querySelector("#incomeBarChart")) {
            new ApexCharts(document.querySelector("#incomeBarChart"), incomeOptions).render();
        }

        var statusOptions = {
            series: @json($statusRaw->pluck('count')),
            labels: @json($statusRaw->pluck('bookingStatus')),
            chart: { type: 'donut', height: 280, fontFamily: 'sans-serif' },
            colors: ['#10B981', '#F97316', '#EF4444', '#3B82F6'], // Green, Orange, Red, Blue
            plotOptions: { pie: { donut: { size: '65%', labels: { show: true, total: { show: true, label: 'Total', fontSize: '14px', fontWeight: 700, color: '#1e293b' } } } } },
            legend: { position: 'bottom', fontSize: '12px', markers: { radius: 12 } },
            stroke: { show: false }
        };

        if(document.querySelector("#statusChart")) {
            new ApexCharts(document.querySelector("#statusChart"), statusOptions).render();
        }
    });
</script>

{{-- EXPORT MODAL --}}
<div id="exportModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeExportModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form action="{{ route('staff.reports.export') }}" method="POST">
                @csrf
                <div class="p-6">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-50 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fab fa-google-drive text-indigo-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-black text-slate-800" id="modal-title">
                                Export Report
                            </h3>
                            <div class="mt-4">
                                <p class="text-xs text-slate-500 mb-4 font-medium">Select the period you want to generate the report for.</p>

                                <div class="flex gap-4 mb-4 bg-gray-50 p-2 rounded-lg">
                                    <label class="flex items-center cursor-pointer">
                                        <input type="radio" name="report_type" value="monthly" checked onclick="toggleExportFields()" class="text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                        <span class="ml-2 text-xs font-bold text-slate-700">Monthly Report</span>
                                    </label>
                                    <label class="flex items-center cursor-pointer">
                                        <input type="radio" name="report_type" value="yearly" onclick="toggleExportFields()" class="text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                        <span class="ml-2 text-xs font-bold text-slate-700">Yearly Report</span>
                                    </label>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div id="exportMonthField">
                                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Month</label>
                                        <select name="month" class="block w-full bg-white border border-slate-200 text-slate-800 text-xs font-bold rounded-lg px-3 py-2 focus:border-indigo-500 outline-none">
                                            @for($m=1; $m<=12; $m++)
                                                <option value="{{ $m }}" {{ $m == date('n') ? 'selected' : '' }}>
                                                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                                </option>
                                            @endfor
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Year</label>
                                        <select name="year" class="block w-full bg-white border border-slate-200 text-slate-800 text-xs font-bold rounded-lg px-3 py-2 focus:border-indigo-500 outline-none">
                                            @php $currentYear = date('Y'); @endphp
                                            @for($y = $currentYear; $y >= $currentYear - 2; $y--)
                                                <option value="{{ $y }}">{{ $y }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-4 flex flex-row-reverse gap-2">
                    <button type="submit" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2.5 bg-indigo-600 text-xs font-bold text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto">
                        Generate & Save
                    </button>
                    <button type="button" onclick="closeExportModal()" class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-4 py-2.5 bg-white text-xs font-bold text-slate-700 hover:bg-gray-100 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openExportModal() {
        document.getElementById('exportModal').classList.remove('hidden');
    }

    function closeExportModal() {
        document.getElementById('exportModal').classList.add('hidden');
    }

    function toggleExportFields() {
        const type = document.querySelector('input[name="report_type"]:checked').value;
        const monthField = document.getElementById('exportMonthField');
        
        if (type === 'yearly') {
            monthField.classList.add('hidden');
        } else {
            monthField.classList.remove('hidden');
        }
    }
</script>
@endsection