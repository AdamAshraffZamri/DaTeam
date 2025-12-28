@extends('layouts.staff')

@section('content')
<div class="min-h-screen bg-gray-100 p-8">
    <div class="max-w-7xl mx-auto space-y-8">
        
        <div class="flex justify-between items-end">
            <div>
                <h1 class="text-4xl font-black text-gray-900 tracking-tight">Fleet Inventory</h1>
                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-[0.3em] mt-2">Hasta Travel & Tours Real-time Assets</p>
            </div>
            <a href="{{ route('staff.fleet.create') }}" class="bg-gray-900 text-white px-8 py-4 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-orange-600 transition-all shadow-xl">
                <i class="fas fa-plus mr-2"></i> Add Fleet
            </a>
        </div>

        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100">
                        <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Vehicle</th>
                        <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Plate No</th>
                        <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Category</th>
                        <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Owner</th>
                        <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($vehicles as $vehicle)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-8 py-6">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-gray-100 overflow-hidden border border-gray-100">
                                    <img src="{{ $vehicle->image ? asset('storage/' . $vehicle->image) : asset('images/placeholder.png') }}" class="w-full h-full object-cover">
                                </div>
                                <div>
                                    <p class="font-black text-gray-900 leading-none">{{ $vehicle->brand }}</p>
                                    <p class="text-[10px] font-bold text-gray-400 mt-1 uppercase">{{ $vehicle->model }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <span class="bg-gray-900 text-white px-3 py-1 rounded-lg font-mono text-xs tracking-widest uppercase">
                                {{ $vehicle->plateNo }}
                            </span>
                        </td>
                        <td class="px-8 py-6 text-[10px] font-black text-gray-500 uppercase">{{ $vehicle->vehicle_category }}</td>
                        <td class="px-8 py-6">
                            <p class="text-[10px] font-black text-gray-900">{{ $vehicle->owner_name ?? 'Unknown' }}</p>
                        </td>
                        <td class="px-8 py-6 text-right space-x-2">
                            <a href="{{ route('staff.fleet.show', $vehicle->VehicleID) }}" class="text-gray-400 hover:text-blue-500 transition-colors"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('staff.fleet.edit', $vehicle->VehicleID) }}" class="text-gray-400 hover:text-orange-500 transition-colors"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('staff.fleet.destroy', $vehicle->VehicleID) }}" method="POST" class="inline" onsubmit="return confirm('Remove this vehicle?')">
                                @csrf @method('DELETE')
                                <button class="text-gray-400 hover:text-red-500 transition-colors"><i class="fas fa-trash-alt"></i></button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection