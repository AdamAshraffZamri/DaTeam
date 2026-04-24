@extends('layouts.app')

@section('content')
<div class="fixed inset-0 z-0">
    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('hastabg.png') }}');"></div>
    <div class="absolute inset-0 bg-gradient-to-r from-black/70 via-black/45 to-black/75"></div>
</div>

<div class="relative z-10 min-h-[calc(100vh-64px)] py-12">
    <div class="container mx-auto px-4 max-w-5xl">

        <a href="{{ route('finance.index') }}" class="inline-flex items-center text-gray-400 hover:text-white mb-8 transition">
            <i class="fas fa-arrow-left mr-2"></i> Back to Payment
        </a>

        <div class="flex flex-col md:flex-row justify-between items-end mb-8 text-white">
            <div>
                <h1 class="text-3xl font-black drop-shadow-lg">Pay Penalty</h1>
                <p class="text-gray-400 mt-1">
                    @if($penalty->bookingID)
                        Booking #{{ $penalty->bookingID }} • Penalty Payment
                    @else
                        Customer Penalty Payment
                    @endif
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <div class="lg:col-span-7 space-y-6">
                <div class="bg-white/5 backdrop-blur-md rounded-[2.5rem] p-8 border border-white/10 shadow-2xl">
                    <h2 class="text-xl font-bold text-white mb-6 flex items-center">
                        <i class="fas fa-exclamation-circle text-red-500 mr-3"></i> Penalty Details
                    </h2>
                    <div class="space-y-4">
                        
                        @if($penalty->reason)
                        <div class="p-4 bg-white/5 rounded-2xl border border-white/5">
                            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Reason</p>
                            <p class="text-white font-medium">{{ $penalty->reason }}</p>
                        </div>
                        @endif

                        @php
                            // Calculate total: Use amount if available (customer-level), otherwise sum fees (booking-based)
                            $totalAmount = $penalty->amount ?? ($penalty->penaltyFees + $penalty->fuelSurcharge + $penalty->mileageSurcharge);
                        @endphp

                        @if($penalty->bookingID)
                            {{-- Booking-based penalty: Show breakdown --}}
                            <div class="flex justify-between items-center p-4 bg-white/5 rounded-2xl border border-white/5">
                                <div class="w-full">
                                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Fee Breakdown</p>
                                    <div class="grid grid-cols-2 gap-2 text-sm">
                                        <div class="text-gray-400">Late Fees:</div>
                                        <div class="text-white text-right">MYR {{ number_format($penalty->penaltyFees ?? 0, 2) }}</div>
                                        
                                        <div class="text-gray-400">Fuel Surcharge:</div>
                                        <div class="text-white text-right">MYR {{ number_format($penalty->fuelSurcharge ?? 0, 2) }}</div>
                                        
                                        <div class="text-gray-400">Mileage Surcharge:</div>
                                        <div class="text-white text-right">MYR {{ number_format($penalty->mileageSurcharge ?? 0, 2) }}</div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="flex justify-between items-center">
                            <p class="text-sm font-bold text-white uppercase tracking-wider">Total Penalty</p>
                            <p class="text-4xl font-black text-red-500">
                                <span class="text-lg font-bold align-top mt-2 inline-block">MYR</span> 
                                {{ number_format($totalAmount, 2) }}
                            </p>
                        </div>

                        @if($penalty->date_imposed)
                        <div class="text-xs text-gray-400 text-center">
                            <i class="fas fa-calendar mr-1"></i> Imposed on {{ \Carbon\Carbon::parse($penalty->date_imposed)->format('d M Y') }}
                        </div>
                        @endif
                    </div>
                </div>

                {{-- PENALTY PAYMENT HISTORY --}}
                @php
                    $penaltyPayments = \App\Models\Payment::where('bookingID', $penalty->bookingID)
                                                          ->where('paymentMethod', 'QR Transfer (Penalty)')
                                                          ->get();
                @endphp

                @if($penaltyPayments && count($penaltyPayments) > 0)
                <div class="bg-white/5 backdrop-blur-md rounded-[2.5rem] p-8 border border-white/10 shadow-2xl">
                    <h2 class="text-xl font-bold text-white mb-6 flex items-center">
                        <i class="fas fa-receipt text-blue-500 mr-3"></i> Payment Submissions
                    </h2>

                    <div class="space-y-4">
                        @foreach($penaltyPayments as $payment)
                        <div class="p-4 rounded-2xl border @if($payment->paymentStatus === 'Verified') bg-green-500/10 border-green-500/20 @elseif($payment->paymentStatus === 'Pending Verification') bg-yellow-500/10 border-yellow-500/20 @else bg-white/5 border-white/5 @endif">
                            
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Submission {{ $loop->iteration }}</p>
                                    <p class="text-white font-bold text-lg">MYR {{ number_format($payment->amount, 2) }}</p>
                                </div>
                                <span class="text-xs font-bold px-3 py-1 rounded-full @if($payment->paymentStatus === 'Verified') bg-green-500/20 text-green-400 @elseif($payment->paymentStatus === 'Pending Verification') bg-yellow-500/20 text-yellow-400 @else bg-gray-500/20 text-gray-400 @endif">
                                    {{ $payment->paymentStatus ?? 'Submitted' }}
                                </span>
                            </div>

                            <div class="text-xs text-gray-400 space-y-1 mb-3">
                                <div><i class="fas fa-calendar-alt mr-2"></i>{{ \Carbon\Carbon::parse($payment->transactionDate)->format('d M Y, h:i A') }}</div>
                                @if($payment->paymentStatus === 'Verified')
                                    <div class="text-green-400"><i class="fas fa-check-circle mr-2"></i> Confirmed by staff</div>
                                @elseif($payment->paymentStatus === 'Pending Verification')
                                    <div class="text-yellow-400"><i class="fas fa-hourglass-half mr-2"></i> Waiting for verification</div>
                                @endif
                            </div>

                            {{-- Receipt Preview --}}
                            @if($payment->installmentDetails)
                            <div class="mt-4">
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Receipt Preview</p>
                                <div class="bg-black/30 rounded-xl p-3 overflow-hidden">
                                    @php
                                        $ext = strtolower(pathinfo($payment->installmentDetails, PATHINFO_EXTENSION));
                                        $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                    @endphp
                                    @if($isImage)
                                        <img src="{{ Storage::disk('s3')->temporaryUrl($payment->installmentDetails, now()->addMinutes(60)) }}" alt="Payment Receipt" class="w-full h-auto rounded-lg max-h-48 object-cover">
                                    @else
                                        <a href="{{ Storage::disk('s3')->temporaryUrl($payment->installmentDetails, now()->addMinutes(60)) }}" target="_blank" class="flex items-center justify-center p-4 text-blue-400 hover:text-blue-300">
                                            <i class="fas fa-file-pdf text-2xl mr-2"></i> View Receipt (PDF)
                                        </a>
                                    @endif
                                </div>
                                <a href="{{ Storage::disk('s3')->temporaryUrl($payment->installmentDetails, now()->addMinutes(60)) }}" target="_blank" class="text-xs text-blue-400 hover:text-blue-300 mt-2 inline-flex items-center">
                                    <i class="fas fa-external-link-alt mr-1"></i> Open Full Receipt
                                </a>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <div class="lg:col-span-5">
                <form action="{{ route('finance.submit_fine', $penalty->penaltyID) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-[2.5rem] p-8 shadow-2xl text-center space-y-6">
                        <div class="bg-white rounded-2xl p-4 w-48 h-48 mx-auto shadow-lg flex items-center justify-center">
                            <img src="{{ asset('qr.JPG') }}" alt="QR Code" class="w-full h-full object-contain">
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Bank Account (HASTA TRAVEL & TOURS SDN. BHD.)</p>
                            <p class="text-xl font-mono font-bold text-white tracking-widest">551306541568(MAYBANK)</p>
                        </div>
                        <label class="block w-full h-32 border-2 border-dashed border-white/20 rounded-2xl flex flex-col items-center justify-center cursor-pointer hover:border-red-500 hover:bg-white/5 transition group bg-black/20">
                            <i class="fas fa-cloud-upload-alt text-2xl text-gray-400 group-hover:text-red-500 mb-2 transition"></i>
                            <span class="text-xs font-bold text-gray-300">Upload Receipt (jpg, jpeg, png only)</span>
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