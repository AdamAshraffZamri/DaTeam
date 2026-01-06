@extends('layouts.staff')

@section('content')
<div class="min-h-screen bg-gray-100 rounded-2xl p-6">
    <div class="max-w-7xl mx-auto">
        
        <div class="mb-8">
            <h1 class="text-3xl font-black text-gray-900">Deposit Management</h1>
            <p class="text-gray-500 mt-1 text-sm">Track and process customer security deposits and refunds.</p>
        </div>

        {{-- TABS --}}
        <div class="flex space-x-1 bg-white p-1 rounded-xl shadow-sm border border-gray-100 mb-6 w-full md:w-fit">
            @foreach(['requested' => 'Refund Requests', 'refunded' => 'Refunded History'] as $key => $label)
                <a href="{{ route('staff.finance.deposits', ['status' => $key]) }}" 
                   class="px-6 py-2.5 rounded-lg text-sm font-bold transition-all flex items-center gap-2
                   {{ $status === $key ? 'bg-orange-50 text-orange-600 shadow-sm ring-1 ring-orange-100' : 'text-gray-500 hover:bg-gray-50' }}">
                   
                   {{ $label }}
                   
                   @if(isset($counts[$key]) && $counts[$key] > 0)
                       <span class="ml-2 text-[10px] px-1.5 py-0.5 rounded-full {{ $status === $key ? 'bg-orange-200 text-orange-700' : 'bg-gray-200 text-gray-600' }}">
                           {{ $counts[$key] }}
                       </span>
                   @endif
                </a>
            @endforeach
        </div>

        {{-- TABLE --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50 border-b border-gray-100 text-xs uppercase text-gray-400 font-bold tracking-wider">
                            <th class="px-6 py-4">Booking Info</th>
                            <th class="px-6 py-4">Customer</th>
                            <th class="px-6 py-4">Refund Amount</th> {{-- Header Updated --}}
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($bookings as $booking)
                        {{-- CLICKABLE ROW --}}
                        <tr onclick="window.location='{{ route('staff.bookings.show', $booking->bookingID) }}'" 
                            class="hover:bg-gray-50/50 transition-colors group cursor-pointer">
                            
                            {{-- BOOKING INFO --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-orange-50 text-orange-600 flex items-center justify-center font-black text-xs">
                                        #{{ $booking->bookingID }}
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-gray-900">{{ $booking->vehicle->model }}</div>
                                        <div class="text-[10px] text-gray-400 font-mono uppercase">{{ $booking->vehicle->plateNo }}</div>
                                        @if($booking->bookingStatus == 'Cancelled' || $booking->bookingStatus == 'Rejected')
                                            <span class="inline-block mt-1 text-[9px] px-1.5 rounded bg-red-100 text-red-600 font-bold uppercase">
                                                {{ $booking->bookingStatus }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- CUSTOMER --}}
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-gray-800">{{ $booking->customer->fullName }}</div>
                                <div class="text-xs text-gray-400">{{ $booking->customer->phoneNo }}</div>
                            </td>

                            {{-- [FIXED] DYNAMIC AMOUNT LOGIC --}}
                            <td class="px-6 py-4">
                                @php
                                    // 1. Calculate Security Deposit Only
                                    $depositAmount = $booking->payments->sum('depoAmount');

                                    // 2. Calculate Total Amount Paid (sum of all valid payments)
                                    // We exclude 'Void' or 'Rejected' payments just in case
                                    $totalPaid = $booking->payments
                                        ->whereNotIn('paymentStatus', ['Void', 'Rejected'])
                                        ->sum('amount');

                                    // 3. Logic: If Booking is Cancelled/Rejected, they likely get a FULL Refund.
                                    // Otherwise (Completed/Active), they get only the Deposit.
                                    $isFullRefund = in_array($booking->bookingStatus, ['Cancelled', 'Rejected']);
                                    
                                    // 4. Set Final Variables
                                    $finalAmount = $isFullRefund ? $totalPaid : $depositAmount;
                                    $label = $isFullRefund ? 'Full Refund (Total Paid)' : 'Security Deposit Only';

                                    // Status Display Logic (Keep existing)
                                    $depositPayment = $booking->payments->where('depoAmount', '>', 0)->first();
                                    $currentDepoStatus = $depositPayment ? $depositPayment->depoStatus : 'Unknown';
                                @endphp

                                <span class="text-sm font-black text-gray-900">RM {{ number_format($finalAmount, 2) }}</span>
                                <span class="block text-[9px] text-gray-400 font-bold uppercase tracking-wide mt-0.5">
                                    {{ $label }}
                                </span>
                            </td>

                            {{-- STATUS --}}
                            <td class="px-6 py-4">
                                @php
                                    $depoColor = match($currentDepoStatus) {
                                        'Requested' => 'bg-red-100 text-red-700',
                                        'Refunded'  => 'bg-green-100 text-green-700',
                                        'Pending'   => 'bg-yellow-100 text-yellow-700',
                                        'Forfeited', 'Void' => 'bg-gray-800 text-white',
                                        default     => 'bg-gray-100 text-gray-600'
                                    };
                                @endphp
                                <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wide {{ $depoColor }}">
                                    {{ $currentDepoStatus }}
                                </span>
                            </td>

                            {{-- ACTIONS --}}
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    
                                    @if($status === 'requested')
                                        {{-- REFUND REQUESTS: Pass the Calculated Final Amount --}}
                                        <button type="button"
                                            onclick="event.stopPropagation(); openRefundModal('{{ route('staff.finance.refund', $booking->bookingID) }}', '{{ number_format($finalAmount, 2) }}')"
                                            class="bg-green-50 hover:bg-green-100 text-green-600 border border-green-200 px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-2">
                                            <i class="fas fa-check-circle"></i> Approve
                                        </button>

                                    @else
                                        <span class="text-xs text-gray-400 italic">No actions</span>
                                    @endif

                                </div>
                            </td>

                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-400 text-sm">
                                No deposits found in this category.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{-- PAGINATION --}}
            @if($bookings->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $bookings->appends(['status' => $status])->links() }}
                </div>
            @endif
        </div>

    </div>
</div>

{{-- 1. REFUND MODAL --}}
<div id="refund-modal" class="fixed inset-0 z-50 hidden bg-black/80 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-2xl w-full max-w-md p-6 shadow-2xl border border-gray-100 transform transition-all">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-bold text-gray-900 text-lg flex items-center">
                <i class="fas fa-hand-holding-usd text-emerald-600 mr-2"></i> Approve Refund
            </h3>
            <button type="button" onclick="closeRefundModal()" class="text-gray-400 hover:text-gray-900 focus:outline-none">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form id="refund-form" action="" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-4 mb-4">
                <p class="text-xs text-emerald-800 font-bold mb-1">Processing Refund</p>
                <p class="text-xs text-emerald-700">Amount: <span id="refund-modal-amount" class="font-black">RM 0.00</span></p>
            </div>

            <div class="mb-4">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Remarks / Reference No.</label>
                <textarea name="remarks" rows="2" class="w-full border border-gray-300 rounded-lg p-3 text-sm outline-none focus:ring-1 focus:ring-emerald-500" placeholder="e.g. Returned to Maybank..."></textarea>
            </div>

            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 rounded-xl shadow-lg transition">Confirm Refund</button>
        </form>
    </div>
</div>

<script>
    // REFUND MODAL
    function openRefundModal(actionUrl, amount) {
        document.getElementById('refund-form').action = actionUrl;
        document.getElementById('refund-modal-amount').innerText = 'RM ' + amount;
        document.getElementById('refund-modal').classList.remove('hidden');
    }
    function closeRefundModal() {
        document.getElementById('refund-modal').classList.add('hidden');
    }
</script>

@endsection