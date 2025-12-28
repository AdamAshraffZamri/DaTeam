<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - HASTA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-900">

    <nav class="bg-gradient-to-r from-[#ea580c] to-red-600 text-white p-4 shadow-lg fixed top-0 w-full z-50">
        <div class="container mx-auto flex justify-between items-center">
            <a href="{{ route('home') }}" class="flex items-center">
                <img src="{{ asset('hasta.jpeg') }}" alt="HASTA Logo" class="h-10 w-auto object-contain drop-shadow-sm hover:scale-105 transition transform">
            </a>

            <div class="hidden md:flex space-x-8 text-sm font-bold tracking-wide">
                <a href="{{ route('home') }}" class="hover:text-orange-100 transition border-b-2 border-transparent hover:border-white pb-1">Home</a>
                <a href="{{ route('pages.about') }}" class="hover:text-orange-100 transition border-b-2 border-transparent hover:border-white pb-1">About Us</a>
                <a href="{{ route('pages.faq') }}" class="hover:text-orange-100 transition border-b-2 border-transparent hover:border-white pb-1">FAQ</a>
                <a href="{{ route('pages.contact') }}" class="hover:text-orange-100 transition border-b-2 border-transparent hover:border-white pb-1">Contact Us</a>
            </div>

            <div class="flex items-center space-x-5">
                <div class="relative cursor-pointer hover:scale-110 transition">
                    <i class="fas fa-bell text-xl"></i>
                    <span class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full border border-white">2</span>
                </div>
                <a href="{{ route('login') }}" class="bg-white text-orange-600 px-5 py-2 rounded-full font-bold text-sm hover:bg-orange-50 transition shadow-md">
                    Login
                </a>
            </div>
        </div>
    </nav>

    <div class="relative min-h-screen flex items-center justify-center bg-cover bg-center" 
         style="background-image: url('https://images.unsplash.com/photo-1449965408869-eaa3f722e40d?q=80&w=1470&auto=format&fit=crop');">
        
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>

        <div class="relative z-10 w-full max-w-md p-6 mt-16">
            
            <img src="{{ asset('hasta.jpeg') }}" alt="HASTA Logo" class="w-32 mx-auto rounded shadow-lg mb-6 block">

            <div class="text-center mb-6">
                <h2 class="text-white text-2xl font-bold">Forgot Password?</h2>
                <p class="text-gray-300 text-sm mt-2">Enter your email and we'll send you a reset link.</p>
            </div>

            @if (session('success'))
                <div class="bg-green-500/20 border border-green-500 text-green-100 px-4 py-3 rounded relative mb-6 text-sm">
                    <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('password.email') }}" method="POST" class="space-y-4">
                @csrf
                
                <div class="relative">
                    <input type="email" name="email" required placeholder="Enter your email address" 
                        class="w-full bg-transparent border border-gray-400 rounded-lg px-4 py-3 text-white placeholder-gray-300 focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 transition">
                </div>
                @error('email')
                    <span class="text-red-400 text-sm block mt-1">{{ $message }}</span>
                @enderror

                <button type="submit" class="w-full mt-4 bg-orange-600 hover:bg-orange-700 text-white font-bold py-3 rounded-lg shadow-lg transition transform hover:scale-[1.02]">
                    Send Reset Link
                </button>
                
                <div class="text-center mt-6">
                    <a href="{{ route('login') }}" class="text-gray-300 hover:text-white text-sm hover:underline">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Login
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>