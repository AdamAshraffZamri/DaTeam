@extends('layouts.app')

@section('content')
{{-- 1. FIXED BACKGROUND --}}
<div class="fixed inset-0 z-0">
    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('hastabg.png') }}');"></div>
    <div class="absolute inset-0 bg-gradient-to-r from-black/70 via-black/45 to-black/75"></div>
</div>

{{-- 2. CONTENT WRAPPER --}}
<div class="relative z-10 min-h-[calc(100vh-64px)] py-12">
    <div class="container mx-auto px-4 max-w-6xl">
        @if(session('success'))
            <div class="container mx-auto px-4 mt-4">
                <div class="bg-green-500/20 border border-green-500 text-green-400 px-4 py-3 rounded-xl">
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="container mx-auto px-4 mt-4">
                <div class="bg-red-500/20 border border-red-500 text-red-400 px-4 py-3 rounded-xl">
                    {{ session('error') }}
                </div>
            </div>
        @endif
        {{-- HEADER --}}
        <div class="text-center mb-10">
            <h1 class="text-4xl font-black text-white drop-shadow-lg">Payments Center</h1>
            <p class="text-gray-400 mt-2">Manage refunds and outstanding rental balances.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

            {{-- ========================= --}}
            {{-- COLUMN 1: CLAIMABLE REFUNDS --}}
            {{-- ========================= --}}
            <div class="bg-black/50 backdrop-blur-md rounded-[2.5rem] p-8 border border-white/10 shadow-2xl h-full flex flex-col">

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
                                    {{-- [FIXED] CALCULATE ACTUAL PAID AMOUNT INSTEAD OF TOTAL COST --}}
                                    <span class="text-white font-bold ml-1">
                                        MYR {{ number_format($claim->payments->sum('amount'), 2) }}
                                    </span>
                                </div>
                                <div class="text-xs font-bold italic text-red-400">
                                    Status: {{ $claim->bookingStatus }}
                                </div>
                            </div>

                            @if($claim->remarks)
                                <div class="bg-white/5 p-3 rounded-xl border border-white/10 mb-4">
                                    <p class="text-[10px] font-bold text-blue-400 uppercase mb-1">
                                        <i class="fas fa-info-circle mr-1"></i> Notes / Deduction Details
                                    </p>
                                    <p class="text-xs text-gray-300 italic leading-relaxed whitespace-pre-line">
                                        {{ $claim->remarks }}
                                    </p>
                                </div>
                            @endif

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
            <div class="bg-black/50 backdrop-blur-md rounded-[2.5rem] p-8 border border-white/10 shadow-2xl h-full flex flex-col">

                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-2xl font-bold text-white flex items-center">
                        <i class="fas fa-file-invoice-dollar text-orange-500 mr-3"></i>
                        Outstanding Payments
                    </h2>
                    <span class="bg-orange-500/20 text-orange-400 text-xs font-bold px-3 py-1 rounded-full border border-orange-500/30">
                        {{ ($balanceBookings->count() ?? 0) + ($fines->count() ?? 0) }} Due
                    </span>
                </div>

                <div class="space-y-4 flex-grow max-h-[500px] overflow-y-auto pr-2 custom-scrollbar">

    {{-- PENALTIES SECTION --}}
    @forelse($fines ?? [] as $penalty)
        @php
            $penaltyAmount = $penalty->amount ?? ($penalty->penaltyFees + $penalty->fuelSurcharge + $penalty->mileageSurcharge);
        @endphp
        
        <div class="bg-white/5 rounded-2xl p-5 border border-red-500/30 hover:bg-white/10 transition relative overflow-hidden flex flex-col">
            <div class="absolute left-0 top-0 bottom-0 w-1 bg-red-500"></div>

            {{-- HEADER --}}
            <div class="flex justify-between items-start mb-3">
                <div>
                    <span class="text-xs font-bold text-red-400 uppercase">Penalty</span>
                    <p class="text-white font-bold text-lg">MYR {{ number_format($penaltyAmount, 2) }}</p>
                </div>
                <div class="text-right">
                    <span class="bg-red-500/20 text-red-400 text-[10px] px-2 py-1 rounded uppercase font-bold">
                        {{ $penalty->penaltyStatus ?? 'Unpaid' }}
                    </span>
                    <p class="text-[10px] text-gray-500 mt-1">ID: #{{ $penalty->penaltyID }}</p>
                </div>
            </div>

            {{-- PENALTY INFO --}}
            <div class="text-gray-400 text-sm mb-3">
                <i class="fas fa-exclamation-triangle mr-1"></i> 
                <span class="font-bold">{{ $penalty->reason ?? 'Penalty Charge' }}</span>
            </div>

            @if($penalty->bookingID)
            <div class="text-gray-400 text-sm mb-3">
                <i class="fas fa-car mr-1"></i> 
                @if($penalty->booking && $penalty->booking->vehicle)
                    {{ $penalty->booking->vehicle->model }} ({{ $penalty->booking->vehicle->plateNo }})
                @else
                    Booking #{{ $penalty->bookingID }}
                @endif
            </div>
            @endif

            {{-- FINANCIAL BREAKDOWN --}}
            <div class="bg-black/20 p-3 rounded-lg text-sm space-y-1 mb-4">
                <div class="flex justify-between font-bold text-white">
                    <span>Penalty Amount:</span>
                    <span>MYR {{ number_format($penaltyAmount, 2) }}</span>
                </div>
                
                @if($penalty->date_imposed)
                <div class="flex justify-between text-gray-400 text-xs mt-2">
                    <span>Date Imposed:</span>
                    <span>{{ \Carbon\Carbon::parse($penalty->date_imposed)->format('d M Y') }}</span>
                </div>
                @endif
            </div>

            {{-- ACTION BUTTONS --}}
            <div class="mt-auto flex justify-end">
                <a href="{{ route('finance.pay_fine', $penalty->penaltyID) }}" 
                   class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-xl font-bold text-sm shadow-lg transition">
                    Pay Penalty
                </a>
            </div>
        </div>
    @empty
    @endforelse

    {{-- BOOKING BALANCES SECTION --}}
    @forelse($balanceBookings ?? [] as $booking)
    @php
        // 1. DATA CALCULATION
        $totalCost = $booking->totalCost;
        
        // Sum of payments effectively received and verified by Admin
        $verifiedPaid = $booking->payments->where('paymentStatus', 'Verified')->sum('amount');
        
        // Sum of payments currently processing (e.g., the deposit you just submitted)
        $pendingPaid = $booking->payments->where('paymentStatus', 'Pending Verification')->sum('amount');
        
        // Gross Balance: Total Cost - Verified Paid (The absolute remaining debt)
        $grossBalance = $totalCost - $verifiedPaid;

        // Net Balance: Gross Balance - Pending Paid (What is left to pay if current pending payments are Confirmed)
        // If you just paid a deposit, this will still be > 0
        $netBalance = $grossBalance - $pendingPaid;
        
        // This ensures floating point precision errors don't cause issues
        if($netBalance < 0) $netBalance = 0;
    @endphp

    {{-- Show card if there is debt OR if we are waiting for a payment to clear --}}
    @if($grossBalance > 0)
        <div class="bg-white/5 rounded-2xl p-5 border border-orange-500/30 hover:bg-white/10 transition relative overflow-hidden flex flex-col">
            <div class="absolute left-0 top-0 bottom-0 w-1 bg-orange-500"></div>

            {{-- HEADER --}}
            <div class="flex justify-between items-start mb-3">
                <div>
                    <span class="text-xs font-bold text-orange-400 uppercase">Outstanding</span>
                    {{-- Show the Net Balance here so user knows what is actually LEFT to pay --}}
                    <p class="text-white font-bold text-lg">MYR {{ number_format($netBalance, 2) }}</p>
                </div>
                <div class="text-right">
                    <span class="bg-orange-500/20 text-orange-400 text-[10px] px-2 py-1 rounded uppercase font-bold">
                        {{ $booking->bookingStatus }}
                    </span>
                    <p class="text-[10px] text-gray-500 mt-1">ID: #{{ $booking->bookingID }}</p>
                </div>
            </div>

            {{-- VEHICLE INFO --}}
            <div class="text-gray-400 text-sm mb-3">
                <i class="fas fa-car mr-1"></i> {{ $booking->vehicle->model }} ({{ $booking->vehicle->plateNo }})
            </div>

            {{-- FINANCIAL BREAKDOWN --}}
            <div class="bg-black/20 p-3 rounded-lg text-sm space-y-1 mb-4">
                <div class="flex justify-between text-gray-400">
                    <span>Total Cost:</span>
                    <span>MYR {{ number_format($totalCost, 2) }}</span>
                </div>
                
                @if($verifiedPaid > 0)
                <div class="flex justify-between text-green-400">
                    <span>Paid (Verified):</span>
                    <span>- MYR {{ number_format($verifiedPaid, 2) }}</span>
                </div>
                @endif

                @if($pendingPaid > 0)
                <div class="flex justify-between text-yellow-400 font-bold">
                    <span>Processing:</span>
                    <span>- MYR {{ number_format($pendingPaid, 2) }}</span>
                </div>
                @endif
                
                <div class="border-t border-white/10 pt-1 mt-1 flex justify-between font-bold text-white">
                    <span>Balance to Pay:</span>
                    <span>MYR {{ number_format($netBalance, 2) }}</span>
                </div>
            </div>

            {{-- ACTION BUTTONS --}}
            <div class="mt-auto flex justify-end">
                @if($netBalance > 0.50) {{-- Using 0.50 buffer for float safety --}}
                    {{-- User still owes money --}}
                    <a href="{{ route('finance.pay', $booking->bookingID) }}" 
                       class="bg-orange-600 hover:bg-orange-700 text-white px-6 py-2 rounded-xl font-bold text-sm shadow-lg transition">
                        Pay Balance
                    </a>
                @elseif($pendingPaid > 0)
                    {{-- User has paid everything, just waiting for admin --}}
                    <span class="bg-yellow-500/20 text-yellow-400 border border-yellow-500/30 px-4 py-2 rounded-xl text-xs font-bold animate-pulse">
                        <i class="fas fa-clock mr-1"></i> Waiting for Verification
                    </span>
                @else
                    {{-- Paid in full --}}
                    <span class="text-green-500 font-bold text-sm">
                        <i class="fas fa-check-circle mr-1"></i> Fully Paid
                    </span>
                @endif
            </div>
        </div>
    @endif

@empty
    <div class="col-span-full text-center py-10 opacity-50">
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
