@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-10">
    <div class="container mx-auto px-4 max-w-5xl">

        <div class="text-center mb-8 relative">
            <h1 class="text-3xl font-bold text-gray-800">Profile</h1>
            
            <div class="relative mt-6 inline-block">
                <div class="w-32 h-32 rounded-full bg-gray-300 flex items-center justify-center border-4 border-white shadow-lg overflow-hidden">
                    <i class="fas fa-user text-6xl text-gray-500"></i>
                </div>
                <button class="absolute bottom-0 right-0 bg-black text-white p-2 rounded-full w-8 h-8 flex items-center justify-center hover:bg-gray-800 transition">
                    <i class="fas fa-pencil-alt text-xs"></i>
                </button>
            </div>

            <div class="absolute top-10 right-0 md:right-10">
                <div class="bg-gray-100 px-4 py-1 rounded-lg shadow-sm flex items-center space-x-2 border border-gray-200">
                    <span class="font-bold text-gray-600">Verified</span>
                    <i class="fas fa-check-circle text-green-500 text-xl"></i>
                </div>
            </div>
        </div>

        <form action="{{ route('profile.update') }}" method="POST" class="bg-white rounded-3xl shadow-xl p-8 md:p-12">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-6">

                <div class="space-y-6">
                    <div>
                        <label class="block text-gray-600 mb-2">Full Name</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:border-orange-500 transition">
                    </div>

                    <div>
                        <label class="block text-gray-600 mb-2">Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:border-orange-500 transition">
                    </div>

                    <div>
                        <label class="block text-gray-600 mb-2">Home Address</label>
                        <input type="text" name="home_address" value="{{ old('home_address', $user->home_address) }}" class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:border-orange-500 transition">
                    </div>

                    <div>
                        <label class="block text-gray-600 mb-2">Faculty</label>
                        <input type="text" value="Faculty of Computing" class="w-full border border-gray-300 rounded-lg p-3 text-gray-500 bg-gray-50" disabled>
                    </div>

                    <div>
                        <label class="block text-gray-600 mb-2">Password</label>
                        <div class="relative">
                            <input type="password" name="password" placeholder="********" class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:border-orange-500 transition">
                            <i class="fas fa-eye-slash absolute right-3 top-3.5 text-gray-400 cursor-pointer"></i>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div>
                        <label class="block text-gray-600 mb-2">Student/Staff ID</label>
                        <div class="flex">
                            <input type="text" name="student_staff_id" value="{{ old('student_staff_id', $user->student_staff_id) }}" class="w-full border border-gray-300 rounded-l-lg p-3 focus:outline-none focus:border-orange-500 transition">
                            <button type="button" class="bg-gray-200 px-4 rounded-r-lg border border-l-0 border-gray-300 text-gray-500 hover:bg-gray-300">
                                <i class="fas fa-camera"></i>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-600 mb-2">IC/Passport No.</label>
                        <div class="flex">
                            <input type="text" name="ic_passport" value="{{ old('ic_passport', $user->ic_passport) }}" class="w-full border border-gray-300 rounded-l-lg p-3 focus:outline-none focus:border-orange-500 transition">
                            <button type="button" class="bg-gray-200 px-4 rounded-r-lg border border-l-0 border-gray-300 text-gray-500 hover:bg-gray-300">
                                <i class="fas fa-camera"></i>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-600 mb-2">College Address</label>
                        <input type="text" name="college_address" value="{{ old('college_address', $user->college_address) }}" class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:border-orange-500 transition">
                    </div>

                    <div>
                        <label class="block text-gray-600 mb-2">Nationality</label>
                        <input type="text" name="nationality" value="{{ old('nationality', $user->nationality) }}" class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:border-orange-500 transition">
                    </div>

                    <div>
                        <label class="block text-gray-600 mb-2">Security Question</label>
                        <select class="w-full border border-gray-300 rounded-lg p-3 text-gray-500 bg-gray-50" disabled>
                            <option>What is your mother name?</option>
                        </select>
                    </div>
                </div>
                
                <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-6">
                     <div>
                        <label class="block text-gray-600 mb-2">Date Of Birth</label>
                        <input type="date" name="dob" value="{{ old('dob', $user->dob ? $user->dob->format('Y-m-d') : '') }}" class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:border-orange-500 transition">
                    </div>
                     <div>
                        <label class="block text-gray-600 mb-2">Phone No.</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:border-orange-500 transition">
                    </div>
                     <div>
                        <label class="block text-gray-600 mb-2">Driving License No.</label>
                        <div class="flex">
                            <input type="text" name="driving_license_no" value="{{ old('driving_license_no', $user->driving_license_no) }}" class="w-full border border-gray-300 rounded-l-lg p-3 focus:outline-none focus:border-orange-500 transition">
                             <button type="button" class="bg-gray-200 px-4 rounded-r-lg border border-l-0 border-gray-300 text-gray-500 hover:bg-gray-300">
                                <i class="fas fa-camera"></i>
                            </button>
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
@endsection