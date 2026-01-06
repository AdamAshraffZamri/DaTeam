@extends('layouts.staff')

@section('content')
<div class="min-h-screen bg-gray-100 rounded-2xl p-6">
    <div class="max-w-6xl mx-auto">
        
        {{-- BACK BUTTON --}}
        <a href="{{ route('staff.loyalty.index') }}" class="inline-flex items-center text-gray-500 hover:text-gray-900 mb-6 transition font-medium">
            <i class="fas fa-arrow-left mr-2"></i> Back to List
        </a>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            {{-- LEFT COLUMN: PROFILE & LOYALTY ROAD --}}
            <div class="space-y-8">
                
                {{-- 1. CUSTOMER PROFILE CARD --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 text-center relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-24 bg-gradient-to-r from-blue-500 to-indigo-600"></div>
                    <div class="relative z-10 -mt-2">
                        <div class="w-24 h-24 mx-auto rounded-full bg-white p-1 shadow-lg">
                            <div class="w-full h-full rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-black text-3xl">
                                {{ substr($customer->fullName, 0, 1) }}
                            </div>
                        </div>
                        <h1 class="text-xl font-bold text-gray-900 mt-4">{{ $customer->fullName }}</h1>
                        <p class="text-sm text-gray-500">{{ $customer->email }}</p>
                        <div class="mt-4 flex justify-center gap-2">
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                {{ $loyalty->tier }} Member
                            </span>
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-600">
                                ID: #{{ $customer->customerID }}
                            </span>
                        </div>
                        <div class="mt-6 border-t border-gray-100 pt-6">
                            <div class="text-3xl font-black text-indigo-600">{{ number_format($loyalty->points) }}</div>
                            <p class="text-xs text-gray-400 font-bold uppercase tracking-wider">Current Points</p>
                        </div>
                    </div>
                </div>

                {{-- 2. LOYALTY ROAD PROGRESS (NEW!) --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-2 flex items-center">
                        <i class="fas fa-road text-purple-500 mr-2"></i> Loyalty Road
                    </h3>
                    <p class="text-xs text-gray-500 mb-6">Tracking qualified bookings (>9 hours).</p>

                    <div class="text-center mb-4">
                        <span class="text-4xl font-black text-purple-600">{{ $currentInCycle }}</span>
                        <span class="text-gray-400 text-xl font-bold">/ 12</span>
                    </div>

                    {{-- Progress Bar --}}
                    <div class="w-full bg-gray-100 rounded-full h-4 mb-2 overflow-hidden">
                        <div class="bg-gradient-to-r from-purple-500 to-pink-500 h-4 rounded-full transition-all duration-1000" 
                             style="width: {{ ($currentInCycle / 12) * 100 }}%"></div>
                    </div>
                    
                    {{-- Milestones Indicators --}}
                    <div class="flex justify-between text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                        <span class="{{ $currentInCycle >= 3 ? 'text-green-500' : '' }}">Step 3</span>
                        <span class="{{ $currentInCycle >= 6 ? 'text-green-500' : '' }}">Step 6</span>
                        <span class="{{ $currentInCycle >= 9 ? 'text-green-500' : '' }}">Step 9</span>
                        <span class="{{ $currentInCycle >= 12 ? 'text-green-500' : '' }}">Step 12</span>
                    </div>

                    <div class="mt-6 bg-purple-50 rounded-lg p-3 text-xs text-purple-800 border border-purple-100">
                        <strong>Next Milestone Reward:</strong>
                        @if($currentInCycle < 3) 20% OFF (at Step 3)
                        @elseif($currentInCycle < 6) 50% OFF (at Step 6)
                        @elseif($currentInCycle < 9) 70% OFF (at Step 9)
                        @else Free Half Day (at Step 12)
                        @endif
                    </div>
                </div>

                {{-- 3. QUICK STATS --}}
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 text-center">
                        <p class="text-xs text-gray-500 font-bold uppercase">Total Spent</p>
                        <p class="text-lg font-bold text-gray-900">RM {{ number_format($totalSpent) }}</p>
                    </div>
                    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 text-center">
                        <p class="text-xs text-gray-500 font-bold uppercase">Total Trips</p>
                        <p class="text-lg font-bold text-gray-900">{{ $bookingCount }}</p>
                    </div>
                </div>

            </div>

            {{-- RIGHT COLUMN: HISTORY & VOUCHERS --}}
            <div class="lg:col-span-2 space-y-8">
                
                {{-- VOUCHERS LIST --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center">
                            <i class="fas fa-ticket-alt text-green-500 mr-2"></i> Assigned Vouchers
                        </h3>
                        <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-bold">{{ $vouchers->count() }} Total</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-96 overflow-y-auto pr-2">
                        @forelse($vouchers as $voucher)
                            <div class="p-4 border border-gray-200 rounded-xl hover:border-green-300 transition group bg-gray-50/50">
                                <div class="flex justify-between items-start mb-2">
                                    <span class="font-mono font-bold text-gray-900 text-lg tracking-wide">{{ $voucher->code }}</span>
                                    <span class="text-[10px] font-bold px-2 py-1 rounded-full uppercase
                                        {{ $voucher->isUsed ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-600' }}">
                                        {{ $voucher->isUsed ? 'Used' : 'Active' }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2 mb-3">
                                    <span class="text-xs font-bold px-2 py-0.5 rounded border 
                                        {{ $voucher->voucherType == 'Rental Discount' ? 'bg-blue-50 text-blue-600 border-blue-100' : 'bg-orange-50 text-orange-600 border-orange-100' }}">
                                        {{ $voucher->voucherType }}
                                    </span>
                                    @if($voucher->discount_percent > 0)
                                        <span class="text-xs font-bold text-green-600 bg-green-50 px-2 py-0.5 rounded border border-green-100">
                                            {{ $voucher->discount_percent }}% OFF
                                        </span>
                                    @elseif($voucher->voucherAmount > 0)
                                        <span class="text-xs font-bold text-green-600 bg-green-50 px-2 py-0.5 rounded border border-green-100">
                                            RM {{ $voucher->voucherAmount }} OFF
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-500 mb-1"><i class="far fa-clock mr-1"></i> Exp: {{ $voucher->validUntil?->format('d M Y') ?? 'N/A' }}</p>
                                <p class="text-xs text-gray-400 italic truncate">{{ $voucher->conditions ?? 'No conditions' }}</p>
                            </div>
                        @empty
                            <div class="col-span-2 text-center py-8 text-gray-400">
                                <i class="fas fa-ticket-alt text-4xl mb-2 text-gray-200"></i>
                                <p class="text-sm">No vouchers assigned yet.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- HISTORY LOG --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center">
                        <i class="fas fa-history text-blue-500 mr-2"></i> Points History
                    </h3>

                    <div class="overflow-hidden rounded-xl border border-gray-200">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-gray-50 text-gray-500 font-bold uppercase text-xs">
                                <tr>
                                    <th class="px-4 py-3">Activity</th>
                                    <th class="px-4 py-3 text-center">Date</th>
                                    <th class="px-4 py-3 text-right">Points</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse($history as $record)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-4 py-3">
                                            <p class="font-medium text-gray-900">{{ $record->reason }}</p>
                                        </td>
                                        <td class="px-4 py-3 text-center text-gray-500 text-xs">
                                            {{ $record->created_at->format('d M Y, H:i') }}
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <span class="font-bold {{ $record->points_change > 0 ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $record->points_change > 0 ? '+' : '' }}{{ number_format($record->points_change) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-8 text-center text-gray-400">
                                            No history records found.
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
</div>
@endsection