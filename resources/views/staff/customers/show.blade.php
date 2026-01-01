@extends('layouts.staff')

@section('title', 'Customer Details')

@section('content')
<div x-data="{ showRejectModal: false }" class="min-h-screen bg-gray-100 p-8">
    <div class="max-w-6xl mx-auto">
        
        <div class="flex justify-between items-center mb-6">
            <a href="{{ route('staff.customers.index') }}" class="inline-flex items-center text-xs font-bold text-gray-500 hover:text-orange-600 uppercase tracking-widest transition">
                <i class="fas fa-arrow-left mr-2"></i> Back to List
            </a>

            <div class="flex items-center gap-3">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Current Status:</span>
                @if($customer->accountStat == 'active')
                    <span class="bg-green-100 text-green-700 px-4 py-2 rounded-xl font-black text-xs uppercase tracking-widest shadow-sm border border-green-200">
                        <i class="fas fa-check-circle mr-2"></i> Verified
                    </span>
                @elseif($customer->accountStat == 'pending')
                    <span class="bg-yellow-100 text-yellow-700 px-4 py-2 rounded-xl font-black text-xs uppercase tracking-widest shadow-sm border border-yellow-200 animate-pulse">
                        <i class="fas fa-clock mr-2"></i> Pending Verification
                    </span>
                @elseif($customer->accountStat == 'rejected')
                    <span class="bg-red-100 text-red-700 px-4 py-2 rounded-xl font-black text-xs uppercase tracking-widest shadow-sm border border-red-200">
                        <i class="fas fa-times-circle mr-2"></i> Rejected
                    </span>
                @endif
            </div>
        </div>

        <div class="bg-gray-900 rounded-[1.5rem] p-6 mb-8 shadow-xl flex justify-between items-center text-white">
            <div>
                <h3 class="font-bold text-lg">Verification Actions</h3>
                <p class="text-gray-400 text-xs mt-1">Review the details below and decide.</p>
            </div>
            <div class="flex gap-3">
                <button @click="showRejectModal = true" type="button" class="bg-gray-800 hover:bg-red-600 text-white px-6 py-3 rounded-xl font-bold text-xs uppercase tracking-widest transition border border-gray-700 hover:border-red-500">
                    <i class="fas fa-times mr-2"></i> Reject
                </button>

                <form action="{{ route('staff.customers.approve', $customer->customerID) }}" method="POST">
                    @csrf
                    <button type="submit" onclick="return confirm('Confirm details are correct?')" class="bg-white text-gray-900 hover:bg-green-500 hover:text-white px-8 py-3 rounded-xl font-bold text-xs uppercase tracking-widest transition shadow-lg">
                        <i class="fas fa-check mr-2"></i> Verify & Approve
                    </button>
                </form>
            </div>
        </div>

        @if($customer->rejection_reason)
        <div class="bg-red-50 border border-red-100 rounded-[1.5rem] p-6 mb-8">
            <h4 class="text-red-800 font-bold text-sm uppercase tracking-widest mb-2"><i class="fas fa-exclamation-triangle mr-2"></i> Previous Rejection Reason</h4>
            <p class="text-red-600 text-sm font-medium">{{ $customer->rejection_reason }}</p>
        </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100 text-center relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-24 bg-gradient-to-r from-orange-500 to-orange-600"></div>
                    <div class="relative mt-8 mb-4">
                        <div class="w-24 h-24 mx-auto bg-white rounded-full p-1 shadow-lg">
                            <div class="w-full h-full rounded-full bg-gray-100 flex items-center justify-center text-3xl font-black text-gray-300">
                                {{ substr($customer->fullName, 0, 1) }}
                            </div>
                        </div>
                    </div>
                    <h2 class="text-xl font-black text-gray-900">{{ $customer->fullName }}</h2>
                    <p class="text-sm font-medium text-gray-500 mb-6">{{ $customer->email }}</p>
                    
                    <form action="{{ route('staff.customers.blacklist', $customer->customerID) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full py-3 rounded-xl text-xs font-bold uppercase tracking-widest transition-all
                            {{ $customer->blacklisted ? 'bg-gray-900 text-white hover:bg-gray-800' : 'bg-red-50 text-red-600 hover:bg-red-100' }}">
                            {{ $customer->blacklisted ? 'Remove Blacklist' : 'Blacklist User' }}
                        </button>
                    </form>
                </div>

                <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100">
                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-6">Contact Information</h3>
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <div class="w-8 flex-shrink-0 text-center"><i class="fas fa-phone text-gray-300"></i></div>
                            <div>
                                <p class="text-xs text-gray-400 uppercase font-bold">Phone Number</p>
                                <p class="text-sm font-bold text-gray-900">{{ $customer->phoneNo }}</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="w-8 flex-shrink-0 text-center"><i class="fas fa-university text-gray-300"></i></div>
                            <div>
                                <p class="text-xs text-gray-400 uppercase font-bold">Faculty</p>
                                <p class="text-sm font-bold text-gray-900">{{ $customer->faculty }}</p>
                            </div>
                        </div>
                         <div class="flex items-start">
                            <div class="w-8 flex-shrink-0 text-center"><i class="fas fa-map-marker-alt text-gray-300"></i></div>
                            <div>
                                <p class="text-xs text-gray-400 uppercase font-bold">Addresses</p>
                                <p class="text-xs font-medium text-gray-600 mb-1"><span class="font-bold">Home:</span> {{ $customer->homeAddress }}</p>
                                <p class="text-xs font-medium text-gray-600"><span class="font-bold">College:</span> {{ $customer->collegeAddress }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2 space-y-6">
                
                <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100">
                    <div class="flex justify-between items-start mb-6">
                         <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest">Identity & License</h3>
                         <span class="text-orange-500 text-xl"><i class="fas fa-id-card"></i></span>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-6">
                        <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100">
                            <p class="text-xs text-gray-400 uppercase font-bold mb-1">IC / Passport No</p>
                            <p class="text-lg font-black text-gray-900">{{ $customer->ic_passport ?? 'Not Provided' }}</p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100">
                            <p class="text-xs text-gray-400 uppercase font-bold mb-1">Driving License No</p>
                            <p class="text-lg font-black text-gray-900">{{ $customer->drivingNo }}</p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100">
                            <p class="text-xs text-gray-400 uppercase font-bold mb-1">Student / Staff ID</p>
                            <p class="text-base font-bold text-gray-900">{{ $customer->stustaffID }}</p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100">
                            <p class="text-xs text-gray-400 uppercase font-bold mb-1">Date of Birth</p>
                            <p class="text-base font-bold text-gray-900">{{ $customer->dob ? \Carbon\Carbon::parse($customer->dob)->format('d M Y') : 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100">
                    <div class="flex justify-between items-start mb-6">
                         <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest">Bank Details</h3>
                         <span class="text-blue-500 text-xl"><i class="fas fa-university"></i></span>
                    </div>
                    <div class="grid grid-cols-2 gap-6">
                        <div class="p-4 bg-blue-50 rounded-2xl border border-blue-100">
                            <p class="text-xs text-blue-400 uppercase font-bold mb-1">Bank Name</p>
                            <p class="text-lg font-black text-blue-900">{{ $customer->bankName ?? 'Not Provided' }}</p>
                        </div>
                        <div class="p-4 bg-blue-50 rounded-2xl border border-blue-100">
                            <p class="text-xs text-blue-400 uppercase font-bold mb-1">Account Number</p>
                            <p class="text-lg font-black text-blue-900">{{ $customer->bankAccountNo ?? 'Not Provided' }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100">
                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-6">Uploaded Documents</h3>
                    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
                        <a href="{{ $customer->driving_license_image ? asset('storage/'.$customer->driving_license_image) : '#' }}" target="_blank" class="group block relative h-32 rounded-xl overflow-hidden bg-gray-100 border border-gray-200">
                             <img src="{{ $customer->driving_license_image ? asset('storage/'.$customer->driving_license_image) : asset('images/placeholder.png') }}" class="w-full h-full object-cover group-hover:opacity-75 transition">
                             <div class="absolute bottom-0 left-0 right-0 bg-black/50 p-2 text-center">
                                 <span class="text-[10px] text-white font-bold uppercase">Driving License</span>
                             </div>
                        </a>
                        <a href="{{ $customer->student_card_image ? asset('storage/'.$customer->student_card_image) : '#' }}" target="_blank" class="group block relative h-32 rounded-xl overflow-hidden bg-gray-100 border border-gray-200">
                             <img src="{{ $customer->student_card_image ? asset('storage/'.$customer->student_card_image) : asset('images/placeholder.png') }}" class="w-full h-full object-cover group-hover:opacity-75 transition">
                             <div class="absolute bottom-0 left-0 right-0 bg-black/50 p-2 text-center">
                                 <span class="text-[10px] text-white font-bold uppercase">Student ID</span>
                             </div>
                        </a>
                        <a href="{{ $customer->ic_passport_image ? asset('storage/'.$customer->ic_passport_image) : '#' }}" target="_blank" class="group block relative h-32 rounded-xl overflow-hidden bg-gray-100 border border-gray-200">
                             <img src="{{ $customer->ic_passport_image ? asset('storage/'.$customer->ic_passport_image) : asset('images/placeholder.png') }}" class="w-full h-full object-cover group-hover:opacity-75 transition">
                             <div class="absolute bottom-0 left-0 right-0 bg-black/50 p-2 text-center">
                                 <span class="text-[10px] text-white font-bold uppercase">IC / Passport</span>
                             </div>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div x-show="showRejectModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" style="display: none;">
        <div @click.away="showRejectModal = false" class="bg-white rounded-[2rem] p-8 max-w-lg w-full shadow-2xl transform transition-all">
            <h3 class="text-2xl font-black text-gray-900 mb-2">Reject Customer</h3>
            <p class="text-gray-500 text-sm mb-6">Please specify which details are incorrect. This message will be sent to the customer.</p>
            
            <form action="{{ route('staff.customers.reject', $customer->customerID) }}" method="POST">
                @csrf
                <div class="mb-6">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-widest mb-2">Rejection Reason</label>
                    <textarea name="rejection_reason" rows="4" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-4 text-sm font-medium focus:ring-2 focus:ring-red-500 focus:outline-none" placeholder="e.g., Driving license image is blurry, Bank account name does not match..."></textarea>
                </div>
                
                <div class="flex justify-end gap-3">
                    <button type="button" @click="showRejectModal = false" class="px-6 py-3 rounded-xl font-bold text-xs uppercase tracking-widest text-gray-500 hover:bg-gray-100 transition">Cancel</button>
                    <button type="submit" class="bg-red-600 text-white px-8 py-3 rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-red-700 shadow-lg shadow-red-500/30 transition">Confirm Reject</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection