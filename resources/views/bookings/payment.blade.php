@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-orange-50/50">
    <div class="container mx-auto px-4 py-8 max-w-7xl">
        
        <div class="bg-orange-200/40 backdrop-blur-sm rounded-[40px] p-8 shadow-xl border border-white/60">
            
            <div class="flex flex-col md:flex-row justify-between items-center mb-6 border-b-2 border-red-400/20 pb-4">
                <h1 class="text-3xl font-bold text-gray-900">Payment <span class="text-sm font-normal text-gray-600">(*All Calculated in MYR)</span></h1>
                
                <div class="flex items-center space-x-4 bg-white/50 px-6 py-2 rounded-full shadow-sm mt-4 md:mt-0">
                    <span class="font-bold text-gray-600">Car Selected :</span>
                    <span class="font-bold text-black text-lg">{{ $vehicle->model }} <br> <span class="text-sm">{{ $vehicle->type }}</span></span>
                    <div class="h-8 w-px bg-gray-400 mx-2"></div>
                    <span class="font-bold text-gray-800">{{ $vehicle->plate_no }}</span>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                <div class="lg:col-span-5 space-y-6">
                    
                    <div class="bg-[#f2bfa0] rounded-3xl p-6 shadow-md border-2 border-white/30">
                        <div class="space-y-3 font-bold text-gray-900 text-lg">
                            <div class="flex justify-between">
                                <span>Rental Price</span>
                                <span>: {{ number_format($vehicle->price_per_day, 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Rental Time within (days)</span>
                                <span>: 1</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Weekday Discount</span>
                                <span>: - 0.00</span>
                            </div>
                            <div class="flex justify-between border-b-2 border-black/50 pb-3">
                                <span>Voucher Discount</span>
                                <span>: - 0.00</span>
                            </div>
                            <div class="flex justify-between pt-1">
                                <span>Total Amount</span>
                                <span>: {{ number_format($vehicle->price_per_day, 2) }}</span>
                            </div>
                            <div class="flex justify-between border-b-2 border-black/50 pb-3">
                                <span>Deposit</span>
                                <span>: {{ number_format($vehicle->base_deposit, 2) }}</span>
                            </div>
                            <div class="flex justify-between pt-1 text-xl">
                                <span>Grand Total</span>
                                <span>: {{ number_format($total, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-[#f2bfa0] rounded-3xl p-6 shadow-md border-2 border-white/30">
                        <h3 class="text-xl font-bold mb-4">Car view:</h3>
                        <div class="flex space-x-4 mb-6">
                            <button class="flex-1 bg-[#ff7f50] text-white font-bold py-2 rounded-full border border-orange-600 shadow-sm">Interior</button>
                            <button class="flex-1 bg-[#ff7f50] text-white font-bold py-2 rounded-full border border-orange-600 shadow-sm opacity-80">Exterior</button>
                        </div>

                        <h3 class="text-xl font-bold mb-2">Pickup & Return:</h3>
                        <div class="relative pt-6 pb-2 px-2">
                            <div class="absolute top-1/2 left-2 right-2 h-0.5 bg-black/40 border-t-2 border-dashed border-black/60"></div>
                            <div class="flex justify-between relative z-10">
                                <div class="text-center">
                                    <i class="fas fa-dot-circle text-2xl mb-1 text-black"></i>
                                    <div class="bg-[#e84e1b] text-white text-[10px] leading-tight px-3 py-1 rounded-full font-bold">
                                        Pickup Time,<br>Pickup Date
                                    </div>
                                    <p class="text-xs font-bold mt-1">Pickup Location</p>
                                </div>
                                <div class="text-center">
                                    <i class="fas fa-map-marker-alt text-2xl mb-1 text-black"></i>
                                    <div class="bg-[#e84e1b] text-white text-[10px] leading-tight px-3 py-1 rounded-full font-bold">
                                        Return Time,<br>Return Date
                                    </div>
                                    <p class="text-xs font-bold mt-1">Return Location</p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center space-x-2 text-gray-800 text-sm">
                            <i class="far fa-clock text-lg"></i>
                            <span class="font-bold">1 Days 0 Hours</span>
                        </div>
                    </div>

                </div>

                <div class="lg:col-span-7 space-y-6">
                    
                    <div class="bg-[#f2bfa0] rounded-3xl p-8 shadow-md border-2 border-white/30 flex flex-col items-center justify-center min-h-[300px]">
                        <div class="bg-white p-4 rounded-xl shadow-lg mb-6">
                             <i class="fas fa-qrcode text-9xl text-black"></i>
                             <p class="text-center text-xs mt-1 text-gray-500">(QR Sample)</p>
                        </div>
                        <h2 class="text-xl md:text-2xl font-bold text-gray-900 text-center">
                            Bank Account Number Hasta : <span class="tracking-widest">...............</span>
                        </h2>
                    </div>

                    <div class="bg-[#f2bfa0] rounded-3xl p-8 shadow-md border-2 border-white/30 grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
                        
                        <div class="flex flex-col items-center">
                            <label class="w-full h-40 flex flex-col items-center justify-center bg-[#ff8c5a] rounded-xl border-2 border-orange-600/30 cursor-pointer hover:bg-[#ff7f50] transition shadow-inner">
                                <i class="fas fa-upload text-4xl text-black mb-2"></i>
                                <span class="font-bold text-black text-lg">Upload</span>
                                <input type="file" class="hidden">
                            </label>
                        </div>

                        <div class="text-center md:text-left space-y-4">
                            <p class="font-bold text-gray-900 leading-relaxed">
                                Please upload your receipt.<br>
                                &<br>
                                Wait for verification from staff.<br>
                                The action will be taken<br>
                                as soon as possible!!!
                            </p>
                            
                            <a href="{{ route('book.payment.submit', [
                                'id' => $vehicle->vehicle_id,
                                'pickup_location' => $pickupLoc, // FINAL PASS
                                'return_location' => $returnLoc, // FINAL PASS
                                'pickup_date' => $pickupDate,
                                'return_date' => $returnDate,
                                'total' => $total
                            ]) }}" ...>
                            Submit
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection