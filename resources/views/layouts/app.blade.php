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
        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        
        /* Smooth transitions for navigation */
        .nav-link-active {
            background-color: #ea580c;
            color: white;
            box-shadow: 0 10px 15px -3px rgba(234, 88, 12, 0.3);
            transform: scale(1.05);
        }
    </style>
</head>
<body class="bg-gray-50 flex flex-col min-h-screen font-sans antialiased overflow-x-hidden">

    <nav class="bg-gradient-to-r from-[#ea580c] to-red-600 text-white p-4 shadow-lg relative z-50">
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
                    <a href="{{ route('login') }}" class="bg-white text-orange-600 px-5 py-2 rounded-full font-bold text-sm hover:bg-orange-50 transition shadow-md">Login</a>
                @endauth
            </div>
        </div>
    </nav>

    {{-- Updated Condition: Hide this Layout Pill Bar on Home AND About pages (since they have their own) --}}
    @if(!request()->routeIs('home') && !request()->routeIs('pages.about') && !request()->routeIs('pages.contact'))
        <div class="w-full flex justify-center py-6 relative z-40">
            <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-full p-1.5 flex items-center shadow-2xl">
                <a href="{{ route('book.create') }}" 
                   class="px-8 py-2.5 rounded-full font-bold transition {{ request()->routeIs('book.create') ? 'nav-link-active' : 'text-white/80 hover:bg-white/10' }}">
                    Book a Car
                </a>
                <a href="{{ route('book.index') }}" 
                   class="px-8 py-2.5 rounded-full font-bold transition {{ request()->routeIs('book.index') ? 'nav-link-active' : 'text-white/80 hover:bg-white/10' }}">
                    My Bookings
                </a>
                <a href="{{ route('loyalty.index') }}" 
                   class="px-8 py-2.5 rounded-full font-bold transition {{ request()->routeIs('loyalty.index') ? 'nav-link-active' : 'text-white/80 hover:bg-white/10' }}">
                    Loyalty
                </a>
                <a href="{{ route('finance.index') }}" 
                   class="px-8 py-2.5 rounded-full font-bold transition {{ request()->routeIs('finance.index') ? 'nav-link-active' : 'text-white/80 hover:bg-white/10' }}">
                    Payments
                </a>
            </div>
        </div>
    @endif
        <button onclick="document.getElementById('help-modal').classList.remove('hidden')" class="fixed bottom-6 right-6 bg-blue-600 text-white p-4 rounded-full shadow-2xl hover:bg-blue-700 transition z-50">
    <i class="fas fa-question text-xl"></i>
</button>

<div id="help-modal" class="fixed inset-0 z-[9999] hidden bg-black/90 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-gray-900 border border-white/10 rounded-2xl max-w-lg w-full p-6 relative">
        <button onclick="document.getElementById('help-modal').classList.add('hidden')" class="absolute top-4 right-4 text-gray-400 hover:text-white">
            <i class="fas fa-times"></i>
        </button>
        
        <h3 class="text-xl font-bold text-white mb-4">How to Rent</h3>
        <ul class="space-y-4 text-sm text-gray-300">
            <li class="flex gap-3">
                <span class="bg-orange-500 text-white w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">1</span>
                <span>Complete your profile with a valid driving license.</span>
            </li>
            <li class="flex gap-3">
                <span class="bg-orange-500 text-white w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">2</span>
                <span>Select a vehicle and dates. (Min 24h gap between rentals).</span>
            </li>
            <li class="flex gap-3">
                <span class="bg-orange-500 text-white w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">3</span>
                <span>Make payment (Deposit or Full) within 30 minutes.</span>
            </li>
            <li class="flex gap-3">
                <span class="bg-orange-500 text-white w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">4</span>
                <span>Wait for Admin Approval ("Approved" status).</span>
            </li>
        </ul>
    </div>
</div>
    <main class="flex-grow relative">
        <div class="container mx-auto px-4">
            @if(session('success'))
                <div class="mt-4 bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm flex items-center" role="alert">
                    <i class="fas fa-check-circle text-xl mr-3"></i>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            @endif
            
            @if($errors->any() && !request()->routeIs('profile.edit'))
                <div class="mt-4 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm">
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                             <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        @yield('content')
    </main>

    <footer class="bg-gray-900 text-white pt-12 pb-8 text-sm relative z-30">
        <div class="container mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12 border-b border-gray-800 pb-12">
                <div class="space-y-4">
                    <img src="{{ asset('hasta.jpeg') }}" alt="HASTA Logo" class="h-12 w-auto object-contain mb-4">
                    <p class="text-gray-400 leading-relaxed">Your premium car rental partner for university life.</p>
                    <div class="flex space-x-4 pt-2">
                        <a href="#" class="w-8 h-8 rounded-full bg-gray-800 flex items-center justify-center hover:bg-[#ea580c] transition"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="w-8 h-8 rounded-full bg-gray-800 flex items-center justify-center hover:bg-[#ea580c] transition"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="w-8 h-8 rounded-full bg-gray-800 flex items-center justify-center hover:bg-[#ea580c] transition"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
                <div>
                     <h4 class="font-bold text-lg mb-4">Company</h4>
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