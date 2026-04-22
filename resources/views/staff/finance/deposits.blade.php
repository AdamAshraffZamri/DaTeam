@extends('layouts.staff')

@section('content')
<div class="min-h-screen bg-slate-100 rounded-2xl p-6">
    <div class="max-w-7xl mx-auto">

        {{-- STATUS MESSAGES --}}
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-2xl flex items-center gap-3 animate-fade-in">
                <i class="fas fa-check-circle"></i>
                <span class="text-sm font-bold">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-2xl flex items-center gap-3 animate-fade-in">
                <i class="fas fa-exclamation-circle"></i>
                <span class="text-sm font-bold">{{ session('error') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-500 text-white rounded-2xl animate-fade-in">
                <ul class="list-disc pl-5 text-sm font-bold">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- HEADER & FILTERS --}}
        <div class="flex flex-col md:flex-row justify-between items-end mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-black text-gray-900">Deposit Management</h1>
                <p class="text-gray-500 mt-1 text-sm">Track and process customer security deposits and refunds.</p>
            </div>

            <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
                <form action="{{ route('staff.finance.deposits') }}" method="GET" id="filterForm" class="flex flex-col md:flex-row items-center gap-3 w-full xl:w-auto">
                    
                    {{-- 1. SEARCH --}}
                    <div class="relative group w-full md:w-64">
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Search ID, Customer..." 
                               class="w-full pl-10 pr-4 py-3.5 rounded-2xl border border-gray-200 bg-white text-sm font-bold text-gray-700 focus:ring-2 focus:ring-gray-900 focus:border-transparent transition-all shadow-sm group-hover:border-gray-300">
                        <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 group-hover:text-gray-600 transition-colors"></i>
                    </div>

                    {{-- 2. STATUS DROPDOWN --}}
                    @php
                        $currentStatus = request('status', 'not_updated');
                        $statuses = [
                            'not_updated' => 'Not Updated Yet',
                            'updated'     => 'Updated'
                        ];
                        $currentLabel = $statuses[$currentStatus] ?? 'Not Updated Yet';
                        $currentCount = $counts[$currentStatus] ?? 0;
                    @endphp

                    <input type="hidden" name="status" id="statusInput" value="{{ $currentStatus }}">

                    <div class="relative w-full md:w-[220px]" id="customDropdown">
                        <button type="button" onclick="toggleDropdown()" 
                            class="w-full flex items-center justify-between bg-white border border-gray-200 text-gray-700 text-xs font-bold py-3.5 px-5 rounded-2xl hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm group">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-filter text-orange-500"></i>
                                <span id="dropdownLabel" class="truncate">{{ $currentLabel }}</span>
                                <span class="flex items-center justify-center w-5 h-5 rounded-full text-[9px] bg-orange-100 text-orange-700 ml-1">
                                    {{ $currentCount }}
                                </span>
                            </div>
                            <i class="fas fa-chevron-down text-[10px] text-gray-400 group-hover:text-gray-600 transition-transform duration-300" id="dropdownArrow"></i>
                        </button>

                        <div id="dropdownMenu" class="absolute top-full right-0 mt-2 w-full bg-white border border-gray-100 rounded-2xl shadow-xl overflow-hidden hidden transform origin-top transition-all duration-200 z-50">
                            @foreach($statuses as $value => $label)
                                <div onclick="selectStatus('{{ $value }}')" 
                                     class="px-5 py-3 text-xs font-bold cursor-pointer transition-colors flex items-center justify-between border-b border-gray-50 last:border-0
                                     {{ $currentStatus == $value ? 'bg-orange-50 text-orange-600' : 'text-gray-600 hover:bg-gray-50' }}">
                                    <span>{{ $label }}</span>
                                    <span class="flex items-center justify-center w-5 h-5 rounded-full text-[9px] {{ $currentStatus == $value ? 'bg-orange-200 text-orange-700' : 'bg-gray-100 text-gray-500' }}">
                                        {{ $counts[$value] ?? 0 }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- 3. DATE FILTER --}}
                    <div class="relative group w-full md:w-48">
                        <input type="date" name="date" value="{{ request('date') }}" 
                            onchange="document.getElementById('filterForm').submit()"
                            class="w-full pl-10 pr-4 py-3.5 rounded-2xl border border-gray-200 bg-white text-sm font-bold text-gray-700 focus:ring-2 focus:ring-gray-900 focus:border-transparent transition-all shadow-sm group-hover:border-gray-300">
                        <i class="fas fa-calendar-alt absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 group-hover:text-gray-600 transition-colors"></i>
                    </div>

                    <a href="https://www.tracksolidpro.com/" target="_blank" onclick="event.stopPropagation()"
                           class="w-10 h-10 rounded-lg border border-gray-200 bg-white text-slate-500 hover:bg-slate-200 flex items-center justify-center transition-all shrink-0" title="GPS - TrackSolid Pro">
                            <i class="fas fa-location-arrow text-xs"></i>
                    </a>
                </form>
            </div>
        </div>

        {{-- DEPOSIT LIST --}}
        <div class="space-y-3">
            @forelse($bookings as $booking)
            @php
                $depositPayment = $booking->payments->where('depoAmount', '>', 0)->first();
                $isFullRefund = in_array($booking->bookingStatus, ['Cancelled', 'Rejected']);
                $finalAmount = $isFullRefund 
                    ? $booking->payments->whereNotIn('paymentStatus', ['Void', 'Rejected'])->sum('amount') 
                    : ($depositPayment ? $depositPayment->depoAmount : 0);
                $currentDepoStatus = $depositPayment ? $depositPayment->depoStatus : 'Unknown';
                $hasUpdate = $depositPayment && !empty($depositPayment->remarks);
                
                $isProcessed = ($currentDepoStatus === 'Processed');
                $btnStyle = $isProcessed ? 'bg-slate-800 hover:bg-slate-900' : 'bg-indigo-600 hover:bg-indigo-700';
            @endphp
            <div class="booking-row bg-white rounded-xl p-4 border border-gray-100 shadow-sm hover:shadow-md transition-all group cursor-pointer hover:border-gray-300 animate-fade-in overflow-hidden" 
                onclick="window.location='{{ route('staff.bookings.show', $booking->bookingID) }}'">
                
                <div class="flex flex-col lg:flex-row items-center w-full">
                    
                    {{-- 1. NO & Customer --}}
                    <div class="flex items-center gap-4 w-full lg:w-[23%] shrink-0">
                        <div class="w-10 h-10 rounded-lg bg-red-50 flex flex-col items-center justify-center border border-orange-200 shrink-0">
                            <span class="text-xs font-black text-gray-500 group-hover:text-orange-600 transition-colors">#{{ $booking->bookingID }}</span>
                        </div>
                        <div class="overflow-hidden">
                            <h4 class="text-sm font-bold text-gray-900 truncate" title="{{ $booking->customer->fullName ?? 'Guest' }}">
                                {{ $booking->customer->fullName ?? 'Guest' }}
                            </h4>
                            <div class="flex flex-col">
                                <p class="text-sm text-blue-600 font-bold tracking-wider truncate">
                                    {{ $booking->customer->bankAccountNo ?? 'No Account No.' }}
                                </p>
                                <p class="text-[10px] text-gray-400 font-medium uppercase ">
                                    {{ $booking->customer->bankName ?? 'No Bank Info' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Vehicle (12%) --}}
                    <div class="w-full lg:w-[12%] border-t lg:border-t-0 lg:border-l border-gray-100 pt-2 lg:pt-0 lg:pl-4 shrink-0">
                        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Vehicle</p>
                        <div class="text-sm font-bold text-gray-800 truncate">{{ $booking->vehicle->model }}</div>
                        <span class="text-[11px] font-mono font-black text-gray-600 bg-gray-50 px-1.5 py-0.5 rounded border border-gray-200 mt-0.5 inline-block uppercase">
                            {{ $booking->vehicle->plateNo }}
                        </span>
                    </div>

                    {{-- 3. Pickup & Return DateTime --}}
                    <div class="w-full lg:w-[20%] border-t lg:border-t-0 lg:border-l border-gray-100 pt-2 lg:pt-0 lg:pl-6 shrink-0">
                        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-2">Pickup & Return</p>
                        <div class="flex flex-col gap-2 items-start text-xs">
                            {{-- Pickup Row --}}
                            <div class="flex items-center gap-2">
                                <i class="fas fa-sign-out-alt text-green-600 text-[10px] flex-shrink-0"></i>
                                <div class="flex items-baseline gap-2">
                                    <span class="font-bold text-gray-900 whitespace-nowrap">{{ \Carbon\Carbon::parse($booking->originalDate)->format('d M Y') }}</span>
                                    <span class="text-[10px] text-gray-500 whitespace-nowrap font-medium">{{ \Carbon\Carbon::createFromFormat('H:i:s', $booking->bookingTime)->format('h:i A') }}</span>
                                </div>
                            </div>
                            {{-- Return Row --}}
                            <div class="flex items-center gap-2">
                                <i class="fas fa-sign-in-alt text-red-600 text-[10px] flex-shrink-0"></i>
                                <div class="flex items-baseline gap-2">
                                    <span class="font-bold text-gray-900 whitespace-nowrap">{{ \Carbon\Carbon::parse($booking->returnDate)->format('d M Y') }}</span>
                                    <span class="text-[10px] text-gray-500 whitespace-nowrap font-medium">{{ \Carbon\Carbon::createFromFormat('H:i:s', $booking->returnTime)->format('h:i A') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 4. Refund (15%) --}}
                    <div class="w-full lg:w-[15%] border-t lg:border-t-0 lg:border-l border-gray-100 pt-2 lg:pt-0 lg:pl-4 shrink-0">
                        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-1">Refund</p>
                        @if($hasUpdate)
                            <span class="text-sm font-bold text-red-600">RM {{ number_format($finalAmount, 2) }}</span>
                        @else
                            <span class="text-sm font-black text-red-600">RM {{ number_format($finalAmount, 2) }}</span>
                        @endif
                        <p class="text-[9px] text-slate-400 font-bold uppercase">{{ $isFullRefund ? 'Full' : 'Deposit Only' }}</p>
                    </div>

                    {{-- 5. Status & Remarks (15%) --}}
                    <div class="w-full lg:w-[15%] border-t lg:border-t-0 lg:border-l border-gray-100 pt-2 lg:pt-0 lg:pl-4 shrink-0">
                        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-1">Status / Remarks</p>
                        @if($hasUpdate)
                            <div class="flex flex-col gap-0.5">
                                <span class="text-[8px] font-black uppercase text-emerald-600">Updated</span>
                                <p class="text-[10px] text-slate-700 leading-tight line-clamp-1 italic" title="{{ $depositPayment->remarks }}">
                                    "{{ $depositPayment->remarks }}"
                                </p>
                            </div>
                        @else
                            <span class="text-[9px] text-slate-400 italic">No updates yet</span>
                        @endif
                    </div>

                    {{-- 6. ACTION (15%) --}}
                    <div class="w-full lg:flex-1 flex justify-end items-center gap-2 pt-2 lg:pt-0 border-t lg:border-t-0 border-gray-100 lg:pl-4 shrink-0" onclick="event.stopPropagation()">                     
                        <button type="button"
                            onclick="event.stopPropagation(); openAdjustmentModal('{{ route('staff.finance.update_deposit', $booking->bookingID) }}', '{{ $finalAmount }}', '{{ addslashes($depositPayment->remarks ?? '') }}', '{{ json_encode($depositPayment->depo_evidence ?? []) }}')"
                            class="{{ $btnStyle }} text-white px-3 py-2 rounded-lg text-[10px] font-bold uppercase tracking-wide transition shadow-sm flex items-center gap-1.5 whitespace-nowrap">
                            <i class="fas {{ $isProcessed ? 'fa-history' : 'fa-edit' }}"></i> 
                            {{ $isProcessed ? 'Re-update' : 'Update' }}
                        </button>
                    </div>
                </div>
            </div>
            @empty
                <div class="flex flex-col items-center justify-center py-20 text-center bg-white rounded-xl border border-dashed border-gray-200">
                    <i class="fas fa-inbox text-gray-200 text-4xl mb-4"></i>
                    <p class="text-gray-500 font-medium">No deposits found in this category.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

