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
            @auth
            <div class="relative">
                <button onclick="toggleNotificationMenu(event)" class="flex items-center text-white-600 hover:text-blue-600 focus:outline-none relative">
                    <i class="fas fa-bell"></i>
                    @php $count = auth()->user()->unreadNotifications->count(); @endphp
                    @if($count > 0)
                        <span class="absolute -top-2 -right-2 bg-blue-600 text-white text-[10px] px-1.5 rounded-full">
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
                    <div class="flex items-center gap-4 bg-white/10 px-4 py-2 rounded-full border border-white/20 backdrop-blur-md shadow-sm">
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 group" title="Edit Profile">
                            <i class="fas fa-user-circle text-2xl group-hover:scale-105 transition"></i>
                            <span class="text-sm font-bold hidden sm:block group-hover:text-orange-100 transition">{{ Auth::user()->name }}</span>
                        </a>
                        
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-sm font-bold bg-white/20 hover:bg-white/30 px-4 py-1 rounded-full transition text-white shadow-sm border border-white/10 hover:shadow-md">
                                Logout
                            </button>
                        </form>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="bg-white text-orange-600 px-5 py-2 rounded-full font-bold text-sm hover:bg-orange-50 transition shadow-md">Login</a>
                @endauth
            </div>
        </div>
    </nav>

    @if(!request()->routeIs('home') && !request()->routeIs('pages.about') && !request()->routeIs('pages.contact') && !request()->routeIs('login') && !request()->routeIs('staff.login') && !request()->routeIs('register') && !request()->routeIs('password.*') && !request()->routeIs('pages.faq'))
        <div class="w-full flex justify-center py-6 relative z-40">
            <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-full p-1.5 flex items-center shadow-2xl">
                <a href="{{ route('book.create') }}" 
                   class="px-8 py-2.5 rounded-full font-bold transition {{ (request()->routeIs('book.create') || request()->routeIs('book.search') || request()->routeIs('book.show') || request()->routeIs('book.payment') || request()->routeIs('book.payment.submit')) ? 'nav-link-active' : 'text-white/80 hover:bg-white/10' }}">
                    Book Now
                </a>
                <a href="{{ route('book.index') }}" 
                   class="px-8 py-2.5 rounded-full font-bold transition {{ (request()->routeIs('book.index') || request()->routeIs('book.cancel')) ? 'nav-link-active' : 'text-white/80 hover:bg-white/10' }}">
                    My Bookings
                </a>
                <a href="{{ route('loyalty.index') }}" 
                   class="px-8 py-2.5 rounded-full font-bold transition {{ (request()->routeIs('loyalty.index') || request()->routeIs('loyalty.redeem') || request()->routeIs('voucher.apply') || request()->routeIs('voucher.available')) ? 'nav-link-active' : 'text-white/80 hover:bg-white/10' }}">
                    Loyalty
                </a>
                <a href="{{ route('finance.index') }}" 
                   class="px-8 py-2.5 rounded-full font-bold transition {{ (request()->routeIs('finance.index') || request()->routeIs('finance.claim') || request()->routeIs('finance.pay') || request()->routeIs('finance.submit_balance') || request()->routeIs('finance.pay_fine') || request()->routeIs('finance.submit_fine')) ? 'nav-link-active' : 'text-white/80 hover:bg-white/10' }}">
                    Payments
                </a>
            </div>
        </div>
    @endif

    <button onclick="document.getElementById('help-modal').classList.remove('hidden')" 
            class="fixed bottom-6 right-6 bg-gradient-to-br from-orange-500 via-red-500 to-pink-600 text-white p-5 rounded-2xl shadow-[0_0_40px_rgba(234,88,12,0.6)] hover:shadow-[0_0_60px_rgba(234,88,12,0.9)] hover:scale-125 hover:rotate-12 transition-all duration-500 z-[99999] border-2 border-white/30 group cursor-pointer animate-bounce-slow backdrop-blur-sm overflow-hidden"
            style="position: fixed !important;">
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/30 to-transparent -skew-x-12 translate-x-[-200%] group-hover:translate-x-[200%] transition-transform duration-1000"></div>
        <div class="absolute inset-0 rounded-2xl bg-orange-500/50 animate-ping opacity-0 group-hover:opacity-75"></div>
        <i class="fas fa-question text-2xl relative z-10 group-hover:scale-125 group-hover:rotate-[360deg] transition-all duration-700 drop-shadow-lg"></i>
        <span class="absolute -top-10 -left-5 bg-gradient-to-r from-yellow-300 to-orange-400 text-orange-900 text-[11px] font-black px-3 py-1 rounded-full shadow-lg animate-help-wiggle group-hover:scale-110 group-hover:-rotate-6 transition-all duration-300 border-2 border-white z-20">
            How to rent? 🔥
        </span>
        <div class="absolute -top-1 -right-1 w-2 h-2 bg-yellow-300 rounded-full animate-float-1 opacity-70"></div>
        <div class="absolute -bottom-1 -left-1 w-2 h-2 bg-pink-300 rounded-full animate-float-2 opacity-70"></div>
        <div class="absolute top-1/2 -right-2 w-1.5 h-1.5 bg-orange-300 rounded-full animate-float-3 opacity-60"></div>
    </button>

    <div id="help-modal" class="fixed inset-0 z-[10000] hidden bg-black/60 backdrop-blur-md flex items-center justify-center p-4">
        <div class="bg-[#1a1a1a] border border-white/10 rounded-[2.5rem] max-w-3xl w-full p-8 relative shadow-2xl overflow-hidden">
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-orange-600/20 blur-[80px] rounded-full"></div>
            <button onclick="document.getElementById('help-modal').classList.add('hidden')" class="absolute top-6 right-6 text-gray-500 hover:text-white transition">
                <i class="fas fa-times text-xl"></i>
            </button>
            <div class="mb-8">
                <h3 class="text-4xl font-black text-white tracking-tighter">Rental <span class="text-orange-500">Journey</span></h3>
                <p class="text-gray-400 text-base mt-2">Follow these steps for a smooth experience.</p>
            </div>
            <div class="relative space-y-8 max-h-[60vh] overflow-y-auto pr-4 custom-scrollbar">
                <div class="absolute left-[15px] top-0 bottom-0 w-0.5 
                            bg-gradient-to-b from-orange-500 via-blue-500 to-emerald-500 opacity-20">
                </div>
                <div class="relative flex gap-6 group">
                    <div class="z-10 w-8 h-8 rounded-full bg-orange-500 text-white flex items-center justify-center shadow-[0_0_15px_rgba(234,88,12,0.4)] shrink-0 transition group-hover:scale-110"><i class="fas fa-user-edit text-[10px]"></i></div>
                    <div class="bg-white/5 border border-white/10 p-4 rounded-2xl flex-1 hover:bg-white/10 transition">
                        <div class="mb-3 bg-gray-800/50 rounded-xl overflow-hidden border border-white/10 aspect-video">
                            <img src="{{ asset('profile.png') }}" alt="Complete Profile" class="w-full h-full object-cover">
                        </div>
                        <h4 class="text-white font-bold text-sm">Complete Profile</h4>
                        <p class="text-xs text-gray-400 mt-1">Please complete all information in your profile settings to verify your account. Account verification is required before booking a vehicle. Any failure to complete this step will result in your booking being denied.</p>
                    </div>
                </div>
                <div class="relative flex gap-6 group">
                    <div class="z-10 w-8 h-8 rounded-full bg-orange-500 text-white flex items-center justify-center shadow-[0_0_15px_rgba(234,88,12,0.4)] shrink-0 transition group-hover:scale-110"><i class="fas fa-calendar-check text-[10px]"></i></div>
                    <div class="bg-white/5 border border-white/10 p-4 rounded-2xl flex-1 hover:bg-white/10 transition">
                        <div class="mb-3 bg-gray-800/50 rounded-xl overflow-hidden border border-white/10 aspect-video">
                            <img src="{{ asset('bookcar.png') }}" alt="Book a Vehicle" class="w-full h-full object-cover">
                        </div>
                        <h4 class="text-white font-bold text-sm">Book a Vehicle</h4>
                        <p class="text-xs text-gray-400 mt-1">Select pickup location, return location, and dates. Browse your favourable car. Note: Every vehicle has a <span class="text-orange-400">3 hour cooldown</span> between bookings. Please choose your booking date wisely. Pickup location and return location except for Student Mall, UTM are subject to additional delivery fees.</p>
                    </div>
                </div>
                <div class="relative flex gap-6 group">
                    <div class="z-10 w-8 h-8 rounded-full bg-orange-500 text-white flex items-center justify-center shadow-[0_0_15px_rgba(234,88,12,0.4)] shrink-0 transition group-hover:scale-110"><i class="fas fa-wallet text-[10px]"></i></div>
                    <div class="bg-white/5 border border-white/10 p-4 rounded-2xl flex-1 hover:bg-white/10 transition">
                        <div class="mb-3 bg-gray-800/50 rounded-xl overflow-hidden border border-white/10 aspect-video ">
                            <img src="{{ asset('payment.png') }}" alt="Secure Slot" class="w-full h-full object-cover">
                        </div>
                        <h4 class="text-white font-bold text-sm">Secure Slot</h4>
                        <p class="text-xs text-gray-400 mt-1">Pay within <span class="text-orange-400">30 minutes</span> to avoid auto-cancellation.</p>
                    </div>
                </div>
                <div class="relative flex gap-6 group">
                    <div class="z-10 w-8 h-8 rounded-full bg-blue-500 text-white flex items-center justify-center shadow-[0_0_15px_rgba(59,130,246,0.4)] shrink-0 transition group-hover:scale-110"><i class="fas fa-user-shield text-[10px]"></i></div>
                    <div class="bg-white/5 border border-white/10 p-4 rounded-2xl flex-1 hover:bg-white/10 transition border-l-4 border-l-blue-500">
                        <div class="mb-3 bg-gray-800/50 rounded-xl overflow-hidden border border-white/10 aspect-video ">
                            <img src="{{ asset('confirmed1.png') }}" alt="Staff Verification" class="w-full h-full object-cover">
                        </div>
                        <h4 class="text-white font-bold text-sm">Staff Verification</h4>
                        <p class="text-xs text-gray-400 mt-1">Your status after submit is submitted. Please wait for verification. Status changes to <span class="text-green-400">Confirmed</span>.</p>
                    </div>
                </div>
                <div class="relative flex gap-6 group">
                    <div class="z-10 w-8 h-8 rounded-full bg-blue-500 text-white flex items-center justify-center shadow-[0_0_15px_rgba(59,130,246,0.4)] shrink-0 transition group-hover:scale-110"><i class="fas fa-camera text-[10px]"></i></div>
                    <div class="bg-white/5 border border-white/10 p-4 rounded-2xl flex-1 hover:bg-white/10 transition border-l-4 border-l-blue-500">
                        <div class="mb-3 bg-gray-800/50 rounded-xl overflow-hidden border border-white/10 aspect-video">
                            <img src="{{ asset('pickup1.png') }}" alt="Pickup Inspection" class="w-full h-full object-cover">
                        </div>
                        <h4 class="text-white font-bold text-sm">Pickup Inspection</h4>
                        <p class="text-xs text-gray-400 mt-1">Upload <span class="font-bold text-white">5 specific photos and some information</span> to activate your rental.</p>
                    </div>
                </div>
                <div class="relative flex gap-6 group">
                    <div class="z-10 w-8 h-8 rounded-full bg-blue-500 text-white flex items-center justify-center shadow-[0_0_15px_rgba(59,130,246,0.4)] shrink-0 transition group-hover:scale-110"><i class="fas fa-key text-[10px]"></i></div>
                    <div class="bg-white/5 border border-white/10 p-4 rounded-2xl flex-1 hover:bg-white/10 transition border-l-4 border-l-blue-500">
                        <div class="mb-3 bg-gray-800/50 rounded-xl overflow-hidden border border-white/10 aspect-video ">
                            <img src="{{ asset('return1.png') }}" alt="Return & Key Check" class="w-full h-full object-cover">
                        </div>
                        <h4 class="text-white font-bold text-sm">Return & Key Check</h4>
                        <p class="text-xs text-gray-400 mt-1">Upload <span class="font-bold text-white">6 photos and some information</span> upon return to end the session.</p>
                    </div>
                </div>
                <div class="relative flex gap-6 group pb-4">
                    <div class="z-10 w-8 h-8 rounded-full bg-emerald-500 text-white flex items-center justify-center shadow-[0_0_15px_rgba(16,185,129,0.4)] shrink-0 transition group-hover:scale-110"><i class="fas fa-check-circle text-[10px]"></i></div>
                    <div class="bg-emerald-500/10 border border-emerald-500/20 p-4 rounded-2xl flex-1 hover:bg-emerald-500/20 transition">
                        <div class="mb-3 bg-gray-800/50 rounded-xl overflow-hidden border border-emerald-500/20 aspect-video">
                            <img src="{{ asset('completed1.png') }}" alt="Completion" class="w-full h-full object-cover">
                        </div>
                        <h4 class="text-emerald-400 font-bold text-sm">Completion</h4>
                        <p class="text-xs text-gray-300 mt-1">Staff performs final verification and deposit is refunded shortly. Your deposit will burn if you break any rules in agreement.</p>
                    </div>
                </div>
            </div>
            <button onclick="document.getElementById('help-modal').classList.add('hidden')" class="w-full mt-6 py-3 bg-white hover:bg-gray-200 text-black font-black rounded-xl transition text-xs uppercase tracking-widest">Understood</button>
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
                    {{-- URGENT DESIGN: Floating Intense Island --}}
                    <div class="relative group overflow-hidden bg-gradient-to-r from-blue-600/70 to-blue-600/70 backdrop-blur-lg border border-white/20 rounded-2xl p-4 shadow-[0_20px_50px_rgba(220,38,38,0.3)] transition-all duration-300 hover:scale-[1.01]">
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full animate-[shine_3s_infinite]"></div>
                        
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <div class="relative">
                                    <div class="absolute inset-0 bg-white rounded-lg animate-ping opacity-20"></div>
                                    <div class="relative bg-white/20 p-2.5 rounded-xl border border-white/30">
                                        <i class="fas fa-bolt text-white text-lg"></i>
                                    </div>
                                </div>
                                
                                <div class="flex flex-col">
                                    <div class="flex items-center gap-2">
                                        <span class="text-white/80 text-[10px] font-black uppercase tracking-[0.2em]">Your booking</span>
                                    </div>
                                    <h3 class="text-white font-bold text-lg leading-tight">
                                        Pickup {{ $reminderBooking->vehicle->model }}
                                    </h3>
                                </div>

                                <div class="hidden sm:flex bg-black/20 backdrop-blur-md px-4 py-1.5 rounded-full border border-white/10 ml-2">
                                    <span class="text-white text-xs font-black">
                                        IN {{ $displayHours }} {{ Str::plural('HOUR', $displayHours) }}
                                    </span>
                                </div>
                            </div>

                            <a href="{{ route('book.index') }}" class="group/btn relative bg-white px-6 py-2.5 rounded-xl flex items-center gap-2 transition-all hover:bg-orange-50 hover:shadow-lg active:scale-95">
                                <span class="text-red-600 font-black text-xs">VIEW NOW</span>
                                <i class="fas fa-arrow-right text-red-600 text-[10px] group-hover/btn:translate-x-1 transition-transform"></i>
                            </a>
                        </div>
                    </div>
                @else
                    {{-- NON-URGENT DESIGN: Minimalist Glass Island --}}
                    <div class="bg-black/40 backdrop-blur-xl border border-white/10 rounded-2xl p-3 shadow-2xl transition-all hover:bg-black/50">
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <div class="bg-orange-500/20 p-2 rounded-xl border border-orange-500/30">
                                    <i class="fas fa-calendar-check text-orange-400"></i>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-gray-400 text-[10px] font-bold uppercase tracking-wider">Upcoming Trip</span>
                                    <p class="text-white text-sm font-bold">
                                        {{ $reminderBooking->vehicle->model }} <span class="text-orange-400 mx-1.5">•</span> <span class="text-gray-300 font-medium">{{ $startDate->diffForHumans() }}</span>
                                    </p>
                                </div>
                            </div>
                            
                            <a href="{{ route('book.index') }}" class="text-white/70 hover:text-white px-4 py-2 rounded-lg text-xs font-bold transition-colors flex items-center gap-2 bg-white/5 border border-white/10">
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

<!-- Flash error message script -->
<script>
    @if(session('error'))
        alert("{{ session('error') }}");
    @endif
</script>

<!-- Custom animation keyframes -->
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

<div id="global-loader" class="fixed inset-0 z-[99999] hidden bg-black/90 flex flex-col items-center justify-center backdrop-blur-md transition-opacity duration-300">
    <div class="relative mb-6">
        <div class="w-20 h-20 border-4 border-orange-500/30 border-t-orange-500 rounded-full animate-spin"></div>
        <div class="absolute inset-0 flex items-center justify-center">
            <i class="fas fa-car-side text-orange-500 text-xl animate-pulse"></i>
        </div>
    </div>
    
    <h3 class="text-white font-black text-2xl tracking-[0.2em] animate-pulse">LOADING</h3>
    <p class="text-gray-400 text-sm mt-3 font-medium text-center max-w-xs">
        Processing your request. This may take a few seconds.
        <br><span class="text-orange-500 text-xs">Please do not close this window.</span>
    </p>
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
</body>
</html>