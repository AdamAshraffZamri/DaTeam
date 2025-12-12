@extends('layouts.app')

@section('content')
<div class="relative min-h-screen bg-slate-800 bg-cover bg-center flex items-center justify-center" style="background-image: url('https://images.unsplash.com/photo-1449965408869-eaa3f722e40d?q=80&w=1470&auto=format&fit=crop');">
    <div class="absolute inset-0 bg-black bg-opacity-60"></div>

    <div class="relative z-10 w-full max-w-md p-6">
        <div class="text-center mb-6">
             <div class="inline-block bg-white p-2 px-4 rounded">
                <span class="text-orange-600 font-bold text-2xl tracking-widest">HASTA</span>
            </div>
            <h2 class="text-white text-xl mt-4 font-bold">Reset Password</h2>
        </div>

        <form action="{{ route('password.reset.custom') }}" method="POST" class="space-y-4">
            @csrf
            
            <input type="email" name="email" placeholder="Email" class="w-full p-3 rounded form-input-transparent" required>

            <div class="relative">
                <select name="security_question" class="w-full p-3 rounded form-input-transparent appearance-none bg-black bg-opacity-30">
                    <option value="" class="text-gray-500">Select Security Question</option>
                    <option value="mother_name" class="text-black">What is your mother's name?</option>
                    <option value="first_pet" class="text-black">What was your first pet's name?</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-white">
                    <i class="fas fa-chevron-down text-xs"></i>
                </div>
            </div>

            <input type="text" name="security_answer" placeholder="Answer for Question 1" class="w-full p-3 rounded form-input-transparent" required>

            <input type="password" name="password" placeholder="New password" class="w-full p-3 rounded form-input-transparent" required>
            <input type="password" name="password_confirmation" placeholder="Confirm password" class="w-full p-3 rounded form-input-transparent" required>

            <button type="submit" class="w-full bg-orange-600 hover:bg-orange-700 text-white font-bold py-3 rounded">
                Reset
            </button>
        </form>
    </div>
</div>
@endsection