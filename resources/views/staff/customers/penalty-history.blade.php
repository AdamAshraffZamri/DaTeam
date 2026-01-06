@extends('layouts.staff')

@section('title', 'Penalty History')

@section('content')
<div class="min-h-screen bg-gray-100 p-8">
    <div class="max-w-7xl mx-auto">
        
        {{-- HEADER --}}
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center gap-4">
                <a href="{{ route('staff.customers.show', $customer->customerID) }}" class="inline-flex items-center text-xs font-bold text-gray-500 hover:text-orange-600 uppercase tracking-widest transition">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Customer
                </a>
                <div class="h-6 w-px bg-gray-300"></div>
                <div>
                    <h1 class="text-2xl font-black text-gray-900">Penalty History</h1>
                    <p class="text-sm text-gray-500">{{ $customer->fullName }}</p>
                </div>
            </div>
        </div>

        {{-- STATISTICS CARDS --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Total Penalties</p>
                        <h3 class="text-3xl font-black text-gray-900">{{ $totalPenalties }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-orange-100 flex items-center justify-center text-orange-600">
                        <i class="fas fa-exclamation-triangle text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Unpaid Penalties</p>
                        <h3 class="text-3xl font-black text-red-600">{{ $unpaidPenalties }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-red-100 flex items-center justify-center text-red-600">
                        <i class="fas fa-clock text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Total Outstanding</p>
                        <h3 class="text-3xl font-black text-gray-900">RM {{ number_format($totalAmount, 2) }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-gray-100 flex items-center justify-center text-gray-600">
                        <i class="fas fa-money-bill-wave text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- PENALTY LIST --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <h2 class="text-lg font-black text-gray-900">All Penalties</h2>
                <p class="text-sm text-gray-500 mt-1">Complete history of penalties for this customer</p>
            </div>

            @if($penalties->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Date</th>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Type</th>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Reason</th>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Booking</th>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Amount</th>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($penalties as $penalty)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div>
                                            <p class="text-sm font-bold text-gray-900">
                                                {{ \Carbon\Carbon::parse($penalty->date_imposed)->format('d M Y') }}
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                {{ \Carbon\Carbon::parse($penalty->date_imposed)->format('h:i A') }}
                                            </p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($penalty->bookingID)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700">
                                                <i class="fas fa-calendar-alt mr-1.5"></i> Booking
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-orange-100 text-orange-700">
                                                <i class="fas fa-user-shield mr-1.5"></i> Customer
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-medium text-gray-900 max-w-xs truncate" title="{{ $penalty->reason }}">
                                            {{ $penalty->reason ?? 'N/A' }}
                                        </p>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($penalty->bookingID && $penalty->booking)
                                            <a href="{{ route('staff.bookings.show', $penalty->bookingID) }}" class="text-sm font-bold text-blue-600 hover:text-blue-800 hover:underline">
                                                #{{ $penalty->bookingID }}
                                            </a>
                                            @if($penalty->booking->vehicle)
                                                <p class="text-xs text-gray-500 mt-0.5">
                                                    {{ $penalty->booking->vehicle->vehicleModel ?? 'N/A' }}
                                                </p>
                                            @endif
                                        @else
                                            <span class="text-sm text-gray-400 italic">N/A</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $totalAmount = $penalty->amount ?? ($penalty->penaltyFees + $penalty->fuelSurcharge + $penalty->mileageSurcharge);
                                        @endphp
                                        <p class="text-sm font-black text-gray-900">RM {{ number_format($totalAmount, 2) }}</p>
                                        @if($penalty->bookingID && ($penalty->fuelSurcharge > 0 || $penalty->mileageSurcharge > 0))
                                            <p class="text-xs text-gray-500 mt-0.5">
                                                @if($penalty->penaltyFees > 0)Penalty: RM{{ number_format($penalty->penaltyFees, 2) }}@endif
                                                @if($penalty->fuelSurcharge > 0) | Fuel: RM{{ number_format($penalty->fuelSurcharge, 2) }}@endif
                                                @if($penalty->mileageSurcharge > 0) | Mileage: RM{{ number_format($penalty->mileageSurcharge, 2) }}@endif
                                            </p>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $isPaid = ($penalty->status == 'Paid' || $penalty->penaltyStatus == 'Paid');
                                        @endphp
                                        @if($isPaid)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">
                                                <i class="fas fa-check-circle mr-1.5"></i> Paid
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700">
                                                <i class="fas fa-exclamation-circle mr-1.5"></i> Unpaid
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- PAGINATION --}}
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $penalties->links() }}
                </div>
            @else
                <div class="p-12 text-center">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                        <i class="fas fa-check-circle text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">No Penalties Found</h3>
                    <p class="text-sm text-gray-500">This customer has no penalty records.</p>
                </div>
            @endif
        </div>

    </div>
</div>
@endsection

