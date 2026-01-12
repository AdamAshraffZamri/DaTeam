<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HASTA - Car Rental</title>
    <link rel="icon" href="{{ asset('dateamlogo.png') }}" type="image/png">
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
        
        /* Hide scrollbar for horizontal scrolling areas on mobile */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }

        /* Smooth transitions for navigation */
        .nav-link-active {
            background-color: #ea580c;
            color: white;
            box-shadow: 0 10px 15px -3px rgba(234, 88, 12, 0.3);
            transform: scale(1.05);
        }

        /* Reminder Banner Animations */
        @keyframes shine {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        @keyframes bounce-subtle {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }
        @keyframes pulse-slow {
            0%, 100% { opacity: 0.6; }
            50% { opacity: 1; }
        }
        @keyframes banner-wiggle {
            0%, 100% { transform: rotate(-5deg); }
            50% { transform: rotate(5deg); }
        }
        .animate-shine { animation: shine 4s ease-in-out infinite; }
        .animate-bounce-subtle { animation: bounce-subtle 2s ease-in-out infinite; }
        .animate-pulse-slow { animation: pulse-slow 3s ease-in-out infinite; }
        .animate-banner-wiggle { animation: banner-wiggle 0.5s ease-in-out infinite; }

        /* Help Button Animations */
        @keyframes bounce-slow {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        @keyframes help-wiggle {
            0%, 100% { transform: rotate(-3deg); }
            50% { transform: rotate(3deg); }
        }
        @keyframes float-1 {
            0%, 100% { transform: translate(0, 0) scale(1); opacity: 0.7; }
            33% { transform: translate(10px, -15px) scale(1.5); opacity: 1; }
            66% { transform: translate(-5px, -25px) scale(0.8); opacity: 0.5; }
        }
        @keyframes float-2 {
            0%, 100% { transform: translate(0, 0) scale(1); opacity: 0.7; }
            33% { transform: translate(-15px, -10px) scale(1.3); opacity: 1; }
            66% { transform: translate(5px, -20px) scale(0.9); opacity: 0.6; }
        }
        @keyframes float-3 {
            0%, 100% { transform: translate(0, 0) scale(1); opacity: 0.6; }
            50% { transform: translate(-20px, 10px) scale(1.4); opacity: 1; }
        }
        .animate-bounce-slow { animation: bounce-slow 2s ease-in-out infinite; }
        .animate-help-wiggle { animation: help-wiggle 1s ease-in-out infinite; }
        .animate-float-1 { animation: float-1 3s ease-in-out infinite; }
        .animate-float-2 { animation: float-2 3.5s ease-in-out infinite; }
        .animate-float-3 { animation: float-3 4s ease-in-out infinite; }
    </style>
</head>
<body class="bg-gray-50 flex flex-col min-h-screen font-sans antialiased overflow-x-hidden">

    <nav class="bg-gradient-to-r from-[#ea580c] to-red-600 text-white p-4 shadow-lg relative z-50">
        <div class="container mx-auto flex justify-between items-center">
            
            <div class="flex items-center gap-4">
                <button onclick="toggleMobileMenu()" class="md:hidden text-white focus:outline-none p-1 hover:bg-white/10 rounded transition">
                    <i class="fas fa-bars text-xl"></i>
                </button>

                <a href="{{ route('home') }}" class="flex items-center">
                    <img src="{{ asset('hasta.jpeg') }}" alt="HASTA Logo" class="h-8 md:h-10 w-auto object-contain drop-shadow-sm hover:scale-105 transition transform">
                </a>
            </div>

            <div class="hidden md:flex gap-12 text-sm font-bold tracking-wide">
                <a href="{{ route('home') }}" 
                   class="transition border-b-2 pb-1 {{ request()->routeIs('home') ? 'text-orange-100 border-white' : 'border-transparent hover:text-orange-100 hover:border-white' }}">
                   Home
                </a>
                <a href="{{ route('pages.about') }}" 
                   class="transition border-b-2 pb-1 {{ request()->routeIs('pages.about') ? 'text-orange-100 border-white' : 'border-transparent hover:text-orange-100 hover:border-white' }}">
                   About Us
                </a>
                <a href="{{ route('pages.faq') }}" 
                   class="transition border-b-2 pb-1 {{ request()->routeIs('pages.faq') ? 'text-orange-100 border-white' : 'border-transparent hover:text-orange-100 hover:border-white' }}">
                   FAQ
                </a>
                <a href="{{ route('pages.contact') }}" 
                   class="transition border-b-2 pb-1 {{ request()->routeIs('pages.contact') ? 'text-orange-100 border-white' : 'border-transparent hover:text-orange-100 hover:border-white' }}">
                   Contact Us
                </a>
            </div>

            <div class="flex items-center space-x-3 md:space-x-5">
                @auth
                <div class="relative">
                    <button onclick="toggleNotificationMenu(event)" class="flex items-center text-white-600 hover:text-blue-600 focus:outline-none relative p-1">
                        <i class="fas fa-bell text-lg md:text-base"></i>
                        @php $count = auth()->user()->unreadNotifications->count(); @endphp
                        @if($count > 0)
                            <span class="absolute -top-1 -right-1 md:-top-2 md:-right-2 bg-blue-600 text-white text-[10px] px-1.5 rounded-full">
                                {{ $count }}
                            </span>
                        @endif
                    </button>

                    <div id="notificationMenu" class="absolute right-0 mt-2 w-72 bg-white shadow-xl rounded-lg hidden z-50 border border-gray-100">
                        <div class="p-3 border-b text-xs font-bold text-gray-400 uppercase">Notifications</div>
                        <div class="max-h-60 overflow-y-auto">
                            @forelse(auth()->user()->unreadNotifications as $notification)
                                <div class="p-4 border-b hover:bg-gray-50">
                                    <p class="text-sm text-gray-800">{{ $notification->data['message'] }}</p>
                                    <p class="text-[10px] text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                                </div>
                            @empty
                                <p class="p-4 text-center text-xs text-gray-400">No new updates</p>
                            @endforelse
                        </div>
                        @if($count > 0)
                            <div class="p-2 border-t text-center">
                                <form action="{{ route('notifications.markRead') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="text-[10px] text-blue-600 font-bold hover:underline">Mark all as read</button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
                @endauth

                @auth
                    <div class="flex items-center gap-2 md:gap-4 bg-white/10 px-2 md:px-4 py-1.5 md:py-2 rounded-full border border-white/20 backdrop-blur-md shadow-sm">
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 group" title="Edit Profile">
                            <i class="fas fa-user-circle text-xl md:text-2xl group-hover:scale-105 transition"></i>
                            <span class="text-xs md:text-sm font-bold hidden sm:block group-hover:text-orange-100 transition">{{ Auth::user()->name }}</span>
                        </a>
                        
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-xs md:text-sm font-bold bg-white/20 hover:bg-white/30 px-3 md:px-4 py-1 rounded-full transition text-white shadow-sm border border-white/10 hover:shadow-md">
                                <span class="hidden sm:inline">Logout</span>
                                <i class="fas fa-sign-out-alt sm:hidden"></i>
                            </button>
                        </form>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="bg-white text-orange-600 px-4 py-1.5 md:px-5 md:py-2 rounded-full font-bold text-xs md:text-sm hover:bg-orange-50 transition shadow-md whitespace-nowrap">Login</a>
                @endauth
            </div>
        </div>

        <div id="mobileMenu" class="hidden md:hidden absolute top-full left-0 w-full bg-gray/95 backdrop-blur-xl shadow-2xl border-t border-gray-100 transition-all duration-300 origin-top transform z-40">
            <div class="flex flex-col p-4 space-y-2">
                {{-- Home --}}
                <a href="{{ route('home') }}" 
                   class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold transition-all duration-300 active:scale-95 group
                   {{ request()->routeIs('home') 
                       ? 'bg-orange-50 text-orange-600 shadow-sm border border-orange-100 translate-x-2' 
                       : 'text-gray-100 hover:bg-gray-50 hover:text-orange-500' }}">
                   <div class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center group-hover:bg-orange-200 transition-colors">
                       <i class="fas fa-home text-orange-500"></i> 
                   </div>
                   <span>Home</span>
                </a>
                
                {{-- About Us --}}
                <a href="{{ route('pages.about') }}" 
                   class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold transition-all duration-300 active:scale-95 group
                   {{ request()->routeIs('pages.about') 
                       ? 'bg-orange-50 text-orange-600 shadow-sm border border-orange-100 translate-x-2' 
                       : 'text-gray-100 hover:bg-gray-50 hover:text-orange-500' }}">
                   <div class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center group-hover:bg-orange-200 transition-colors">
                       <i class="fas fa-info-circle text-orange-500"></i>
                   </div>
                   <span>About Us</span>
                </a>
                
                {{-- FAQ --}}
                <a href="{{ route('pages.faq') }}" 
                   class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold transition-all duration-300 active:scale-95 group
                   {{ request()->routeIs('pages.faq') 
                       ? 'bg-orange-50 text-orange-600 shadow-sm border border-orange-100 translate-x-2' 
                       : 'text-gray-100 hover:bg-gray-50 hover:text-orange-500' }}">
                   <div class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center group-hover:bg-orange-200 transition-colors">
                       <i class="fas fa-question-circle text-orange-500"></i>
                   </div>
                   <span>FAQ</span>
                </a>
                
                {{-- Contact Us --}}
                <a href="{{ route('pages.contact') }}" 
                   class="flex items-center gap-3 px-4 py-3 rounded-xl font-bold transition-all duration-300 active:scale-95 group
                   {{ request()->routeIs('pages.contact') 
                       ? 'bg-orange-50 text-orange-600 shadow-sm border border-orange-100 translate-x-2' 
                       : 'text-gray-100 hover:bg-gray-50 hover:text-orange-500' }}">
                   <div class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center group-hover:bg-orange-200 transition-colors">
                       <i class="fas fa-envelope text-orange-500"></i>
                   </div>
                   <span>Contact Us</span>
                </a>
            </div>
        </div>
    </nav>

    @if(!request()->routeIs('home') && !request()->routeIs('pages.about') && !request()->routeIs('pages.contact') && !request()->routeIs('login') && !request()->routeIs('staff.login') && !request()->routeIs('register') && !request()->routeIs('password.*') && !request()->routeIs('pages.faq'))
        <div class="w-full flex justify-center py-4 md:py-6 relative z-40">
            {{-- 
                Mobile Fixes:
                1. w-fit + mx-auto: Centers the container.
                2. max-w-full: Prevents overflowing the screen width.
                3. px-4: Ensures a small gap from the screen edges.
            --}}
            <div class="w-fit max-w-full px-4 mx-auto overflow-x-auto no-scrollbar">
                
                {{-- Container --}}
                <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-full p-1 md:p-1.5 flex items-center shadow-2xl">
                    
                    {{-- Book Now --}}
                    {{-- Updated: text-xs (was text-[10px]) and px-4 (was px-3) for better mobile visibility --}}
                    <a href="{{ route('book.create') }}" 
                       class="px-4 sm:px-6 py-2 sm:py-2.5 rounded-full font-bold text-[12px] sm:text-[15px] transition-all duration-300 whitespace-nowrap active:scale-95
                       {{ (request()->routeIs('book.create') || request()->routeIs('book.search') || request()->routeIs('book.show') || request()->routeIs('book.payment') || request()->routeIs('book.payment.submit')) 
                           ? 'nav-link-active' 
                           : 'text-white hover:bg-white/10' }}">
                        Book Now
                    </a>
                    
                    {{-- My Bookings --}}
                    <a href="{{ route('book.index') }}" 
                       class="px-4 sm:px-6 py-2 sm:py-2.5 rounded-full font-bold text-[12px] sm:text-[15px] transition-all duration-300 whitespace-nowrap active:scale-95
                       {{ (request()->routeIs('book.index') || request()->routeIs('book.cancel')) 
                           ? 'nav-link-active' 
                           : 'text-white hover:bg-white/10' }}">
                        My Bookings
                    </a>
                    
                    {{-- Loyalty --}}
                    <a href="{{ route('loyalty.index') }}" 
                       class="px-4 sm:px-6 py-2 sm:py-2.5 rounded-full font-bold text-[12px] sm:text-[15px] transition-all duration-300 whitespace-nowrap active:scale-95
                       {{ (request()->routeIs('loyalty.index') || request()->routeIs('loyalty.redeem') || request()->routeIs('voucher.apply') || request()->routeIs('voucher.available')) 
                           ? 'nav-link-active' 
                           : 'text-white hover:bg-white/10' }}">
                        Loyalty
                    </a>
                    
                    {{-- Payments --}}
                    <a href="{{ route('finance.index') }}" 
                       class="px-4 sm:px-6 py-2 sm:py-2.5 rounded-full font-bold text-[12px] sm:text-[15px] transition-all duration-300 whitespace-nowrap active:scale-95
                       {{ (request()->routeIs('finance.index') || request()->routeIs('finance.claim') || request()->routeIs('finance.pay') || request()->routeIs('finance.submit_balance') || request()->routeIs('finance.pay_fine') || request()->routeIs('finance.submit_fine')) 
                           ? 'nav-link-active' 
                           : 'text-white hover:bg-white/10' }}">
                        Payments
                    </a>
                </div>
            </div>
        </div>
    @endif

    <button onclick="document.getElementById('help-modal').classList.remove('hidden')" 
            class="fixed bottom-6 right-6 bg-gradient-to-br from-orange-500 via-red-500 to-pink-600 text-white p-4 md:p-5 rounded-2xl shadow-[0_0_40px_rgba(234,88,12,0.6)] hover:shadow-[0_0_60px_rgba(234,88,12,0.9)] hover:scale-125 hover:rotate-12 transition-all duration-500 z-[99999] border-2 border-white/30 group cursor-pointer animate-bounce-slow backdrop-blur-sm overflow-hidden"
            style="position: fixed !important; z-index: 99999;">
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/30 to-transparent -skew-x-12 translate-x-[-200%] group-hover:translate-x-[200%] transition-transform duration-1000"></div>
        <div class="absolute inset-0 rounded-2xl bg-orange-500/50 animate-ping opacity-0 group-hover:opacity-75"></div>
        
        {{-- Icon size maintained --}}
        <i class="fas fa-question text-2xl relative z-10 group-hover:scale-125 group-hover:rotate-[360deg] transition-all duration-700 drop-shadow-lg"></i>
        
        <span class="absolute -top-10 -left-5 bg-gradient-to-r from-yellow-300 to-orange-400 text-orange-900 text-[11px] font-black px-3 py-1 rounded-full shadow-lg animate-help-wiggle group-hover:scale-110 group-hover:-rotate-6 transition-all duration-300 border-2 border-white z-20 whitespace-nowrap">
            How to rent? 🔥
        </span>
        <div class="absolute -top-1 -right-1 w-2 h-2 bg-yellow-300 rounded-full animate-float-1 opacity-70"></div>
        <div class="absolute -bottom-1 -left-2 w-1 h-1 bg-pink-300 rounded-full animate-float-2 opacity-70"></div>
        <div class="absolute -top-3 -right-3 w-2 h-2 bg-yellow-300 rounded-full animate-float-1 opacity-75"></div>
        <div class="absolute -bottom-3 -left-3 w-1 h-1 bg-pink-300 rounded-full animate-float-2 opacity-75"></div>
        <div class="absolute top-1/2 -right-2 w-1.5 h-1.5 bg-orange-300 rounded-full animate-float-3 opacity-60"></div>
    </button>

    <div id="help-modal" class="fixed inset-0 z-[10000] hidden bg-black/60 backdrop-blur-md flex items-center justify-center p-4">
        <div class="bg-[#1a1a1a] border border-white/10 rounded-2xl md:rounded-[2.5rem] max-w-3xl w-full p-4 md:p-8 relative shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-orange-600/20 blur-[80px] rounded-full"></div>
            <button onclick="document.getElementById('help-modal').classList.add('hidden')" class="absolute top-4 right-4 md:top-6 md:right-6 text-gray-500 hover:text-white transition z-20">
                <i class="fas fa-times text-xl"></i>
            </button>
            <div class="mb-4 md:mb-8 shrink-0">
                <h3 class="text-2xl md:text-4xl font-black text-white tracking-tighter">Rental <span class="text-orange-500">Journey</span></h3>
                <p class="text-gray-400 text-sm md:text-base mt-2">Follow these steps for a smooth experience.</p>
            </div>
            
            <div class="relative space-y-6 md:space-y-8 overflow-y-auto pr-2 md:pr-4 custom-scrollbar flex-1">
                <div class="absolute left-[19px] top-0 bottom-0 w-0.5 bg-gradient-to-b from-orange-500 via-blue-500 to-emerald-500 opacity-20"></div>
                
                <div class="relative flex gap-4 md:gap-6 group items-start">
                    <button type="button" onclick="toggleStepImage(this)" class="z-10 w-10 h-10 rounded-full bg-orange-500 text-white flex items-center justify-center shadow-[0_0_15px_rgba(234,88,12,0.4)] shrink-0 transition hover:scale-110 active:scale-95 cursor-pointer">
                        <i class="fas fa-user-edit text-sm"></i>
                    </button>
                    
                    <div class="bg-white/5 border border-white/10 p-3 md:p-4 rounded-xl md:rounded-2xl flex-1 hover:bg-white/10 transition">
                        <div class="step-image hidden md:block mb-3 bg-gray-800/50 rounded-xl overflow-hidden border border-white/10 aspect-video transition-all duration-300">
                            <img src="{{ asset('profile.png') }}" alt="Complete Profile" class="w-full h-full object-cover">
                        </div>
                        <h4 class="text-white font-bold text-sm">Complete Profile</h4>
                        <p class="text-xs text-gray-400 mt-1">Please complete all information in your profile settings to verify your account. <span class="text-orange-400 font-bold text-[10px] uppercase">(Tap icon to view image)</span></p>
                    </div>
                </div>
                
                <div class="relative flex gap-4 md:gap-6 group items-start">
                    <button type="button" onclick="toggleStepImage(this)" class="z-10 w-10 h-10 rounded-full bg-orange-500 text-white flex items-center justify-center shadow-[0_0_15px_rgba(234,88,12,0.4)] shrink-0 transition hover:scale-110 active:scale-95 cursor-pointer">
                        <i class="fas fa-calendar-check text-sm"></i>
                    </button>
                    <div class="bg-white/5 border border-white/10 p-3 md:p-4 rounded-xl md:rounded-2xl flex-1 hover:bg-white/10 transition">
                         <div class="step-image hidden md:block mb-3 bg-gray-800/50 rounded-xl overflow-hidden border border-white/10 aspect-video transition-all duration-300">
                            <img src="{{ asset('bookcar.png') }}" alt="Book a Vehicle" class="w-full h-full object-cover">
                        </div>
                        <h4 class="text-white font-bold text-sm">Book a Vehicle</h4>
                        <p class="text-xs text-gray-400 mt-1">Select pickup location, return location, and dates. Note: Every vehicle has a <span class="text-orange-400">3 hour cooldown</span> between bookings.</p>
                    </div>
                </div>

                <div class="relative flex gap-4 md:gap-6 group items-start">
                    <button type="button" onclick="toggleStepImage(this)" class="z-10 w-10 h-10 rounded-full bg-orange-500 text-white flex items-center justify-center shadow-[0_0_15px_rgba(234,88,12,0.4)] shrink-0 transition hover:scale-110 active:scale-95 cursor-pointer">
                        <i class="fas fa-wallet text-sm"></i>
                    </button>
                    <div class="bg-white/5 border border-white/10 p-3 md:p-4 rounded-xl md:rounded-2xl flex-1 hover:bg-white/10 transition">
                         <div class="step-image hidden md:block mb-3 bg-gray-800/50 rounded-xl overflow-hidden border border-white/10 aspect-video transition-all duration-300">
                            <img src="{{ asset('payment.png') }}" alt="Secure Slot" class="w-full h-full object-cover">
                        </div>
                        <h4 class="text-white font-bold text-sm">Secure Slot</h4>
                        <p class="text-xs text-gray-400 mt-1">Pay within <span class="text-orange-400">30 minutes</span> to avoid auto-cancellation.</p>
                    </div>
                </div>

                <div class="relative flex gap-4 md:gap-6 group items-start">
                    <button type="button" onclick="toggleStepImage(this)" class="z-10 w-10 h-10 rounded-full bg-blue-500 text-white flex items-center justify-center shadow-[0_0_15px_rgba(59,130,246,0.4)] shrink-0 transition hover:scale-110 active:scale-95 cursor-pointer">
                        <i class="fas fa-user-shield text-sm"></i>
                    </button>
                    <div class="bg-white/5 border border-white/10 p-3 md:p-4 rounded-xl md:rounded-2xl flex-1 hover:bg-white/10 transition border-l-4 border-l-blue-500">
                         <div class="step-image hidden md:block mb-3 bg-gray-800/50 rounded-xl overflow-hidden border border-white/10 aspect-video transition-all duration-300">
                            <img src="{{ asset('confirmed1.png') }}" alt="Staff Verification" class="w-full h-full object-cover">
                        </div>
                        <h4 class="text-white font-bold text-sm">Staff Verification</h4>
                        <p class="text-xs text-gray-400 mt-1">Wait for verification. Status changes to <span class="text-green-400">Confirmed</span>.</p>
                    </div>
                </div>

                <div class="relative flex gap-4 md:gap-6 group items-start">
                    <button type="button" onclick="toggleStepImage(this)" class="z-10 w-10 h-10 rounded-full bg-blue-500 text-white flex items-center justify-center shadow-[0_0_15px_rgba(59,130,246,0.4)] shrink-0 transition hover:scale-110 active:scale-95 cursor-pointer">
                        <i class="fas fa-camera text-sm"></i>
                    </button>
                    <div class="bg-white/5 border border-white/10 p-3 md:p-4 rounded-xl md:rounded-2xl flex-1 hover:bg-white/10 transition border-l-4 border-l-blue-500">
                         <div class="step-image hidden md:block mb-3 bg-gray-800/50 rounded-xl overflow-hidden border border-white/10 aspect-video transition-all duration-300">
                            <img src="{{ asset('pickup1.png') }}" alt="Pickup Inspection" class="w-full h-full object-cover">
                        </div>
                        <h4 class="text-white font-bold text-sm">Pickup Inspection</h4>
                        <p class="text-xs text-gray-400 mt-1">Upload <span class="font-bold text-white">5 specific photos</span> to activate your rental.</p>
                    </div>
                </div>

                <div class="relative flex gap-4 md:gap-6 group items-start">
                    <button type="button" onclick="toggleStepImage(this)" class="z-10 w-10 h-10 rounded-full bg-blue-500 text-white flex items-center justify-center shadow-[0_0_15px_rgba(59,130,246,0.4)] shrink-0 transition hover:scale-110 active:scale-95 cursor-pointer">
                        <i class="fas fa-key text-sm"></i>
                    </button>
                    <div class="bg-white/5 border border-white/10 p-3 md:p-4 rounded-xl md:rounded-2xl flex-1 hover:bg-white/10 transition border-l-4 border-l-blue-500">
                         <div class="step-image hidden md:block mb-3 bg-gray-800/50 rounded-xl overflow-hidden border border-white/10 aspect-video transition-all duration-300">
                            <img src="{{ asset('return1.png') }}" alt="Return & Key Check" class="w-full h-full object-cover">
                        </div>
                        <h4 class="text-white font-bold text-sm">Return & Key Check</h4>
                        <p class="text-xs text-gray-400 mt-1">Upload <span class="font-bold text-white">6 photos</span> upon return.</p>
                    </div>
                </div>

                <div class="relative flex gap-4 md:gap-6 group pb-4 items-start">
                    <button type="button" onclick="toggleStepImage(this)" class="z-10 w-10 h-10 rounded-full bg-emerald-500 text-white flex items-center justify-center shadow-[0_0_15px_rgba(16,185,129,0.4)] shrink-0 transition hover:scale-110 active:scale-95 cursor-pointer">
                        <i class="fas fa-check-circle text-sm"></i>
                    </button>
                    <div class="bg-emerald-500/10 border border-emerald-500/20 p-3 md:p-4 rounded-xl md:rounded-2xl flex-1 hover:bg-emerald-500/20 transition">
                         <div class="step-image hidden md:block mb-3 bg-gray-800/50 rounded-xl overflow-hidden border border-emerald-500/20 aspect-video transition-all duration-300">
                            <img src="{{ asset('completed1.png') }}" alt="Completion" class="w-full h-full object-cover">
                        </div>
                        <h4 class="text-emerald-400 font-bold text-sm">Completion</h4>
                        <p class="text-xs text-gray-300 mt-1">Staff performs final verification and deposit is refunded shortly.</p>
                    </div>
                </div>
            </div>
            <button onclick="document.getElementById('help-modal').classList.add('hidden')" class="w-full mt-4 md:mt-6 py-3 bg-white hover:bg-gray-200 text-black font-black rounded-xl transition text-xs uppercase tracking-widest shrink-0">Understood</button>
        </div>
    </div>

    <main class="flex-grow relative">
    @auth
    @php
        $reminderBooking = Auth::user()->bookings()
            ->whereIn('bookingStatus', ['Confirmed', 'Active'])
            ->orderBy('originalDate', 'asc')
            ->first();
    @endphp

    @if($reminderBooking && request()->routeIs('home'))
        @php
            $startDate = \Carbon\Carbon::parse($reminderBooking->originalDate);
            $diffInHours = now()->diffInHours($startDate, false);
            $displayHours = round($diffInHours); 
            $isUrgent = ($diffInHours > 0 && $diffInHours <= 24);
        @endphp

        <div class="absolute top-4 left-0 w-full z-50 px-4 pointer-events-none">
            <div class="container mx-auto max-w-5xl pointer-events-auto">
                @if($isUrgent)
                    {{-- URGENT DESIGN: Responsive & Compact --}}
                    <div class="relative group overflow-hidden bg-gradient-to-r from-blue-600/70 to-blue-600/70 backdrop-blur-lg border border-white/20 rounded-2xl p-3 sm:p-4 shadow-[0_20px_50px_rgba(220,38,38,0.3)] transition-all duration-300 hover:scale-[1.01]">
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full animate-[shine_3s_infinite]"></div>
                        
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 overflow-hidden">
                                {{-- Icon --}}
                                <div class="relative shrink-0">
                                    <div class="absolute inset-0 bg-white rounded-lg animate-ping opacity-20"></div>
                                    <div class="relative bg-white/20 p-2 sm:p-2.5 rounded-xl border border-white/30">
                                        <i class="fas fa-bolt text-white text-base sm:text-lg"></i>
                                    </div>
                                </div>
                                
                                {{-- Text Content --}}
                                <div class="flex flex-col min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="text-white/80 text-[8px] sm:text-[10px] font-black uppercase tracking-[0.2em]">Your booking</span>
                                        {{-- Mobile Timer Badge (Visible only on small screens) --}}
                                        <span class="sm:hidden bg-red-500/80 px-1.5 py-0.5 rounded text-[8px] font-bold text-white whitespace-nowrap animate-pulse">
                                            {{ $displayHours }}h LEFT
                                        </span>
                                    </div>
                                    <h3 class="text-white font-bold text-sm sm:text-lg leading-tight truncate pr-2">
                                        Pickup {{ $reminderBooking->vehicle->model }}
                                    </h3>
                                </div>

                                {{-- Desktop Timer (Hidden on mobile) --}}
                                <div class="hidden sm:flex bg-black/20 backdrop-blur-md px-4 py-1.5 rounded-full border border-white/10 ml-2 shrink-0">
                                    <span class="text-white text-xs font-black">
                                        IN {{ $displayHours }} {{ Str::plural('HOUR', $displayHours) }}
                                    </span>
                                </div>
                            </div>

                            {{-- Action Button --}}
                            <a href="{{ route('book.index') }}" class="group/btn relative bg-white px-4 py-2 sm:px-6 sm:py-2.5 rounded-xl flex items-center gap-2 transition-all hover:bg-orange-50 hover:shadow-lg active:scale-95 shrink-0">
                                <span class="text-red-600 font-black text-[10px] sm:text-xs">VIEW</span>
                                <i class="fas fa-arrow-right text-red-600 text-[10px] group-hover/btn:translate-x-1 transition-transform hidden sm:block"></i>
                            </a>
                        </div>
                    </div>
                @else
                    {{-- NON-URGENT DESIGN: Responsive & Compact --}}
                    <div class="bg-black/40 backdrop-blur-xl border border-white/10 rounded-2xl p-2.5 sm:p-3 shadow-2xl transition-all hover:bg-black/50">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="bg-orange-500/20 p-1.5 sm:p-2 rounded-xl border border-orange-500/30 shrink-0">
                                    <i class="fas fa-calendar-check text-orange-400 text-sm sm:text-base"></i>
                                </div>
                                <div class="flex flex-col min-w-0">
                                    <span class="text-gray-400 text-[8px] sm:text-[10px] font-bold uppercase tracking-wider">Upcoming Trip</span>
                                    <p class="text-white text-xs sm:text-sm font-bold truncate pr-2">
                                        {{ $reminderBooking->vehicle->model }} <span class="text-orange-400 mx-1">•</span> <span class="text-gray-300 font-medium">{{ $startDate->diffForHumans() }}</span>
                                    </p>
                                </div>
                            </div>
                            
                            <a href="{{ route('book.index') }}" class="text-white/70 hover:text-white px-3 py-1.5 sm:px-4 sm:py-2 rounded-lg text-[10px] sm:text-xs font-bold transition-colors flex items-center gap-2 bg-white/5 border border-white/10 shrink-0">
                                <span>Details</span>
                                <i class="fas fa-chevron-right text-[8px]"></i>
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif
    @endauth

    <div class="container mx-auto px-4">
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mt-4 bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm flex items-center" role="alert">
                <i class="fas fa-check-circle text-xl mr-3"></i>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif
    </div>

    @yield('content')
    </main>

    <footer class="bg-gray-900 text-white pt-8 md:pt-12 pb-8 text-sm relative z-30">
        <div class="container mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 md:gap-12 border-b border-gray-800 pb-8 md:pb-12 text-center md:text-left">
                <div class="space-y-4 flex flex-col items-center md:items-start">
                    <img src="{{ asset('hasta.jpeg') }}" alt="HASTA Logo" class="h-10 md:h-12 w-auto object-contain mb-2 md:mb-4">
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
                    <ul class="space-y-4 text-gray-400 flex flex-col items-center md:items-start">
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
            <div class="pt-8 text-center text-gray-500 flex flex-col md:flex-row justify-between items-center text-xs md:text-sm">
                <p>&copy; {{ date('Y') }} Hasta Travel & Tours Sdn. Bhd. All rights reserved.</p>
                <p class="mt-2 md:mt-0">SSM: 20240100987</p>
            </div>
        </div>
    </footer>

<script>
    function toggleMobileMenu() {
        const menu = document.getElementById('mobileMenu');
        menu.classList.toggle('hidden');
    }
</script>

<script>
    function toggleNotificationMenu(event) {
        event.stopPropagation();
        const menu = document.getElementById('notificationMenu');
        menu.classList.toggle('hidden');
    }
    window.addEventListener('click', function(e) {
        const menu = document.getElementById('notificationMenu');
        if (!menu.contains(e.target)) {
            menu.classList.add('hidden');
        }
    });
</script>

<script>
    @if(session('error'))
        alert("{{ session('error') }}");
    @endif
</script>

<style>
        @keyframes shine {
            100% { transform: translateX(100%); }
        }

        .glow-on-hover {
        border: none;
        outline: none;
        color: #fff;
        background: #fb5901ff;
        cursor: pointer;
        position: relative;
        z-index: 0;
        border-radius: 9999px; /* fully rounded */
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        text-decoration: none;
    }

    .glow-on-hover:before {
        content: '';
        background: linear-gradient(
            45deg,
            #ff0000,
            #ff7300,
            #fffb00,
            #48ff00,
            #00ffd5,
            #002bff,
            #7a00ff,
            #ff00c8,
            #ff0000
        );
        position: absolute;
        top: -2px;
        left: -2px;
        background-size: 400%;
        z-index: -1;
        filter: blur(6px);
        width: calc(100% + 4px);
        height: calc(100% + 4px);
        animation: glowing 20s linear infinite;
        opacity: 0;
        transition: opacity .3s ease-in-out;
        border-radius: 9999px;
    }

    .glow-on-hover:hover:before {
        opacity: 1;
    }

    .glow-on-hover:after {
        z-index: -1;
        content: '';
        position: absolute;
        width: 100%;
        height: 100%;
        background: #fb5901ff;
        left: 0;
        top: 0;
        border-radius: 9999px;
    }

    @keyframes glowing {
        0% { background-position: 0 0; }
        50% { background-position: 400% 0; }
        100% { background-position: 0 0; }
    }

</style>

<div id="global-loader" class="fixed inset-0 z-[99999] hidden bg-zinc-950/90 backdrop-blur-xl flex flex-col items-center justify-center transition-opacity duration-300">
    <div class="relative flex items-center justify-center mb-8">
        <div class="absolute inset-0 rounded-full bg-orange-500/5 blur-xl animate-pulse"></div>
        <div class="w-24 h-24 border-[3px] border-white/5 border-t-orange-500 rounded-full animate-spin"></div>
        <div class="absolute w-16 h-16 border-[3px] border-white/5 border-b-orange-400 rounded-full animate-[spin_2s_linear_infinite_reverse]"></div>
        <div class="absolute inset-0 flex items-center justify-center">
            <i class="fas fa-car-side text-orange-500 text-2xl drop-shadow-[0_0_10px_rgba(249,115,22,0.5)] animate-pulse"></i>
        </div>
    </div>
    
    <div class="text-center space-y-3 z-10">
        <h3 class="text-white font-bold text-3xl tracking-[0.3em] uppercase drop-shadow-md">
            Load<span class="text-orange-500">ing</span>
        </h3>
        <div class="flex flex-col items-center gap-1">
            <p class="text-zinc-400 text-sm font-light tracking-wide max-w-xs leading-relaxed">
                Processing your request. This may take a few seconds.
            </p>
            <span class="inline-block mt-2 px-3 py-1 rounded-full bg-orange-500/10 border border-orange-500/20 text-orange-400 text-[10px] font-bold tracking-wider uppercase animate-pulse">
                Please do not close this window
            </span>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const loader = document.getElementById('global-loader');
        
        // Attach listener to ALL forms
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                // 1. Ensure form is valid (HTML5 validation)
                if (!this.checkValidity()) return;

                // 2. Ignore "GET" forms (Search/Filter) if you want, 
                // but usually loading is good for them too. 
                // We'll focus on POST for emails.
                if (this.method.toUpperCase() === 'POST') {
                    loader.classList.remove('hidden');
                    
                    // Optional: Disable submit button to prevent double-clicks
                    const btn = this.querySelector('button[type="submit"]');
                    if(btn) {
                        btn.classList.add('opacity-50', 'cursor-not-allowed');
                    }
                }
            });
        });

        // Hide loader if user hits "Back" button (BF Cache fix)
        window.addEventListener('pageshow', (event) => {
            if (event.persisted) {
                loader.classList.add('hidden');
            }
        });
    });
