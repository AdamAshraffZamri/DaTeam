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
                
                {{-- SEARCH & FILTERS FORM --}}
                <form action="{{ route('staff.fleet.index') }}" method="GET" id="filterForm" class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
                    
                    {{-- 1. Search Bar --}}
                    <div class="relative w-full md:w-64 group">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search plate, model..." 
                            class="w-full bg-white border border-gray-200 text-gray-700 text-xs font-bold py-3.5 pl-10 pr-4 rounded-2xl focus:outline-none focus:border-orange-500 transition-all shadow-sm group-hover:shadow-md placeholder-gray-400">
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-hover:text-orange-500 transition-colors"></i>
                    </div>

                    {{-- 2. Model Filter (With Visible Count) --}}
                    <div class="relative w-full md:w-[220px]" id="modelDropdown">
                        <input type="hidden" name="model" id="modelInput" value="{{ request('model', 'all') }}">
                        
                        {{-- Calculate Current Count for Display on Button --}}
                        @php
                            $currentModel = request('model', 'all');
                            $displayCount = ($currentModel == 'all') 
                                ? $vehicles->count() 
                                : $vehicles->where('model', $currentModel)->count();
                        @endphp

                        <button type="button" onclick="toggleModelDropdown()" 
                            class="w-full flex items-center justify-between bg-white border border-gray-200 text-gray-700 text-xs font-bold py-3.5 px-5 rounded-2xl hover:border-orange-500 hover:text-orange-600 transition-all shadow-sm hover:shadow-md group">
                            
                            <div class="flex items-center gap-2 truncate">
                                <i class="fas fa-car text-orange-500"></i>
                                <span class="capitalize truncate">
                                    {{ ($currentModel == 'all') ? 'All Models' : $currentModel }}
                                </span>
                                {{-- VISIBLE BADGE ON BUTTON --}}
                                <span class="flex items-center justify-center w-5 h-5 rounded-full text-[9px] bg-orange-100 text-orange-700 ml-1">
                                    {{ $displayCount }}
                                </span>
                            </div>

                            <i class="fas fa-chevron-down text-[10px] text-gray-400 group-hover:text-orange-500 transition-transform duration-300" id="modelDropdownArrow"></i>
                        </button>

                        <div id="modelMenu" class="absolute top-full right-0 mt-2 w-full bg-white border border-gray-100 rounded-2xl shadow-xl overflow-hidden hidden transform origin-top transition-all duration-200 z-50 max-h-64 overflow-y-auto custom-scrollbar">
                            
                            {{-- All Models Option --}}
                            <div onclick="selectModel('all')" 
                                class="px-5 py-3 text-xs font-bold cursor-pointer transition-colors flex items-center justify-between border-b border-gray-50 last:border-0 {{ ($currentModel == 'all') ? 'bg-orange-50 text-orange-600' : 'text-gray-600 hover:bg-gray-50' }}">
                                <span>All Models</span>
                                <span class="flex items-center justify-center w-5 h-5 rounded-full text-[9px] {{ ($currentModel == 'all') ? 'bg-orange-200 text-orange-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $vehicles->count() }}
                                </span>
                            </div>

                            {{-- Specific Models --}}
                            @foreach($vehicleModels as $model)
                                @php
                                    $count = $vehicles->where('model', $model)->count();
                                @endphp
                                <div onclick="selectModel('{{ $model }}')" 
                                    class="px-5 py-3 text-xs font-bold cursor-pointer transition-colors flex items-center justify-between border-b border-gray-50 last:border-0 {{ $currentModel == $model ? 'bg-orange-50 text-orange-600' : 'text-gray-600 hover:bg-gray-50' }}">
                                    <span>{{ $model }}</span>
                                    <span class="flex items-center justify-center w-5 h-5 rounded-full text-[9px] {{ $currentModel == $model ? 'bg-orange-200 text-orange-700' : 'bg-gray-100 text-gray-500' }}">
                                        {{ $count }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- 3. View Filter (Grouped Status & Ownership) --}}
                    <div class="relative w-full md:w-[220px]" id="customDropdown">
                        <input type="hidden" name="status" id="statusInput" value="{{ request('status', 'all') }}">
                        <input type="hidden" name="ownership" id="ownershipInput" value="{{ request('ownership', 'all') }}">
                        
                        @php
                            $currentStatus = request('status', 'all');
                            $currentOwnership = request('ownership', 'all');
                            
                            // Dynamic Label Based on What is Selected
                            $dropdownLabel = 'All Records';
                            if ($currentStatus !== 'all') {
                                $dropdownLabel = match($currentStatus) {
                                    'ready' => 'Status: Ready',
                                    'rented' => 'Status: Rented',
                                    'inactive' => 'Status: Inactive',
                                    default => 'Status: ' . ucfirst($currentStatus)
                                };
                            } elseif ($currentOwnership !== 'all') {
                                $dropdownLabel = 'Owner: ' . strtoupper($currentOwnership);
                            }
                        @endphp

                        <button type="button" onclick="toggleDropdown()" 
                            class="w-full flex items-center justify-between bg-white border border-gray-200 text-gray-700 text-xs font-bold py-3.5 px-5 rounded-2xl hover:border-orange-500 hover:text-orange-600 transition-all shadow-sm hover:shadow-md group">
                            
                            <div class="flex items-center gap-2 truncate">
                                <i class="fas fa-layer-group text-orange-500 shrink-0"></i>
                                <span id="dropdownLabel" class="capitalize truncate">{{ $dropdownLabel }}</span>
                            </div>

                            <i class="fas fa-chevron-down text-[10px] text-gray-400 group-hover:text-orange-500 transition-transform duration-300" id="dropdownArrow"></i>
                        </button>

                        <div id="dropdownMenu" 
                            class="absolute top-full right-0 mt-2 w-full bg-white border border-gray-100 rounded-2xl shadow-xl overflow-hidden hidden transform origin-top transition-all duration-200 z-50 max-h-[300px] overflow-y-auto custom-scrollbar">
                            
                            {{-- All Records View --}}
                            <div onclick="selectCombined('all', 'all')" class="px-5 py-3 text-xs font-bold cursor-pointer transition-colors flex items-center justify-between border-b border-gray-100 {{ ($currentStatus == 'all' && $currentOwnership == 'all') ? 'bg-orange-50 text-orange-600' : 'text-gray-600 hover:bg-gray-50' }}">
                                <span>All Records</span>
                                @if($currentStatus == 'all' && $currentOwnership == 'all') <i class="fas fa-check"></i> @endif
                            </div>
                            
                            {{-- Status Group --}}
                            <div class="px-5 py-2 text-[10px] font-black text-gray-400 uppercase tracking-wider bg-gray-50 border-b border-gray-100">
                                By Status
                            </div>

                            <div onclick="selectCombined('available', 'all')" class="px-5 py-3 text-xs font-bold cursor-pointer transition-colors flex items-center justify-between border-b border-gray-50 {{ $currentStatus == 'available' ? 'bg-orange-50 text-orange-600' : 'text-gray-600 hover:bg-gray-50' }}">
                                <span class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Ready</span>
                                @if($currentStatus == 'available') <i class="fas fa-check"></i> @endif
                            </div>

                            <div onclick="selectCombined('rented', 'all')" class="px-5 py-3 text-xs font-bold cursor-pointer transition-colors flex items-center justify-between border-b border-gray-50 {{ $currentStatus == 'rented' ? 'bg-orange-50 text-orange-600' : 'text-gray-600 hover:bg-gray-50' }}">
                                <span class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span> Rented</span>
                                @if($currentStatus == 'rented') <i class="fas fa-check"></i> @endif
                            </div>

                            <div onclick="selectCombined('maintenance', 'all')" class="px-5 py-3 text-xs font-bold cursor-pointer transition-colors flex items-center justify-between border-b border-gray-50 {{ $currentStatus == 'maintenance' ? 'bg-orange-50 text-orange-600' : 'text-gray-600 hover:bg-gray-50' }}">
                                <span class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Maintenance</span>
                                @if($currentStatus == 'maintenance') <i class="fas fa-check"></i> @endif
                            </div>

                            <div onclick="selectCombined('inactive', 'all')" class="px-5 py-3 text-xs font-bold cursor-pointer transition-colors flex items-center justify-between border-b border-gray-100 {{ $currentStatus == 'inactive' ? 'bg-orange-50 text-orange-600' : 'text-gray-600 hover:bg-gray-50' }}">
                                <span class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Inactive</span>
                                @if($currentStatus == 'inactive') <i class="fas fa-check"></i> @endif
                            </div>

                            {{-- Ownership Group --}}
                            <div class="px-5 py-2 text-[10px] font-black text-gray-400 uppercase tracking-wider bg-gray-50 border-b border-gray-100">
                                By Ownership
                            </div>

                            <div onclick="selectCombined('all', 'HASTA')" class="px-5 py-3 text-xs font-bold cursor-pointer transition-colors flex items-center justify-between border-b border-gray-50 {{ $currentOwnership == 'HASTA' ? 'bg-orange-50 text-orange-600' : 'text-gray-600 hover:bg-gray-50' }}">
                                <span class="flex items-center gap-2"><i class="fas fa-id-badge text-gray-400"></i> HASTA</span>
                                @if($currentOwnership == 'HASTA') <i class="fas fa-check"></i> @endif
                            </div>

                            <div onclick="selectCombined('all', 'BROKER')" class="px-5 py-3 text-xs font-bold cursor-pointer transition-colors flex items-center justify-between border-b border-gray-50 {{ $currentOwnership == 'BROKER' ? 'bg-orange-50 text-orange-600' : 'text-gray-600 hover:bg-gray-50' }}">
                                <span class="flex items-center gap-2"><i class="fas fa-handshake text-gray-400"></i> BROKER</span>
                                @if($currentOwnership == 'BROKER') <i class="fas fa-check"></i> @endif
                            </div>

                            <div onclick="selectCombined('all', 'AGENT')" class="px-5 py-3 text-xs font-bold cursor-pointer transition-colors flex items-center justify-between {{ $currentOwnership == 'AGENT' ? 'bg-orange-50 text-orange-600' : 'text-gray-600 hover:bg-gray-50' }}">
                                <span class="flex items-center gap-2"><i class="fas fa-user-tag text-gray-400"></i> AGENT</span>
                                @if($currentOwnership == 'AGENT') <i class="fas fa-check"></i> @endif
                            </div>
                        </div>
                    </div>

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
            <a href="{{ route('staff.fleet.show', $vehicle->VehicleID) }}" class="animate-fade-in fleet-item block bg-white rounded-xl p-2 pr-4 border border-gray-100 shadow-sm hover:shadow-md transition-all group">
                <div class="flex flex-col md:flex-row items-center gap-4 md:gap-8">
                    {{-- IMAGE --}}
                    <div class="w-full md:w-24 h-20 md:h-16 rounded-lg bg-gray-50 flex items-center justify-center overflow-hidden border border-gray-100 relative shrink-0">
                        @if($vehicle->image)
                            <img src="{{ asset('storage/'.$vehicle->image) }}" class="w-full h-full object-cover">
                        @else
                            <i class="fas fa-car text-gray-300 text-2xl"></i>
                        @endif
                        
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
                    
                    {{-- OWNERSHIP TYPE --}}
                    <div class="flex-1 w-full md:w-auto text-center md:text-left border-t md:border-t-0 md:border-l border-gray-100 pt-2 md:pt-0 md:pl-6">
                        <label class="text-[9px] font-bold text-gray-400 uppercase tracking-wider block mb-0.5">Ownership</label>
                        <div class="flex items-center justify-center md:justify-start gap-2">
                            <i class="fas fa-id-badge text-gray-300 text-xs"></i>
                            <span class="text-sm font-bold text-blue-600 uppercase">
                                {{ $vehicle->ownership_type ?? 'HASTA' }}
                            </span>
                        </div>
                    </div>

                    {{-- STATUS & TOGGLE --}}
                    <div class="flex items-center justify-between w-full md:w-auto md:justify-end gap-6 border-t md:border-t-0 border-gray-100 pt-3 md:pt-0 mt-2 md:mt-0">
                        @php
                            // Read directly from the new database column
                            $currentStatus = strtolower($vehicle->status ?? 'available'); 
                            
                            if($vehicle->isBookedToday) {
                                $currentStatus = 'rented';
                            }

                            $statusConfig = match($currentStatus) {
                                'rented' => ['text' => 'Rented', 'bg' => 'bg-orange-50', 'border' => 'border-orange-100', 'text_color' => 'text-orange-600', 'dot' => 'bg-orange-500', 'pulse' => 'animate-pulse'],
                                'maintenance' => ['text' => 'Maintenance', 'bg' => 'bg-blue-50', 'border' => 'border-blue-100', 'text_color' => 'text-blue-700', 'dot' => 'bg-blue-500', 'pulse' => ''],
                                'inactive' => ['text' => 'Inactive', 'bg' => 'bg-gray-100', 'border' => 'border-gray-200', 'text_color' => 'text-gray-500', 'dot' => 'bg-gray-400', 'pulse' => ''],
                                default => ['text' => 'Ready', 'bg' => 'bg-green-50', 'border' => 'border-green-100', 'text_color' => 'text-green-700', 'dot' => 'bg-green-500', 'pulse' => ''],
                            };
                        @endphp

                        <div class="flex items-center {{ $statusConfig['bg'] }} px-3 py-1.5 rounded-lg border {{ $statusConfig['border'] }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $statusConfig['dot'] }} mr-2 {{ $statusConfig['pulse'] }}"></span>
                            <span class="text-[10px] font-bold {{ $statusConfig['text_color'] }} uppercase">{{ $statusConfig['text'] }}</span>
                        </div>
                        
                        <form action="{{ route('staff.fleet.status', $vehicle->VehicleID) }}" method="POST" @click.stop>
                            @csrf
                            <button type="submit" class="w-9 h-9 rounded-lg flex items-center justify-center transition-all border shadow-sm {{ ($vehicle->availability || $vehicle->isBookedToday) ? 'bg-white text-gray-300 border-gray-200 hover:bg-red-50 hover:text-red-500' : 'bg-gray-800 text-white border-transparent hover:bg-gray-700' }}">
                                <i class="fas fa-power-off text-sm"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
        
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
    // --- DROPDOWN & SUBMIT LOGIC ---
    function toggleDropdown() {
        const menu = document.getElementById('dropdownMenu');
        const arrow = document.getElementById('dropdownArrow');
        closeOthers('dropdownMenu');
        
        menu.classList.toggle('hidden');
        if (!menu.classList.contains('hidden')) {
            menu.classList.add('animate-fade-in-down');
            if(arrow) arrow.style.transform = 'rotate(180deg)';
        } else {
            if(arrow) arrow.style.transform = 'rotate(0deg)';
        }
    }

    function toggleModelDropdown() {
        const menu = document.getElementById('modelMenu');
        const arrow = document.getElementById('modelDropdownArrow');
        closeOthers('modelMenu');

        menu.classList.toggle('hidden');
        if (!menu.classList.contains('hidden')) {
            menu.classList.add('animate-fade-in-down');
            if(arrow) arrow.style.transform = 'rotate(180deg)';
        } else {
            if(arrow) arrow.style.transform = 'rotate(0deg)';
        }
    }

    function closeOthers(currentId) {
        if(currentId !== 'dropdownMenu') document.getElementById('dropdownMenu').classList.add('hidden');
        if(currentId !== 'modelMenu') document.getElementById('modelMenu').classList.add('hidden');
    }

    // Handles the combined Status and Ownership group submission
    function selectCombined(statusValue, ownershipValue) {
        document.getElementById('statusInput').value = statusValue;
        document.getElementById('ownershipInput').value = ownershipValue;
        document.getElementById('filterForm').submit();
    }

    // Standard single-select for the model dropdown
    function selectModel(value) {
        document.getElementById('modelInput').value = value;
        document.getElementById('filterForm').submit();
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        const statusDropdown = document.getElementById('customDropdown');
        const modelDropdown = document.getElementById('modelDropdown');

        if (statusDropdown && !statusDropdown.contains(event.target)) {
            document.getElementById('dropdownMenu').classList.add('hidden');
            const arrow = document.getElementById('dropdownArrow');
            if(arrow) arrow.style.transform = 'rotate(0deg)';
        }

        if (modelDropdown && !modelDropdown.contains(event.target)) {
            document.getElementById('modelMenu').classList.add('hidden');
            const arrow = document.getElementById('modelDropdownArrow');
            if(arrow) arrow.style.transform = 'rotate(0deg)';
        }
    });
</script>

<style>
    .no-scrollbar::-webkit-scrollbar { display: none; } 
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #9ca3af; }
    @keyframes fade-in { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
    .animate-fade-in { animation: fade-in 0.2s ease-out forwards; }
    .animate-fade-in-down { animation: fade-in 0.15s ease-out forwards; }
</style>
@endsection