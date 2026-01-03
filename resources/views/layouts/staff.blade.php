<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasta Staff Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans">

    <div class="flex h-screen overflow-hidden">
    
    {{-- SIDEBAR --}}
    <aside class="w-64 bg-orange-100/45 border-r border-gray-100 hidden md:flex flex-col justify-between fixed h-full z-20 shadow-[4px_0_24px_rgba(0,0,0,0.02)]">
        <div>
            {{-- LOGO AREA --}}
            <div class="h-24 flex items-center px-8">
                 <img src="{{ asset('hasta.jpeg') }}" alt="Hasta Logo" class="h-10 w-auto object-contain">
            </div>

            {{-- NAVIGATION --}}
            <nav class="px-4 space-y-1 mt-2">
                
                {{-- Dashboard Link --}}
                <a href="{{ route('staff.dashboard') }}" 
                   class="group flex items-center px-4 py-3.5 rounded-xl transition-all duration-300 font-bold text-sm relative overflow-hidden
                   {{ request()->routeIs('staff.dashboard') 
                        ? 'text-white shadow-lg shadow-orange-500/25' 
                        : 'text-gray-500 hover:bg-orange-50 hover:text-orange-600' }}">
                    
                    {{-- Active Background Gradient --}}
                    @if(request()->routeIs('staff.dashboard'))
                        <div class="absolute inset-0 bg-gradient-to-br from-orange-500 to-orange-600 z-0"></div>
                    @endif

                    <div class="relative z-10 flex items-center w-full">
                        <i class="fas fa-th-large w-6 text-center mr-3 text-lg {{ request()->routeIs('staff.dashboard') ? 'text-orange-100' : 'text-gray-400 group-hover:text-orange-500 transition-colors' }}"></i> 
                        <span>Dashboard</span>
                    </div>
                </a>

                {{-- Manage Bookings --}}
                <a href="{{ route('staff.bookings.index') }}" 
                   class="group flex items-center px-4 py-3.5 rounded-xl transition-all duration-300 font-bold text-sm relative overflow-hidden
                   {{ request()->routeIs('staff.bookings.*') 
                        ? 'text-white shadow-lg shadow-orange-500/25' 
                        : 'text-gray-500 hover:bg-orange-50 hover:text-orange-600' }}">
                    
                    @if(request()->routeIs('staff.bookings.*'))
                        <div class="absolute inset-0 bg-gradient-to-br from-orange-500 to-orange-600 z-0"></div>
                    @endif

                    <div class="relative z-10 flex items-center w-full">
                        <i class="fas fa-file-invoice w-6 text-center mr-3 text-lg {{ request()->routeIs('staff.bookings.*') ? 'text-orange-100' : 'text-gray-400 group-hover:text-orange-500 transition-colors' }}"></i> 
                        <span>Manage Bookings</span>
                    </div>
                </a>
                
                {{-- Fleet Management --}}
                <a href="{{ route('staff.fleet.index') }}" 
                   class="group flex items-center px-4 py-3.5 rounded-xl transition-all duration-300 font-bold text-sm relative overflow-hidden
                   {{ request()->routeIs('staff.fleet.*') 
                        ? 'text-white shadow-lg shadow-orange-500/25' 
                        : 'text-gray-500 hover:bg-orange-50 hover:text-orange-600' }}">
                    
                    @if(request()->routeIs('staff.fleet.*'))
                        <div class="absolute inset-0 bg-gradient-to-br from-orange-500 to-orange-600 z-0"></div>
                    @endif

                    <div class="relative z-10 flex items-center w-full">
                        <i class="fas fa-car w-6 text-center mr-3 text-lg {{ request()->routeIs('staff.fleet.*') ? 'text-orange-100' : 'text-gray-400 group-hover:text-orange-500 transition-colors' }}"></i> 
                        <span>Fleet Management</span>
                    </div>
                </a>

                {{-- Customer Management --}}
                <a href="{{ route('staff.customers.index') }}"
                   class="group flex items-center px-4 py-3.5 rounded-xl transition-all duration-300 font-bold text-sm relative overflow-hidden
                   {{ request()->routeIs('staff.customers.*') 
                        ? 'text-white shadow-lg shadow-orange-500/25' 
                        : 'text-gray-500 hover:bg-orange-50 hover:text-orange-600' }}">
                    
                    @if(request()->routeIs('staff.customers.*'))
                        <div class="absolute inset-0 bg-gradient-to-br from-orange-500 to-orange-600 z-0"></div>
                    @endif

                    <div class="relative z-10 flex items-center w-full">
                        <i class="fas fa-user w-6 text-center mr-3 text-lg {{ request()->routeIs('staff.customers.*') ? 'text-orange-100' : 'text-gray-400 group-hover:text-orange-500 transition-colors' }}"></i> 
                        <span>Customer Management</span>
                    </div>
                </a>

                {{-- Reporting --}}
                <a href="#" class="group flex items-center px-4 py-3.5 rounded-xl transition-all duration-300 font-bold text-sm text-gray-500 hover:bg-orange-50 hover:text-orange-600">
                    <div class="relative z-10 flex items-center w-full">
                        <i class="fas fa-chart-bar w-6 text-center mr-3 text-lg text-gray-400 group-hover:text-orange-500 transition-colors"></i> 
                        <span>Reporting & Analysis</span>
                    </div>
                </a>

                {{-- Loyalty --}}
                <a href="{{ route('staff.loyalty.index') }}" 
                   class="group flex items-center px-4 py-3.5 rounded-xl transition-all duration-300 font-bold text-sm relative overflow-hidden
                   {{ request()->routeIs('staff.loyalty.*') 
                        ? 'text-white shadow-lg shadow-orange-500/25' 
                        : 'text-gray-500 hover:bg-orange-50 hover:text-orange-600' }}">
                    
                    @if(request()->routeIs('staff.loyalty.*'))
                         <div class="absolute inset-0 bg-gradient-to-br from-orange-500 to-orange-600 z-0"></div>
                    @endif

                    <div class="relative z-10 flex items-center w-full">
                        <i class="fas fa-medal w-6 text-center mr-3 text-lg {{ request()->routeIs('staff.loyalty.*') ? 'text-orange-100' : 'text-gray-400 group-hover:text-orange-500 transition-colors' }}"></i> 
                        <span>Loyalty & Rewards</span>
                    </div>
                </a>

                {{-- Staff Management (Admin Only) --}}
                @if(Auth::guard('staff')->user()->role === 'admin')
                    <a href="{{ route('staff.management.index') }}" 
                       class="group flex items-center px-4 py-3.5 rounded-xl transition-all duration-300 font-bold text-sm relative overflow-hidden
                       {{ request()->routeIs('staff.management.*') 
                            ? 'text-white shadow-lg shadow-orange-500/25' 
                            : 'text-gray-500 hover:bg-orange-50 hover:text-orange-600' }}">
                        
                        @if(request()->routeIs('staff.management.*'))
                            <div class="absolute inset-0 bg-gradient-to-br from-orange-500 to-orange-600 z-0"></div>
                        @endif

                        <div class="relative z-10 flex items-center w-full">
                            <i class="fas fa-users-cog w-6 text-center mr-3 text-lg {{ request()->routeIs('staff.management.*') ? 'text-orange-100' : 'text-gray-400 group-hover:text-orange-500 transition-colors' }}"></i> 
                            <span>Staff Management</span>
                        </div>
                    </a>
                @endif
            </nav>
        </div>

        {{-- LOGOUT --}}
        <div class="p-6 border-t border-gray-50">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="flex items-center w-full px-4 py-3 rounded-xl text-red-500 font-bold text-sm hover:bg-red-50 hover:text-red-600 transition-all group">
                    <i class="fas fa-sign-out-alt w-6 text-center mr-3 text-lg opacity-50 group-hover:opacity-100 transition-opacity"></i> 
                    <span>Log Out</span>
                </button>
            </form>
        </div>
    </aside>
    
        <div class="flex-1 flex flex-col h-screen overflow-hidden md:ml-64">
            
            <header class="h-20 bg-gradient-to-r from-[#ea580c]/50 to-red-100 border-b border-gray-100 flex justify-between items-center px-8 sticky top-0 z-10">
                <h2 class="text-2xl font-bold text-gray-800">@yield('title', 'Staff Overview')</h2>
                
                <div class="flex items-center space-x-6">

                    {{-- STAFF NOTIFICATION BELL --}}
                    <div class="relative">
                        <button id="notif-bell" class="flex items-center text-gray-500 hover:text-orange-600 focus:outline-none relative p-2 transition-colors">
                            <i class="fas fa-bell text-xl"></i>
                            @php $unreadCount = Auth::guard('staff')->user()->unreadNotifications->count(); @endphp
                            @if($unreadCount > 0)
                                <span class="absolute top-1 right-1 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full border-2 border-white">
                                    {{ $unreadCount }}
                                </span>
                            @endif
                        </button>

                        {{-- DROPDOWN MENU --}}
                        <div id="notif-dropdown" class="absolute right-0 mt-3 w-80 bg-white border border-gray-100 rounded-2xl shadow-2xl hidden z-50 overflow-hidden transform origin-top-right transition-all">
                            <div class="p-4 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                                <h4 class="text-xs font-black uppercase text-gray-400 tracking-wider">Notifications</h4>
                                @if($unreadCount > 0)
                                    <span class="text-[10px] bg-orange-100 text-orange-600 px-2 py-0.5 rounded-full font-bold">{{ $unreadCount }} NEW</span>
                                @endif
                            </div>
                            
                            <div class="max-h-96 overflow-y-auto custom-scrollbar">
                                @forelse(Auth::guard('staff')->user()->unreadNotifications as $notification)
                                    <a href="{{ route('staff.bookings.show', $notification->data['booking_id'] ?? '#') }}" 
                                       class="block p-4 hover:bg-orange-50/50 transition border-b border-gray-50 group">
                                        <div class="flex gap-3">
                                            <div class="mt-1">
                                                <div class="w-2 h-2 bg-orange-500 rounded-full"></div>
                                            </div>
                                            <div>
                                                <p class="text-sm font-semibold text-gray-800 leading-tight group-hover:text-orange-700 transition-colors">
                                                    {{ $notification->data['message'] }}
                                                </p>
                                                <p class="text-[10px] text-gray-400 mt-1.5 flex items-center">
                                                    <i class="far fa-clock mr-1"></i> {{ $notification->created_at->diffForHumans() }}
                                                </p>
                                            </div>
                                        </div>
                                    </a>
                                @empty
                                    <div class="p-10 text-center">
                                        <div class="bg-gray-50 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3">
                                            <i class="fas fa-check text-gray-300"></i>
                                        </div>
                                        <p class="text-xs text-gray-400 font-medium">No unread notifications</p>
                                    </div>
                                @endforelse
                            </div>

                            {{-- MARK AS READ BUTTON --}}
                            @if($unreadCount > 0)
                                <div class="p-3 bg-gray-50 border-t border-gray-100 text-center">
                                    <form action="{{ route('notifications.markRead') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-[11px] text-orange-600 font-extrabold hover:text-orange-700 uppercase tracking-tighter">
                                            Clear All Notifications
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center space-x-3 border-l border-gray-100 pl-6">
                        <div class="text-right hidden md:block">
                            <p class="text-sm font-bold text-gray-900 leading-none">Hasta Admin</p>
                            <p class="text-xs text-gray-400 mt-1">Staff</p>
                        </div>
                        <div class="w-10 h-10 bg-orange-600 rounded-full flex items-center justify-center text-white font-bold text-lg shadow-md shadow-orange-500/20">
                            H
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-white p-8">
                @yield('content')
            </main>

        </div>
    </div>
<script>
    const bell = document.getElementById('notif-bell');
    const dropdown = document.getElementById('notif-dropdown');

    // Toggle dropdown on click
    bell.addEventListener('click', (e) => {
        e.stopPropagation();
        dropdown.classList.toggle('hidden');
        
        // Optional: Add a simple animation class if you want
        if (!dropdown.classList.contains('hidden')) {
            dropdown.classList.add('animate-in', 'fade-in', 'zoom-in-95', 'duration-100');
        }
    });

    // Close dropdown if clicking anywhere else on the document
    document.addEventListener('click', (e) => {
        if (!dropdown.contains(e.target) && !bell.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });

    // Prevent dropdown from closing when clicking inside the notification list
    dropdown.addEventListener('click', (e) => {
        e.stopPropagation();
    });
</script>
</body>
</html>