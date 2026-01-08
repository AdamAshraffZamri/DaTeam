@extends('layouts.app')

@section('content')
{{-- SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- 1. FIXED BACKGROUND --}}
<div class="fixed inset-0 z-0">
    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('hastabg.png') }}');"></div>
    <div class="absolute inset-0 bg-gradient-to-b from-black/80 via-black/50 to-black/90"></div>
</div>

{{-- 2. SCROLLABLE CONTENT --}}
<div class="relative z-10 min-h-screen py-10">
    <div class="container mx-auto px-4 max-w-5xl">

        {{-- ALERTS --}}
        @if(session('status'))
        <div class="bg-green-500/20 backdrop-blur-md border-l-4 border-green-500 text-green-200 p-4 mb-6 rounded shadow-lg flex items-center animate-pulse" role="alert">
            <i class="fas fa-check-circle mr-3 text-xl text-green-500"></i>
            <div><p class="font-bold text-green-400">Success</p><p>{{ session('status') }}</p></div>
        </div>
        @endif
        
        <div class="flex flex-col items-center mb-10">
            {{-- (Status Logic kept same as before) --}}
            @if($user->blacklisted)
                <div class="px-8 py-3 rounded-2xl bg-black/60 border border-red-500 text-red-500 font-black uppercase tracking-[0.2em] shadow-[0_0_20px_rgba(239,68,68,0.4)] flex items-center gap-4 text-lg">
                    <i class="fas fa-ban text-2xl"></i> Account Blacklisted
                </div>
                @if($user->blacklist_reason)
                    <div class="mt-4 bg-gray-900/80 border border-red-500/30 p-4 rounded-xl max-w-lg text-center backdrop-blur-md">
                        <p class="text-[10px] text-red-400 uppercase font-bold mb-1 tracking-widest">Reason for suspension</p>
                        <p class="text-white font-medium">{{ $user->blacklist_reason }}</p>
                    </div>
                @endif
            @elseif($user->accountStat == 'Confirmed' || $user->accountStat == 'active')
                <div class="px-8 py-3 rounded-2xl bg-green-500/20 border border-green-500 text-green-400 font-black uppercase tracking-[0.2em] shadow-[0_0_20px_rgba(34,197,94,0.3)] flex items-center gap-4">
                    <i class="fas fa-check-circle text-2xl"></i> Account Verified
                </div>
            @elseif($user->accountStat == 'pending')
                <div class="px-8 py-3 rounded-2xl bg-yellow-500/20 border border-yellow-500 text-yellow-400 font-black uppercase tracking-[0.2em] shadow-[0_0_20px_rgba(234,179,8,0.3)] animate-pulse flex items-center gap-4">
                    <i class="fas fa-clock text-2xl"></i> Pending Verification
                </div>
                <p class="text-gray-400 text-xs mt-3">Staff is reviewing your details. This usually takes 24 hours.</p>
            @elseif($user->accountStat == 'rejected')
                <div class="px-8 py-3 rounded-2xl bg-red-600/20 border border-red-500 text-red-400 font-black uppercase tracking-[0.2em] shadow-[0_0_20px_rgba(220,38,38,0.3)] flex items-center gap-4">
                    <i class="fas fa-times-circle text-2xl"></i> Verification Rejected
                </div>
                @if($user->rejection_reason)
                    <div class="mt-4 bg-red-900/40 border border-red-500/50 p-6 rounded-2xl max-w-lg text-center backdrop-blur-md shadow-xl">
                        <div class="flex justify-center mb-2"><i class="fas fa-exclamation-triangle text-red-400 text-xl"></i></div>
                        <p class="text-xs text-red-300 uppercase font-bold mb-2 tracking-widest">Please fix the following:</p>
                        <p class="text-white font-bold text-lg leading-relaxed">"{{ $user->rejection_reason }}"</p>
                    </div>
                @endif
            @else
                <div class="px-8 py-3 rounded-2xl bg-white/10 border border-white/20 text-gray-300 font-black uppercase tracking-[0.2em] flex items-center gap-4">
                    <i class="fas fa-user-shield text-2xl"></i> Unverified
                </div>
                <p class="text-gray-400 text-xs mt-3">Please complete your profile to verify your account.</p>
            @endif
        </div>

        {{-- ==================== PROFILE HEADER ==================== --}}
        <div class="text-center mb-8 relative">
            <h1 class="text-3xl font-black text-white drop-shadow-md">Complete Your Profile</h1>
            <p class="text-gray-400 mt-2">These details are required for insurance and refunds.</p>

            <div class="relative mt-6 inline-block">
                {{-- Avatar Display --}}
                <div class="w-32 h-32 rounded-full bg-white/10 backdrop-blur-md flex items-center justify-center border-4 border-white/20 shadow-2xl overflow-hidden">
                    @if($user->avatar)
                        <img src="{{ asset($user->avatar) }}" class="w-full h-full object-cover">
                    @else
                        <i class="fas fa-user text-6xl text-gray-400"></i>
                    @endif
                </div>

                {{-- AVATAR FORM --}}
                <form action="{{ route('profile.avatar.update') }}" method="POST" enctype="multipart/form-data" id="avatar-form">
                    @csrf
                    <input type="file" name="avatar" id="avatar_input" class="hidden" accept="image/*" onchange="document.getElementById('avatar-form').submit();">
                    <button type="button" onclick="document.getElementById('avatar_input').click()" class="absolute bottom-0 right-0 bg-[#ea580c] hover:bg-orange-600 text-white p-2 rounded-full w-8 h-8 flex items-center justify-center transition shadow-lg cursor-pointer z-20">
                        <i class="fas fa-pencil-alt text-xs"></i>
                    </button>
                </form>
            </div>
        </div>

        {{-- ================= FORM 1: PERSONAL & BANK INFO ================= --}}
        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" id="profileForm" onsubmit="return validateBankDetails()" class="bg-black/25 backdrop-blur-xl border border-white/10 rounded-[2.5rem] shadow-2xl p-8 md:p-12 mb-10">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8">
                
                {{-- LEFT COLUMN: Personal Info --}}
                <div class="space-y-6">
                    <h3 class="text-lg font-bold text-orange-500 border-b border-white/10 pb-2 flex items-center gap-2">
                        <i class="fas fa-user-circle"></i> Personal Information
                    </h3>
                    
                    {{-- FULL NAME --}}
                    <div>
                        <label class="block text-gray-400 mb-2 font-bold text-xs uppercase tracking-wider">Full Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $user->fullName) }}" 
                               class="w-full bg-white/5 border border-white/10 rounded-xl p-3 text-white focus:outline-none focus:border-orange-500 focus:bg-white/10 transition placeholder-gray-500 uppercase"
                               required pattern="[a-zA-Z\s]+" title="Name must contain only letters and spaces."
                               oninput="this.value = this.value.toUpperCase()">
                    </div>

                    {{-- EMAIL --}}
                    <div>
                        <label class="block text-gray-400 mb-2 font-bold text-xs uppercase tracking-wider">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full bg-white/5 border border-white/10 rounded-xl p-3 text-white focus:outline-none focus:border-orange-500 focus:bg-white/10 transition placeholder-gray-500" required>
                    </div>

                    {{-- PHONE (UPDATED FORMAT: 000-0000000) --}}
                    <div>
                        <label class="block text-gray-400 mb-2 font-bold text-xs uppercase tracking-wider">Phone No. <span class="text-red-500">*</span></label>
                        <input type="tel" name="phone" value="{{ old('phone', $user->phoneNo) }}" 
                               class="w-full bg-white/5 border border-white/10 rounded-xl p-3 text-white focus:outline-none focus:border-orange-500 focus:bg-white/10 transition placeholder-gray-500"
                               required 
                               placeholder="012-3456789"
                               pattern="\d{3}-\d{7,8}" 
                               title="Format: 012-3456789 (Use hyphens)"
                               oninput="formatPhone(this)">
                    </div>

                    {{-- DOB --}}
                    <div>
                        <label class="block text-gray-400 mb-2 font-bold text-xs uppercase tracking-wider">Date Of Birth</label>
                        <input type="date" name="dob" value="{{ old('dob', optional($user->dob)->format('Y-m-d')) }}" class="w-full bg-white/5 border border-white/10 rounded-xl p-3 text-white focus:outline-none focus:border-orange-500 focus:bg-white/10 transition [color-scheme:dark]">
                    </div>

                    {{-- NATIONALITY --}}
                    <div>
                        <label class="block text-gray-400 mb-2 font-bold text-xs uppercase tracking-wider">Nationality</label>
                        <select name="nationality" class="w-full bg-white/5 border border-white/10 rounded-xl p-3 text-white focus:outline-none focus:border-orange-500 focus:bg-white/10 transition cursor-pointer">
                            <option value="" class="text-black">-- Select Country --</option>
                            @php $countries = ["Malaysia","Indonesia","Singapore","Brunei","Thailand","Vietnam","Philippines","China","India","Pakistan","Bangladesh","Yemen","Saudi Arabia","United Kingdom","United States","Nigeria","Egypt","Japan","Korea, Republic of"]; @endphp
                            @foreach($countries as $country)
                                <option value="{{ $country }}" class="text-black" {{ old('nationality', $user->nationality) == $country ? 'selected' : '' }}>{{ $country }}</option>
                            @endforeach
                            <option value="Other" class="text-black" {{ old('nationality', $user->nationality) == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>

                    {{-- EMERGENCY CONTACT (UPDATED FORMAT) --}}
                    <div class="pt-4 border-t border-white/10">
                        <h4 class="text-xs font-bold text-orange-400 uppercase tracking-widest mb-4">Emergency Contact</h4>
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block text-gray-400 mb-2 font-bold text-xs uppercase tracking-wider">Name <span class="text-red-500">*</span></label>
                                <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name', $user->emergency_contact_name) }}" 
                                       class="w-full bg-white/5 border border-white/10 rounded-xl p-3 text-white focus:outline-none focus:border-orange-500 focus:bg-white/10 transition placeholder-gray-500 uppercase"
                                       required pattern="[a-zA-Z\s]+" oninput="this.value = this.value.toUpperCase()">
                            </div>
                            <div>
                                <label class="block text-gray-400 mb-2 font-bold text-xs uppercase tracking-wider">Phone No. <span class="text-red-500">*</span></label>
                                <input type="tel" name="emergency_contact_no" value="{{ old('emergency_contact_no', $user->emergency_contact_no) }}" 
                                       class="w-full bg-white/5 border border-white/10 rounded-xl p-3 text-white focus:outline-none focus:border-orange-500 focus:bg-white/10 transition placeholder-gray-500"
                                       required 
                                       placeholder="012-3456789"
                                       pattern="\d{3}-\d{7,8}" 
                                       title="Format: 012-3456789"
                                       oninput="formatPhone(this)">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- RIGHT COLUMN: Documents & Address --}}
                <div class="space-y-6">
                    <h3 class="text-lg font-bold text-orange-500 border-b border-white/10 pb-2 flex items-center gap-2">
                        <i class="fas fa-folder-open"></i> Documents & Address
                    </h3>

                    {{-- STUDENT ID --}}
                    <div>
                        <label class="block text-gray-400 mb-2 font-bold text-xs uppercase tracking-wider">Student/Staff ID <span class="text-red-500">*</span></label>
                        <div class="flex">
                            <input type="text" name="student_staff_id" value="{{ old('student_staff_id', $user->stustaffID) }}" 
                                   class="w-full bg-white/5 border border-white/10 rounded-l-xl p-3 text-white focus:outline-none focus:border-orange-500 focus:bg-white/10 transition placeholder-gray-500 uppercase"
                                   required oninput="this.value = this.value.toUpperCase()">
                            <button type="button" onclick="document.getElementById('student_file').click()" class="bg-white/10 px-4 rounded-r-xl border border-l-0 border-white/10 text-gray-400 hover:bg-white/20 hover:text-white transition" id="btn_student">
                                <i class="fas fa-camera" id="icon_student"></i>
                            </button>
                            <input type="file" name="student_card_image" id="student_file" class="hidden" accept="image/*" onchange="fileSelected('student')">
                        </div>
                    </div>

                    {{-- IC/PASSPORT --}}
                    <div>
                        <label class="block text-gray-400 mb-2 font-bold text-xs uppercase tracking-wider">IC / Passport No. <span class="text-red-500">*</span></label>
                        <div class="flex">
                            <input type="text" name="ic_passport" value="{{ old('ic_passport', $user->ic_passport) }}" 
                                   class="w-full bg-white/5 border border-white/10 rounded-l-xl p-3 text-white focus:outline-none focus:border-orange-500 focus:bg-white/10 transition placeholder-gray-500 uppercase"
                                   required oninput="this.value = this.value.toUpperCase()">
                            <button type="button" onclick="document.getElementById('ic_file').click()" class="bg-white/10 px-4 rounded-r-xl border border-l-0 border-white/10 text-gray-400 hover:bg-white/20 hover:text-white transition" id="btn_ic">
                                <i class="fas fa-camera" id="icon_ic"></i>
                            </button>
                            <input type="file" name="ic_passport_image" id="ic_file" class="hidden" accept="image/*" onchange="fileSelected('ic')">
                        </div>
                    </div>

                    {{-- LICENSE --}}
                    <div>
                        <label class="block text-gray-400 mb-2 font-bold text-xs uppercase tracking-wider">Driving License Expired Date <span class="text-red-500">*</span></label>
                        <div class="flex">
                            <input type="date" name="driving_license_expiry" value="{{ old('driving_license_expiry', $user->driving_license_expiry) }}" 
                                   class="w-full bg-white/5 border border-white/10 rounded-l-xl p-3 text-white focus:outline-none focus:border-orange-500 focus:bg-white/10 transition [color-scheme:dark]" required>
                             <button type="button" onclick="document.getElementById('license_file').click()" class="bg-white/10 px-4 rounded-r-xl border border-l-0 border-white/10 text-gray-400 hover:bg-white/20 hover:text-white transition" id="btn_license">
                                <i class="fas fa-camera" id="icon_license"></i>
                            </button>
                            <input type="file" name="driving_license_image" id="license_file" class="hidden" accept="image/*" onchange="fileSelected('license')">
                        </div>
                    </div>

                    {{-- ADDRESS --}}
                    <div>
                        <label class="block text-gray-400 mb-2 font-bold text-xs uppercase tracking-wider">Home Address <span class="text-red-500">*</span></label>
                        <input type="text" name="home_address" value="{{ old('home_address', $user->homeAddress) }}" class="w-full bg-white/5 border border-white/10 rounded-xl p-3 text-white focus:outline-none focus:border-orange-500 focus:bg-white/10 transition placeholder-gray-500" required>
                    </div>

                    {{-- COLLEGE --}}
                    <div>
                        <label class="block text-gray-400 mb-2 font-bold text-xs uppercase tracking-wider">College (UTM JB) <span class="text-red-500">*</span></label>
                        <select name="college_address" class="w-full bg-white/5 border border-white/10 rounded-xl p-3 text-white focus:outline-none focus:border-orange-500 focus:bg-white/10 transition cursor-pointer" required>
                            <option value="" class="text-black">-- Select Residential College --</option>
                            @php $colleges = ["Kolej Rahman Putra (KRP)", "Kolej Tun Fatimah (KTF)", "Kolej Tun Razak (KTR)", "Kolej Tun Hussein Onn (KTHO)", "Kolej Tun Dr. Ismail (KTDI)", "Kolej Tuanku Canselor (KTC)", "Kolej Perdana (KP)", "Kolej 9 & 10", "Kolej Datin Seri Endon (KDSE)", "Kolej Dato' Onn Jaafar (KDOJ)", "Off-Campus (Rental/Family)"]; @endphp
                            @foreach($colleges as $col)
                                <option value="{{ $col }}" class="text-black" {{ old('college_address', $user->collegeAddress) == $col ? 'selected' : '' }}>{{ $col }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- FACULTY --}}
                    <div>
                        <label class="block text-gray-400 mb-2 font-bold text-xs uppercase tracking-wider">Faculty <span class="text-red-500">*</span></label>
                        <select name="faculty" class="w-full bg-white/5 border border-white/10 rounded-xl p-3 text-white focus:outline-none focus:border-orange-500 focus:bg-white/10 transition cursor-pointer" required>
                            <option value="" class="text-black">-- Select Faculty --</option>
                            @php $faculties = ["Faculty of Civil Engineering (FKA)", "Faculty of Mechanical Engineering (FKM)", "Faculty of Electrical Engineering (FKE)", "Faculty of Chemical & Energy Engineering (FCEE)", "Faculty of Computing (FC)", "Faculty of Science (FS)", "Faculty of Built Environment & Surveying (FABU)", "Faculty of Social Sciences & Humanities (FSSH)", "Faculty of Management (FM)", "Razak Faculty of Technology and Informatics", "MJIIT", "AHIBS"]; @endphp
                            @foreach($faculties as $fac)
                                <option value="{{ $fac }}" class="text-black" {{ old('faculty', $user->faculty) == $fac ? 'selected' : '' }}>{{ $fac }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            
            {{-- BANK DETAILS --}}
            <div class="mt-10 pt-8 border-t border-white/10">
                 <h3 class="text-lg font-bold text-orange-500 mb-4 flex items-center gap-2"><i class="fas fa-university"></i> Refund Information (Bank Details)</h3>
                 
                 <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                     <div>
                        <label class="block text-gray-400 mb-2 font-bold text-xs uppercase tracking-wider">Bank Name <span class="text-red-500">*</span></label>
                        <select name="bank_name" id="bankSelect" onchange="updateBankHint()" class="w-full bg-white/5 border border-white/10 rounded-xl p-3 text-white focus:outline-none focus:border-orange-500 focus:bg-white/10 transition cursor-pointer" required>
                            <option value="" class="text-black">-- Select Bank --</option>
                            @php $banks = ["Maybank", "CIMB Bank", "Public Bank", "RHB Bank", "Hong Leong Bank", "AmBank", "UOB Malaysia", "Bank Rakyat", "OCBC Bank", "HSBC Bank", "Bank Islam", "Affin Bank", "Alliance Bank", "Standard Chartered", "MBSB Bank", "BSN (Bank Simpanan Nasional)", "Agrobank", "Bank Muamalat", "Kuwait Finance House", "Al Rajhi Bank", "GXBank (Digital)", "Aeon Bank (Digital)", "Boost Bank (Digital)"]; @endphp
                            @foreach($banks as $bank)
                                <option value="{{ $bank }}" class="text-black" {{ old('bank_name', $user->bankName) == $bank ? 'selected' : '' }}>{{ $bank }}</option>
                            @endforeach
                        </select>
                     </div>
                     <div>
                        <label class="block text-gray-400 mb-2 font-bold text-xs uppercase tracking-wider">Account Number <span class="text-red-500">*</span></label>
                        <input type="text" name="bank_account_no" id="bankAccInput" value="{{ old('bank_account_no', $user->bankAccountNo) }}" 
                               placeholder="e.g. 162234..." 
                               class="w-full bg-white/5 border border-white/10 rounded-xl p-3 text-white focus:outline-none focus:border-orange-500 focus:bg-white/10 transition placeholder-gray-500"
                               required pattern="[0-9]+" title="Only numbers allowed.">
                        <p id="bankError" class="text-red-500 text-[10px] font-bold mt-1 hidden"></p>
                     </div>
                 </div>
            </div>

            <div class="flex justify-end items-center mt-8 pt-4 border-t border-white/10">
                <button type="submit" class="bg-[#ea580c] hover:bg-orange-600 text-white font-bold py-3 px-10 rounded-xl shadow-lg transition transform hover:scale-[1.02]">
                    Save Profile Information
                </button>
            </div>
        </form>

        {{-- ================= FORM 2: IMPROVED PASSWORD UPDATE ================= --}}
        <form action="{{ route('profile.password') }}" method="POST" class="bg-black/25 backdrop-blur-xl border border-white/10 rounded-[2.5rem] shadow-2xl overflow-hidden mb-8">
            @csrf
            @method('PUT')
            
            <div class="px-8 py-6 border-b border-white/10 bg-white/5 flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-orange-500/20 text-orange-500 flex items-center justify-center">
                    <i class="fas fa-lock"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-white">Security Settings</h3>
                    <p class="text-xs text-gray-400">Update your password to keep your account safe.</p>
                </div>
            </div>
            
            <div class="p-8 md:p-12 space-y-8">
                {{-- 1. Current Password --}}
                <div class="max-w-xl"> 
                    <label class="block text-gray-400 mb-2 font-bold text-xs uppercase tracking-wider">Current Password</label>
                    <div class="relative">
                        <input type="password" name="current_password" id="current_password" placeholder="Enter current password" 
                            autocomplete="off" class="w-full bg-white/5 border border-white/10 rounded-xl p-3 text-white focus:outline-none focus:border-orange-500 focus:bg-white/10 transition placeholder-gray-500 pr-10">
                        <button type="button" onclick="togglePassword('current_password', 'icon_curr')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-white transition">
                            <i class="fas fa-eye" id="icon_curr"></i>
                        </button>
                    </div>
                </div>

                <div class="border-t border-white/10"></div>

                {{-- 2. New Password Grid --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-400 mb-2 font-bold text-xs uppercase tracking-wider">New Password</label>
                        <div class="relative">
                            <input type="password" name="password" id="password" placeholder="Min. 8 characters" 
                                autocomplete="new-password" class="w-full bg-white/5 border border-white/10 rounded-xl p-3 text-white focus:outline-none focus:border-orange-500 focus:bg-white/10 transition placeholder-gray-500 pr-10">
                            <button type="button" onclick="togglePassword('password', 'icon_pass')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-white transition">
                                <i class="fas fa-eye" id="icon_pass"></i>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-400 mb-2 font-bold text-xs uppercase tracking-wider">Confirm Password</label>
                        <div class="relative">
                            <input type="password" name="password_confirmation" id="password_confirmation" placeholder="Retype new password" 
                                autocomplete="new-password" class="w-full bg-white/5 border border-white/10 rounded-xl p-3 text-white focus:outline-none focus:border-orange-500 focus:bg-white/10 transition placeholder-gray-500 pr-10">
                            <button type="button" onclick="togglePassword('password_confirmation', 'icon_confirm')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-white transition">
                                <i class="fas fa-eye" id="icon_confirm"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="bg-gray-700 hover:bg-gray-600 border border-gray-600 text-white font-bold py-3 px-8 rounded-xl text-sm shadow-md transition transform hover:scale-[1.02] flex items-center justify-center gap-2">
                        <i class="fas fa-key"></i> Update Password
                    </button>
                </div>
            </div>
        </form>

    </div>
</div>

<script>
    // 1. Phone Formatter Logic
    function formatPhone(input) {
        let numbers = input.value.replace(/\D/g, ''); // Strip non-numeric
        if (numbers.length > 11) numbers = numbers.substring(0, 11); // Max 11 digits
        
        if (numbers.length > 3) {
            input.value = numbers.substring(0, 3) + '-' + numbers.substring(3);
        } else {
            input.value = numbers;
        }
    }

    // 2. File Upload Logic
    function fileSelected(type) {
        const input = document.getElementById(type + '_file');
        const icon = document.getElementById('icon_' + type);
        const btn = document.getElementById('btn_' + type);

        if (input.files && input.files[0]) {
            icon.classList.remove('fa-camera');
            icon.classList.add('fa-check');
            icon.classList.remove('text-gray-400');
            icon.classList.add('text-green-400');
            btn.classList.remove('bg-white/10');
            btn.classList.add('bg-green-500/20', 'border-green-500/30');
        }
    }

    // 3. Password Toggle Logic
    function togglePassword(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        
        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = "password";
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    // 4. Bank Validation Logic
    const bankRules = {
        'Maybank': 12, 'CIMB Bank': 10, 'Public Bank': 10, 'RHB Bank': 10, 'Hong Leong Bank': 10,
        'AmBank': 13, 'UOB Malaysia': 10, 'Bank Rakyat': 10, 'OCBC Bank': 10, 'HSBC Bank': 12,
        'Bank Islam': 14, 'Affin Bank': 12, 'Alliance Bank': 10, 'Standard Chartered': 10,
        'MBSB Bank': 10, 'BSN (Bank Simpanan Nasional)': 16, 'Agrobank': 13, 'Bank Muamalat': 14,
        'Kuwait Finance House': 10, 'Al Rajhi Bank': 15,
        'GXBank (Digital)': 'flex', 'Aeon Bank (Digital)': 'flex', 'Boost Bank (Digital)': 'flex'
    };

    function updateBankHint() {
        const bank = document.getElementById('bankSelect').value;
        const input = document.getElementById('bankAccInput');
        const errorMsg = document.getElementById('bankError');
        
        errorMsg.classList.add('hidden');
        input.classList.remove('border-red-500');
        input.classList.add('border-white/10');

        if (bank && bankRules[bank] && bankRules[bank] !== 'flex') {
            input.placeholder = `Enter ${bankRules[bank]} digits`;
        } else {
            input.placeholder = "Enter account number";
        }
    }

    function validateBankDetails() {
        const bank = document.getElementById('bankSelect').value;
        const accNum = document.getElementById('bankAccInput').value.trim();
        const errorMsg = document.getElementById('bankError');
        const input = document.getElementById('bankAccInput');

        // Only validate if bank is selected
        if (bank && bankRules[bank]) {
            const rule = bankRules[bank];
            const length = accNum.length;

            // Check if numeric
            if (!/^\d+$/.test(accNum)) {
                errorMsg.innerText = "Invalid! Only numbers allowed.";
                errorMsg.classList.remove('hidden');
                input.classList.add('border-red-500');
                input.focus();
                return false;
            }

            // Check length (if rigid)
            if (rule !== 'flex' && length !== rule) {
                errorMsg.innerText = `Invalid! ${bank} account must be exactly ${rule} digits.`;
                errorMsg.classList.remove('hidden');
                input.classList.remove('border-white/10');
                input.classList.add('border-red-500');
                input.focus();
                return false;
            }
        }
        return true;
    }
</script>

{{-- Validation Error Popup --}}
@if ($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const errors = @json($errors->messages());
        let errorMessages = '<ul style="text-align: left;">';
        for (const [field, messages] of Object.entries(errors)) {
            messages.forEach(message => { errorMessages += `<li style="margin: 8px 0;">${message}</li>`; });
        }
        errorMessages += '</ul>';
        
        Swal.fire({
            icon: 'error',
            title: '❌ Validation Error',
            html: errorMessages,
            confirmButtonColor: '#ea580c',
            confirmButtonText: 'OK',
            background: '#1f2937',
            color: '#fff'
        });
    });
</script>
@endif

@endsection