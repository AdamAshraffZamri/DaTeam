@extends('layouts.staff')

@section('content')
<div class="min-h-screen bg-gray-100 p-6">
    <div class="max-w-6xl mx-auto">
        
        {{-- BACK BUTTON --}}
        <a href="{{ route('staff.bookings.index') }}" class="inline-flex items-center text-gray-500 hover:text-gray-900 mb-6 transition">
            <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
        </a>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            {{-- LEFT COLUMN: CUSTOMER & VEHICLE INFO --}}
            <div class="space-y-6">
                
                {{-- 1. CUSTOMER DETAILS --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4 border-b border-gray-100 pb-2">
                        Customer Information
                    </h3>
                    
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 text-xl font-bold">
                            {{ substr($booking->customer->name ?? 'G', 0, 1) }}
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900">{{ $booking->customer->name ?? 'Guest User' }}</h4>
                            <p class="text-xs text-gray-500">Joined {{ optional($booking->customer->created_at)->format('M Y') ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Email</span>
                            <span class="font-medium text-gray-900 truncate max-w-[150px]" title="{{ $booking->customer->email }}">{{ $booking->customer->email }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Phone</span>
                            <a href="tel:{{ $booking->customer->phoneNo }}" class="font-medium text-blue-600 hover:underline">
                                {{ $booking->customer->phoneNo }}
                            </a>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">IC / Passport</span>
                            <span class="font-medium text-gray-900">{{ $booking->customer->ic_passport }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">License No</span>
                            <span class="font-medium text-gray-900">{{ $booking->customer->drivingNo }}</span>
                        </div>
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <p class="text-gray-500 text-xs mb-1">Home Address</p>
                            <p class="font-medium text-gray-900 leading-tight text-xs">{{ $booking->customer->homeAddress }}</p>
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
                {{-- 2. VEHICLE DETAILS --}}
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

                {{-- 3. PAYMENT & DOCUMENTS (UPDATED) --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4 border-b border-gray-100 pb-2">
                        Payment & Documents
                    </h3>
                    
                    {{-- Financials --}}
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-gray-500 text-sm">Total Cost</span>
                        <span class="font-bold text-gray-900">RM {{ number_format($booking->totalCost, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center mb-6">
                        <span class="text-gray-500 text-sm">Deposit Status</span>
                        @if($booking->payment)
                            <span class="text-xs font-bold px-2 py-1 rounded 
                                {{ $booking->payment->depoStatus == 'Refunded' ? 'bg-green-100 text-green-700' : 
                                  ($booking->payment->depoStatus == 'Requested' ? 'bg-red-100 text-red-700 animate-pulse' : 'bg-orange-100 text-orange-700') }}">
                                {{ $booking->payment->depoStatus }}
                            </span>
                        @else
                            <span class="text-xs font-bold px-2 py-1 rounded bg-gray-100 text-gray-500">Unpaid</span>
                        @endif
                    </div>
                    
                    <div class="space-y-3">
                        {{-- A. PAYMENT RECEIPT --}}
                        @if($booking->payment && $booking->payment->installmentDetails)
                            <a href="{{ asset('storage/' . $booking->payment->installmentDetails) }}" target="_blank" class="flex items-center justify-center w-full bg-blue-50 border border-blue-200 text-blue-700 py-2.5 rounded-lg text-sm font-bold hover:bg-blue-100 transition">
                                <i class="fas fa-receipt mr-2"></i> View Payment Receipt
                            </a>
                        @else
                            <div class="w-full bg-gray-50 border border-gray-200 text-gray-400 py-2.5 rounded-lg text-sm font-bold text-center flex items-center justify-center">
                                <i class="fas fa-times mr-2"></i> No Receipt
                            </div>
                        @endif

                        {{-- B. AGREEMENT (ADDED THIS) --}}
                        @if($booking->aggreementLink)
                            <a href="{{ asset('storage/' . $booking->aggreementLink) }}" target="_blank" class="flex items-center justify-center w-full bg-purple-50 border border-purple-200 text-purple-700 py-2.5 rounded-lg text-sm font-bold hover:bg-purple-100 transition">
                                <i class="fas fa-file-signature mr-2"></i> View Signed Agreement
                            </a>
                        @else
                            <div class="w-full bg-gray-50 border border-gray-200 text-gray-400 py-2.5 rounded-lg text-sm font-bold text-center flex items-center justify-center">
                                <i class="fas fa-times mr-2"></i> No Agreement
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- RIGHT COLUMN: INSPECTIONS & ACTIONS --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- 0. STAFF ASSIGNMENT CARD (NEW) --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col md:flex-row justify-between items-center gap-4">
                    
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold text-lg shadow-md
                            {{ $booking->staff ? 'bg-indigo-600' : 'bg-gray-300' }}">
                            {{ $booking->staff ? substr($booking->staff->fullName, 0, 1) : '?' }}
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Staff In Charge</h4>
                            @if($booking->staff)
                                <p class="text-lg font-bold text-gray-900">{{ $booking->staff->fullName }}</p>
                                <p class="text-xs text-indigo-600 font-medium">ID: #{{ $booking->staff->staffID }}</p>
                            @else
                                <p class="text-lg font-bold text-gray-400 italic">Unassigned</p>
                            @endif
                        </div>
                    </div>

                    {{-- Assignment Form --}}
                    <form action="{{ route('staff.bookings.assign', $booking->bookingID) }}" method="POST" class="flex items-center gap-2 w-full md:w-auto">
                        @csrf
                        <select name="staff_id" class="bg-gray-50 border border-gray-200 text-gray-700 text-sm rounded-lg p-2.5 focus:ring-indigo-500 focus:border-indigo-500 block w-full md:w-48">
                            <option value="" disabled selected>Select Staff...</option>
                            @foreach($allStaff as $staffMember)
                                <option value="{{ $staffMember->staffID }}" {{ $booking->staffID == $staffMember->staffID ? 'selected' : '' }}>
                                    {{ $staffMember->fullName }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-4 rounded-lg text-sm transition">
                            Assign
                        </button>
                    </form>
                </div>

                {{-- 4. INSPECTION GALLERY --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-bold text-gray-900">Inspection Gallery</h3>
                        {{-- STAFF UPLOAD BUTTON --}}
                        <button onclick="document.getElementById('staff-upload-modal').classList.remove('hidden')" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-xs font-bold hover:bg-blue-700 transition shadow-lg shadow-blue-500/20">
                            <i class="fas fa-camera mr-1"></i> Staff Upload
                        </button>
                    </div>

                    <div class="space-y-8">
                        {{-- Customer Uploads --}}
                        <div>
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Customer Uploads</h4>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                @forelse($booking->inspections->whereNull('staffID') as $inspection)
                                    @php $photos = json_decode($inspection->photosBefore ?? $inspection->photosAfter); @endphp
                                    @if($photos)
                                        @foreach($photos as $photo)
                                            <a href="{{ asset('storage/'.$photo) }}" target="_blank" class="block relative group overflow-hidden rounded-lg border border-gray-200 aspect-square">
                                                <img src="{{ asset('storage/'.$photo) }}" class="w-full h-full object-cover transition transform group-hover:scale-110">
                                                <div class="absolute bottom-0 left-0 right-0 bg-black/60 text-white text-[10px] p-1 text-center truncate">
                                                    {{ $inspection->inspectionType }}
                                                </div>
                                            </a>
                                        @endforeach
                                    @endif
                                @empty
                                    <div class="col-span-full text-center py-4 bg-gray-50 rounded-lg text-gray-400 text-xs italic">
                                        No photos uploaded by customer.
                                    </div>
                                @endforelse
                            </div>
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

                {{-- 5. WORKFLOW ACTIONS --}}
                <div class="bg-gray-900 rounded-2xl shadow-lg p-6 text-white">
                    <h3 class="text-lg font-bold mb-4">Manage Booking Status</h3>
                    <p class="text-sm text-gray-400 mb-6">Current Status: <span class="text-white font-bold">{{ $booking->bookingStatus }}</span></p>

                    <div class="flex flex-wrap gap-3">
                        @if($booking->bookingStatus == 'Submitted')
                            {{-- Step 1: Verify Payment --}}
                            @if(!$booking->payment || $booking->payment->paymentStatus !== 'Verified')
                                <form action="{{ route('staff.bookings.verify_payment', $booking->bookingID) }}" method="POST">@csrf
                                    <button class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-3 rounded-xl font-bold transition shadow-lg shadow-orange-500/20">
                                        1. Verify Payment
                                    </button>
                                </form>

                                {{-- [NEW] REJECT BUTTON (For Fraud/Issues) --}}
                                <button onclick="document.getElementById('reject-modal').classList.remove('hidden')" type="button" class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-xl font-bold transition shadow-lg shadow-red-500/20 flex items-center">
                                    <i class="fas fa-ban mr-2"></i> Reject
                                </button>
                            @else
                                {{-- Step 2: Approve Agreement --}}
                                <form action="{{ route('staff.bookings.approve_agreement', $booking->bookingID) }}" method="POST">@csrf
                                    <button class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-xl font-bold transition shadow-lg shadow-green-500/20">
                                        2. Approve Agreement
                                    </button>
                                </form>

                                {{-- [NEW] REJECT BUTTON (Even if verified, maybe car unavailable) --}}
                                <button onclick="document.getElementById('reject-modal').classList.remove('hidden')" type="button" class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-xl font-bold transition shadow-lg shadow-red-500/20 flex items-center">
                                    <i class="fas fa-ban mr-2"></i> Reject
                                </button>
                            @endif

                        @elseif($booking->bookingStatus == 'Confirmed')
                            {{-- Step 3: Pickup --}}
                            <form action="{{ route('staff.bookings.pickup', $booking->bookingID) }}" method="POST">@csrf
                                <button class="bg-purple-500 hover:bg-purple-600 text-white px-6 py-3 rounded-xl font-bold transition shadow-lg shadow-purple-500/20">
                                    3. Vehicle Pickup (Handover)
                                </button>
                            </form>

                        @elseif($booking->bookingStatus == 'Active')
                            {{-- Step 4: Return --}}
                            <form action="{{ route('staff.bookings.return', $booking->bookingID) }}" method="POST" onsubmit="return confirm('Complete rental & release deposit?');">@csrf
                                <button class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-xl font-bold transition shadow-lg shadow-blue-500/20">
                                    4. Process Return (Complete)
                                </button>
                            </form>

                        @elseif($booking->bookingStatus == 'Cancelled' && $booking->payment && $booking->payment->depoStatus == 'Requested')
                            {{-- Refund --}}
                            <form action="{{ route('staff.bookings.refund', $booking->bookingID) }}" method="POST" onsubmit="return confirm('Issue refund to customer?');">@csrf
                                <button class="bg-red-500 hover:bg-red-600 text-white px-6 py-3 rounded-xl font-bold transition shadow-lg shadow-red-500/20 flex items-center">
                                    <i class="fas fa-hand-holding-usd mr-2"></i> Approve Refund
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- STAFF UPLOAD MODAL --}}
<div id="staff-upload-modal" class="fixed inset-0 z-50 hidden bg-black/80 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-2xl w-full max-w-md p-6 shadow-2xl">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-bold text-gray-900 text-lg">Staff Inspection Upload</h3>
            <button onclick="document.getElementById('staff-upload-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-900"><i class="fas fa-times"></i></button>
        </div>
        
        <form action="{{ route('staff.inspections.store', $booking->bookingID) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-4">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Inspection Stage</label>
                <select name="type" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm bg-gray-50 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
                    <option value="Pickup">Pickup (Pre-Rental)</option>
                    <option value="Return">Return (Post-Rental)</option>
                </select>
            </div>

            <label class="block w-full h-32 border-2 border-dashed border-gray-300 rounded-xl flex flex-col items-center justify-center cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition mb-6">
                <i class="fas fa-camera text-2xl text-gray-400 mb-2"></i>
                <span class="text-sm text-gray-500">Tap to Select Photos</span>
                <input type="file" name="photos[]" multiple class="hidden" required onchange="this.previousElementSibling.innerText = this.files.length + ' files selected'">
            </label>

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl shadow-lg transition">
                Upload Verification
            </button>
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
@endsection