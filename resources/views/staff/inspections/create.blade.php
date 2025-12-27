@extends('layouts.staff')

@section('content')
<div class="max-w-4xl mx-auto">
    
    <div class="mb-6 flex items-center justify-between">
        <div>
            <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Inspection Mode</span>
            <h1 class="text-3xl font-black text-gray-900">{{ $type }} Inspection</h1>
        </div>
        <div class="text-right bg-white p-3 rounded-xl border border-gray-100 shadow-sm">
            <p class="font-bold text-lg text-gray-800">{{ $booking->vehicle->model }}</p>
            <p class="text-sm text-gray-500 font-mono">{{ $booking->vehicle->plateNo }}</p>
        </div>
    </div>

    <form action="{{ route('staff.inspections.store', $booking->bookingID) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        <input type="hidden" name="inspectionType" value="{{ $type }}">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 h-full">
                <h3 class="font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-tachometer-alt mr-2 text-blue-500"></i> Vehicle Vitals
                </h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Current Mileage (km)</label>
                        <input type="number" name="mileage" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 font-mono font-bold text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g. 45020" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Fuel Level</label>
                        <div class="grid grid-cols-5 gap-2">
                            @foreach(['Reserve', '1/4', '1/2', '3/4', 'Full'] as $level)
                            <label class="cursor-pointer">
                                <input type="radio" name="fuelLevel" value="{{ $level }}" class="peer sr-only" required>
                                <div class="text-xs font-bold py-2 text-center rounded-lg border border-gray-200 text-gray-500 peer-checked:bg-blue-500 peer-checked:text-white peer-checked:border-blue-600 transition">
                                    {{ $level }}
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 h-full">
                <h3 class="font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-camera mr-2 text-orange-500"></i> Evidence Photos
                </h3>
                
                <div class="border-2 border-dashed border-gray-200 rounded-xl h-48 flex flex-col items-center justify-center text-center bg-gray-50 hover:bg-white hover:border-orange-400 transition cursor-pointer relative group">
                    <input type="file" name="photos[]" multiple accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" required>
                    
                    <div class="group-hover:scale-110 transition-transform duration-200">
                        <i class="fas fa-cloud-upload-alt text-4xl text-gray-300 mb-2"></i>
                    </div>
                    <p class="text-sm font-bold text-gray-600">Tap to upload photos</p>
                    <p class="text-xs text-gray-400 mt-1">Required: Front, Back, Sides, Interior</p>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <h3 class="font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-clipboard-list mr-2 text-gray-500"></i> Condition Report
            </h3>
            
            <textarea name="notes" rows="3" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 mb-4 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Note any scratches, dents, cleanliness issues, or missing items..."></textarea>

            @if($type == 'Return')
            <div class="bg-red-50 p-4 rounded-xl border border-red-100 flex items-center justify-between">
                <div>
                    <label class="block text-sm font-bold text-red-800">New Damage / Penalty Cost (RM)</label>
                    <p class="text-xs text-red-500 mt-1">If 0, full deposit will be refunded.</p>
                </div>
                <div class="w-1/3">
                    <input type="number" name="damageCosts" value="0" class="w-full bg-white border border-red-200 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 text-red-600 font-bold text-right">
                </div>
            </div>
            @endif
        </div>

        <div class="bg-gray-900 p-6 rounded-2xl shadow-lg border border-gray-800 text-white">
            <h3 class="font-bold text-lg mb-6 flex items-center text-orange-500">
                <i class="fas fa-check-circle mr-3"></i> Staff Verification
            </h3>
            
            <div class="space-y-4">
                <label class="flex items-start p-4 bg-white/10 rounded-xl cursor-pointer hover:bg-white/20 transition border border-white/5">
                    <input type="checkbox" name="staff_agree" class="mt-1 w-5 h-5 rounded text-orange-500 focus:ring-orange-500 border-gray-500" required>
                    <div class="ml-4">
                        <span class="block font-bold text-sm">I certify this inspection is accurate</span>
                        <span class="block text-xs text-gray-400 mt-1">
                            Logged in as: <span class="text-white font-bold">{{ Auth::guard('staff')->user()->name ?? 'Staff Member' }}</span>
                        </span>
                    </div>
                </label>
            </div>
        </div>

        <button type="submit" class="w-full bg-orange-600 text-white font-black py-4 rounded-xl shadow-xl shadow-orange-500/20 hover:bg-orange-700 transition transform hover:scale-[1.01] flex items-center justify-center">
            <span>Submit {{ $type }} Inspection</span>
            <i class="fas fa-arrow-right ml-3"></i>
        </button>

    </form>
</div>
@endsection