{{-- ADJUSTMENT MODAL --}}
<div id="adjustment-modal" class="fixed inset-0 z-50 hidden bg-slate-900/40 flex items-center justify-center p-4 backdrop-blur-sm transition-opacity duration-300">
    <div class="bg-white rounded-[2rem] w-full max-w-md p-8 shadow-2xl border border-white/50 transform transition-all scale-100">
        
        <div class="flex justify-between items-center mb-6">
            <h3 class="font-black text-slate-900 text-xl flex items-center gap-2">
                <i class="fas fa-calculator text-indigo-500"></i> Update Deposit
            </h3>
            <button type="button" onclick="closeAdjustmentModal()" class="w-8 h-8 rounded-full bg-slate-50 text-slate-400 hover:bg-slate-100 hover:text-slate-600 flex items-center justify-center transition">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form id="adjustment-form" action="" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PATCH')
            
            <div class="mb-6">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Final Refund Amount (RM)</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 font-black text-slate-600">RM</span>
                    <input type="number" name="adjusted_amount" id="adjusted_amount" step="0.01" required
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl py-4 pl-12 pr-4 text-lg font-black text-slate-800 focus:bg-white focus:border-indigo-500 transition-all outline-none">
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Evidence / Documents</label>
                
                {{-- EXISTING FILES --}}
                <div id="evidence-preview-container" class="grid grid-cols-4 gap-2 mb-4 hidden"></div>

                {{-- NEW UPLOAD --}}
                <div class="relative group">
                    <input type="file" name="attachments[]" id="attachments" multiple
                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20">
                    <div id="upload-placeholder" class="w-full bg-slate-50 border-2 border-dashed border-slate-200 rounded-2xl py-6 flex flex-col items-center justify-center transition-all">
                        <i class="fas fa-file-upload text-2xl text-slate-300 mb-2" id="multi-upload-icon"></i>
                        <span class="text-[10px] font-black text-slate-200 uppercase tracking-widest text-center px-4" id="multi-file-label">
                            Select documents or images
                        </span>
                    </div>
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Staff Remarks / Explanation</label>
                <textarea name="remarks" id="modal_remarks" rows="3" required
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl p-4 text-sm font-bold text-slate-800 focus:bg-white focus:border-indigo-500 outline-none transition-all placeholder-slate-300" 
                    placeholder="Deposit fully refunded / Deposit partially refunded / Deposit hold / Other..."></textarea>
            </div>

            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 rounded-xl shadow-lg transition transform active:scale-95 uppercase tracking-wide text-xs flex items-center justify-center gap-2">
                Save & Update <i class="fas fa-save"></i>
            </button>
        </form>
    </div>
