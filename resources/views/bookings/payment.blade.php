@extends('layouts.app')

@section('content')
{{-- 1. FIXED BACKGROUND --}}
<div class="fixed inset-0 z-0">
    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('hastabg.png') }}');"></div>
    <div class="absolute inset-0 bg-gradient-to-b from-black/80 via-black/50 to-black/90"></div>
</div>

{{-- 2. SCROLLABLE CONTENT --}}
<div class="relative z-10 min-h-[calc(100vh-64px)] pb-20">
    
    <div class="container mx-auto px-4 py-12 max-w-6xl">
        
        {{-- HEADER SECTION --}}
        <div class="flex flex-col md:flex-row justify-between items-end mb-8 text-white">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight drop-shadow-lg">Complete Payment</h1>
                <p class="text-gray-400 mt-1 text-sm font-medium">Secure checkout for your rental</p>
            </div>
            
            <div class="mt-4 md:mt-0 bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl px-6 py-3 flex items-center space-x-4">
                <div class="text-right">
                    <p class="text-xs font-bold text-orange-400 uppercase tracking-wider">Vehicle Selected</p>
                    <p class="font-bold text-xl leading-none">{{ $vehicle->model }}</p>
                </div>
                <div class="h-8 w-px bg-white/20"></div>
                <div class="bg-[#ea580c] text-white font-bold px-3 py-1 rounded-lg text-sm shadow-sm uppercase tracking-tighter">
                    {{ $vehicle->plateNo }}
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            {{-- LEFT COLUMN: ITINERARY & SUMMARY --}}
            <div class="lg:col-span-7 space-y-6">
                
                {{-- RENTAL ITINERARY (Glass) --}}
                <div class="bg-white/5 backdrop-blur-md rounded-[2rem] p-6 border border-white/10 shadow-2xl">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-bold text-white flex items-center">
                            <i class="far fa-calendar-alt text-orange-500 mr-3"></i> Rental Itinerary
                        </h3>
                        <span class="bg-orange-500/20 text-orange-300 text-xs font-bold px-3 py-1 rounded-full border border-orange-500/30">
                            {{ $days }} Days Duration
                        </span>
                    </div>

                    <div class="relative pl-6 border-l-2 border-dashed border-white/10 space-y-8">
                        {{-- Pickup --}}
                        <div class="relative">
                            <div class="absolute -left-[31px] top-1 bg-gray-900 border-4 border-green-500 w-6 h-6 rounded-full shadow-lg"></div>
                            <div>
                                <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Pickup</p>
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-bold text-white text-lg">{{ \Carbon\Carbon::parse($pickupDate)->format('d M Y') }}</p>
                                        <p class="text-sm font-medium text-gray-400">{{ $pickupLoc }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs font-bold text-gray-500">Time</p>
                                        <p class="font-bold text-white">{{ request('pickup_time', '10:00') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Return --}}
                        <div class="relative">
                            <div class="absolute -left-[31px] top-1 bg-gray-900 border-4 border-red-500 w-6 h-6 rounded-full shadow-lg"></div>
                            <div>
                                <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Return</p>
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-bold text-white text-lg">{{ \Carbon\Carbon::parse($returnDate)->format('d M Y') }}</p>
                                        <p class="text-sm font-medium text-gray-400">{{ $returnLoc }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs font-bold text-gray-500">Time</p>
                                        <p class="font-bold text-white">{{ request('return_time', '10:00') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- PAYMENT SUMMARY (Glass) --}}
                <div class="bg-white/5 backdrop-blur-md rounded-[2rem] p-8 border border-white/10 shadow-2xl">
                    <h3 class="text-lg font-bold text-white mb-6 flex items-center">
                        <i class="fas fa-file-invoice-dollar text-orange-500 mr-3"></i> Payment Summary
                    </h3>
                    
                    <div class="space-y-4 text-sm">
                        <div class="flex justify-between items-center text-gray-300">
                            <span>Rental Rate (RM {{ number_format($vehicle->priceHour * 24, 0) }} / day)</span>
                            <span class="font-bold text-white">MYR {{ number_format(($vehicle->priceHour * 24) * $days, 2) }}</span>
                        </div>
                        
                        <div class="flex justify-between items-center pt-2">
                            <span class="text-gray-400">Total Rent</span>
                            <span class="font-bold text-white">MYR {{ number_format(($vehicle->priceHour * 24) * $days, 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400">Security Deposit <span class="text-[10px] text-gray-500 uppercase ml-1">(Refundable)</span></span>
                            <span class="font-bold text-white">MYR {{ number_format($vehicle->baseDepo, 2) }}</span>
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t-2 border-dashed border-white/10 flex justify-between items-end">
                        <div>
                            <p class="text-xs font-bold text-gray-500 uppercase">Grand Total</p>
                            <p class="text-xs text-gray-600 font-medium">*All prices in MYR</p>
                        </div>
                        <p class="text-4xl font-black text-[#ea580c] tracking-tight">
                            <span class="text-lg font-bold align-top mt-2 inline-block">MYR</span> {{ number_format($total, 2) }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- RIGHT COLUMN: PAYMENT SUBMISSION --}}
            <div class="lg:col-span-5">
                <form action="{{ route('book.payment.submit', ['id' => $vehicle->VehicleID]) }}" method="POST" enctype="multipart/form-data">
                    @csrf 
                    <input type="hidden" name="pickup_location" value="{{ $pickupLoc }}">
                    <input type="hidden" name="return_location" value="{{ $returnLoc }}">
                    <input type="hidden" name="pickup_date" value="{{ $pickupDate }}">
                    <input type="hidden" name="return_date" value="{{ $returnDate }}">
                    <input type="hidden" name="pickup_time" value="{{ request('pickup_time', '10:00') }}">
                    <input type="hidden" name="return_time" value="{{ request('return_time', '10:00') }}">
                    <input type="hidden" name="total" value="{{ $total }}">

                    <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-[2rem] p-8 shadow-2xl text-center space-y-8">
                        
                        {{-- QR Section --}}
                        <div>
                            <div class="bg-white rounded-2xl p-3 w-48 h-48 flex items-center justify-center overflow-hidden mx-auto mb-4 shadow-2xl border-4 border-white/10">
                                <img src="{{ asset('qr.JPG') }}" alt="Payment QR Code" class="w-full h-full object-contain">
                            </div>
                            
                            <div class="mt-4">
                                <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Bank Account (HASTA)</p>
                                <div class="flex items-center justify-center space-x-2 mt-1">
                                    <p class="text-xl font-bold text-white font-mono tracking-widest">8821 3491 0022</p>
                                    <button type="button" class="text-gray-500 hover:text-orange-500 transition"><i class="far fa-copy"></i></button>
                                </div>
                            </div>
                        </div>

                        {{-- File Upload --}}
                        <div>
                            <label for="proof_upload" class="block w-full h-36 border-2 border-dashed border-white/10 rounded-2xl flex flex-col items-center justify-center cursor-pointer hover:border-orange-500 hover:bg-white/5 transition group bg-white/5">
                                <div class="w-12 h-12 bg-white/10 rounded-full shadow-xl flex items-center justify-center mb-2 group-hover:scale-110 transition border border-white/10">
                                    <i class="fas fa-cloud-upload-alt text-orange-500 text-lg"></i>
                                </div>
                                <span class="text-sm font-bold text-gray-300 group-hover:text-white">Click to upload receipt</span>
                                <span class="text-[10px] text-gray-500 mt-1 uppercase">JPG, PNG or PDF (Max 2MB)</span>
                                
                                <input type="file" id="proof_upload" name="payment_proof" class="hidden" onchange="document.getElementById('file-name').innerText = 'Selected: ' + this.files[0].name">
                            </label>
                            <p id="file-name" class="text-xs text-orange-400 mt-2 font-bold"></p>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="space-y-4">
                            <p class="text-[10px] text-center text-gray-500 leading-relaxed px-4 uppercase font-bold">
                                Verification usually takes 15-30 minutes.
                            </p>
                            
                            <button type="submit" class="block w-full bg-[#ea580c] hover:bg-orange-600 text-white font-bold py-4 rounded-xl shadow-xl hover:shadow-orange-500/40 transition-all transform hover:scale-[1.02] text-center text-lg">
                                Confirm & Submit
                            </button>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
@endsection