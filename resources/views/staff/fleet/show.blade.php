@extends('layouts.staff')

@section('content')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    [x-cloak] { display: none !important; }
    
    /* Calendar Styling */
    .apple-calendar { font-family: -apple-system, BlinkMacSystemFont, sans-serif; }
    .fc-theme-standard td, .fc-theme-standard th { border-color: #f3f4f6; }
    .fc-col-header-cell-cushion { text-transform: uppercase; font-size: 11px; font-weight: 700; color: #9ca3af; padding: 10px 0; }
    .fc-daygrid-day-number { color: #374151; font-size: 13px; font-weight: 600; padding: 8px; }
    .fc-day-today .fc-daygrid-day-number { background: #f97316; color: white; border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; margin: 4px; }
    .fc-day-today { background: transparent !important; }
    
    /* Base Event Styling */
    .fc-event { border-radius: 6px; padding: 2px 4px; font-size: 10px; border: none; box-shadow: 0 1px 2px rgba(0,0,0,0.05); cursor: pointer; transition: transform 0.1s; }
    .fc-event:hover { transform: scale(1.02); }
    .fc-event-title { font-weight: 700 !important; } 
    .fc-event-time { font-weight: 700 !important; margin-right: 4px; }
    .fc-highlight { background: rgba(249, 115, 22, 0.2) !important; } /* Visual feedback when clicking */
    
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 4px; }
</style>

{{-- MAIN CONTAINER with ID for Alpine Scope --}}
<div id="fleet-calendar-container" class="min-h-screen bg-slate-100 rounded-2xl p-6" x-data="fleetShow()">
    <div class="max-w-7xl mx-auto">

        {{-- === HEADER === --}}
        <div class="w-full flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            
            {{-- LEFT: Brand & Model ONLY --}}
            <div>
                <h1 class="text-4xl font-black text-gray-900 tracking-tight leading-none">
                    {{ $vehicle->brand }} {{ $vehicle->model }}
                </h1>
            </div>

            {{-- RIGHT: Actions (Your Original Button Styles) --}}
            <div class="flex gap-3">
                {{-- Edit Button --}}
                <a href="{{ route('staff.fleet.edit', $vehicle->VehicleID) }}" 
                   class="bg-gray-600 hover:bg-gray-500 text-white px-6 py-3.5 rounded-2xl font-bold text-xs shadow-lg shadow-gray-900/20 transition-all transform hover:scale-105 flex items-center gap-2 whitespace-nowrap">
                    <i class="fas fa-edit"></i>
                    <span>Edit</span>
                </a>

                {{-- Delete Button --}}
                <form action="{{ route('staff.fleet.destroy', $vehicle->VehicleID) }}" method="POST" onsubmit="return confirm('Confirm to delete this vehicle? This action can\'t be undone.');">
                    @csrf @method('DELETE')
                    <button type="submit" 
                            class="bg-red-600 hover:bg-red-500 text-white px-6 py-3.5 rounded-2xl font-bold text-xs shadow-lg shadow-red-900/20 transition-all transform hover:scale-105 flex items-center gap-2 whitespace-nowrap">
                        <i class="fas fa-trash-alt"></i>
                        <span>Delete</span>
                    </button>
                </form>
            </div>

        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- LEFT COL --}}
            <div class="lg:col-span-2 space-y-6">
                
                {{-- 1. VEHICLE DETAILS --}}
                <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-8 flex flex-col gap-8 relative">
                    <div class="absolute top-4 left-5 z-10">
                        @if($vehicle->availability)
                            <span class="px-3 py-1.5 rounded-full bg-green-50 text-green-700 text-[10px] font-black uppercase tracking-wider border border-green-100 shadow-sm">Available</span>
                        @else
                            <span class="px-3 py-1.5 rounded-full bg-red-50 text-red-700 text-[10px] font-black uppercase tracking-wider border border-red-100 shadow-sm">Unavailable</span>
                        @endif
                    </div>

                    <div class="flex flex-col md:flex-row gap-8">
                        <div class="w-full md:w-5/12 flex flex-col gap-6">
                            <div class="h-60 bg-gray-50 rounded-2xl flex items-center justify-center overflow-hidden border border-gray-100 shrink-0 relative group shadow-sm">
                                @if($vehicle->image) 
                                    <img src="{{ asset('storage/' . $vehicle->image) }}" class="w-full h-full object-cover">
                                @else 
                                    <i class="fas fa-car text-4xl text-gray-300"></i> 
                                @endif
                            </div>
                            <div>
                                <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-3">Owner Details</span>
                                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 flex items-start gap-3">
                                    <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-sm font-black shadow-sm shrink-0">
                                        {{ substr($vehicle->owner_name ?? 'H', 0, 1) }}
                                    </div>
                                    <div class="space-y-1 overflow-hidden">
                                        <p class="text-sm font-bold text-gray-900 leading-none truncate" title="{{ $vehicle->owner_name }}">{{ $vehicle->owner_name ?? 'Hasta Travel & Tours' }}</p>
                                        <div class="flex flex-wrap gap-x-4 gap-y-1 text-[10px] text-gray-500 font-medium pt-1">
                                            <div class="flex items-center gap-1" title="Phone">
                                                <i class="fas fa-phone text-[9px] opacity-70"></i>
                                                <span>{{ $vehicle->owner_phone ?? 'No Phone' }}</span>
                                            </div>
                                            <div class="flex items-center gap-1" title="NRIC">
                                                <i class="fas fa-id-card text-[9px] opacity-70"></i>
                                                <span>{{ $vehicle->owner_nric ?? 'No ID' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        {{-- RIGHT: Info & Docs --}}
                        <div class="flex-1 flex flex-col justify-between gap-6 text-left">
                            
                            {{-- GRID: 2 Columns, 3 Rows --}}
                            <div class="grid grid-cols-2 gap-y-6 gap-x-4">
                                
                                {{-- Row 1 --}}
                                <div>
                                    <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Plate Number</span>
                                    <span class="font-bold text-gray-700 text-lg bg-gray-100 px-2 py-0.5 rounded inline-block font-mono border border-gray-200">{{ $vehicle->plateNo }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Manufacture Year</span>
                                    <span class="font-bold text-gray-900">{{ $vehicle->year }}</span>
                                </div>

                                {{-- Row 2 --}}
                                <div>
                                    <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Color</span>
                                    <span class="font-bold text-gray-500">{{ $vehicle->color }}</span>
                                </div>
                                <div>
                                    {{-- Changed 'Capacity/Transmission' to 'Fuel Type' --}}
                                    <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Fuel Type</span>
                                    <span class="font-bold text-gray-900">{{ $vehicle->fuel_type ?? 'Petrol' }}</span>
                                </div>

                                {{-- Row 3 --}}
                                <div>
                                    <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Body Type</span>
                                    <span class="font-bold text-gray-900">{{ $vehicle->type }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Current Mileage</span>
                                    <span class="font-bold text-orange-500">{{ number_format($currentMileage) }} km</span>
                                </div>
                            </div>

                            <div class="border-t border-gray-100 pt-6">
                                <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-3">Documents Preview</span>
                                <div class="grid grid-cols-3 gap-4">
                                    {{-- Road Tax --}}
                                    <div class="flex flex-col gap-2">
                                        <div class="group relative aspect-[4/3] rounded-xl border overflow-hidden transition-all w-full {{ $vehicle->road_tax_image ? 'border-gray-200 hover:border-blue-500 shadow-sm cursor-pointer' : 'border-gray-100 bg-gray-50 cursor-not-allowed' }}"
                                             @if($vehicle->road_tax_image) @click="openViewer('Road Tax', '{{ asset('storage/'.$vehicle->road_tax_image) }}')" @endif>
                                            @if($vehicle->road_tax_image)
                                                @php $rtExt = pathinfo($vehicle->road_tax_image, PATHINFO_EXTENSION); @endphp
                                                @if(in_array(strtolower($rtExt), ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                                    <img src="{{ asset('storage/' . $vehicle->road_tax_image) }}" class="w-full h-full object-cover blur-[2px] group-hover:blur-0 transition-all duration-300">
                                                @else
                                                    <div class="w-full h-full flex flex-col items-center justify-center bg-blue-50/50 text-blue-500">
                                                        <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center shadow-md"><i class="fas fa-file-pdf text-xl"></i></div>
                                                    </div>
                                                @endif
                                                <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center p-2 backdrop-blur-sm">
                                                    <span class="text-white text-[9px] font-medium text-center break-all leading-tight">{{ basename($vehicle->road_tax_image) }}</span>
                                                </div>
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-gray-300 bg-gray-50">
                                                    <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center shadow-sm border border-gray-100"><i class="fas fa-file-invoice text-xl"></i></div>
                                                </div>
                                                <div class="absolute inset-0 bg-gray-100/90 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                                    <span class="text-gray-400 text-[10px] font-bold uppercase tracking-wide">Not Available</span>
                                                </div>
                                            @endif
                                        </div>
                                        <span class="text-sm font-medium text-gray-600 text-center">Road Tax</span>
                                    </div>

                                    {{-- Grant --}}
                                    <div class="flex flex-col gap-2">
                                        <div class="group relative aspect-[4/3] rounded-xl border overflow-hidden transition-all w-full {{ $vehicle->grant_image ? 'border-gray-200 hover:border-blue-500 shadow-sm cursor-pointer' : 'border-gray-100 bg-gray-50 cursor-not-allowed' }}"
                                             @if($vehicle->grant_image) @click="openViewer('Grant', '{{ asset('storage/'.$vehicle->grant_image) }}')" @endif>
                                            @if($vehicle->grant_image)
                                                @php $gExt = pathinfo($vehicle->grant_image, PATHINFO_EXTENSION); @endphp
                                                @if(in_array(strtolower($gExt), ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                                    <img src="{{ asset('storage/' . $vehicle->grant_image) }}" class="w-full h-full object-cover blur-[2px] group-hover:blur-0 transition-all duration-300">
                                                @else
                                                    <div class="w-full h-full flex flex-col items-center justify-center bg-blue-50/50 text-blue-500">
                                                        <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center shadow-md"><i class="fas fa-file-pdf text-xl"></i></div>
                                                    </div>
                                                @endif
                                                <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center p-2 backdrop-blur-sm">
                                                    <span class="text-white text-[9px] font-medium text-center break-all leading-tight">{{ basename($vehicle->grant_image) }}</span>
                                                </div>
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-gray-300 bg-gray-50">
                                                    <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center shadow-sm border border-gray-100"><i class="fas fa-scroll text-xl"></i></div>
                                                </div>
                                                <div class="absolute inset-0 bg-gray-100/90 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                                    <span class="text-gray-400 text-[10px] font-bold uppercase tracking-wide">Not Available</span>
                                                </div>
                                            @endif
                                        </div>
                                        <span class="text-sm font-medium text-gray-600 text-center">Grant</span>
                                    </div>

                                    {{-- Insurance --}}
                                    <div class="flex flex-col gap-2">
                                        <div class="group relative aspect-[4/3] rounded-xl border overflow-hidden transition-all w-full {{ $vehicle->insurance_image ? 'border-gray-200 hover:border-blue-500 shadow-sm cursor-pointer' : 'border-gray-100 bg-gray-50 cursor-not-allowed' }}"
                                             @if($vehicle->insurance_image) @click="openViewer('Insurance', '{{ asset('storage/'.$vehicle->insurance_image) }}')" @endif>
                                            @if($vehicle->insurance_image)
                                                @php $iExt = pathinfo($vehicle->insurance_image, PATHINFO_EXTENSION); @endphp
                                                @if(in_array(strtolower($iExt), ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                                    <img src="{{ asset('storage/' . $vehicle->insurance_image) }}" class="w-full h-full object-cover blur-[2px] group-hover:blur-0 transition-all duration-300">
                                                @else
                                                    <div class="w-full h-full flex flex-col items-center justify-center bg-blue-50/50 text-blue-500">
                                                        <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center shadow-md"><i class="fas fa-file-pdf text-xl"></i></div>
                                                    </div>
                                                @endif
                                                <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center p-2 backdrop-blur-sm">
                                                    <span class="text-white text-[9px] font-medium text-center break-all leading-tight">{{ basename($vehicle->insurance_image) }}</span>
                                                </div>
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-gray-300 bg-gray-50">
                                                    <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center shadow-sm border border-gray-100"><i class="fas fa-shield-alt text-xl"></i></div>
                                                </div>
                                                <div class="absolute inset-0 bg-gray-100/90 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                                    <span class="text-gray-400 text-[10px] font-bold uppercase tracking-wide">Not Available</span>
                                                </div>
                                            @endif
                                        </div>
                                        <span class="text-sm font-medium text-gray-600 text-center">Insurance</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($vehicle->hourly_rates)
                        <div class="border-t border-gray-100 pt-6 mt-2 text-center">
                            <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-3">Rental Rates (Hourly Tiers)</span>
                            <div class="flex justify-center gap-2 overflow-x-auto custom-scrollbar pb-2">
                                @foreach($vehicle->hourly_rates as $hour => $rate)
                                    <div class="bg-gray-50 border border-gray-200 text-gray-700 rounded-xl px-3 py-2 text-center min-w-[65px] flex flex-col justify-center shadow-sm hover:bg-gray-100 transition-colors">
                                        <span class="block text-[9px] uppercase font-bold opacity-80">{{ $hour }}H</span>
                                        <span class="block text-sm font-black leading-tight">RM{{ $rate }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- 2. CALENDAR SECTION --}}
                <div class="bg-white rounded-[2rem] shadow-sm border border-orange-100 p-8 relative">
                    <div class="flex justify-between items-end mb-6">
                        <div>
                            <h2 id="customCalendarTitle" class="text-2xl font-bold text-gray-900 tracking-tight"></h2>
                            <p class="text-[10px] font-bold text-orange-400 uppercase tracking-wider mt-1">Manage Availability</p>
                        </div>
                        <div class="flex items-center gap-1 bg-white p-1 rounded-xl shadow-sm border border-orange-100">
                            <button id="prevBtn" class="w-8 h-8 rounded-lg hover:bg-orange-50 flex items-center justify-center text-gray-600 transition"><i class="fas fa-chevron-left text-xs"></i></button>
                            <button id="nextBtn" class="w-8 h-8 rounded-lg hover:bg-orange-50 flex items-center justify-center text-gray-600 transition"><i class="fas fa-chevron-right text-xs"></i></button>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl p-4 border border-orange-200 shadow-sm">
                        <div id="calendar" class="apple-calendar text-xs"></div>
                    </div>

                    <div class="flex flex-col md:flex-row justify-between items-end gap-4 mt-6 pt-4 border-t border-dashed border-orange-200">
                        <div class="flex flex-wrap gap-4">
                             <div class="flex items-center gap-2"><div class="w-2 h-2 rounded-full bg-orange-500"></div><span class="text-[10px] font-medium text-gray-500 uppercase">Booked</span></div>
                             <div class="flex items-center gap-2"><div class="w-2 h-2 rounded-full bg-red-500"></div><span class="text-[10px] font-medium text-gray-500 uppercase">Maintenance</span></div>
                             <div class="flex items-center gap-2"><div class="w-2 h-2 rounded-full bg-blue-500"></div><span class="text-[10px] font-medium text-gray-500 uppercase">Delivery</span></div>
                             <div class="flex items-center gap-2"><div class="w-2 h-2 rounded-full bg-purple-500"></div><span class="text-[10px] font-medium text-gray-500 uppercase">Holiday</span></div>
                             <div class="flex items-center gap-2"><div class="w-2 h-2 rounded-full bg-gray-500"></div><span class="text-[10px] font-medium text-gray-500 uppercase">Blocked</span></div>
                        </div>
                        
                        <button @click="historyModalOpen = true" class="text-[10px] font-bold text-gray-500 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 px-3 py-1.5 rounded-lg transition flex items-center gap-2">
                            <i class="fas fa-history"></i> History Log
                        </button>
                    </div>
                </div>
            </div>

            {{-- RIGHT COL: Financials & Bookings --}}
            <div class="flex flex-col gap-6">
                
                {{-- 1. Financial Card (Fixed Top) --}}
                <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6 text-center shrink-0">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Net Profit</h3>
                    <div class="text-4xl font-black {{ $netProfit >= 0 ? 'text-gray-900' : 'text-red-600' }} mb-4 tracking-tight">
                        RM {{ number_format($netProfit, 2) }}
                    </div>
                    <div class="flex justify-center gap-6 text-xs font-medium border-t border-gray-100 pt-4">
                        <div class="text-center">
                            <span class="text-green-600 font-bold block text-sm">RM {{ number_format($totalEarnings, 2) }}</span>
                            <span class="text-gray-400 text-[9px] uppercase font-bold tracking-wider">Earnings</span>
                        </div>
                        <div class="w-px bg-gray-100 h-8"></div>
                        <div class="text-center">
                            <span class="text-red-600 font-bold block text-sm">RM {{ number_format($totalMaintenanceCost, 2) }}</span>
                            <span class="text-gray-400 text-[9px] uppercase font-bold tracking-wider">Cost</span>
                        </div>
                    </div>
                </div>

                {{-- 2. Tabbed List Card (Fixed Height & Scrollable) --}}
                {{-- Added 'h-[600px]' to force a fixed size matching roughly the calendar height --}}
                <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 flex flex-col h-[1080px] overflow-hidden">
                    
                    {{-- Tabs Header (Fixed at top of card) --}}
                    <div class="p-4 border-b border-gray-100 shrink-0 bg-white z-10">
                        <div class="bg-gray-100 p-1 rounded-full flex gap-1">
                            <button @click="activeTab = 'bookings'" 
                                :class="activeTab === 'bookings' ? 'bg-white text-orange-600 shadow-sm' : 'text-gray-500 hover:bg-gray-200'" 
                                class="flex-1 py-2.5 rounded-full text-xs font-bold uppercase tracking-wider transition-all">
                                Bookings
                            </button>
                            <button @click="activeTab = 'service'" 
                                :class="activeTab === 'service' ? 'bg-white text-red-600 shadow-sm' : 'text-gray-500 hover:bg-gray-200'" 
                                class="flex-1 py-2.5 rounded-full text-xs font-bold uppercase tracking-wider transition-all">
                                Service
                            </button>
                        </div>
                    </div>
                    
                    {{-- Scrollable List Area --}}
                    {{-- 'flex-1' fills the rest of the 600px, 'overflow-y-auto' enables scroll --}}
                    <div class="flex-1 overflow-y-auto custom-scrollbar p-4">
                        
                        {{-- Booking List --}}
                        <div x-show="activeTab === 'bookings'" class="space-y-3">
                            @forelse($vehicle->bookings->sortByDesc('created_at') as $booking)
                                <a href="{{ route('staff.bookings.show', $booking->bookingID) }}" class="block bg-orange-50 p-4 rounded-2xl border border-gray-100 shadow-sm hover:border-orange-200 hover:shadow-md transition group">
                                    <div class="flex justify-between items-start mb-2">
                                        <div>
                                            <span class="text-lg font-black text-gray-900 block group-hover:text-orange-600">#{{ $booking->bookingID }}</span>
                                            <span class="text-s text-gray-400">{{ \Carbon\Carbon::parse($booking->pickupDate)->format('d M Y') }}</span>
                                        </div>
                                        @php
                                            $statusColor = match($booking->bookingStatus) {
                                                'Confirmed', 'Active' => 'bg-green-50 text-green-700',
                                                'Completed' => 'bg-blue-50 text-blue-700',
                                                'Cancelled' => 'bg-red-50 text-red-700',
                                                default => 'bg-gray-50 text-gray-600'
                                            };
                                        @endphp
                                        <span class="text-xs font-bold px-2 py-1 rounded-lg {{ $statusColor }}">{{ $booking->bookingStatus }}</span>
                                    </div>
                                    <div class="flex justify-between items-end border-t border-gray-50 pt-2 mt-2">
                                        <div class="flex items-center gap-2">
                                            <div class="w-5 h-5 rounded-full bg-gray-100 flex items-center justify-center text-[10px] font-bold text-gray-500">
                                                {{ substr($booking->customer->fullName, 0, 1) }}
                                            </div>
                                            <span class="text-[12px] font-bold text-gray-600 truncate max-w-[80px]">{{ $booking->customer->fullName }}</span>
                                        </div>
                                        <span class="text-xs font-bold text-gray-800">{{ $booking->return_mileage ? number_format($booking->return_mileage).' km' : '-' }}</span>
                                    </div>
                                </a>
                            @empty
                                <div class="flex flex-col items-center justify-center py-10 text-gray-400 h-full">
                                    <i class="fas fa-calendar-times text-2xl mb-2 opacity-50"></i>
                                    <p class="text-xs">No booking history.</p>
                                </div>
                            @endforelse
                        </div>

                        {{-- Service List --}}
                        <div x-show="activeTab === 'service'" class="space-y-3">
                            @forelse($vehicle->maintenances->whereIn('type', ['maintenance', null])->sortByDesc('start_time') as $maint)
                                <div class="bg-red-50 p-4 rounded-2xl border border-gray-100 shadow-sm hover:border-red-200 transition">
                                    <div class="flex justify-between items-start mb-2">
                                        <div>
                                            <span class="text-lg font-black text-gray-900 block">{{ \Carbon\Carbon::parse($maint->start_time)->format('d M Y') }}</span>
                                            <span class="text-s text-gray-400">Ref: #{{ $maint->MaintenanceID }}</span>
                                        </div>
                                        <span class="text-s font-bold text-red-600 bg-red-50 px-2 py-1 rounded-lg">RM {{ number_format($maint->cost, 2) }}</span>
                                    </div>
                                    <p class="text-[12px] text-gray-600 leading-relaxed font-medium">{{ $maint->description }}</p>
                                </div>
                            @empty
                                <div class="flex flex-col items-center justify-center py-10 text-gray-400 h-full">
                                    <i class="fas fa-wrench text-2xl mb-2 opacity-50"></i>
                                    <p class="text-xs">No service records.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- VIEWER MODAL --}}
    <div x-show="viewerOpen" class="fixed inset-0 z-[70] flex items-center justify-center bg-gray-900/60 backdrop-blur-md p-4" x-transition.opacity x-cloak>
        <div class="bg-white rounded-[2rem] shadow-2xl w-full max-w-4xl h-[85vh] flex flex-col overflow-hidden" @click.away="viewerOpen = false">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-white">
                <div class="flex items-center gap-4">
                    <h3 class="text-2xl font-black text-gray-900" x-text="viewerTitle"></h3>
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">{{ $vehicle->plateNo }}</p>
                </div>
                <div class="flex items-center gap-4">
                    <a :href="viewerSrc" download class="text-[10px] font-bold text-blue-600 bg-blue-50 px-3 py-1.5 rounded-lg hover:bg-blue-100 transition flex items-center gap-1">
                        <i class="fas fa-download"></i> Download
                    </a>
                    <button @click="viewerOpen = false" class="w-8 h-8 rounded-full bg-gray-50 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition"><i class="fas fa-times"></i></button>
                </div>
            </div>
            <div class="flex-1 bg-gray-100 p-4 flex items-center justify-center overflow-auto">
                <template x-if="viewerSrc">
                    <div class="w-full h-full flex items-center justify-center">
                        <template x-if="viewerType === 'pdf'">
                            <object :data="viewerSrc" type="application/pdf" class="w-full h-full rounded-xl border border-gray-200 bg-white shadow-sm">
                                <div class="flex flex-col items-center justify-center h-full text-gray-500">
                                    <p class="mb-2">Unable to display PDF directly.</p>
                                    <a :href="viewerSrc" target="_blank" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-bold">Open PDF</a>
                                </div>
                            </object>
                        </template>
                        <template x-if="viewerType !== 'pdf'">
                            <img :src="viewerSrc" class="max-w-full max-h-full object-contain rounded-lg shadow-lg">
                        </template>
                    </div>
                </template>
                <template x-if="!viewerSrc">
                    <div class="text-center text-gray-400">
                        <i class="fas fa-exclamation-circle text-4xl mb-2"></i>
                        <p class="text-sm font-bold">File not available</p>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- BLOCK SCHEDULE MODAL --}}
    <div x-show="blockModalOpen" 
        class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/20 backdrop-blur-sm px-4 transition-opacity duration-300"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-cloak>

        <form action="{{ route('staff.fleet.maintenance.store', $vehicle->VehicleID) }}" 
            method="POST" 
            class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all duration-300 scale-100"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            @click.away="blockModalOpen = false">
            
            @csrf
            
            {{-- Header --}}
            <div class="px-8 py-6 border-b border-slate-50 flex justify-between items-start bg-white">
                <div>
                    <h3 class="text-xl font-bold text-slate-900 tracking-tight">Block Schedule</h3>
                    <p class="text-sm font-medium text-slate-500 mt-1" x-text="dateRangeText"></p>
                </div>
                <button type="button" @click="blockModalOpen = false" class="w-8 h-8 rounded-full bg-slate-50 text-slate-400 hover:bg-slate-100 hover:text-slate-600 flex items-center justify-center transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="p-8 space-y-6">
                <input type="hidden" name="start_date" x-model="selectedStart">
                <input type="hidden" name="end_date" x-model="selectedEnd">

                {{-- Time & Toggle Section --}}
                <div class="space-y-4">
                    {{-- Time Selectors --}}
                    <div x-show="!allDay" x-collapse class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-semibold text-slate-500 mb-1.5 block">Start Time</label>
                            <div class="relative">
                                <select name="start_time" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-700 outline-none focus:bg-white focus:border-red-500 focus:ring-0 transition-all appearance-none cursor-pointer">
                                    @for($i = 0; $i < 24; $i++)
                                        <option value="{{ sprintf('%02d:00', $i) }}">{{ sprintf('%02d:00', $i) }}</option>
                                    @endfor
                                </select>
                                <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-slate-400">
                                    <i class="fas fa-chevron-down text-[10px]"></i>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500 mb-1.5 block">End Time</label>
                            <div class="relative">
                                <select name="end_time" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-700 outline-none focus:bg-white focus:border-red-500 focus:ring-0 transition-all appearance-none cursor-pointer">
                                    @for($i = 0; $i < 24; $i++)
                                        <option value="{{ sprintf('%02d:00', $i) }}" {{ $i == 23 ? 'selected' : '' }}>{{ sprintf('%02d:00', $i) }}</option>
                                    @endfor
                                </select>
                                <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-slate-400">
                                    <i class="fas fa-chevron-down text-[10px]"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    {{-- All Day Toggle --}}
                    <div class="flex items-center justify-between bg-slate-50 px-4 py-3 rounded-xl border border-slate-100 cursor-pointer group hover:border-slate-200 transition-colors" @click="allDay = !allDay">
                        <span class="text-sm font-semibold text-slate-700 group-hover:text-slate-900 transition-colors">Block Entire Day</span>
                        <div class="w-11 h-6 rounded-full relative transition-colors duration-200 ease-in-out" 
                            :class="allDay ? 'bg-slate-900' : 'bg-slate-200'">
                            <div class="w-4 h-4 bg-white rounded-full absolute top-1 left-1 shadow-sm transition-transform duration-200 ease-in-out" 
                                :class="allDay ? 'translate-x-5' : ''"></div>
                        </div>
                        <input type="hidden" name="all_day" :value="allDay ? 'true' : 'false'">
                    </div>
                </div>

                {{-- Reason Selection Grid --}}
                <div>
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3 block">Reason</label>
                    <div class="grid grid-cols-2 gap-3">
                        @foreach(['maintenance' => ['red', 'Maintenance', 'fa-tools'], 'delivery' => ['blue', 'Delivery', 'fa-truck'], 'holiday' => ['purple', 'Holiday', 'fa-umbrella-beach'], 'other' => ['gray', 'Other', 'fa-comment-alt']] as $key => $details)
                        <button type="button" @click="blockType = '{{ $key }}'" 
                                class="relative flex flex-col items-start p-3.5 rounded-2xl border text-left transition-all duration-200 group"
                                :class="blockType === '{{ $key }}' 
                                    ? 'bg-{{ $details[0] }}-50 border-{{ $details[0] }}-500 ring-1 ring-{{ $details[0] }}-500' 
                                    : 'bg-white border-slate-200 hover:border-slate-300 hover:shadow-sm'">
                            
                            <div class="flex justify-between w-full mb-2">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm transition-colors"
                                    :class="blockType === '{{ $key }}' ? 'bg-{{ $details[0] }}-100 text-{{ $details[0] }}-600' : 'bg-slate-50 text-slate-400 group-hover:bg-slate-100'">
                                    <i class="fas {{ $details[2] }}"></i>
                                </div>
                                <div x-show="blockType === '{{ $key }}'" class="text-{{ $details[0] }}-600">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                            </div>
                            <span class="text-xs font-bold transition-colors"
                                :class="blockType === '{{ $key }}' ? 'text-{{ $details[0] }}-900' : 'text-slate-600 group-hover:text-slate-900'">
                                {{ $details[1] }}
                            </span>
                        </button>
                        @endforeach
                    </div>
                    <input type="hidden" name="type" x-model="blockType">
                </div>

                {{-- Dynamic Inputs --}}
                <div class="pt-2">
                    {{-- Maintenance Inputs --}}
                    <div x-show="blockType === 'maintenance'" x-transition class="space-y-3">
                        <div class="relative">
                            <select name="maintenance_desc" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-semibold text-slate-700 outline-none focus:bg-white focus:border-red-500 focus:ring-0 transition-all appearance-none cursor-pointer">
                                <option>Regular Service</option><option>Tire Change</option><option>Battery Replacement</option><option>Major Repair</option><option>Inspection</option>
                            </select>
                            <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-slate-400"><i class="fas fa-chevron-down text-[10px]"></i></div>
                        </div>
                        <div class="relative">
                            <span class="absolute left-4 top-3 text-slate-400 text-sm font-bold">RM</span>
                            <input type="number" name="maintenance_cost" placeholder="0.00" class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-12 pr-4 py-3 text-sm font-bold text-slate-700 outline-none focus:bg-white focus:border-red-500 focus:ring-0 transition-all placeholder:font-normal">
                        </div>
                    </div>

                    {{-- Delivery Inputs --}}
                    <div x-show="blockType === 'delivery'" x-transition>
                        <input type="text" name="ref_id" placeholder="Related Booking ID (e.g., #BK-2024-001)" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-semibold text-slate-700 outline-none focus:bg-white focus:border-blue-500 focus:ring-0 transition-all placeholder:text-slate-400 placeholder:font-normal" :required="blockType === 'delivery'">
                        <input type="hidden" name="reason" value="Vehicle Delivery Logistics">
                    </div>

                    {{-- Other/Holiday Inputs --}}
                    <div x-show="blockType === 'other' || blockType === 'holiday'" x-transition>
                        <textarea name="reason" rows="2" placeholder="Enter remarks..." class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-semibold text-slate-700 outline-none focus:bg-white focus:border-purple-500 focus:ring-0 transition-all placeholder:text-slate-400 placeholder:font-normal resize-none" :required="blockType === 'other' || blockType === 'holiday'"></textarea>
                    </div>
                </div>

                <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white py-4 rounded-xl font-bold text-sm shadow-lg shadow-slate-200 hover:shadow-xl transition-all transform active:scale-[0.99] flex items-center justify-center gap-2">
                    <i class="fas fa-lock text-xs opacity-70"></i> Confirm Block
                </button>
            </div>
        </form>
    </div>

    {{-- HISTORY MODAL --}}
    <div x-show="historyModalOpen" 
        class="fixed inset-0 z-[80] flex items-center justify-center bg-slate-900/20 backdrop-blur-sm px-4 transition-opacity duration-300"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-cloak>

        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md h-[65vh] flex flex-col overflow-hidden transform transition-all duration-300 scale-100"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            @click.away="historyModalOpen = false">
            
            {{-- Header --}}
            <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-white z-10">
                <div>
                    <h3 class="text-lg font-bold text-slate-900 tracking-tight">History Log</h3>
                    <p class="text-xs text-slate-500 font-medium">Maintenance & block records</p>
                </div>
                <button @click="historyModalOpen = false" class="w-8 h-8 rounded-full bg-slate-50 text-slate-400 hover:bg-slate-100 hover:text-slate-600 flex items-center justify-center transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            {{-- Content --}}
            <div class="flex-1 overflow-y-auto p-5 space-y-4 bg-slate-50 custom-scrollbar">
                @forelse($vehicle->maintenances->sortByDesc('updated_at') as $log)
                    <div class="group bg-white border border-slate-100 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all duration-200 relative overflow-hidden">
                        
                        {{-- Unblocked Status Strip --}}
                        @if($log->type === 'unblocked')
                            <div class="absolute left-0 top-0 bottom-0 w-1 bg-emerald-400"></div>
                        @endif

                        <div class="flex justify-between items-start mb-3">
                            @php
                                $badgeStyle = match($log->type) {
                                    'maintenance' => 'bg-red-50 text-red-600 border-red-100',
                                    'delivery' => 'bg-blue-50 text-blue-600 border-blue-100',
                                    'holiday' => 'bg-purple-50 text-purple-600 border-purple-100',
                                    'unblocked' => 'bg-slate-100 text-slate-500 border-slate-200',
                                    default => 'bg-gray-50 text-gray-600 border-gray-200'
                                };
                                $icon = match($log->type) {
                                    'maintenance' => 'fa-tools',
                                    'delivery' => 'fa-truck',
                                    'holiday' => 'fa-umbrella-beach',
                                    'unblocked' => 'fa-history',
                                    default => 'fa-info-circle'
                                };
                            @endphp
                            
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider border {{ $badgeStyle }} flex items-center gap-1.5">
                                    <i class="fas {{ $icon }} text-[9px]"></i>
                                    {{ $log->type === 'unblocked' ? 'Archived' : $log->type }}
                                </span>
                            </div>
                            
                            <span class="text-[10px] font-semibold text-slate-400">{{ $log->created_at->format('d M, h:i A') }}</span>
                        </div>

                        {{-- Description Logic --}}
                        @php
                            $parts = explode('| [UNBLOCKED]', $log->description);
                            $cleanDesc = $parts[0];
                            $unblockInfo = isset($parts[1]) ? trim($parts[1]) : null;
                        @endphp

                        <div class="space-y-1 mb-3">
                            <p class="text-sm font-semibold text-slate-800 leading-snug {{ $log->type === 'unblocked' ? 'text-slate-500' : '' }}">
                                {{ $cleanDesc ?? 'No Description' }}
                            </p>
                            
                            <div class="flex items-center gap-1.5 text-xs text-slate-500">
                                <div class="w-4 h-4 rounded-full bg-slate-100 flex items-center justify-center text-[8px] text-slate-400">
                                    <i class="fas fa-user"></i>
                                </div>
                                <span class="font-medium">By {{ $log->staff->name ?? 'System' }}</span>
                            </div>
                        </div>

                        {{-- Unblock Info Box --}}
                        @if($unblockInfo)
                            <div class="mt-3 py-2 px-3 bg-emerald-50/50 rounded-lg border border-emerald-100/60 flex items-start gap-2.5">
                                <div class="mt-0.5 text-emerald-500"><i class="fas fa-unlock text-xs"></i></div>
                                <div class="flex-1">
                                    <p class="text-[10px] font-bold text-emerald-700 uppercase tracking-wide mb-0.5">Unblocked</p>
                                    <p class="text-xs text-emerald-600/90 leading-relaxed">
                                        {{ str_replace('by ', '', $unblockInfo) }}
                                    </p>
                                </div>
                            </div>
                        @endif
                        
                        {{-- Footer Date Range --}}
                        <div class="mt-3 pt-3 border-t border-slate-50 flex items-center justify-between">
                            <div class="flex items-center gap-2 text-xs font-medium text-slate-500">
                                <span class="text-slate-400"><i class="far fa-calendar-alt"></i></span>
                                <span>{{ \Carbon\Carbon::parse($log->start_time)->format('d M') }}</span>
                                <i class="fas fa-arrow-right text-[8px] text-slate-300"></i>
                                <span>{{ \Carbon\Carbon::parse($log->end_time)->format('d M') }}</span>
                            </div>
                            
                            {{-- Duration pill (optional nice-to-have) --}}
                            @php
                                $duration = \Carbon\Carbon::parse($log->start_time)->diffInDays(\Carbon\Carbon::parse($log->end_time));
                            @endphp
                            <span class="text-[10px] font-bold text-slate-300">
                                {{ $duration == 0 ? 'Same Day' : $duration . ' Days' }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center h-full text-slate-400 pb-8">
                        <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-3">
                            <i class="fas fa-clipboard-list text-2xl text-slate-300"></i>
                        </div>
                        <p class="text-sm font-semibold text-slate-500">No records found</p>
                        <p class="text-xs text-slate-400">Activity will appear here.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

</div>

<script>
    function fleetShow() {
        return {
            viewerOpen: false,
            blockModalOpen: false,
            historyModalOpen: false,
            
            viewerTitle: '',
            viewerSrc: '',
            viewerType: '',
            
            activeTab: 'bookings',
            
            selectedStart: '',
            selectedEnd: '',
            dateRangeText: '',
            blockType: 'maintenance',
            allDay: true,

            openViewer(title, path) {
                if(!path) return;
                this.viewerTitle = title;
                this.viewerSrc = path; 
                const cleanPath = path.split('?')[0];
                const extension = cleanPath.split('.').pop().toLowerCase();
                this.viewerType = extension === 'pdf' ? 'pdf' : 'image';
                this.viewerOpen = true;
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var events = @json($events);
        
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: false,
            height: 'auto',
            selectable: true,
            selectMirror: true, 
            unselectAuto: false, 
            eventDisplay: 'block',
            eventTimeFormat: { hour: '2-digit', minute: '2-digit', meridiem: false, hour12: false },
            nextDayThreshold: '00:00:00',
            
            select: function(info) {
                // FIXED: Use specific ID to get the correct Alpine Scope
                let alpineEl = document.getElementById('fleet-calendar-container');
                let alpine = Alpine.$data(alpineEl);
                
                alpine.selectedStart = info.startStr;
                
                // Adjust end date because FullCalendar select end is exclusive
                let endDate = new Date(info.endStr);
                endDate.setDate(endDate.getDate() - 1);
                alpine.selectedEnd = endDate.toISOString().split('T')[0];

                let diffTime = Math.abs(endDate - new Date(info.startStr));
                let diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                let options = { month: 'short', day: 'numeric' };
                alpine.dateRangeText = `${new Date(info.startStr).toLocaleDateString('en-US', options)} - ${endDate.toLocaleDateString('en-US', options)} (${diffDays} Days)`;

                alpine.blockModalOpen = true;
                alpine.blockType = 'maintenance'; 
                alpine.allDay = true;
            },

            events: events,
            
            eventDidMount: function(info) {
                if (!info.event.allDay) {
                    info.el.style.backgroundColor = info.event.backgroundColor;
                    info.el.style.borderColor = info.event.backgroundColor;
                    info.el.style.color = '#ffffff';
                    info.el.style.opacity = '1';
                    
                    let titleEl = info.el.querySelector('.fc-event-title');
                    let timeEl  = info.el.querySelector('.fc-event-time');
                    if(titleEl) titleEl.style.color = '#ffffffff';
                    if(timeEl)  timeEl.style.color  = '#000000ff';
                } 
                else {
                    info.el.style.backgroundColor = info.event.backgroundColor;
                    info.el.style.borderColor = info.event.backgroundColor;
                    info.el.style.color = '#000000ff';
                    info.el.style.borderWidth = '1px';
                    info.el.style.borderStyle = 'solid';
                    info.el.style.fontWeight = 'bold';
                    info.el.style.opacity = '1';

                    let titleEl = info.el.querySelector('.fc-event-title');
                    if(titleEl) titleEl.style.color = '#000000ff';
                }
            },
            
            eventClick: function(info) {
                var props = info.event.extendedProps;
                
                if (props.type === 'booking') {
                    let start = info.event.start.toLocaleString([], {month:'short', day:'numeric', hour:'2-digit', minute:'2-digit'});
                    let end = info.event.end ? info.event.end.toLocaleString([], {month:'short', day:'numeric', hour:'2-digit', minute:'2-digit'}) : '';

                    Swal.fire({
                        title: info.event.title, 
                        html: `<div class="text-left text-sm text-gray-600 font-sans">
                                <div class="bg-orange-50 p-3 rounded-lg border border-orange-100 mb-3">
                                    <p class="font-bold text-orange-800 text-xs uppercase tracking-wider mb-1">Booking Time</p>
                                    <p class="font-bold text-gray-900">${start} to ${end}</p>
                                </div>
                                <p><strong>Status:</strong> ${props.status}</p>
                               </div>`,
                        showCancelButton: true,
                        confirmButtonText: 'View Details',
                        cancelButtonText: 'Close',
                        confirmButtonColor: '#f97316',
                        cancelButtonColor: '#1f2937',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            let bookingId = info.event.id.replace('booking_', '');
                            window.location.href = `/staff/bookings/${bookingId}`;
                        }
                    });
                }
                else if (props.type === 'block') {
                    let desc = props.desc || '-';
                    let extraHtml = '';
                    if(props.cost > 0) extraHtml += `<div class="mt-2 text-red-600 font-bold">Cost: RM ${props.cost}</div>`;
                    if(props.ref_id) extraHtml += `<div class="mt-2 text-blue-600 font-bold">Ref: ${props.ref_id}</div>`;

                    let historyHtml = '';
                    if (props.staff_name && props.created_at) {
                        historyHtml = `
                            <div class="mt-4 pt-3 border-t border-gray-100 text-xs text-gray-500">
                                <div class="flex items-center gap-2 mb-1">
                                    <i class="fas fa-user-lock text-gray-400"></i>
                                    <span>Blocked by: <strong class="text-gray-700">${props.staff_name}</strong></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-clock text-gray-400"></i>
                                    <span>On: ${props.created_at}</span>
                                </div>
                            </div>
                        `;
                    }

                    Swal.fire({
                        title: info.event.title,
                        html: `<div class="text-left text-sm text-gray-600 font-sans">
                                <p><strong>Time:</strong> ${info.event.allDay ? 'Whole Day' : info.event.start.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) + ' - ' + (info.event.end ? info.event.end.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) : '')}</p>
                                <hr class="my-3 border-gray-100">
                                <p><strong>Details:</strong> ${desc}</p>
                                ${extraHtml}
                                ${historyHtml}
                               </div>`,
                        showCancelButton: true,
                        confirmButtonText: 'Unblock Date',
                        cancelButtonText: 'Close',
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#1f2937',
                        focusCancel: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            let form = document.createElement('form');
                            form.method = 'POST';
                            form.action = `/staff/fleet/maintenance/${info.event.id}`;
                            form.innerHTML = `@csrf @method('DELETE')`;
                            document.body.appendChild(form);
                            form.submit();
                        }
                    });
                }
            }
        });
        
        calendar.render();

        document.getElementById('customCalendarTitle').innerText = calendar.view.title;
        document.getElementById('prevBtn').addEventListener('click', function() { calendar.prev(); document.getElementById('customCalendarTitle').innerText = calendar.view.title; });
        document.getElementById('nextBtn').addEventListener('click', function() { calendar.next(); document.getElementById('customCalendarTitle').innerText = calendar.view.title; });
    });
</script>
@endsection