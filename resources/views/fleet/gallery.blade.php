@extends('layouts.app')

@section('content')
{{-- 1. FIXED BACKGROUND --}}
<div class="fixed inset-0 z-0">
    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('hastabg.png') }}');"></div>
    <div class="absolute inset-0 bg-gradient-to-r from-black/85 via-black/70 to-black/85"></div>
</div>

<div class="relative z-10 min-h-screen py-12 pt-28">
    <div class="container mx-auto px-4">
        
        {{-- Header --}}
        <div class="text-center mb-16">
            <h1 class="text-4xl md:text-6xl font-black text-white mb-4 tracking-tight uppercase">The Hasta Fleet</h1>
            <div class="h-1 w-20 bg-orange-500 mx-auto mb-4"></div>
            <p class="text-gray-300 max-w-2xl mx-auto">Real-time rental rates for UTM Students & Staff. Transparent pricing, no hidden fees.</p>
        </div>

        {{-- Vehicle Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-10">
            @forelse($vehicles as $vehicle)
                <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-3xl overflow-hidden flex flex-col transition-all duration-500 hover:border-orange-500/50 hover:shadow-[0_0_40px_rgba(249,115,22,0.15)] group">
                    
                    {{-- Top Section: Image & Status --}}
                    <div class="relative h-64 bg-black/40">
                        @if($vehicle->image)
                            <img src="{{ asset('storage/' . $vehicle->image) }}" alt="{{ $vehicle->model }}" 
                                 class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-zinc-900">
                                <i class="fas fa-car text-6xl text-white/10"></i>
                            </div>
                        @endif

                        {{-- Floating Status Badge --}}
                        <div class="absolute top-5 right-5">
                            @php
                                $statusColors = [
                                    'available' => 'bg-emerald-500',
                                    'booked' => 'bg-orange-500',
                                    'maintenance' => 'bg-rose-500'
                                ];
                                $status = strtolower($vehicle->status ?? 'available');
                            @endphp
                            <span class="{{ $statusColors[$status] ?? 'bg-blue-500' }} text-white px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest shadow-xl">
                                {{ $status }}
                            </span>
                        </div>
                    </div>

                    {{-- Bottom Section: Info & Rates --}}
                    <div class="p-8 flex-grow flex flex-col">
                        <div class="mb-6">
                            <h3 class="text-3xl font-black text-white uppercase tracking-tighter">{{ $vehicle->brand }} {{ $vehicle->model }}</h3>
                            <p class="text-orange-500 font-bold text-sm tracking-[0.2em]">{{ $vehicle->plateNo ?? $vehicle->plate_no }}</p>
                        </div>

                        {{-- RENTAL RATES TABLE (Real-time data from Seeder) --}}
                        <div class="bg-black/30 rounded-2xl p-4 mb-8 border border-white/5">
                            <p class="text-[10px] uppercase text-gray-500 font-bold mb-3 tracking-widest">Standard Rental Rates (MYR)</p>
                            <div class="grid grid-cols-5 gap-2 text-center">
                                @php
                                    // These keys match your DatabaseSeeder structure: ['1'=>30, '3'=>50, etc]
                                    $targetHours = ['1', '3', '5', '12', '24'];
                                    $rates = is_array($vehicle->hourly_rates) ? $vehicle->hourly_rates : json_decode($vehicle->hourly_rates, true);
                                @endphp

                                @foreach($targetHours as $hour)
                                    <div class="flex flex-col">
                                        <span class="text-[10px] text-gray-400 font-medium mb-1">{{ $hour }}H</span>
                                        <span class="text-white font-bold text-sm">
                                            {{ isset($rates[$hour]) ? number_format($rates[$hour], 0) : '-' }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Specs Grid --}}
                        <div class="flex items-center gap-6 mb-8 text-gray-400">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-cog text-xs text-orange-500"></i>
                                <span class="text-[11px] font-bold uppercase">{{ $vehicle->type ?? 'Auto' }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i class="fas fa-gas-pump text-xs text-orange-500"></i>
                                <span class="text-[11px] font-bold uppercase">{{ $vehicle->fuelType ?? 'RON95' }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i class="fas fa-users text-xs text-orange-500"></i>
                                <span class="text-[11px] font-bold uppercase">5 Seats</span>
                            </div>
                        </div>

                        <!-- {{-- Action --}}
                        <div class="mt-auto">
                            @if($status === 'available')
                                <a href="{{ route('book.create', ['vehicle_id' => $vehicle->id]) }}" 
                                   class="block w-full text-center bg-white text-black font-black py-4 rounded-xl transition-all hover:bg-orange-500 hover:text-white shadow-lg active:scale-95">
                                    BOOK THIS VEHICLE
                                </a>
                            @else
                                <button disabled class="w-full bg-white/5 text-gray-500 font-black py-4 rounded-xl border border-white/10 cursor-not-allowed">
                                    CURRENTLY BUSY
                                </button>
                            @endif
                        </div> -->
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-24">
                    <div class="mb-6"><i class="fas fa-car-side text-7xl text-white/5"></i></div>
                    <h3 class="text-2xl text-white font-bold">No vehicles found in the fleet.</h3>
                    <p class="text-gray-500">Please check back later or contact support.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection