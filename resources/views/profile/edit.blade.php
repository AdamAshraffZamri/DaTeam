@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6 max-w-4xl">
    <h1 class="text-3xl font-bold text-center mb-8">Profile</h1>

    <div class="flex flex-col items-center mb-10">
        <div class="relative">
            <div class="w-32 h-32 rounded-full bg-gray-200 flex items-center justify-center overflow-hidden border-4 border-white shadow-lg">
                <i class="fas fa-user text-6xl text-gray-400"></i>
            </div>
            <button class="absolute bottom-0 right-0 bg-black text-white rounded-full p-2 w-8 h-8 flex items-center justify-center shadow">
                <i class="fas fa-pencil-alt text-xs"></i>
            </button>
        </div>
    </div>

    <form action="{{ route('profile.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
            
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Full Name</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full border border-gray-300 p-2 rounded focus:border-orange-500 outline-none">
            </div>

            <div>
                 <label class="block text-gray-700 text-sm font-bold mb-2">Student/Staff ID</label>
                 <div class="flex">
                    <input type="text" name="student_staff_id" value="{{ old('student_staff_id', $user->student_staff_id) }}" class="w-full border border-gray-300 p-2 rounded-l focus:border-orange-500 outline-none">
                    <button type="button" class="bg-gray-200 px-3 border border-l-0 border-gray-300 rounded-r text-gray-500 hover:bg-gray-300"><i class="fas fa-camera"></i></button>
                 </div>
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full border border-gray-300 p-2 rounded focus:border-orange-500 outline-none">
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">IC/Passport No.</label>
                <div class="flex">
                    <input type="text" name="ic_passport" value="{{ old('ic_passport', $user->ic_passport) }}" class="w-full border border-gray-300 p-2 rounded-l focus:border-orange-500 outline-none">
                    <button type="button" class="bg-gray-200 px-3 border border-l-0 border-gray-300 rounded-r text-gray-500 hover:bg-gray-300"><i class="fas fa-camera"></i></button>
                 </div>
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Home Address</label>
                <input type="text" name="home_address" value="{{ old('home_address', $user->home_address) }}" class="w-full border border-gray-300 p-2 rounded focus:border-orange-500 outline-none">
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">College Address</label>
                <input type="text" name="college_address" value="{{ old('college_address', $user->college_address) }}" class="w-full border border-gray-300 p-2 rounded focus:border-orange-500 outline-none">
            </div>
            
            <div>
                 <label class="block text-gray-700 text-sm font-bold mb-2">Faculty</label>
                 <input type="text" value="Faculty of Computing" class="w-full border border-gray-300 p-2 rounded focus:border-orange-500 outline-none text-gray-500" disabled>
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Nationality</label>
                <input type="text" name="nationality" value="{{ old('nationality', $user->nationality) }}" class="w-full border border-gray-300 p-2 rounded focus:border-orange-500 outline-none">
            </div>

            <div>
                 <label class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                 <div class="relative">
                     <input type="password" value="********" class="w-full border border-gray-300 p-2 rounded focus:border-orange-500 outline-none" disabled>
                     <i class="fas fa-eye-slash absolute right-3 top-3 text-gray-400"></i>
                 </div>
            </div>

            <div>
                 <label class="block text-gray-700 text-sm font-bold mb-2">Security Question</label>
                 <select class="w-full border border-gray-300 p-2 rounded bg-white" disabled>
                     <option>What is your mother's name?</option>
                 </select>
            </div>

        </div>

        <div class="flex justify-end items-center mt-10 space-x-4">
            <a href="{{ route('home') }}" class="text-gray-500 underline text-sm hover:text-gray-800">Skip for now</a>
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-8 rounded shadow">Save</button>
            <button type="reset" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-8 rounded shadow">Reset</button>
        </div>
    </form>
</div>
@endsection