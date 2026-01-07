@extends('layouts.app')

@section('content')
{{-- SweetAlert2 for Validation Popups --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- 1. FIXED BACKGROUND --}}
<div class="fixed inset-0 z-0">
    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('hastabg.png') }}');"></div>
    <div class="absolute inset-0 bg-gradient-to-b from-black/80 via-black/50 to-black/90"></div>
</div>

{{-- 2. SCROLLABLE CONTENT --}}
<div class="relative z-10 min-h-screen py-10">
    <div class="container mx-auto px-4 max-w-5xl">

        {{-- ALERTS (Flash Messages) --}}
        @if(session('error'))
        <div class="bg-red-500/20 backdrop-blur-md border-l-4 border-red-500 text-red-200 p-4 mb-6 rounded shadow-lg flex items-center" role="alert">
            <i class="fas fa-exclamation-circle mr-3 text-xl text-red-500"></i>
            <div><p class="font-bold text-red-400">Action Required</p><p>{{ session('error') }}</p></div>
        </div>
        @endif

        @if(session('status'))
        <div class="bg-green-500/20 backdrop-blur-md border-l-4 border-green-500 text-green-200 p-4 mb-6 rounded shadow-lg flex items-center animate-pulse" role="alert">
            <i class="fas fa-check-circle mr-3 text-xl text-green-500"></i>
            <div><p class="font-bold text-green-400">Success</p><p>{{ session('status') }}</p></div>
        </div>
        @endif

        {{-- ==================== STATUS SECTION ==================== --}}
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
            @elseif($user->accountStat == 'approved' || $user->accountStat == 'active')
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


        {{-- ==================== PROFILE HEADER (Avatar) ==================== --}}
        <div class="text-center mb-8 relative">
            <h1 class="text-3xl font-black text-white drop-shadow-md">Complete Your Profile</h1>
            <p class="text-gray-400 mt-2">These details are required for insurance and refunds.</p>

            <div class="relative mt-6 inline-block">
                {{-- Avatar Display --}}
                <div class="w-32 h-32 rounded-full bg-white/10 backdrop-blur-md flex items-center justify-center border-4 border-white/20 shadow-2xl overflow-hidden">
                    @if($user->avatar)
                        {{-- FIXED: Correct Path --}}
                        <img src="{{ $user->avatar ? asset($user->avatar) : asset('login.png') }}" class="w-full h-full object-cover" id="avatar_preview">
                    @else
                        <i class="fas fa-user text-6xl text-gray-400" id="avatar_icon"></i>
                        <img src="" class="w-full h-full object-cover hidden" id="avatar_preview">
                    @endif
                </div>

                {{-- AVATAR FORM (Separate from Main Info) --}}
                <form action="{{ route('profile.avatar.update') }}" method="POST" enctype="multipart/form-data" id="avatar-form">
                    @csrf
                    {{-- Hidden File Input --}}
                    <input type="file" name="avatar" id="avatar_input" class="hidden" accept="image/*" onchange="document.getElementById('avatar-form').submit();">
                    
                    {{-- The Pencil Button --}}
                    <button type="button" onclick="document.getElementById('avatar_input').click()" class="absolute bottom-0 right-0 bg-[#ea580c] hover:bg-orange-600 text-white p-2 rounded-full w-8 h-8 flex items-center justify-center transition shadow-lg cursor-pointer z-20">
                        <i class="fas fa-pencil-alt text-xs"></i>
                    </button>
                </form>
            </div>
        </div>

        {{-- VALIDATION ERRORS --}}
        @if ($errors->any())
            <div class="bg-red-500/10 backdrop-blur-md border border-red-500/30 text-red-200 px-6 py-4 rounded-2xl relative mb-6 shadow-lg">
                <strong class="font-bold block mb-2">Please fix the following:</strong>
                <ul class="list-disc list-inside text-sm space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        {{-- ================= FORM 1: MAIN PROFILE INFO ================= --}}
        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="bg-black/25 backdrop-blur-xl border border-white/10 rounded-[2.5rem] shadow-2xl p-8 md:p-12 mb-8">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-6">
                {{-- LEFT: Personal Info --}}
                <div class="space-y-6">
                    <h3 class="text-lg font-bold text-orange-500 border-b border-white/10 pb-2">Personal Information</h3>
                    
                    {{-- FULL NAME: Uppercase --}}
                    <div>
                        <label class="block text-gray-400 mb-2 font-bold text-xs uppercase tracking-wider">Full Name</label>
                        <input type="text" name="name" value="{{ old('name', $user->fullName) }}" 
                               class="w-full bg-white/5 border border-white/10 rounded-xl p-3 text-white focus:outline-none focus:border-orange-500 focus:bg-white/10 transition placeholder-gray-500 uppercase"
                               oninput="this.value = this.value.toUpperCase()">
                    </div>

                    <div>
                        <label class="block text-gray-400 mb-2 font-bold text-xs uppercase tracking-wider">Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full bg-white/5 border border-white/10 rounded-xl p-3 text-white focus:outline-none focus:border-orange-500 focus:bg-white/10 transition placeholder-gray-500">
                    </div>

                    <div>
                        <label class="block text-gray-400 mb-2 font-bold text-xs uppercase tracking-wider">Phone No. <span class="text-red-500">*</span></label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phoneNo) }}" class="w-full bg-white/5 border border-white/10 rounded-xl p-3 text-white focus:outline-none focus:border-orange-500 focus:bg-white/10 transition placeholder-gray-500">
                    </div>

                    {{-- EMERGENCY CONTACT --}}
                    <div class="col-span-1 md:col-span-2 mt-2 mb-2">
                        <h4 class="text-sm font-bold text-gray-500 uppercase tracking-widest mb-4 border-b border-white/10 pb-2">Emergency Contact</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-gray-400 mb-2 font-bold text-xs uppercase tracking-wider">Name <span class="text-red-500">*</span></label>
                                <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name', $user->emergency_contact_name) }}" class="w-full bg-white/5 border border-white/10 rounded-xl p-3 text-white focus:outline-none focus:border-orange-500 focus:bg-white/10 transition placeholder-gray-500">
                            </div>
                            <div>
                                <label class="block text-gray-400 mb-2 font-bold text-xs uppercase tracking-wider">Phone No. <span class="text-red-500">*</span></label>
                                <input type="text" name="emergency_contact_no" value="{{ old('emergency_contact_no', $user->emergency_contact_no) }}" class="w-full bg-white/5 border border-white/10 rounded-xl p-3 text-white focus:outline-none focus:border-orange-500 focus:bg-white/10 transition placeholder-gray-500">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-400 mb-2 font-bold text-xs uppercase tracking-wider">Date Of Birth</label>
                        <input type="date" name="dob" value="{{ old('dob', optional($user->dob)->format('Y-m-d')) }}" class="w-full bg-white/5 border border-white/10 rounded-xl p-3 text-white focus:outline-none focus:border-orange-500 focus:bg-white/10 transition [color-scheme:dark]">
                    </div>

                    {{-- NATIONALITY: Country Dropdown --}}
                    <div>
                        <label class="block text-gray-400 mb-2 font-bold text-xs uppercase tracking-wider">Nationality</label>
                        <select name="nationality" class="w-full bg-white/5 border border-white/10 rounded-xl p-3 text-white focus:outline-none focus:border-orange-500 focus:bg-white/10 transition cursor-pointer">
                            <option value="" class="text-black">-- Select Country --</option>
                            @php
                                $countries = ["Malaysia","Indonesia","Singapore","Brunei","Thailand","Vietnam","Philippines","China","India","Pakistan","Bangladesh","Yemen","Saudi Arabia","United Kingdom","United States","Nigeria","Egypt","Japan","Korea, Republic of"]; 
                            @endphp
                            @foreach($countries as $country)
                                <option value="{{ $country }}" class="text-black" {{ old('nationality', $user->nationality) == $country ? 'selected' : '' }}>{{ $country }}</option>
                            @endforeach
                            <option value="Other" class="text-black" {{ old('nationality', $user->nationality) == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                </div>

                {{-- RIGHT: Documents & Address --}}
                <div class="space-y-6">
                    <h3 class="text-lg font-bold text-orange-500 border-b border-white/10 pb-2">Documents & Address</h3>

                    {{-- STAFF/STUDENT ID: Uppercase --}}
                    <div>
                        <label class="block text-gray-400 mb-2 font-bold text-xs uppercase tracking-wider">Student/Staff ID</label>
                        <div class="flex">
                            <input type="text" name="student_staff_id" value="{{ old('student_staff_id', $user->stustaffID) }}" 
                                   class="w-full bg-white/5 border border-white/10 rounded-l-xl p-3 text-white focus:outline-none focus:border-orange-500 focus:bg-white/10 transition placeholder-gray-500 uppercase"
                                   oninput="this.value = this.value.toUpperCase()">
                            <button type="button" onclick="document.getElementById('student_file').click()" class="bg-white/10 px-4 rounded-r-xl border border-l-0 border-white/10 text-gray-400 hover:bg-white/20 hover:text-white transition" id="btn_student">
                                <i class="fas fa-camera" id="icon_student"></i>
                            </button>
                            <input type="file" name="student_card_image" id="student_file" class="hidden" accept="image/*" onchange="fileSelected('student')">
                        </div>
                    </div>

                    {{-- IC / PASSPORT: Uppercase String --}}
                    <div>
                        <label class="block text-gray-400 mb-2 font-bold text-xs uppercase tracking-wider">IC / Passport No.</label>
                        <div class="flex">
                            <input type="text" name="ic_passport" value="{{ old('ic_passport', $user->ic_passport) }}" 
                                   class="w-full bg-white/5 border border-white/10 rounded-l-xl p-3 text-white focus:outline-none focus:border-orange-500 focus:bg-white/10 transition placeholder-gray-500 uppercase"
                                   oninput="this.value = this.value.toUpperCase()">
                            <button type="button" onclick="document.getElementById('ic_file').click()" class="bg-white/10 px-4 rounded-r-xl border border-l-0 border-white/10 text-gray-400 hover:bg-white/20 hover:text-white transition" id="btn_ic">
                                <i class="fas fa-camera" id="icon_ic"></i>
                            </button>
                            <input type="file" name="ic_passport_image" id="ic_file" class="hidden" accept="image/*" onchange="fileSelected('ic')">
                        </div>
                    </div>

                    {{-- DRIVING LICENSE: Expiry Date (Replaced Number) --}}
                    <div>
                        <label class="block text-gray-400 mb-2 font-bold text-xs uppercase tracking-wider">Driving License Expired Date <span class="text-red-500">*</span></label>
                        <div class="flex">
                            <input type="date" name="driving_license_expiry" value="{{ old('driving_license_expiry', $user->driving_license_expiry) }}" 
                                   class="w-full bg-white/5 border border-white/10 rounded-l-xl p-3 text-white focus:outline-none focus:border-orange-500 focus:bg-white/10 transition [color-scheme:dark]">
                             <button type="button" onclick="document.getElementById('license_file').click()" class="bg-white/10 px-4 rounded-r-xl border border-l-0 border-white/10 text-gray-400 hover:bg-white/20 hover:text-white transition" id="btn_license">
                                <i class="fas fa-camera" id="icon_license"></i>
                            </button>
                            <input type="file" name="driving_license_image" id="license_file" class="hidden" accept="image/*" onchange="fileSelected('license')">
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-400 mb-2 font-bold text-xs uppercase tracking-wider">Home Address</label>
                        <input type="text" name="home_address" value="{{ old('home_address', $user->homeAddress) }}" class="w-full bg-white/5 border border-white/10 rounded-xl p-3 text-white focus:outline-none focus:border-orange-500 focus:bg-white/10 transition placeholder-gray-500">
                    </div>

                    {{-- COLLEGE ADDRESS: UTM Colleges Dropdown --}}
                    <div>
                        <label class="block text-gray-400 mb-2 font-bold text-xs uppercase tracking-wider">College (UTM JB)</label>
                        <select name="college_address" class="w-full bg-white/5 border border-white/10 rounded-xl p-3 text-white focus:outline-none focus:border-orange-500 focus:bg-white/10 transition cursor-pointer">
                            <option value="" class="text-black">-- Select Residential College --</option>
                            @php
                                $colleges = [
                                    "Kolej Rahman Putra (KRP)",
                                    "Kolej Tun Fatimah (KTF)",
                                    "Kolej Tun Razak (KTR)",
                                    "Kolej Tun Hussein Onn (KTHO)",
                                    "Kolej Tun Dr. Ismail (KTDI)",
                                    "Kolej Tuanku Canselor (KTC)",
                                    "Kolej Perdana (KP)",
                                    "Kolej 9 & 10",
                                    "Kolej Datin Seri Endon (KDSE)",
                                    "Kolej Dato' Onn Jaafar (KDOJ)",
                                    "Off-Campus (Rental/Family)"
                                ];
                            @endphp
                            @foreach($colleges as $col)
                                <option value="{{ $col }}" class="text-black" {{ old('college_address', $user->collegeAddress) == $col ? 'selected' : '' }}>{{ $col }}</option>
                            @endforeach
                        </select>
                    </div>

                     {{-- FACULTY: UTM Faculties Dropdown --}}
                     <div>
                        <label class="block text-gray-400 mb-2 font-bold text-xs uppercase tracking-wider">Faculty</label>
                        <select name="faculty" class="w-full bg-white/5 border border-white/10 rounded-xl p-3 text-white focus:outline-none focus:border-orange-500 focus:bg-white/10 transition cursor-pointer">
                            <option value="" class="text-black">-- Select Faculty --</option>
                            @php
                                $faculties = [
                                    "Faculty of Civil Engineering (FKA)",
                                    "Faculty of Mechanical Engineering (FKM)",
                                    "Faculty of Electrical Engineering (FKE)",
                                    "Faculty of Chemical & Energy Engineering (FCEE)",
                                    "Faculty of Computing (FC)",
                                    "Faculty of Science (FS)",
                                    "Faculty of Built Environment & Surveying (FABU)",
                                    "Faculty of Social Sciences & Humanities (FSSH)",
                                    "Faculty of Management (FM)",
                                    "Razak Faculty of Technology and Informatics",
                                    "MJIIT (Malaysia-Japan International Institute of Technology)",
                                    "Azman Hashim International Business School (AHIBS)"
                                ];
                            @endphp
                            @foreach($faculties as $fac)
                                <option value="{{ $fac }}" class="text-black" {{ old('faculty', $user->faculty) == $fac ? 'selected' : '' }}>{{ $fac }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            
            {{-- BANK DETAILS: All Malaysia Banks --}}
            <div class="mt-10 pt-8 border-t border-white/10">
                 <h3 class="text-lg font-bold text-orange-500 mb-4">Refund Information (Bank Details)</h3>
                 
                 <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                     <div>
                        <label class="block text-gray-400 mb-2 font-bold text-xs uppercase tracking-wider">Bank Name <span class="text-red-500">*</span></label>
                        <select name="bank_name" class="w-full bg-white/5 border border-white/10 rounded-xl p-3 text-white focus:outline-none focus:border-orange-500 focus:bg-white/10 transition cursor-pointer">
                            <option value="" class="text-black">-- Select Bank --</option>
                            @php
                                $banks = [
                                    "Maybank", "CIMB Bank", "Public Bank", "RHB Bank", "Hong Leong Bank", 
                                    "AmBank", "UOB Malaysia", "Bank Rakyat", "OCBC Bank", "HSBC Bank", 
                                    "Bank Islam", "Affin Bank", "Alliance Bank", "Standard Chartered", 
                                    "MBSB Bank", "BSN (Bank Simpanan Nasional)", "Agrobank", "Bank Muamalat",
                                    "Kuwait Finance House", "Al Rajhi Bank",
                                    "GXBank (Digital)", "Aeon Bank (Digital)", "Boost Bank (Digital)"
                                ];
                            @endphp
                            @foreach($banks as $bank)
                                <option value="{{ $bank }}" class="text-black" {{ old('bank_name', $user->bankName) == $bank ? 'selected' : '' }}>{{ $bank }}</option>
                            @endforeach
                        </select>
                     </div>
                     <div>
                        <label class="block text-gray-400 mb-2 font-bold text-xs uppercase tracking-wider">Account Number <span class="text-red-500">*</span></label>
                        <input type="text" name="bank_account_no" value="{{ old('bank_account_no', $user->bankAccountNo) }}" placeholder="e.g. 162234..." class="w-full bg-white/5 border border-white/10 rounded-xl p-3 text-white focus:outline-none focus:border-orange-500 focus:bg-white/10 transition placeholder-gray-500">
                     </div>
                 </div>
            </div>

            <div class="flex justify-end items-center mt-8 pt-4 border-t border-white/10">
                <button type="submit" class="bg-[#ea580c] hover:bg-orange-600 text-white font-bold py-3 px-10 rounded-xl shadow-lg transition transform hover:scale-[1.02]">
                    Save Profile Information
                </button>
            </div>
        </form>

        {{-- ================= FORM 2: PASSWORD UPDATE ================= --}}
        <form action="{{ route('profile.password') }}" method="POST" class="bg-black/25 backdrop-blur-xl border border-white/10 rounded-[2.5rem] shadow-2xl p-8 md:p-12">
            @csrf
            @method('PUT')
            
            <h3 class="text-lg font-bold text-orange-500 mb-4">Edit Password</h3>
            <p class="text-sm text-gray-400 mb-4">Update your password securely here.</p>
             
             <div>
                 {{-- 1. Current Password --}}
                 <div class="mb-6 max-w-md"> 
                    <label class="block text-gray-400 mb-2 font-bold text-xs uppercase tracking-wider">Current Password</label>
                    <div>
                        <input type="password" name="current_password" id="current_password" placeholder="Enter current password to confirm" 
                            autocomplete="off" class="w-full bg-white/5 border border-white/10 rounded-xl p-3 text-white focus:outline-none focus:border-orange-500 focus:bg-white/10 transition placeholder-gray-500">
                    </div>
                 </div>

                 {{-- 2. GRID ROW FOR NEW PASS, CONFIRM PASS, AND BUTTON --}}
                 <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
                     
                     {{-- New Password --}}
                     <div>
                        <label class="block text-gray-400 mb-2 font-bold text-xs uppercase tracking-wider">New Password</label>
                        <div class="relative">
                            <input type="password" name="password" id="password" placeholder="Min. 8 characters" 
                                autocomplete="new-password" class="w-full bg-white/5 border border-white/10 rounded-xl p-3 text-white focus:outline-none focus:border-orange-500 focus:bg-white/10 transition placeholder-gray-500 pr-10">
                            
                            <button type="button" onclick="togglePassword('password', 'icon_pass')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-white">
                                <i class="fas fa-eye" id="icon_pass"></i>
                            </button>
                        </div>
                     </div>

                     {{-- Confirm Password --}}
                     <div>
                        <label class="block text-gray-400 mb-2 font-bold text-xs uppercase tracking-wider">Confirm Password</label>
                        <div class="relative">
                            <input type="password" name="password_confirmation" id="password_confirmation" placeholder="Retype new password" 
                                autocomplete="new-password" class="w-full bg-white/5 border border-white/10 rounded-xl p-3 text-white focus:outline-none focus:border-orange-500 focus:bg-white/10 transition placeholder-gray-500 pr-10">
                            
                            <button type="button" onclick="togglePassword('password_confirmation', 'icon_confirm')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-white">
                                <i class="fas fa-eye" id="icon_confirm"></i>
                            </button>
                        </div>
                     </div>

                     {{-- FORM 2 SUBMIT BUTTON --}}
                     <div>
                        <button type="submit" class="bg-gray-700 hover:bg-gray-600 border border-gray-600 text-white font-bold py-2 px-4 rounded-lg text-sm shadow-md transition transform hover:scale-[1.02] flex items-center justify-center">
                            <i class="fas fa-key mr-2"></i> Update Password
                        </button>
                     </div>

                 </div>
             </div>
        </form>

        {{-- BACK HOME LINK --}}
        <div class="flex justify-center mt-12">
            <a href="{{ route('home') }}" class="text-gray-400 underline font-medium hover:text-white transition">Back to Home</a>
        </div>

    </div>
</div>

<script>
    // 1. File Upload Preview Logic (For Documents)
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

    // 2. Password Toggle Logic
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
</script>

{{-- VALIDATION ERROR POPUP --}}
@if ($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const errors = @json($errors->messages());
        let errorMessages = '<ul style="text-align: left;">';
        
        for (const [field, messages] of Object.entries(errors)) {
            messages.forEach(message => {
                errorMessages += `<li style="margin: 8px 0;">${message}</li>`;
            });
        }
        errorMessages += '</ul>';
        
        Swal.fire({
            icon: 'error',
            title: '❌ Invalid Information',
            html: errorMessages,
            confirmButtonColor: '#ea580c',
            confirmButtonText: 'Try Again',
            allowOutsideClick: false,
            allowEscapeKey: false,
            background: '#353639ff',
            color: '#fff',
            customClass: {
                popup: 'border border-red-500/30 backdrop-blur-md',
                confirmButton: 'bg-orange-600 hover:bg-orange-700 text-white px-6 py-2 rounded-lg font-bold'
            }
        });
    });
</script>
@endif

@endsection