</script>

{{-- Chat Widget --}}
<div class="fixed bottom-28 right-6 z-[100000] flex flex-col items-end space-y-4 font-sans pointer-events-none">

    {{-- 1. Dark Backdrop (Covers screen when open) --}}
    <div id="chat-backdrop" 
         onclick="toggleChat()"
         class="fixed inset-0 bg-black/60 backdrop-blur-[2px] transition-opacity duration-300 opacity-0 pointer-events-none"
         style="z-index: -1;">
    </div>

    {{-- Chat Window --}}
    <div id="chat-window" 
         class="transform transition-all duration-300 ease-in-out opacity-0 translate-y-5 scale-95 pointer-events-none
                w-[85vw] sm:w-[350px] h-[55vh] sm:h-[500px] bg-white/95 backdrop-blur-2xl rounded-2xl shadow-2xl border border-white/20 flex flex-col overflow-hidden ring-1 ring-black/5">
        
        {{-- Header --}}
        <div class="bg-gray-900 p-4 flex justify-between items-center shadow-md shrink-0 relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-gray-800 to-black opacity-90"></div>
            <div class="flex items-center gap-3 relative z-10">
                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-orange-500 to-red-600 flex items-center justify-center text-white shadow-lg shadow-orange-500/20">
                    <i class="fas fa-robot text-sm"></i>
                </div>
                <div class="flex flex-col">
                    <span class="font-bold text-white text-sm tracking-wide">Hasta Assistant</span>
                    <span class="text-[10px] text-emerald-400 flex items-center gap-1.5 font-medium">
                        <span class="relative flex h-2 w-2">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        Online
                    </span>
                </div>
            </div>
            <button onclick="toggleChat()" class="text-gray-400 hover:text-white transition-colors bg-white/5 hover:bg-white/10 p-1.5 rounded-lg relative z-10">
                <i class="fas fa-chevron-down text-xs"></i>
            </button>
        </div>

        {{-- Messages Area --}}
        <div id="messages" 
             class="flex-1 p-4 overflow-y-auto space-y-4 scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-transparent"
             style="background-color: #f8fafc; background-image: radial-gradient(#cbd5e1 1px, transparent 1px); background-size: 20px 20px;">
            
            <div class="flex gap-3 animate-fade-in">
                <div class="w-8 h-8 rounded-full bg-white border border-gray-200 flex items-center justify-center text-orange-600 shrink-0 mt-1 shadow-sm">
                    <i class="fas fa-robot text-xs"></i>
                </div>
                <div class="bg-white border border-gray-200 text-slate-700 p-3.5 rounded-2xl rounded-tl-none shadow-sm text-sm max-w-[85%] leading-relaxed">
                    Hello! I'm here to help you with your booking. How can I assist you today?
                </div>
            </div>
        </div>

        {{-- Input Area --}}
        <div class="p-3 sm:p-4 bg-white border-t border-gray-100 shrink-0">
            <form onsubmit="sendMessage(event)" class="relative flex items-center gap-2">
                <input type="text" 
                       id="user-input" 
                       class="w-full bg-gray-50 text-slate-800 rounded-full pl-5 pr-12 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500/50 border border-gray-200 transition-all placeholder-gray-400" 
                       placeholder="Type your question..."
                       autocomplete="off">
                <button type="submit" 
                        class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 bg-gradient-to-r from-orange-600 to-red-600 text-white rounded-full hover:shadow-lg hover:scale-105 active:scale-95 flex items-center justify-center transition-all duration-300">
                    <i class="fas fa-paper-plane text-[10px] translate-x-[-1px] translate-y-[1px]"></i>
                </button>
            </form>
            <div class="text-center mt-2 flex items-center justify-center gap-1.5 opacity-60">
                <i class="fas fa-bolt text-[10px] text-orange-500"></i>
                <p class="text-[10px] text-gray-400 font-medium">Powered by DaTeam Ai</p>
            </div>
        </div>
    </div>

    {{-- Toggle Button (Sized exactly like the Help button) --}}
    <button onclick="toggleChat()" 
        id="chat-toggle-btn"
        class="bg-gradient-to-br from-indigo-600 via-purple-600 to-blue-500 text-white p-3 md:p-4 rounded-2xl shadow-[0_0_40px_rgba(79,70,229,0.6)] hover:shadow-[0_0_60px_rgba(79,70,229,0.9)] hover:scale-125 transition-all duration-500 border-2 border-white/30 group cursor-pointer animate-bounce-slow backdrop-blur-sm overflow-hidden pointer-events-auto relative z-50">
    
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/30 to-transparent -skew-x-12 translate-x-[-200%] group-hover:translate-x-[200%] transition-transform duration-1000"></div>
        <div class="absolute inset-0 rounded-2xl bg-indigo-500/50 animate-ping opacity-0 group-hover:opacity-75"></div>

        {{-- Icon size matches Help button (text-xl) --}}
        <i class="fas fa-robot text-xl relative z-10 drop-shadow-lg icon-open animate-robot-wave group-hover:animate-none"></i>
        <i class="fas fa-times text-xl relative z-10 hidden icon-close"></i>

        <span class="absolute -top-10 -right-5 bg-gradient-to-r from-blue-400 to-indigo-500 text-white text-[11px] font-black px-3 py-1 rounded-full shadow-lg animate-pulse-slow group-hover:scale-110 transition-all duration-300 border-2 border-white z-20 whitespace-nowrap">
            DaTeam Ai ⚡
        </span>

        <div class="absolute -top-1 -left-1 w-2 h-2 bg-cyan-300 rounded-full animate-float-1 opacity-70"></div>
        <div class="absolute -bottom-1 -right-1 w-2 h-2 bg-purple-300 rounded-full animate-float-2 opacity-70"></div>
        <div class="absolute top-1/2 -left-2 w-1.5 h-1.5 bg-blue-300 rounded-full animate-float-3 opacity-60"></div>
        <div class="absolute top-2 left-2 w-1.5 h-1.5 bg-cyan-300 rounded-full animate-float-1 opacity-70"></div>
        <div class="absolute bottom-2 right-2 w-1.5 h-1.5 bg-purple-300 rounded-full animate-float-2 opacity-70"></div>
    </button>
