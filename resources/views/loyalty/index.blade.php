@extends('layouts.app')

@section('content')
<div class="relative min-h-screen bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1568605114967-8130f3a36994?q=80&w=1470&auto=format&fit=crop');">
    <div class="absolute inset-0 bg-white/40 backdrop-blur-sm"></div>

    <div class="relative z-10 container mx-auto px-4 py-10 max-w-5xl">
        
        <h2 class="bg-orange-200 inline-block px-6 py-2 rounded-t-xl text-xl font-bold text-gray-800 shadow">Loyalty Points</h2>
        <div class="bg-white/90 backdrop-blur rounded-b-xl rounded-tr-xl p-8 shadow-lg mb-12">
            <div class="grid grid-cols-4 gap-4 text-center">
                <div class="bg-red-400 text-white font-bold py-2 rounded-lg">Points Earned</div>
                <div class="bg-red-400 text-white font-bold py-2 rounded-lg">Points Redeemed</div>
                <div class="bg-red-400 text-white font-bold py-2 rounded-lg">Total Points</div>
                <div class="bg-red-400 text-white font-bold py-2 rounded-lg">Tier</div>
                
                <div class="bg-gray-200 py-4 rounded-lg font-bold text-gray-700">1400</div>
                <div class="bg-gray-200 py-4 rounded-lg font-bold text-gray-700">400</div>
                <div class="bg-gray-200 py-4 rounded-lg font-bold text-gray-700">1000</div>
                <div class="bg-gray-200 py-4 rounded-lg font-bold text-gray-700">Silver</div>
            </div>
        </div>

        <h2 class="bg-orange-200 inline-block px-6 py-2 rounded-t-xl text-xl font-bold text-gray-800 shadow">Rewards</h2>
        <div class="bg-white/90 backdrop-blur rounded-b-xl rounded-tr-xl p-6 shadow-lg relative">
            
            <div class="flex items-center space-x-4 overflow-x-auto pb-4">
                <button class="text-orange-500 text-3xl"><i class="fas fa-chevron-left"></i></button>
                
                <div class="flex-shrink-0 w-80 bg-red-900 rounded-xl overflow-hidden shadow-md">
                     <div class="h-32 bg-yellow-600 flex items-center justify-center text-white font-bold text-2xl">ZUS COFFEE</div>
                </div>

                <div class="flex-shrink-0 w-80 bg-purple-100 rounded-xl overflow-hidden shadow-md border border-purple-200 p-4">
                    <div class="flex justify-between items-center h-full">
                        <span class="text-purple-800 font-bold text-xl">tealive</span>
                        <span class="bg-purple-800 text-white px-2 py-1 font-bold">RM 2 OFF</span>
                    </div>
                </div>

                <button class="text-orange-500 text-3xl"><i class="fas fa-chevron-right"></i></button>
            </div>
        </div>

    </div>
</div>
@endsection