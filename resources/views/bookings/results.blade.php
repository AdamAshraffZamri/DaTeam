@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-cover bg-center bg-fixed" style="background-image: url('https://images.unsplash.com/photo-1568605114967-8130f3a36994?q=80&w=1470&auto=format&fit=crop');">
    <div class="absolute inset-0 bg-white/40 backdrop-blur-sm fixed"></div>

    <div class="relative z-10 container mx-auto px-4 py-8">
        
        <div class="bg-orange-100/90 backdrop-blur-md rounded-3xl p-6 shadow-xl mb-8 border border-white/50">
            <form action="{{ route('book.search') }}" method="GET">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-center">
                    
                    <div class="lg:col-span-5 grid grid-cols-2 gap-4">
                        <div class="relative">
                            <div class="bg-white rounded-full flex items-center px-4 py-3 shadow-sm border border-gray-200 focus-within:ring-2 ring-orange-400 transition-all">
                                <i class="fas fa-map-marker-alt text-green-600 mr-2"></i>
                                <input type="text" 
                                       name="pickup_location" 
                                       value="{{ request('pickup_location', 'Student Mall, UTM') }}" 
                                       placeholder="Pickup..." 
                                       class="bg-transparent w-full outline-none text-gray-700 font-bold text-sm">
                            </div>
                            <p class="text-[10px] font-bold text-gray-600 ml-3 mt-1">Pickup Point</p>
                        </div>
                        <div class="relative">
                            <div class="bg-white rounded-full flex items-center px-4 py-3 shadow-sm border border-gray-200 focus-within:ring-2 ring-orange-400 transition-all">
                                <i class="fas fa-flag-checkered text-red-600 mr-2"></i>
                                <input type="text" 
                                       name="return_location" 
                                       value="{{ request('return_location', 'Student Mall, UTM') }}" 
                                       placeholder="Return..." 
                                       class="bg-transparent w-full outline-none text-gray-700 font-bold text-sm">
                            </div>
                            <p class="text-[10px] font-bold text-gray-600 ml-3 mt-1">Return Point</p>
                        </div>
                    </div>

                    <div class="lg:col-span-3 text-center">
                        <label class="block text-gray-800 font-bold text-sm mb-1">Pickup:</label>
                        <div class="flex space-x-2 justify-center">
                            <input type="date" name="pickup_date" value="{{ request('pickup_date', now()->format('Y-m-d')) }}" class="bg-gray-300 rounded-lg px-3 py-2 text-center font-bold text-gray-700 border-none w-full" required>
                            <input type="time" name="pickup_time" value="{{ request('pickup_time') }}" class="bg-gray-300 rounded-lg px-3 py-2 text-center font-bold text-gray-700 border-none w-24">
                        </div>
                    </div>

                    <div class="lg:col-span-3 text-center">
                        <label class="block text-gray-800 font-bold text-sm mb-1">Return:</label>
                        <div class="flex space-x-2 justify-center">
                            <input type="date" name="return_date" value="{{ request('return_date', now()->addDay()->format('Y-m-d')) }}" class="bg-gray-300 rounded-lg px-3 py-2 text-center font-bold text-gray-700 border-none w-full" required>
                            <input type="time" name="return_time" value="{{ request('return_time') }}" class="bg-gray-300 rounded-lg px-3 py-2 text-center font-bold text-gray-700 border-none w-24">
                        </div>
                    </div>

                    <div class="lg:col-span-1 flex justify-end">
                        <button type="submit" class="bg-red-600 text-white rounded-xl p-3 shadow-lg hover:bg-red-700 transition transform hover:scale-105 active:scale-95 w-full">
                            <i class="fas fa-search text-xl"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="bg-orange-100/90 backdrop-blur-md rounded-3xl p-8 shadow-xl min-h-[600px] border border-white/50">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                
                <div class="md:col-span-1 space-y-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800 mb-3 text-center">Vehicle Type:</h3>
                        <div class="space-y-2 flex flex-col items-center">
                            <button class="w-full bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-lg shadow-sm border border-black transition">Axia</button>
                            <button class="w-full bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-lg shadow-sm border border-black transition">Bezza</button>
                            <button class="w-full bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-lg shadow-sm border border-black transition">Myvi</button>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-3">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        
                        @foreach($vehicles as $vehicle)
                        <div class="bg-[#e89f58] rounded-3xl p-4 shadow-lg text-white relative">
                            <div class="absolute top-4 left-1/2 transform -translate-x-1/2 text-xs font-bold uppercase tracking-widest text-white/80">
                                {{ $vehicle->plate_no }}
                            </div>

                            <div class="bg-white rounded-2xl p-4 mt-6 mb-4 h-40 flex items-center justify-center shadow-inner">
                                <img src="{{ $vehicle->image }}" class="max-w-full max-h-full object-contain">
                            </div>

                            <h3 class="text-center font-bold text-lg mb-4">{{ $vehicle->model }}</h3>

                            <div class="grid grid-cols-2 gap-y-2 text-sm px-2">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-chair text-black"></i>
                                    <span>5 Seats</span>
                                </div>
                                <div class="text-right font-bold text-xl">
                                    MYR {{ number_format($vehicle->price_per_day, 2) }} <span class="text-sm font-normal">/ day</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-cogs text-black"></i>
                                    <span>Automatic</span>
                                </div>
                                <div class="text-right flex items-center justify-end space-x-1">
                                    <i class="fas fa-star text-black"></i>
                                    <span class="font-bold">5.0 / 5.0</span>
                                </div>
                            </div>

                            <div class="mt-6 text-center">
                                <a href="{{ route('book.show', [
                                    'id' => $vehicle->vehicle_id,
                                    'pickup_date' => request('pickup_date', now()->format('Y-m-d')), 
                                    'return_date' => request('return_date', now()->addDay()->format('Y-m-d')),
                                    'pickup_location' => request('pickup_location', 'Student Mall, UTM'),
                                    'return_location' => request('return_location', 'Student Mall, UTM')
                                ]) }}" class="bg-green-400 hover:bg-green-500 text-black font-bold py-2 px-12 rounded-full shadow-md transition transform hover:scale-105 inline-block">
                                    Select
                                </a>
                            </div>
                        </div>
                        @endforeach

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection