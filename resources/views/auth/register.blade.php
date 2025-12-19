@extends('layouts.app')

@section('content')
<div class="relative min-h-screen bg-slate-800 bg-cover bg-center flex items-center justify-center" style="background-image: url('https://images.unsplash.com/photo-1449965408869-eaa3f722e40d?q=80&w=1470&auto=format&fit=crop');">
    <div class="absolute inset-0 bg-black bg-opacity-60"></div>

    <div class="relative z-10 w-full max-w-md p-6">
        <div class="text-center mb-6">
            <div class="inline-block bg-white p-2 px-4 rounded">
                <span class="text-orange-600 font-bold text-2xl tracking-widest">HASTA</span>
            </div>
            <h2 class="text-white text-xl mt-4 font-bold">Create Account</h2>
        </div>

        

        <form action="{{ route('register') }}" method="POST" class="space-y-3">
            @csrf
            <input type="hidden" name="role" value="customer">

            <input type="text" name="name" placeholder="Full Name" class="w-full p-3 rounded form-input-transparent text-black" required>

            <input type="email" name="email" placeholder="Email" class="w-full p-3 rounded form-input-transparent text-black" required>
            
            <input type="text" name="phone" placeholder="Phone Number" class="w-full p-3 rounded form-input-transparent text-black">
            
            <input type="password" name="password" placeholder="Enter password" class="w-full p-3 rounded form-input-transparent text-black" required>
            
            <input type="password" name="password_confirmation" placeholder="Confirm password" class="w-full p-3 rounded form-input-transparent text-black" required>
            
            <button type="submit" class="w-full bg-orange-600 hover:bg-orange-700 text-white font-bold py-3 rounded mt-4 transition duration-200">
                Sign up
            </button>
            
            <a href="{{ route('login') }}" class="block w-full text-center bg-orange-900 bg-opacity-60 text-white font-bold py-3 rounded mt-2 border border-orange-800 hover:bg-opacity-80 transition duration-200">
                Log in
            </a>
        </form>
    </div>
</div>
@endsection