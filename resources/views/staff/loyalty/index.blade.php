@extends('layouts.staff')

@section('content')
<div class="min-h-screen bg-gray-100 rounded-2xl p-6">
    <div class="max-w-7xl mx-auto">
        
        {{-- PAGE HEADER --}}
        <div class="mb-8">
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">
            Loyalty & Rewards Management
            </h1>
            <p class="text-gray-500 mt-1 text-sm">Monitor customer loyalty points, rewards activities, and manage vouchers</p>
        </div>

        {{-- KEY METRICS --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            {{-- Total Points Distributed --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Total Points Distributed</p>
                        <h3 class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($totalPointsDistributed) }}</h3>
                    </div>
                    <div class="w-14 h-14 rounded-full bg-blue-100 flex items-center justify-center">
                        <i class="fas fa-gift text-blue-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            {{-- Total Points Redeemed --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Points Redeemed</p>
                        <h3 class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($totalPointsRedeemed) }}</h3>
                    </div>
                    <div class="w-14 h-14 rounded-full bg-green-100 flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            {{-- Rental Vouchers --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Rental Vouchers</p>
                        <h3 class="text-3xl font-bold text-gray-900 mt-2">{{ $rentalVouchers->count() }}</h3>
                    </div>
                    <div class="w-14 h-14 rounded-full bg-purple-100 flex items-center justify-center">
                        <i class="fas fa-car text-purple-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            {{-- Total Members --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Loyalty Members</p>
                        <h3 class="text-3xl font-bold text-gray-900 mt-2">{{ $loyaltyStats->count() }}</h3>
                    </div>
                    <div class="w-14 h-14 rounded-full bg-orange-100 flex items-center justify-center">
                        <i class="fas fa-users text-orange-600 text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABS NAVIGATION --}}
        <div class="mb-6 overflow-x-auto">
            <div class="flex gap-4 border-b border-gray-200 min-w-max">
                <button id="btn-customers" onclick="showTab('customers')" class="tab-btn active px-6 py-3 font-bold text-gray-900 border-b-2 border-orange-500 transition">
                    <i class="fas fa-list mr-2"></i> Customer List
                </button>
                <button id="btn-tier" onclick="showTab('tier')" class="tab-btn px-6 py-3 font-bold text-gray-500 border-b-2 border-transparent hover:text-gray-900 transition">
                    <i class="fas fa-layer-group mr-2"></i> Tier Breakdown
                </button>
                
                {{-- GABUNGAN TAB REWARDS & VOUCHERS --}}
                <button id="btn-rewards_vouchers" onclick="showTab('rewards_vouchers')" class="tab-btn px-6 py-3 font-bold text-gray-500 border-b-2 border-transparent hover:text-gray-900 transition">
                    <i class="fas fa-gift mr-2"></i> Rewards & Vouchers
                </button>

                <button id="btn-activities" onclick="showTab('activities')" class="tab-btn px-6 py-3 font-bold text-gray-500 border-b-2 border-transparent hover:text-gray-900 transition">
                    <i class="fas fa-history mr-2"></i> Recent Activities
                </button>
            </div>
        </div>

        {{-- TAB 1: CUSTOMER LOYALTY LIST --}}
        <div id="customers-tab" class="tab-content">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center">
                    <i class="fas fa-users text-indigo-600 mr-3"></i> All Customers Loyalty Points
                </h3>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50">
                                <th class="px-6 py-3 text-left font-bold text-gray-700">#</th>
                                <th class="px-6 py-3 text-left font-bold text-gray-700">Customer Name</th>
                                <th class="px-6 py-3 text-center font-bold text-gray-700">Loyalty Points</th>
                                <th class="px-6 py-3 text-center font-bold text-gray-700">Tier</th>
                                <th class="px-6 py-3 text-center font-bold text-gray-700">Vouchers</th>
                                <th class="px-6 py-3 text-center font-bold text-gray-700">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($loyaltyStats as $index => $loyalty)
                                <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 text-gray-700 font-medium">{{ $index + 1 }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center font-bold">
                                                {{ substr($loyalty->customer->fullName ?? 'G', 0, 1) }}
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-900">{{ $loyalty->customer->fullName ?? 'N/A' }}</p>
                                                <p class="text-xs text-gray-500">{{ $loyalty->customer->email ?? 'N/A' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-xl font-bold text-indigo-600">{{ number_format($loyalty->points) }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-3 py-1 rounded-full text-xs font-bold
                                            {{ $loyalty->tier == 'Platinum' ? 'bg-cyan-100 text-cyan-700' : ($loyalty->tier == 'Gold' ? 'bg-yellow-100 text-yellow-700' : ($loyalty->tier == 'Silver' ? 'bg-slate-100 text-slate-700' : 'bg-amber-100 text-amber-700')) }}">
                                            {{ $loyalty->tier }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @php $voucherCount = \App\Models\Voucher::where('customerID', $loyalty->user_id)->count(); @endphp
                                        <span class="font-bold text-gray-700">{{ $voucherCount }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <a href="{{ route('staff.loyalty.show_customer', $loyalty->user_id) }}" class="text-indigo-600 hover:text-indigo-800 font-bold transition">
                                            <i class="fas fa-eye mr-1"></i> View Details
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-400">
                                        <i class="fas fa-inbox text-3xl mb-2 block"></i>
                                        <p>No loyalty data yet</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- TAB 2: TIER BREAKDOWN --}}
        <div id="tier-tab" class="tab-content hidden">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center">
                    <i class="fas fa-layer-group text-indigo-600 mr-3"></i> Member Tier Distribution
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                    {{-- Bronze --}}
                    <div class="bg-gradient-to-br from-amber-200 to-amber-600 rounded-xl p-6 border border-amber-200">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-bold text-gray-900 text-lg">Bronze</h4>
                            <i class="fas fa-medal text-amber-700 text-3xl"></i>
                        </div>
                        <p class="text-4xl font-bold text-amber-900 mb-2">{{ $tierBreakdown['Bronze'] }}</p>
                        <p class="text-xs text-amber-700">0 - 999 points</p>
                    </div>

                    {{-- Silver --}}
                    <div class="bg-gradient-to-br from-slate-200 to-slate-600 rounded-xl p-6 border border-slate-200">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-bold text-gray-900 text-lg">Silver</h4>
                            <i class="fas fa-medal text-slate-600 text-3xl"></i>
                        </div>
                        <p class="text-4xl font-bold text-slate-900 mb-2">{{ $tierBreakdown['Silver'] }}</p>
                        <p class="text-xs text-slate-700">1000 - 2499 points</p>
                    </div>

                    {{-- Gold --}}
                    <div class="bg-gradient-to-br from-yellow-200 to-yellow-600 rounded-xl p-6 border border-yellow-300">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-bold text-gray-900 text-lg">Gold</h4>
                            <i class="fas fa-medal text-yellow-600 text-3xl"></i>
                        </div>
                        <p class="text-4xl font-bold text-yellow-900 mb-2">{{ $tierBreakdown['Gold'] }}</p>
                        <p class="text-xs text-yellow-700">2500 - 4999 points</p>
                    </div>

                    {{-- Platinum --}}
                    <div class="bg-gradient-to-br from-cyan-200 to-cyan-600 rounded-xl p-6 border border-cyan-300">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-bold text-gray-900 text-lg">Platinum</h4>
                            <i class="fas fa-crown text-cyan-600 text-3xl"></i>
                        </div>
                        <p class="text-4xl font-bold text-cyan-900 mb-2">{{ $tierBreakdown['Platinum'] }}</p>
                        <p class="text-xs text-cyan-700">5000+ points</p>
                    </div>
                </div>

                {{-- Top Performers --}}
                <div>
                    <h4 class="font-bold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-trophy text-yellow-500 mr-2"></i> Top 10 Performers
                    </h4>
                    <div class="space-y-2">
                        @foreach($topPerformers as $index => $performer)
                            <div class="flex items-center justify-between p-3 rounded-lg {{ $index == 0 ? 'bg-yellow-50 border border-yellow-200' : ($index == 1 ? 'bg-slate-50 border border-slate-200' : ($index == 2 ? 'bg-orange-50 border border-orange-200' : 'bg-gray-50 border border-gray-200')) }}">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center font-bold text-sm">
                                        {{ $index + 1 }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $performer->customer->fullName ?? 'N/A' }}</p>
                                        <p class="text-xs text-gray-500">{{ $performer->tier }}</p>
                                    </div>
                                </div>
                                <span class="font-bold text-indigo-600 text-lg">{{ number_format($performer->points) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- TAB 3: COMBINED REWARDS & VOUCHERS MANAGEMENT --}}
        <div id="rewards_vouchers-tab" class="tab-content hidden space-y-8">
            
            {{-- ========================================================= --}}
            {{-- SECTION A: MANAGE DISPLAYED REWARDS (MENU) --}}
            {{-- ========================================================= --}}
            <div class="bg-white rounded-2xl shadow-lg border-l-4 border-pink-500 overflow-hidden">
                
                {{-- Header Section --}}
                <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                    <div>
                        <h3 class="text-xl font-black text-gray-900 flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg bg-pink-100 flex items-center justify-center text-pink-600">
                                <i class="fas fa-store"></i>
                            </span>
                            Manage Displayed Rewards
                        </h3>
                        <p class="text-sm text-gray-500 mt-1 ml-10">Set up the catalog of rewards visible to customers.</p>
                    </div>
                </div>

                <div class="p-6">
                    {{-- Add Reward Form --}}
                    <div class="bg-gradient-to-r from-gray-50 to-white p-6 rounded-2xl border border-gray-200 mb-8 relative">
                        <div class="absolute top-0 right-0 -mt-2 -mr-2 w-16 h-16 bg-pink-500/10 rounded-full blur-xl"></div>
                        
                        <h4 class="text-xs font-bold text-pink-600 uppercase tracking-widest mb-4">Add New Reward Item</h4>
                        
                        <form action="{{ route('staff.loyalty.store_reward') }}" method="POST">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                                <div class="md:col-span-3">
                                    <label class="block text-xs font-bold text-gray-600 mb-1">Merchant Name</label>
                                    <input 
                                        type="text" 
                                        name="name" 
                                        placeholder="e.g. KFC" 
                                        class="w-full bg-white border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition shadow-sm uppercase font-bold" 
                                        required
                                        oninput="this.value = this.value.toUpperCase()"
                                    >
                                </div>
                                <div class="md:col-span-3">
                                    <label class="block text-xs font-bold text-gray-600 mb-1">Offer Description</label>
                                    <input type="text" name="offer" placeholder="RM5 OFF" class="w-full bg-white border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition shadow-sm" required oninput="this.value = this.value.toUpperCase()">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-bold text-gray-600 mb-1">Points Cost</label>
                                    <div class="relative">
                                        <input type="number" name="points" placeholder="200" class="w-full bg-white border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition shadow-sm pl-8" required>
                                        <span class="absolute left-3 top-2.5 text-gray-400 text-xs"><i class="fas fa-star"></i></span>
                                    </div>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-bold text-gray-600 mb-1">Code Prefix</label>
                                    <input type="text" name="code_prefix" placeholder="KFC" class="w-full bg-white border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition shadow-sm" required oninput="this.value = this.value.toUpperCase()">
                                </div>
                                <div class="md:col-span-2">
                                    <button type="submit" class="w-full bg-pink-600 hover:bg-pink-700 text-white font-bold py-2.5 px-4 rounded-xl shadow-lg shadow-pink-600/30 transition transform active:scale-95 flex items-center justify-center gap-2">
                                        <i class="fas fa-plus-circle"></i> Add
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    {{-- List Rewards Table --}}
                    <div class="overflow-hidden rounded-xl border border-gray-200 shadow-sm">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-100 text-gray-600 uppercase text-[10px] tracking-wider font-bold">
                                <tr>
                                    <th class="px-6 py-4 text-left">Merchant / Reward</th>
                                    <th class="px-6 py-4 text-left">Offer Description</th>
                                    <th class="px-6 py-4 text-center">Points Cost</th>
                                    <th class="px-6 py-4 text-center">            </th>
                                    <th class="px-6 py-4 text-center">Status</th>
                                    <th class="px-6 py-4 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($manageRewards as $reward)
                                    <tr class="border-b border-gray-100">
                                        <form action="{{ route('staff.loyalty.update_reward', $reward->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            
                                            {{-- 1. Merchant Name --}}
                                            <td class="px-6 py-3">
                                                <input type="text" name="name" value="{{ $reward->name }}" class="bg-transparent font-bold text-gray-900 border-b border-transparent focus:border-pink-500 focus:ring-0 w-full transition px-0">
                                            </td>

                                            {{-- 2. Offer Description --}}
                                            <td class="px-6 py-3">
                                                <input type="text" name="offer" value="{{ $reward->offer_description }}" class="bg-transparent text-gray-600 border-b border-transparent focus:border-pink-500 focus:ring-0 w-full transition px-0">
                                            </td>

                                            {{-- 3. Points Required --}}
                                            <td class="px-6 py-3 text-center">
                                                <div class="inline-flex items-center bg-gray-100 rounded-lg px-3 py-1">
                                                    <i class="fas fa-star text-orange-400 text-xs mr-2"></i>
                                                    <input type="number" name="points" value="{{ $reward->points_required }}" class="bg-transparent text-center w-12 text-gray-900 font-bold focus:outline-none p-0 border-none h-auto">
                                                </div>
                                            </td>

                                            {{-- 4. Discount % (Milestone Only) --}}
                                            <td class="px-6 py-3 text-center">
                                                @if($reward->category == 'Milestone')
                                                    <div class="flex items-center justify-center gap-1">
                                                        <span class="text-[10px] text-gray-400 font-bold bg-gray-100 px-1.5 py-0.5 rounded">STEP {{ $reward->milestone_step }}</span>
                                                        <input type="number" name="discount_percent" value="{{ $reward->discount_percent }}" class="w-10 text-center font-bold text-green-600 border-b border-gray-300 focus:border-green-500 focus:ring-0 p-0">
                                                        <span class="text-xs text-gray-500">%</span>
                                                    </div>
                                                @else
                                                    <span class="text-gray-300">-</span>
                                                @endif
                                            </td>

                                            {{-- 5. Active Toggle --}}
                                            <td class="px-6 py-3 text-center">
                                                <label class="inline-flex items-center cursor-pointer">
                                                    <input type="checkbox" name="is_active" {{ $reward->is_active ? 'checked' : '' }} class="sr-only peer">
                                                    <div class="relative w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-pink-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-pink-500"></div>
                                                </label>
                                            </td>

                                            {{-- 6. Actions --}}
                                            <td class="px-6 py-3 text-center">
                                                {{-- Buang opacity-0 dan group-hover... supaya sentiasa nampak --}}
                                                <div class="flex items-center justify-center gap-3">
                                                    <button type="submit" class="text-blue-500 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 p-2 rounded-lg transition" title="Save Changes">
                                                        <i class="fas fa-save"></i>
                                                    </button>
                                                    <a href="{{ route('staff.loyalty.delete_reward', $reward->id) }}" class="text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 p-2 rounded-lg transition" onclick="return confirm('Remove this reward?')" title="Delete Reward">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </form>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ========================================================= --}}
            {{-- SECTION B: MANAGE VOUCHERS (SPECIFIC CODES) --}}
            {{-- ========================================================= --}}
            <div class="bg-white rounded-2xl shadow-lg border-l-4 border-green-500 overflow-hidden">
                
                {{-- Header Section --}}
                <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                    <div>
                        <h3 class="text-xl font-black text-gray-900 flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center text-green-600">
                                <i class="fas fa-ticket-alt"></i>
                            </span>
                            Manage Specific Vouchers
                        </h3>
                        <p class="text-sm text-gray-500 mt-1 ml-10">Create discount codes for rental or merchant redemptions.</p>
                    </div>
                </div>

                <div class="p-6">
                    {{-- Add New Voucher Form --}}
                    <div class="bg-gradient-to-r from-gray-50 to-white p-8 rounded-2xl border border-gray-200 mb-10">
                        <h4 class="text-sm font-bold text-green-700 uppercase tracking-widest mb-6 border-b border-green-100 pb-2">Create New Voucher</h4>
                        
                        <form action="{{ route('staff.loyalty.store_voucher') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            @csrf
                            
                            {{-- 1. Code --}}
                            <div class="col-span-1 md:col-span-2">
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Voucher Code</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                        <i class="fas fa-barcode"></i>
                                    </span>
                                    <input 
                                        type="text" 
                                        name="code" 
                                        class="w-full pl-10 pr-4 py-3 bg-white border border-gray-200 rounded-xl text-gray-900 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 font-mono font-bold tracking-wider uppercase transition" 
                                        placeholder="e.g. HASTA10-ABC" 
                                        required
                                        oninput="this.value = this.value.toUpperCase()" 
                                    >
                                </div>
                            </div>

                            {{-- 2. Amount --}}
                            <div class="col-span-1">
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Discount (RM/%)</label>
                                <input 
                                    type="number" 
                                    name="amount" 
                                    class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl text-gray-900 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition font-bold" 
                                    placeholder="0.00"
                                    required
                                >
                            </div>

                            {{-- 3. Type (FIXED DISPLAY) --}}
                            <div class="col-span-1">
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Type</label>
                                {{-- Input Hidden --}}
                                <input type="hidden" name="type" value="Rental Discount">
                                {{-- Visual Display --}}
                                <div class="w-full px-4 py-3 bg-green-50 border border-green-200 rounded-xl text-green-800 text-sm font-bold flex items-center shadow-inner cursor-not-allowed">
                                    <i class="fas fa-car mr-2"></i> Rental Discount
                                </div>
                            </div>

                            {{-- 4. Valid From --}}
                            <div class="col-span-1">
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Valid From</label>
                                <input type="datetime-local" name="valid_from" class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl text-gray-600 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition" required>
                            </div>

                            {{-- 5. Valid Until --}}
                            <div class="col-span-1">
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Valid Until</label>
                                <input type="datetime-local" name="valid_until" class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl text-gray-600 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition" required>
                            </div>

                            {{-- 6. Description --}}
                            <div class="col-span-1 md:col-span-2">
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Internal Note (Optional)</label>
                                <input type="text" name="description" class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl text-gray-600 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition" placeholder="e.g. Promo Merdeka" maxlength="255">
                            </div>

                            {{-- Submit Button --}}
                            <div class="col-span-1 md:col-span-4 mt-2">
                                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-green-600/30 transition transform active:scale-[0.98] flex items-center justify-center gap-2 text-base">
                                    <i class="fas fa-check-circle"></i> Create Voucher Code
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- Sub-Section: Rental Vouchers List --}}
                    <div class="mb-8">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="h-8 w-1 bg-green-500 rounded-full"></div>
                            <h4 class="text-lg font-bold text-gray-800">Rental Discount Vouchers</h4>
                            <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-md text-xs font-bold">{{ $rentalVouchers->count() }}</span>
                        </div>

                        <div class="overflow-hidden rounded-xl border border-gray-200 shadow-sm">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 text-gray-500 uppercase text-[10px] tracking-wider font-bold">
                                    <tr>
                                        <th class="px-6 py-3 text-left">Code</th>
                                        <th class="px-6 py-3 text-center">Amount (RM)</th>
                                        <th class="px-6 py-3 text-center">Valid Range</th>
                                        <th class="px-6 py-3 text-center">Status</th>
                                        <th class="px-6 py-3 text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @forelse($rentalVouchers as $voucher)
                                        <tr class="hover:bg-gray-50 transition">
                                            <td class="px-6 py-4 font-mono font-bold text-gray-900 tracking-wide">{{ $voucher->code }}</td>
                                            <td class="px-6 py-4 text-center">
                                                <span class="font-bold text-green-600 bg-green-50 px-2 py-1 rounded">RM {{ number_format($voucher->voucherAmount, 2) }}</span>
                                            </td>
                                            <td class="px-6 py-4 text-center text-xs text-gray-500">
                                                <div>{{ $voucher->validFrom?->format('d M') }} - {{ $voucher->validUntil?->format('d M Y') }}</div>
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide border {{ $voucher->isUsed ? 'bg-red-50 text-red-600 border-red-100' : 'bg-green-50 text-green-600 border-green-100' }}">
                                                    {{ $voucher->isUsed ? 'Used' : 'Active' }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-center space-x-2">
                                                <button onclick="editVoucher({{ $voucher->voucherID }})" class="text-blue-600 hover:text-blue-800 transition" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form action="{{ route('staff.loyalty.delete_voucher', $voucher->voucherID) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-400 hover:text-red-600 transition" onclick="return confirm('Delete this voucher?')" title="Delete">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-10 text-center text-gray-400 italic bg-gray-50">
                                                No rental vouchers created yet.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Sub-Section: Merchant Vouchers List --}}
                    <div>
                        <div class="flex items-center gap-3 mb-4">
                            <div class="h-8 w-1 bg-purple-500 rounded-full"></div>
                            <h4 class="text-lg font-bold text-gray-800">Merchant Reward Vouchers</h4>
                            <span class="bg-purple-100 text-purple-700 px-2 py-0.5 rounded-md text-xs font-bold">{{ $merchantVouchers->count() }}</span>
                        </div>

                        <div class="overflow-hidden rounded-xl border border-gray-200 shadow-sm">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 text-gray-500 uppercase text-[10px] tracking-wider font-bold">
                                    <tr>
                                        <th class="px-6 py-3 text-left">Code</th>
                                        <th class="px-6 py-3 text-left">Merchant</th>
                                        <th class="px-6 py-3 text-center">Expires</th>
                                        <th class="px-6 py-3 text-center">Status</th>
                                        <th class="px-6 py-3 text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @forelse($merchantVouchers as $voucher)
                                        <tr class="hover:bg-gray-50 transition">
                                            <td class="px-6 py-4 font-mono font-bold text-gray-900 tracking-wide">{{ $voucher->code }}</td>
                                            <td class="px-6 py-4 text-gray-700 font-medium">{{ $voucher->redeem_place ?? 'N/A' }}</td>
                                            <td class="px-6 py-4 text-center text-xs text-gray-500">
                                                {{ $voucher->validUntil?->format('d M Y') ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide border {{ $voucher->isUsed ? 'bg-red-50 text-red-600 border-red-100' : 'bg-green-50 text-green-600 border-green-100' }}">
                                                    {{ $voucher->isUsed ? 'Used' : 'Active' }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-center space-x-2">
                                                <button onclick="editVoucher({{ $voucher->voucherID }})" class="text-blue-600 hover:text-blue-800 transition">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form action="{{ route('staff.loyalty.delete_voucher', $voucher->voucherID) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-400 hover:text-red-600 transition" onclick="return confirm('Delete this voucher?')">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-10 text-center text-gray-400 italic bg-gray-50">
                                                No merchant vouchers created yet.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- TAB 4: RECENT ACTIVITIES (UPDATED WITH FILTER) --}}
        <div id="activities-tab" class="tab-content hidden">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center">
                        <i class="fas fa-history text-blue-600 mr-3"></i> Recent Loyalty Activities
                    </h3>

                    {{-- FILTER DROPDOWN --}}
                    <form method="GET" action="{{ route('staff.loyalty.index') }}">
                        <div class="flex gap-2">
                            <select name="activity_type" class="bg-gray-50 border border-gray-200 text-sm rounded-lg p-2 focus:ring-orange-500" onchange="this.form.submit()">
                                <option value="all" {{ request('activity_type') == 'all' ? 'selected' : '' }}>All Activities</option>
                                <option value="earned" {{ request('activity_type') == 'earned' ? 'selected' : '' }}>Points Earned</option>
                                <option value="redeemed" {{ request('activity_type') == 'redeemed' ? 'selected' : '' }}>Points Redeemed</option>
                                <option value="rental" {{ request('activity_type') == 'rental' ? 'selected' : '' }}>Rental Rewards</option>
                                <option value="merchant" {{ request('activity_type') == 'merchant' ? 'selected' : '' }}>Food Vouchers</option>
                            </select>
                            {{-- Hack to keep the tab active on reload --}}
                            <input type="hidden" id="active_tab_input" name="tab" value="activities">
                        </div>
                    </form>
                </div>

                <div class="space-y-2 max-h-96 overflow-y-auto">
                    @forelse($recentActivities as $activity)
                        <div class="flex items-start justify-between p-4 rounded-lg hover:bg-gray-50 transition border border-gray-100">
                            <div class="flex-1">
                                <p class="text-sm font-bold text-gray-900">{{ $activity->reason }}</p>
                                <p class="text-xs text-gray-600 mt-1">
                                    <i class="fas fa-user mr-1"></i> {{ $activity->customer->fullName ?? 'Unknown' }}
                                </p>
                                <p class="text-xs text-gray-400 mt-1">{{ $activity->created_at->format('M d, Y H:i') }}</p>
                            </div>
                            <span class="font-bold text-lg {{ $activity->points_change > 0 ? 'text-green-600' : 'text-red-600' }} ml-4">
                                {{ $activity->points_change > 0 ? '+' : '' }}{{ number_format($activity->points_change) }}
                            </span>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-400">
                            <p class="text-sm">No recent activities</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</div>

{{-- SCRIPTS --}}
<script>
    function showTab(tabName) {
        // Hide all tabs
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.add('hidden');
        });
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('border-orange-500', 'text-gray-900');
            btn.classList.add('border-transparent', 'text-gray-500');
        });

        // Show selected tab
        const selectedTab = document.getElementById(tabName + '-tab');
        if(selectedTab) {
            selectedTab.classList.remove('hidden');
        }

        // Highlight Button
        const btnId = 'btn-' + tabName;
        const btn = document.getElementById(btnId);
        if(btn) {
            btn.classList.remove('border-transparent', 'text-gray-500');
            btn.classList.add('border-orange-500', 'text-gray-900');
        }
    }

    // Auto-select tab if reloading from filter
    document.addEventListener("DOMContentLoaded", function() {
        const urlParams = new URLSearchParams(window.location.search);
        if(urlParams.has('activity_type')) {
            showTab('activities');
        } 
    });

    function editVoucher(voucherId) {
        // Fetch voucher data via AJAX
        fetch(`/staff/loyalty/voucher/${voucherId}/edit`)
            .then(response => response.json())
            .then(data => {
                // Populate modal with voucher data
                document.getElementById('editVoucherId').value = data.voucherID;
                document.getElementById('editVoucherCode').value = data.voucherCode;
                document.getElementById('editVoucherAmount').value = data.voucherAmount;
                document.getElementById('editVoucherType').value = data.voucherType;
                
                // Adjust for datetime-local format (YYYY-MM-DDTHH:MM)
                document.getElementById('editValidFrom').value = data.validFrom.replace(' ', 'T');
                document.getElementById('editValidUntil').value = data.validUntil.replace(' ', 'T');
                
                document.getElementById('editVoucherDescription').value = data.conditions || '';
                
                // Show modal
                document.getElementById('editModal').classList.remove('hidden');
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to load voucher data');
            });
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }

    // Close modal when clicking outside
    document.getElementById('editModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeEditModal();
        }
    });
