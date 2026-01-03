@extends('layouts.staff')

@section('content')
{{-- LIBRARIES --}}
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="min-h-screen bg-gray-100 p-6" x-data="{ activeTab: 'meter' }">
    <div class="max-w-7xl mx-auto">

        {{-- HEADER (Same as before) --}}
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
                <a href="{{ route('staff.fleet.edit', $vehicle->VehicleID ?? $vehicle->id) }}" class="bg-white text-gray-700 border border-gray-200 px-6 py-3 rounded-xl font-bold text-sm shadow-sm hover:bg-gray-50 hover:shadow-md transition-all transform hover:scale-105 active:scale-95 flex items-center gap-2">
                    <i class="fas fa-edit"></i> Modify
                </a>
                <form action="{{ route('staff.fleet.destroy', $vehicle->VehicleID ?? $vehicle->id) }}" method="POST" onsubmit="return confirm('Delete vehicle?');">
                    @csrf @method('DELETE')
                    <button type="submit" class="bg-red-50 text-red-600 border border-red-100 px-6 py-3 rounded-xl font-bold text-sm shadow-sm hover:bg-red-100 hover:shadow-md transition-all transform hover:scale-105 active:scale-95 flex items-center gap-2">
                        <i class="fas fa-trash-alt"></i> Delete
                    </button>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            {{-- LEFT COL: INFO & CALENDAR --}}
            <div class="lg:col-span-2 space-y-8">
                
                {{-- INFO CARD --}}
                <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-8 flex flex-col md:flex-row gap-8">
                    <div class="w-full md:w-1/3 aspect-[4/3] bg-gray-50 rounded-2xl flex items-center justify-center overflow-hidden border border-gray-100 shrink-0">
                        @if($vehicle->image)
                            <img src="{{ asset('storage/' . $vehicle->image) }}" class="w-full h-full object-cover">
                        @else
                            <i class="fas fa-car text-4xl text-gray-300"></i>
                        @endif
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

                {{-- CALENDAR CARD --}}
                <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-8">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Booking Schedule</h3>
                        <div class="flex gap-3 text-[10px] font-bold">
                            <span class="flex items-center gap-1"><div class="w-2 h-2 rounded-full bg-orange-500"></div> Booked</span>
                            <span class="flex items-center gap-1"><div class="w-2 h-2 rounded-full bg-red-500"></div> Blocked</span>
                        </div>
                    </div>
                    <div id="calendar" class="text-xs"></div>
                </div>
            </div>

            {{-- RIGHT COL: SWAPPED TABS --}}
            <div class="space-y-6">
                <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden flex flex-col h-full min-h-[500px]">
                    <div class="flex border-b border-gray-100">
                        <button @click="activeTab = 'history'" :class="activeTab === 'history' ? 'bg-purple-50 text-purple-600 border-b-2 border-purple-500' : 'text-gray-400 hover:bg-gray-50'" class="flex-1 py-4 text-xs font-bold uppercase tracking-wider transition-all">History</button>
                        <button @click="activeTab = 'meter'" :class="activeTab === 'meter' ? 'bg-blue-50 text-blue-600 border-b-2 border-blue-500' : 'text-gray-400 hover:bg-gray-50'" class="flex-1 py-4 text-xs font-bold uppercase tracking-wider transition-all">Meter</button>
                    </div>
                    <div class="p-6 flex-1 bg-gray-50/30">
                        {{-- History --}}
                        <div x-show="activeTab === 'history'" class="space-y-4">
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Inspection Logs</h4>
                            @forelse($vehicle->bookings->sortByDesc('created_at') as $booking)
                                @if($booking->inspections->count() > 0)
                                    <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
                                        <div class="flex justify-between mb-2">
                                            <span class="text-xs font-bold text-gray-900">#{{ $booking->bookingID }}</span>
                                            <span class="text-[10px] text-gray-400">{{ $booking->created_at->format('d M') }}</span>
                                        </div>
                                        <div class="flex gap-2 overflow-x-auto pb-2 custom-scrollbar">
                                            @foreach($booking->inspections as $inspection)
                                                @php $photos = json_decode($inspection->photosBefore ?? $inspection->photosAfter); @endphp
                                                @if($photos)
                                                    <a href="{{ asset('storage/'.$photos[0]) }}" target="_blank" class="shrink-0 w-12 h-12 rounded-lg overflow-hidden border border-gray-200"><img src="{{ asset('storage/'.$photos[0]) }}" class="w-full h-full object-cover"></a>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @empty
                                <div class="text-center py-10"><i class="fas fa-clipboard-check text-gray-300 text-2xl mb-2"></i><p class="text-xs text-gray-400">No data.</p></div>
                            @endforelse
                        </div>
                        {{-- Meter --}}
                        <div x-show="activeTab === 'meter'" class="space-y-4">
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Recorded Mileage</h4>
                            @forelse($vehicle->bookings->where('bookingStatus', 'Completed')->sortByDesc('returnDate') as $booking)
                                <div class="bg-white p-4 rounded-xl border border-gray-100 flex justify-between items-center shadow-sm">
                                    <div><span class="text-xs font-bold text-gray-900 block">Return</span><span class="text-[10px] text-gray-400">{{ \Carbon\Carbon::parse($booking->returnDate)->format('d M Y') }}</span></div>
                                    <div class="text-right"><span class="block text-sm font-black text-blue-600">{{ number_format($booking->return_mileage ?? 0) }} km</span></div>
                                </div>
                            @empty
                                <div class="text-center py-10"><i class="fas fa-tachometer-alt text-gray-300 text-2xl mb-2"></i><p class="text-xs text-gray-400">No data.</p></div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- FORMS --}}
