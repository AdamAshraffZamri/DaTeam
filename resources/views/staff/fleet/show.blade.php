@extends('layouts.staff')

@section('content')
<div class="min-h-screen bg-gray-100 p-8 flex justify-center items-start">
    <div class="w-full max-w-7xl bg-white/80 backdrop-blur-xl rounded-[3rem] shadow-sm border border-white overflow-hidden flex flex-col md:flex-row">
        
        <div class="w-full md:w-1/3 bg-gray-50/50 p-12 border-r border-gray-100 space-y-10">
            <div>
                <div class="flex items-center justify-between mb-10">
                    <h1 class="text-3xl font-black text-gray-900 leading-none">Fleet<br><span class="text-orange-500">Asset.</span></h1>
                    <a href="{{ route('staff.fleet.index') }}" class="text-gray-400 hover:text-gray-900 transition-colors">
                        <i class="fas fa-arrow-left text-xl"></i>
                    </a>
                </div>
                
                <div class="relative rounded-[2.5rem] overflow-hidden bg-white shadow-2xl border border-white aspect-square shadow-inner">
                    <img src="{{ $vehicle->image ? asset('storage/' . $vehicle->image) : asset('images/placeholder.png') }}" 
                         class="w-full h-full object-cover">
                    
                    <div class="absolute top-6 right-6">
                        <span class="px-4 py-2 rounded-2xl text-[10px] font-black uppercase tracking-widest backdrop-blur-md shadow-sm border {{ $vehicle->availability ? 'bg-green-500/10 text-green-600 border-green-200' : 'bg-red-500/10 text-red-600 border-red-200' }}">
                            {{ $vehicle->availability ? 'Available' : 'Maintenance' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="space-y-4 text-center">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block">Vehicle Category</label>
                <div class="inline-flex bg-white p-4 rounded-3xl shadow-sm border border-gray-100">
                    <i class="fas fa-{{ $vehicle->vehicle_category === 'car' ? 'car' : 'motorcycle' }} text-3xl text-orange-500"></i>
                </div>
                <p class="text-xs font-black text-gray-900 uppercase tracking-widest">{{ $vehicle->vehicle_category }}</p>
            </div>

            <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 text-center">Classification</label>
                <p class="text-center font-black text-gray-900 uppercase tracking-tighter text-xl">{{ $vehicle->type }}</p>
            </div>
        </div>

        <div class="flex-1 p-12 md:p-16">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-12">
                
                <div class="space-y-8">
                    <h3 class="text-[11px] font-black text-orange-500 uppercase tracking-[0.4em] border-b border-orange-100 pb-2">Technical Profile</h3>
                    
                    <div class="flex justify-between items-end border-b border-gray-50 py-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Plate Number</label>
                        <span class="font-black text-gray-900 tracking-widest uppercase">{{ $vehicle->plateNo }}</span>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col border-b border-gray-50 py-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Brand</label>
                            <span class="font-bold text-gray-900">{{ $vehicle->brand }}</span>
                        </div>
                        <div class="flex flex-col border-b border-gray-50 py-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Model</label>
                            <span class="font-bold text-gray-900">{{ $vehicle->model }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col border-b border-gray-50 py-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Year</label>
                            <span class="font-bold text-gray-900">{{ $vehicle->year }}</span>
                        </div>
                        <div class="flex flex-col border-b border-gray-50 py-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Color</label>
                            <span class="font-bold text-gray-900">{{ $vehicle->color }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col border-b border-gray-50 py-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Mileage</label>
                            <span class="font-bold text-gray-900">{{ number_format($vehicle->mileage) }} KM</span>
                        </div>
                        <div class="flex flex-col border-b border-gray-50 py-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Fuel Type</label>
                            <span class="font-bold text-gray-900">{{ $vehicle->fuelType }}</span>
                        </div>
                    </div>
                </div>

                <div class="space-y-8">
                    <h3 class="text-[11px] font-black text-blue-500 uppercase tracking-[0.4em] border-b border-blue-50 pb-2">Legal & Ownership</h3>
                    
                    <div class="flex flex-col border-b border-gray-50 py-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Owner Full Name</label>
                        <span class="font-bold text-gray-900">{{ $vehicle->owner_name }}</span>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col border-b border-gray-50 py-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Contact No.</label>
                            <span class="font-bold text-gray-900">{{ $vehicle->owner_phone }}</span>
                        </div>
                        <div class="flex flex-col border-b border-gray-50 py-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Owner NRIC/ID</label>
                            <span class="font-bold text-gray-900">{{ $vehicle->owner_nric }}</span>
                        </div>
                    </div>

                    <div class="pt-4 flex justify-between items-center bg-orange-50 p-6 rounded-3xl border border-orange-100">
                        <label class="text-[10px] font-black text-orange-400 uppercase tracking-widest">Security Deposit</label>
                        <span class="font-black text-orange-600 text-xl tracking-tighter">RM {{ number_format($vehicle->baseDepo, 2) }}</span>
                    </div>
                </div>
            </div>

            <div class="mt-16 bg-gray-50/80 p-10 rounded-[2.5rem] border border-gray-100 shadow-inner">
                <h4 class="text-[11px] font-black text-gray-900 uppercase tracking-[0.4em] mb-10 text-center">Hourly Rate Architecture (RM)</h4>
                <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-7 gap-4 text-center">
                    @php
                        $rates = is_array($vehicle->hourly_rates) ? $vehicle->hourly_rates : json_decode($vehicle->hourly_rates, true);
                    @endphp
                    @foreach([1, 3, 5, 7, 9, 12, 24] as $h)
                    <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm">
                        <span class="text-[8px] font-black text-gray-400 uppercase mb-1 block tracking-tighter">{{ $h }} Hour</span>
                        <span class="font-black text-gray-900 text-lg tracking-tighter">RM{{ $rates[$h] ?? '0' }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-12 flex gap-4">
                <a href="{{ route('staff.fleet.edit', $vehicle->VehicleID) }}" class="flex-1 bg-gray-900 text-white py-6 rounded-3xl font-black text-[11px] uppercase tracking-[0.3em] shadow-xl hover:bg-orange-600 transition-all text-center">
                    Modify Asset Details
                </a>
                
                <form action="{{ route('staff.fleet.destroy', $vehicle->VehicleID) }}" method="POST" onsubmit="return confirm('Archive this vehicle? This will remove it from active inventory.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-8 bg-red-50 text-red-500 rounded-3xl border border-red-100 hover:bg-red-500 hover:text-white transition-all h-full">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection