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
        <a href="{{ url()->previous() }}" class="inline-flex items-center text-gray-400 hover:text-white mb-4 transition">
            <i class="fas fa-arrow-left mr-2"></i> Back to Vehicle Details
        </a>
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
                
                {{-- RENTAL ITINERARY --}}
                <div class="bg-white/5 backdrop-blur-md rounded-[2rem] p-6 border border-white/10 shadow-2xl">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-bold text-white flex items-center">
                            <i class="far fa-calendar-alt text-orange-500 mr-3"></i> Rental Itinerary
                        </h3>
                        
                        {{-- DYNAMIC DURATION BADGE --}}
                        <span class="bg-orange-500/20 text-orange-300 text-xs font-bold px-3 py-1 rounded-full border border-orange-500/30">
                            @if($days > 0 && $extraHours > 0)
                                {{ $days }} Day(s) + {{ $extraHours }} Hour(s)
                            @elseif($days > 0)
                                {{ $days }} Day(s)
                            @else
                                {{ $totalHours }} Hour(s)
                            @endif
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

                {{-- PAYMENT SUMMARY --}}
                <div class="bg-white/5 backdrop-blur-md rounded-[2rem] p-8 border border-white/10 shadow-2xl">
                    <h3 class="text-lg font-bold text-white mb-6 flex items-center">
                        <i class="fas fa-file-invoice-dollar text-orange-500 mr-3"></i> Payment Summary
                    </h3>
                    
                    <div class="space-y-4 text-sm">
                        <div class="flex justify-between items-center text-gray-300">
                            {{-- Detailed Breakdown of Calculation --}}
                            <span>
                                Rental Charges
                                <span class="block text-[10px] text-gray-500 mt-0.5">
                                    @if($days > 0) {{ $days }} Day(s) @endif
                                    @if($days > 0 && $extraHours > 0) + @endif
                                    @if($extraHours > 0) {{ $extraHours }} Hour(s) Tier @endif
                                </span>
                            </span>
                            
                            {{-- USE PRE-CALCULATED VARIABLE (No Blade Math) --}}
                            <span class="font-bold text-white">MYR {{ number_format($rentalCharge, 2) }}</span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400">Security Deposit <span class="text-[10px] text-gray-500 uppercase ml-1">(Refundable)</span></span>
                            <span class="font-bold text-white">MYR {{ number_format($vehicle->baseDepo, 2) }}</span>
                        </div>

                        <div id="discount_row" class="flex justify-between items-center hidden text-green-400">
                            <span class="font-bold">Voucher Discount</span>
                            <span class="font-bold">- MYR <span id="discount_amount">0.00</span></span>
                        </div>
                    </div>

                    {{-- PAYMENT OPTIONS --}}
                    <div class="mt-6 pt-6 border-t border-dashed border-white/10">
                        <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-3">Choose Payment Option</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Option 1: Full Payment --}}
                            <label class="cursor-pointer group relative">
                                <input type="radio" name="payment_choice" value="full" class="peer sr-only" checked onchange="updatePaymentMode('full')">
                                <div class="bg-white/5 border border-white/10 rounded-xl p-4 peer-checked:bg-orange-500/10 peer-checked:border-orange-500 transition h-full">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-sm font-bold text-white">Full Payment</span>
                                        <i class="fas fa-check-circle text-orange-500 opacity-0 peer-checked:opacity-100 transition"></i>
                                    </div>
                                    <p class="text-[10px] text-gray-400">Pay everything now.</p>
                                </div>
                            </label>

                            {{-- Option 2: Deposit Only --}}
                            <label class="cursor-pointer group relative">
                                <input type="radio" name="payment_choice" value="deposit" class="peer sr-only" onchange="updatePaymentMode('deposit')">
                                <div class="bg-white/5 border border-white/10 rounded-xl p-4 peer-checked:bg-orange-500/10 peer-checked:border-orange-500 transition h-full">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-sm font-bold text-white">Pay Deposit</span>
                                        <i class="fas fa-check-circle text-orange-500 opacity-0 peer-checked:opacity-100 transition"></i>
                                    </div>
                                    <p class="text-[10px] text-gray-400">Pay RM {{ number_format($vehicle->baseDepo, 0) }} now. Balance later.</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- VOUCHER INPUT --}}
                    <div class="mt-6 pt-4 border-t border-dashed border-white/10">
                         <label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-2 block">Have a Voucher?</label>
                         <div class="flex gap-2">
                             <input type="text" id="voucher_code" class="bg-white/5 border border-white/10 text-white text-sm rounded-lg px-4 py-3 w-full focus:outline-none focus:border-orange-500 transition uppercase font-bold" placeholder="Enter Code">
                             <button type="button" onclick="applyVoucher()" id="btn_apply_voucher" class="bg-gray-700 hover:bg-gray-600 text-white text-xs font-bold px-5 py-2 rounded-lg transition border border-white/10 whitespace-nowrap">
                                 APPLY
                             </button>
                         </div>
                         <p id="voucher_message" class="text-xs mt-2 font-medium"></p>
                    </div>

                    {{-- GRAND TOTAL DISPLAY --}}
                    <div class="mt-6 pt-6 border-t-2 border-dashed border-white/10 flex justify-between items-end">
                        <div>
                            <p class="text-xs font-bold text-gray-500 uppercase">Amount to Pay Now</p>
                            <p class="text-xs text-gray-600 font-medium">*All prices in MYR</p>
                        </div>
                        <p class="text-4xl font-black text-[#ea580c] tracking-tight">
                            <span class="text-lg font-bold align-top mt-2 inline-block">MYR</span> <span id="grand_total_display">{{ number_format($total, 2) }}</span>
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
                    
                    {{-- DYNAMIC HIDDEN FIELDS --}}
                    <input type="hidden" name="total" id="hidden_total" value="{{ $total }}">
                    <input type="hidden" name="payment_type" id="hidden_payment_type" value="full">
                    <input type="hidden" name="voucher_id" id="hidden_voucher_id" value="">

                    <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-[2rem] p-8 shadow-2xl text-center space-y-8">
                        
                        {{-- AGREEMENT SECTION --}}
                        <div class="bg-white/5 rounded-xl p-4 border border-white/10 text-left">
                            <h4 class="text-white font-bold flex items-center mb-2">
                                <i class="fas fa-file-signature text-orange-500 mr-2"></i> Rental Agreement
                            </h4>
                            <p class="text-xs text-gray-400 mb-4">Please download, sign, and upload agreement form.</p>
                            
                            <a href="{{ route('book.agreement.preview', [
                                'vehicle_id' => $vehicle->VehicleID, 
                                'pickup_location' => $pickupLoc,
                                'return_location' => $returnLoc,
                                'return_date' => $returnDate,
                                'return_time' => request('return_time', '10:00')
                            ]) }}" target="_blank" class="block w-full text-center py-2 rounded-lg border border-white/20 text-gray-300 text-xs font-bold hover:bg-white/5 transition mb-4">
                                <i class="fas fa-download mr-1"></i> Download Agreement PDF
                            </a>

                            <label class="block w-full h-24 border-2 border-dashed border-white/20 rounded-xl flex flex-col items-center justify-center cursor-pointer hover:border-orange-500 hover:bg-white/5 transition group">
                                <i class="fas fa-pen-nib text-gray-500 group-hover:text-orange-500 mb-1 transition"></i>
                                <span class="text-xs font-bold text-gray-400 group-hover:text-white">Upload Signed Form</span>
                                <input type="file" name="agreement_proof" class="hidden" required onchange="document.getElementById('agree-name').innerText = this.files[0].name">
                            </label>
                            <p id="agree-name" class="text-[10px] text-orange-400 mt-1 font-bold h-4"></p>
                        </div>

                        {{-- QR Section --}}
                        <div class="border-t border-white/10 pt-6">
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
                                <input type="file" id="proof_upload" name="payment_proof" class="hidden" required onchange="document.getElementById('file-name').innerText = 'Selected: ' + this.files[0].name">
                            </label>
                            <p id="file-name" class="text-xs text-orange-400 mt-2 font-bold"></p>
                        </div>
                                {{-- REMARKS FIELD --}}
                                    <div class="mb-6">
                                        <label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-2 block">
                                            Additional Requests (Optional)
                                        </label>
                                        <textarea name="remarks" rows="2" 
                                            class="bg-white/5 border border-white/10 text-white text-sm rounded-lg px-4 py-3 w-full focus:outline-none focus:border-orange-500 transition" 
                                            placeholder="e.g. Baby seat needed, picking up late..."></textarea>
                                    </div>

                                    <div class="bg-yellow-500/20 border border-yellow-500/50 rounded-xl p-4 mb-6 flex items-start gap-3">                                        <div>
                                            <p class="text-sm font-bold text-yellow-400">Notice</p>
                                            <p class="text-xs text-gray-300">
                                                Please complete your payment <span class="font-bold text-white">ASAP</span>. 
                                                Bookings are not secured until proof of payment is uploaded.
                                            </p>
                                        </div>
                                    </div>
                        <div class="space-y-4">
                            <button type="submit" class="block w-full bg-[#ea580c] hover:bg-orange-600 text-white font-bold py-4 rounded-xl shadow-xl transition-all transform hover:scale-[1.02] text-center text-lg">
                                Confirm & Submit
                            </button>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<script>
    // 1. STATE MANAGEMENT
    // Using simple values passed from controller - NO MATH here
    let fullTotal = {{ $total }}; 
    let depositAmount = {{ $vehicle->baseDepo }}; 
    let currentVoucherDiscount = 0; 
    let activeMode = 'full'; 

    // 2. TOGGLE PAYMENT MODE
    function updatePaymentMode(mode) {
        activeMode = mode;
        document.getElementById('hidden_payment_type').value = mode;
        renderTotal();
    }

    // 3. RENDER LOGIC
    function renderTotal() {
        let displayAmount = 0;
        let displayTotalElement = document.getElementById('grand_total_display');
        let hiddenTotalInput = document.getElementById('hidden_total');

        if (activeMode === 'full') {
            displayAmount = fullTotal - currentVoucherDiscount;
            if(displayAmount < 0) displayAmount = 0;
        } else {
            // Deposit Mode: Voucher does not apply to security deposit
            displayAmount = depositAmount; 
        }

        displayTotalElement.innerText = displayAmount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        hiddenTotalInput.value = displayAmount.toFixed(2);
    }

    // 4. APPLY VOUCHER
    function applyVoucher() {
        const code = document.getElementById('voucher_code').value;
        const btn = document.getElementById('btn_apply_voucher');
        const msg = document.getElementById('voucher_message');

        if(!code) {
            msg.innerText = "Please enter a code.";
            msg.className = "text-xs mt-2 font-bold text-red-500";
            return;
        }

        btn.innerText = "...";
        btn.disabled = true;
        msg.innerText = "";

        fetch("{{ route('voucher.apply') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({
                code: code,
                total_amount: fullTotal 
            })
        })
        .then(response => response.json())
        .then(data => {
            btn.innerText = "APPLY";
            btn.disabled = false;

            if (data.success) {
                msg.innerText = data.message;
                msg.className = "text-xs mt-2 font-bold text-green-400";
                
                currentVoucherDiscount = parseFloat(data.discount_amount.toString().replace(/,/g, ''));
                
                document.getElementById('discount_row').classList.remove('hidden');
                document.getElementById('discount_amount').innerText = data.discount_amount;
                document.getElementById('hidden_voucher_id').value = data.voucher_id;
                
                // Lock input after success
                document.getElementById('voucher_code').disabled = true;
                document.getElementById('voucher_code').classList.add('opacity-50', 'cursor-not-allowed');
                btn.classList.add('hidden');

                renderTotal();
            } else {
                msg.innerText = data.message;
                msg.className = "text-xs mt-2 font-bold text-red-500";
            }
        })
        .catch(error => {
            console.error('Error:', error);
            msg.innerText = "System error. Try again.";
            msg.className = "text-xs mt-2 font-bold text-red-500";
            btn.innerText = "APPLY";
            btn.disabled = false;
        });
    }
</script>
@endsection