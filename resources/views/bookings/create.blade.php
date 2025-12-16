@extends('layouts.app')

@section('content')
<div class="relative min-h-screen bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1568605114967-8130f3a36994?q=80&w=1470&auto=format&fit=crop');">
    
    <div class="absolute inset-0 bg-white/30 backdrop-blur-sm"></div>

    <div class="relative z-10 container mx-auto px-4 py-20">
        
        <div class="bg-orange-100/90 backdrop-blur-md rounded-3xl p-8 shadow-2xl max-w-6xl mx-auto mt-10 border border-white/50">
            
            <form action="{{ route('book.search') }}" method="GET">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-6 items-start">
                    
                    <div class="lg:col-span-5 grid grid-cols-1 md:grid-cols-2 gap-4 w-full">
                        
                        <div class="relative">
                            <div class="bg-white rounded-full flex items-center px-5 py-4 shadow-sm border border-gray-200 focus-within:ring-2 ring-orange-400 transition-all">
                                <i class="fas fa-map-marker-alt text-green-600 mr-3 text-lg"></i>
                                <input type="text" 
                                       name="pickup_location" 
                                       value="Student Mall, UTM" 
                                       placeholder="Pickup Location" 
                                       class="bg-transparent w-full outline-none text-gray-700 placeholder-gray-400 font-medium">
                            </div>
                            <p class="text-xs font-bold text-gray-600 ml-4 mt-1">Pickup Point</p>
                        </div>

                        <div class="relative">
                            <div class="bg-white rounded-full flex items-center px-5 py-4 shadow-sm border border-gray-200 focus-within:ring-2 ring-orange-400 transition-all">
                                <i class="fas fa-flag-checkered text-red-600 mr-3 text-lg"></i>
                                <input type="text" 
                                       name="return_location" 
                                       value="Student Mall, UTM" 
                                       placeholder="Return Location" 
                                       class="bg-transparent w-full outline-none text-gray-700 placeholder-gray-400 font-medium">
                            </div>
                            <p class="text-xs font-bold text-gray-600 ml-4 mt-1">Return Point</p>
                        </div>

                    </div>

                    <div class="md:col-span-3 text-center">
                        <label class="block text-gray-900 font-bold mb-1 text-lg drop-shadow-sm">Pickup:</label>
                        <div class="flex space-x-2 justify-center">
                            <input type="date" name="pickup_date" required class="bg-gray-200/80 rounded-lg px-3 py-2 w-full text-center outline-none border border-gray-300 focus:border-orange-500">
                            <input type="time" name="pickup_time" class="bg-gray-200/80 rounded-lg px-3 py-2 w-full text-center outline-none border border-gray-300 focus:border-orange-500">
                        </div>
                    </div>

                    <div class="md:col-span-3 text-center">
                        <label class="block text-gray-900 font-bold mb-1 text-lg drop-shadow-sm">Return:</label>
                        <div class="flex space-x-2 justify-center">
                            <input type="date" name="return_date" required class="bg-gray-200/80 rounded-lg px-3 py-2 w-full text-center outline-none border border-gray-300 focus:border-orange-500">
                            <input type="time" name="return_time" class="bg-gray-200/80 rounded-lg px-3 py-2 w-full text-center outline-none border border-gray-300 focus:border-orange-500">
                        </div>
                    </div>

                    <div class="lg:col-span-1 flex justify-center lg:justify-end mt-1 lg:mt-9">
                        <button type="submit" class="w-full lg:w-auto bg-red-600 text-white rounded-xl p-4 shadow-lg hover:bg-red-700 hover:shadow-xl transition transform hover:scale-105 active:scale-95 flex items-center justify-center">
                            <i class="fas fa-search text-xl"></i>
                            <span class="ml-2 font-bold lg:hidden">Search</span> 
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection