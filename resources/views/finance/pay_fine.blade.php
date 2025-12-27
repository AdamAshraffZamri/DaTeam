@extends('layouts.app')

@section('content')
<div class="fixed inset-0 z-0">
    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('hastabg.png') }}');"></div>
    <div class="absolute inset-0 bg-gradient-to-b from-black/80 via-black/50 to-black/90"></div>
</div>

<div class="relative z-10 min-h-[calc(100vh-64px)] py-12">
    <div class="container mx-auto px-4 max-w-5xl">

        <a href="{{ route('finance.index') }}" class="inline-flex items-center text-gray-400 hover:text-white mb-8 transition">
            <i class="fas fa-arrow-left mr-2"></i> Back to Finance
        </a>

        <div class="flex flex-col md:flex-row justify-between items-end mb-8 text-white">
            <div>
                <h1 class="text-3xl font-black drop-shadow-lg">Pay Penalty</h1>
                {{-- FIX: Construct the reference string manually since 'reason' column doesn't exist --}}
                <p class="text-gray-400 mt-1">Ref #{{ $penalty->bookingID }} • Penalty Fee</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <div class="lg:col-span-7 space-y-6">
                <div class="bg-white/5 backdrop-blur-md rounded-[2.5rem] p-8 border border-white/10 shadow-2xl">
                    <h2 class="text-xl font-bold text-white mb-6 flex items-center">
                        <i class="fas fa-exclamation-circle text-red-500 mr-3"></i> Penalty Details
                    </h2>
                    <div class="space-y-4">
                        
                        {{-- FIX: Display Breakdown instead of $penalty->reason --}}
                        <div class="flex justify-between items-center p-4 bg-white/5 rounded-2xl border border-white/5">
                            <div class="w-full">
                                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Fee Breakdown</p>
                                <div class="grid grid-cols-2 gap-2 text-sm">
                                    <div class="text-gray-400">Late Fees:</div>
                                    <div class="text-white text-right">MYR {{ number_format($penalty->penaltyFees, 2) }}</div>
                                    
                                    <div class="text-gray-400">Fuel Surcharge:</div>
                                    <div class="text-white text-right">MYR {{ number_format($penalty->fuelSurcharge, 2) }}</div>
                                    
                                    <div class="text-gray-400">Mileage Surcharge:</div>
                                    <div class="text-white text-right">MYR {{ number_format($penalty->mileageSurcharge, 2) }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-between items-center">
                            <p class="text-sm font-bold text-white uppercase tracking-wider">Total Fine</p>
                            <p class="text-4xl font-black text-red-500">
                                <span class="text-lg font-bold align-top mt-2 inline-block">MYR</span> 
                                {{-- FIX: Calculate the sum here --}}
                                {{ number_format($penalty->penaltyFees + $penalty->fuelSurcharge + $penalty->mileageSurcharge, 2) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-5">
                <form action="{{ route('finance.submit_fine', $penalty->penaltyID) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-[2.5rem] p-8 shadow-2xl text-center space-y-6">
                        <div class="bg-white rounded-2xl p-4 w-48 h-48 mx-auto shadow-lg flex items-center justify-center">
                            <img src="{{ asset('qr.JPG') }}" alt="QR Code" class="w-full h-full object-contain">
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Pay To: HASTA Rental</p>
                            <p class="text-xl font-mono font-bold text-white tracking-widest">8821 3491 0022</p>
                        </div>
                        <label class="block w-full h-32 border-2 border-dashed border-white/20 rounded-2xl flex flex-col items-center justify-center cursor-pointer hover:border-red-500 hover:bg-white/5 transition group bg-black/20">
                            <i class="fas fa-cloud-upload-alt text-2xl text-gray-400 group-hover:text-red-500 mb-2 transition"></i>
                            <span class="text-xs font-bold text-gray-300">Upload Receipt</span>
                            <input type="file" name="payment_proof" class="hidden" required onchange="document.getElementById('fname').innerText = this.files[0].name">
                        </label>
                        <p id="fname" class="text-xs text-red-400 font-bold h-4"></p>
                        <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-4 rounded-xl shadow-lg transition transform hover:scale-[1.02]">
                            Submit Fine Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection