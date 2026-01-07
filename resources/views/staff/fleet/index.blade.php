@extends('layouts.staff')

@section('content')
<div class="min-h-screen bg-slate-100 rounded-2xl p-6">
    <div class="max-w-7xl mx-auto">

        {{-- HEADER & ACTIONS --}}
        <div class="flex flex-col md:flex-row justify-between items-end mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-black text-gray-900">Fleet Inventory</h1>
                <p class="text-gray-500 mt-1 text-sm">Monitor vehicle status and availability.</p>
            </div>

            <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
                {{-- Search & Filters Form --}}
                <form action="{{ route('staff.fleet.index') }}" method="GET" id="filterForm" class="contents">
                    
                    {{-- 1. Search Bar --}}
                    <div class="relative w-full md:w-64 group">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search plate, model..." 
                            class="w-full bg-white border border-gray-200 text-gray-700 text-xs font-bold py-3.5 pl-10 pr-4 rounded-2xl focus:outline-none focus:border-orange-500 transition-all shadow-sm group-hover:shadow-md placeholder-gray-400">
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-hover:text-orange-500 transition-colors"></i>
                    </div>

                    {{-- 2. Model Filter --}}
                    <div class="relative w-full md:w-48">
                        <select name="model" onchange="this.form.submit()" 
                            class="w-full bg-white border border-gray-200 text-gray-700 text-xs font-bold py-3.5 px-4 rounded-2xl appearance-none focus:outline-none focus:border-orange-500 transition-all shadow-sm cursor-pointer hover:shadow-md">
                            <option value="all">All Models</option>
                            @foreach($vehicleModels as $model)
                                <option value="{{ $model }}" {{ request('model') == $model ? 'selected' : '' }}>{{ $model }}</option>
                            @endforeach
                        </select>
                        <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                    </div>

                    {{-- 3. Custom Status Dropdown --}}
                    <div class="relative w-full md:w-[180px]" id="customDropdown">
                        <input type="hidden" name="status" id="statusInput" value="{{ request('status', 'all') }}">
                        
                        <button type="button" onclick="toggleDropdown()" 
                            class="w-full flex items-center justify-between bg-white border border-gray-200 text-gray-700 text-xs font-bold py-3.5 px-5 rounded-2xl hover:border-orange-500 hover:text-orange-600 transition-all shadow-sm hover:shadow-md group">
                            
                            <div class="flex items-center gap-2">
                                <i class="fas fa-filter text-orange-500"></i>
                                <span id="dropdownLabel" class="capitalize">
                                    {{ (request('status') == 'all' || !request('status')) ? 'All Status' : ucfirst(request('status')) }}
                                </span>
                            </div>

                            <i class="fas fa-chevron-down text-[10px] text-gray-400 group-hover:text-orange-500 transition-transform duration-300" id="dropdownArrow"></i>
                        </button>

                        <div id="dropdownMenu" 
                            class="absolute top-full right-0 mt-2 w-full bg-white border border-gray-100 rounded-2xl shadow-xl overflow-hidden hidden transform origin-top transition-all duration-200 z-50">
                            
                            <div onclick="selectStatus('all')" 
                                class="px-5 py-3 text-xs font-bold cursor-pointer transition-colors flex items-center justify-between border-b border-gray-50 last:border-0 {{ (request('status') == 'all' || !request('status')) ? 'bg-orange-50 text-orange-600' : 'text-gray-600 hover:bg-gray-50' }}">
                                <span>All Status</span>
                                @if(request('status') == 'all' || !request('status')) <i class="fas fa-check"></i> @endif
                            </div>
                            
                            <div onclick="selectStatus('active')" 
                                class="px-5 py-3 text-xs font-bold cursor-pointer transition-colors flex items-center justify-between border-b border-gray-50 last:border-0 {{ request('status') == 'active' ? 'bg-orange-50 text-orange-600' : 'text-gray-600 hover:bg-gray-50' }}">
                                <span>Active</span>
                                @if(request('status') == 'active') <i class="fas fa-check"></i> @endif
                            </div>

                            <div onclick="selectStatus('inactive')" 
                                class="px-5 py-3 text-xs font-bold cursor-pointer transition-colors flex items-center justify-between border-b border-gray-50 last:border-0 {{ request('status') == 'inactive' ? 'bg-orange-50 text-orange-600' : 'text-gray-600 hover:bg-gray-50' }}">
                                <span>Inactive</span>
                                @if(request('status') == 'inactive') <i class="fas fa-check"></i> @endif
                            </div>
                        </div>
                    </div>
                </form>

                {{-- Add Button --}}
                <a href="{{ route('staff.fleet.create') }}" class="bg-orange-600 hover:bg-orange-500 text-white px-6 py-3.5 rounded-2xl font-bold text-xs shadow-lg shadow-orange-900/20 transition-all transform hover:scale-105 flex items-center gap-2 shrink-0 whitespace-nowrap">
                    <i class="fas fa-plus"></i>
                    <span>Add Vehicle</span>
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 rounded-2xl p-4 flex items-center gap-4 shadow-sm animate-fade-in">
                <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-600 shrink-0 border border-green-200">
                    <i class="fas fa-check text-sm"></i>
                </div>
                <div>
                    <h4 class="text-sm font-black text-green-900">Success!</h4>
                    <p class="text-xs text-green-700 font-medium">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        {{-- VEHICLE LIST --}}
        <div class="space-y-3">
            @foreach($vehicles as $vehicle)
            {{-- LINK TO SHOW PAGE --}}
            <a href="{{ route('staff.fleet.show', $vehicle->VehicleID) }}" class="animate-fade-in fleet-item block bg-white rounded-xl p-2 pr-4 border border-gray-100 shadow-sm hover:shadow-md transition-all group">
                
                <div class="flex flex-col md:flex-row items-center gap-4 md:gap-8">
                    {{-- IMAGE --}}
                    <div class="w-full md:w-24 h-20 md:h-16 rounded-lg bg-gray-50 flex items-center justify-center overflow-hidden border border-gray-100 relative shrink-0">
                        @if($vehicle->image)
                            <img src="{{ asset('storage/'.$vehicle->image) }}" class="w-full h-full object-cover">
                        @else
                            <i class="fas fa-car text-gray-300 text-2xl"></i>
                        @endif
                        
                        {{-- UPDATED OVERLAY LOGIC: Only show Inactive if NOT Rented AND NOT Available --}}
                        @if(!$vehicle->availability && !$vehicle->isBookedToday)
                            <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-[1px] flex items-center justify-center">
                                <span class="text-[10px] font-bold text-white bg-black/50 px-2 py-0.5 rounded border border-white/20">Inactive</span>
                            </div>
                        @endif
                    </div>

                    {{-- DETAILS --}}
                    <div class="flex-1 w-full md:w-auto text-center md:text-left">
                        <label class="text-[9px] font-bold text-gray-400 uppercase tracking-wider block mb-0.5">Model</label>
                        <h3 class="text-base font-black text-gray-900 leading-tight">{{ $vehicle->model }}</h3>
                    </div>
                    <div class="flex-1 w-full md:w-auto text-center md:text-left border-t md:border-t-0 md:border-l border-gray-100 pt-2 md:pt-0 md:pl-6">
                        <label class="text-[9px] font-bold text-gray-400 uppercase tracking-wider block mb-0.5">Plate</label>
                        <p class="text-sm font-mono font-bold text-gray-700 bg-gray-50 inline-block px-2 py-0.5 rounded border border-gray-200">{{ $vehicle->plateNo }}</p>
                    </div>
                    
                    {{-- NEXT BOOKING DATE --}}
                    <div class="flex-1 w-full md:w-auto text-center md:text-left border-t md:border-t-0 md:border-l border-gray-100 pt-2 md:pt-0 md:pl-6">
                        <label class="text-[9px] font-bold text-gray-400 uppercase tracking-wider block mb-0.5">Next Booking</label>
                        <div class="flex items-center justify-center md:justify-start gap-2">
                            <i class="fas fa-calendar-alt text-gray-300 text-xs"></i>
                            <span class="text-sm font-bold {{ $vehicle->nextBookingDate ? 'text-orange-600' : 'text-gray-400' }}">
                                {{ $vehicle->nextBookingDate ? $vehicle->nextBookingDate->format('d M Y') : 'No Upcoming' }}
                            </span>
                        </div>
                    </div>

                    {{-- STATUS & TOGGLE (Click.stop prevents navigation when toggling) --}}
                    <div class="flex items-center justify-between w-full md:w-auto md:justify-end gap-6 border-t md:border-t-0 border-gray-100 pt-3 md:pt-0 mt-2 md:mt-0">
                        <div class="text-right">
                            @if($vehicle->isBookedToday)
                                <div class="flex items-center bg-orange-50 px-3 py-1.5 rounded-lg border border-orange-100">
                                    <span class="w-1.5 h-1.5 rounded-full bg-orange-500 mr-2 animate-pulse"></span><span class="text-[10px] font-bold text-orange-600 uppercase">Rented</span>
                                </div>
                            @elseif($vehicle->availability == 0)
                                <div class="flex items-center bg-gray-100 px-3 py-1.5 rounded-lg border border-gray-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400 mr-2"></span><span class="text-[10px] font-bold text-gray-500 uppercase">Offline</span>
                                </div>
                            @else
                                <div class="flex items-center bg-green-50 px-3 py-1.5 rounded-lg border border-green-100">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500 mr-2"></span><span class="text-[10px] font-bold text-green-700 uppercase">Ready</span>
                                </div>
                            @endif
                        </div>
                        <form action="{{ route('staff.fleet.status', $vehicle->VehicleID) }}" method="POST" @click.stop>
                            @csrf
                            {{-- UPDATED BUTTON LOGIC: Show Active (White) if Available OR Rented --}}
                            <button type="submit" class="w-9 h-9 rounded-lg flex items-center justify-center transition-all border shadow-sm {{ ($vehicle->availability || $vehicle->isBookedToday) ? 'bg-white text-gray-300 border-gray-200 hover:bg-red-50 hover:text-red-500' : 'bg-gray-800 text-white border-transparent hover:bg-gray-700' }}">
                                <i class="fas fa-power-off text-sm"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
        
        {{-- EMPTY STATE --}}
        @if($vehicles->isEmpty())
        <div class="flex flex-col items-center justify-center py-20 text-center animate-fade-in">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4"><i class="fas fa-car-crash text-gray-300 text-2xl"></i></div>
            <p class="text-gray-500 font-medium">No vehicles found matching criteria.</p>
            <a href="{{ route('staff.fleet.index') }}" class="mt-4 text-xs font-bold text-orange-600 hover:text-orange-500">Clear Filters</a>
        </div>
        @endif
    </div>
