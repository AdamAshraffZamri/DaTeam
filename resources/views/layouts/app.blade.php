<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HASTA - Car Rental</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .glass-panel {
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(5px);
            border-radius: 8px;
        }
        /* Custom scrollbar for a cleaner look */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1; 
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1; 
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8; 
        }
    </style>
</head>
<body class="bg-gray-50 flex flex-col min-h-screen font-sans antialiased">

    <nav class="bg-gradient-to-r from-[#ea580c] to-red-600 text-white p-4 shadow-lg relative z-30">
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
                
                @auth
                    <div class="flex items-center gap-3 bg-white/10 px-3 py-1.5 rounded-full border border-white/20 backdrop-blur-md">
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-2">
                            <i class="fas fa-user-circle text-2xl"></i>
                            <span class="text-xs font-bold hidden sm:block">{{ Auth::user()->name }}</span>
                        </a>
                        <div class="w-px h-4 bg-white/30"></div>
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-xs font-bold hover:text-orange-200 transition">Logout</button>
                        </form>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="bg-white text-orange-600 px-5 py-2 rounded-full font-bold text-sm hover:bg-orange-50 transition shadow-md">
                        Login
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    @if(!request()->routeIs('home'))
                
        {{-- Navigation Pill --}}
        <div class="container mx-auto flex justify-center py-4 relative z-20">
            <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-full p-1.5 flex items-center shadow-2xl">
                
                {{-- Book a Car --}}
                <a href="{{ route('book.create') }}" 
                class="px-8 py-2.5 rounded-full font-bold transition {{ request()->routeIs('book.create') ? 'bg-[#ea580c] text-white shadow-lg scale-105' : 'text-white/80 hover:bg-white/10' }}">
                    Book a Car
                </a>

                {{-- My Bookings --}}
                <a href="{{ route('book.index') }}" 
                class="px-8 py-2.5 rounded-full font-bold transition {{ request()->routeIs('book.index') ? 'bg-[#ea580c] text-white shadow-lg scale-105' : 'text-white/80 hover:bg-white/10' }}">
                    My Bookings
                </a>

                {{-- Loyalty --}}
                <a href="{{ route('loyalty.index') }}" 
                class="px-8 py-2.5 rounded-full font-bold transition {{ request()->routeIs('loyalty.index') ? 'bg-[#ea580c] text-white shadow-lg scale-105' : 'text-white/80 hover:bg-white/10' }}">
                    Loyalty
                </a>

                {{-- Finance --}}
                <a href="{{ route('finance.index') }}" 
                class="px-8 py-2.5 rounded-full font-bold transition {{ request()->routeIs('finance.index') ? 'bg-[#ea580c] text-white shadow-lg scale-105' : 'text-white/80 hover:bg-white/10' }}">
                    Finance
                </a>

            </div>
        </div>
    
    @endif

    <main class="flex-grow">
        @if(session('success'))
            <div class="container mx-auto px-4 mt-4">
                <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-r shadow-sm flex items-center" role="alert">
                    <i class="fas fa-check-circle text-xl mr-3"></i>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            </div>
        @endif
        
        @if($errors->any())
            <div class="container mx-auto px-4 mt-4">
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-r shadow-sm">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                             <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="bg-gray-900 text-white pt-12 pb-8 text-sm relative z-20">
        <div class="container mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12 border-b border-gray-800 pb-12">
                <div class="space-y-4">
                    <img src="{{ asset('hasta.jpeg') }}" alt="HASTA Logo" class="h-12 w-auto object-contain mb-4">
                    <p class="text-gray-400 leading-relaxed">Your premium car rental partner for university life. Affordable, reliable, and convenient.</p>
                    <div class="flex space-x-4 pt-2">
                        <a href="#" class="w-8 h-8 rounded-full bg-gray-800 flex items-center justify-center hover:bg-[#ea580c] transition"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="w-8 h-8 rounded-full bg-gray-800 flex items-center justify-center hover:bg-[#ea580c] transition"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="w-8 h-8 rounded-full bg-gray-800 flex items-center justify-center hover:bg-[#ea580c] transition"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
                <div>
                     <h4 class="font-bold text-lg mb-4 text-white">Company</h4>
                     <ul class="space-y-3 text-gray-400">
                         <li><a href="{{ route('pages.about') }}" class="hover:text-[#ea580c] transition">About Us</a></li>
                         <li><a href="#" class="hover:text-[#ea580c] transition">Careers</a></li>
                         <li><a href="#" class="hover:text-[#ea580c] transition">Privacy Policy</a></li>
                         <li><a href="#" class="hover:text-[#ea580c] transition">Terms of Service</a></li>
                     </ul>
                </div>
                <div>
                     <h4 class="font-bold text-lg mb-4 text-white">Support</h4>
                     <ul class="space-y-3 text-gray-400">
                         <li><a href="#" class="hover:text-[#ea580c] transition">My Account</a></li>
                         <li><a href="{{ route('pages.faq') }}" class="hover:text-[#ea580c] transition">Help Center</a></li>
                         <li><a href="#" class="hover:text-[#ea580c] transition">Report a Bug</a></li>
                     </ul>
                </div>
                <div>
                    <h4 class="font-bold text-lg mb-4 text-white">Contact Us</h4>
                    <ul class="space-y-4 text-gray-400">
                        <li class="flex items-start gap-3">
                            <i class="fas fa-map-marker-alt mt-1 text-[#ea580c]"></i>
                            <span>UTM Skudai,<br>Johor Bahru, Malaysia</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <i class="fas fa-phone text-[#ea580c]"></i>
                            <span>+60 11-1090 0700</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <i class="fas fa-envelope text-[#ea580c]"></i>
                            <span>support@hasta.com</span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="pt-8 text-center text-gray-500 flex flex-col md:flex-row justify-between items-center">
                <p>&copy; {{ date('Y') }} Hasta Travel & Tours Sdn. Bhd. All rights reserved.</p>
                <p class="mt-2 md:mt-0">SSM: 20240100987</p>
            </div>
        </div>
    </footer>
</body>
</html>