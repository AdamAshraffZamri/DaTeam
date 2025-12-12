@extends('layouts.app')

@section('content')
<div class="relative min-h-screen bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1568605114967-8130f3a36994?q=80&w=1470&auto=format&fit=crop');">
    <div class="absolute inset-0 bg-white/40 backdrop-blur-sm"></div>

    <div class="relative z-10 container mx-auto px-4 py-10 max-w-6xl">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            
            <div class="bg-orange-200/90 backdrop-blur rounded-3xl p-6 shadow-xl h-full">
                <h2 class="text-2xl font-bold text-center mb-6 text-gray-800">Deposit</h2>
                
                <div class="bg-white rounded-xl p-4 mb-4 shadow-sm">
                    <div class="flex justify-between font-bold text-gray-800 mb-2">
                        <span>#Book 0002</span>
                        <span>27/11/2025</span>
                    </div>
                    <div class="text-gray-600 text-sm mb-2">
                        <div>MVD 7380</div>
                        <div>Axia Blue(RM50)</div>
                        <div class="text-red-500 font-semibold">4 days left</div>
                    </div>
                    <div class="flex justify-end">
                        <button class="bg-green-500 text-white px-6 py-1 rounded-full font-bold shadow hover:bg-green-600">Claim</button>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-4 mb-4 shadow-sm opacity-80">
                    <div class="flex justify-between font-bold text-gray-800 mb-2">
                        <span>#Book 0001</span>
                        <span>20/11/2025</span>
                    </div>
                    <div class="text-gray-600 text-sm mb-2">
                        <div>BCN 6171</div>
                        <div>Myvi Black(RM50)</div>
                        <div>0 days left</div>
                    </div>
                    <div class="flex justify-end">
                        <button class="bg-green-300 text-white px-6 py-1 rounded-full font-bold cursor-default">Claimed</button>
                    </div>
                </div>
            </div>

            <div class="bg-orange-200/90 backdrop-blur rounded-3xl p-6 shadow-xl h-full flex flex-col">
                <h2 class="text-2xl font-bold text-center mb-6 text-gray-800">Fine</h2>
                
                <div class="bg-white rounded-xl p-4 mb-4 shadow-sm flex-grow">
                    <span class="font-bold text-gray-800">No fine</span>
                </div>

                <div class="mt-auto flex justify-end">
                    <button class="bg-blue-500 text-white px-8 py-2 rounded-full font-bold shadow hover:bg-blue-600">Payment</button>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection