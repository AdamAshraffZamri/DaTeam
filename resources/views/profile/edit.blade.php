@extends('layouts.app')

@section('content')
{{-- 1. FIXED BACKGROUND --}}
<div class="fixed inset-0 z-0">
    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('hastabg.png') }}');"></div>
    <div class="absolute inset-0 bg-gradient-to-b from-black/80 via-black/50 to-black/90"></div>
</div>

{{-- 2. SCROLLABLE CONTENT --}}
<div class="relative z-10 min-h-screen py-10">
    <div class="container mx-auto px-4 max-w-5xl">

        {{-- ALERTS --}}
        @if(session('error'))
        <div class="bg-red-500/20 backdrop-blur-md border-l-4 border-red-500 text-red-200 p-4 mb-6 rounded shadow-lg flex items-center" role="alert">
            <i class="fas fa-exclamation-circle mr-3 text-xl text-red-500"></i>
            <div>
                <p class="font-bold text-red-400">Action Required</p>
                <p>{{ session('error') }}</p>
            </div>
        </div>
        @endif

        @if(session('warning'))
        <div class="bg-orange-500/20 backdrop-blur-md border-l-4 border-orange-500 text-orange-200 p-4 mb-6 rounded shadow-lg flex items-center" role="alert">
            <i class="fas fa-user-edit mr-3 text-xl text-orange-500"></i>
            <div>
                <p class="font-bold text-orange-400">Welcome to HASTA!</p>
                <p>{{ session('warning') }}</p>
            </div>
        </div>
        @endif

        @if(session('status'))
        <div class="bg-green-500/20 backdrop-blur-md border-l-4 border-green-500 text-green-200 p-4 mb-6 rounded shadow-lg flex items-center animate-pulse" role="alert">
            <i class="fas fa-check-circle mr-3 text-xl text-green-500"></i>
            <div>
                <p class="font-bold text-green-400">Success</p>
                <p>{{ session('status') }}</p>
            </div>
        </div>
        @endif

        {{-- PROFILE HEADER --}}
        <div class="text-center mb-8 relative">
            <h1 class="text-3xl font-black text-white drop-shadow-md">Complete Your Profile</h1>
            <p class="text-gray-400 mt-2">These details are required for insurance and refunds.</p>

            <div class="relative mt-6 inline-block">
                <div class="w-32 h-32 rounded-full bg-white/10 backdrop-blur-md flex items-center justify-center border-4 border-white/20 shadow-2xl overflow-hidden">
                    @if($user->avatar)
                        <img src="{{ asset('storage/' . $user->avatar) }}" class="w-full h-full object-cover" id="avatar_preview">
                    @else
                        <i class="fas fa-user text-6xl text-gray-400" id="avatar_icon"></i>
                        <img src="" class="w-full h-full object-cover hidden" id="avatar_preview">
                    @endif
                </div>
                <button type="button" onclick="document.getElementById('avatar_input').click()" class="absolute bottom-0 right-0 bg-[#ea580c] hover:bg-orange-600 text-white p-2 rounded-full w-8 h-8 flex items-center justify-center transition shadow-lg cursor-pointer">
                    <i class="fas fa-pencil-alt text-xs"></i>
                </button>
                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" id="avatar-form">
                     @csrf
                     @method('PUT')
                     <input type="file" name="avatar" id="avatar_input" class="hidden" accept="image/*" onchange="previewAvatar(this)">
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
        
        {{-- ================= FORM 1: PROFILE INFO ================= --}}
        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="bg-black/25 backdrop-blur-xl border border-white/10 rounded-[2.5rem] shadow-2xl p-8 md:p-12 mb-8">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-6">
                {{-- LEFT: Personal Info --}}
                <div class="space-y-6">
                    <h3 class="text-lg font-bold text-orange-500 border-b border-white/10 pb-2">Personal Information</h3>
                    
                    <div>
                        <label class="block text-gray-400 mb-2 font-bold text-xs uppercase tracking-wider">Full Name</label>
                        <input type="text" name="name" value="{{ old('name', $user->fullName) }}" class="w-full bg-white/5 border border-white/10 rounded-xl p-3 text-white focus:outline-none focus:border-orange-500 focus:bg-white/10 transition placeholder-gray-500">
                    </div>

                    <div>
                        <label class="block text-gray-400 mb-2 font-bold text-xs uppercase tracking-wider">Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full bg-white/5 border border-white/10 rounded-xl p-3 text-white focus:outline-none focus:border-orange-500 focus:bg-white/10 transition placeholder-gray-500">
                    </div>

                    <div>
                        <label class="block text-gray-400 mb-2 font-bold text-xs uppercase tracking-wider">Phone No. <span class="text-red-500">*</span></label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phoneNo) }}" class="w-full bg-white/5 border border-white/10 rounded-xl p-3 text-white focus:outline-none focus:border-orange-500 focus:bg-white/10 transition placeholder-gray-500">
                    </div>
                    
                    <div>
                        <label class="block text-gray-400 mb-2 font-bold text-xs uppercase tracking-wider">Emergency Contact No. <span class="text-red-500">*</span></label>
                        <input type="text" name="emergency_contact_no" value="{{ old('emergency_contact_no', $user->emergency_contact_no) }}" class="w-full bg-red-500/10 border border-red-500/30 rounded-xl p-3 text-white focus:outline-none focus:border-red-500 transition placeholder-red-300/50" placeholder="Family or close friend">
                    </div>

                    <div>
                        <label class="block text-gray-400 mb-2 font-bold text-xs uppercase tracking-wider">Date Of Birth</label>
                        <input type="date" name="dob" value="{{ old('dob', optional($user->dob)->format('Y-m-d')) }}" class="w-full bg-white/5 border border-white/10 rounded-xl p-3 text-white focus:outline-none focus:border-orange-500 focus:bg-white/10 transition [color-scheme:dark]">
                    </div>

                    <div>
                        <label class="block text-gray-400 mb-2 font-bold text-xs uppercase tracking-wider">Nationality</label>
                        <input type="text" name="nationality" value="{{ old('nationality', $user->nationality) }}" class="w-full bg-white/5 border border-white/10 rounded-xl p-3 text-white focus:outline-none focus:border-orange-500 focus:bg-white/10 transition placeholder-gray-500">
                    </div>
                </div>

                {{-- RIGHT: Documents & Address --}}
                <div class="space-y-6">
                    <h3 class="text-lg font-bold text-orange-500 border-b border-white/10 pb-2">Documents & Address</h3>

                    <div>
                        <label class="block text-gray-400 mb-2 font-bold text-xs uppercase tracking-wider">Student/Staff ID</label>
                        <div class="flex">
                            <input type="text" name="student_staff_id" value="{{ old('student_staff_id', $user->stustaffID) }}" class="w-full bg-white/5 border border-white/10 rounded-l-xl p-3 text-white focus:outline-none focus:border-orange-500 focus:bg-white/10 transition placeholder-gray-500">
                            <button type="button" onclick="document.getElementById('student_file').click()" class="bg-white/10 px-4 rounded-r-xl border border-l-0 border-white/10 text-gray-400 hover:bg-white/20 hover:text-white transition" id="btn_student">
                                <i class="fas fa-camera" id="icon_student"></i>
                            </button>
                            <input type="file" name="student_card_image" id="student_file" class="hidden" accept="image/*" onchange="fileSelected('student')">
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-400 mb-2 font-bold text-xs uppercase tracking-wider">IC/Passport No.</label>
                        <div class="flex">
                            <input type="text" name="ic_passport" value="{{ old('ic_passport', $user->ic_passport) }}" class="w-full bg-white/5 border border-white/10 rounded-l-xl p-3 text-white focus:outline-none focus:border-orange-500 focus:bg-white/10 transition placeholder-gray-500">
                            <button type="button" onclick="document.getElementById('ic_file').click()" class="bg-white/10 px-4 rounded-r-xl border border-l-0 border-white/10 text-gray-400 hover:bg-white/20 hover:text-white transition" id="btn_ic">
                                <i class="fas fa-camera" id="icon_ic"></i>
                            </button>
                            <input type="file" name="ic_passport_image" id="ic_file" class="hidden" accept="image/*" onchange="fileSelected('ic')">
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-400 mb-2 font-bold text-xs uppercase tracking-wider">Driving License No. <span class="text-red-500">*</span></label>
                        <div class="flex">
                            <input type="text" name="driving_license_no" value="{{ old('driving_license_no', $user->drivingNo) }}" class="w-full bg-white/5 border border-white/10 rounded-l-xl p-3 text-white focus:outline-none focus:border-orange-500 focus:bg-white/10 transition placeholder-gray-500">
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

                    <div>
                        <label class="block text-gray-400 mb-2 font-bold text-xs uppercase tracking-wider">College Address</label>
                        <input type="text" name="college_address" value="{{ old('college_address', $user->collegeAddress) }}" class="w-full bg-white/5 border border-white/10 rounded-xl p-3 text-white focus:outline-none focus:border-orange-500 focus:bg-white/10 transition placeholder-gray-500">
                    </div>

                     <div>
                        <label class="block text-gray-400 mb-2 font-bold text-xs uppercase tracking-wider">Faculty</label>
                        <input type="text" name="faculty" value="{{ old('faculty', $user->faculty) }}" placeholder="e.g. Faculty of Computing" class="w-full bg-white/5 border border-white/10 rounded-xl p-3 text-white focus:outline-none focus:border-orange-500 focus:bg-white/10 transition placeholder-gray-500">
                    </div>
                </div>
            </div>
            
            {{-- BANK DETAILS --}}
            <div class="mt-10 pt-8 border-t border-white/10">
                 <h3 class="text-lg font-bold text-orange-500 mb-4">Refund Information (Bank Details)</h3>
                 <p class="text-sm text-gray-400 mb-4">Required for refunding your deposit securely.</p>
                 
                 <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                     <div>
                        <label class="block text-gray-400 mb-2 font-bold text-xs uppercase tracking-wider">Bank Name <span class="text-red-500">*</span></label>
                        <select name="bank_name" class="w-full bg-white/5 border border-white/10 rounded-xl p-3 text-white focus:outline-none focus:border-orange-500 focus:bg-white/10 transition cursor-pointer">
                            <option value="" class="text-black">-- Select Bank --</option>
                            <option value="Maybank" class="text-black" {{ old('bank_name', $user->bankName) == 'Maybank' ? 'selected' : '' }}>Maybank</option>
                            <option value="CIMB" class="text-black" {{ old('bank_name', $user->bankName) == 'CIMB' ? 'selected' : '' }}>CIMB Bank</option>
                            <option value="Public Bank" class="text-black" {{ old('bank_name', $user->bankName) == 'Public Bank' ? 'selected' : '' }}>Public Bank</option>
                            <option value="RHB" class="text-black" {{ old('bank_name', $user->bankName) == 'RHB' ? 'selected' : '' }}>RHB Bank</option>
                            <option value="Hong Leong" class="text-black" {{ old('bank_name', $user->bankName) == 'Hong Leong' ? 'selected' : '' }}>Hong Leong Bank</option>
                            <option value="AmBank" class="text-black" {{ old('bank_name', $user->bankName) == 'AmBank' ? 'selected' : '' }}>AmBank</option>
                            <option value="Bank Islam" class="text-black" {{ old('bank_name', $user->bankName) == 'Bank Islam' ? 'selected' : '' }}>Bank Islam</option>
                        </select>
                     </div>
                     <div>
                        <label class="block text-gray-400 mb-2 font-bold text-xs uppercase tracking-wider">Account Number <span class="text-red-500">*</span></label>
                        <input type="text" name="bank_account_no" value="{{ old('bank_account_no', $user->bankAccountNo) }}" placeholder="e.g. 162234..." class="w-full bg-white/5 border border-white/10 rounded-xl p-3 text-white focus:outline-none focus:border-orange-500 focus:bg-white/10 transition placeholder-gray-500">
                     </div>
                 </div>
            </div>

            {{-- FORM 1 SUBMIT BUTTON --}}
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
                        <input type="text" name="current_password" id="current_password" placeholder="Enter current password to confirm" 
                            class="w-full bg-white/5 border border-white/10 rounded-xl p-3 text-white focus:outline-none focus:border-orange-500 focus:bg-white/10 transition placeholder-gray-500">
                    </div>
                 </div>

                 {{-- 2. GRID ROW FOR NEW PASS, CONFIRM PASS, AND BUTTON --}}
                 <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
                     
                     {{-- New Password --}}
                     <div>
                        <label class="block text-gray-400 mb-2 font-bold text-xs uppercase tracking-wider">New Password</label>
                        <div class="relative">
                            <input type="password" name="password" id="password" placeholder="Min. 8 characters" 
                                class="w-full bg-white/5 border border-white/10 rounded-xl p-3 text-white focus:outline-none focus:border-orange-500 focus:bg-white/10 transition placeholder-gray-500 pr-10">
                            
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
                                class="w-full bg-white/5 border border-white/10 rounded-xl p-3 text-white focus:outline-none focus:border-orange-500 focus:bg-white/10 transition placeholder-gray-500 pr-10">
                            
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
    // 1. File Upload Preview Logic
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

    // 2. Avatar Preview Logic
    function previewAvatar(input) {
        if (input.files && input.files[0]) {
            // Check if we need to submit the specific avatar form if it's detached
            // But since the input is now part of Form 1, standard submission works.
            // Just preview it here:
            var reader = new FileReader();
            reader.onload = function(e) {
                const icon = document.getElementById('avatar_icon');
                if(icon) icon.classList.add('hidden');
                
                const img = document.getElementById('avatar_preview');
                img.src = e.target.result;
                img.classList.remove('hidden');
            }
            reader.readAsDataURL(input.files[0]);
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
</script>
@endsection