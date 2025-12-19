@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 pb-20">
    
    <div class="bg-white shadow-md border-b border-gray-200 sticky top-0 z-30">
        <div class="container mx-auto px-4 py-4">
            <form action="{{ route('book.search') }}" method="GET">
                <div class="flex flex-col lg:flex-row items-center gap-4">
                    <div class="flex items-center space-x-2 w-full lg:w-1/3 bg-gray-100 rounded-xl px-4 py-2 border border-gray-200">
                        <i class="fas fa-map-marker-alt text-green-600"></i>
                        <input type="text" name="pickup_location" value="{{ request('pickup_location', 'Student Mall, UTM') }}" class="bg-transparent border-none w-full text-sm font-bold focus:ring-0 placeholder-gray-400" placeholder="Pickup">
                        <span class="text-gray-300">|</span>
                        <i class="fas fa-flag-checkered text-red-600"></i>
                        <input type="text" name="return_location" value="{{ request('return_location', 'Student Mall, UTM') }}" class="bg-transparent border-none w-full text-sm font-bold focus:ring-0 placeholder-gray-400" placeholder="Return">
                    </div>

                    <div class="flex items-center space-x-2 w-full lg:w-1/3">
                        <div class="flex-1 bg-gray-100 rounded-xl px-3 py-2 border border-gray-200 flex items-center">
                            <span class="text-xs font-bold text-gray-500 mr-2">Pick:</span>
                            <input type="date" name="pickup_date" value="{{ request('pickup_date', now()->format('Y-m-d')) }}" class="bg-transparent border-none w-full text-sm font-bold p-0 focus:ring-0">
                        </div>
                        <div class="flex-1 bg-gray-100 rounded-xl px-3 py-2 border border-gray-200 flex items-center">
                            <span class="text-xs font-bold text-gray-500 mr-2">Ret:</span>
                            <input type="date" name="return_date" value="{{ request('return_date', now()->addDay()->format('Y-m-d')) }}" class="bg-transparent border-none w-full text-sm font-bold p-0 focus:ring-0">
                        </div>
                    </div>

                    <button type="submit" class="w-full lg:w-auto px-8 py-3 bg-gray-900 hover:bg-orange-600 text-white rounded-xl font-bold transition-colors shadow-lg">
                        Update Search
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="container mx-auto px-4 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            
            <div class="lg:col-span-1 hidden lg:block">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 sticky top-32">
                    <h3 class="text-lg font-extrabold text-gray-900 mb-4">Vehicle Type</h3>
                    <div class="space-y-2">
                        <label class="flex items-center space-x-3 cursor-pointer group">
                            <div class="w-5 h-5 rounded border border-gray-300 flex items-center justify-center group-hover:border-orange-500">
                                <input type="checkbox" class="hidden">
                                <div class="w-2.5 h-2.5 rounded-sm bg-orange-500 opacity-0 group-hover:opacity-100 transition"></div>
                            </div>
                            <span class="font-medium text-gray-600 group-hover:text-gray-900">Compact</span>
                        </label>
                        <label class="flex items-center space-x-3 cursor-pointer group">
                            <div class="w-5 h-5 rounded border border-gray-300 flex items-center justify-center group-hover:border-orange-500">
                                <input type="checkbox" class="hidden">
                                <div class="w-2.5 h-2.5 rounded-sm bg-orange-500 opacity-0 group-hover:opacity-100 transition"></div>
                            </div>
                            <span class="font-medium text-gray-600 group-hover:text-gray-900">Sedan</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-3 grid grid-cols-1 md:grid-cols-2 gap-6">
                @forelse($vehicles as $vehicle)
                <div class="group bg-white rounded-3xl p-5 shadow-sm hover:shadow-2xl border border-gray-100 transition-all duration-300 hover:-translate-y-1 relative">
                    
                    <div class="absolute top-5 right-5 z-10">
                        <span class="bg-gray-100 text-gray-500 text-xs font-bold px-3 py-1 rounded-full border border-gray-200">
                            {{ $vehicle->plateNo }}
                        </span>
                    </div>

                    <div class="h-48 flex items-center justify-center mb-4 relative overflow-hidden rounded-2xl bg-gray-50">
                        <i class="fas fa-car-side text-6xl text-gray-300"></i>
                    </div>

                    <div class="space-y-3">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-xl font-black text-gray-900">{{ $vehicle->model }}</h3>
                                <div class="flex items-center space-x-1 text-yellow-400 text-sm mt-1">
                                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                                    <span class="text-gray-400 font-medium ml-1">(5.0)</span>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-sm text-gray-500">
                            <div class="flex items-center"><i class="fas fa-chair w-6 text-center text-orange-500"></i> 5 Seats</div>
                            <div class="flex items-center"><i class="fas fa-cogs w-6 text-center text-orange-500"></i> Auto</div>
                            <div class="flex items-center"><i class="fas fa-gas-pump w-6 text-center text-orange-500"></i> {{ $vehicle->fuelType ?? 'Petrol' }}</div>
                            <div class="flex items-center"><i class="fas fa-snowflake w-6 text-center text-orange-500"></i> A/C</div>
                        </div>

                        <div class="pt-4 mt-4 border-t border-gray-100 flex items-center justify-between">
                            <div>
                                <span class="block text-xs text-gray-400 font-bold uppercase">Daily Rate</span>
                                <span class="text-2xl font-black text-orange-600">RM {{ number_format($vehicle->priceHour * 24, 0) }}</span>
                            </div>
                            
                            <a href="{{ route('book.payment', [
                                'id' => $vehicle->VehicleID, 
                                'pickup_date' => request('pickup_date', now()->format('Y-m-d')), 
                                'return_date' => request('return_date', now()->addDay()->format('Y-m-d')),
                                'pickup_location' => request('pickup_location', 'Student Mall, UTM'),
                                'return_location' => request('return_location', 'Student Mall, UTM')
                            ]) }}" class="bg-gray-900 hover:bg-orange-600 text-white px-8 py-3 rounded-xl font-bold shadow-lg transition-all transform hover:scale-105">
                                Select
                            </a>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-span-full py-12 text-center">
                    <div class="bg-orange-50 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-car-crash text-3xl text-orange-400"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">No cars available</h3>
                    <p class="text-gray-500">Try changing your dates or location.</p>
                </div>
                @endforelse
            </div>

        </div>
    </div>
</div>
@endsection