</script>

{{-- EDIT VOUCHER MODAL --}}
<div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-900">Edit Voucher</h3>
            <button onclick="closeEditModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <form id="editVoucherForm" method="POST" class="p-6 space-y-4">
            @csrf
            @method('PUT')
            
            <input type="hidden" id="editVoucherId" name="id">

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Voucher Code</label>
                <input type="text" id="editVoucherCode" name="code" required 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Type</label>
                <select id="editVoucherType" name="type" required 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                    <option value="Rental Discount">Rental Discount</option>
                    <option value="Merchant Reward">Merchant Reward</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Amount (RM)</label>
                <input type="number" id="editVoucherAmount" name="amount" step="0.01" required 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Valid From</label>
                    <input type="datetime-local" id="editValidFrom" name="valid_from" required 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Valid Until</label>
                    <input type="datetime-local" id="editValidUntil" name="valid_until" required 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Description (Optional)</label>
                <textarea id="editVoucherDescription" name="description" rows="3" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"></textarea>
            </div>

            <div class="flex gap-3 pt-4">
                <button type="button" onclick="closeEditModal()" 
                    class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-semibold">
                    Cancel
                </button>
                <button type="submit" 
                    class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold">
                    Update Voucher
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Update form action when editing
    document.getElementById('editVoucherForm')?.addEventListener('submit', function(e) {
        const voucherId = document.getElementById('editVoucherId').value;
        this.action = `/staff/loyalty/voucher/${voucherId}`;
    });
</script>
@endsection