</div>

<style>
    .scrollbar-thin::-webkit-scrollbar { width: 6px; }
    .scrollbar-thin::-webkit-scrollbar-thumb { background-color: #CBD5E1; border-radius: 20px; }
    .scrollbar-thin::-webkit-scrollbar-track { background-color: transparent; }
    
    @keyframes robot-wave {
        0%, 100% { transform: rotate(0deg); }
        20% { transform: rotate(-15deg); }
        70% { transform: rotate(10deg); }
    }
    .animate-robot-wave {
        animation: robot-wave 2s ease-in-out infinite;
    }
</style>

<script>
    let isOpen = false;

    function toggleChat() {
        // Guest Redirect Logic
        const isLoggedIn = {{ auth()->check() ? 'true' : 'false' }};
        
        if (!isLoggedIn) {
            window.location.href = "{{ route('login') }}";
            return;
        }

        const box = document.getElementById('chat-window');
        const btn = document.getElementById('chat-toggle-btn');
        const backdrop = document.getElementById('chat-backdrop');
        const iconRobot = btn.querySelector('.icon-open');
        const iconClose = btn.querySelector('.icon-close');

        isOpen = !isOpen;

        if (isOpen) {
            // Open State
            box.classList.remove('opacity-0', 'translate-y-5', 'pointer-events-none', 'scale-95');
            box.classList.add('pointer-events-auto'); 
            
            // 2. Show Backdrop (Dark Background)
            backdrop.classList.remove('opacity-0', 'pointer-events-none');
            backdrop.classList.add('opacity-100', 'pointer-events-auto');

            iconRobot.classList.add('hidden');
            iconClose.classList.remove('hidden');
            
            setTimeout(() => document.getElementById('user-input').focus(), 300);
        } else {
            // Close State
            box.classList.remove('pointer-events-auto');
            box.classList.add('opacity-0', 'translate-y-5', 'pointer-events-none', 'scale-95');

            // 2. Hide Backdrop
            backdrop.classList.remove('opacity-100', 'pointer-events-auto');
            backdrop.classList.add('opacity-0', 'pointer-events-none');
            
            iconRobot.classList.remove('hidden');
            iconClose.classList.add('hidden');
        }
    }

    function showTyping() {
        const chatBox = document.getElementById('messages');
        const id = 'typing-' + Date.now();
        
        const html = `
            <div id="${id}" class="flex gap-3 animate-fade-in">
                <div class="w-8 h-8 rounded-full bg-white border border-gray-200 flex items-center justify-center text-orange-600 shrink-0 mt-1 shadow-sm">
                    <i class="fas fa-robot text-xs"></i>
                </div>
                <div class="bg-white border border-gray-200 p-3.5 rounded-2xl rounded-tl-none shadow-sm w-16 flex items-center justify-center gap-1">
                    <div class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce"></div>
                    <div class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce delay-100"></div>
                    <div class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce delay-200"></div>
                </div>
            </div>`;
            
        chatBox.insertAdjacentHTML('beforeend', html);
        chatBox.scrollTop = chatBox.scrollHeight;
        return id;
    }

    async function sendMessage(e) {
        e.preventDefault();
        const input = document.getElementById('user-input');
        const message = input.value.trim();
        const chatBox = document.getElementById('messages');

        if (!message) return;

        // Add User Message
        chatBox.innerHTML += `
            <div class="flex gap-2 justify-end animate-fade-in">
                <div class="bg-gradient-to-r from-orange-600 to-red-600 text-white p-3.5 rounded-2xl rounded-tr-none shadow-md text-sm max-w-[85%] leading-relaxed">
                    ${message}
                </div>
            </div>`;
        
        input.value = '';
        chatBox.scrollTop = chatBox.scrollHeight;

        const typingId = showTyping();

        try {
            const response = await fetch("{{ route('chatbot.ask') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ message: message })
            });
            
            const data = await response.json();

            document.getElementById(typingId).remove();

            // Show Bot Response
            chatBox.innerHTML += `
                <div class="flex gap-3 animate-fade-in">
                    <div class="w-8 h-8 rounded-full bg-white border border-gray-200 flex items-center justify-center text-orange-600 shrink-0 mt-1 shadow-sm">
                        <i class="fas fa-robot text-xs"></i>
                    </div>
                    <div class="bg-white border border-gray-200 text-slate-700 p-3.5 rounded-2xl rounded-tl-none shadow-sm text-sm max-w-[85%] leading-relaxed">
                        ${data.reply}
                    </div>
                </div>`;
            
            chatBox.scrollTop = chatBox.scrollHeight;

        } catch (error) {
            console.error(error);
            document.getElementById(typingId).remove();
            
            chatBox.innerHTML += `
                <div class="flex gap-3">
                    <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center text-red-600 shrink-0 mt-1">
                        <i class="fas fa-exclamation-triangle text-xs"></i>
                    </div>
                    <div class="bg-white border border-red-100 text-red-600 p-3.5 rounded-2xl rounded-tl-none shadow-sm text-sm">
                        Sorry, I'm having trouble connecting right now.
                    </div>
                </div>`;
        }
    }
</script>

<script>
    // Logic to toggle images in the help modal when the icon is clicked
    function toggleStepImage(btn) {
        // Find the parent group's next sibling or find the image container within the group
        const group = btn.closest('.group');
        const imgContainer = group.querySelector('.step-image');
        
        if(imgContainer) {
            imgContainer.classList.toggle('hidden');
        }
    }
</script>

</body>
</html>