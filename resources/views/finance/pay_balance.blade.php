@extends('layouts.app')

@section('content')
{{-- BACKGROUND --}}
<div class="fixed inset-0 z-0">
    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('hastabg.png') }}');"></div>
    <div class="absolute inset-0 bg-gradient-to-b from-black/80 via-black/50 to-black/90"></div>
</div>

<div class="relative z-10 min-h-[calc(100vh-64px)] py-12">
    <div class="container mx-auto px-4 max-w-5xl">

        {{-- BACK BUTTON --}}
        <a href="{{ route('finance.index') }}" class="inline-flex items-center text-gray-400 hover:text-white mb-8 transition">
            <i class="fas fa-arrow-left mr-2"></i> Back to Finance
        </a>

        <div class="flex flex-col md:flex-row justify-between items-end mb-8 text-white">
            <div>
                <h1 class="text-3xl font-black drop-shadow-lg">Settle Balance</h1>
                <p class="text-gray-400 mt-1">Booking #{{ $booking->bookingID }} • {{ $booking->vehicle->model }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            {{-- LEFT: BREAKDOWN --}}
            <div class="lg:col-span-7 space-y-6">
                <div class="bg-white/5 backdrop-blur-md rounded-[2.5rem] p-8 border border-white/10 shadow-2xl">
                    <h2 class="text-xl font-bold text-white mb-6 flex items-center">
                        <i class="fas fa-calculator text-orange-500 mr-3"></i> Payment Breakdown
                    </h2>

                    <div class="space-y-4">
                        {{-- Total Cost --}}
                        <div class="flex justify-between items-center p-4 bg-white/5 rounded-2xl border border-white/5">
                            <div>
                                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Booking Cost</p>
                                <p class="text-gray-400 text-xs mt-1">{{ $booking->days }} Days Rental + Deposit</p>
                            </div>
                            <p class="text-white font-bold text-lg">MYR {{ number_format($booking->totalCost, 2) }}</p>
                        </div>

                        {{-- Already Paid --}}
                        <div class="flex justify-between items-center p-4 bg-green-500/10 rounded-2xl border border-green-500/20">
                            <div>
                                <p class="text-xs font-bold text-green-400 uppercase tracking-wider">Amount Paid</p>
                                <p class="text-green-500/60 text-xs mt-1">Deposit / Partial Payments</p>
                            </div>
                            <p class="text-green-400 font-bold text-lg">- MYR {{ number_format($totalPaid, 2) }}</p>
                        </div>

                        {{-- Divider --}}
                        <div class="border-t-2 border-dashed border-white/10 my-4"></div>

                        {{-- Balance Due --}}
                        <div class="flex justify-between items-center">
                            <p class="text-sm font-bold text-white uppercase tracking-wider">Remaining Balance</p>
                            <p class="text-4xl font-black text-[#ea580c]">
                                <span class="text-lg font-bold align-top mt-2 inline-block">MYR</span> {{ number_format($balance, 2) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT: PAYMENT FORM --}}
            <div class="lg:col-span-5">
                <form action="{{ route('finance.submit_balance', $booking->bookingID) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-[2.5rem] p-8 shadow-2xl text-center space-y-6">
                        
                        <div class="bg-white rounded-2xl p-4 w-48 h-48 mx-auto shadow-lg flex items-center justify-center">
                            <img src="{{ asset('qr.JPG') }}" alt="QR Code" class="w-full h-full object-contain">
                        </div>

                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Pay To: HASTA Rental</p>
                            <p class="text-xl font-mono font-bold text-white tracking-widest">8821 3491 0022</p>
                        </div>

                        {{-- Upload --}}
                        <label class="block w-full h-32 border-2 border-dashed border-white/20 rounded-2xl flex flex-col items-center justify-center cursor-pointer hover:border-orange-500 hover:bg-white/5 transition group bg-black/20">
                            <i class="fas fa-cloud-upload-alt text-2xl text-gray-400 group-hover:text-orange-500 mb-2 transition"></i>
                            <span class="text-xs font-bold text-gray-300">Upload Receipt</span>
                            <input type="file" name="payment_proof" class="hidden" onchange="document.getElementById('fname').innerText = this.files[0].name">
                        </label>
                        <p id="fname" class="text-xs text-orange-400 font-bold h-4"></p>

                        <button type="submit" class="w-full bg-[#ea580c] hover:bg-orange-600 text-white font-bold py-4 rounded-xl shadow-lg transition transform hover:scale-[1.02]">
                            Submit Payment
                        </button>

                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
@endsection