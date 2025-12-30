@extends('layouts.app')

@section('content')
<div class="fixed inset-0 z-0">
    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('hastabg.png') }}');"></div>
    <div class="absolute inset-0 bg-black/70 backdrop-blur-[2px]"></div>
</div>

<div class="relative z-10 min-h-screen py-12">
    <div class="container mx-auto px-4 max-w-7xl">
        
        {{-- HEADER --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-12 gap-6">
            <div class="animate-fade-in">
                <h1 class="text-5xl md:text-6xl font-black text-white drop-shadow-[0_4px_4px_rgba(0,0,0,0.5)] tracking-tight">
                    Loyalty & Rewards
                </h1>
                <p class="text-xl text-gray-300 mt-2 font-medium">Earn points with every booking and unlock exclusive rewards.</p>
            </div>

            <div class="flex gap-4">
                <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-2xl px-6 py-4 text-center shadow-2xl">
                    <p class="text-gray-400 text-[10px] font-bold uppercase tracking-widest mb-1">CURRENT TIER</p>
                    <p class="text-orange-500 text-2xl font-black uppercase">{{ ucfirst($loyalty->tier ?? 'Bronze') }}</p>
                </div>
                <div class="bg-orange-600 border border-orange-400/50 rounded-2xl px-8 py-4 text-center shadow-lg shadow-orange-600/30 flex flex-col justify-center">
                    <p class="text-white text-2xl font-black leading-none">{{ number_format($loyalty->points ?? 0) }} PTS</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">
            {{-- STATUS CARD --}}
            <div class="bg-white/5 backdrop-blur-2xl rounded-[2.5rem] p-8 border border-white/10 shadow-2xl relative overflow-hidden group">
                <div class="absolute -top-24 -left-24 w-48 h-48 bg-orange-500/10 rounded-full blur-[80px]"></div>
                <div class="relative z-10">
                    <div class="flex justify-between items-center mb-8">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-orange-500/20 flex items-center justify-center border border-orange-500/30">
                                <i class="fas fa-crown text-orange-500 text-2xl"></i>
                            </div>
                            <h2 class="text-2xl font-black text-white">Loyalty Status</h2>
                        </div>
                        <span class="bg-orange-500/20 text-orange-400 px-4 py-1.5 rounded-full border border-orange-500/30 text-[10px] font-black uppercase tracking-widest">
                            {{ ucfirst($loyalty->tier ?? 'Bronze') }} Member
                        </span>
                    </div>

                    <div class="space-y-4">
                        <div class="bg-black/20 backdrop-blur-md rounded-2xl p-5 border border-white/5 hover:border-white/20 transition-all duration-300">
                            <div class="flex items-center gap-5">
                                <div class="w-10 h-10 rounded-xl bg-green-500/10 flex items-center justify-center text-green-400 border border-green-500/20">
                                    <i class="fas fa-arrow-up"></i>
                                </div>
                                <div>
                                    <p class="text-gray-400 text-[10px] font-bold uppercase tracking-widest">Points Earned</p>
                                    <p class="text-white text-3xl font-black tracking-tight">{{ number_format($loyalty->points_earned ?? 0) }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-black/20 backdrop-blur-md rounded-2xl p-5 border border-white/5 hover:border-white/20 transition-all duration-300">
                            <div class="flex items-center gap-5">
                                <div class="w-10 h-10 rounded-xl bg-red-500/10 flex items-center justify-center text-red-400 border border-red-500/20">
                                    <i class="fas fa-gift"></i>
                                </div>
                                <div>
                                    <p class="text-gray-400 text-[10px] font-bold uppercase tracking-widest">Points Redeemed</p>
                                    <p class="text-white text-3xl font-black tracking-tight">{{ number_format($loyalty->points_redeemed ?? 0) }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-orange-500/10 backdrop-blur-md rounded-2xl p-5 border border-orange-500/20">
                            <div class="flex items-center gap-5">
                                <div class="w-10 h-10 rounded-xl bg-orange-500/20 flex items-center justify-center text-orange-400 border border-orange-500/30">
                                    <i class="fas fa-wallet"></i>
                                </div>
                                <div>
                                    <p class="text-gray-400 text-[10px] font-bold uppercase tracking-widest">Total Points</p>
                                    <p class="text-white text-3xl font-black tracking-tight">{{ number_format($loyalty->points ?? 0) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- VOUCHERS CARD --}}
            <div class="bg-white/5 backdrop-blur-2xl rounded-[2.5rem] p-8 border border-white/10 shadow-2xl relative overflow-hidden">
                <div class="flex justify-between items-center mb-8">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-purple-500/20 flex items-center justify-center border border-purple-500/30">
                            <i class="fas fa-ticket-alt text-purple-400 text-2xl"></i>
                        </div>
                        <h2 class="text-2xl font-black text-white">Your Vouchers</h2>
                    </div>
                    <span class="bg-purple-600 text-white px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest">
                        {{ $vouchers->count() }} Active
                    </span>
                </div>

                <div class="bg-black/30 rounded-3xl p-6 border border-white/5 mb-6">
                    <div class="flex justify-between items-end mb-4">
                        <p class="text-gray-300 text-sm font-bold">Next Reward: <span class="text-orange-400">{{ $nextReward ?? 'Discount' }}</span></p>
                        <span class="text-white font-black text-xl">
                            {{ isset($bookingsNeeded) ? (3 - ($bookingsNeeded % 3)) . '/3' : '0/3' }}
                        </span>
                    </div>
                    <div class="w-full bg-white/10 rounded-full h-4 p-1">
                        <div class="bg-gradient-to-r from-orange-500 to-yellow-400 h-full rounded-full transition-all duration-1000" 
                             style="width: {{ $progressPercent ?? 0 }}%"></div>
                    </div>
                    <p class="text-gray-400 text-xs mt-4">
                        Book <span class="text-white font-bold">{{ $bookingsNeeded ?? 3 }}</span> more times to unlock your reward!
                    </p>
                </div>

                {{-- AUTO-ISSUED RENTAL DISCOUNT VOUCHERS (Below Progress Bar) --}}
                @php
                    $rentalVouchers = $vouchers->filter(function($v) {
                        return $v->voucherType === 'Rental Discount';
                    });
                @endphp
                @if($rentalVouchers->count() > 0)
                <div class="bg-gradient-to-br from-green-600/20 to-emerald-600/20 rounded-3xl p-6 border border-green-500/30 mb-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-8 h-8 rounded-lg bg-green-500/30 flex items-center justify-center border border-green-500/50">
                            <i class="fas fa-gift text-green-400 text-lg"></i>
                        </div>
                        <h3 class="text-white font-black">Earned Rental Discounts</h3>
                    </div>
                    <div class="space-y-3">
                        @foreach($rentalVouchers as $rv)
                        <div class="bg-black/20 rounded-2xl p-4 border border-green-500/20 flex justify-between items-center hover:border-green-500/50 transition-all">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-green-500/20 flex items-center justify-center border border-green-500/30">
                                    <i class="fas fa-car text-green-400 text-lg"></i>
                                </div>
                                <div>
                                    <p class="text-white font-bold">{{ $rv->discount_percent }}% OFF Rental</p>
                                    <p class="text-green-300 text-[10px] font-bold uppercase">{{ $rv->conditions }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-green-400 font-black text-sm">{{ $rv->code }}</p>
                                <p class="text-[9px] text-green-300 uppercase">Ready to Use</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <p class="text-green-300 text-xs mt-4 text-center">💡 Apply these vouchers at checkout when booking your next car</p>
                </div>
                @endif

                <div class="space-y-4 max-h-[220px] overflow-y-auto no-scrollbar">
                    @forelse($vouchers as $voucher)
                        <div class="bg-gradient-to-r from-purple-600/30 to-blue-600/30 backdrop-blur-md rounded-2xl p-5 border border-white/10 flex justify-between items-center">
                            <div class="flex items-center gap-4">
                                <i class="fas fa-tag text-purple-400 text-xl"></i>
                                <div>
                                    <p class="text-white font-black text-lg">{{ $voucher->code }}</p>
                                    <p class="text-white/60 text-xs">
                                        {{ $voucher->voucherType == 'Merchant Reward' ? 'Use at '.$voucher->redeem_place : $voucher->discount_percent.'% OFF Rental' }}
                                    </p>
                                </div>
                            </div>
                            <span class="text-[10px] font-bold text-white bg-white/10 px-3 py-1 rounded-lg border border-white/20 uppercase">Valid</span>
                        </div>
                    @empty
                        <div class="text-center py-10 opacity-30">
                            <i class="fas fa-ticket-alt text-4xl mb-2 text-white"></i>
                            <p class="text-white text-sm">No active vouchers yet.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- REWARDS GRID --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="bg-white/5 backdrop-blur-2xl rounded-[2.5rem] p-8 border border-white/10 shadow-2xl">
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-12 h-12 rounded-2xl bg-yellow-500/20 flex items-center justify-center border border-yellow-500/30">
                        <i class="fas fa-trophy text-yellow-500 text-2xl"></i>
                    </div>
                    <h2 class="text-2xl font-black text-white">Top Rankings</h2>
                </div>
                <div class="space-y-4">
                    @forelse($rankings->take(4) as $index => $ranking)
                        <div class="flex items-center gap-4 p-4 rounded-2xl border transition-all {{ $ranking->user_id == Auth::id() ? 'bg-orange-500/20 border-orange-500/50 shadow-[0_0_20px_rgba(234,88,12,0.15)]' : 'bg-white/5 border-white/5' }}">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center font-black text-lg {{ $index == 0 ? 'bg-yellow-500 text-black' : 'bg-white/10 text-white' }}">
                                {{ $index + 1 }}
                            </div>
                            <div class="flex-1">
                                <p class="text-white font-bold leading-none">{{ $ranking->customer->fullName ?? 'User' }}</p>
                                <p class="text-gray-500 text-[10px] font-bold uppercase tracking-widest mt-1">{{ $ranking->tier ?? 'Bronze' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-white font-black leading-none">{{ number_format($ranking->points) }}</p>
                                <p class="text-[9px] text-gray-500 font-bold uppercase mt-1">PTS</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-400">No rankings available.</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white/5 backdrop-blur-2xl rounded-[2.5rem] p-8 border border-white/10 shadow-2xl">
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-12 h-12 rounded-2xl bg-pink-500/20 flex items-center justify-center border border-pink-500/30">
                        <i class="fas fa-gift text-pink-500 text-2xl"></i>
                    </div>
                    <h2 class="text-2xl font-black text-white">Available Rewards</h2>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    @if(isset($rewards) && count($rewards) > 0)
                        @foreach($rewards as $reward)
                            <div onclick="redeemReward('{{ $reward['id'] }}', '{{ $reward['name'] }}', {{ $reward['points'] }})" 
                                 class="relative {{ $reward['color'] }} backdrop-blur-md border rounded-[1.5rem] p-6 text-center group cursor-pointer hover:scale-[1.03] transition-all">
                                <span class="absolute top-3 right-3 bg-white/10 text-white text-[9px] font-black px-2 py-1 rounded-md border border-white/10">
                                    {{ $reward['points'] }} PTS
                                </span>
                                <i class="fas {{ $reward['icon'] }} text-white text-2xl mb-3 opacity-60"></i>
                                <p class="text-white font-black text-sm tracking-tighter uppercase">{{ $reward['name'] }}</p>
                                <p class="text-white/70 text-[10px] mt-1">{{ $reward['offer'] }}</p>
                            </div>
                        @endforeach
                    @else
                        <p class="text-gray-400 col-span-2 text-center">Rewards loading...</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODALS --}}
<div id="redemptionModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/80 backdrop-blur-sm">
    <div class="bg-gray-900 border border-white/10 p-8 rounded-3xl max-w-sm w-full text-center relative shadow-2xl">
        <h3 class="text-2xl font-black text-white mb-2">Confirm Redemption?</h3>
        <p id="modalText" class="text-gray-400 text-sm mb-6"></p>
        <div class="flex gap-3 justify-center">
            <button onclick="closeModal()" class="px-6 py-2 rounded-xl bg-gray-700 text-white font-bold text-sm">Cancel</button>
            <button id="confirmBtn" class="px-6 py-2 rounded-xl bg-orange-600 text-white font-bold text-sm">Confirm</button>
        </div>
    </div>
</div>

<div id="successModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/90 backdrop-blur-md">
    <div class="bg-gradient-to-br from-gray-900 to-black border border-orange-500/30 p-8 rounded-3xl max-w-sm w-full text-center relative shadow-[0_0_50px_rgba(234,88,12,0.3)]">
        <div class="w-16 h-16 bg-green-500/20 rounded-full flex items-center justify-center mx-auto mb-4 border border-green-500/50">
            <i class="fas fa-check text-green-500 text-2xl"></i>
        </div>
        <h3 class="text-3xl font-black text-white mb-1">SUCCESS!</h3>
        <p class="text-gray-400 text-sm mb-6">You have claimed your voucher.</p>
        <div class="bg-white/10 p-4 rounded-xl border border-dashed border-white/30 mb-6">
            <p class="text-xs text-gray-500 uppercase tracking-widest font-bold mb-1">YOUR VOUCHER CODE</p>
            <p id="successCode" class="text-2xl font-mono font-black text-orange-500 tracking-wider">CODE123</p>
            <p id="voucherName" class="text-xs text-orange-400 mt-2 font-bold uppercase"></p>
        </div>
        <button onclick="location.reload()" class="w-full py-3 rounded-xl bg-white text-black font-black uppercase tracking-wide hover:bg-gray-200">Close & Refresh</button>
    </div>
</div>

<script>
function redeemReward(id, name, points) {
    document.getElementById('modalText').innerText = `Redeem ${name} voucher for ${points} points?`;
    document.getElementById('redemptionModal').classList.remove('hidden');

    document.getElementById('confirmBtn').onclick = function() {
        // Send redemption request
        fetch('{{ route("loyalty.redeem") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ reward_id: id })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            closeModal();
            if(data.success) {
                // Show success modal with the voucher code
                document.getElementById('successCode').innerText = data.code;
                document.getElementById('voucherName').innerText = data.voucher_name;
                document.getElementById('successModal').classList.remove('hidden');
            } else {
                alert(data.message || 'Failed to redeem voucher.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            closeModal();
            alert('Something went wrong. Check console for details.');
        });
    };
}

function closeModal() {
    document.getElementById('redemptionModal').classList.add('hidden');
}
</script>

<style scoped>
@keyframes fade-in { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.animate-fade-in { animation: fade-in 0.5s ease-out forwards; }
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endsection