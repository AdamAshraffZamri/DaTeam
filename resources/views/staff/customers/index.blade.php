@extends('layouts.staff')

@section('content')
<div class="min-h-screen bg-slate-100 rounded-2xl p-6">
    <div class="max-w-7xl mx-auto">

        {{-- HEADER & ACTIONS --}}
        <div class="flex flex-col xl:flex-row justify-between items-end xl:items-center mb-8 gap-4">
            <div class="w-full xl:w-auto">
                <h1 class="text-3xl font-black text-gray-900">Customer Database</h1>
                <p class="text-gray-500 mt-1 text-sm">Manage user profiles, verification status, and history.</p>
            </div>

            {{-- SEARCH & FILTER FORM --}}
            <form action="{{ route('staff.customers.index') }}" method="GET" id="filterForm" class="flex flex-col md:flex-row items-center gap-3 w-full xl:w-auto">
                
                {{-- 1. SEARCH INPUT --}}
                <div class="relative group w-full md:w-72">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Search Name, ID, Email..." 
                           class="w-full pl-10 pr-4 py-3.5 rounded-2xl border border-gray-200 bg-white text-sm font-bold text-gray-700 focus:ring-2 focus:ring-gray-900 focus:border-transparent transition-all shadow-sm group-hover:border-gray-300">
                    <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 group-hover:text-gray-600 transition-colors"></i>
                </div>

                {{-- 2. STATUS DROPDOWN (UPDATED WITH COUNTS) --}}
                @php
                    $currentStatus = request('status', 'all');
                    $statuses = [
                        'all'         => 'All Status',
                        'approved'    => 'Approved',
                        'pending'     => 'Pending',
                        'rejected'    => 'Rejected',
                        'blacklisted' => 'Blacklisted'
                    ];
                    $currentLabel = $statuses[$currentStatus] ?? 'All Status';

                    // Helper to get counts (Direct Model Query to ensure accuracy across pages)
                    $getCount = function($status) {
                        $query = \App\Models\Customer::query();
                        return match($status) {
                            'all'         => $query->count(),
                            'blacklisted' => $query->where('blacklisted', 1)->count(),
                            'approved'   => $query->whereIn('accountStat', ['approved', 'active'])->count(),
                            default       => $query->where('accountStat', $status)->count(),
                        };
                    };

                    $currentCount = $getCount($currentStatus);
                @endphp

                <input type="hidden" name="status" id="statusInput" value="{{ $currentStatus }}">

                <div class="relative w-full md:w-[200px]" id="customDropdown">
                    <button type="button" onclick="toggleDropdown()" 
                        class="w-full flex items-center justify-between bg-white border border-gray-200 text-gray-700 text-xs font-bold py-3.5 px-5 rounded-2xl hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm group">
                        
                        <div class="flex items-center gap-2">
                            <i class="fas fa-filter text-orange-500"></i>
                            <span id="dropdownLabel" class="truncate">{{ $currentLabel }}</span>
                            
                            {{-- MAIN BUTTON BADGE --}}
                            <span class="flex items-center justify-center w-5 h-5 rounded-full text-[9px] bg-orange-100 text-orange-700 ml-1">
                                {{ $currentCount }}
                            </span>
                        </div>
                        <i class="fas fa-chevron-down text-[10px] text-gray-400 group-hover:text-gray-600 transition-transform duration-300" id="dropdownArrow"></i>
                    </button>

                    <div id="dropdownMenu" 
                        class="absolute top-full right-0 mt-2 w-full bg-white border border-gray-100 rounded-2xl shadow-xl overflow-hidden hidden transform origin-top transition-all duration-200 z-50">
                        
                        @foreach($statuses as $value => $label)
                            @php $count = $getCount($value); @endphp
                            <div onclick="selectStatus('{{ $value }}')" 
                                 class="px-5 py-3 text-xs font-bold cursor-pointer transition-colors flex items-center justify-between border-b border-gray-50 last:border-0
                                 {{ $currentStatus == $value ? 'bg-orange-50 text-orange-600' : 'text-gray-600 hover:bg-gray-50' }}">
                                
                                <span>{{ $label }}</span>
                                
                                {{-- DROPDOWN LIST BADGE --}}
                                <span class="flex items-center justify-center w-5 h-5 rounded-full text-[9px] {{ $currentStatus == $value ? 'bg-orange-200 text-orange-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $count }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </form>
        </div>

        {{-- CUSTOMER LIST (CARD STYLE) --}}
        <div class="space-y-3">
            @forelse($customers as $customer)
            <div class="booking-row bg-white rounded-xl p-4 border border-gray-100 shadow-sm hover:shadow-md transition-all group cursor-pointer hover:border-orange-200 animate-fade-in" 
                 onclick="window.location='{{ route('staff.customers.show', $customer->customerID) }}'">
                
                <div class="flex flex-col lg:flex-row items-center">
                    
                    {{-- 1. AVATAR & NAME --}}
                    <div class="flex items-center gap-4 w-full lg:w-[30%] shrink-0">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-orange-100 to-orange-200 flex items-center justify-center text-orange-600 font-black text-lg shadow-inner border border-white shrink-0">
                            {{ substr($customer->fullName, 0, 1) }}
                        </div>
                        <div class="overflow-hidden">
                            <h4 class="text-sm font-bold text-gray-900 truncate group-hover:text-orange-600 transition-colors" title="{{ $customer->fullName }}">
                                {{ $customer->fullName }}
                            </h4>
                            <p class="text-[10px] font-bold text-gray-400 mt-0.5 uppercase tracking-wider">
                                ID: {{ $customer->stustaffID ?? 'N/A' }}
                            </p>
                        </div>
                    </div>

                    {{-- 2. ROLE & FACULTY --}}
                    <div class="w-full lg:w-[20%] border-t lg:border-t-0 lg:border-l border-gray-100 pt-2 lg:pt-0 lg:pl-6 shrink-0">
                        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-1">Faculty</p>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-100">
                            {{ $customer->faculty ?? 'General' }}
                        </span>
                    </div>

                    {{-- 3. CONTACT INFO --}}
                    <div class="w-full lg:w-[25%] border-t lg:border-t-0 lg:border-l border-gray-100 pt-2 lg:pt-0 lg:pl-6 shrink-0">
                        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-1">Contact</p>
                        <div class="flex flex-col gap-0.5">
                            <div class="flex items-center text-[11px] font-medium text-gray-600 truncate">
                                <i class="fas fa-envelope w-4 text-gray-300"></i> {{ $customer->email }}
                            </div>
                            <div class="flex items-center text-[11px] font-medium text-gray-600">
                                <i class="fas fa-phone w-4 text-gray-300"></i> {{ $customer->phoneNo ?? '-' }}
                            </div>
                        </div>
                    </div>

                    {{-- 4. STATUS --}}
                    <div class="w-full lg:w-[15%] border-t lg:border-t-0 lg:border-l border-gray-100 pt-2 lg:pt-0 lg:pl-6 shrink-0">
                        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-1">Status</p>
                        
                        @if($customer->blacklisted)
                            <span class="block w-24 text-center px-0 py-1.5 rounded-full text-[10px] font-bold border uppercase tracking-wider bg-gray-900 text-white border-gray-800 shadow-sm">
                                Blacklisted
                            </span>
                        @elseif($customer->accountStat == 'Approved' || $customer->accountStat == 'active')
                            <span class="block w-24 text-center px-0 py-1.5 rounded-full text-[10px] font-bold border uppercase tracking-wider bg-green-100 text-green-700 border-green-200">
                                Approved
                            </span>
                        @elseif($customer->accountStat == 'pending')
                            <span class="block w-24 text-center px-0 py-1.5 rounded-full text-[10px] font-bold border uppercase tracking-wider bg-orange-100 text-orange-700 border-orange-200 animate-pulse">
                                Pending
                            </span>
                        @elseif($customer->accountStat == 'rejected')
                            <span class="block w-24 text-center px-0 py-1.5 rounded-full text-[10px] font-bold border uppercase tracking-wider bg-red-100 text-red-700 border-red-200">
                                Rejected
                            </span>
                        @else
                            <span class="block w-24 text-center px-0 py-1.5 rounded-full text-[10px] font-bold border uppercase tracking-wider bg-gray-100 text-gray-500 border-gray-200">
                                Unverified
                            </span>
                        @endif
                    </div>

                    {{-- 5. ACTION --}}
                    <div class="w-full lg:flex-1 flex justify-end items-center gap-2 pt-2 lg:pt-0 border-t lg:border-t-0 border-gray-100 lg:pl-4">
                        <a href="{{ route('staff.customers.show', $customer->customerID) }}" 
                           class="w-8 h-8 rounded-full flex items-center justify-center bg-gray-50 text-gray-400 hover:bg-orange-100 hover:text-orange-600 transition-all border border-gray-200 hover:border-orange-200">
                            <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                    </div>

                </div>
            </div>
            @empty
            <div class="flex flex-col items-center justify-center py-20 text-center animate-fade-in">
                <div class="bg-gray-50 rounded-full p-4 mb-4"><i class="fas fa-users text-gray-300 text-3xl"></i></div>
                <p class="text-gray-500 font-medium">No customers found.</p>
                @if(request('status') || request('search'))
                    <a href="{{ route('staff.customers.index') }}" class="text-orange-500 font-bold text-xs mt-2 hover:underline">Clear Filters</a>
                @endif
            </div>
            @endforelse
        </div>

        {{-- PAGINATION --}}
        <div class="mt-6 px-4">
            {{ $customers->links() }}
        </div>

    </div>
</div>

{{-- ANIMATION STYLES --}}
<style>
    @keyframes fade-in { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
    .animate-fade-in { animation: fade-in 0.2s ease-out forwards; }
    .animate-fade-in-down { animation: fade-in 0.15s ease-out forwards; }
</style>

{{-- DROPDOWN SCRIPT --}}
<script>
    function toggleDropdown() {
        const menu = document.getElementById('dropdownMenu');
        const arrow = document.getElementById('dropdownArrow');
        
        if (menu.classList.contains('hidden')) {
            menu.classList.remove('hidden');
            menu.classList.add('animate-fade-in-down');
            if(arrow) arrow.style.transform = 'rotate(180deg)';
        } else {
            menu.classList.add('hidden');
            if(arrow) arrow.style.transform = 'rotate(0deg)';
        }
    }

    function selectStatus(value) {
        document.getElementById('statusInput').value = value;
        document.getElementById('filterForm').submit();
    }

    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('customDropdown');
        const menu = document.getElementById('dropdownMenu');
        const arrow = document.getElementById('dropdownArrow');

        if (dropdown && !dropdown.contains(event.target)) {
            menu.classList.add('hidden');
            if(arrow) arrow.style.transform = 'rotate(0deg)';
        }
    });
</script>
@endsection