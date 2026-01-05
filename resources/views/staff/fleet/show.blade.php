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
    .fc-event { border-radius: 6px; padding: 2px 4px; font-size: 10px; border: none; box-shadow: 0 1px 2px rgba(0,0,0,0.05); cursor: pointer; transition: transform 0.1s; }
    .fc-event:hover { transform: scale(1.02); }
    .fc-event-title { font-weight: 500 !important; } 
    .fc-event-time { font-weight: 700 !important; margin-right: 4px; }
    
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 4px; }
</style>

<div class="min-h-screen bg-gray-100 rounded-2xl p-6" x-data="fleetShow()">
    <div class="max-w-7xl mx-auto">

        {{-- === HEADER === --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4"> 
            <div>
                <a href="{{ route('staff.fleet.index') }}" class="inline-flex items-center text-gray-500 hover:text-gray-900 transition">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Fleet
                </a>
            </div>
            
            <div class="flex gap-3">
                <a href="{{ route('staff.fleet.edit', $vehicle->VehicleID) }}" 
                   class="bg-gray-500 hover:bg-gray-500 text-white px-8 py-3 rounded-full font-bold text-sm shadow-lg shadow-gray-900/20 transition-all transform hover:scale-105 flex items-center gap-3 shrink-0 whitespace-nowrap">
                    <div class="bg-white/20 p-1.5 rounded-full flex items-center justify-center"><i class="fas fa-edit text-xs"></i></div>
                    <span>Edit</span>
                </a>
                
                <form action="{{ route('staff.fleet.destroy', $vehicle->VehicleID) }}" method="POST" onsubmit="return confirm('Delete vehicle?');">
                    @csrf @method('DELETE')
                    <button type="submit" 
                            class="bg-red-500 hover:bg-red-500 text-white px-8 py-3 rounded-full font-bold text-sm shadow-lg shadow-red-900/20 transition-all transform hover:scale-105 flex items-center gap-3 shrink-0 whitespace-nowrap">
                        <div class="bg-white/20 p-1.5 rounded-full flex items-center justify-center"><i class="fas fa-trash-alt text-xs"></i></div> <span>Delete</span>
                    </button>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- LEFT COL --}}
            <div class="lg:col-span-2 space-y-6">
                
                {{-- 1. VEHICLE DETAILS --}}
                <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-8 flex flex-col gap-8 relative">
                    <div class="absolute top-8 right-8 z-10">
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
                            <div class="grid grid-cols-2 gap-y-5 gap-x-4">
                                <div class="col-span-2">
                                    <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Make & Model</span>
                                    <span class="font-bold text-gray-900 text-2xl leading-tight">{{ $vehicle->brand }} {{ $vehicle->model }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Plate Number</span>
                                    <span class="font-bold text-gray-900 text-lg bg-gray-100 px-2 rounded inline-block font-mono border border-gray-200">{{ $vehicle->plateNo }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Type & Year</span>
                                    <span class="font-bold text-gray-700">{{ $vehicle->type }} ({{ $vehicle->year }})</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Spec</span>
                                    <span class="font-bold text-gray-700">{{ $vehicle->color }} • {{ $vehicle->fuelType }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Current Mileage</span>
                                    <span class="font-bold text-gray-900">{{ number_format($currentMileage) }} km</span>
                                </div>
                            </div>

                            {{-- Document Previews (Added Separator Line) --}}
                            <div class="border-t border-gray-100 pt-6">
                                <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-3">Documents Preview</span>
                                <div class="grid grid-cols-3 gap-4">
                                    <div class="flex flex-col gap-2">
                                        <div class="group relative aspect-[4/3] rounded-xl border overflow-hidden transition-all w-full {{ $vehicle->road_tax_image ? 'border-gray-200 hover:border-blue-500 shadow-sm cursor-pointer' : 'border-gray-100 bg-gray-50 cursor-not-allowed' }}"
                                             @if($vehicle->road_tax_image) @click="openViewer('Road Tax', '{{ asset('storage/'.$vehicle->road_tax_image) }}')" @endif>
                                            @if($vehicle->road_tax_image)
                                                @php $rtExt = pathinfo($vehicle->road_tax_image, PATHINFO_EXTENSION); @endphp
                                                @if(in_array(strtolower($rtExt), ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                                    <img src="{{ asset('storage/' . $vehicle->road_tax_image) }}" class="w-full h-full object-cover blur-[2px] group-hover:blur-0 transition-all duration-300">
                                                @else
                                                    <div class="w-full h-full flex flex-col items-center justify-center bg-blue-50/50 text-blue-500">
                                                        <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center shadow-md">
                                                            <i class="fas fa-file-pdf text-xl"></i>
                                                        </div>
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

                                    <div class="flex flex-col gap-2">
                                        <div class="group relative aspect-[4/3] rounded-xl border overflow-hidden transition-all w-full {{ $vehicle->grant_image ? 'border-gray-200 hover:border-blue-500 shadow-sm cursor-pointer' : 'border-gray-100 bg-gray-50 cursor-not-allowed' }}"
                                             @if($vehicle->grant_image) @click="openViewer('Grant', '{{ asset('storage/'.$vehicle->grant_image) }}')" @endif>
                                            @if($vehicle->grant_image)
                                                @php $gExt = pathinfo($vehicle->grant_image, PATHINFO_EXTENSION); @endphp
                                                @if(in_array(strtolower($gExt), ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                                    <img src="{{ asset('storage/' . $vehicle->grant_image) }}" class="w-full h-full object-cover blur-[2px] group-hover:blur-0 transition-all duration-300">
                                                @else
                                                    <div class="w-full h-full flex flex-col items-center justify-center bg-blue-50/50 text-blue-500">
                                                        <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center shadow-md">
                                                            <i class="fas fa-file-pdf text-xl"></i>
                                                        </div>
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

                                    <div class="flex flex-col gap-2">
                                        <div class="group relative aspect-[4/3] rounded-xl border overflow-hidden transition-all w-full {{ $vehicle->insurance_image ? 'border-gray-200 hover:border-blue-500 shadow-sm cursor-pointer' : 'border-gray-100 bg-gray-50 cursor-not-allowed' }}"
                                             @if($vehicle->insurance_image) @click="openViewer('Insurance', '{{ asset('storage/'.$vehicle->insurance_image) }}')" @endif>
                                            @if($vehicle->insurance_image)
                                                @php $iExt = pathinfo($vehicle->insurance_image, PATHINFO_EXTENSION); @endphp
                                                @if(in_array(strtolower($iExt), ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                                    <img src="{{ asset('storage/' . $vehicle->insurance_image) }}" class="w-full h-full object-cover blur-[2px] group-hover:blur-0 transition-all duration-300">
                                                @else
                                                    <div class="w-full h-full flex flex-col items-center justify-center bg-blue-50/50 text-blue-500">
                                                        <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center shadow-md">
                                                            <i class="fas fa-file-pdf text-xl"></i>
                                                        </div>
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

                <div class="bg-white rounded-[2rem] shadow-sm border border-orange-100 p-8">
                    <div class="flex justify-between items-end mb-6">
                        <div>
                            <h2 id="customCalendarTitle" class="text-2xl font-bold text-gray-900 tracking-tight"></h2>
                            <p class="text-[10px] font-bold text-orange-400 uppercase tracking-wider mt-1">Manage Availability</p>
                        </div>
                        <div class="flex items-center gap-1 bg-white p-1 rounded-xl shadow-sm border border-orange-100">
                            <button id="prevBtn" class="w-8 h-8 rounded-lg hover:bg-orange-50 flex items-center justify-center text-gray-600 transition"><i class="fas fa-chevron-left text-xs"></i></button>
                            <button id="todayBtn" class="text-xs font-bold text-gray-900 hover:bg-orange-50 px-3 py-1.5 rounded-lg transition uppercase tracking-wide">Today</button>
                            <button id="nextBtn" class="w-8 h-8 rounded-lg hover:bg-orange-50 flex items-center justify-center text-gray-600 transition"><i class="fas fa-chevron-right text-xs"></i></button>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl p-4 border border-orange-300 shadow-sm">
                        <div id="calendar" class="apple-calendar text-xs"></div>
                    </div>

                    <div class="flex flex-wrap gap-4 mt-6 pt-4 border-t border-dashed border-orange-200">
                         <div class="flex items-center gap-2"><div class="w-2 h-2 rounded-full bg-orange-500"></div><span class="text-[10px] font-medium text-gray-500 uppercase">Booked</span></div>
                         <div class="flex items-center gap-2"><div class="w-2 h-2 rounded-full bg-red-500"></div><span class="text-[10px] font-medium text-gray-500 uppercase">Maintenance</span></div>
                         <div class="flex items-center gap-2"><div class="w-2 h-2 rounded-full bg-blue-500"></div><span class="text-[10px] font-medium text-gray-500 uppercase">Delivery</span></div>
                         <div class="flex items-center gap-2"><div class="w-2 h-2 rounded-full bg-purple-500"></div><span class="text-[10px] font-medium text-gray-500 uppercase">Holiday</span></div>
                         <div class="flex items-center gap-2"><div class="w-2 h-2 rounded-full bg-gray-500"></div><span class="text-[10px] font-medium text-gray-500 uppercase">Blocked</span></div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-6 h-full">
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

                <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 flex flex-col flex-1 h-full overflow-hidden">
                    <div class="p-4 border-b border-gray-100 shrink-0">
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
                    
                    <div class="p-4 flex-1 overflow-y-auto custom-scrollbar">
                        <div x-show="activeTab === 'bookings'" class="space-y-3">
                            @forelse($vehicle->bookings->sortByDesc('created_at') as $booking)
                                <a href="{{ route('staff.bookings.show', $booking->bookingID) }}" class="block bg-white p-4 rounded-2xl border border-gray-100 shadow-sm hover:border-orange-200 hover:shadow-md transition group">
                                    <div class="flex justify-between items-start mb-2">
                                        <div>
                                            <span class="text-xs font-black text-gray-900 block group-hover:text-orange-600">#{{ $booking->bookingID }}</span>
                                            <span class="text-[10px] text-gray-400">{{ \Carbon\Carbon::parse($booking->pickupDate)->format('d M Y') }}</span>
                                        </div>
                                        @php
                                            $statusColor = match($booking->bookingStatus) {
                                                'Confirmed', 'Active' => 'bg-green-50 text-green-700',
                                                'Completed' => 'bg-blue-50 text-blue-700',
                                                'Cancelled' => 'bg-red-50 text-red-700',
                                                default => 'bg-gray-50 text-gray-600'
                                            };
                                        @endphp
                                        <span class="text-[10px] font-bold px-2 py-1 rounded-lg {{ $statusColor }}">{{ $booking->bookingStatus }}</span>
                                    </div>
                                    <div class="flex justify-between items-end border-t border-gray-50 pt-2 mt-2">
                                        <div class="flex items-center gap-2">
                                            <div class="w-5 h-5 rounded-full bg-gray-100 flex items-center justify-center text-[10px] font-bold text-gray-500">
                                                {{ substr($booking->customer->fullName, 0, 1) }}
                                            </div>
                                            <span class="text-[10px] font-bold text-gray-600 truncate max-w-[80px]">{{ $booking->customer->fullName }}</span>
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

                        <div x-show="activeTab === 'service'" class="space-y-3">
                            @forelse($vehicle->maintenances->whereIn('type', ['maintenance', null])->sortByDesc('start_time') as $maint)
                                <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm hover:border-red-200 transition">
                                    <div class="flex justify-between items-start mb-2">
                                        <div>
                                            <span class="text-xs font-black text-gray-900 block">{{ \Carbon\Carbon::parse($maint->start_time)->format('d M Y') }}</span>
                                            <span class="text-[10px] text-gray-400">Ref: #{{ $maint->MaintenanceID }}</span>
                                        </div>
                                        <span class="text-xs font-bold text-red-600 bg-red-50 px-2 py-1 rounded-lg">RM {{ number_format($maint->cost, 2) }}</span>
                                    </div>
                                    <p class="text-xs text-gray-600 leading-relaxed font-medium">{{ $maint->description }}</p>
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

    <div x-show="viewerOpen" class="fixed inset-0 z-[70] flex items-center justify-center bg-gray-900/60 backdrop-blur-md p-4" x-transition.opacity x-cloak>
        <div class="bg-white rounded-[2rem] shadow-2xl w-full max-w-4xl h-[85vh] flex flex-col overflow-hidden" @click.away="viewerOpen = false">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-white">
                <div class="flex items-center gap-4">
                    <div>
                        <h3 class="text-2xl font-black text-gray-900" x-text="viewerTitle"></h3>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">{{ $vehicle->plateNo }}</p>
                    </div>
                    <a :href="viewerSrc" download class="text-[10px] font-bold text-blue-600 bg-blue-50 px-3 py-1.5 rounded-lg hover:bg-blue-100 transition flex items-center gap-1">
                        <i class="fas fa-download"></i> Download
                    </a>
                </div>
                <button @click="viewerOpen = false" class="w-8 h-8 rounded-full bg-gray-50 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition"><i class="fas fa-times"></i></button>
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

    <div x-show="blockModalOpen" class="fixed inset-0 z-[60] flex items-center justify-center bg-gray-900/60 backdrop-blur-sm px-4" x-transition.opacity x-cloak>
        <form action="{{ route('staff.fleet.maintenance.store', $vehicle->VehicleID) }}" method="POST" 
              class="bg-white/95 backdrop-blur-xl border border-white/50 rounded-[2rem] shadow-2xl w-full max-w-sm overflow-hidden transform transition-all" 
              @click.away="blockModalOpen = false">
            @csrf
            
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-white/50">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Block Schedule</h3>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-0.5" x-text="dateRangeText"></p>
                </div>
                <button type="button" @click="blockModalOpen = false" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
            </div>
            
            <div class="p-6 space-y-5">
                <input type="hidden" name="start_date" x-model="selectedStart">
                <input type="hidden" name="end_date" x-model="selectedEnd">

                <div class="space-y-3">
                    <div x-show="!allDay" x-transition class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">Start Time</label>
                            <div class="relative">
                                <select name="start_time" class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2 text-sm font-bold text-gray-800 outline-none focus:border-gray-900 transition-colors appearance-none">
                                    @for($i = 0; $i < 24; $i++)
                                        <option value="{{ sprintf('%02d:00', $i) }}">{{ sprintf('%02d:00', $i) }}</option>
                                    @endfor
                                </select>
                                <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-gray-400">
                                    <i class="fas fa-chevron-down text-[10px]"></i>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">End Time</label>
                            <div class="relative">
                                <select name="end_time" class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2 text-sm font-bold text-gray-800 outline-none focus:border-gray-900 transition-colors appearance-none">
                                    @for($i = 0; $i < 24; $i++)
                                        <option value="{{ sprintf('%02d:00', $i) }}" {{ $i == 23 ? 'selected' : '' }}>{{ sprintf('%02d:00', $i) }}</option>
                                    @endfor
                                </select>
                                <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-gray-400">
                                    <i class="fas fa-chevron-down text-[10px]"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between bg-white/60 p-3 rounded-xl border border-gray-100 cursor-pointer hover:bg-white transition-colors" @click="allDay = !allDay">
                        <span class="text-xs font-bold text-gray-700">Block Whole Day</span>
                        <div class="w-10 h-5 bg-gray-200 rounded-full relative transition-colors" :class="allDay ? 'bg-gray-900' : 'bg-gray-200'">
                            <div class="w-3 h-3 bg-white rounded-full absolute top-1 left-1 transition-transform" :class="allDay ? 'translate-x-5' : ''"></div>
                        </div>
                        <input type="hidden" name="all_day" :value="allDay ? 'true' : 'false'">
                    </div>
                </div>

                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-2">Select Reason</label>
                    <div class="flex flex-col gap-2">
                        <button type="button" @click="blockType = 'maintenance'" class="w-full text-left px-4 py-3 rounded-xl text-xs font-bold border transition-all capitalize flex justify-between items-center group" :class="blockType === 'maintenance' ? 'bg-red-50 border-red-200 text-red-600 shadow-sm' : 'bg-white text-gray-600 border-gray-200 hover:border-red-300 hover:text-red-500'">
                            <div class="flex items-center gap-2"><div class="w-2 h-2 rounded-full bg-red-500"></div> Maintenance</div>
                            <i class="fas fa-check text-red-600" x-show="blockType === 'maintenance'"></i>
                        </button>
                        <button type="button" @click="blockType = 'delivery'" class="w-full text-left px-4 py-3 rounded-xl text-xs font-bold border transition-all capitalize flex justify-between items-center group" :class="blockType === 'delivery' ? 'bg-blue-50 border-blue-200 text-blue-600 shadow-sm' : 'bg-white text-gray-600 border-gray-200 hover:border-blue-300 hover:text-blue-500'">
                            <div class="flex items-center gap-2"><div class="w-2 h-2 rounded-full bg-blue-500"></div> Delivery</div>
                            <i class="fas fa-check text-blue-600" x-show="blockType === 'delivery'"></i>
                        </button>
                        <button type="button" @click="blockType = 'holiday'" class="w-full text-left px-4 py-3 rounded-xl text-xs font-bold border transition-all capitalize flex justify-between items-center group" :class="blockType === 'holiday' ? 'bg-purple-50 border-purple-200 text-purple-600 shadow-sm' : 'bg-white text-gray-600 border-gray-200 hover:border-purple-300 hover:text-purple-500'">
                            <div class="flex items-center gap-2"><div class="w-2 h-2 rounded-full bg-purple-500"></div> Holiday</div>
                            <i class="fas fa-check text-purple-600" x-show="blockType === 'holiday'"></i>
                        </button>
                        <button type="button" @click="blockType = 'other'" class="w-full text-left px-4 py-3 rounded-xl text-xs font-bold border transition-all capitalize flex justify-between items-center group" :class="blockType === 'other' ? 'bg-gray-100 border-gray-300 text-gray-800 shadow-sm' : 'bg-white text-gray-600 border-gray-200 hover:border-gray-300 hover:text-gray-800'">
                            <div class="flex items-center gap-2"><div class="w-2 h-2 rounded-full bg-gray-500"></div> Other</div>
                            <i class="fas fa-check text-gray-800" x-show="blockType === 'other'"></i>
                        </button>
                    </div>
                    <input type="hidden" name="type" x-model="blockType">
                </div>

                <div x-show="blockType === 'maintenance'" class="space-y-3 pt-2 border-t border-gray-100">
                    <select name="maintenance_desc" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm font-bold text-gray-800 outline-none focus:border-red-500 transition-colors">
                        <option>Regular Service</option>
                        <option>Tire Change</option>
                        <option>Battery Replacement</option>
                        <option>Major Repair</option>
                        <option>Inspection</option>
                    </select>
                    <input type="number" name="maintenance_cost" placeholder="Cost (RM)" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm font-bold text-gray-800 outline-none focus:border-red-500 transition-colors">
                </div>

                <div x-show="blockType === 'delivery'" class="pt-2 border-t border-gray-100">
                    <input type="text" name="ref_id" placeholder="Related Booking ID (Required)" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm font-bold text-gray-800 outline-none focus:border-blue-500 transition-colors" :required="blockType === 'delivery'">
                    <input type="hidden" name="reason" value="Vehicle Delivery Logistics">
                </div>

                <div x-show="blockType === 'other' || blockType === 'holiday'" class="pt-2 border-t border-gray-100">
                    <input type="text" name="reason" placeholder="Enter specific remarks (Required)..." class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm font-bold text-gray-800 outline-none focus:border-gray-900 transition-colors" :required="blockType === 'other' || blockType === 'holiday'">
                </div>

                <button type="submit" class="w-full bg-red-600 text-white py-4 rounded-xl font-bold text-sm uppercase tracking-wider shadow-lg hover:bg-red-700 transition transform active:scale-[0.98]">
                    Confirm Block
                </button>
            </div>
        </form>
    </div>

</div>

<script>
    function fleetShow() {
        return {
            viewerOpen: false,
            blockModalOpen: false,
            
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
        
        console.table(events); 

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: false,
            height: 'auto',
            selectable: true,
            eventDisplay: 'block', // FORCE LABELS
            eventTimeFormat: { hour: '2-digit', minute: '2-digit', meridiem: false, hour12: false },
            nextDayThreshold: '00:00:00', // Ensures times ending at midnight don't bleed into next day
            
            select: function(info) {
                let alpine = Alpine.$data(document.querySelector('[x-data]'));
                alpine.selectedStart = info.startStr;
                
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
            
            // POPUP & CLICK LOGIC
            eventClick: function(info) {
                var props = info.event.extendedProps;
                
                // 1. BOOKING POPUP
                if (info.event.extendedProps.type === 'booking') {
                    // Format dates for display
                    let start = info.event.start.toLocaleString([], {month:'short', day:'numeric', hour:'2-digit', minute:'2-digit'});
                    let end = info.event.end ? info.event.end.toLocaleString([], {month:'short', day:'numeric', hour:'2-digit', minute:'2-digit'}) : '';

                    Swal.fire({
                        title: info.event.title, // Customer Name
                        html: `<div class="text-left text-sm text-gray-600 font-sans">
                                <div class="bg-orange-50 p-3 rounded-lg border border-orange-100 mb-3">
                                    <p class="font-bold text-orange-800 text-xs uppercase tracking-wider mb-1">Booking Time</p>
                                    <p class="font-bold text-gray-900">${start} <span class="text-gray-400 mx-1">to</span> ${end}</p>
                                </div>
                                <p><strong>Status:</strong> ${props.status}</p>
                               </div>`,
                        showCancelButton: true,
                        confirmButtonText: 'View Full Details',
                        cancelButtonText: 'Close',
                        confirmButtonColor: '#f97316',
                        cancelButtonColor: '#1f2937',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Extract booking ID from event ID "booking_123"
                            let bookingId = info.event.id.replace('booking_', '');
                            window.location.href = `/staff/bookings/${bookingId}`;
                        }
                    });
                }
                // 2. MAINTENANCE/BLOCK POPUP
                else if (props.type === 'block') {
                    let desc = props.description || props.reason || '-';
                    let extraHtml = '';
                    if(props.cost > 0) extraHtml += `<div class="mt-2 text-red-600 font-bold">Cost: RM ${props.cost}</div>`;
                    if(props.ref_id) extraHtml += `<div class="mt-2 text-blue-600 font-bold">Ref: ${props.ref_id}</div>`;

                    Swal.fire({
                        title: info.event.title,
                        html: `<div class="text-left text-sm text-gray-600 font-sans">
                                <p><strong>Time:</strong> ${info.event.allDay ? 'Whole Day' : info.event.start.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) + ' - ' + (info.event.end ? info.event.end.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) : '')}</p>
                                <hr class="my-3 border-gray-100">
                                <p><strong>Details:</strong> ${desc}</p>
                                ${extraHtml}
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
            },
            
            eventDidMount: function(info) {
                if (!info.event.allDay) {
                    // Partial Day Events: White background, Colored Text, Dotted Border
                    info.el.style.backgroundColor = info.event.backgroundColor;
                    info.el.style.opacity = '0.6'; // Lower opacity
                    info.el.style.color = info.event.backgroundColor; 
                    info.el.style.border = '2px dotted ' + info.event.backgroundColor; // Fixed missing space
                    info.el.style.fontWeight = 'bold';
                } else {
                    // Full Day Events: Normal Solid Color
                    info.el.style.opacity = '1';
                    info.el.style.border = 'none';
                    info.el.style.color = '#ffffff';
                    info.el.style.backgroundColor = info.event.backgroundColor;
                }
            }
        });
        
        calendar.render();

        document.getElementById('customCalendarTitle').innerText = calendar.view.title;
        document.getElementById('prevBtn').addEventListener('click', function() { calendar.prev(); document.getElementById('customCalendarTitle').innerText = calendar.view.title; });
        document.getElementById('nextBtn').addEventListener('click', function() { calendar.next(); document.getElementById('customCalendarTitle').innerText = calendar.view.title; });
        document.getElementById('todayBtn').addEventListener('click', function() { calendar.today(); document.getElementById('customCalendarTitle').innerText = calendar.view.title; });
    });
</script>
@endsection