</div>

<script>
    // --- 1. DROPDOWN LOGIC ---
    function toggleDropdown() {
        document.getElementById('dropdownMenu').classList.toggle('hidden');
        document.getElementById('dropdownArrow').style.transform = document.getElementById('dropdownMenu').classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
    }

    function selectStatus(value) {
        document.getElementById('statusInput').value = value;
        document.getElementById('filterForm').submit();
    }

    window.onclick = function(event) {
        if (!event.target.closest('#customDropdown')) {
            document.getElementById('dropdownMenu').classList.add('hidden');
            document.getElementById('dropdownArrow').style.transform = 'rotate(0deg)';
        }
    }

    // --- 2. MODAL CORE LOGIC ---
    function openAdjustmentModal(actionUrl, currentAmount, currentRemarks, evidenceJson) {
        const modal = document.getElementById('adjustment-modal');
        const container = document.getElementById('evidence-preview-container');
        const evidence = JSON.parse(evidenceJson || '[]');

        document.getElementById('adjustment-form').action = actionUrl;
        document.getElementById('adjusted_amount').value = currentAmount;
        document.getElementById('modal_remarks').value = currentRemarks;

        // Reset File UI
        document.getElementById('attachments').value = '';
        resetFileUploadUI();

        // Render Evidence
        container.innerHTML = '';
        if (evidence.length > 0) {
            container.classList.remove('hidden');
            evidence.forEach((path) => {
                const ext = path.split('.').pop().toLowerCase();
                const isImg = ['jpg', 'jpeg', 'png', 'webp'].includes(ext);
                
                let preview = isImg ? `<img src="/storage/${path}" class="w-full h-full object-cover">` 
                                    : `<div class="w-full h-full flex flex-col items-center justify-center bg-slate-50"><i class="fas fa-file-alt text-slate-400"></i><span class="text-[8px] font-bold uppercase">${ext}</span></div>`;

                const thumb = document.createElement('div');
                thumb.className = "relative aspect-square rounded-lg overflow-hidden border border-slate-200 group/thumb bg-white";
                thumb.innerHTML = `${preview}
                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover/thumb:opacity-100 transition-opacity flex items-center justify-center gap-2">
                        <a href="/storage/${path}" target="_blank" class="text-white text-xs"><i class="fas fa-eye"></i></a>
                        <button type="button" onclick="deleteEvidenceFile('${path}')" class="text-white text-xs hover:text-red-400"><i class="fas fa-trash-alt"></i></button>
                    </div>`;
                container.appendChild(thumb);
            });
        } else {
            container.classList.add('hidden');
        }

        modal.classList.remove('hidden');
    }

    function closeAdjustmentModal() {
        document.getElementById('adjustment-modal').classList.add('hidden');
    }

    function resetFileUploadUI() {
        const lbl = document.getElementById('multi-file-label');
        const ico = document.getElementById('multi-upload-icon');
        const ph = document.getElementById('upload-placeholder');
        lbl.innerText = "Select documents or images";
        lbl.className = "text-[10px] font-black text-slate-400 uppercase tracking-widest text-center px-4";
        ico.className = "fas fa-file-upload text-2xl text-slate-300 mb-2";
        ph.className = "w-full bg-slate-50 border-2 border-dashed border-slate-200 rounded-2xl py-6 flex flex-col items-center justify-center transition-all";
    }

    // --- 3. GREEN SELECTION FEEDBACK ---
    document.addEventListener('change', function(e) {
        if (e.target && e.target.id === 'attachments') {
            const input = e.target;
            const lbl = document.getElementById('multi-file-label');
            const ico = document.getElementById('multi-upload-icon');
            const ph = document.getElementById('upload-placeholder');
            
            if (input.files.length > 0) {
                lbl.innerText = input.files.length + (input.files.length === 1 ? " file" : " files") + " selected";
                lbl.classList.replace('text-slate-400', 'text-green-600');
                ico.classList.replace('text-slate-300', 'text-green-500');
                ph.classList.add('border-green-300', 'bg-green-50/30');
            } else {
                resetFileUploadUI();
            }
        }
    });

    // --- 4. DELETE EVIDENCE (AJAX) ---
    function deleteEvidenceFile(path) {
        if (!confirm('Permanently delete this evidence?')) return;

        fetch('{{ route("staff.finance.delete_evidence") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ path: path })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Deleted successfully');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(() => alert('Server error occurred during deletion.'));
    }
</script>

<style>
    @keyframes fade-in { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
    .animate-fade-in { animation: fade-in 0.2s ease-out forwards; }
    .line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
    .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
</style>
@endsection