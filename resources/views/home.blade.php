@extends('layouts.app')

@section('content')
<div class="relative bg-white overflow-hidden">
    <div class="relative h-96 md:h-[600px]">
        
        <img src="https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?q=80&w=1470&auto=format&fit=crop" 
             class="absolute w-full h-full object-cover">
        
        <div class="absolute inset-0 bg-gradient-to-b from-gray-900/60 via-gray-900/20 to-transparent"></div>

        <div class="relative container mx-auto px-6 h-full flex flex-col justify-center items-center text-white">
            
            <div class="bg-orange-500 rounded-full p-1.5 px-3 inline-flex flex-wrap gap-2 md:gap-4 mb-12 shadow-lg justify-center">
                <a href="{{ route('book.create') }}" class="px-5 py-2 hover:bg-orange-600 rounded-full text-sm md:text-base font-bold transition">Book a Car</a>
                <a href="{{ route('book.index') }}" class="px-5 py-2 hover:bg-orange-600 rounded-full text-sm md:text-base font-bold transition">My Bookings</a>
                <a href="{{ route('loyalty.index') }}" class="px-5 py-2 hover:bg-orange-600 rounded-full text-sm md:text-base font-bold transition">Loyalty & Rewards</a>
                <a href="{{ route('finance.index') }}" class="px-5 py-2 hover:bg-orange-600 rounded-full text-sm md:text-base font-bold transition">Finance</a>
            </div>

            <h1 class="text-4xl md:text-6xl font-extrabold mb-4 text-center drop-shadow-xl tracking-wide">
                Rent Your Perfect Car Today
            </h1>
            <p class="text-lg md:text-xl mb-8 text-center drop-shadow-md max-w-2xl font-light">
                Choose from our wide range of vehicles for any occasion at affordable prices
            </p>
        </div>
    </div>

    <div class="container mx-auto px-6 py-16">
        <h2 class="text-3xl font-bold mb-10 text-gray-800 text-center md:text-left">Welcome to Hasta Travel & Tours</h2>

        <div class="flex flex-col md:flex-row bg-orange-50 rounded-2xl overflow-hidden shadow-md">
            <div class="p-10 md:w-1/2 flex flex-col justify-center">
                <h3 class="text-2xl font-bold mb-4 text-gray-900">Need a ride?</h3>
                <p class="text-gray-600 mb-8 leading-relaxed">
                    Get wheels without breaking your budget.<br>
                    Choose your car, your time, your way.
                </p>
                <div class="border-l-4 border-orange-500 pl-4">
                    <h4 class="text-xl font-bold text-gray-800">Fast. Easy. Affordable.</h4>
                    <p class="text-gray-500 text-sm mt-1">Pay only for the hours you drive.</p>
                </div>
            </div>
            
            <div class="md:w-1/2 min-h-[300px]">
                <img src="https://images.unsplash.com/photo-1449965408869-eaa3f722e40d?q=80&w=1470&auto=format&fit=crop" 
                     class="w-full h-full object-cover">
            </div>
        </div>
    </div>
</div>
@endsection