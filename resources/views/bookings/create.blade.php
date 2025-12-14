@extends('layouts.app')

@section('content')
<div class="relative min-h-screen bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1568605114967-8130f3a36994?q=80&w=1470&auto=format&fit=crop');">
    
    <div class="absolute inset-0 bg-white/30 backdrop-blur-sm"></div>

    <div class="relative z-10 container mx-auto px-4 py-20">
        
        <div class="bg-orange-100/90 backdrop-blur-md rounded-3xl p-8 shadow-2xl max-w-6xl mx-auto mt-10 border border-white/50">
            
            <form action="#" method="GET">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-6 items-start">
                    
                    <div class="lg:col-span-5 relative w-full">
                        <div class="bg-white rounded-full flex items-center px-5 py-4 shadow-sm border border-gray-200 focus-within:ring-2 ring-orange-400 transition-all">
                            <i class="fas fa-map-marker-alt text-orange-500 mr-3 text-lg"></i>
                            <input type="text" placeholder="Pickup Location" class="bg-transparent w-full outline-none text-gray-700 placeholder-gray-400 font-medium">
                        </div>
                        <div class="mt-3 ml-2">
                            <label class="inline-flex items-center cursor-pointer group">
                                <input type="checkbox" class="form-checkbox text-orange-600 rounded focus:ring-orange-500 w-4 h-4">
                                <span class="ml-2 text-sm text-gray-700 font-semibold group-hover:text-orange-700 transition">Return at UTM </span>
                            </label>
                        </div>
                    </div>

                    <div class="lg:col-span-3 text-center w-full">
                        <label class="block text-gray-800 font-bold mb-2 text-lg drop-shadow-sm text-left lg:text-center">Pickup:</label>
                        <div class="flex space-x-2">
                            <input type="date" class="bg-white rounded-xl px-3 py-3 w-3/5 text-center outline-none border border-gray-200 focus:border-orange-500 shadow-sm font-medium text-gray-600">
                            <input type="time" class="bg-white rounded-xl px-2 py-3 w-2/5 text-center outline-none border border-gray-200 focus:border-orange-500 shadow-sm font-medium text-gray-600">
                        </div>
                    </div>

                    <div class="lg:col-span-3 text-center w-full">
                        <label class="block text-gray-800 font-bold mb-2 text-lg drop-shadow-sm text-left lg:text-center">Return:</label>
                        <div class="flex space-x-2">
                            <input type="date" class="bg-white rounded-xl px-3 py-3 w-3/5 text-center outline-none border border-gray-200 focus:border-orange-500 shadow-sm font-medium text-gray-600">
                            <input type="time" class="bg-white rounded-xl px-2 py-3 w-2/5 text-center outline-none border border-gray-200 focus:border-orange-500 shadow-sm font-medium text-gray-600">
                        </div>
                    </div>

                    <div class="lg:col-span-1 flex justify-center lg:justify-end mt-1 lg:mt-9">
                        <button type="submit" class="w-full lg:w-auto bg-red-600 text-white rounded-xl p-4 shadow-lg hover:bg-red-700 hover:shadow-xl transition transform hover:scale-105 active:scale-95 flex items-center justify-center">
                            <i class="fas fa-search text-xl"></i>
                            <span class="ml-2 font-bold lg:hidden">Search</span> </button>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection