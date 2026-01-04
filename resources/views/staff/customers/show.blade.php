@extends('layouts.staff')

@section('title', 'Customer Details')

@section('content')
<div x-data="{ 
    showRejectModal: false, 
    showBlacklistModal: false, 
    showPenaltyModal: false 
}" class="min-h-screen bg-gray-100 p-8">
    <div class="max-w-6xl mx-auto">
        
        <div class="flex justify-between items-center mb-6">
            <a href="{{ route('staff.customers.index') }}" class="inline-flex items-center text-xs font-bold text-gray-500 hover:text-orange-600 uppercase tracking-widest transition">
                <i class="fas fa-arrow-left mr-2"></i> Back to List
            </a>

            <div class="flex items-center gap-3">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Current Status:</span>
                
                @if($customer->blacklisted)
                    <span class="bg-gray-900 text-white px-4 py-2 rounded-xl font-black text-xs uppercase tracking-widest shadow-lg shadow-gray-900/20">
                        <i class="fas fa-ban mr-2"></i> Blacklisted
                    </span>
                @elseif($customer->accountStat == 'approved' || $customer->accountStat == 'active')
                    <span class="bg-green-100 text-green-700 px-4 py-2 rounded-xl font-black text-xs uppercase tracking-widest shadow-sm border border-green-200">
                        <i class="fas fa-check-circle mr-2"></i> Approved
                    </span>
                @elseif($customer->accountStat == 'pending')
                    <span class="bg-yellow-100 text-yellow-700 px-4 py-2 rounded-xl font-black text-xs uppercase tracking-widest shadow-sm border border-yellow-200 animate-pulse">
                        <i class="fas fa-clock mr-2"></i> Pending Verification
                    </span>
                @elseif($customer->accountStat == 'rejected')
                    <span class="bg-red-100 text-red-700 px-4 py-2 rounded-xl font-black text-xs uppercase tracking-widest shadow-sm border border-red-200">
                        <i class="fas fa-times-circle mr-2"></i> Rejected
                    </span>
                @else
                    <span class="bg-gray-200 text-gray-600 px-4 py-2 rounded-xl font-black text-xs uppercase tracking-widest shadow-sm border border-gray-300">
                        <i class="fas fa-user-clock mr-2"></i> Unverified
                    </span>
                @endif
            </div>
        </div>

        @if($customer->blacklisted && $customer->blacklist_reason)
        <div class="bg-gray-800 border border-gray-700 rounded-[1.5rem] p-6 mb-8 text-white shadow-xl">
            <h4 class="text-gray-400 font-bold text-[10px] uppercase tracking-widest mb-2"><i class="fas fa-ban mr-2"></i> Reason for Blacklisting</h4>
            <p class="text-sm font-bold">{{ $customer->blacklist_reason }}</p>
        </div>
        @endif

        @if(!$customer->blacklisted && $customer->accountStat == 'rejected' && $customer->rejection_reason)
        <div class="bg-red-50 border border-red-100 rounded-[1.5rem] p-6 mb-8">
            <h4 class="text-red-800 font-bold text-[10px] uppercase tracking-widest mb-2"><i class="fas fa-exclamation-triangle mr-2"></i> Previous Rejection Reason</h4>
            <p class="text-red-600 text-sm font-bold">{{ $customer->rejection_reason }}</p>
        </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r shadow-sm">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-red-500"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-bold text-red-800">Action Failed</h3>
                        <div class="mt-2 text-sm text-red-700">
                            <ul class="list-disc pl-5 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            @endif

        <div class="bg-white rounded-[1.5rem] p-6 mb-8 shadow-sm border border-gray-100 flex justify-between items-center">
            <div>
                <h3 class="font-bold text-lg text-gray-900">Account Actions</h3>
                <p class="text-gray-400 text-xs mt-1">Review details and manage access.</p>
            </div>
            <div class="flex gap-3">
    
                @if($customer->blacklisted)
                    <form action="{{ route('staff.customers.blacklist', $customer->customerID) }}" method="POST" onsubmit="return confirm('Restore this user account?');">
                        @csrf
                        <button type="submit" class="bg-gray-900 text-white px-6 py-3 rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-gray-800 transition shadow-lg shadow-gray-900/20">
                            <i class="fas fa-unlock mr-2"></i> Remove Blacklist
                        </button>
                    </form>
                @else
                    <button @click="showPenaltyModal = true" type="button" class="bg-orange-100 text-orange-600 px-6 py-3 rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-orange-200 transition border border-orange-200">
                        <i class="fas fa-exclamation-triangle mr-2"></i> Penalty
                    </button>
                    <button @click="showBlacklistModal = true" type="button" class="bg-gray-100 text-gray-600 px-6 py-3 rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-gray-200 transition">
                        <i class="fas fa-ban mr-2"></i> Blacklist
                    </button>

                    {{-- REJECT BUTTON: Hide if Verified OR Already Rejected --}}
                    @if($customer->accountStat !== 'active' && $customer->accountStat !== 'approved' && $customer->accountStat !== 'rejected')
                        <button @click="showRejectModal = true" type="button" class="bg-red-50 text-red-600 px-6 py-3 rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-red-100 transition border border-red-100">
                            <i class="fas fa-times mr-2"></i> Reject
                        </button>
                    @endif

                    {{-- APPROVE BUTTON: Hide if already Verified (But KEEP if Rejected, so you can change your mind) --}}
                    @if($customer->accountStat !== 'active' && $customer->accountStat !== 'approved')
                        <form action="{{ route('staff.customers.approve', $customer->customerID) }}" method="POST">
                            @csrf
                            <button type="submit" onclick="return confirm('Confirm all details are correct?')" class="bg-green-500 text-white px-8 py-3 rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-green-600 transition shadow-lg shadow-green-500/30">
                                <i class="fas fa-check mr-2"></i> Approve User
                            </button>
                        </form>
                    @endif
                    
                @endif
            </div>
        </div>

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

                    <div class="flex justify-center">
                         <span class="px-4 py-1.5 bg-gray-50 text-gray-600 rounded-full text-[10px] font-bold uppercase tracking-wider border border-gray-100">
                            Joined {{ $customer->created_at->format('M Y') }}
                         </span>
                    </div>
                </div>

                <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100">
                    <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-6">Contact Information</h3>
                    <div class="space-y-6">
                        <div class="flex items-start">
                            <div class="w-8 flex-shrink-0 text-center"><i class="fas fa-phone text-gray-300"></i></div>
                            <div>
                                <p class="text-[10px] text-gray-400 uppercase font-bold">Phone Number</p>
                                <p class="text-sm font-bold text-gray-900">{{ $customer->phoneNo ?? 'Not Provided' }}</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="w-8 flex-shrink-0 text-center"><i class="fas fa-university text-gray-300"></i></div>
                            <div>
                                <p class="text-[10px] text-gray-400 uppercase font-bold">Faculty</p>
                                <p class="text-sm font-bold text-gray-900">{{ $customer->faculty ?? 'N/A' }}</p>
                            </div>
                        </div>
                         <div class="flex items-start">
                            <div class="w-8 flex-shrink-0 text-center"><i class="fas fa-map-marker-alt text-gray-300"></i></div>
                            <div>
                                <p class="text-[10px] text-gray-400 uppercase font-bold">Addresses</p>
                                <div class="mt-1 space-y-2">
                                    <div>
                                        <span class="text-[10px] font-bold text-orange-500 bg-orange-50 px-2 py-0.5 rounded">Home</span>
                                        <p class="text-xs font-medium text-gray-600 mt-0.5">{{ $customer->homeAddress ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-[10px] font-bold text-blue-500 bg-blue-50 px-2 py-0.5 rounded">College</span>
                                        <p class="text-xs font-medium text-gray-600 mt-0.5">{{ $customer->collegeAddress ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                         <div class="flex items-start">
                            <div class="w-8 flex-shrink-0 text-center">
                                <i class="fas fa-heartbeat text-gray-300"></i>
                            </div>
                            <div>
                                <p class="text-[10px] text-gray-400 uppercase font-bold">Emergency Contact</p>
                                
                                {{-- 1. CONTACT NAME (New) --}}
                                <p class="text-sm font-black text-gray-900">
                                    {{ $customer->emergency_contact_name ?? 'Name Not Provided' }}
                                </p>
                                
                                {{-- 2. CONTACT NUMBER --}}
                                {{-- Updated variable to match database: emergency_contact_no --}}
                                <p class="text-xs font-medium text-gray-500 mt-0.5">
                                    {{ $customer->emergency_contact_no ?? 'N/A' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2 space-y-6">
                
                <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100">
                    <div class="flex justify-between items-start mb-6">
                         <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Identity Verification</h3>
                         <span class="text-orange-500 text-xl"><i class="fas fa-id-card"></i></span>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100">
                            <p class="text-[10px] text-gray-400 uppercase font-bold mb-1">IC / Passport No</p>
                            <p class="text-lg font-black text-gray-900">{{ $customer->ic_passport ?? 'Not Provided' }}</p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100">
                            <p class="text-[10px] text-gray-400 uppercase font-bold mb-1">Driving License No</p>
                            <p class="text-lg font-black text-gray-900">{{ $customer->drivingNo ?? 'Not Provided' }}</p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100">
                            <p class="text-[10px] text-gray-400 uppercase font-bold mb-1">Student / Staff ID</p>
                            <p class="text-base font-bold text-gray-900">{{ $customer->stustaffID ?? 'N/A' }}</p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100">
                            <p class="text-[10px] text-gray-400 uppercase font-bold mb-1">Date of Birth</p>
                            <p class="text-base font-bold text-gray-900">{{ $customer->dob ? \Carbon\Carbon::parse($customer->dob)->format('d M Y') : 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100">
                    <div class="flex justify-between items-start mb-6">
                         <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Bank Details</h3>
                         <span class="text-blue-500 text-xl"><i class="fas fa-university"></i></span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="p-4 bg-blue-50 rounded-2xl border border-blue-100">
                            <p class="text-[10px] text-blue-400 uppercase font-bold mb-1">Bank Name</p>
                            <p class="text-lg font-black text-blue-900">{{ $customer->bankName ?? 'Not Provided' }}</p>
                        </div>
                        <div class="p-4 bg-blue-50 rounded-2xl border border-blue-100">
                            <p class="text-[10px] text-blue-400 uppercase font-bold mb-1">Account Number</p>
                            <p class="text-lg font-black text-blue-900">{{ $customer->bankAccountNo ?? 'Not Provided' }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100">
                    <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-6">Uploaded Documents</h3>
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

    <div x-show="showRejectModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" style="display: none;">
        <div @click.away="showRejectModal = false" class="bg-white border border-red-100 rounded-[2rem] p-8 max-w-lg w-full shadow-2xl overflow-hidden">
            
            <div class="mb-6 border-b border-gray-100 pb-4">
                <h3 class="text-2xl font-black text-gray-900 flex items-center">
                    <i class="fas fa-times-circle text-red-600 mr-3"></i> Reject Application
                </h3>
                <p class="text-gray-500 text-sm mt-1">Select all reasons that apply.</p>
            </div>
            
            <form action="{{ route('staff.customers.reject', $customer->customerID) }}" method="POST">
                @csrf
                
                <div class="mb-6 max-h-60 overflow-y-auto custom-scrollbar p-1">
                    <label class="block text-[10px] font-bold text-gray-900 uppercase tracking-widest mb-3">Common Reasons</label>
                    
                    <div class="space-y-3">
                        <label class="flex items-center p-3 border border-gray-200 rounded-xl hover:bg-red-50 hover:border-red-200 transition cursor-pointer group">
                            <input type="checkbox" name="rejection_reason[]" value="Unclear ID/Passport Photo" class="w-5 h-5 text-red-600 border-gray-300 rounded focus:ring-red-500">
                            <span class="ml-3 text-sm font-bold text-gray-700 group-hover:text-red-900">Unclear ID/Passport Photo</span>
                        </label>

                        <label class="flex items-center p-3 border border-gray-200 rounded-xl hover:bg-red-50 hover:border-red-200 transition cursor-pointer group">
                            <input type="checkbox" name="rejection_reason[]" value="Driving License Expired" class="w-5 h-5 text-red-600 border-gray-300 rounded focus:ring-red-500">
                            <span class="ml-3 text-sm font-bold text-gray-700 group-hover:text-red-900">Driving License Expired</span>
                        </label>

                        <label class="flex items-center p-3 border border-gray-200 rounded-xl hover:bg-red-50 hover:border-red-200 transition cursor-pointer group">
                            <input type="checkbox" name="rejection_reason[]" value="Selfie Verification Failed" class="w-5 h-5 text-red-600 border-gray-300 rounded focus:ring-red-500">
                            <span class="ml-3 text-sm font-bold text-gray-700 group-hover:text-red-900">Selfie Verification Failed</span>
                        </label>

                        <label class="flex items-center p-3 border border-gray-200 rounded-xl hover:bg-red-50 hover:border-red-200 transition cursor-pointer group">
                            <input type="checkbox" name="rejection_reason[]" value="Incomplete Profile Address" class="w-5 h-5 text-red-600 border-gray-300 rounded focus:ring-red-500">
                            <span class="ml-3 text-sm font-bold text-gray-700 group-hover:text-red-900">Incomplete Profile Address</span>
                        </label>

                        <label class="flex items-center p-3 border border-gray-200 rounded-xl hover:bg-red-50 hover:border-red-200 transition cursor-pointer group">
                            <input type="checkbox" name="rejection_reason[]" value="Invalid Bank Account Details" class="w-5 h-5 text-red-600 border-gray-300 rounded focus:ring-red-500">
                            <span class="ml-3 text-sm font-bold text-gray-700 group-hover:text-red-900">Invalid Bank Account Details</span>
                        </label>

                        <label class="flex items-center p-3 border border-gray-200 rounded-xl hover:bg-red-50 hover:border-red-200 transition cursor-pointer group">
                            <input type="checkbox" name="rejection_reason[]" value="Document Names Do Not Match" class="w-5 h-5 text-red-600 border-gray-300 rounded focus:ring-red-500">
                            <span class="ml-3 text-sm font-bold text-gray-700 group-hover:text-red-900">Document Names Do Not Match</span>
                        </label>
                        
                        <label class="flex items-center p-3 border border-gray-200 rounded-xl hover:bg-red-50 hover:border-red-200 transition cursor-pointer group">
                            <input type="checkbox" name="rejection_reason[]" value="Blurry Documents" class="w-5 h-5 text-red-600 border-gray-300 rounded focus:ring-red-500">
                            <span class="ml-3 text-sm font-bold text-gray-700 group-hover:text-red-900">Blurry Documents</span>
                        </label>

                        <label class="flex items-center p-3 border border-gray-200 rounded-xl hover:bg-red-50 hover:border-red-200 transition cursor-pointer group">
                            <input type="checkbox" name="rejection_reason[]" value="Other" class="w-5 h-5 text-red-600 border-gray-300 rounded focus:ring-red-500">
                            <span class="ml-3 text-sm font-bold text-gray-700 group-hover:text-red-900">Other</span>
                        </label>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-[10px] font-bold text-gray-900 uppercase tracking-widest mb-2">Specific Details (Optional)</label>
                    <textarea name="rejection_custom" rows="2" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-4 text-sm font-medium text-gray-900 focus:ring-2 focus:ring-red-500 focus:outline-none placeholder-gray-400" placeholder="e.g., 'License expires tomorrow' or 'Address mismatch'"></textarea>
                </div>
                
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                    <button type="button" @click="showRejectModal = false" class="px-6 py-3 rounded-xl font-bold text-xs uppercase tracking-widest text-gray-500 hover:bg-gray-100 transition">Cancel</button>
                    <button type="submit" class="bg-red-600 text-white px-8 py-3 rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-red-700 shadow-lg shadow-red-600/20 transition transform active:scale-95">Confirm Reject</button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="showBlacklistModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" style="display: none;">
        <div @click.away="showBlacklistModal = false" class="bg-white border border-gray-200 rounded-[2rem] p-8 max-w-lg w-full shadow-2xl">
            <h3 class="text-2xl font-black text-gray-900 mb-2">
                <i class="fas fa-ban text-gray-900 mr-2"></i> Blacklist Customer
            </h3>
            <p class="text-gray-500 mb-6 text-sm">Block this user from making future bookings.</p>
            
            <form action="{{ route('staff.customers.blacklist', $customer->customerID) }}" method="POST">
                @csrf
                
                <div class="mb-4">
                    <label class="block text-[10px] font-bold text-gray-900 uppercase tracking-widest mb-2">Select Main Reason</label>
                    {{-- CHANGED: bg-gray-50, text-gray-900 (Dark/Strong) --}}
                    <select name="blacklist_reason" class="w-full bg-gray-50 border border-gray-300 rounded-xl p-4 text-sm font-bold text-gray-900 focus:ring-2 focus:ring-black focus:outline-none appearance-none">
                        <option value="" disabled selected>-- Choose a Reason --</option>
                        <option value="Violation of Terms & Conditions">Violation of Terms & Conditions</option>
                        <option value="Severe Vehicle Damage">Severe Vehicle Damage</option>
                        <option value="Non-Payment / Outstanding Debt">Non-Payment / Outstanding Debt</option>
                        <option value="Abusive Behavior towards Staff">Abusive Behavior towards Staff</option>
                        <option value="Illegal Activity / Police Case">Illegal Activity / Police Case</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="mb-6">
                    <label class="block text-[10px] font-bold text-gray-900 uppercase tracking-widest mb-2">Additional Remarks (Optional)</label>
                    {{-- CHANGED: bg-white, text-gray-900 --}}
                    <textarea name="blacklist_custom" rows="2" class="w-full bg-white border border-gray-300 rounded-xl p-4 text-sm font-medium text-gray-900 focus:ring-2 focus:ring-black focus:outline-none placeholder-gray-400" placeholder="Type specific details here..."></textarea>
                </div>
                
                <div class="flex justify-end gap-3">
                    <button type="button" @click="showBlacklistModal = false" class="px-6 py-3 rounded-xl font-bold text-xs uppercase tracking-widest text-gray-500 hover:bg-gray-100 transition">Cancel</button>
                    <button type="submit" class="bg-gray-900 text-white px-8 py-3 rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-black shadow-lg shadow-gray-900/20 transition">Confirm Blacklist</button>
                </div>
            </form>
        </div>
    </div>

    {{-- PENALTY MODAL --}}
    <div x-show="showPenaltyModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" style="display: none;">
        <div @click.away="showPenaltyModal = false" class="bg-white border border-orange-200 rounded-[2rem] p-8 max-w-lg w-full shadow-2xl">
            <h3 class="text-2xl font-black text-gray-900 mb-2">
                <i class="fas fa-exclamation-triangle text-orange-600 mr-2"></i> Impose Penalty
            </h3>
            <p class="text-gray-500 mb-6 text-sm">Charge this customer for violating rules. They must pay before making new bookings.</p>
            
            <form action="{{ route('staff.customers.penalty', $customer->customerID) }}" method="POST">
                @csrf
                
                <div class="mb-4">
                    <label class="block text-[10px] font-bold text-gray-900 uppercase tracking-widest mb-2">Select Main Reason</label>
                    <select name="penalty_reason" class="w-full bg-gray-50 border border-gray-300 rounded-xl p-4 text-sm font-bold text-gray-900 focus:ring-2 focus:ring-orange-500 focus:outline-none appearance-none" required>
                        <option value="" disabled selected>-- Choose a Reason --</option>
                        <option value="Late Return">Late Return</option>
                        <option value="Vehicle Damage">Vehicle Damage</option>
                        <option value="Violation of Terms & Conditions">Violation of Terms & Conditions</option>
                        <option value="Excessive Mileage">Excessive Mileage</option>
                        <option value="Fuel Surcharge">Fuel Surcharge</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-[10px] font-bold text-gray-900 uppercase tracking-widest mb-2">Penalty Amount (RM)</label>
                    <input type="number" name="penalty_amount" step="0.01" min="0.01" class="w-full bg-gray-50 border border-gray-300 rounded-xl p-4 text-sm font-bold text-gray-900 focus:ring-2 focus:ring-orange-500 focus:outline-none" placeholder="0.00" required>
                </div>

                <div class="mb-6">
                    <label class="block text-[10px] font-bold text-gray-900 uppercase tracking-widest mb-2">Additional Remarks (Optional)</label>
                    <textarea name="penalty_custom" rows="2" class="w-full bg-white border border-gray-300 rounded-xl p-4 text-sm font-medium text-gray-900 focus:ring-2 focus:ring-orange-500 focus:outline-none placeholder-gray-400" placeholder="Type specific details here..."></textarea>
                </div>
                
                <div class="flex justify-end gap-3">
                    <button type="button" @click="showPenaltyModal = false" class="px-6 py-3 rounded-xl font-bold text-xs uppercase tracking-widest text-gray-500 hover:bg-gray-100 transition">Cancel</button>
                    <button type="submit" class="bg-orange-600 text-white px-8 py-3 rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-orange-700 shadow-lg shadow-orange-600/20 transition">Confirm Penalty</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection