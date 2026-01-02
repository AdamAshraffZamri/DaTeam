@extends('layouts.staff')

@section('title', 'Customers')

@section('content')
<div class="min-h-screen bg-gray-100 p-8">
    <div class="max-w-7xl mx-auto space-y-8">
        
        <div class="flex justify-between items-end">
            <div>
                <h1 class="text-4xl font-black text-gray-900 tracking-tight">Customer Database</h1>
                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-[0.3em] mt-2">Manage Users & Verification</p>
            </div>
            
            <form action="{{ route('staff.customers.index') }}" method="GET" class="relative">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Search name, ID or email..." 
                       class="bg-white pl-12 pr-6 py-4 rounded-2xl shadow-sm border-none w-80 text-sm font-bold focus:ring-2 focus:ring-orange-500">
                <i class="fas fa-search absolute left-5 top-1/2 -translate-y-1/2 text-gray-400"></i>
            </form>
        </div>

        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100">
                        <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Profile</th>
                        <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Role & Faculty</th>
                        <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Contact</th>
                        <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                        <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($customers as $customer)
                    <tr class="hover:bg-gray-50/50 transition-colors group">
                        <td class="px-8 py-6">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-orange-100 to-orange-200 flex items-center justify-center text-orange-600 font-bold text-lg shadow-inner">
                                    {{ substr($customer->fullName, 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-black text-gray-900 leading-none">{{ $customer->fullName }}</p>
                                    <p class="text-[10px] font-bold text-gray-400 mt-1 uppercase tracking-wider">
                                        ID: {{ $customer->stustaffID }}
                                    </p>
                                </div>
                            </div>
                        </td>

                        <td class="px-8 py-6">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700">
                                {{ $customer->faculty ?? 'General' }}
                            </span>
                        </td>

                        <td class="px-8 py-6">
                            <div class="flex flex-col space-y-1">
                                <div class="flex items-center text-xs font-medium text-gray-600">
                                    <i class="fas fa-envelope w-4 text-gray-300"></i> {{ $customer->email }}
                                </div>
                                <div class="flex items-center text-xs font-medium text-gray-600">
                                    <i class="fas fa-phone w-4 text-gray-300"></i> {{ $customer->phoneNo }}
                                </div>
                            </div>
                        </td>

                        <td class="px-8 py-6">
                            @if($customer->blacklisted)
                                <span class="bg-red-100 text-red-600 px-3 py-1 rounded-lg font-bold text-[10px] uppercase tracking-wider">
                                    Blacklisted
                                </span>
                            @elseif($customer->accountStat == 'active')
                                <span class="bg-green-100 text-green-600 px-3 py-1 rounded-lg font-bold text-[10px] uppercase tracking-wider">
                                    Active
                                </span>
                            @else
                                <span class="bg-gray-100 text-gray-500 px-3 py-1 rounded-lg font-bold text-[10px] uppercase tracking-wider">
                                    Pending
                                </span>
                            @endif
                        </td>

                        <td class="px-8 py-6 text-right space-x-2">
                            <a href="{{ route('staff.customers.show', $customer->customerID) }}" 
                               class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-white border border-gray-200 text-gray-400 hover:text-orange-600 hover:border-orange-600 transition-all shadow-sm">
                                <i class="fas fa-arrow-right text-xs"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-8 py-12 text-center text-gray-400 font-medium">
                            No customers found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            
            <div class="px-8 py-6 border-t border-gray-100">
                {{ $customers->links() }}
            </div>
        </div>
    </div>
</div>
@endsection