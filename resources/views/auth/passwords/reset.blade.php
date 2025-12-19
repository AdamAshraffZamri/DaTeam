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

        <form action="{{ route('password.update') }}" method="POST" class="space-y-4">
            @csrf
            
            <input type="hidden" name="token" value="{{ $token }}">

            <input type="email" name="email" value="{{ $email ?? old('email') }}" placeholder="Email" class="w-full p-3 rounded form-input-transparent text-black" required>
            @error('email') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror

            <input type="password" name="password" placeholder="New password" class="w-full p-3 rounded form-input-transparent text-black" required>
            @error('password') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
            
            <input type="password" name="password_confirmation" placeholder="Confirm password" class="w-full p-3 rounded form-input-transparent text-black" required>

            <button type="submit" class="w-full bg-orange-600 hover:bg-orange-700 text-white font-bold py-3 rounded transition duration-200">
                Update Password
            </button>
        </form>
    </div>
</div>
@endsection