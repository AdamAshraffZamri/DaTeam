@extends('layouts.app')

@section('content')
<div class="relative min-h-[90vh] flex flex-col justify-center bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1568605114967-8130f3a36994?q=80&w=1470&auto=format&fit=crop');">
    
    <div class="absolute inset-0 bg-black/40 backdrop-blur-[2px]"></div>

    <div class="relative z-10 container mx-auto px-4 mt-10">
        
        <div class="flex justify-center mb-8">
            <div class="bg-white/10 backdrop-blur-md border border-white/20 p-1.5 rounded-full inline-flex space-x-1">
                <a href="{{ route('book.create') }}" class="px-8 py-3 rounded-full bg-orange-600 text-white font-bold shadow-lg transform scale-105 transition">Book a Car</a>
                <a href="{{ route('book.index') }}" class="px-8 py-3 rounded-full text-white font-bold hover:bg-white/10 transition">My Bookings</a>
                <a href="#" class="px-8 py-3 rounded-full text-white font-bold hover:bg-white/10 transition">Loyalty & Rewards</a>
                <a href="#" class="px-8 py-3 rounded-full text-white font-bold hover:bg-white/10 transition">Finance</a>
            </div>
        </div>

        <div class="text-center mb-12">
            <h1 class="text-5xl font-extrabold text-white drop-shadow-lg mb-2">Drive your adventure.</h1>
            <p class="text-xl text-gray-200 font-medium drop-shadow-md">Premium car rental services for UTM Students & Staff.</p>
        </div>

        <div class="max-w-7xl mx-auto">
            <form action="{{ route('book.search') }}" method="GET">
                <div class="bg-white rounded-[2rem] shadow-2xl p-4 md:flex md:items-center md:space-x-4 border border-gray-100">
                    
                    <div class="flex-1 relative group px-4 py-2 hover:bg-gray-50 rounded-2xl transition border border-transparent hover:border-gray-200">
                        <div class="flex items-center space-x-3">
                            <div class="bg-green-100 p-2 rounded-full text-green-600"><i class="fas fa-map-marker-alt"></i></div>
                            <div class="w-full">
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide">Pickup Point</label>
                                <input type="text" name="pickup_location" value="Student Mall, UTM" class="w-full bg-transparent border-none p-0 text-gray-800 font-bold text-sm focus:ring-0 truncate">
                            </div>
                        </div>
                    </div>

                    <div class="hidden md:block w-px h-12 bg-gray-200"></div>

                    <div class="flex-1 relative group px-4 py-2 hover:bg-gray-50 rounded-2xl transition border border-transparent hover:border-gray-200">
                        <div class="flex items-center space-x-3">
                            <div class="bg-red-100 p-2 rounded-full text-red-600"><i class="fas fa-flag-checkered"></i></div>
                            <div class="w-full">
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide">Return Point</label>
                                <input type="text" name="return_location" value="Student Mall, UTM" class="w-full bg-transparent border-none p-0 text-gray-800 font-bold text-sm focus:ring-0 truncate">
                            </div>
                        </div>
                    </div>

                    <div class="hidden md:block w-px h-12 bg-gray-200"></div>

                    <div class="flex-1 relative group px-4 py-2 hover:bg-gray-50 rounded-2xl transition border border-transparent hover:border-gray-200">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Pickup Date</label>
                        <div class="flex space-x-2">
                            <input type="date" name="pickup_date" required class="w-full bg-gray-100 rounded-lg border-none py-1 px-2 text-gray-800 text-sm font-bold focus:ring-2 focus:ring-orange-500">
                            <input type="time" name="pickup_time" class="w-24 bg-gray-100 rounded-lg border-none py-1 px-2 text-gray-800 text-sm font-bold focus:ring-2 focus:ring-orange-500">
                        </div>
                    </div>

                    <div class="flex-1 relative group px-4 py-2 hover:bg-gray-50 rounded-2xl transition border border-transparent hover:border-gray-200">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Return Date</label>
                        <div class="flex space-x-2">
                            <input type="date" name="return_date" required class="w-full bg-gray-100 rounded-lg border-none py-1 px-2 text-gray-800 text-sm font-bold focus:ring-2 focus:ring-orange-500">
                            <input type="time" name="return_time" class="w-24 bg-gray-100 rounded-lg border-none py-1 px-2 text-gray-800 text-sm font-bold focus:ring-2 focus:ring-orange-500">
                        </div>
                    </div>

                    <div class="pl-2">
                        <button type="submit" class="w-full md:w-16 md:h-16 bg-gradient-to-br from-red-600 to-orange-500 rounded-2xl flex items-center justify-center text-white shadow-xl hover:shadow-orange-500/40 hover:scale-105 transition-all duration-300 group">
                            <i class="fas fa-search text-xl group-hover:rotate-90 transition-transform duration-300"></i>
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>
</div>
@endsection