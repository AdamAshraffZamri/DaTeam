@extends('layouts.staff')

@section('content')
<div x-data="{ 
    showRejectModal: false, 
    showBlacklistModal: false, 
    showPenaltyModal: false 
}" class="min-h-screen bg-slate-100 rounded-2xl p-6">

    <div class="max-w-7xl mx-auto">
        
        {{-- 1. HEADER --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 md:mb-8 animate-fade-in">
            <div>
                <h1 class="text-2xl md:text-3xl font-black text-slate-900 tracking-tight">Customer Profile</h1>
                <p class="text-slate-500 text-xs md:text-sm font-medium mt-1">Viewing details for <span class="text-slate-900 font-bold">{{ $customer->fullName }}</span></p>
            </div>

            {{-- Status Badge --}}
            <div class="flex items-center justify-between md:justify-end gap-3 px-4 py-2 bg-white rounded-xl border border-gray-100 shadow-sm w-full md:w-auto">
                <div class="flex flex-col items-start md:items-end">
                    <span class="text-[9px] md:text-[10px] font-bold text-gray-400 uppercase tracking-widest">Account Status</span>
                    
                    @if($customer->blacklisted)
                        <span class="text-xs font-black text-slate-900 uppercase">BLACKLISTED</span>
                    @elseif($customer->accountStat == 'Confirmed' || $customer->accountStat == 'active')
                        <span class="text-xs font-black text-green-600 uppercase">VERIFIED</span>
                    @elseif($customer->accountStat == 'pending')
                        <span class="text-xs font-black text-orange-500 uppercase">PENDING</span>
                    @elseif($customer->accountStat == 'rejected')
                        <span class="text-xs font-black text-red-600 uppercase">REJECTED</span>
                    @else
                        <span class="text-xs font-black text-gray-400 uppercase">UNVERIFIED</span>
                    @endif
                </div>
                
                @if($customer->blacklisted)
                    <div class="w-3 h-3 rounded-full bg-slate-900 shadow-[0_0_8px_rgba(15,23,42,0.4)]"></div>
                @elseif($customer->accountStat == 'Confirmed' || $customer->accountStat == 'active')
                    <div class="w-3 h-3 rounded-full bg-green-500 shadow-[0_0_8px_rgba(34,197,94,0.4)]"></div>
                @elseif($customer->accountStat == 'pending')
                    <div class="w-3 h-3 rounded-full bg-orange-500 shadow-[0_0_8px_rgba(249,115,22,0.4)] animate-pulse"></div>
                @elseif($customer->accountStat == 'rejected')
                    <div class="w-3 h-3 rounded-full bg-red-500 shadow-[0_0_8px_rgba(239,68,68,0.4)]"></div>
                @else
                    <div class="w-3 h-3 rounded-full bg-gray-300"></div>
                @endif
            </div>
        </div>

        {{-- 2. ALERTS --}}
        @if($customer->blacklisted && $customer->blacklist_reason)
            <div class="bg-slate-800 rounded-2xl p-4 md:p-5 mb-6 shadow-lg flex items-start gap-4 text-white animate-fade-in">
                <div class="w-10 h-10 rounded-xl bg-slate-700 flex items-center justify-center shrink-0">
                    <i class="fas fa-ban text-red-400"></i>
                </div>
                <div>
                    <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Blacklist Reason</h4>
                    <p class="text-sm font-bold leading-relaxed">{{ $customer->blacklist_reason }}</p>
                </div>
            </div>
        @endif

        @if(!$customer->blacklisted && $customer->accountStat == 'rejected' && $customer->rejection_reason)
            <div class="bg-red-50 border border-red-100 rounded-2xl p-4 md:p-5 mb-6 flex items-start gap-4 animate-fade-in">
                <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center shrink-0 text-red-600">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div>
                    <h4 class="text-[10px] font-black text-red-400 uppercase tracking-widest mb-1">Rejection Reason</h4>
                    <p class="text-sm font-bold text-red-900 leading-relaxed">{{ $customer->rejection_reason }}</p>
                </div>
            </div>
        @endif

        {{-- 3. ACTION BAR --}}
        <div class="bg-white rounded-[1.5rem] p-4 md:p-6 mb-8 shadow-sm border border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="text-center sm:text-left">
                <h3 class="font-bold text-base md:text-lg text-gray-900">Account Actions</h3>
                <p class="text-gray-400 text-xs mt-0.5">Manage user access and history.</p>
            </div>
            <div class="grid grid-cols-2 sm:flex sm:flex-wrap justify-center gap-2 w-full sm:w-auto">
                <a href="{{ route('staff.customers.penalty_history', $customer->customerID) }}" class="bg-purple-100 text-purple-600 px-4 py-3 rounded-xl font-bold text-[10px] md:text-xs uppercase text-center tracking-widest hover:bg-purple-200 transition border border-purple-200">
                    <i class="fas fa-history sm:mr-2"></i> <span class="hidden sm:inline">Penalty History</span><span class="sm:hidden">History</span>
                </a>
    
                @if($customer->blacklisted)
                    <form action="{{ route('staff.customers.blacklist', $customer->customerID) }}" method="POST" onsubmit="return confirm('Restore account?');" class="col-span-1">
                        @csrf
                        <button type="submit" class="w-full bg-gray-900 text-white px-4 py-3 rounded-xl font-bold text-[10px] md:text-xs uppercase tracking-widest hover:bg-gray-800 transition">
                            <i class="fas fa-unlock mr-1"></i> Restore
                        </button>
                    </form>
                @else
                    <button @click="showPenaltyModal = true" type="button" class="bg-orange-100 text-orange-600 px-4 py-3 rounded-xl font-bold text-[10px] md:text-xs uppercase tracking-widest border border-orange-200">
                        <i class="fas fa-exclamation-triangle sm:mr-1"></i> Penalty
                    </button>
                    <button @click="showBlacklistModal = true" type="button" class="bg-gray-100 text-gray-600 px-4 py-3 rounded-xl font-bold text-[10px] md:text-xs uppercase tracking-widest">
                        <i class="fas fa-ban sm:mr-1"></i> Blacklist
                    </button>

                    @if($customer->accountStat !== 'active' && $customer->accountStat !== 'Confirmed' && $customer->accountStat !== 'rejected')
                        <button @click="showRejectModal = true" type="button" class="bg-red-50 text-red-600 px-4 py-3 rounded-xl font-bold text-[10px] md:text-xs uppercase tracking-widest border border-red-100">
                            <i class="fas fa-times sm:mr-1"></i> Reject
                        </button>
                    @endif

                    @if($customer->accountStat !== 'active' && $customer->accountStat !== 'Confirmed')
                        <form action="{{ route('staff.customers.approve', $customer->customerID) }}" method="POST" class="col-span-2 sm:col-span-1">
                            @csrf
                            <button type="submit" onclick="return confirm('Verify user?')" class="w-full bg-green-500 text-white px-6 py-3 rounded-xl font-bold text-[10px] md:text-xs uppercase tracking-widest hover:bg-green-600 shadow-lg shadow-green-500/30">
                                <i class="fas fa-check mr-1"></i> Verify
                            </button>
                        </form>
                    @endif
                @endif
            </div>
        </div>
        {{-- 3. LAYOUT GRID --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            {{-- LEFT COLUMN: PROFILE CARD --}}
            <div class="lg:col-span-1 space-y-6">
                
                {{-- Profile Card --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden relative animate-fade-in-down">
                    <div class="h-24 bg-gradient-to-r from-slate-800 to-slate-900 relative">
                        {{-- Optional: Add a subtle pattern overlay --}}
                        <div class="absolute inset-0 opacity-10" style="background-image: url('{{ asset('pattern.svg') }}'); background-size: cover;"></div>
                    </div>
                    <div class="px-6 pb-6 text-center">
                        <div class="relative -mt-10 mb-4 z-10">
                            {{-- Maintain the original rotated border design --}}
                            <div class="w-20 h-20 mx-auto bg-white rounded-2xl p-1 shadow-md rotate-3 overflow-hidden">
                                @if($customer->avatar && !empty($customer->avatar))
                                    {{-- SHOW AVATAR IMAGE --}}
                                    <img src="{{ Storage::disk('s3')->temporaryUrl($customer->avatar, now()->addMinutes(60)) }}" 
                                        alt="{{ $customer->fullName }}" 
                                        class="w-full h-full rounded-xl object-cover -rotate-3 group-hover:scale-110 transition-transform duration-300"
                                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                @else
                                    {{-- FALLBACK TO LETTER (Old Design) --}}
                                    <div class="w-full h-full rounded-xl bg-slate-100 flex items-center justify-center text-2xl font-black text-slate-300 uppercase -rotate-3">
                                        {{ substr($customer->fullName, 0, 1) }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        <h2 class="text-lg font-black text-slate-900 leading-tight">{{ $customer->fullName }}</h2>
                        <p class="text-xs font-bold text-slate-400 mt-1">{{ $customer->email }}</p>
                        
                        <div class="mt-6 pt-6 border-t border-gray-50 flex justify-center">
                            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-orange-50 border border-orange-100 text-[10px] font-bold text-orange-700 uppercase tracking-wide">
                                <i class="far fa-calendar-alt"></i> Joined {{ $customer->created_at->format('d M Y') }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Contact Info --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-6">Contact Details</h3>
                    
                    <div class="space-y-5">
                        <div class="flex gap-3">
                            <div class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400 shrink-0">
                                <i class="fas fa-phone text-xs"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase">Phone Number</p>
                                <p class="text-sm font-bold text-slate-800">{{ $customer->phoneNo ?? 'Not Provided' }}</p>
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <div class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400 shrink-0">
                                <i class="fas fa-map-marker-alt text-xs"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase">Address</p>
                                <p class="text-xs font-medium text-slate-600 mt-1 leading-relaxed">{{ $customer->homeAddress ?? 'N/A' }}</p>
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <div class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400 shrink-0">
                                <i class="fas fa-university text-xs"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase">Faculty / College</p>
                                <p class="text-xs font-bold text-slate-800 mt-0.5">{{ $customer->faculty ?? 'N/A' }}</p>
                                <p class="text-[10px] text-slate-500">{{ $customer->collegeAddress }}</p>
                            </div>
                        </div>

                        {{-- Emergency Contact --}}
                        <div class="pt-4 border-t border-gray-50">
                            <div class="flex gap-3">
                                <div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center text-red-400 shrink-0">
                                    <i class="fas fa-heartbeat text-xs"></i>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-red-400 uppercase">Emergency Contact</p>
                                    <p class="text-sm font-black text-slate-800">{{ $customer->emergency_contact_name ?? 'N/A' }}</p>
                                    <p class="text-xs font-bold text-slate-500">{{ $customer->emergency_contact_no ?? '' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- RIGHT COLUMN: ACTIONS & DETAILS --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Action Bar --}}
                

                {{-- Identity Verification with Local Previews --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-8">
                    <div class="flex items-center gap-2 mb-6 border-b border-gray-50 pb-2">
                        <div class="w-8 h-8 rounded-lg bg-orange-50 text-orange-600 flex items-center justify-center">
                            <i class="fas fa-id-card text-xs"></i>
                        </div>
                        <h3 class="text-sm font-black text-slate-800 uppercase tracking-wider">Identity Verification</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        
                        @php
                            $docs = [
                                ['label' => 'IC / Passport No', 'val' => $customer->ic_passport, 'file' => $customer->ic_passport_image, 'type' => 'ic'],
                                ['label' => 'License Expiry', 'val' => $customer->driving_license_expiry, 'file' => $customer->driving_license_image, 'type' => 'license'],
                                ['label' => 'Student ID No', 'val' => $customer->stustaffID, 'file' => $customer->student_card_image, 'type' => 'student_card'],
                                ['label' => 'Date of Birth', 'val' => $customer->dob ? \Carbon\Carbon::parse($customer->dob)->format('d M Y') : 'N/A', 'file' => null]
                            ];
                        @endphp

                        @foreach($docs as $doc)
                        <div class="space-y-3">
                            <div>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ $doc['label'] }}</span>
                                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 font-bold text-slate-900 text-sm">
                                    {{ $doc['val'] ?? 'Not Provided' }}
                                </div>
                            </div>
                            
                            @if($doc['label'] != 'Date of Birth')
                            <div class="w-full">
                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-1 block">Document Preview</span>
                                
                                @if($doc['file'])
                                    @php 
                                        $filePath = 'documents/' . basename($doc['file']);
                                        $isImage = in_array(pathinfo($doc['file'], PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                    @endphp

                                    @if(Storage::disk('s3')->exists($filePath))
                                        <a href="{{ Storage::disk('s3')->temporaryUrl($filePath, now()->addMinutes(60)) }}" target="_blank" 
                                        class="block w-full aspect-video rounded-xl border border-slate-200 overflow-hidden bg-slate-100 hover:border-orange-500 transition-all group relative">
                                            
                                            @if($isImage)
                                                {{-- DIRECT IMAGE PREVIEW --}}
                                                <img src="{{ Storage::disk('s3')->temporaryUrl($filePath, now()->addMinutes(60)) }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
                                                    <i class="fas fa-search-plus text-white text-xl"></i>
                                                </div>
                                            @else
                                                {{-- PDF/OTHER FILE PREVIEW --}}
                                                <div class="absolute inset-0 flex flex-col items-center justify-center bg-slate-50 group-hover:bg-white transition-colors">
                                                    <i class="fas fa-file-pdf text-2xl text-red-500 mb-1"></i>
                                                    <span class="text-[9px] font-black text-slate-400 uppercase">View PDF Document</span>
                                                </div>
                                            @endif
                                        </a>
                                    @else
                                        <div class="w-full aspect-video rounded-xl border border-dashed border-slate-200 bg-slate-50 flex flex-col items-center justify-center">
                                            <i class="fas fa-times-circle text-slate-300 mb-1"></i>
                                            <span class="text-[9px] font-bold text-slate-400 uppercase">File Not Found</span>
                                        </div>
                                    @endif
                                @else
                                    <div class="w-full aspect-video rounded-xl border border-dashed border-slate-200 bg-slate-50 flex flex-col items-center justify-center">
                                        <i class="fas fa-times-circle text-slate-300 mb-1"></i>
                                        <span class="text-[9px] font-bold text-slate-400 uppercase">No File Uploaded</span>
                                    </div>
                                @endif
                            </div>
                            @endif
                        </div>
                        @endforeach

                    </div>
                </div>

                {{-- Bank Details --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-8">
                    <div class="flex items-center gap-2 mb-6 border-b border-gray-50 pb-2">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                            <i class="fas fa-university text-xs"></i>
                        </div>
                        <h3 class="text-sm font-black text-slate-800 uppercase tracking-wider">Bank Information</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Bank Name</span>
                            <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 font-bold text-blue-900 text-sm">
                                {{ $customer->bankName ?? 'Not Provided' }}
                            </div>
                        </div>
                        
                        <div class="space-y-1">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Account Number</span>
                            <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 font-bold text-blue-900 text-sm">
                                {{ $customer->bankAccountNo ?? 'Not Provided' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- === MODALS === --}}

    {{-- REJECT MODAL --}}
    <div x-show="showRejectModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div @click.away="showRejectModal = false" class="bg-white rounded-[2rem] p-8 max-w-lg w-full shadow-2xl border border-red-100">
            <h3 class="text-xl font-black text-slate-900 mb-2 flex items-center gap-2">
                <i class="fas fa-times-circle text-red-600"></i> Reject Application
            </h3>
            <p class="text-xs text-slate-500 font-medium mb-6">Select specific reasons for rejection.</p>
            
            <form action="{{ route('staff.customers.reject', $customer->customerID) }}" method="POST">
                @csrf
                <div class="space-y-3 mb-6 max-h-60 overflow-y-auto custom-scrollbar pr-2">
                    @foreach(['Unclear ID/Passport Photo', 'Driving License Expired', 'Selfie Verification Failed', 'Incomplete Profile', 'Document Mismatch', 'Blurry Documents', 'Other'] as $reason)
                    <label class="flex items-center p-3 rounded-xl border border-slate-200 cursor-pointer hover:border-red-300 hover:bg-red-50/50 transition-all group">
                        <input type="checkbox" name="rejection_reason[]" value="{{ $reason }}" class="w-4 h-4 text-red-600 rounded border-gray-300 focus:ring-red-500">
                        <span class="ml-3 text-xs font-bold text-slate-600 group-hover:text-red-700 uppercase tracking-wide">{{ $reason }}</span>
                    </label>
                    @endforeach
                </div>

                <div class="mb-6">
                    <textarea name="rejection_custom" rows="2" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-4 text-sm font-bold text-slate-800 focus:bg-white focus:border-red-500 focus:ring-4 focus:ring-red-500/10 outline-none transition-all placeholder-slate-400" placeholder="Additional details..."></textarea>
                </div>
                
                <div class="flex justify-end gap-3 pt-4 border-t border-slate-50">
                    <button type="button" @click="showRejectModal = false" class="px-6 py-2.5 rounded-xl text-xs font-bold text-slate-500 hover:text-slate-800 hover:bg-slate-50 transition uppercase tracking-wide">Cancel</button>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-red-600 text-white text-xs font-bold uppercase tracking-wide hover:bg-red-700 shadow-lg shadow-red-600/20 transition">Confirm Reject</button>
                </div>
            </form>
        </div>
    </div>

    {{-- BLACKLIST MODAL --}}
    <div x-show="showBlacklistModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div @click.away="showBlacklistModal = false" class="bg-white rounded-[2rem] p-8 max-w-lg w-full shadow-2xl border border-slate-200">
            <h3 class="text-xl font-black text-slate-900 mb-2 flex items-center gap-2">
                <i class="fas fa-ban text-slate-900"></i> Blacklist Customer
            </h3>
            <p class="text-xs text-slate-500 font-medium mb-6">This will block the user from making future bookings.</p>
            
            <form action="{{ route('staff.customers.blacklist', $customer->customerID) }}" method="POST">
                @csrf
                <div class="space-y-4 mb-6">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Reason</label>
                        <select name="blacklist_reason" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-800 focus:bg-white focus:border-slate-900 focus:ring-4 focus:ring-slate-200 outline-none transition-all appearance-none cursor-pointer">
                            <option value="" disabled selected>Select Reason</option>
                            <option value="Violation of Terms">Violation of Terms</option>
                            <option value="Vehicle Damage">Vehicle Damage</option>
                            <option value="Non-Payment">Non-Payment</option>
                            <option value="Abusive Behavior">Abusive Behavior</option>
                            <option value="Illegal Activity">Illegal Activity</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Remarks</label>
                        <textarea name="blacklist_custom" rows="2" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-4 text-sm font-bold text-slate-800 focus:bg-white focus:border-slate-900 focus:ring-4 focus:ring-slate-200 outline-none transition-all placeholder-slate-400" placeholder="Details..."></textarea>
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 pt-4 border-t border-slate-50">
                    <button type="button" @click="showBlacklistModal = false" class="px-6 py-2.5 rounded-xl text-xs font-bold text-slate-500 hover:text-slate-800 hover:bg-slate-50 transition uppercase tracking-wide">Cancel</button>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-slate-900 text-white text-xs font-bold uppercase tracking-wide hover:bg-black shadow-lg shadow-slate-900/20 transition">Confirm</button>
                </div>
            </form>
        </div>
    </div>

    {{-- PENALTY MODAL --}}
    <div x-show="showPenaltyModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div @click.away="showPenaltyModal = false" class="bg-white rounded-[2rem] p-8 max-w-lg w-full shadow-2xl border border-orange-100">
            <h3 class="text-xl font-black text-slate-900 mb-2 flex items-center gap-2">
                <i class="fas fa-exclamation-triangle text-orange-600"></i> Impose Penalty
            </h3>
            <p class="text-xs text-slate-500 font-medium mb-6">Charge fees for violations or damages.</p>
            
            <form action="{{ route('staff.customers.penalty', $customer->customerID) }}" method="POST">
                @csrf
                <div class="space-y-4 mb-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Reason</label>
                            <select name="penalty_reason" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-3 text-sm font-bold text-slate-800 focus:bg-white focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 outline-none transition-all">
                                <option value="Late Return">Late Return</option>
                                <option value="Vehicle Damage">Vehicle Damage</option>
                                <option value="Mileage">Excess Mileage</option>
                                <option value="Fuel">Fuel Surcharge</option>
                                <option value="Delivery Fee">Delivery Fee</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Amount (RM)</label>
                            <input type="number" name="penalty_amount" step="0.01" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-3 text-sm font-bold text-slate-800 focus:bg-white focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 outline-none transition-all" placeholder="0.00">
                        </div>
                    </div>
                    
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Remarks</label>
                        <textarea name="penalty_custom" rows="2" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-4 text-sm font-bold text-slate-800 focus:bg-white focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 outline-none transition-all placeholder-slate-400" placeholder="Details..."></textarea>
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 pt-4 border-t border-slate-50">
                    <button type="button" @click="showPenaltyModal = false" class="px-6 py-2.5 rounded-xl text-xs font-bold text-slate-500 hover:text-slate-800 hover:bg-slate-50 transition uppercase tracking-wide">Cancel</button>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-orange-600 text-white text-xs font-bold uppercase tracking-wide hover:bg-orange-700 shadow-lg shadow-orange-600/20 transition">Confirm Penalty</button>
                </div>
            </form>
        </div>
    </div>

</div>

<style>
    @keyframes fade-in { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .animate-fade-in { animation: fade-in 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    [x-cloak] { display: none !important; }
</style>
@endsection