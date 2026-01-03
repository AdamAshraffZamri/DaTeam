@extends('layouts.staff')

@section('content')
<div class="min-h-screen bg-gray-100 rounded-2xl p-6">
    <div class="max-w-5xl mx-auto">
        
        {{-- BACK BUTTON --}}
        <a href="{{ route('staff.loyalty.index') }}" class="inline-flex items-center text-gray-500 hover:text-gray-900 mb-6 transition">
            <i class="fas fa-arrow-left mr-2"></i> Back to Loyalty Management
        </a>

        {{-- CUSTOMER HEADER --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-6">
                    <div class="w-20 h-20 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-3xl">
                        {{ substr($customer->name, 0, 1) }}
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">{{ $customer->name }}</h1>
                        <p class="text-gray-500">{{ $customer->email }}</p>
                        <p class="text-sm text-gray-400 mt-1">ID: {{ $customer->customerID }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-4xl font-bold text-indigo-600">{{ number_format($loyalty->points) }}</div>
                    <p class="text-gray-500 text-sm">Total Points</p>
                    <div class="mt-3">
                        <span class="px-4 py-2 rounded-full text-sm font-bold
                            {{ $loyalty->tier == 'Platinum' ? 'bg-cyan-100 text-cyan-700' : ($loyalty->tier == 'Gold' ? 'bg-yellow-100 text-yellow-700' : ($loyalty->tier == 'Silver' ? 'bg-slate-100 text-slate-700' : 'bg-amber-100 text-amber-700')) }}">
                            {{ $loyalty->tier }} Tier
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- STATS ROW --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <p class="text-gray-500 text-sm font-medium">Total Bookings</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ $bookingCount }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <p class="text-gray-500 text-sm font-medium">Total Spent</p>
                <p class="text-3xl font-bold text-indigo-600 mt-2">RM {{ number_format($totalSpent, 2) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <p class="text-gray-500 text-sm font-medium">Vouchers</p>
                <p class="text-3xl font-bold text-green-600 mt-2">{{ $vouchers->count() }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            
            {{-- LEFT: LOYALTY HISTORY --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center">
                    <i class="fas fa-history text-blue-600 mr-3"></i> Loyalty Points History
                </h3>

                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @forelse($history as $record)
                        <div class="flex items-start justify-between p-3 rounded-lg border border-gray-100 hover:bg-gray-50 transition">
                            <div class="flex-1">
                                <p class="text-sm font-bold text-gray-900">{{ $record->reason }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ $record->created_at->format('M d, Y H:i') }}</p>
                            </div>
                            <span class="font-bold text-lg {{ $record->points_change > 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $record->points_change > 0 ? '+' : '' }}{{ number_format($record->points_change) }}
                            </span>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-400">
                            <p class="text-sm">No loyalty history</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- RIGHT: VOUCHERS --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center">
                    <i class="fas fa-ticket-alt text-purple-600 mr-3"></i> Assigned Vouchers
                </h3>

                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @forelse($vouchers as $voucher)
                        <div class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                            <div class="flex items-start justify-between mb-2">
                                <div>
                                    <p class="font-bold text-gray-900">{{ $voucher->code }}</p>
                                    <p class="text-xs text-gray-500 mt-1">{{ $voucher->conditions ?? 'No description' }}</p>
                                </div>
                                <span class="text-xs font-bold px-2 py-1 rounded-full 
                                    {{ $voucher->voucherType == 'Rental Discount' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                                    {{ $voucher->voucherType }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">
                                    <i class="fas fa-calendar-alt mr-1"></i> {{ $voucher->validUntil?->format('M d, Y') ?? 'N/A' }}
                                </span>
                                <span class="font-bold px-2 py-1 rounded-full {{ $voucher->isUsed ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                                    {{ $voucher->isUsed ? 'Used' : 'Active' }}
                                </span>
                            </div>
                            @if($voucher->voucherType == 'Rental Discount')
                                <p class="text-indigo-600 font-bold mt-2">RM {{ number_format($voucher->voucherAmount, 2) }} Discount</p>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-400">
                            <p class="text-sm">No vouchers assigned</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
