@extends('layouts.staff')

@section('content')
<div class="min-h-screen bg-gray-100 p-6">
    <div class="max-w-7xl mx-auto">

        {{-- HEADER & ACTIONS --}}
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            <div class="w-full md:w-auto">
                <h1 class="text-3xl font-black text-gray-900">Fleet Inventory</h1>
                <p class="text-gray-500 mt-1 text-sm">Monitor vehicle status and availability.</p>
            </div>

            <div class="flex items-center gap-6 w-full md:w-auto justify-start md:justify-end overflow-x-auto no-scrollbar p-1">
                {{-- Filters --}}
                <div class="flex gap-2 shrink-0">
                    <button onclick="filterFleet('all')" data-filter="all" class="filter-btn active-filter px-5 py-2.5 rounded-full text-xs font-bold transition-all border border-transparent bg-gray-900 text-white shadow-md flex items-center gap-2">
                        <span>All</span><span class="bg-white/20 px-1.5 py-0.5 rounded-full text-[10px]">{{ $total }}</span>
                    </button>
                    <button onclick="filterFleet('active')" data-filter="active" class="filter-btn px-5 py-2.5 rounded-full text-xs font-bold transition-all border border-gray-200 bg-white text-gray-500 hover:bg-gray-50 shadow-sm flex items-center gap-2">
                        <span>Active</span><span class="bg-gray-100 text-gray-400 px-1.5 py-0.5 rounded-full text-[10px]">{{ $activeCount }}</span>
                    </button>
                    <button onclick="filterFleet('inactive')" data-filter="inactive" class="filter-btn px-5 py-2.5 rounded-full text-xs font-bold transition-all border border-gray-200 bg-white text-gray-500 hover:bg-gray-50 shadow-sm flex items-center gap-2">
                        <span>Inactive</span><span class="bg-gray-100 text-gray-400 px-1.5 py-0.5 rounded-full text-[10px]">{{ $inactiveCount }}</span>
                    </button>
                </div>

                <div class="h-8 w-px bg-gray-300 hidden md:block shrink-0"></div>

                {{-- Add Button --}}
                <a href="{{ route('staff.fleet.create') }}" class="bg-orange-600 hover:bg-orange-500 text-white px-8 py-3 rounded-full font-bold text-sm shadow-lg shadow-orange-900/20 transition-all transform hover:scale-105 flex items-center gap-3 shrink-0 whitespace-nowrap">
                    <div class="bg-white/20 p-1.5 rounded-full flex items-center justify-center"><i class="fas fa-plus text-xs"></i></div>
                    <span>Add Vehicle</span>
                </a>
            </div>
        </div>

        {{-- VEHICLE LIST --}}
        <div class="space-y-3">
            @foreach($vehicles->sortByDesc('availability') as $vehicle)
            {{-- LINK TO SHOW PAGE --}}
            <a href="{{ route('staff.fleet.show', $vehicle->VehicleID) }}" class="fleet-item block bg-white rounded-xl p-2 pr-4 border border-gray-100 shadow-sm hover:shadow-md transition-all group" 
                 data-status="{{ $vehicle->availability ? 'active' : 'inactive' }}">
                
                <div class="flex flex-col md:flex-row items-center gap-4 md:gap-8">
                    {{-- IMAGE --}}
                    <div class="w-full md:w-24 h-20 md:h-16 rounded-lg bg-gray-50 flex items-center justify-center overflow-hidden border border-gray-100 relative shrink-0">
                        @if($vehicle->image)
                            <img src="{{ asset('storage/'.$vehicle->image) }}" class="w-full h-full object-cover">
                        @else
                            <i class="fas fa-car text-gray-300 text-2xl"></i>
                        @endif
                        @if(!$vehicle->availability)
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
                    <div class="flex-1 w-full md:w-auto text-center md:text-left border-t md:border-t-0 md:border-l border-gray-100 pt-2 md:pt-0 md:pl-6">
                        <label class="text-[9px] font-bold text-gray-400 uppercase tracking-wider block mb-0.5">Class</label>
                        <div class="flex items-center justify-center md:justify-start gap-2">
                            <i class="fas {{ strtolower($vehicle->category ?? 'car') == 'bike' ? 'fa-motorcycle' : 'fa-car' }} text-gray-300 text-xs"></i>
                            <span class="text-sm font-bold text-gray-700">{{ $vehicle->type }}</span>
                        </div>
                    </div>

                    {{-- STATUS & TOGGLE (Click.stop prevents navigation when toggling) --}}
                    <div class="flex items-center justify-between w-full md:w-auto md:justify-end gap-6 border-t md:border-t-0 border-gray-100 pt-3 md:pt-0 mt-2 md:mt-0">
                        <div class="text-right">
                            @if($vehicle->availability == 0)
                                <div class="flex items-center bg-gray-100 px-3 py-1.5 rounded-lg border border-gray-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400 mr-2"></span><span class="text-[10px] font-bold text-gray-500 uppercase">Offline</span>
                                </div>
                            @elseif($vehicle->isBookedToday)
                                <div class="flex items-center bg-orange-50 px-3 py-1.5 rounded-lg border border-orange-100">
                                    <span class="w-1.5 h-1.5 rounded-full bg-orange-500 mr-2 animate-pulse"></span><span class="text-[10px] font-bold text-orange-600 uppercase">Rented</span>
                                </div>
                            @else
                                <div class="flex items-center bg-green-50 px-3 py-1.5 rounded-lg border border-green-100">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500 mr-2"></span><span class="text-[10px] font-bold text-green-700 uppercase">Ready</span>
                                </div>
                            @endif
                        </div>
                        <form action="{{ route('staff.fleet.status', $vehicle->VehicleID) }}" method="POST" @click.stop>
                            @csrf
                            <button type="submit" class="w-9 h-9 rounded-lg flex items-center justify-center transition-all border shadow-sm {{ $vehicle->availability ? 'bg-white text-gray-300 border-gray-200 hover:bg-red-50 hover:text-red-500' : 'bg-gray-800 text-white border-transparent hover:bg-gray-700' }}">
                                <i class="fas fa-power-off text-sm"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
        
        {{-- EMPTY STATE --}}
        <div id="empty-fleet" class="hidden flex-col items-center justify-center py-20 text-center">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4"><i class="fas fa-car-crash text-gray-300 text-2xl"></i></div>
            <p class="text-gray-500 font-medium">No vehicles found matching this filter.</p>
        </div>
    </div>
</div>

<script>
    function filterFleet(status) {
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.classList.remove('bg-gray-900', 'text-white', 'border-transparent');
            btn.classList.add('bg-white', 'text-gray-500', 'border-gray-200', 'hover:bg-gray-50');
            if (btn.dataset.filter === status) {
                btn.classList.remove('bg-white', 'text-gray-500', 'border-gray-200', 'hover:bg-gray-50');
                btn.classList.add('bg-gray-900', 'text-white', 'border-transparent');
            }
        });
        let count = 0;
        document.querySelectorAll('.fleet-item').forEach(row => {
            if (status === 'all' || row.dataset.status === status) {
                row.style.display = 'block'; count++;
            } else {
                row.style.display = 'none';
            }
        });
        const empty = document.getElementById('empty-fleet');
        (count === 0) ? empty.classList.remove('hidden', 'flex') : empty.classList.add('hidden');
        if(count === 0) empty.classList.add('flex');
    }
</script>
<style>.no-scrollbar::-webkit-scrollbar { display: none; } .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }</style>
@endsection