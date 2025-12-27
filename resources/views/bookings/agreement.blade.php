@extends('layouts.app')

@section('content')
<div class="fixed inset-0 z-0">
    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('hastabg.png') }}');"></div>
    <div class="absolute inset-0 bg-gradient-to-b from-black/90 via-black/80 to-black/95"></div>
</div>

<div class="relative z-10 py-12">
    <div class="container mx-auto px-4 max-w-3xl">
        
        {{-- Back Button --}}
        <button onclick="history.back()" class="inline-flex items-center text-gray-400 hover:text-white mb-8 transition">
            <i class="fas fa-arrow-left mr-2"></i> Back
        </button>

        <div class="bg-white text-black rounded-[2px] shadow-2xl overflow-hidden relative">
            <div class="absolute inset-0 bg-gray-50 opacity-50 pointer-events-none"></div>

            <div class="relative p-12 space-y-8">
                {{-- Header --}}
                <div class="text-center border-b-2 border-black pb-8">
                    <h1 class="text-3xl font-serif font-bold uppercase tracking-widest mb-2">Rental Agreement</h1>
                    <p class="text-sm font-serif text-gray-600">
                        Agreement ID: #{{ $booking->bookingID == 'PENDING' ? 'DRAFT-PREVIEW' : 'AGR-'.$booking->bookingID }}
                    </p>
                </div>

                {{-- Parties --}}
                <div class="space-y-4 font-serif">
                    <p><strong>THIS AGREEMENT</strong> is made on <strong>{{ \Carbon\Carbon::parse($booking->aggreementDate)->format('d F Y') }}</strong> between:</p>
                    <div class="pl-6 border-l-4 border-gray-300 space-y-4">
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-bold">The Owner (Lessor)</p>
                            <p class="text-lg font-bold">HASTA CAR RENTAL SERVICES</p>
                            <p class="text-sm">UTM Johor Bahru, Malaysia</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-bold">The Renter (Lessee)</p>
                            <p class="text-lg font-bold">{{ $booking->customer->name }}</p>
                            <p class="text-sm">ID/Passport: {{ $booking->customer->ic_passport ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                {{-- Vehicle Details --}}
                <div class="font-serif">
                    <h3 class="text-lg font-bold uppercase border-b border-gray-300 pb-2 mb-4">1. Vehicle Details</h3>
                    <table class="w-full text-sm text-left">
                        <tr class="border-b border-gray-100"><td class="py-2 font-bold text-gray-600">Model</td><td class="py-2">{{ $booking->vehicle->model }}</td></tr>
                        <tr class="border-b border-gray-100"><td class="py-2 font-bold text-gray-600">Plate Number</td><td class="py-2">{{ $booking->vehicle->plateNo }}</td></tr>
                    </table>
                </div>

                {{-- Terms --}}
                <div class="font-serif text-sm space-y-4 text-justify">
                    <h3 class="text-lg font-bold uppercase border-b border-gray-300 pb-2 mb-4">2. Terms & Conditions</h3>
                    <p><strong>2.1 Usage:</strong> The Renter agrees to use the vehicle solely for personal use.</p>
                    <p><strong>2.2 Return Policy:</strong> Return to <strong>{{ $booking->returnLocation }}</strong> by <strong>{{ \Carbon\Carbon::parse($booking->returnDate)->format('d M Y') }}</strong> at <strong>{{ $booking->returnTime }}</strong>.</p>
                    <p><strong>2.3 Liability:</strong> The Renter is responsible for all fines and damages.</p>
                </div>

                {{-- Signatures --}}
                <div class="grid grid-cols-2 gap-12 pt-12 mt-12 border-t-2 border-black font-serif">
                    <div>
                        <p class="mb-8 font-bold">Signed by Lessor:</p>
                        <div class="h-16 flex items-end"><p class="font-bold text-xl">HASTA MANAGER</p></div>
                        <div class="border-t border-black pt-2"><p class="text-xs text-gray-500">Authorized Signature</p></div>
                    </div>
                    <div>
                        <p class="mb-8 font-bold">Signed by Lessee:</p>
                        {{-- IF PREVIEW, SHOW BLANK SPACE FOR SIGNATURE --}}
                        @if($booking->bookingID == 'PENDING')
                            <div class="h-16 flex items-end border-b border-dashed border-gray-400 mb-2"></div>
                            <p class="text-xs text-gray-400 text-center">(Sign Here)</p>
                        @else
                            <div class="h-16 flex items-end"><p class="font-script text-2xl text-blue-900">{{ $booking->customer->name }}</p></div>
                        @endif
                        <div class="border-t border-black pt-2">
                            <p class="text-sm font-bold">{{ strtoupper($booking->customer->name) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-8">
            <button onclick="window.print()" class="bg-white/10 hover:bg-white/20 text-white px-8 py-3 rounded-full font-bold transition">
                <i class="fas fa-print mr-2"></i> Print / Save as PDF
            </button>
        </div>
    </div>
</div>
@endsection