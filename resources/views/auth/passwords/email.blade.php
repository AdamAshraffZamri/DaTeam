@extends('layouts.app')

@section('content')
<div class="relative min-h-screen bg-slate-800 bg-cover bg-center flex items-center justify-center" style="background-image: url('https://images.unsplash.com/photo-1449965408869-eaa3f722e40d?q=80&w=1470&auto=format&fit=crop');">
    <div class="absolute inset-0 bg-black bg-opacity-60"></div>

    <div class="relative z-10 w-full max-w-md p-6">
        <div class="text-center mb-6">
             <div class="inline-block bg-white p-2 px-4 rounded">
                <span class="text-orange-600 font-bold text-2xl tracking-widest">HASTA</span>
            </div>
            <h2 class="text-white text-xl mt-4 font-bold">Forgot Password?</h2>
            <p class="text-gray-300 text-sm mt-2">Enter your email and we'll send you a reset link.</p>
        </div>

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        <form action="{{ route('password.email') }}" method="POST" class="space-y-4">
            @csrf
            
            <input type="email" name="email" placeholder="Enter your email address" class="w-full p-3 rounded form-input-transparent text-black" required>
            @error('email')
                <span class="text-red-400 text-sm">{{ $message }}</span>
            @enderror

            <button type="submit" class="w-full bg-orange-600 hover:bg-orange-700 text-white font-bold py-3 rounded transition duration-200">
                Send Reset Link
            </button>
            
            <div class="text-center mt-4">
                <a href="{{ route('login') }}" class="text-gray-300 hover:text-white text-sm">Back to Login</a>
            </div>
        </form>
    </div>
</div>
@endsection