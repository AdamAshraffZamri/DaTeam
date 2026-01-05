@extends('layouts.staff')

@section('content')
<div class="min-h-screen bg-gray-100 rounded-2xl p-6">
    <div class="max-w-6xl mx-auto">
        
        {{-- BACK BUTTON --}}
        <a href="{{ route('staff.bookings.index') }}" class="inline-flex items-center text-gray-500 hover:text-gray-900 mb-6 transition">
            <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
        </a>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            {{-- LEFT COLUMN: CUSTOMER, RENTAL, VEHICLE --}}
            <div class="space-y-6">
                
                {{-- 1. [UPDATED] COLLAPSIBLE CUSTOMER INFORMATION --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 transition-all duration-300">
                    
                    {{-- HEADER: Title & Toggle Button --}}
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">
                            Customer Info
                        </h3>
                        <button onclick="toggleCustomerDetails()" id="toggle-cust-btn" class="text-xs text-indigo-600 font-bold hover:text-indigo-800 transition flex items-center gap-1 bg-indigo-50 px-2 py-1 rounded-lg">
                            <span>Show Details</span> <i class="fas fa-chevron-down"></i>
                        </button>
                    </div>
                    
                    {{-- COMPACT VIEW (Always Visible) --}}
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 text-xl font-bold shrink-0">
                            {{ substr($booking->customer->fullName ?? 'G', 0, 1) }}
                        </div>
                        <div class="overflow-hidden">
                            <h4 class="font-bold text-gray-900 truncate">{{ $booking->customer->fullName ?? 'Guest User' }}</h4>
                            <a href="tel:{{ $booking->customer->phoneNo }}" class="text-xs text-gray-500 mt-1 flex items-center gap-1 hover:text-indigo-600 transition">
                                <i class="fas fa-phone-alt text-gray-400 text-[10px]"></i> 
                                {{ $booking->customer->phoneNo }}
                            </a>
                        </div>
                    </div>

                    {{-- EXPANDABLE DETAILS (Hidden by Default) --}}
                    <div id="cust-details" class="hidden mt-5 pt-5 border-t border-gray-100 space-y-3 text-sm animate-fade-in-down">
                        
                        <div class="flex justify-between">
                            <span class="text-gray-500">Email</span>
                            <span class="font-medium text-gray-900 truncate max-w-[150px]" title="{{ $booking->customer->email }}">{{ $booking->customer->email }}</span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-500">IC / Passport</span>
                            <span class="font-medium text-gray-900">{{ $booking->customer->ic_passport }}</span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-500">License No</span>
                            <span class="font-medium text-gray-900">{{ $booking->customer->drivingNo }}</span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-500">Joined</span>
                            <span class="font-medium text-gray-900">{{ optional($booking->customer->created_at)->format('M Y') ?? 'N/A' }}</span>
                        </div>

                        <div class="mt-2 pt-2 border-t border-gray-50">
                            <p class="text-gray-400 text-[10px] uppercase font-bold mb-1">Home Address</p>
                            <p class="font-medium text-gray-900 leading-snug text-xs">{{ $booking->customer->homeAddress }}</p>
                        </div>
                    </div>
                </div>

                @if($booking->remarks)
                <div class="bg-yellow-50 rounded-2xl shadow-sm border border-yellow-200 p-6 relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-10">
                        <i class="fas fa-comment-dots text-6xl text-yellow-600"></i>
                    </div>
                    <h3 class="text-xs font-bold text-yellow-700 uppercase tracking-wider mb-3 flex items-center relative z-10">
                        <i class="fas fa-bullhorn mr-2"></i> Customer Request
                    </h3>
                    <div class="bg-white/50 rounded-xl p-3 border border-yellow-100 relative z-10">
                        <p class="text-sm text-gray-800 font-medium italic leading-relaxed">
                            "{{ $booking->remarks }}"
                        </p>
                    </div>
                </div>
                @endif

                {{-- 2. RENTAL INFORMATION --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4 border-b border-gray-100 pb-2">
                        Rental Information
                    </h3>
                    
                    <div class="relative pl-4 border-l-2 border-dashed border-gray-200 space-y-8">
                        
                        {{-- PICKUP --}}
                        <div class="relative">
                            <div class="absolute -left-[21px] top-1 w-3 h-3 rounded-full bg-green-500 ring-4 ring-white"></div>
                            <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wider mb-1">Pickup</p>
                            <p class="font-bold text-gray-900 text-base leading-tight">
                                {{ \Carbon\Carbon::parse($booking->originalDate)->format('d M Y') }}
                                <span class="text-gray-400 font-normal text-sm">at</span> 
                                {{ \Carbon\Carbon::parse($booking->bookingTime)->format('h:i A') }}
                            </p>
                            <div class="text-xs text-gray-600 mt-2 flex items-start gap-2 bg-gray-50 p-2 rounded-lg border border-gray-100">
                                <i class="fas fa-map-marker-alt text-green-500 mt-0.5 shrink-0"></i>
                                <span class="leading-snug">{{ $booking->pickupLocation }}</span>
                            </div>
                        </div>

                        {{-- RETURN --}}
                        <div class="relative">
                            <div class="absolute -left-[21px] top-1 w-3 h-3 rounded-full bg-red-500 ring-4 ring-white"></div>
                            <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wider mb-1">Return</p>
                            <p class="font-bold text-gray-900 text-base leading-tight">
                                {{ \Carbon\Carbon::parse($booking->returnDate)->format('d M Y') }}
                                <span class="text-gray-400 font-normal text-sm">at</span> 
                                {{ \Carbon\Carbon::parse($booking->returnTime)->format('h:i A') }}
                            </p>
                            <div class="text-xs text-gray-600 mt-2 flex items-start gap-2 bg-gray-50 p-2 rounded-lg border border-gray-100">
                                <i class="fas fa-flag-checkered text-red-500 mt-0.5 shrink-0"></i>
                                <span class="leading-snug">{{ $booking->returnLocation }}</span>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- 3. VEHICLE DETAILS --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4 border-b border-gray-100 pb-2">
                        Vehicle Details
                    </h3>
                    <img src="{{ asset('storage/' . $booking->vehicle->image) }}" alt="Car" class="w-full h-32 object-cover rounded-xl mb-4 bg-gray-50">
                    
                    <div class="flex justify-between items-center mb-2">
                        <h4 class="font-bold text-lg text-gray-900">{{ $booking->vehicle->model }}</h4>
                        <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded text-xs font-bold uppercase">
                            {{ $booking->vehicle->plateNo }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-500">{{ $booking->vehicle->color }} • {{ $booking->vehicle->transmission }} • {{ $booking->vehicle->seat }} Seats</p>
                </div>

            </div>

            {{-- RIGHT COLUMN: ADMIN & ACTIONS --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- NEW TOP GRID: STAFF & PAYMENT --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    {{-- 0. STAFF ASSIGNMENT CARD --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold text-lg shadow-md
                                {{ $booking->staff ? 'bg-indigo-600' : 'bg-gray-300' }}">
                                {{ $booking->staff ? substr($booking->staff->name, 0, 1) : '?' }}
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Staff In Charge</h4>
                                @if($booking->staff)
                                    <p class="text-lg font-bold text-gray-900">{{ $booking->staff->name }}</p>
                                @else
                                    <p class="text-lg font-bold text-gray-400 italic">Unassigned</p>
                                @endif
                            </div>
                        </div>

                        {{-- Assignment Form --}}
                        <form action="{{ route('staff.bookings.assign', $booking->bookingID) }}" method="POST" class="w-full">
                            @csrf
                            <label class="text-[10px] font-bold text-gray-400 uppercase mb-1 block">Assign Agent</label>
                            <div class="flex gap-2">
                                <select name="staff_id" class="bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-lg p-2.5 focus:ring-indigo-500 focus:border-indigo-500 block w-full">
                                    <option value="" disabled selected>Select Staff...</option>
                                    @foreach($allStaff as $staffMember)
                                        <option value="{{ $staffMember->staffID }}" {{ $booking->staffID == $staffMember->staffID ? 'selected' : '' }}>
                                            {{ $staffMember->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-4 rounded-lg text-sm transition">
                                    <i class="fas fa-check"></i>
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- 4. PAYMENT & DOCUMENTS --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Payment & Docs</h3>
                                @if($booking->payment)
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded uppercase
                                        {{ $booking->payment->depoStatus == 'Refunded' ? 'bg-green-100 text-green-700' : 
                                          ($booking->payment->depoStatus == 'Requested' ? 'bg-red-100 text-red-700 animate-pulse' : 'bg-orange-100 text-orange-700') }}">
                                        {{ $booking->payment->depoStatus }}
                                    </span>
                                @endif
                            </div>
                            
                            <div class="flex justify-between items-end mb-4">
                                <span class="text-gray-500 text-sm">Total Cost</span>
                                <span class="text-2xl font-black text-gray-900 leading-none">RM {{ number_format($booking->totalCost, 2) }}</span>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-3 gap-2"> {{-- Changed from grid-cols-2 to grid-cols-3 --}}
                            
                            {{-- Receipt --}}
                            @if($booking->payment && $booking->payment->installmentDetails)
                                <a href="{{ asset('storage/' . $booking->payment->installmentDetails) }}" target="_blank" class="flex flex-col items-center justify-center bg-blue-50 border border-blue-200 text-blue-700 py-2 rounded-lg text-xs font-bold hover:bg-blue-100 transition text-center h-full">
                                    <i class="fas fa-receipt mb-1 text-lg"></i> Receipt
                                </a>
                            @else
                                <div class="flex flex-col items-center justify-center bg-gray-50 border border-gray-200 text-gray-400 py-2 rounded-lg text-xs font-bold text-center h-full opacity-60">
                                    <i class="fas fa-times mb-1 text-lg"></i> No Receipt
                                </div>
                            @endif

                            {{-- Agreement --}}
                            @if($booking->aggreementLink)
                                <a href="{{ asset('storage/' . $booking->aggreementLink) }}" target="_blank" class="flex flex-col items-center justify-center bg-purple-50 border border-purple-200 text-purple-700 py-2 rounded-lg text-xs font-bold hover:bg-purple-100 transition text-center h-full">
                                    <i class="fas fa-file-signature mb-1 text-lg"></i> Agreement
                                </a>
                            @else
                                <div class="flex flex-col items-center justify-center bg-gray-50 border border-gray-200 text-gray-400 py-2 rounded-lg text-xs font-bold text-center h-full opacity-60">
                                    <i class="fas fa-times mb-1 text-lg"></i> No Doc
                                </div>
                            @endif

                            {{-- Invoice (NEW) --}}
                            @if($booking->bookingStatus == 'Completed')
                                <a href="{{ route('staff.bookings.invoice', $booking->bookingID) }}" target="_blank" class="flex flex-col items-center justify-center bg-emerald-50 border border-emerald-200 text-emerald-700 py-2 rounded-lg text-xs font-bold hover:bg-emerald-100 transition text-center h-full">
                                    <i class="fas fa-file-invoice mb-1 text-lg"></i> Invoice
                                </a>
                            @else
                                <div class="flex flex-col items-center justify-center bg-gray-50 border border-gray-200 text-gray-400 py-2 rounded-lg text-xs font-bold text-center h-full opacity-60" title="Available after completion">
                                    <i class="fas fa-clock mb-1 text-lg"></i> Invoice
                                </div>
                            @endif
                        </div>
                    </div>

                </div>

                {{-- 5. INSPECTION GALLERY --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Inspection Gallery</h3>
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Pickup: 5 Photos | Return: 6 Photos</p>
                        </div>
                        <button onclick="document.getElementById('staff-upload-modal').classList.remove('hidden')" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-xs font-bold hover:bg-blue-700 transition shadow-lg shadow-blue-500/20">
                            <i class="fas fa-camera mr-1"></i> Staff Upload
                        </button>
                    </div>

                    <div class="space-y-8">
                        {{-- Customer Uploads --}}
                        <div>
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Customer Uploads</h4>
                            @forelse($booking->inspections->whereNull('staffID') as $inspection)
                                <div class="mb-6 bg-gray-50 rounded-xl p-4 border border-gray-100">
                                    <div class="flex justify-between items-center mb-3 border-b border-gray-200 pb-2">
                                        <span class="text-xs font-bold uppercase text-gray-500">{{ $inspection->inspectionType }} Inspection</span>
                                        <div class="flex gap-4 text-xs">
                                            <span class="text-gray-600">
                                                <i class="fas fa-tachometer-alt text-orange-500 mr-1"></i> 
                                                <strong>{{ $inspection->mileageBefore ?? $inspection->mileageAfter }}</strong> km
                                            </span>
                                            <span class="text-gray-600">
                                                <i class="fas fa-gas-pump text-orange-500 mr-1"></i> 
                                                <strong>{{ $inspection->fuelBefore ?? $inspection->fuelAfter }}</strong>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                                        @php $photos = json_decode($inspection->photosBefore ?? $inspection->photosAfter); @endphp
                                        @if($photos)
                                            @foreach($photos as $photo)
                                                <a href="{{ asset('storage/'.$photo) }}" target="_blank" class="block relative group overflow-hidden rounded-lg border border-gray-200 aspect-square">
                                                    <img src="{{ asset('storage/'.$photo) }}" class="w-full h-full object-cover transition transform group-hover:scale-110">
                                                </a>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="col-span-full text-center py-4 bg-gray-50 rounded-lg text-gray-400 text-xs italic">
                                    No photos uploaded by customer.
                                </div>
                            @endforelse
                        </div>

                        {{-- Staff Uploads --}}
                        <div>
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Staff Verifications</h4>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                @forelse($booking->inspections->whereNotNull('staffID') as $inspection)
                                    @php $photos = json_decode($inspection->photosBefore ?? $inspection->photosAfter); @endphp
                                    @if($photos)
                                        @foreach($photos as $photo)
                                            <a href="{{ asset('storage/'.$photo) }}" target="_blank" class="block relative group overflow-hidden rounded-lg border border-gray-200 aspect-square">
                                                <img src="{{ asset('storage/'.$photo) }}" class="w-full h-full object-cover transition transform group-hover:scale-110">
                                                <div class="absolute bottom-0 left-0 right-0 bg-blue-900/80 text-white text-[10px] p-1 text-center truncate">
                                                    Staff: {{ $inspection->inspectionType }}
                                                </div>
                                            </a>
                                        @endforeach
                                    @endif
                                @empty
                                    <div class="col-span-full text-center py-4 bg-gray-50 rounded-lg text-gray-400 text-xs italic">
                                        No verification photos yet.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 6. CUSTOMER FEEDBACK --}}
                @if($booking->feedback)
                <div class="bg-indigo-50 rounded-2xl shadow-sm border border-indigo-100 p-6 relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-10">
                        <i class="fas fa-star text-6xl text-indigo-600"></i>
                    </div>
                    
                    <h3 class="text-xs font-bold text-indigo-700 uppercase tracking-wider mb-4 border-b border-indigo-200 pb-2">
                        Customer Review
                    </h3>

                    <div class="flex items-center gap-1 mb-3">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="fas fa-star text-lg {{ $i <= $booking->feedback->rating ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                        @endfor
                        <span class="ml-2 text-xs font-bold text-indigo-600 bg-indigo-100 px-2 py-1 rounded">
                            {{ $booking->feedback->rating }}/5
                        </span>
                    </div>

                    @if($booking->feedback->comment)
                        <div class="bg-white/80 rounded-xl p-4 border border-indigo-100 relative z-10">
                            <i class="fas fa-quote-left text-indigo-200 text-xl absolute -top-2 -left-2"></i>
                            <p class="text-sm text-gray-800 italic relative z-10 pl-2">
                                "{{ $booking->feedback->comment }}"
                            </p>
                        </div>
                    @else
                        <p class="text-sm text-gray-500 italic">No written comment provided.</p>
                    @endif
                    
                    <p class="text-[10px] text-indigo-400 mt-3 text-right">
                        Submitted {{ $booking->feedback->created_at->format('d M Y, h:i A') }}
                    </p>
                </div>
                @endif

                {{-- 7. WORKFLOW ACTIONS --}}
                <div class="bg-gray-900 rounded-2xl shadow-lg p-6 text-white">
                    <h3 class="text-lg font-bold mb-4">Manage Booking Status</h3>
                    
                    <div class="flex justify-between items-center mb-6 border-b border-gray-700 pb-4">
                        <p class="text-sm text-gray-400">Current Status: <span class="text-white font-bold">{{ $booking->bookingStatus }}</span></p>
                        
                        {{-- GLOBAL VERIFY BUTTON --}}
                        @php
                            $pendingPayments = $booking->payments->where('paymentStatus', 'Pending Verification');
                        @endphp
                        
                        @if($pendingPayments->count() > 0)
                            <div class="flex items-center gap-3 bg-orange-500/10 border border-orange-500/50 p-2 pr-4 rounded-lg animate-pulse">
                                <i class="fas fa-bell text-orange-500 ml-2"></i>
                                <span class="text-xs font-bold text-orange-400">New Payment Received!</span>
                                <form action="{{ route('staff.bookings.verify_payment', $booking->bookingID) }}" method="POST">
                                    @csrf
                                    <button class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-1.5 rounded text-xs font-bold transition">
                                        Verify Now
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>

                    <div class="flex flex-wrap gap-3">
                        
                        {{-- STEP 1: INITIAL PROCESSING --}}
                        @if(in_array($booking->bookingStatus, ['Submitted', 'Deposit Paid', 'Paid']))
                            @if($pendingPayments->count() > 0)
                                <button disabled class="bg-gray-700 text-gray-400 px-6 py-3 rounded-xl font-bold cursor-not-allowed flex items-center">
                                    <i class="fas fa-lock mr-2"></i> Approve Agreement
                                </button>
                                <p class="w-full text-[10px] text-red-400 mt-1">* Verify payment above to unlock approval.</p>
                                <button onclick="document.getElementById('reject-modal').classList.remove('hidden')" type="button" class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-xl font-bold transition shadow-lg shadow-red-500/20 flex items-center">
                                    <i class="fas fa-ban mr-2"></i> Reject
                                </button>
                            @else
                                <form action="{{ route('staff.bookings.approve_agreement', $booking->bookingID) }}" method="POST">@csrf
                                    <button class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-xl font-bold transition shadow-lg shadow-green-500/20">
                                        2. Approve Agreement
                                    </button>
                                </form>
                                <button onclick="document.getElementById('reject-modal').classList.remove('hidden')" type="button" class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-xl font-bold transition shadow-lg shadow-red-500/20 flex items-center">
                                    <i class="fas fa-ban mr-2"></i> Reject
                                </button>
                            @endif

                        {{-- STEP 2: CONFIRMED --}}
                        @elseif($booking->bookingStatus == 'Confirmed')
                            <form action="{{ route('staff.bookings.pickup', $booking->bookingID) }}" method="POST">@csrf
                                <button class="bg-purple-500 hover:bg-purple-600 text-white px-6 py-3 rounded-xl font-bold transition shadow-lg shadow-purple-500/20">
                                    3. Vehicle Pickup (Handover)
                                </button>
                            </form>

                        {{-- STEP 3: ACTIVE --}}
                        @elseif($booking->bookingStatus == 'Active')
                            <form action="{{ route('staff.bookings.return', $booking->bookingID) }}" method="POST" onsubmit="return confirm('Complete rental & release deposit?');">@csrf
                                <button class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-xl font-bold transition shadow-lg shadow-blue-500/20">
                                    4. Process Return (Complete)
                                </button>
                            </form>

                        {{-- STEP 4: CANCELLATIONS --}}
                        @elseif($booking->bookingStatus == 'Cancelled' && $booking->payment && $booking->payment->depoStatus == 'Requested')
                            <button onclick="document.getElementById('refund-modal').classList.remove('hidden')" class="bg-emerald-500 hover:bg-emerald-600 text-white px-6 py-3 rounded-xl font-bold transition shadow-lg shadow-emerald-500/20 flex items-center">
                                <i class="fas fa-hand-holding-usd mr-2"></i> Process Refund
                            </button>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- STAFF UPLOAD MODAL --}}
<div id="staff-upload-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl">
        <h3 class="text-lg font-bold mb-4">Staff Verification Upload</h3>
        <form action="{{ route('staff.inspections.store', $booking->bookingID) }}" method="POST" enctype="multipart/form-data">            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Inspection Type</label>
                    <select name="type" id="staff-ins-type" class="w-full border-gray-200 rounded-lg text-sm" onchange="updateHint(this.value)">
                        <option value="Pickup">Pickup (5 Photos)</option>
                        <option value="Return">Return (6 Photos)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Photos</label>
                    <input type="file" name="photos[]" multiple required class="text-sm">
                    <p id="ins-hint" class="mt-2 text-[10px] text-gray-500 italic">
                        Required: Front, Back, Left, Right, Dashboard.
                    </p>
                </div>
                <div class="flex justify-end gap-2 pt-4">
                    <button type="button" onclick="this.closest('#staff-upload-modal').classList.add('hidden')" class="px-4 py-2 text-sm font-bold text-gray-500">Cancel</button>
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg text-sm font-bold">Upload</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- REJECT MODAL --}}
<div id="reject-modal" class="fixed inset-0 z-50 hidden bg-black/80 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-2xl w-full max-w-md p-6 shadow-2xl border-2 border-red-100">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-bold text-gray-900 text-lg flex items-center">
                <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i> Reject Booking
            </h3>
            <button onclick="document.getElementById('reject-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-900"><i class="fas fa-times"></i></button>
        </div>
        
        <form action="{{ route('staff.bookings.reject', $booking->bookingID) }}" method="POST">
            @csrf
            
            <div class="mb-4">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Rejection Reason</label>
                <textarea name="reason" rows="3" class="w-full border border-gray-300 rounded-lg p-3 text-sm focus:border-red-500 focus:ring-1 focus:ring-red-500 outline-none" placeholder="e.g., Fake receipt uploaded OR Vehicle unavailable..." required></textarea>
            </div>

            <div class="mb-6">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Action Type</label>
                <div class="grid grid-cols-1 gap-3">
                    <label class="flex items-start p-3 border rounded-xl cursor-pointer hover:bg-red-50 transition border-gray-200 has-[:checked]:border-red-500 has-[:checked]:bg-red-50">
                        <input type="radio" name="reject_action" value="fraud" class="mt-1 text-red-600 focus:ring-red-500" required>
                        <div class="ml-3">
                            <span class="block text-sm font-bold text-gray-900">Fraud / Invalid Receipt</span>
                            <span class="block text-xs text-gray-500">User is cheating. Reject payment & booking. <br><span class="text-red-600 font-bold">NO REFUND.</span></span>
                        </div>
                    </label>

                    <label class="flex items-start p-3 border rounded-xl cursor-pointer hover:bg-green-50 transition border-gray-200 has-[:checked]:border-green-500 has-[:checked]:bg-green-50">
                        <input type="radio" name="reject_action" value="refund" class="mt-1 text-green-600 focus:ring-green-500">
                        <div class="ml-3">
                            <span class="block text-sm font-bold text-gray-900">Valid Payment (Issue Refund)</span>
                            <span class="block text-xs text-gray-500">Payment is real but we cannot fulfill. <br><span class="text-green-600 font-bold">ISSUE REFUND.</span></span>
                        </div>
                    </label>
                </div>
            </div>

            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded-xl shadow-lg transition">
                Confirm Rejection
            </button>
        </form>
    </div>
</div>
{{-- REFUND MODAL --}}
<div id="refund-modal" class="fixed inset-0 z-50 hidden bg-black/80 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-2xl w-full max-w-md p-6 shadow-2xl border border-gray-100">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-bold text-gray-900 text-lg flex items-center">
                <i class="fas fa-hand-holding-usd text-emerald-600 mr-2"></i> Process Refund
            </h3>
            <button onclick="document.getElementById('refund-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-900"><i class="fas fa-times"></i></button>
        </div>
        
        <form action="{{ route('staff.bookings.refund', $booking->bookingID) }}" method="POST">
            @csrf
            
            {{-- Info Box --}}
            <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 mb-4">
                <p class="text-xs text-blue-800 font-bold mb-1">
                    <i class="fas fa-info-circle mr-1"></i> Refund Policy
                </p>
                <p class="text-xs text-blue-700 leading-relaxed">
                    If you are deducting any amount (e.g. for damages, late fees, or cancellation policy), please explain clearly in the remarks below. This will be recorded in the booking details.
                </p>
            </div>

            {{-- Remarks Input --}}
            <div class="mb-6">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Remarks / Deduction Reason</label>
                <textarea name="refund_remarks" rows="3" class="w-full border border-gray-300 rounded-lg p-3 text-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none placeholder-gray-400" placeholder="e.g. Full refund issued via Transfer... OR RM50 deducted for cleaning fee..."></textarea>
            </div>

            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 rounded-xl shadow-lg transition flex items-center justify-center gap-2">
                <i class="fas fa-check-circle"></i> Confirm Refund
            </button>
        </form>
    </div>
</div>

<script>
function toggleCustomerDetails() {
    const details = document.getElementById('cust-details');
    const btn = document.getElementById('toggle-cust-btn');
    const btnText = btn.querySelector('span');
    const btnIcon = btn.querySelector('i');

    if (details.classList.contains('hidden')) {
        details.classList.remove('hidden');
        btnText.innerText = 'Hide Details';
        btnIcon.classList.remove('fa-chevron-down');
        btnIcon.classList.add('fa-chevron-up');
    } else {
        details.classList.add('hidden');
        btnText.innerText = 'Show Details';
        btnIcon.classList.remove('fa-chevron-up');
        btnIcon.classList.add('fa-chevron-down');
    }
}

function updateHint(val) {
    const hint = document.getElementById('ins-hint');
    if(val === 'Return') {
        hint.innerText = "Required: Front, Back, Left, Right, Dashboard + Car Key Location.";
    } else {
        hint.innerText = "Required: Front, Back, Left, Right, Dashboard.";
    }
}
</script>

<style>
@keyframes fade-in-down {
    from { opacity: 0; transform: translateY(-5px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in-down {
    animation: fade-in-down 0.2s ease-out forwards;
}
</style>
@endsection