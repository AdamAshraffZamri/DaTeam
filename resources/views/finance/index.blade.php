@extends('layouts.app')

@section('content')
{{-- 1. FIXED BACKGROUND --}}
<div class="fixed inset-0 z-0">
    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('hastabg.png') }}');"></div>
    <div class="absolute inset-0 bg-gradient-to-b from-black/80 via-black/50 to-black/90"></div>
</div>

{{-- 2. CONTENT WRAPPER --}}
<div class="relative z-10 min-h-[calc(100vh-64px)] py-12">
    <div class="container mx-auto px-4 max-w-6xl">
        
        {{-- HEADER --}}
        <div class="text-center mb-10">
            <h1 class="text-4xl font-black text-white drop-shadow-lg">Payments Center</h1>
            <p class="text-gray-400 mt-2">Manage your claims, refunds, and outstanding payments.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            
            {{-- COLUMN 1: CLAIMS (Refunds) --}}
            <div class="bg-white/5 backdrop-blur-md rounded-[2.5rem] p-8 border border-white/10 shadow-2xl h-full flex flex-col">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-2xl font-bold text-white flex items-center">
                        <i class="fas fa-hand-holding-usd text-green-400 mr-3"></i> Claimable
                    </h2>
                    <span class="bg-green-500/20 text-green-400 text-xs font-bold px-3 py-1 rounded-full border border-green-500/30">
                        {{ isset($claims) ? $claims->count() : 0 }} Items
                    </span>
                </div>
                
                <div class="space-y-4 flex-grow max-h-[500px] overflow-y-auto pr-2 custom-scrollbar">
                    @forelse($claims ?? [] as $claim)
                    <div class="bg-white/5 rounded-2xl p-5 border border-white/5 hover:bg-white/10 transition group relative overflow-hidden">
                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-green-500"></div>
                        
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Booking ID</span>
                                <p class="text-white font-bold">#{{ $claim->bookingID }}</p>
                            </div>
                            <span class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($claim->bookingDate)->format('d/m/Y') }}</span>
                        </div>
                        
                        <div class="text-gray-300 text-sm mb-4 space-y-1">
                            <div class="flex items-center"><i class="fas fa-car text-gray-500 mr-2"></i> {{ $claim->vehicle->model ?? 'Unknown' }}</div>
                            <div class="flex items-center"><i class="fas fa-money-bill-wave text-gray-500 mr-2"></i> Refund Amount: <span class="text-white font-bold ml-1">MYR {{ number_format($claim->totalCost, 2) }}</span></div>
                            <div class="text-red-400 text-xs mt-1 font-bold italic">Status: {{ $claim->bookingStatus }}</div>
                        </div>

                        <div class="flex justify-end">
                            <button onclick="alert('Claim request sent to admin!')" class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-xl font-bold text-sm shadow-lg shadow-green-500/20 transition transform hover:scale-105">
                                Claim Refund
                            </button>
                        </div>
                    </div>
                    @empty
                    <div class="h-full flex flex-col items-center justify-center text-center opacity-50 py-10">
                        <i class="fas fa-folder-open text-4xl text-gray-600 mb-2"></i>
                        <p class="text-gray-400">No claimable items found.</p>
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- COLUMN 2: OUTSTANDING (Fines + Remaining Balances) --}}
            <div class="bg-white/5 backdrop-blur-md rounded-[2.5rem] p-8 border border-white/10 shadow-2xl h-full flex flex-col">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-2xl font-bold text-white flex items-center">
                        <i class="fas fa-file-invoice-dollar text-orange-500 mr-3"></i> Outstanding
                    </h2>
                    <span class="bg-orange-500/20 text-orange-400 text-xs font-bold px-3 py-1 rounded-full border border-orange-500/30">
                        {{ (isset($fines) ? $fines->count() : 0) + (isset($balanceBookings) ? $balanceBookings->count() : 0) }} Due
                    </span>
                </div>
                
                <div class="space-y-4 flex-grow max-h-[500px] overflow-y-auto pr-2 custom-scrollbar">
                    
                    {{-- 1. LOOP: REMAINING BALANCES (Deposit Paid) --}}
                    @foreach($balanceBookings ?? [] as $booking)
                        @php
                            $paid = $booking->payments->sum('amount');
                            $balance = $booking->totalCost - $paid;
                        @endphp
                        
                        @if($balance > 0)
                        <div class="bg-white/5 rounded-2xl p-5 border border-orange-500/30 hover:bg-white/10 transition relative overflow-hidden">
                            <div class="absolute left-0 top-0 bottom-0 w-1 bg-orange-500"></div>

                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <span class="text-xs font-bold text-orange-400 uppercase tracking-wider">Remaining Balance</span>
                                    <p class="text-white font-bold text-lg">MYR {{ number_format($balance, 2) }}</p>
                                </div>
                                <span class="bg-orange-500/20 text-orange-400 text-[10px] px-2 py-1 rounded uppercase font-bold">Rent Due</span>
                            </div>

                            <div class="text-gray-400 text-sm mb-4">
                                <p class="font-medium text-white">{{ $booking->vehicle->model }} ({{ $booking->vehicle->plateNo }})</p>
                                <p class="text-xs mt-1">Ref Booking: #{{ $booking->bookingID }}</p>
                                <p class="text-xs text-red-400 mt-1"><i class="far fa-clock"></i> Due before {{ \Carbon\Carbon::parse($booking->pickupDate)->format('d M Y') }}</p>
                            </div>
                            <div class="mt-auto flex justify-end">
                                {{-- Link to a payment page or trigger modal --}}
                                <a href="{{ route('finance.pay', $booking->bookingID) }}" class="bg-orange-600 hover:bg-orange-700 text-white px-8 py-2 rounded-xl font-bold text-sm shadow-lg shadow-orange-500/30 transition transform hover:scale-105">
                                    Pay Balance
                                </a>
                            </div>
                        </div>
                        @endif
                    @endforeach

                    {{-- 2. LOOP: PENALTY FINES --}}
                    @foreach($fines ?? [] as $fine)
                    <div class="bg-white/5 rounded-2xl p-5 border border-red-500/30 hover:bg-white/10 transition relative overflow-hidden">
                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-red-500"></div>

                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <span class="text-xs font-bold text-red-400 uppercase tracking-wider">Penalty Fee</span>
                                <p class="text-white font-bold text-lg">MYR {{ number_format($fine->amount, 2) }}</p>
                            </div>
                            <span class="bg-red-500/20 text-red-400 text-[10px] px-2 py-1 rounded uppercase font-bold">{{ $fine->status }}</span>
                        </div>

                        <div class="text-gray-400 text-sm mb-4">
                            <p class="font-medium text-white">{{ $fine->reason }}</p>
                            <p class="text-xs mt-1">Ref Booking: #{{ $fine->bookingID }}</p>
                        </div>

                        <div class="mt-auto flex justify-end">
                            <a href="#" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-2 rounded-xl font-bold text-sm shadow-lg shadow-blue-500/30 transition transform hover:scale-105">
                                Pay Fine
                            </a>
                        </div>
                    </div>
                    @endforeach

                    {{-- EMPTY STATE --}}
                    @if((!isset($fines) || $fines->isEmpty()) && (!isset($balanceBookings) || $balanceBookings->isEmpty()))
                    <div class="h-full flex flex-col items-center justify-center text-center opacity-50 py-10">
                        <i class="fas fa-check-circle text-5xl text-green-500/50 mb-4"></i>
                        <p class="text-gray-300 font-bold">All caught up!</p>
                        <p class="text-gray-500 text-sm">No outstanding fines or payments.</p>
                    </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: rgba(255,255,255,0.05); border-radius: 2px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 2px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.4); }
</style>
@endsection