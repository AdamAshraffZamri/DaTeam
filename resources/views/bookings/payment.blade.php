@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 pb-20 relative">
    
    <div class="absolute top-0 left-0 w-full h-80 bg-gradient-to-b from-orange-600 to-orange-500 -z-10"></div>

    <div class="container mx-auto px-4 py-12 max-w-6xl">
        
        <div class="flex flex-col md:flex-row justify-between items-end mb-8 text-white">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight">Complete Payment</h1>
                <p class="text-orange-100 mt-1 text-sm font-medium">Secure checkout for your rental</p>
            </div>
            
            <div class="mt-4 md:mt-0 bg-white/20 backdrop-blur-md border border-white/30 rounded-2xl px-6 py-3 flex items-center space-x-4">
                <div class="text-right">
                    <p class="text-xs font-bold text-orange-100 uppercase tracking-wider">Vehicle Selected</p>
                    <p class="font-bold text-xl leading-none">{{ $vehicle->model }}</p>
                </div>
                <div class="h-8 w-px bg-white/30"></div>
                <div class="bg-white text-orange-600 font-bold px-3 py-1 rounded-lg text-sm shadow-sm">
                    {{ $vehicle->plateNo }}
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <div class="lg:col-span-7 space-y-6">
                
                <div class="bg-white rounded-[2rem] p-6 shadow-xl shadow-orange-900/5 border border-gray-100">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center">
                            <i class="far fa-calendar-alt text-orange-500 mr-2"></i> Rental Itinerary
                        </h3>
                        <span class="bg-orange-50 text-orange-700 text-xs font-bold px-3 py-1 rounded-full">
                            {{ $days }} Days Duration
                        </span>
                    </div>

                    <div class="relative pl-6 border-l-2 border-dashed border-gray-200 space-y-8">
                        <div class="relative">
                            <div class="absolute -left-[31px] top-1 bg-white border-4 border-green-500 w-6 h-6 rounded-full shadow-sm"></div>
                            <div>
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-1">Pickup</p>
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-bold text-gray-900 text-lg">{{ \Carbon\Carbon::parse($pickupDate)->format('d M Y') }}</p>
                                        <p class="text-sm font-medium text-gray-500">{{ $pickupLoc }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs font-bold text-gray-400">Time</p>
                                        <p class="font-bold text-gray-900">10:00 AM</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="relative">
                            <div class="absolute -left-[31px] top-1 bg-white border-4 border-red-500 w-6 h-6 rounded-full shadow-sm"></div>
                            <div>
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-1">Return</p>
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-bold text-gray-900 text-lg">{{ \Carbon\Carbon::parse($returnDate)->format('d M Y') }}</p>
                                        <p class="text-sm font-medium text-gray-500">{{ $returnLoc }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs font-bold text-gray-400">Time</p>
                                        <p class="font-bold text-gray-900">10:00 AM</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-[2rem] p-8 shadow-xl shadow-orange-900/5 border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center">
                        <i class="fas fa-file-invoice-dollar text-orange-500 mr-2"></i> Payment Summary
                    </h3>
                    
                    <div class="space-y-4 text-sm">
                        <div class="flex justify-between items-center text-gray-600">
                            <span>Rental Rate (x {{ $days }} days)</span>
                            <span class="font-bold">MYR {{ number_format(($vehicle->priceHour * 24) * $days, 2) }}</span>
                        </div>
                        
                        <div class="flex justify-between items-center pt-2">
                            <span class="text-gray-800 font-bold">Total Rent</span>
                            <span class="font-bold text-gray-900">MYR {{ number_format(($vehicle->priceHour * 24) * $days, 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-800 font-bold">Security Deposit <span class="text-xs font-normal text-gray-400">(Refundable)</span></span>
                            <span class="font-bold text-gray-900">MYR {{ number_format($vehicle->baseDepo, 2) }}</span>
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t-2 border-dashed border-gray-200 flex justify-between items-end">
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase">Grand Total</p>
                            <p class="text-xs text-gray-400 font-medium">*All prices in MYR</p>
                        </div>
                        <p class="text-4xl font-black text-orange-600 tracking-tight">
                            <span class="text-lg font-bold align-top mt-2 inline-block">MYR</span> {{ number_format($total, 2) }}
                        </p>
                    </div>
                </div>

            </div>

            <div class="lg:col-span-5">
                <form action="{{ route('book.payment.submit', ['id' => $vehicle->VehicleID]) }}" method="POST" enctype="multipart/form-data">
                    @csrf <input type="hidden" name="pickup_location" value="{{ $pickupLoc }}">
                    <input type="hidden" name="return_location" value="{{ $returnLoc }}">
                    <input type="hidden" name="pickup_date" value="{{ $pickupDate }}">
                    <input type="hidden" name="return_date" value="{{ $returnDate }}">
                    <input type="hidden" name="total" value="{{ $total }}">

                    <div class="bg-white rounded-[2rem] p-8 shadow-2xl shadow-orange-500/10 border border-gray-100 text-center">                            <div class="bg-white rounded-xl p-2 w-48 h-48 flex items-center justify-center overflow-hidden mx-auto mb-4 border border-gray-200 shadow-sm">
                                <img src="{{ asset('qr.jpg') }}" alt="Payment QR Code" class="w-full h-full object-contain">
                            </div>
                            
                            <div class="mt-2">
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">Bank Account Number (HASTA)</p>
                                <div class="flex items-center justify-center space-x-2 mt-1">
                                    <p class="text-xl font-bold text-gray-900 font-mono tracking-widest">8821 3491 0022</p>
                                    <button type="button" class="text-gray-400 hover:text-orange-500 transition"><i class="far fa-copy"></i></button>
                                </div>
                            </div>
                    </div>

                        <div class="mb-8">
                            <label for="proof_upload" class="block w-full h-32 border-2 border-dashed border-gray-300 rounded-2xl flex flex-col items-center justify-center cursor-pointer hover:border-orange-500 hover:bg-orange-50 transition group bg-gray-50/50">
                                <div class="w-10 h-10 bg-white rounded-full shadow-sm flex items-center justify-center mb-2 group-hover:scale-110 transition">
                                    <i class="fas fa-cloud-upload-alt text-orange-500 text-lg"></i>
                                </div>
                                <span class="text-sm font-bold text-gray-600 group-hover:text-orange-600">Click to upload receipt</span>
                                <span class="text-xs text-gray-400 mt-1">JPG, PNG or PDF</span>
                                
                                <input type="file" id="proof_upload" name="payment_proof" class="hidden" onchange="alert('File Selected: ' + this.files[0].name)">
                            </label>
                        </div>

                        <div class="space-y-4">
                            <p class="text-xs text-center text-gray-400 leading-relaxed px-4">
                                By clicking submit, you agree to our booking terms. Verification usually takes 15-30 minutes.
                            </p>
                            
                            <button type="submit" class="block w-full bg-gray-900 hover:bg-green-600 text-white font-bold py-4 rounded-xl shadow-xl hover:shadow-green-500/30 transition-all transform hover:scale-[1.02] text-center text-lg">
                                Confirm & Submit Payment
                            </button>
                        </div>

                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
@endsection