<form id="blockForm" action="{{ route('staff.fleet.block', $vehicle->VehicleID ?? $vehicle->id) }}" method="POST" class="hidden">@csrf <input type="date" name="date" id="blockDateInput"></form>
<form id="unblockForm" action="{{ route('staff.fleet.unblock', $vehicle->VehicleID ?? $vehicle->id) }}" method="POST" class="hidden">@csrf <input type="date" name="date" id="unblockDateInput"></form>

{{-- CALENDAR LOGIC --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var bookings = @json($events);
        
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: { left: 'title', center: '', right: 'prev,next' },
            height: 350, contentHeight: 'auto',
            events: bookings,
            
            // 1. BLOCK DATE (Click Empty Cell)
            dateClick: function(info) {
                Swal.fire({
                    title: 'Block Date?', text: "Mark " + info.dateStr + " as Inactive?", icon: 'warning',
                    showCancelButton: true, confirmButtonColor: '#ef4444', confirmButtonText: 'Yes, Block'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('blockDateInput').value = info.dateStr;
                        document.getElementById('blockForm').submit();
                    }
                });
            },

            // 2. CLICK EVENT (Show Details OR Unblock)
            eventClick: function(info) {
                var props = info.event.extendedProps;

                // --- A. IF MAINTENANCE BLOCK ---
                if (props.type === 'maintenance') {
                    Swal.fire({
                        title: 'Unblock Date?', text: "Make active again?", icon: 'question',
                        showCancelButton: true, confirmButtonColor: '#22c55e', confirmButtonText: 'Yes, Unblock'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.getElementById('unblockDateInput').value = props.date_value;
                            document.getElementById('unblockForm').submit();
                        }
                    });
                } 
                // --- B. IF ACTUAL BOOKING (POPUP DETAILS) ---
                else {
                    Swal.fire({
                        title: '<strong>Booking Details</strong>',
                        html: `
                            <div class="text-left text-sm space-y-3 p-2">
                                <div class="flex justify-between border-b pb-2">
                                    <span class="text-gray-500 font-bold">Status</span>
                                    <span class="font-bold text-orange-600 uppercase">${props.status}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Customer</span>
                                    <span class="font-bold text-gray-900">${props.cust_name}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Phone</span>
                                    <span class="font-bold text-gray-900">${props.cust_phone}</span>
                                </div>
                                <div class="bg-gray-50 p-3 rounded-lg mt-2">
                                    <p class="text-xs text-gray-400 uppercase font-bold mb-1">Schedule</p>
                                    <p class="font-bold text-gray-800">${props.time}</p>
                                    <div class="mt-2 text-xs">
                                        <span class="block text-gray-500">Pick: <b class="text-gray-800">${props.pickup}</b></span>
                                        <span class="block text-gray-500">Drop: <b class="text-gray-800">${props.dropoff}</b></span>
                                    </div>
                                </div>
                                <div class="flex justify-between border-t pt-3 mt-2">
                                    <span class="text-gray-500 font-bold">Total Cost</span>
                                    <span class="font-black text-xl text-green-600">RM ${props.cost}</span>
                                </div>
                            </div>
                        `,
                        showCloseButton: true,
                        showConfirmButton: false,
                        focusConfirm: false
                    });
                }
            }
        });
        calendar.render();
    });
</script>

<style>
    .custom-scrollbar::-webkit-scrollbar { height: 4px; width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 4px; }
    .fc-toolbar-title { font-size: 14px !important; font-weight: 800 !important; color: #1f2937; }
    .fc-button { background-color: white !important; color: #9ca3af !important; border: 1px solid #f3f4f6 !important; font-size: 10px !important; padding: 4px 8px !important; box-shadow: none !important; }
    .fc-button:hover { color: #111827 !important; border-color: #d1d5db !important; }
    .fc-event { border: none !important; padding: 2px; font-size: 9px; border-radius: 4px; cursor: pointer; }
    .fc-daygrid-day:hover { background-color: #f9fafb; cursor: pointer; }
    .fc-theme-standard td, .fc-theme-standard th { border-color: #f3f4f6 !important; }
</style>
@endsection