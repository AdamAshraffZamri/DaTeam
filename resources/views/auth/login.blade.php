@extends('layouts.app')

@section('content')
<div class="relative min-h-screen bg-slate-800 bg-cover bg-center flex items-center justify-center" style="background-image: url('https://images.unsplash.com/photo-1449965408869-eaa3f722e40d?q=80&w=1470&auto=format&fit=crop');">
    
    <div class="absolute inset-0 bg-black bg-opacity-60"></div>

    <div class="relative z-10 w-full max-w-md p-6">
        
        <div class="text-center mb-6">
            <div class="inline-block bg-white p-2 px-4 rounded">
                <span class="text-orange-600 font-bold text-2xl tracking-widest">HASTE</span>
            </div>
        </div>

        <div class="flex justify-end mb-6">
            <div class="inline-flex rounded-md shadow-sm" role="group">
                <button type="button" class="px-4 py-1 text-sm font-medium text-white bg-orange-600 rounded-l-lg border border-orange-600">
                    Customer
                </button>
                <button type="button" class="px-4 py-1 text-sm font-medium text-gray-900 bg-white rounded-r-lg border border-gray-200 hover:bg-gray-100">
                    Staff
                </button>
            </div>
        </div>

        <h2 class="text-2xl font-bold text-white text-center mb-6">Welcome back!</h2>

        <form action="{{ route('login') }}" method="POST" class="space-y-4">
            @csrf
            
            <div>
                <input type="email" name="email" placeholder="Email or Phone Number" 
                       class="w-full p-3 rounded form-input-transparent focus:outline-none focus:border-orange-500" required>
            </div>
            
            <div>
                <input type="password" name="password" placeholder="Enter password" 
                       class="w-full p-3 rounded form-input-transparent focus:outline-none focus:border-orange-500" required>
            </div>

            <div class="flex justify-between text-sm text-gray-300">
                <a href="{{ route('password.reset.custom') }}" class="hover:text-white">Forgot password?</a>
            </div>

            <button type="submit" class="w-full bg-orange-600 hover:bg-orange-700 text-white font-bold py-3 rounded transition duration-300">
                Log in
            </button>
        </form>

        <div class="mt-4">
            <a href="{{ route('register') }}" class="block w-full text-center bg-orange-900 bg-opacity-60 hover:bg-opacity-80 text-white font-bold py-3 rounded transition duration-300 border border-orange-800">
                Sign up
            </a>
        </div>
    </div>
</div>
@endsection