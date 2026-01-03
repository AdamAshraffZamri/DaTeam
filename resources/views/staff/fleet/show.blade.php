@extends('layouts.staff')

@section('content')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- WRAPPER --}}
<div class="min-h-screen bg-gray-100 rounded-2xl p-6" x-data="{ activeTab: 'service', serviceModalOpen: false }">
    <div class="max-w-7xl mx-auto">

        {{-- === HEADER === --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <div>
                <a href="{{ route('staff.fleet.index') }}" class="inline-flex items-center text-gray-500 hover:text-gray-900 mb-4 transition">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Fleet
                </a>
                <div class="flex items-baseline gap-3">
                    <h1 class="text-3xl font-black text-gray-900">{{ $vehicle->model }}</h1>
                    <span class="px-3 py-1 rounded-md bg-gray-200 text-gray-600 text-sm font-mono font-bold">{{ $vehicle->plateNo }}</span>
                </div>
            </div>
            
            <div class="flex gap-3">
                <a href="{{ route('staff.fleet.edit', $vehicle->VehicleID ?? $vehicle->id) }}" class="bg-white text-gray-700 border border-gray-200 px-6 py-3 rounded-2xl font-bold text-sm shadow-sm hover:bg-gray-50 hover:shadow-md transition-all transform hover:scale-105 active:scale-95 flex items-center gap-2">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <form action="{{ route('staff.fleet.destroy', $vehicle->VehicleID ?? $vehicle->id) }}" method="POST" onsubmit="return confirm('Delete vehicle?');">
                    @csrf @method('DELETE')
                    <button type="submit" class="bg-red-50 text-red-600 border border-red-100 px-6 py-3 rounded-2xl font-bold text-sm shadow-sm hover:bg-red-100 hover:shadow-md transition-all transform hover:scale-105 active:scale-95 flex items-center gap-2">
                        <i class="fas fa-trash-alt"></i> Delete
                    </button>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- LEFT COL --}}
            <div class="lg:col-span-2 space-y-8">
                
                {{-- INFO CARD --}}
                <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-8 flex flex-col md:flex-row gap-8">
                    <div class="w-full md:w-1/3 aspect-[4/3] bg-gray-50 rounded-2xl flex items-center justify-center overflow-hidden border border-gray-100 shrink-0">
                        @if($vehicle->image) <img src="{{ asset('storage/' . $vehicle->image) }}" class="w-full h-full object-cover"> @else <i class="fas fa-car text-4xl text-gray-300"></i> @endif
                    </div>
                    <div class="flex-1 grid grid-cols-2 gap-y-6 gap-x-4">
                        <div><span class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest">Type</span><span class="font-bold text-gray-900">{{ $vehicle->type }} ({{ $vehicle->year }})</span></div>
                        <div><span class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest">Mileage</span><span class="font-bold text-gray-900">{{ number_format($currentMileage) }} km</span></div>
                        <div><span class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest">Color</span><span class="font-bold text-gray-900">{{ $vehicle->color }}</span></div>
                        <div><span class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest">Fuel</span><span class="font-bold text-gray-900">{{ $vehicle->fuelType }}</span></div>
                        <div class="col-span-2 pt-4 border-t border-gray-100">
                            <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Owner</span>
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 text-xs font-bold">{{ substr($vehicle->owner_name ?? 'H', 0, 1) }}</div>
                                <span class="text-sm font-bold text-gray-700">{{ $vehicle->owner_name ?? 'Hasta Travel' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- === APPLE STYLE CALENDAR === --}}
                <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-8">
                    <div class="flex justify-between items-end mb-6">
                        <div>
                            <h2 id="customCalendarTitle" class="text-2xl font-bold text-gray-900 tracking-tight"></h2>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mt-1">Vehicle Schedule</p>
                        </div>
                        <div class="flex items-center gap-1">
                            <button id="prevBtn" class="w-8 h-8 rounded-full hover:bg-gray-100 flex items-center justify-center text-gray-600 transition"><i class="fas fa-chevron-left text-xs"></i></button>
                            <button id="todayBtn" class="text-xs font-bold text-gray-900 hover:bg-gray-100 px-3 py-1.5 rounded-lg transition uppercase tracking-wide">Today</button>
                            <button id="nextBtn" class="w-8 h-8 rounded-full hover:bg-gray-100 flex items-center justify-center text-gray-600 transition"><i class="fas fa-chevron-right text-xs"></i></button>
                        </div>
                    </div>

                    <div id="calendar" class="apple-calendar text-xs"></div>

                    <div class="flex gap-4 mt-6 border-t border-dashed border-gray-100 pt-4">
                         <div class="flex items-center gap-2">
                             <div class="w-2 h-2 rounded-full bg-orange-500 shadow-sm"></div>
                             <span class="text-[10px] font-medium text-gray-400 uppercase tracking-wider">Booked</span>
                         </div>
                         <div class="flex items-center gap-2">
                             <div class="w-2 h-2 rounded-full bg-red-500 shadow-sm"></div>
                             <span class="text-[10px] font-medium text-gray-400 uppercase tracking-wider">Maintenance</span>
                         </div>
                         <div class="flex items-center gap-2">
                             <div class="w-2 h-2 rounded-full bg-gray-500 shadow-sm"></div>
                             <span class="text-[10px] font-medium text-gray-400 uppercase tracking-wider">Blocked</span>
                         </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT COL: TABS --}}
            <div class="space-y-6">
                <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden flex flex-col h-full min-h-[500px]">
                    <div class="flex border-b border-gray-100">
                        <button @click="activeTab = 'service'" :class="activeTab === 'service' ? 'bg-red-50 text-red-600 border-b-2 border-red-500' : 'text-gray-400 hover:bg-gray-50'" class="flex-1 py-4 text-xs font-bold uppercase tracking-wider transition-all">Service</button>
                        <button @click="activeTab = 'history'" :class="activeTab === 'history' ? 'bg-purple-50 text-purple-600 border-b-2 border-purple-500' : 'text-gray-400 hover:bg-gray-50'" class="flex-1 py-4 text-xs font-bold uppercase tracking-wider transition-all">Inspection</button>
                        <button @click="activeTab = 'meter'" :class="activeTab === 'meter' ? 'bg-blue-50 text-blue-600 border-b-2 border-blue-500' : 'text-gray-400 hover:bg-gray-50'" class="flex-1 py-4 text-xs font-bold uppercase tracking-wider transition-all">Meter</button>
                    </div>
                    <div class="p-6 flex-1 bg-gray-50/30">
                        
                        {{-- SERVICE TAB --}}
                        <div x-show="activeTab === 'service'" class="space-y-6">
                            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm space-y-4">
                                <div class="flex justify-between items-center">
                                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Current Status</span>
                                    @if($serviceDue)
                                        <span class="px-3 py-1 rounded-full bg-red-100 text-red-600 text-xs font-bold flex items-center gap-1 animate-pulse border border-red-200"><i class="fas fa-wrench"></i> {{ $serviceMsg }}</span>
                                    @else
                                        <span class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs font-bold flex items-center gap-1 border border-green-200"><i class="fas fa-check-circle"></i> Vehicle Healthy</span>
                                    @endif
                                </div>
                                <button @click="serviceModalOpen = true" type="button" class="w-full bg-gray-900 text-white border border-gray-900 px-4 py-3 rounded-xl font-bold text-sm shadow-md hover:bg-gray-800 hover:shadow-lg transition-all transform active:scale-[0.98] flex items-center justify-center gap-2">
                                    <i class="fas fa-plus"></i> Log New Service
                                </button>
                            </div>

                            <div class="space-y-4">
                                <div class="flex justify-between items-center px-1">
                                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Log History</h4>
                                    <span class="text-[10px] font-bold bg-gray-200 px-2 py-0.5 rounded">{{ $vehicle->maintenances->count() }} Records</span>
                                </div>
                                @forelse($vehicle->maintenances->sortByDesc('date') as $maint)
                                    <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm hover:border-red-200 transition group cursor-default">
                                        <div class="flex justify-between items-start mb-2">
                                            <div><span class="text-xs font-black text-gray-900 block">{{ \Carbon\Carbon::parse($maint->date)->format('d M Y') }}</span><span class="text-[10px] text-gray-400">Ref: #{{ $maint->MaintenanceID }}</span></div>
                                            <span class="text-xs font-bold text-red-600 bg-red-50 px-2 py-1 rounded-lg">RM {{ number_format($maint->cost, 2) }}</span>
                                        </div>
                                        <p class="text-xs text-gray-600 leading-relaxed font-medium">{{ $maint->description }}</p>
                                    </div>
                                @empty
                                    <div class="text-center py-6"><p class="text-xs text-gray-400 font-medium">No service records found.</p></div>
                                @endforelse
                            </div>
                        </div>

                        {{-- INSPECTION TAB --}}
                        <div x-show="activeTab === 'history'" class="space-y-4">
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Inspection Logs</h4>
                            @forelse($vehicle->bookings->sortByDesc('created_at') as $booking)
                                @if($booking->inspections->count() > 0)
                                    <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
                                        <div class="flex justify-between mb-2"><span class="text-xs font-bold text-gray-900">#{{ $booking->bookingID }}</span><span class="text-[10px] text-gray-400">{{ $booking->created_at->format('d M') }}</span></div>
                                        <div class="flex gap-2 overflow-x-auto pb-2 custom-scrollbar">
                                            @foreach($booking->inspections as $inspection)
                                                @php $photos = json_decode($inspection->photosBefore ?? $inspection->photosAfter); @endphp
                                                @if($photos) <a href="{{ asset('storage/'.$photos[0]) }}" target="_blank" class="shrink-0 w-12 h-12 rounded-lg overflow-hidden border border-gray-200"><img src="{{ asset('storage/'.$photos[0]) }}" class="w-full h-full object-cover"></a> @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @empty
                                <div class="text-center py-10"><i class="fas fa-clipboard-check text-gray-300 text-2xl mb-2"></i><p class="text-xs text-gray-400">No inspections.</p></div>
                            @endforelse
                        </div>

                        {{-- METER TAB --}}
                        <div x-show="activeTab === 'meter'" class="space-y-4">
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Mileage Logs</h4>
                            @forelse($vehicle->bookings->where('bookingStatus', 'Completed')->sortByDesc('returnDate') as $booking)
                                <div class="bg-white p-4 rounded-xl border border-gray-100 flex justify-between items-center shadow-sm">
                                    <div><span class="text-xs font-bold text-gray-900 block">Return</span><span class="text-[10px] text-gray-400">{{ \Carbon\Carbon::parse($booking->returnDate)->format('d M Y') }}</span></div>
                                    <div class="text-right"><span class="block text-sm font-black text-blue-600">{{ number_format($booking->return_mileage ?? 0) }} km</span></div>
                                </div>
                            @empty
                                <div class="text-center py-10"><i class="fas fa-tachometer-alt text-gray-300 text-2xl mb-2"></i><p class="text-xs text-gray-400">No logs.</p></div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- === SERVICE MODAL (HTML) === --}}
    <div x-show="serviceModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm px-4" 
         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         style="display: none;">
        
        <div class="bg-white/95 backdrop-blur-xl rounded-[2rem] shadow-2xl w-full max-w-md overflow-hidden transform transition-all border border-white/50" @click.away="serviceModalOpen = false">
            <div class="px-8 py-4 border-b border-gray-100 flex justify-between items-center">
                <h3 class="text-lg font-bold pb-0 text-gray-900">Log New Service</h3>
                <button @click="serviceModalOpen = false" class="text-gray-400 hover:text-gray-600 transition"><i class="fas fa-times"></i></button>
            </div>
            <div class="px-8 pb-8 pt-5">
                <form action="{{ route('staff.fleet.maintenance.store', $vehicle->VehicleID ?? $vehicle->id) }}" method="POST" class="space-y-6">
                    @csrf
                    {{-- Date --}}
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1.5 ml-1">Service Date</label>
                        <div class="relative">
                            <i class="fas fa-calendar-alt absolute left-4 top-3.5 text-gray-400 text-sm"></i>
                            <input type="date" name="date" value="{{ date('Y-m-d') }}" class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-10 pr-4 py-3 text-sm font-bold text-gray-800 outline-none focus:bg-white focus:border-gray-900 focus:ring-1 focus:ring-gray-900 transition-all cursor-pointer">
                        </div>
                    </div>
                    
                    {{-- Description --}}
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1.5 ml-1">Description</label>
                        <input list="service-options" name="description" placeholder="e.g. Oil change..." class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm font-medium text-gray-800 outline-none focus:bg-white focus:border-gray-900 focus:ring-1 focus:ring-gray-900 transition-all placeholder:text-gray-400">
                        <datalist id="service-options">
                            <option value="Regular Service (Oil & Filter)">
                            <option value="Tire Change / Rotation">
                            <option value="Battery Replacement">
                            <option value="Brake Inspection">
                        </datalist>
                    </div>

                    {{-- Cost --}}
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1.5 ml-1">Total Cost (RM)</label>
                        <input type="number" name="cost" step="0.01" placeholder="0.00" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm font-bold text-gray-800 outline-none focus:bg-white focus:border-gray-900 focus:ring-1 focus:ring-gray-900 transition-all">
                    </div>

                    {{-- Submit --}}
                    <div class="pt-2">
                        <button type="submit" class="w-full bg-gray-900 text-white py-3.5 rounded-xl font-bold text-xs uppercase tracking-wider shadow-lg hover:bg-black hover:shadow-xl transition-all transform active:scale-[0.98]">
                            Save Record
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<form id="blockForm" action="{{ route('staff.fleet.block', $vehicle->VehicleID ?? $vehicle->id) }}" method="POST" class="hidden">@csrf <input type="date" name="date" id="blockDateInput"></form>
<form id="unblockForm" action="{{ route('staff.fleet.unblock', $vehicle->VehicleID ?? $vehicle->id) }}" method="POST" class="hidden">@csrf <input type="date" name="date" id="unblockDateInput"></form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var events = @json($events);
        
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: false,
            height: 'auto',
            contentHeight: 'auto',
            events: events,
            dayMaxEvents: 2,
            fixedWeekCount: false,
            
            // 1. CLICK EMPTY DATE (Block Confirmation)
            dateClick: function(info) {
                Swal.fire({
                    html: `
                        <div class="p-6 relative text-left">
                            <button onclick="Swal.close()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition outline-none"><i class="fas fa-times text-sm"></i></button>
                            <h3 class="text-lg font-bold text-gray-800 mb-1">Block Date</h3>
                            <p class="text-sm text-gray-500 font-normal mb-5">Mark <span class="font-bold text-gray-800">${info.dateStr}</span> as unavailable?</p>
                            <div class="flex gap-3">
                                <button onclick="Swal.close()" class="flex-1 py-2.5 rounded-xl border border-gray-200 text-gray-500 text-xs font-semibold hover:bg-gray-50 transition">Cancel</button>
                                <button onclick="document.getElementById('blockDateInput').value = '${info.dateStr}'; document.getElementById('blockForm').submit();" class="flex-1 py-2.5 rounded-xl bg-gray-900 text-white text-xs font-semibold shadow-lg shadow-gray-200 hover:bg-black transition">Confirm</button>
                            </div>
                        </div>
                    `,
                    showConfirmButton: false,
                    width: '320px'
                });
            },

            // 2. CLICK EXISTING EVENT
            eventClick: function(info) {
                var props = info.event.extendedProps;

                // === A. BLOCKED DATE ===
                // Checks for 'maintenance_block' OR if ID starts with 'blk_' to fix undefined error
                if (props.type === 'maintenance_block' || (info.event.id && String(info.event.id).startsWith('blk_'))) {
                    
                    let dateToUnblock = props.date_value || info.event.startStr;

                    Swal.fire({
                        html: `
                            <div class="p-5 relative text-left">
                                <button onclick="Swal.close()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition outline-none"><i class="fas fa-times text-sm"></i></button>
                                <div class="flex items-center gap-3 mb-5">
                                    <div class="w-10 h-10 rounded-full bg-red-50 flex items-center justify-center shrink-0"><i class="fas fa-ban text-red-500 text-sm"></i></div>
                                    <div><h3 class="text-base font-bold text-gray-800">Unavailable</h3><p class="text-[10px] text-gray-400 font-medium uppercase tracking-wider">Blocked Date</p></div>
                                </div>
                                <div class="bg-gray-50/50 rounded-xl p-3 border border-gray-100 mb-5 flex justify-between items-center">
                                    <span class="text-xs text-gray-500 font-medium">Date</span>
                                    <span class="text-sm font-semibold text-gray-800">${info.event.start.toLocaleDateString()}</span>
                                </div>
                                <button onclick="document.getElementById('unblockDateInput').value = '${dateToUnblock}'; document.getElementById('unblockForm').submit();" class="w-full py-3 rounded-xl bg-white border border-red-100 text-red-600 text-xs font-bold hover:bg-red-50 transition shadow-sm"><i class="fas fa-trash-alt mr-1"></i> Unblock Date</button>
                            </div>
                        `,
                        showConfirmButton: false,
                        width: '340px'
                    });

                // === B. MAINTENANCE LOG ===
                } else if (props.type === 'maintenance_log') {
                    Swal.fire({
                        html: `
                            <div class="p-5 relative text-left">
                                <button onclick="Swal.close()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition outline-none"><i class="fas fa-times text-sm"></i></button>
                                <div class="flex items-center gap-3 mb-5">
                                    <div class="w-10 h-10 rounded-full bg-orange-50 flex items-center justify-center shrink-0"><i class="fas fa-wrench text-orange-500 text-sm"></i></div>
                                    <div><h3 class="text-base font-bold text-gray-800">Maintenance</h3><p class="text-[10px] text-gray-400 font-medium uppercase tracking-wider">${info.event.start.toLocaleDateString()}</p></div>
                                </div>
                                <div class="bg-gray-50/80 p-4 rounded-2xl border border-gray-100 mb-5">
                                    <p class="text-xs text-gray-500 font-medium mb-1">Description</p>
                                    <p class="text-sm font-semibold text-gray-800 leading-snug">${props.desc}</p>
                                </div>
                                <div class="flex justify-between items-end border-t border-dashed border-gray-200 pt-4">
                                    <span class="text-xs text-gray-400 font-medium uppercase tracking-wider">Cost</span>
                                    <span class="text-xl font-bold text-gray-900 tracking-tight">RM ${props.cost}</span>
                                </div>
                            </div>
                        `,
                        showConfirmButton: false,
                        width: '360px'
                    });

                // === C. BOOKING DETAILS ===
                } else {
                    let statusColor = 'text-gray-500 bg-gray-50';
                    if(props.status === 'Active' || props.status === 'Approved') statusColor = 'text-green-600 bg-green-50';
                    if(props.status === 'Completed') statusColor = 'text-blue-600 bg-blue-50';
                    if(props.status === 'Submitted') statusColor = 'text-amber-600 bg-amber-50';

                    Swal.fire({
                        html: `
                            <div class="p-6 relative text-left font-sans">
                                <button onclick="Swal.close()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition outline-none"><i class="fas fa-times text-sm"></i></button>
                                <div class="flex justify-between items-center mb-6 pr-6">
                                    <div><p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-0.5">Booking Ref</p><h3 class="text-2xl font-bold text-gray-900">#${info.event.id}</h3></div>
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wide ${statusColor}">${props.status}</span>
                                </div>
                                <div class="flex items-center gap-3 mb-6 pb-6 border-b border-dashed border-gray-200">
                                    <div class="w-9 h-9 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 text-xs"><i class="fas fa-user"></i></div>
                                    <div class="overflow-hidden"><p class="text-xs font-bold text-gray-800 truncate">${props.cust_name || 'Guest'}</p><p class="text-[10px] text-gray-400 font-medium font-mono">${props.cust_phone || '-'}</p></div>
                                </div>
                                <div class="relative pl-3 mb-6 space-y-5">
                                    <div class="absolute left-[5px] top-2 bottom-2 w-[1px] bg-gray-200"></div>
                                    <div class="relative flex gap-3">
                                        <div class="w-2.5 h-2.5 rounded-full bg-white border-2 border-gray-400 z-10 shrink-0 translate-x-[-4px]"></div>
                                        <div><p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-0.5">Pickup</p><p class="text-xs font-bold text-gray-800 leading-tight mb-0.5">${props.pickup || 'Location'}</p><p class="text-[10px] text-gray-500 bg-gray-50 inline-block px-1.5 rounded">${info.event.start.toLocaleDateString()}</p></div>
                                    </div>
                                    <div class="relative flex gap-3">
                                        <div class="w-2.5 h-2.5 rounded-full bg-white border-2 border-orange-400 z-10 shrink-0 translate-x-[-4px]"></div>
                                        <div><p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-0.5">Return</p><p class="text-xs font-bold text-gray-800 leading-tight mb-0.5">${props.dropoff || 'Location'}</p><p class="text-[10px] text-gray-500 bg-gray-50 inline-block px-1.5 rounded">${info.event.end ? info.event.end.toLocaleDateString() : info.event.start.toLocaleDateString()}</p></div>
                                    </div>
                                </div>
                                <div class="flex gap-3 pt-2">
                                    <a href="/staff/bookings/${info.event.id}" class="flex-1 py-3 rounded-xl bg-gray-900 text-white text-xs font-bold text-center hover:bg-black transition shadow-lg shadow-gray-200">View Details</a>
                                </div>
                            </div>
                        `,
                        showConfirmButton: false,
                        width: '380px'
                    });
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

<style>
    /* CSS FOR GLASSY POPUPS */
    div:where(.swal2-container) div:where(.swal2-popup) {
        background: rgba(255, 255, 255, 0.95) !important;
        backdrop-filter: blur(16px) !important;
        -webkit-backdrop-filter: blur(16px) !important;
        border: 1px solid rgba(255, 255, 255, 0.8) !important;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15) !important;
        border-radius: 28px !important;
        padding: 0 !important;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
    }
    div:where(.swal2-html-container) { margin: 0 !important; padding: 0 !important; }
    
    /* CALENDAR STYLES */
    .apple-calendar { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
    .fc-theme-standard td, .fc-theme-standard th { border: 1px solid #f3f4f6 !important; }
    .fc-theme-standard .fc-scrollgrid { border: none !important; }
    .fc-col-header-cell-cushion { color: #9ca3af; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; padding-bottom: 10px !important; }
    .fc-daygrid-day-top { flex-direction: row !important; padding: 8px 0 0 8px !important; }
    .fc-daygrid-day-number { font-size: 13px; font-weight: 600; color: #374151; z-index: 2; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; border-radius: 50%; margin-left: 2px; }
    .fc-day-today .fc-daygrid-day-number { background-color: #ef4444; color: white !important; }
    .fc-day-today { background: transparent !important; }
    .fc-event { border: none !important; border-radius: 6px !important; padding: 1px 2px !important; margin-top: 2px !important; box-shadow: 0 1px 2px rgba(0,0,0,0.05); font-size: 9px !important; font-weight: 700 !important; }
    .fc-daygrid-day:hover { background-color: #f9fafb !important; cursor: pointer; }
    .custom-scrollbar::-webkit-scrollbar { height: 4px; width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 4px; }
</style>
@endsection