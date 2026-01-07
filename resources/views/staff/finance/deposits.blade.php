@extends('layouts.staff')

@section('content')
<div class="min-h-screen bg-slate-100 rounded-2xl p-6">
    <div class="max-w-7xl mx-auto">
        
        {{-- HEADER --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 animate-fade-in">
            <div>
                <h1 class="text-3xl font-black text-slate-900 tracking-tight">Deposit Management</h1>
                <p class="text-slate-500 text-sm font-medium mt-1">Track and process customer security deposits and refunds.</p>
            </div>
        </div>

        {{-- TABS --}}
        <div class="flex space-x-1 bg-white p-1.5 rounded-full shadow-sm border border-gray-100 mb-6 w-full md:w-fit animate-fade-in">
            @foreach(['requested' => 'Refund Requests', 'refunded' => 'Refunded History'] as $key => $label)
                <a href="{{ route('staff.finance.deposits', ['status' => $key]) }}" 
                   class="px-6 py-2.5 rounded-full text-xs font-bold uppercase tracking-wide transition-all flex items-center gap-2
                   {{ $status === $key 
                       ? 'bg-slate-900 text-white shadow-md' 
                       : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800' }}">
                    
                    {{ $label }}
                    
                    @if(isset($counts[$key]) && $counts[$key] > 0)
                        <span class="ml-2 text-[10px] px-1.5 py-0.5 rounded-full {{ $status === $key ? 'bg-white text-slate-900' : 'bg-slate-200 text-slate-600' }}">
                            {{ $counts[$key] }}
                        </span>
                    @endif
                </a>
            @endforeach
        </div>

        {{-- TABLE CARD --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden animate-fade-in">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-gray-100 text-[10px] uppercase text-slate-400 font-bold tracking-widest">
                            <th class="px-6 py-4">Booking Info</th>
                            <th class="px-6 py-4">Customer</th>
                            <th class="px-6 py-4">Refund Amount</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($bookings as $booking)
                        
                        {{-- CLICKABLE ROW --}}
                        <tr onclick="window.location='{{ route('staff.bookings.show', $booking->bookingID) }}'" 
                            class="hover:bg-slate-50/80 transition-colors group cursor-pointer">
                            
                            {{-- BOOKING INFO --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-orange-50 text-orange-600 border border-orange-100 flex items-center justify-center font-black text-xs shrink-0">
                                        #{{ $booking->bookingID }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-slate-900">{{ $booking->vehicle->model }}</div>
                                        <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">{{ $booking->vehicle->plateNo }}</div>
                                        
                                        @if($booking->bookingStatus == 'Cancelled' || $booking->bookingStatus == 'Rejected')
                                            <span class="inline-block mt-1 text-[9px] px-1.5 py-0.5 rounded bg-red-50 text-red-600 font-bold uppercase tracking-wide border border-red-100">
                                                {{ $booking->bookingStatus }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- CUSTOMER --}}
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-slate-800">{{ $booking->customer->fullName }}</div>
                                <div class="text-xs text-slate-400 font-medium">{{ $booking->customer->phoneNo }}</div>
                            </td>

                            {{-- DYNAMIC AMOUNT --}}
                            <td class="px-6 py-4">
                                @php
                                    // 1. Calculate Security Deposit Only
                                    $depositAmount = $booking->payments->sum('depoAmount');

                                    // 2. Calculate Total Amount Paid (excluding void/rejected)
                                    $totalPaid = $booking->payments
                                        ->whereNotIn('paymentStatus', ['Void', 'Rejected'])
                                        ->sum('amount');

                                    // 3. Logic: If Cancelled/Rejected => Full Refund potential. Else => Deposit only.
                                    $isFullRefund = in_array($booking->bookingStatus, ['Cancelled', 'Rejected']);
                                    
                                    // 4. Set Variables
                                    $finalAmount = $isFullRefund ? $totalPaid : $depositAmount;
                                    $label = $isFullRefund ? 'Full Refund (Total Paid)' : 'Security Deposit Only';

                                    // Status Logic
                                    $depositPayment = $booking->payments->where('depoAmount', '>', 0)->first();
                                    $currentDepoStatus = $depositPayment ? $depositPayment->depoStatus : 'Unknown';
                                @endphp

                                <div class="flex flex-col">
                                    <span class="text-sm font-black text-slate-900">RM {{ number_format($finalAmount, 2) }}</span>
                                    <span class="text-[9px] text-slate-400 font-bold uppercase tracking-wide mt-0.5">{{ $label }}</span>
                                </div>
                            </td>

                            {{-- STATUS --}}
                            <td class="px-6 py-4">
                                @php
                                    $statusClasses = match($currentDepoStatus) {
                                        'Requested' => 'bg-orange-50 text-orange-600 border-orange-100 animate-pulse',
                                        'Refunded'  => 'bg-green-50 text-green-600 border-green-100',
                                        'Pending'   => 'bg-yellow-50 text-yellow-600 border-yellow-100',
                                        'Forfeited', 'Void' => 'bg-slate-800 text-white border-slate-900',
                                        default     => 'bg-gray-100 text-gray-500 border-gray-200'
                                    };
                                @endphp
                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-widest border {{ $statusClasses }}">
                                    {{ $currentDepoStatus }}
                                </span>
                            </td>

                            {{-- ACTIONS --}}
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if($status === 'requested')
                                        {{-- REFUND BUTTON --}}
                                        <button type="button"
                                            onclick="event.stopPropagation(); openRefundModal('{{ route('staff.finance.refund', $booking->bookingID) }}', '{{ number_format($finalAmount, 2) }}')"
                                            class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wide transition shadow-lg shadow-green-500/20 flex items-center gap-2 transform hover:-translate-y-0.5">
                                            <i class="fas fa-check-circle"></i> Approve
                                        </button>
                                    @else
                                        <span class="w-8 h-8 rounded-full bg-slate-50 flex items-center justify-center text-slate-300">
                                            <i class="fas fa-check"></i>
                                        </span>
                                    @endif
                                </div>
                            </td>

                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-20 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                                        <i class="fas fa-inbox text-slate-300 text-2xl"></i>
                                    </div>
                                    <p class="text-slate-500 text-sm font-medium">No deposits found in this category.</p>
                                </div>
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

{{-- REFUND MODAL --}}
<div id="refund-modal" class="fixed inset-0 z-50 hidden bg-slate-900/60 flex items-center justify-center p-4 backdrop-blur-sm transition-opacity duration-300">
    <div class="bg-white rounded-[2rem] w-full max-w-md p-8 shadow-2xl border border-gray-100 transform transition-all scale-100">
        
        <div class="flex justify-between items-center mb-6">
            <h3 class="font-black text-slate-900 text-xl flex items-center gap-2">
                <i class="fas fa-hand-holding-usd text-green-500"></i> Approve Refund
            </h3>
            <button type="button" onclick="closeRefundModal()" class="w-8 h-8 rounded-full bg-slate-50 text-slate-400 hover:bg-slate-100 hover:text-slate-600 flex items-center justify-center transition">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form id="refund-form" action="" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="bg-green-50 border border-green-100 rounded-xl p-5 mb-6 flex flex-col items-center text-center">
                <p class="text-[10px] text-green-600 font-bold uppercase tracking-widest mb-1">Total Refund Amount</p>
                <p class="text-3xl font-black text-green-700 tracking-tight" id="refund-modal-amount">RM 0.00</p>
            </div>

            <div class="mb-6">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Remarks / Reference No.</label>
                <textarea name="remarks" rows="3" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-4 text-sm font-bold text-slate-800 focus:bg-white focus:border-green-500 focus:ring-4 focus:ring-green-500/10 outline-none transition-all placeholder-slate-400" placeholder="e.g. Transferred via Maybank (Ref: 123456)..."></textarea>
            </div>

            <button type="submit" class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-green-500/30 transition transform active:scale-[0.98] uppercase tracking-wide text-xs">
                Confirm Refund
            </button>
        </form>
    </div>
</div>

<script>
    function openRefundModal(actionUrl, amount) {
        const modal = document.getElementById('refund-modal');
        document.getElementById('refund-form').action = actionUrl;
        document.getElementById('refund-modal-amount').innerText = 'RM ' + amount;
        
        modal.classList.remove('hidden');
        // Small animation delay for smooth entrance if desired, or relying on CSS transitions
    }

    function closeRefundModal() {
        document.getElementById('refund-modal').classList.add('hidden');
    }
</script>

<style>
    @keyframes fade-in { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .animate-fade-in { animation: fade-in 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
</style>

@endsection