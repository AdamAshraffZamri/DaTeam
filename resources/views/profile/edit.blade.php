@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-10">
    <div class="container mx-auto px-4 max-w-5xl">

        <div class="text-center mb-8 relative">
            <h1 class="text-3xl font-bold text-gray-800">Profile</h1>
            
            {{-- PROFILE PICTURE --}}
            <div class="relative mt-6 inline-block">
                <div class="w-32 h-32 rounded-full bg-gray-300 flex items-center justify-center border-4 border-white shadow-lg overflow-hidden">
                    @if($user->avatar)
                        <img src="{{ asset('storage/' . $user->avatar) }}" class="w-full h-full object-cover" id="avatar_preview">
                    @else
                        <i class="fas fa-user text-6xl text-gray-500" id="avatar_icon"></i>
                        <img src="" class="w-full h-full object-cover hidden" id="avatar_preview">
                    @endif
                </div>
                
                <button type="button" onclick="document.getElementById('avatar_input').click()" class="absolute bottom-0 right-0 bg-black text-white p-2 rounded-full w-8 h-8 flex items-center justify-center hover:bg-gray-800 transition cursor-pointer">
                    <i class="fas fa-pencil-alt text-xs"></i>
                </button>
                
                <input type="file" name="avatar" id="avatar_input" class="hidden" accept="image/*" onchange="previewAvatar(this)">
            </div>

            <div class="absolute top-10 right-0 md:right-10">
                <div class="bg-gray-100 px-4 py-1 rounded-lg shadow-sm flex items-center space-x-2 border border-gray-200">
                    <span class="font-bold text-gray-600">Verified</span>
                    <i class="fas fa-check-circle text-green-500 text-xl"></i>
                </div>
            </div>
        </div>

        @if (session('status'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline">{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>• {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-3xl shadow-xl p-8 md:p-12">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-6">

                {{-- LEFT COLUMN --}}
                <div class="space-y-6">
                    <div>
                        <label class="block text-gray-600 mb-2">Full Name</label>
                        {{-- FIX: $user->fullName --}}
                        <input type="text" name="name" value="{{ old('name', $user->fullName) }}" class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:border-orange-500 transition">
                    </div>

                    <div>
                        <label class="block text-gray-600 mb-2">Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:border-orange-500 transition">
                    </div>

                    <div>
                        <label class="block text-gray-600 mb-2">Home Address</label>
                        {{-- FIX: $user->homeAddress --}}
                        <input type="text" name="home_address" value="{{ old('home_address', $user->homeAddress) }}" class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:border-orange-500 transition">
                    </div>

                    <div>
                        <label class="block text-gray-600 mb-2">Faculty</label>
                        <input type="text" name="faculty" value="{{ old('faculty', $user->faculty) }}" placeholder="e.g. Faculty of Computing" class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:border-orange-500 transition">
                    </div>

                    <div>
                        <label class="block text-gray-600 mb-2">Password</label>
                        <div class="relative">
                            <input type="password" name="password" placeholder="********" class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:border-orange-500 transition">
                            <i class="fas fa-eye-slash absolute right-3 top-3.5 text-gray-400 cursor-pointer"></i>
                        </div>
                    </div>
                </div>

                {{-- RIGHT COLUMN --}}
                <div class="space-y-6">
                    {{-- Student ID --}}
                    <div>
                        <label class="block text-gray-600 mb-2">Student/Staff ID</label>
                        <div class="flex">
                            {{-- FIX: $user->stustaffID --}}
                            <input type="text" name="student_staff_id" value="{{ old('student_staff_id', $user->stustaffID) }}" class="w-full border border-gray-300 rounded-l-lg p-3 focus:outline-none focus:border-orange-500 transition">
                            <button type="button" onclick="document.getElementById('student_file').click()" class="bg-gray-200 px-4 rounded-r-lg border border-l-0 border-gray-300 text-gray-500 hover:bg-gray-300 transition" id="btn_student">
                                <i class="fas fa-camera" id="icon_student"></i>
                            </button>
                            <input type="file" name="student_card_image" id="student_file" class="hidden" accept="image/*" onchange="fileSelected('student')">
                        </div>
                    </div>

                    {{-- IC Upload --}}
                    <div>
                        <label class="block text-gray-600 mb-2">IC/Passport No.</label>
                        <div class="flex">
                            <input type="text" name="ic_passport" value="{{ old('ic_passport', $user->ic_passport) }}" class="w-full border border-gray-300 rounded-l-lg p-3 focus:outline-none focus:border-orange-500 transition">
                            <button type="button" onclick="document.getElementById('ic_file').click()" class="bg-gray-200 px-4 rounded-r-lg border border-l-0 border-gray-300 text-gray-500 hover:bg-gray-300 transition" id="btn_ic">
                                <i class="fas fa-camera" id="icon_ic"></i>
                            </button>
                            <input type="file" name="ic_passport_image" id="ic_file" class="hidden" accept="image/*" onchange="fileSelected('ic')">
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-600 mb-2">College Address</label>
                        {{-- FIX: $user->collegeAddress --}}
                        <input type="text" name="college_address" value="{{ old('college_address', $user->collegeAddress) }}" class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:border-orange-500 transition">
                    </div>

                    <div>
                        <label class="block text-gray-600 mb-2">Nationality</label>
                        <input type="text" name="nationality" value="{{ old('nationality', $user->nationality) }}" class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:border-orange-500 transition">
                    </div>
                </div>
                
                {{-- BOTTOM WIDE SECTION --}}
                <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-6">
                     <div>
                        <label class="block text-gray-600 mb-2">Date Of Birth</label>
                        <input type="date" name="dob" value="{{ old('dob', optional($user->dob)->format('Y-m-d')) }}" class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:border-orange-500 transition">
                    </div>
                     <div>
                        <label class="block text-gray-600 mb-2">Phone No.</label>
                        {{-- FIX: $user->phoneNo --}}
                        <input type="text" name="phone" value="{{ old('phone', $user->phoneNo) }}" class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:border-orange-500 transition">
                    </div>
                     
                    {{-- Driving License --}}
                    <div>
                        <label class="block text-gray-600 mb-2">Driving License No.</label>
                        <div class="flex">
                            {{-- FIX: $user->drivingNo --}}
                            <input type="text" name="driving_license_no" value="{{ old('driving_license_no', $user->drivingNo) }}" class="w-full border border-gray-300 rounded-l-lg p-3 focus:outline-none focus:border-orange-500 transition">
                             <button type="button" onclick="document.getElementById('license_file').click()" class="bg-gray-200 px-4 rounded-r-lg border border-l-0 border-gray-300 text-gray-500 hover:bg-gray-300 transition" id="btn_license">
                                <i class="fas fa-camera" id="icon_license"></i>
                            </button>
                            <input type="file" name="driving_license_image" id="license_file" class="hidden" accept="image/*" onchange="fileSelected('license')">
                        </div>
                    </div>
                     <div>
                        <label class="block text-gray-600 mb-2">Emergency Contact No.</label>
                        <input type="text" name="emergency_contact_no" value="{{ old('emergency_contact_no', $user->emergency_contact_no) }}" class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:border-orange-500 transition">
                    </div>
                </div>

            </div>

            <div class="flex justify-end items-center mt-12 space-x-4">
                <a href="{{ route('home') }}" class="text-gray-500 underline font-medium hover:text-gray-800">Skip for now</a>
                <button type="submit" class="bg-blue-500 text-white font-bold py-3 px-10 rounded-lg shadow hover:bg-blue-600 transition transform hover:scale-105">Save</button>
                <button type="reset" class="bg-red-500 text-white font-bold py-3 px-10 rounded-lg shadow hover:bg-red-600 transition transform hover:scale-105">Reset</button>
            </div>
        </form>

    </div>
</div>

<script>
    // ... (Keep existing script)
    function fileSelected(type) {
        const input = document.getElementById(type + '_file');
        const icon = document.getElementById('icon_' + type);
        const btn = document.getElementById('btn_' + type);

        if (input.files && input.files[0]) {
            icon.classList.remove('fa-camera');
            icon.classList.add('fa-check');
            icon.classList.remove('text-gray-500');
            icon.classList.add('text-green-600');
            btn.classList.remove('bg-gray-200');
            btn.classList.add('bg-green-100');
        }
    }

    function previewAvatar(input) {
        if (input.files && input.files[0]) {
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
</script>
@endsection