</div>

<script>
    // --- CUSTOM DROPDOWN LOGIC ---
    function toggleDropdown() {
        const menu = document.getElementById('dropdownMenu');
        const arrow = document.getElementById('dropdownArrow');
        
        if (menu.classList.contains('hidden')) {
            // Open
            menu.classList.remove('hidden');
            menu.classList.add('animate-fade-in-down');
            if(arrow) arrow.style.transform = 'rotate(180deg)';
        } else {
            // Close
            menu.classList.add('hidden');
            if(arrow) arrow.style.transform = 'rotate(0deg)';
        }
    }

    function selectStatus(value) {
        document.getElementById('statusInput').value = value;
        document.getElementById('filterForm').submit();
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('customDropdown');
        const menu = document.getElementById('dropdownMenu');
        const arrow = document.getElementById('dropdownArrow');

        if (dropdown && !dropdown.contains(event.target)) {
            menu.classList.add('hidden');
            if(arrow) arrow.style.transform = 'rotate(0deg)';
        }
    });
</script>

<style>
    .no-scrollbar::-webkit-scrollbar { display: none; } 
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    
    @keyframes fade-in { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
    .animate-fade-in { animation: fade-in 0.2s ease-out forwards; }
    .animate-fade-in-down { animation: fade-in 0.15s ease-out forwards; }
</style>
@endsection