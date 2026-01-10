@extends('layouts.app')

@section('content')
{{-- 1. FIXED BACKGROUND --}}
<div class="fixed inset-0 z-0">
    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('hastabg.png') }}');"></div>
    <div class="absolute inset-0 bg-gradient-to-r from-black/70 via-black/45 to-black/75"></div>
</div>

{{-- 2. MAIN CONTENT CONTAINER --}}
<div class="relative z-10 min-h-screen py-12">
    <div class="container mx-auto px-4 max-w-7xl">
        
        {{-- HEADER SECTION --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-12 gap-6">
            <div class="animate-fade-in">
                <h1 class="text-4xl md:text-4xl font-black text-white drop-shadow-[0_4px_4px_rgba(0,0,0,0.5)] tracking-tight">
                    Loyalty & Rewards
                </h1>
                <p class="text text-gray-300 mt-2 font-medium">Earn points with every booking and unlock exclusive rewards.</p>
            </div>

            {{-- TOP STATS --}}
            <div class="flex gap-4">
                <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-2xl px-4 py-2 text-center shadow-2xl gold-glow-card transition-all duration-500">
                    <p class="text-gray-400 text-[10px] font-bold uppercase tracking-widest mb-1">CURRENT TIER</p>
                    <p class="text-orange-500 text-2xl font-black uppercase">{{ ucfirst($loyalty->tier ?? 'Bronze') }}</p>
                </div>
                <div class="bg-orange-600 border border-orange-400/50 rounded-2xl px-4 py-6 text-center shadow-lg shadow-orange-600/30 flex flex-col justify-center gold-glow-card transition-all duration-500">
                    <p class="text-white text-2xl font-black leading-none">{{ number_format($loyalty->points ?? 0) }} PTS</p>
                </div>
            </div>
        </div>

        {{-- MAIN GRID SYSTEM (2 COLUMNS) --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
            
            {{-- === LEFT COLUMN === --}}
            <div class="space-y-8">
                
                {{-- 1. LOYALTY STATUS CARD (Fixed Height: h-[480px]) --}}
                {{-- Added: gold-glow-card class --}}
                <div class="w-full h-[480px] bg-black/50 backdrop-blur-2xl rounded-3xl p-6 border border-white/10 shadow-2xl relative overflow-hidden group flex flex-col justify-between gold-glow-card transition-all duration-500">
                    <div class="absolute -top-24 -left-24 w-48 h-48 bg-orange-500/10 rounded-full blur-[80px]"></div>
                    
                    {{-- Header --}}
                    <div class="relative z-10">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-12 h-12 rounded-2xl bg-orange-500/20 flex items-center justify-center border border-orange-500/30">
                                <i class="fas fa-crown text-orange-500 text-2xl"></i>
                            </div>
                            <h2 class="text-2xl font-black text-white">Loyalty Status</h2>
                        </div>
                    </div>

                    {{-- Stats Content (Distributed vertically) --}}
                    <div class="space-y-3 relative z-10 flex-1 flex flex-col justify-center">
                        {{-- Points Earned --}}
                        <div class="bg-black/20 backdrop-blur-md rounded-2xl p-4 border border-white/5 hover:border-white/20 transition-all duration-300">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-green-500/10 flex items-center justify-center text-green-400 border border-green-500/20">
                                    <i class="fas fa-arrow-up"></i>
                                </div>
                                <div>
                                    <p class="text-gray-400 text-[10px] font-bold uppercase tracking-widest">Points Earned</p>
                                    <p class="text-white text-2xl font-black tracking-tight">{{ number_format($pointsEarned) }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Points Redeemed --}}
                        <div class="bg-black/20 backdrop-blur-md rounded-2xl p-4 border border-white/5 hover:border-white/20 transition-all duration-300">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-red-500/10 flex items-center justify-center text-red-400 border border-red-500/20">
                                    <i class="fas fa-gift"></i>
                                </div>
                                <div>
                                    <p class="text-gray-400 text-[10px] font-bold uppercase tracking-widest">Points Redeemed</p>
                                    <p class="text-white text-2xl font-black tracking-tight">{{ number_format($pointsRedeemed) }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Total Points --}}
                        <div class="bg-orange-500/10 backdrop-blur-md rounded-2xl p-4 border border-orange-500/20">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-orange-500/20 flex items-center justify-center text-orange-400 border border-orange-500/30">
                                    <i class="fas fa-wallet"></i>
                                </div>
                                <div>
                                    <p class="text-gray-400 text-[10px] font-bold uppercase tracking-widest">Total Points</p>
                                    <p class="text-white text-2xl font-black tracking-tight">{{ number_format($loyalty->points ?? 0) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 2. AVAILABLE REWARDS (Fixed Height: h-[550px]) --}}
                {{-- Added: gold-glow-card class --}}
                <div class="w-full h-[550px] bg-black/50 backdrop-blur-2xl rounded-3xl p-6 border border-white/10 shadow-2xl flex flex-col gold-glow-card transition-all duration-500">
                    {{-- Header --}}
                    <div class="flex items-center gap-4 mb-6 shrink-0">
                        <div class="w-10 h-10 rounded-2xl bg-pink-500/20 flex items-center justify-center border border-pink-500/30">
                            <i class="fas fa-gift text-pink-500 text-2xl"></i>
                        </div>
                        
                        <h2 class="text-2xl font-black text-white tracking-tight leading-none">Available Rewards</h2>
                    </div>

                    {{-- Grid Content (UPDATED WITH DB LOOP) --}}
                    <div class="grid grid-cols-2 gap-4 h-full overflow-y-auto custom-scrollbar pr-1">
                        @foreach($rewards as $reward)
                        @if($reward->name == 'HASTAHD' || $reward->category == 'Milestone') 
                        @continue 
                    @endif
                            <div onclick="redeemReward('{{ $reward->id }}', '{{ $reward->name }}', {{ $reward->points_required }})" 
                                 class="relative {{ $reward->color_class }} backdrop-blur-md border rounded-[1.5rem] p-5 text-center group cursor-pointer hover:scale-[1.02] transition-all duration-300 hover:shadow-lg flex flex-col justify-center items-center h-full min-h-[180px] gold-glow-card">

                                <span class="absolute top-2 right-2 bg-black/40 text-white text-[9px] font-black px-2 py-1 rounded-md border border-white/10 backdrop-blur-sm">
                                    {{ $reward->points_required }} PTS
                                </span>

                                <div class="h-12 flex items-center justify-center mb-3">
                                    <i class="fas {{ $reward->icon_class }} text-white text-3xl opacity-80 group-hover:scale-110 transition-transform"></i>
                                </div>

                                <p class="text-white font-black text-sm tracking-tighter uppercase leading-tight mb-2">{{ $reward->name }}</p>
                                <p class="text-white/60 text-[10px] bg-black/20 px-2 py-1 rounded-lg">{{ $reward->offer_description }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
            
            {{-- === RIGHT COLUMN === --}}
            <div class="space-y-8">

            
                {{-- LOYALTY ROAD CARD (FLIP INTERACTIVE) --}}
                {{-- Container Utama (Fixed Height) --}}
                {{-- Added: gold-glow-card to the wrapper --}}
                <div class="w-full h-[480px] group perspective-1000 relative mx-auto my-2 cursor-pointer gold-glow-card rounded-3xl transition-all duration-500" onclick="toggleFlip(this)">
                    
                    {{-- INNER FLIPPER --}}
                    <div class="relative w-full h-full transition-all duration-700 transform style-preserve-3d shadow-2xl rounded-3xl" id="flipperBox">
                        
                        {{-- =============================================== --}}
                        {{-- MUKA DEPAN (PROGRESS BAR)                       --}}
                        {{-- =============================================== --}}
                        <div class="absolute inset-0 w-full h-full bg-black/50 backdrop-blur-2xl rounded-3xl p-6 border border-white/10 flex flex-col justify-between backface-hidden">
                            
                            {{-- Header --}}
                            <div class="flex justify-between items-center mb-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl bg-purple-500/20 flex items-center justify-center border border-purple-500/30">
                                        <i class="fas fa-road text-purple-400 text-2xl"></i>
                                    </div>
                                    <div>
                                        <h2 class="text-2xl font-black text-white">Loyalty Road</h2>
                                        <p class="text-xs text-gray-400">VALID FOR MORE THAN 9 HOURS BOOKINGS ONLY</p>
                                    </div>
                                </div>
                                <span class="bg-purple-600 text-white px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest">
                                    Level {{ floor(($qualifiedBookingsCount ?? 0) / 12) + 1 }}
                                </span>
                            </div>

                            {{-- Progress Content Wrapper --}}
                            <div class="w-full bg-black/30 rounded-3xl p-8 border border-white/5 flex flex-col justify-center flex-1 mx-auto my-2">
                                
                                {{-- Stats Header --}}
                                <div class="flex justify-between items-end mb-6">
                                    <div>
                                        <p class="text-gray-300 text-sm font-bold">Next Reward:</p>
                                        <p class="text-transparent bg-clip-text bg-gradient-to-r from-orange-400 to-yellow-300 font-black text-2xl">
                                            {{ $nextReward ?? '20% OFF' }}
                                        </p>
                                    </div>
                                    
                                    <div class="text-right">
                                        <span class="text-white font-black text-3xl tracking-tighter">
                                            {{ $currentInCycle ?? 0 }}<span class="text-gray-500 text-xl">/12</span>
                                        </span>
                                    </div>
                                </div>

                                {{-- PROGRESS BAR (Alignment Fixed) --}}
                                <div class="relative w-full h-5 mb-8">
                                    {{-- Track --}}
                                    <div class="absolute top-0 left-0 w-full h-full bg-white/10 rounded-full"></div>

                                    {{-- Fill --}}
                                    <div class="absolute top-0 left-0 h-full bg-gradient-to-r from-orange-500 via-red-500 to-purple-500 rounded-full transition-all duration-1000 shadow-[0_0_15px_rgba(249,115,22,0.5)]" 
                                        style="width: {{ $progressPercent ?? 0 }}%">
                                    </div>

                                    {{-- Dots & Labels --}}
                                    @foreach([3, 6, 9, 12] as $step)
                                        @php 
                                            $pos = ($step / 12) * 100;
                                            $reached = ($currentInCycle ?? 0) >= $step;
                                        @endphp

                                        {{-- Dot --}}
                                        <div class="absolute top-1/2 left-[{{$pos}}%] -translate-x-1/2 -translate-y-1/2 w-3 h-3 rounded-full shadow-lg border-2 z-10 transition-all {{ $reached ? 'border-orange-400 bg-orange-400 scale-125' : 'border-gray-600 bg-gray-700' }}"></div>

                                        {{-- Label --}}
                                        <div class="absolute top-7 left-[{{$pos}}%] -translate-x-1/2 text-center w-8">
                                            <span class="text-[10px] font-bold uppercase tracking-wide {{ $reached ? 'text-white' : 'text-gray-500' }}">
                                                {{ $step }}x
                                            </span>
                                        </div>
                                    @endforeach
                                </div>

                                {{-- Instruction --}}
                                <p class="text-gray-400 text-xs mt-2 text-center">
                                    Book <span class="text-white font-bold">{{ $bookingsNeeded ?? 3 }}</span> more times (>9hrs) to unlock <span class="text-orange-400">{{ $nextReward }}</span>!
                                </p>
                                
                                {{-- Flip Hint --}}
                                <div class="mt-4 flex justify-center text-gray-500 text-[10px] uppercase font-bold tracking-widest animate-pulse">
                                    <i class="fas fa-sync-alt mr-2"></i> Tap to View Reward List
                                </div>
                            </div>

                            {{-- Footer T&C --}}
                            <div class="bg-purple-900/20 border border-purple-500/20 rounded-xl p-3 flex items-start gap-3 mt-2">
                                <i class="fas fa-info-circle text-purple-400 mt-0.5 text-sm"></i>
                                <p class="text-[11px] text-purple-200 leading-tight">
                                    <span class="font-bold text-white">Voucher T&C:</span> Rewards can only be redeemed for bookings made on <span class="font-bold text-yellow-300">Monday - Thursday</span>.
                                </p>
                            </div>
                        </div>

            
                        {{-- =============================================== --}}
                        {{-- MUKA BELAKANG (REWARD LIST - MILESTONE ONLY)    --}}
                        {{-- =============================================== --}}
                        <div class="absolute inset-0 w-full h-full bg-gray-900 rounded-3xl p-8 border border-gray-700 flex flex-col backface-hidden rotate-y-180">
                            
                            {{-- Header Belakang --}}
                            <div class="flex justify-between items-center mb-6 border-b border-gray-800 pb-4">
                                <div>
                                    <h3 class="text-white font-black text-xl">Milestone Rewards</h3>
                                    <p class="text-[10px] text-gray-500 uppercase tracking-widest">Unlock by collecting stamps</p>
                                </div>
                                <div class="w-8 h-8 rounded-full bg-gray-800 flex items-center justify-center text-gray-400">
                                    <i class="fas fa-trophy"></i>
                                </div>
                            </div>

                            <div class="flex-grow space-y-3 overflow-y-auto pr-2 custom-scrollbar">
                                {{-- LOGIC: KITA FILTER 'Milestone' SAHAJA --}}
                                @php
                                    // 1. Ambil rewards dari variable $rewards
                                    // 2. Filter hanya Category = 'Milestone' (Abaikan Merchant/Point)
                                    // 3. Susun ikut step (3, 6, 9, 12)
                                    $milestoneRewards = isset($rewards) ? $rewards->where('category', 'Milestone')->sortBy('milestone_step') : collect([]);
                                    
                                    // Fallback jika DB kosong (Untuk demo sahaja)
                                    if($milestoneRewards->isEmpty()){
                                        $milestoneRewards = collect([
                                            (object)['milestone_step' => 3, 'name' => '20% OFF', 'offer_description' => '20% Discount for first 3 stamped bookings .'],
                                            (object)['milestone_step' => 6, 'name' => '50% OFF', 'offer_description' => '50% off for first 6 stamped bookings.'],
                                            (object)['milestone_step' => 9, 'name' => '70% OFF', 'offer_description' => '70% off for first 9 stamped bookings.'],
                                            (object)['milestone_step' => 12, 'name' => 'FREE HALF DAY', 'offer_description' => 'Free half-day rental (12 hours).'],
                                        ]);
                                    }
                                @endphp

                                @foreach($milestoneRewards as $reward)
                                    @php 
                                        $isUnlocked = ($currentInCycle ?? 0) >= $reward->milestone_step; 
                                    @endphp
                                    
                                    <div class="flex items-center justify-between p-4 rounded-xl border transition-all {{ $isUnlocked ? 'bg-green-500/10 border-green-500/30' : 'bg-white/5 border-white/5 grayscale opacity-60' }}">
                                        <div class="flex items-center gap-4">
                                            {{-- Badge Stamp --}}
                                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-black text-sm shrink-0 {{ $isUnlocked ? 'bg-green-500 text-white shadow-lg shadow-green-500/40' : 'bg-gray-700 text-gray-400' }}">
                                                {{ $reward->milestone_step }}x
                                            </div>
                                            
                                            {{-- Text --}}
                                            <div>
                                                <p class="text-sm font-black {{ $isUnlocked ? 'text-white' : 'text-gray-400' }}">
                                                    {{ $reward->name }}
                                                </p>
                                                <p class="text-[10px] text-gray-500 leading-tight">{{ Str::limit($reward->offer_description, 45) }}</p>
                                            </div>
                                        </div>

                                        {{-- Icon Status --}}
                                        <div>
                                            @if($isUnlocked)
                                                <div class="w-6 h-6 rounded-full bg-green-500 flex items-center justify-center">
                                                    <i class="fas fa-check text-white text-xs"></i>
                                                </div>
                                            @else
                                                <i class="fas fa-lock text-gray-600 text-sm"></i>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-auto pt-4 text-center">
                                <p class="text-gray-500 text-[10px] font-bold uppercase tracking-widest hover:text-white transition">Tap card to flip back</p>
                            </div>

                        </div>

                    </div>
                </div>

                
                {{-- 4. MY VOUCHERS (With History Tabs) --}}
                {{-- Added: gold-glow-card class --}}
                <div class="w-full h-[550px] bg-black/50 backdrop-blur-2xl rounded-3xl p-6 border border-white/10 shadow-2xl flex flex-col relative overflow-hidden gold-glow-card transition-all duration-500">
                    
                    {{-- Header with Tabs --}}
                    <div class="flex justify-between items-center mb-6 shrink-0 z-10">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-600/20 flex items-center justify-center border border-blue-500/30">
                                <i class="fas fa-wallet text-blue-400"></i>
                            </div>
                            <div>
                                <h3 class="text-2xl font-black text-white tracking-tight leading-none">My Vouchers</h3>
                                
                                {{-- Tabs Switcher --}}
                                <div class="flex gap-4 mt-1">
                                    <button onclick="switchTab('active')" id="tab-active" class="text-[10px] font-bold uppercase tracking-widest text-blue-400 border-b-2 border-blue-400 pb-0.5 transition-colors">
                                        Active
                                    </button>
                                    <button onclick="switchTab('history')" id="tab-history" class="text-[10px] font-bold uppercase tracking-widest text-gray-500 hover:text-gray-300 pb-0.5 transition-colors">
                                        History
                                    </button>
                                </div>
                            </div>
                        </div>
                        <span class="text-[10px] font-bold text-white bg-white/10 px-3 py-1 rounded-full border border-white/10">
                            {{ $vouchers->count() }} Available
                        </span>
                    </div>

                    
                    {{-- CONTENT: ACTIVE VOUCHERS --}}
                    <div id="view-active" class="space-y-3 h-full overflow-y-auto pr-2 custom-scrollbar transition-opacity duration-300">
                        @forelse($vouchers as $voucher)
                            {{-- Kad Voucher (Clickable untuk buka detail) --}}
                            <div onclick="openVoucherDetail('{{ $voucher->code }}', '{{ $voucher->redeem_place ?? 'Rental Discount' }}', '{{ $voucher->voucherType }}', '{{ \Carbon\Carbon::parse($voucher->validUntil)->format('d M Y') }}')" 
                                class="group relative flex items-center overflow-hidden rounded-xl bg-gradient-to-r from-gray-900 to-gray-800 border border-white/10 hover:border-orange-500/50 transition-all duration-300 hover:shadow-lg hover:shadow-orange-900/20 hover:-translate-y-1 shrink-0 cursor-pointer p-4">
                                
                                {{-- IKON KIRI --}}
                                <div class="w-12 h-12 rounded-xl flex-shrink-0 flex items-center justify-center mr-4
                                    {{ $voucher->voucherType == 'Merchant Reward' ? 'bg-pink-500/10 text-pink-400 border border-pink-500/20' : 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20' }}">
                                    <i class="fas {{ $voucher->voucherType == 'Merchant Reward' ? 'fa-store' : 'fa-car' }} text-lg"></i>
                                </div>

                                {{-- INFO TENGAH --}}
                                <div class="flex-1 min-w-0">
                                    @if($voucher->voucherType == 'Merchant Reward')
                                        <p class="text-white font-black text-sm uppercase tracking-wide leading-tight truncate">{{ $voucher->redeem_place }}</p>
                                    @else
                                        {{-- [LOGIC BARU] Check dulu conditions dia --}}
                                        @if(str_contains(strtoupper($voucher->conditions ?? ''), 'FREE HALF DAY'))
                                            {{-- Kalau jumpa keyword FREE HALF DAY --}}
                                            <p class="text-white font-black text-lg leading-none">FREE HALF DAY</p>
                                        @else
                                            {{-- Kalau tak jumpa, baru tunjuk % --}}
                                            <p class="text-white font-black text-lg leading-none">{{ $voucher->discount_percent }}% OFF</p>
                                        @endif
                                    @endif

                                    {{-- KOD DIPAPARKAN TERUS DI SINI --}}
                                    <div class="flex items-center gap-2 mt-2">
                                        <span class="font-mono text-sm font-black text-orange-400 bg-black/40 px-3 py-1 rounded border border-white/10 tracking-widest">
                                            {{ $voucher->code }}
                                        </span>
                                    </div>
                                </div>

                                {{-- INFO KANAN (TARIKH) --}}
                                <div class="text-right pl-2">
                                    <p class="text-[9px] text-gray-400 mb-1">Expires</p>
                                    <p class="text-[10px] font-bold text-green-400">
                                        {{ \Carbon\Carbon::parse($voucher->validUntil)->format('d M') }}
                                    </p>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12 px-4 border-2 border-dashed border-white/5 rounded-2xl bg-white/[0.02] h-full flex flex-col justify-center items-center">
                                <i class="fas fa-ticket-alt text-3xl mb-3 text-white/20 block"></i>
                                <p class="text-gray-400 text-sm font-medium">No active vouchers.</p>
                            </div>
                        @endforelse
                    </div>

                    {{-- CONTENT: HISTORY VOUCHERS (Hidden by Default) --}}
                    <div id="view-history" class="hidden space-y-3 h-full overflow-y-auto pr-2 custom-scrollbar transition-opacity duration-300">
                        @forelse($pastVouchers as $history)
                            <div class="flex overflow-hidden rounded-xl bg-white/5 border border-white/5 grayscale opacity-70 hover:opacity-100 transition-all shrink-0">
                                
                                {{-- INFO --}}
                                <div class="flex-1 p-4 flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-xl flex-shrink-0 flex items-center justify-center bg-gray-700/50 text-gray-400 border border-gray-600/30">
                                        @if($history->isUsed)
                                            <i class="fas fa-check-double text-lg"></i>
                                        @else
                                            <i class="fas fa-clock text-lg"></i>
                                        @endif
                                    </div>
                                    <div>
                                        @if($history->voucherType == 'Merchant Reward')
                                            <p class="text-gray-300 font-bold text-sm uppercase leading-tight">{{ $history->redeem_place }}</p>
                                        @else
                                            <p class="text-gray-300 font-bold text-lg leading-none">{{ $history->discount_percent }}% OFF</p>
                                        @endif
                                        
                                        <p class="text-[10px] font-bold mt-1 uppercase {{ $history->isUsed ? 'text-green-500' : 'text-red-500' }}">
                                            {{ $history->isUsed ? 'REDEEMED' : 'EXPIRED' }}
                                        </p>
                                    </div>
                                </div>

                                {{-- HIDDEN CODE SECTION --}}
                                <div class="w-[100px] bg-black/40 p-2 flex flex-col justify-center items-center text-center border-l border-white/5">
                                    <p class="text-[9px] text-gray-500 uppercase font-bold mb-1">Code</p>
                                    <div class="bg-white/5 px-2 py-1 rounded w-full mb-1">
                                        {{-- MASKED CODE --}}
                                        <p class="text-gray-500 font-mono text-xs font-bold tracking-widest">••••••••</p>
                                    </div>
                                    <p class="text-[9px] text-gray-500">
                                        @if($history->isUsed)
                                            Used: {{ $history->updated_at->format('d M y') }}
                                        @else
                                            Exp: {{ \Carbon\Carbon::parse($history->validUntil)->format('d M y') }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12 px-4 border-2 border-dashed border-white/5 rounded-2xl bg-white/[0.02] h-full flex flex-col justify-center items-center">
                                <i class="fas fa-history text-3xl mb-3 text-white/20 block"></i>
                                <p class="text-gray-400 text-sm font-medium">No voucher history yet.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>{{-- End Grid --}}

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


{{-- [UPDATED] STYLISH VOUCHER DETAIL MODAL --}}
<div id="voucherModal" class="fixed inset-0 z-[60] hidden flex items-center justify-center bg-black/95 backdrop-blur-xl transition-opacity duration-300 opacity-0" style="transition: opacity 0.3s ease-out;">
    
    {{-- Card Container with Pop-Up Animation --}}
    <div id="voucherModalCard" class="bg-gradient-to-b from-gray-900 via-gray-800 to-black p-8 rounded-[2.5rem] max-w-sm w-full text-center relative border border-white/20 shadow-[0_0_80px_rgba(234,88,12,0.25)] transform scale-90 transition-transform duration-300">
        
        {{-- Close Button --}}
        <button onclick="closeVoucherModal()" class="absolute top-5 right-5 text-gray-400 hover:text-white transition bg-white/10 hover:bg-white/20 w-10 h-10 rounded-full flex items-center justify-center z-10 backdrop-blur-md border border-white/5">
            <i class="fas fa-times text-lg"></i>
        </button>

        {{-- Top Decoration --}}
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-24 h-1 bg-gradient-to-r from-transparent via-orange-500 to-transparent opacity-50"></div>

        {{-- Header Info --}}
        <div class="mb-8 mt-4">
            <div class="inline-block px-3 py-1 rounded-full bg-orange-500/10 border border-orange-500/20 mb-3">
                <p class="text-[10px] text-orange-400 font-black uppercase tracking-[0.2em]">MERCHANT VOUCHER</p>
            </div>
            <h3 id="modalVoucherName" class="text-3xl font-black text-white leading-tight mb-2 tracking-tight drop-shadow-lg">LOADING...</h3>
            <p id="modalExpiry" class="text-xs font-bold text-gray-400 uppercase tracking-wider"></p>
        </div>
        
        {{-- BIG GLOWING CODE DISPLAY --}}
        <div class="mb-10 relative group mx-auto max-w-[280px]">
            {{-- Glowing Orb Behind --}}
            <div class="absolute inset-0 bg-orange-500 blur-2xl rounded-full opacity-20 group-hover:opacity-40 transition duration-700 animate-pulse"></div>
            
            {{-- Code Box --}}
            <div class="relative bg-black/80 border-2 border-dashed border-orange-500/30 rounded-2xl py-8 px-4 flex flex-col items-center justify-center gap-3 backdrop-blur-sm">
                
                {{-- Cutout Circles (Ticket effect) --}}
                <div class="absolute -left-3 top-1/2 -translate-y-1/2 w-6 h-6 bg-gray-800 rounded-full"></div>
                <div class="absolute -right-3 top-1/2 -translate-y-1/2 w-6 h-6 bg-gray-800 rounded-full"></div>

                <p class="text-[9px] text-gray-500 font-bold uppercase tracking-[0.3em] mb-1">SHOW TO CASHIER</p>
                <p id="modalCodeText" class="text-4xl font-mono font-black text-transparent bg-clip-text bg-gradient-to-r from-white via-gray-200 to-gray-400 tracking-widest drop-shadow-2xl">...</p>
            </div>
        </div>
        
        {{-- ACTION BUTTONS --}}
        <div id="modalActions" class="space-y-5">
            {{-- Stylish Use Now Button --}}
            <button id="btnUseNow" onclick="triggerUseNow()" 
                    class="group relative w-full overflow-hidden rounded-2xl p-[2px] focus:outline-none focus:ring-4 focus:ring-orange-500/30">
                <span class="absolute inset-[-1000%] animate-[spin_3s_linear_infinite] bg-[conic-gradient(from_90deg_at_50%_50%,#c2410c_0%,#fb923c_50%,#c2410c_100%)]"></span>
                <span class="inline-flex h-full w-full cursor-pointer items-center justify-center rounded-2xl bg-gray-900 px-8 py-4 text-sm font-black uppercase tracking-widest text-white backdrop-blur-3xl transition-all group-hover:bg-gray-800 gap-3">
                    <i class="fas fa-qrcode text-orange-500 text-lg group-hover:scale-110 transition-transform"></i> USE NOW
                </span>
            </button>
            
            <p class="text-[10px] text-gray-500 px-6 leading-relaxed opacity-70">
                Please ensure you are at the counter before pressing the button. Once used, it cannot be undone.
            </p>
        </div>
    </div>
</div>

{{-- SCRIPTS & STYLES --}}
<script>
function redeemReward(id, name, points) {
    document.getElementById('modalText').innerText = `Redeem ${name} voucher for ${points} points?`;
    document.getElementById('redemptionModal').classList.remove('hidden');

    document.getElementById('confirmBtn').onclick = function() {
        this.disabled = true;
        this.innerText = 'Processing...';

        fetch('{{ route("loyalty.redeem") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ reward_id: id })
        })
        .then(response => response.json())
        .then(data => {
            closeModal();
            if(data.success) {
                document.getElementById('successCode').innerText = data.code;
                document.getElementById('voucherName').innerText = data.voucher_name;
                document.getElementById('successModal').classList.remove('hidden');
            } else {
                alert(data.message || 'Failed to redeem voucher.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Something went wrong.');
        })
        .finally(() => {
            document.getElementById('confirmBtn').disabled = false;
            document.getElementById('confirmBtn').innerText = 'Confirm';
        });
    };
}

function closeModal() {
    document.getElementById('redemptionModal').classList.add('hidden');
    const confirmBtn = document.getElementById('confirmBtn');
    confirmBtn.disabled = false;
    confirmBtn.innerText = 'Confirm';
    confirmBtn.onclick = null;
}

document.addEventListener('click', function(event) {
    const modal = document.getElementById('redemptionModal');
    if (event.target === modal) closeModal();
});
</script>

<style scoped>
@keyframes fade-in {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in {
    animation: fade-in 0.5s ease-out forwards;
}
.custom-scrollbar::-webkit-scrollbar {
    width: 5px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.02);
    border-radius: 10px;
    margin: 10px 0;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.3);
}

/* === GOLD GLOW EFFECT === */
.gold-glow-card {
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    /* Optional: Bagi glow sikit walaupun belum hover (subtle) */
    box-shadow: 0 0 15px rgba(251, 191, 36, 0.05); 
}

.gold-glow-card:hover {
    /* 1. Border jadi solid terang (Opacity 1.0) */
    border-color: #fbbf24 !important; /* Amber-400 */

    /* 2. EFFECT GLOW POWER (Layered Shadows) */
    box-shadow: 
        0 0 20px rgba(251, 191, 36, 0.7),   /* Glow dekat yang tajam/terang */
        0 0 60px rgba(251, 191, 36, 0.4),   /* Glow jauh yang merebak */
        inset 0 0 20px rgba(251, 191, 36, 0.1); /* Glow sikit di bahagian dalam kad */

    transform: translateY(-5px) scale(1.01); /* Naik atas sikit lagi */
    z-index: 20; /* Pastikan dia duduk atas element lain bila glow */
}


</style>

<script>
// --- TAMBAH INI: SELF REDEEM LOGIC ---
function confirmUseVoucher(code, placeName) {
    if(confirm(`Use Voucher ${placeName} Now?\n\nMake sure you are at the payment counter.`)) {
        
        fetch('{{ route("voucher.use_now") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ code: code })
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert(`SUCCESS! Voucher used on: ${data.date}.\nPlease show this screen to the staff.\nIf you dont show to the Staff, the voucher might not be applied.`);
                location.reload(); 
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert(`System error. Please try again.`);
        });
    }
}

// --- TAB SWITCHER LOGIC ---
function switchTab(tab) {
    const activeView = document.getElementById('view-active');
    const historyView = document.getElementById('view-history');
    const activeTabBtn = document.getElementById('tab-active');
    const historyTabBtn = document.getElementById('tab-history');

    if(tab === 'active') {
        activeView.classList.remove('hidden');
        historyView.classList.add('hidden');
        
        // Style Button
        activeTabBtn.classList.add('text-blue-400', 'border-b-2', 'border-blue-400');
        activeTabBtn.classList.remove('text-gray-500');
        
        historyTabBtn.classList.add('text-gray-500');
        historyTabBtn.classList.remove('text-blue-400', 'border-b-2', 'border-blue-400');
    } else {
        activeView.classList.add('hidden');
        historyView.classList.remove('hidden');

        // Style Button
        historyTabBtn.classList.add('text-blue-400', 'border-b-2', 'border-blue-400');
        historyTabBtn.classList.remove('text-gray-500');

        activeTabBtn.classList.add('text-gray-500');
        activeTabBtn.classList.remove('text-blue-400', 'border-b-2', 'border-blue-400');
    }
}    

// --- VARIABLES & FUNGSI POP-UP VOUCHER ---
let currentVoucherCode = '';
let currentVoucherName = '';

// --- BUKA MODAL DETAIL (UPDATED ANIMATION) ---
function openVoucherDetail(code, name, type, expiryDate) {
    // 1. Simpan data
    currentVoucherCode = code;
    currentVoucherName = name;

    // 2. Isi maklumat dalam Modal
    document.getElementById('modalVoucherName').innerText = name;
    document.getElementById('modalCodeText').innerText = code;
    document.getElementById('modalExpiry').innerText = 'Valid until: ' + expiryDate;
    
    // 3. Logic Butang "USE NOW"
    const btnUseNow = document.getElementById('btnUseNow');
    if (type === 'Merchant Reward') {
        btnUseNow.classList.remove('hidden');
    } else {
        // Kalau Rental Discount, sorok butang Use Now (sebab auto-apply masa booking)
        btnUseNow.classList.add('hidden'); 
    }

    // 4. Tunjuk Modal with Animation
    const modal = document.getElementById('voucherModal');
    const modalCard = document.getElementById('voucherModalCard');
    
    modal.classList.remove('hidden');
    
    // Slight delay to allow display:block to render before opacity transition
    setTimeout(() => {
        modal.classList.remove('opacity-0');
        modalCard.classList.remove('scale-90');
        modalCard.classList.add('scale-100');
    }, 10);
}

// --- FUNGSI USE NOW (TRIGGER) ---
function triggerUseNow() {
    confirmUseVoucher(currentVoucherCode, currentVoucherName);
}

// --- CLOSE MODAL (UPDATED ANIMATION) ---
function closeVoucherModal() {
    const modal = document.getElementById('voucherModal');
    const modalCard = document.getElementById('voucherModalCard');

    modal.classList.add('opacity-0');
    modalCard.classList.remove('scale-100');
    modalCard.classList.add('scale-90');

    // Wait for transition to finish before hiding
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}

// Tutup modal bila klik luar kotak
document.getElementById('voucherModal').addEventListener('click', function(e) {
    if (e.target === this) closeVoucherModal();
});
</script>

<style>
    /* CSS untuk Effect Flip 3D */
    .perspective-1000 {
        perspective: 1000px;
    }
    .style-preserve-3d {
        transform-style: preserve-3d;
    }
    .backface-hidden {
        backface-visibility: hidden;
        -webkit-backface-visibility: hidden; /* Safari */
    }
    .rotate-y-180 {
        transform: rotateY(180deg);
    }
    
    /* Class untuk trigger pusing */
    .is-flipped {
        transform: rotateY(180deg);
    }

    /* Custom Scrollbar untuk list reward */
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #4b5563; border-radius: 4px; }
</style>

<script>
    function toggleFlip(element) {
        // Cari container dalam (id="flipperBox") dan toggle class
        const flipper = element.querySelector('#flipperBox');
        flipper.classList.toggle('is-flipped');
    }
</script>
@endsection