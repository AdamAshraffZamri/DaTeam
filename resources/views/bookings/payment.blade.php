@extends('layouts.app')

@section('content')
{{-- 1. FIXED BACKGROUND --}}
<div class="fixed inset-0 z-0">
    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('hastabg.png') }}');"></div>
    <div class="absolute inset-0 bg-gradient-to-r from-black/70 via-black/45 to-black/75"></div>
</div>

{{-- 2. SCROLLABLE CONTENT --}}
<div class="relative z-10 pb-20">
    
    <div class="container mx-auto px-4 py-12 max-w-6xl">
        
        {{-- HEADER SECTION --}}
        <a href="{{ url()->previous() }}" class="inline-flex items-center text-white hover:text-white mb-4 transition">
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
            
            {{-- ========================
                 LEFT COLUMN: INFO & COST
                 ======================== --}}
            <div class="lg:col-span-7 space-y-6">
                
                {{-- A. RENTAL ITINERARY CARD --}}
                <div class="bg-black/50 backdrop-blur-md rounded-[2rem] p-6 border border-white/10 shadow-2xl">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-bold text-white flex items-center">
                            <i class="far fa-calendar-alt text-orange-500 mr-3"></i> Rental Itinerary
                        </h3>
                        
                        {{-- Duration Badge --}}
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
                                <p class="text-xs font-bold text-white uppercase tracking-wide mb-1">Pickup</p>
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-bold text-white text-lg">{{ \Carbon\Carbon::parse($pickupDate)->format('d M Y') }}</p>
                                        <p class="text-sm font-medium text-gray-400">{{ $pickupLoc }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs font-bold text-white">Time</p>
                                        <p class="font-bold text-white">{{ request('pickup_time', '10:00') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Return --}}
                        <div class="relative">
                            <div class="absolute -left-[31px] top-1 bg-gray-900 border-4 border-red-500 w-6 h-6 rounded-full shadow-lg"></div>
                            <div>
                                <p class="text-xs font-bold text-white uppercase tracking-wide mb-1">Return</p>
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-bold text-white text-lg">{{ \Carbon\Carbon::parse($returnDate)->format('d M Y') }}</p>
                                        <p class="text-sm font-medium text-gray-400">{{ $returnLoc }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs font-bold text-white">Time</p>
                                        <p class="font-bold text-white">{{ request('return_time', '10:00') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- B. PAYMENT SUMMARY & VOUCHER CARD --}}
                <div class="bg-black/50 backdrop-blur-md rounded-[2.5rem] p-8 border border-white/10 shadow-2xl relative overflow-hidden">
                    {{-- Decorative Glow --}}
                    <div class="absolute top-0 right-0 w-64 h-64 bg-orange-500/10 rounded-full blur-3xl pointer-events-none -mr-32 -mt-32"></div>

                    <h3 class="text-xl font-black text-white mb-8 flex items-center tracking-tight">
                        <i class="fas fa-file-invoice-dollar text-orange-500 mr-4 text-2xl"></i> 
                        PAYMENT SUMMARY
                    </h3>
                    
                    {{-- 1. Cost Breakdown --}}
                    <div class="bg-black/20 rounded-2xl p-6 border border-white/5 mb-6">
                        <h4 class="text-[12px] text-white font-black uppercase tracking-[0.2em] mb-4">Cost Breakdown</h4>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-400">Rental Charges</span>
                                <span class="text-white font-bold">RM {{ number_format($rentalCharge, 2) }}</span>
                            </div>
                            <div class="flex justify-between items-center text-sm">
                                <div class="flex items-center text-gray-400">
                                    <span>Security Deposit</span>
                                    <span class="ml-2 text-[9px] bg-white/10 px-1.5 py-0.5 rounded text-gray-300">REFUNDABLE</span>
                                </div>
                                <span class="text-white font-bold">RM {{ number_format($vehicle->baseDepo, 2) }}</span>
                            </div>
                            {{-- Hidden Discount Row (Show via JS) --}}
                            <div id="discount_row" class="hidden flex justify-between items-center text-sm">
                                <span class="text-green-400 font-bold">Voucher Discount</span>
                                <span class="text-green-400 font-bold">- RM <span id="discount_amount">0.00</span></span>
                            </div>
                        </div>

                        <div class="mt-6 pt-4 border-t border-white/10 flex justify-between items-center">
                            <span class="text-xs font-black text-orange-500 uppercase tracking-widest">Total Cost</span>
                            <span class="text-2xl font-black text-white tracking-tighter">
                                RM <span id="total_booking_cost_display">{{ number_format($total, 2) }}</span>
                            </span> 
                        </div>
                    </div>

                    {{-- 2. Select Payment Mode --}}
                    <div class="space-y-4 mb-8">
                        <h4 class="text-[10px] text-white font-black uppercase tracking-[0.2em] mb-2">Select Payment Mode</h4>
                        
                        {{-- Full Payment --}}
                        <label class="group relative block cursor-pointer">
                            <input type="radio" name="payment_type" value="full" checked onchange="updatePaymentMode('full')" class="peer sr-only">
                            <div class="p-4 rounded-xl border border-white/10 bg-white/5 peer-checked:bg-orange-500/10 peer-checked:border-orange-500 transition-all duration-300 hover:bg-white/10">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 mt-1">
                                    </div>
                                    <div class="ml-4">
                                        <span class="block text-white font-bold text-sm group-hover:text-orange-400 transition-colors">Pay Full Amount</span>
                                        <span class="block text-xs text-gray-400 mt-1">
                                            Pay <span class="text-white">RM {{ number_format($total, 2) }}</span> now. No balance due.
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </label>

                        {{-- Deposit Only --}}
                        <label class="group relative block cursor-pointer">
                            <input type="radio" name="payment_type" value="deposit" onchange="updatePaymentMode('deposit')" class="peer sr-only">
                            <div class="p-4 rounded-xl border border-white/10 bg-white/5 peer-checked:bg-orange-500/10 peer-checked:border-orange-500 transition-all duration-300 hover:bg-white/10">
                                <div class="flex items-start">
                                    <div class="ml-4">
                                        <span class="block text-white font-bold text-sm group-hover:text-orange-400 transition-colors">Pay Deposit Only</span>
                                        <span class="block text-xs text-gray-400 mt-1">
                                            Pay <span class="text-white">RM {{ number_format($vehicle->baseDepo, 2) }}</span> now. 
                                            Balance <span class="text-orange-400">RM {{ number_format($total - $vehicle->baseDepo, 2) }}</span> due on pickup.
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </label>
                    </div>

                    {{-- 3. Voucher Input --}}
                    <div class="mt-6 pt-4 border-t border-dashed border-white/10">
                         <label class="text-[10px] font-bold text-white uppercase tracking-widest mb-2 block">Have a Voucher?</label>
                         <div class="relative flex gap-2">
                             <div class="relative flex-1">
                                 <input type="text" id="voucher_code" class="bg-white/5 border border-white/10 text-white text-sm rounded-lg px-4 py-3 w-full focus:outline-none focus:border-orange-500 transition uppercase font-bold" placeholder="Enter Code" autocomplete="off">
                                 
                                 {{-- VOUCHER DROPDOWN --}}
                                 <div id="voucherDropdown" class="hidden absolute top-full left-0 right-0 mt-1 bg-gray-800 border border-white/20 rounded-lg shadow-2xl z-50 max-h-64 overflow-y-auto">
                                     </div>
                             </div>
                             <button type="button" onclick="applyVoucher()" id="btn_apply_voucher" class="bg-gray-700 hover:bg-gray-600 text-white text-xs font-bold px-5 py-2 rounded-lg transition border border-white/10 whitespace-nowrap">
                                 APPLY
                             </button>
                         </div>
                         <p id="voucher_message" class="text-xs mt-2 font-medium"></p>
                    </div>

                    {{-- 4. Grand Total Display --}}
                    <div class="mt-6 pt-6 border-t-2 border-dashed border-white/10 flex justify-between items-end">
                        <div>
                            <p class="text-xs font-bold text-white uppercase">Amount to Pay Now</p>
                            <p class="text-xs text-white font-medium">*All prices in MYR</p>
                            <p id="balance_warning_ui" class="text-[10px] text-orange-400 font-bold mt-1 hidden">Balance to pay later: RM 0.00</p>
                        </div>
                        <p class="text-4xl font-black text-[#ea580c] tracking-tight">
                            <span class="text-lg font-bold align-top mt-2 inline-block">MYR</span> <span id="grand_total_display">{{ number_format($total, 2) }}</span>
                        </p>
                    </div>
                </div>
            </div>

            {{-- ==============================
                 RIGHT COLUMN: FORM & ACTION
                 ============================== --}}
            <div class="lg:col-span-5">
                <form action="{{ route('book.payment.submit', ['id' => $vehicle->VehicleID]) }}" method="POST" enctype="multipart/form-data">
                    @csrf 
                    {{-- Hidden Info Inputs --}}
                    <input type="hidden" name="pickup_location" value="{{ $pickupLoc }}">
                    <input type="hidden" name="return_location" value="{{ $returnLoc }}">
                    <input type="hidden" name="pickup_date" value="{{ $pickupDate }}">
                    <input type="hidden" name="return_date" value="{{ $returnDate }}">
                    <input type="hidden" name="pickup_time" value="{{ request('pickup_time', '10:00') }}">
                    <input type="hidden" name="return_time" value="{{ request('return_time', '10:00') }}">
                    
                    {{-- Dynamic Hidden Fields (JS Updates These) --}}
                    <input type="hidden" name="total" id="hidden_total" value="{{ $total }}">
                    <input type="hidden" name="payment_type" id="hidden_payment_type" value="full">
                    <input type="hidden" name="voucher_id" id="hidden_voucher_id" value="">

                    <div class="bg-black/50 backdrop-blur-xl border border-white/20 rounded-[2rem] p-8 shadow-2xl space-y-8">
                        
                        {{-- 1. AGREEMENT SECTION --}}
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
                                <span class="text-xs font-bold text-gray-400 group-hover:text-white">Upload Signed Form(pdf only)</span>
                                <input type="file" name="agreement_proof" class="hidden" required onchange="document.getElementById('agree-name').innerText = this.files[0].name">
                            </label>
                            <p id="agree-name" class="text-[10px] text-orange-400 mt-1 font-bold h-4 overflow-hidden"></p>
                        </div>

                        {{-- 2. QR & BANK SECTION --}}
                        <div class="border-t border-white/10 pt-6 text-center">
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

                        {{-- 3. UPLOAD PAYMENT PROOF --}}
                        <div>
                            <label for="proof_upload" class="block w-full h-36 border-2 border-dashed border-white/10 rounded-2xl flex flex-col items-center justify-center cursor-pointer hover:border-orange-500 hover:bg-white/5 transition group bg-white/5">
                                <div class="w-12 h-12 bg-white/10 rounded-full shadow-xl flex items-center justify-center mb-2 group-hover:scale-110 transition border border-white/10">
                                    <i class="fas fa-cloud-upload-alt text-orange-500 text-lg"></i>
                                </div>
                                <span class="text-sm font-bold text-gray-300 group-hover:text-white">Click to upload receipt(jpg, jpeg, png only)</span>
                                <input type="file" id="proof_upload" name="payment_proof" class="hidden" required onchange="document.getElementById('file-name').innerText = 'Selected: ' + this.files[0].name">
                            </label>
                            <p id="file-name" class="text-xs text-orange-400 mt-2 font-bold text-center h-4"></p>
                        </div>

                        {{-- 4. REMARKS FIELD --}}
                        <div class="mb-6">
                            <label class="text-[10px] font-bold text-white uppercase tracking-widest mb-2 block">
                                Additional Requests (Optional)
                            </label>
                            <textarea name="remarks" rows="2" 
                                class="bg-white/5 border border-white/10 text-white text-sm rounded-lg px-4 py-3 w-full focus:outline-none focus:border-orange-500 transition" 
                                placeholder="e.g. Baby seat needed, picking up late..."></textarea>
                        </div>

                        {{-- 5. NOTICE & SUBMIT --}}
                        <div class="bg-yellow-500/20 border border-yellow-500/50 rounded-xl p-4 mb-6 flex items-start gap-3">
                             <div>
                                 <p class="text-sm font-bold text-yellow-400">Notice</p>
                                 <p class="text-xs text-gray-300">
                                     Please complete your payment <span class="font-bold text-white">ASAP</span>. 
                                     Bookings are not secured until proof of payment is uploaded.
                                 </p>
                             </div>
                        </div>

                        <button type="submit" class="w-full bg-[#ea580c] hover:bg-orange-600 text-white py-4 rounded-xl font-black text-sm uppercase tracking-widest shadow-lg shadow-orange-500/20 transition-all transform hover:scale-[1.02] active:scale-95 flex items-center justify-center group">
                            <span>Submit</span>
                        </button>

                        <p class="text-center text-[10px] text-white flex items-center justify-center">
                            <i class="fas fa-lock text-gray-600 mr-1.5"></i> Secure SSL Encrypted Payment
                        </p>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<script>
    // 1. STATE MANAGEMENT
    let fullTotal = {{ $total }}; 
    let depositAmount = {{ $vehicle->baseDepo }}; 
    let currentVoucherDiscount = 0; 
    let activeMode = 'full'; 

    function updatePaymentMode(mode) {
        activeMode = mode;
        document.getElementById('hidden_payment_type').value = mode; 
        renderTotal();
    }

    function renderTotal() {
        let payNowAmount = 0;
        let balanceAmount = 0;

        // 1. Calculate Discounted Total
        let finalTotalCost = fullTotal - currentVoucherDiscount;
        if (finalTotalCost < 0) finalTotalCost = 0;

        // 2. Determine "Pay Now" vs "Balance"
        if (activeMode === 'full') {
            payNowAmount = finalTotalCost;
            balanceAmount = 0;
        } else {
            // Deposit Mode
            payNowAmount = depositAmount;
            
            // Safety: If discount makes total less than deposit, force full payment
            if(finalTotalCost < depositAmount) {
                 payNowAmount = finalTotalCost;
                 balanceAmount = 0;
            } else {
                 balanceAmount = finalTotalCost - depositAmount;
            }
        }

        // 3. Update Display Elements
        const displayString = formatMoney(payNowAmount);

        // Update the big orange number
        const grandTotalEl = document.getElementById('grand_total_display');
        if(grandTotalEl) grandTotalEl.innerText = displayString;
        
        // Update the Submit Button Text
        const btnDisplayEl = document.getElementById('payAmountDisplay');
        if(btnDisplayEl) btnDisplayEl.innerText = displayString;

        // Update Balance Warning Text
        const balanceUi = document.getElementById('balance_warning_ui');
        if(balanceUi) {
             if(balanceAmount > 0) {
                 balanceUi.innerText = `Balance to pay later: RM ${formatMoney(balanceAmount)}`;
                 balanceUi.classList.remove('hidden');
             } else {
                 balanceUi.classList.add('hidden');
             }
        }
        
        // Update Total Cost Summary Line
        const summaryTotalEl = document.getElementById('total_booking_cost_display');
        if(summaryTotalEl) {
            summaryTotalEl.innerText = formatMoney(finalTotalCost);
        }
    
        // 4. Update Hidden Input (Safe if backend recalculates, but good for form submission)
        document.getElementById('hidden_total').value = payNowAmount.toFixed(2);
    }

    // 2. HELPER: FORMAT MONEY
    function formatMoney(amount) {
        return amount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    // 3. APPLY VOUCHER
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
                
                // Update the missing variable here
                currentVoucherDiscount = parseFloat(data.discount_amount.toString().replace(/,/g, ''));
                
                document.getElementById('discount_row').classList.remove('hidden');
                document.getElementById('discount_amount').innerText = data.discount_amount;
                document.getElementById('hidden_voucher_id').value = data.voucher_id;
                
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

    // 4. VOUCHER DROPDOWN FUNCTIONALITY
    const voucherInput = document.getElementById('voucher_code');
    const voucherDropdown = document.getElementById('voucherDropdown');
    let allVouchers = [];

    fetch("{{ route('voucher.available') }}")
        .then(response => response.json())
        .then(data => {
            allVouchers = data;
        })
        .catch(error => {
            allVouchers = [];
        });

    voucherInput.addEventListener('focus', () => {
        if (allVouchers.length > 0) displaySuggestions(allVouchers);
        else showNoVouchersMessage();
    });

    voucherInput.addEventListener('input', (e) => {
        const value = e.target.value.toUpperCase();
        if (value.length === 0) {
            if (allVouchers.length > 0) displaySuggestions(allVouchers);
            else showNoVouchersMessage();
        } else {
            const filtered = allVouchers.filter(v => v.code.toUpperCase().includes(value));
            if (filtered.length > 0) displaySuggestions(filtered);
            else showNoVouchersMessage();
        }
    });

    function displaySuggestions(vouchers) {
        if (vouchers.length === 0) {
            showNoVouchersMessage();
            return;
        }
        voucherDropdown.innerHTML = vouchers.map(voucher => `
            <div class="p-3 hover:bg-white/10 cursor-pointer border-b border-white/5 last:border-b-0 transition"
                 onclick="selectVoucher('${voucher.code}')">
                <div class="flex justify-between items-center">
                    <div class="flex-1">
                        <p class="font-bold text-white text-sm">${voucher.code}</p>
                        <p class="text-xs text-gray-400 mt-1"><i class="fas fa-tag mr-1"></i> ${voucher.type}</p>
                    </div>
                    <div class="text-right">
                        ${voucher.discount_percent ? 
                            `<p class="font-bold text-orange-400 text-sm">${voucher.discount_percent}% OFF</p>` : 
                            `<p class="font-bold text-orange-400 text-sm">RM ${parseFloat(voucher.amount).toFixed(2)}</p>`
                        }
                        <p class="text-xs text-gray-500 mt-1">Exp: ${voucher.expires}</p>
                    </div>
                </div>
            </div>
        `).join('');
        voucherDropdown.classList.remove('hidden');
    }

    function showNoVouchersMessage() {
        voucherDropdown.innerHTML = `<div class="p-4 text-center"><p class="text-sm text-gray-400">No Available Vouchers</p></div>`;
        voucherDropdown.classList.remove('hidden');
    }

    function selectVoucher(code) {
        voucherInput.value = code;
        voucherDropdown.classList.add('hidden');
        applyVoucher();
    }

    document.addEventListener('click', (e) => {
        if (e.target !== voucherInput && !voucherDropdown.contains(e.target)) {
            voucherDropdown.classList.add('hidden');
        }
    });
</script>
@endsection