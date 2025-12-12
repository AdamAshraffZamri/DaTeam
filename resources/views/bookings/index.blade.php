@extends('layouts.app')

@section('content')
<div class="relative min-h-screen bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1568605114967-8130f3a36994?q=80&w=1470&auto=format&fit=crop');">
    <div class="absolute inset-0 bg-white/40 backdrop-blur-sm"></div>

    <div class="relative z-10 container mx-auto px-4 py-10">
        
        <div class="bg-white/80 rounded-full p-1 inline-flex mb-8 shadow-sm overflow-x-auto max-w-full">
            @foreach(['Draft', 'Submitted', 'To Pay', 'Confirmed', 'Active Rental', 'Completed', 'Cancelled'] as $status)
                <button class="px-4 py-1 rounded-full text-sm font-medium {{ $loop->first ? 'bg-orange-500 text-white' : 'text-gray-600 hover:bg-gray-200' }}">
                    {{ $status }}
                </button>
            @endforeach
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            
            <div class="bg-gray-600/80 backdrop-blur text-white rounded-3xl p-6 shadow-xl relative">
                <div class="bg-white rounded-xl p-4 mb-4">
                    <img src="https://perodua.com.my/assets/images/cars/axia/colors/white.png" class="w-full h-32 object-contain">
                </div>
                
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between border-b border-gray-500 pb-1">
                        <span>Car Model:</span>
                        <span class="font-bold">Perodua Axia (2nd Gen)</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Pickup Date:</span>
                        <span>15-12-2025</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-500 pb-1">
                        <span>Pickup Location:</span>
                        <span>Student Mall, UTM</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Return Date:</span>
                        <span>15-12-2025</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Return Location:</span>
                        <span>MA1, KTDI, UTM</span>
                    </div>
                </div>

                <div class="flex space-x-3 mt-6">
                    <button class="flex-1 bg-gray-300 text-gray-800 py-2 rounded-full font-bold hover:bg-white transition">Edit</button>
                    <button class="flex-1 bg-green-500 text-white py-2 rounded-full font-bold hover:bg-green-600 transition">Submit</button>
                </div>
            </div>

            </div>
    </div>
</div>
@endsection