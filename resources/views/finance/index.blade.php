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
            <p class="text-gray-400 mt-2">Manage refunds and outstanding rental balances.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

            {{-- ========================= --}}
            {{-- COLUMN 1: CLAIMABLE REFUNDS --}}
            {{-- ========================= --}}
            <div class="bg-white/5 backdrop-blur-md rounded-[2.5rem] p-8 border border-white/10 shadow-2xl h-full flex flex-col">

                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-2xl font-bold text-white flex items-center">
                        <i class="fas fa-hand-holding-usd text-green-400 mr-3"></i>
                        Claimable Refunds
                    </h2>
                    <span class="bg-green-500/20 text-green-400 text-xs font-bold px-3 py-1 rounded-full border border-green-500/30">
                        {{ $claims->count() ?? 0 }} Items
                    </span>
                </div>

                <div class="space-y-4 flex-grow max-h-[500px] overflow-y-auto pr-2 custom-scrollbar">
                    @forelse($claims ?? [] as $claim)

                        <div class="bg-white/5 rounded-2xl p-5 border border-white/5 hover:bg-white/10 transition relative overflow-hidden">
                            <div class="absolute left-0 top-0 bottom-0 w-1 bg-green-500"></div>

                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <span class="text-xs font-bold text-gray-500 uppercase">Booking ID</span>
                                    <p class="text-white font-bold">#{{ $claim->bookingID }}</p>
                                </div>
                                <span class="text-xs text-gray-400">
                                    {{ \Carbon\Carbon::parse($claim->bookingDate)->format('d/m/Y') }}
                                </span>
                            </div>

                            <div class="text-gray-300 text-sm mb-4 space-y-1">
                                <div>
                                    <i class="fas fa-car text-gray-500 mr-2"></i>
                                    {{ $claim->vehicle->model ?? 'Unknown Vehicle' }}
                                </div>
                                <div>
                                    <i class="fas fa-money-bill-wave text-gray-500 mr-2"></i>
                                    Refund:
                                    <span class="text-white font-bold ml-1">
                                        MYR {{ number_format($claim->totalCost, 2) }}
                                    </span>
                                </div>
                                <div class="text-xs font-bold italic text-red-400">
                                    Status: {{ $claim->bookingStatus }}
                                </div>
                            </div>

                            <div class="flex justify-end">
                                @if($claim->payment && $claim->payment->depoStatus === 'Requested')
                                    <span class="bg-yellow-500/20 text-yellow-400 px-4 py-2 rounded-xl text-xs font-bold border border-yellow-500/30 animate-pulse">
                                        <i class="fas fa-clock mr-1"></i> Processing
                                    </span>

                                @elseif($claim->payment && $claim->payment->depoStatus === 'Refunded')
                                    <span class="bg-green-500/20 text-green-400 px-4 py-2 rounded-xl text-xs font-bold border border-green-500/30">
                                        <i class="fas fa-check-circle mr-1"></i> Refunded
                                    </span>

                                @else
                                    <form action="{{ route('finance.claim', $claim->bookingID) }}" method="POST">
                                        @csrf
                                        <button class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-xl font-bold text-sm shadow-lg shadow-green-500/20 transition hover:scale-105">
                                            Claim Refund
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>

                    @empty
                        <div class="h-full flex flex-col items-center justify-center text-center opacity-50 py-10">
                            <i class="fas fa-folder-open text-4xl text-gray-600 mb-2"></i>
                            <p class="text-gray-400">No claimable refunds available.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- ========================= --}}
            {{-- COLUMN 2: OUTSTANDING PAYMENTS --}}
            {{-- ========================= --}}
            <div class="bg-white/5 backdrop-blur-md rounded-[2.5rem] p-8 border border-white/10 shadow-2xl h-full flex flex-col">

                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-2xl font-bold text-white flex items-center">
                        <i class="fas fa-file-invoice-dollar text-orange-500 mr-3"></i>
                        Outstanding Payments
                    </h2>
                    <span class="bg-orange-500/20 text-orange-400 text-xs font-bold px-3 py-1 rounded-full border border-orange-500/30">
                        {{ $balanceBookings->count() ?? 0 }} Due
                    </span>
                </div>

                <div class="space-y-4 flex-grow max-h-[500px] overflow-y-auto pr-2 custom-scrollbar">
    @forelse($balanceBookings ?? [] as $booking)
        @php
            // Get all VERIFIED payments
            $verifiedPayments = $booking->payments->where('paymentStatus', 'Verified');
            
            // Total amount paid (including deposit and balance payments)
            $totalPaid = $verifiedPayments->sum('amount');
            
            // Total deposit paid (sum of depoAmount fields from verified payments)
            $depositPaid = $verifiedPayments->sum('depoAmount');
            
            // Calculate remaining balance
            $balance = $booking->totalCost - $totalPaid;
            
            // Calculate what's been paid towards the actual rental (non-deposit)
            $rentalPaid = $totalPaid - $depositPaid;
            
            // Show only if there's actual balance to pay
        @endphp

        @if($balance > 0)
            <div class="bg-white/5 rounded-2xl p-5 border border-orange-500/30 hover:bg-white/10 transition relative overflow-hidden">
                <div class="absolute left-0 top-0 bottom-0 w-1 bg-orange-500"></div>

                <div class="flex justify-between items-start mb-3">
                    <div>
                        <span class="text-xs font-bold text-orange-400 uppercase">
                            Outstanding Balance
                        </span>
                        <p class="text-white font-bold text-lg">
                            MYR {{ number_format($balance, 2) }}
                        </p>
                    </div>
                    <span class="bg-orange-500/20 text-orange-400 text-[10px] px-2 py-1 rounded uppercase font-bold">
                        {{ $booking->bookingStatus }}
                    </span>
                </div>

                {{-- Payment breakdown --}}
                <div class="text-gray-300 text-sm mb-3 space-y-1 bg-black/20 p-3 rounded-lg">
                    <div class="flex justify-between">
                        <span>Total Booking Cost:</span>
                        <span class="font-bold">MYR {{ number_format($booking->totalCost, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Deposit Required:</span>
                        <span class="text-blue-400">MYR {{ number_format($booking->vehicle->baseDepo ?? 0, 2) }}</span>
                    </div>
                    <div class="border-t border-gray-700 pt-1 mt-1"></div>
                    <div class="flex justify-between">
                        <span>Total Paid:</span>
                        <span class="text-green-400 font-bold">MYR {{ number_format($totalPaid, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="ml-2">↳ Deposit portion:</span>
                        <span>MYR {{ number_format($depositPaid, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="ml-2">↳ Rental portion:</span>
                        <span>MYR {{ number_format($rentalPaid, 2) }}</span>
                    </div>
                    <div class="border-t border-gray-700 pt-1 mt-1">
                        <div class="flex justify-between font-bold">
                            <span>Balance Due:</span>
                            <span class="text-orange-400">MYR {{ number_format($balance, 2) }}</span>
                        </div>
                    </div>
                </div>

                <div class="text-gray-400 text-sm mb-4">
                    <p class="font-medium text-white">
                        {{ $booking->vehicle->model }}
                        ({{ $booking->vehicle->plateNo }})
                    </p>
                    <p class="text-xs mt-1">
                        Booking ID: #{{ $booking->bookingID }}
                    </p>
                    <p class="text-xs text-red-400 mt-1">
                        <i class="far fa-clock mr-1"></i>
                        Due by {{ \Carbon\Carbon::parse($booking->originalDate ?? $booking->created_at)->format('d M Y') }}
                    </p>
                </div>

                <div class="flex justify-end">
                    <a href="{{ route('finance.pay', $booking->bookingID) }}"
                       class="bg-orange-600 hover:bg-orange-700 text-white px-8 py-2 rounded-xl font-bold text-sm shadow-lg shadow-orange-500/30 transition hover:scale-105">
                        Pay Balance
                    </a>
                </div>
            </div>
        @endif

    @empty
        <div class="h-full flex flex-col items-center justify-center text-center opacity-50 py-10">
            <i class="fas fa-check-circle text-5xl text-green-500/50 mb-4"></i>
            <p class="text-gray-300 font-bold">All payments settled</p>
            <p class="text-gray-500 text-sm">No outstanding balances.</p>
        </div>
    @endforelse
</div>
            </div>

        </div>
    </div>
</div>

{{-- SCROLLBAR --}}
<style>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: rgba(255,255,255,0.05); }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.25); border-radius: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.45); }
</style>
@endsection
