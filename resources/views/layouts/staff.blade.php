<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Hasta Staff Portal</title>

    <script src="https://cdn.tailwindcss.com"></script>
    
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
        
        [x-cloak] { display: none !important; }
        
        /* Transitions */
        .sidebar-transition { transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .content-transition { transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        
        /* Scrollbars */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .custom-scrollbar::-webkit-scrollbar { width: 5px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 5px; }
    </style>
</head>

<body class="bg-gray-50 text-gray-800 font-sans antialiased"
      x-cloak
      x-data="{ 
          sidebarOpen: JSON.parse(localStorage.getItem('sidebarOpen')) ?? true,
          animationsOn: false,
          toggleSidebar() {
              this.sidebarOpen = !this.sidebarOpen;
              localStorage.setItem('sidebarOpen', this.sidebarOpen);
          },
          init() {
              // Prevent flicker on load
              setTimeout(() => { this.animationsOn = true; }, 300);
          }
      }"
      x-init="init()"
>

    {{-- === SIDEBAR === --}}
    <aside 
        class="fixed inset-y-0 left-0 z-50 bg-orange-100/45 border-r border-gray-100 flex flex-col justify-between shadow-[4px_0_24px_rgba(0,0,0,0.02)] backdrop-blur-sm"
        :class="[
            sidebarOpen ? 'w-64' : 'w-20',
            animationsOn ? 'sidebar-transition' : '' 
        ]"
    >
        <div>
            {{-- LOGO AREA --}}
            <div class="h-24 flex items-center relative" :class="sidebarOpen ? 'px-8' : 'justify-center'">
                <img src="{{ asset('hasta.jpeg') }}" alt="Hasta Logo" class="h-10 w-auto object-contain transition-all duration-300" :class="sidebarOpen ? '' : 'scale-75'">
            </div>

            {{-- NAVIGATION --}}
            <nav class="px-3 space-y-1 mt-2 overflow-y-auto no-scrollbar max-h-[calc(100vh-180px)]">
                @php
                    $activeClass = "text-white shadow-lg shadow-orange-500/25";
                    $inactiveClass = "text-gray-500 hover:bg-orange-50 hover:text-orange-600";
                    $iconActive = "text-orange-100";
                    $iconInactive = "text-gray-400 group-hover:text-orange-500";
                @endphp

                {{-- Dashboard --}}
                <a href="{{ route('staff.dashboard') }}" 
                   class="group flex items-center px-4 py-3.5 rounded-xl font-bold text-sm relative overflow-hidden mb-1 transition-all duration-300 {{ request()->routeIs('staff.dashboard') ? $activeClass : $inactiveClass }}"
                   :class="sidebarOpen ? '' : 'justify-center'">
                    @if(request()->routeIs('staff.dashboard')) <div class="absolute inset-0 bg-gradient-to-br from-orange-500 to-orange-600 z-0"></div> @endif
                    <div class="relative z-10 flex items-center w-full" :class="sidebarOpen ? '' : 'justify-center'">
                        <i class="fas fa-th-large w-6 text-center text-lg {{ request()->routeIs('staff.dashboard') ? $iconActive : $iconInactive }}" :class="sidebarOpen ? 'mr-3' : ''"></i> 
                        <span x-show="sidebarOpen">Dashboard</span>
                    </div>
                    <div x-show="!sidebarOpen" class="absolute left-16 bg-slate-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 ml-1 shadow-lg transition-opacity duration-200">Dashboard</div>
                </a>

                {{-- Manage Bookings --}}
                <a href="{{ route('staff.bookings.index') }}" 
                   class="group flex items-center px-4 py-3.5 rounded-xl font-bold text-sm relative overflow-hidden mb-1 transition-all duration-300 {{ request()->routeIs('staff.bookings.*') ? $activeClass : $inactiveClass }}"
                   :class="sidebarOpen ? '' : 'justify-center'">
                    @if(request()->routeIs('staff.bookings.*')) <div class="absolute inset-0 bg-gradient-to-br from-orange-500 to-orange-600 z-0"></div> @endif
                    <div class="relative z-10 flex items-center w-full" :class="sidebarOpen ? '' : 'justify-center'">
                        <i class="fas fa-file-invoice w-6 text-center text-lg {{ request()->routeIs('staff.bookings.*') ? $iconActive : $iconInactive }}" :class="sidebarOpen ? 'mr-3' : ''"></i> 
                        <span x-show="sidebarOpen">Manage Bookings</span>
                    </div>
                    <div x-show="!sidebarOpen" class="absolute left-16 bg-slate-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 ml-1 shadow-lg transition-opacity duration-200">Bookings</div>
                </a>

                {{-- Deposit Management --}}
                <a href="{{ route('staff.finance.deposits') }}" 
                   class="group flex items-center px-4 py-3.5 rounded-xl font-bold text-sm relative overflow-hidden mb-1 transition-all duration-300 {{ request()->routeIs('staff.finance.*') ? $activeClass : $inactiveClass }}"
                   :class="sidebarOpen ? '' : 'justify-center'">
                    @if(request()->routeIs('staff.finance.*')) <div class="absolute inset-0 bg-gradient-to-br from-orange-500 to-orange-600 z-0"></div> @endif
                    <div class="relative z-10 flex items-center w-full" :class="sidebarOpen ? '' : 'justify-center'">
                        <i class="fas fa-wallet w-6 text-center text-lg {{ request()->routeIs('staff.finance.*') ? $iconActive : $iconInactive }}" :class="sidebarOpen ? 'mr-3' : ''"></i> 
                        <span x-show="sidebarOpen">Deposit Management</span>
                    </div>
                    <div x-show="!sidebarOpen" class="absolute left-16 bg-slate-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 ml-1 shadow-lg transition-opacity duration-200">Deposits</div>
                </a>

                {{-- Fleet Management --}}
                <a href="{{ route('staff.fleet.index') }}" 
                   class="group flex items-center px-4 py-3.5 rounded-xl font-bold text-sm relative overflow-hidden mb-1 transition-all duration-300 {{ request()->routeIs('staff.fleet.*') ? $activeClass : $inactiveClass }}"
                   :class="sidebarOpen ? '' : 'justify-center'">
                    @if(request()->routeIs('staff.fleet.*')) <div class="absolute inset-0 bg-gradient-to-br from-orange-500 to-orange-600 z-0"></div> @endif
                    <div class="relative z-10 flex items-center w-full" :class="sidebarOpen ? '' : 'justify-center'">
                        <i class="fas fa-car w-6 text-center text-lg {{ request()->routeIs('staff.fleet.*') ? $iconActive : $iconInactive }}" :class="sidebarOpen ? 'mr-3' : ''"></i> 
                        <span x-show="sidebarOpen">Fleet Management</span>
                    </div>
                    <div x-show="!sidebarOpen" class="absolute left-16 bg-slate-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 ml-1 shadow-lg transition-opacity duration-200">Fleet</div>
                </a>

                {{-- Customer Management --}}
                <a href="{{ route('staff.customers.index') }}" 
                   class="group flex items-center px-4 py-3.5 rounded-xl font-bold text-sm relative overflow-hidden mb-1 transition-all duration-300 {{ request()->routeIs('staff.customers.*') ? $activeClass : $inactiveClass }}"
                   :class="sidebarOpen ? '' : 'justify-center'">
                    @if(request()->routeIs('staff.customers.*')) <div class="absolute inset-0 bg-gradient-to-br from-orange-500 to-orange-600 z-0"></div> @endif
                    <div class="relative z-10 flex items-center w-full" :class="sidebarOpen ? '' : 'justify-center'">
                        <i class="fas fa-user w-6 text-center text-lg {{ request()->routeIs('staff.customers.*') ? $iconActive : $iconInactive }}" :class="sidebarOpen ? 'mr-3' : ''"></i> 
                        <span x-show="sidebarOpen">Customer Management</span>
                    </div>
                    <div x-show="!sidebarOpen" class="absolute left-16 bg-slate-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 ml-1 shadow-lg transition-opacity duration-200">Customers</div>
                </a>

                {{-- Reporting --}}
                <a href="{{ route('staff.reports.index') }}" 
                   class="group flex items-center px-4 py-3.5 rounded-xl font-bold text-sm relative overflow-hidden mb-1 transition-all duration-300 {{ request()->routeIs('staff.reports.*') ? $activeClass : $inactiveClass }}"
                   :class="sidebarOpen ? '' : 'justify-center'">
                    @if(request()->routeIs('staff.reports.*')) <div class="absolute inset-0 bg-gradient-to-br from-orange-500 to-orange-600 z-0"></div> @endif
                    <div class="relative z-10 flex items-center w-full" :class="sidebarOpen ? '' : 'justify-center'">
                        <i class="fas fa-chart-bar w-6 text-center text-lg {{ request()->routeIs('staff.reports.*') ? $iconActive : $iconInactive }}" :class="sidebarOpen ? 'mr-3' : ''"></i> 
                        <span x-show="sidebarOpen">Reporting & Analysis</span>
                    </div>
                    <div x-show="!sidebarOpen" class="absolute left-16 bg-slate-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 ml-1 shadow-lg transition-opacity duration-200">Reports</div>
                </a>

                {{-- Loyalty --}}
                <a href="{{ route('staff.loyalty.index') }}" 
                   class="group flex items-center px-4 py-3.5 rounded-xl font-bold text-sm relative overflow-hidden mb-1 transition-all duration-300 {{ request()->routeIs('staff.loyalty.*') ? $activeClass : $inactiveClass }}"
                   :class="sidebarOpen ? '' : 'justify-center'">
                    @if(request()->routeIs('staff.loyalty.*')) <div class="absolute inset-0 bg-gradient-to-br from-orange-500 to-orange-600 z-0"></div> @endif
                    <div class="relative z-10 flex items-center w-full" :class="sidebarOpen ? '' : 'justify-center'">
                        <i class="fas fa-medal w-6 text-center text-lg {{ request()->routeIs('staff.loyalty.*') ? $iconActive : $iconInactive }}" :class="sidebarOpen ? 'mr-3' : ''"></i> 
                        <span x-show="sidebarOpen">Loyalty & Rewards</span>
                    </div>
                    <div x-show="!sidebarOpen" class="absolute left-16 bg-slate-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 ml-1 shadow-lg transition-opacity duration-200">Loyalty</div>
                </a>

                {{-- Staff Management (Admin Only) --}}
                @if(Auth::guard('staff')->user()->role === 'admin')
                    <a href="{{ route('staff.management.index') }}" 
                       class="group flex items-center px-4 py-3.5 rounded-xl font-bold text-sm relative overflow-hidden mb-1 transition-all duration-300 {{ request()->routeIs('staff.management.*') ? $activeClass : $inactiveClass }}"
                       :class="sidebarOpen ? '' : 'justify-center'">
                        @if(request()->routeIs('staff.management.*')) <div class="absolute inset-0 bg-gradient-to-br from-orange-500 to-orange-600 z-0"></div> @endif
                        <div class="relative z-10 flex items-center w-full" :class="sidebarOpen ? '' : 'justify-center'">
                            <i class="fas fa-users-cog w-6 text-center text-lg {{ request()->routeIs('staff.management.*') ? $iconActive : $iconInactive }}" :class="sidebarOpen ? 'mr-3' : ''"></i> 
                            <span x-show="sidebarOpen">Staff Management</span>
                        </div>
                        <div x-show="!sidebarOpen" class="absolute left-16 bg-slate-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 pointer-events-none z-50 ml-1 shadow-lg transition-opacity duration-200">Staff</div>
                    </a>
                @endif
            </nav>
        </div>

        {{-- LOGOUT --}}
        <div class="p-6 border-t border-gray-50">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" 
                        class="flex items-center w-full px-4 py-3 rounded-xl text-red-500 font-bold text-sm hover:bg-red-50 hover:text-red-600 transition-all group"
                        :class="sidebarOpen ? '' : 'justify-center'">
                    <i class="fas fa-sign-out-alt w-6 text-center text-lg opacity-50 group-hover:opacity-100 transition-opacity" :class="sidebarOpen ? 'mr-3' : ''"></i> 
                    <span x-show="sidebarOpen">Log Out</span>
                </button>
            </form>
        </div>
    </aside>

    {{-- === MAIN CONTENT WRAPPER === --}}
    <div class="flex-1 flex flex-col h-screen overflow-hidden"
         :class="[
            sidebarOpen ? 'ml-64' : 'ml-20',
            animationsOn ? 'content-transition' : ''
         ]"
    >
        
        {{-- TOP HEADER --}}
        <header class="h-20 bg-slate-100 border-b border-gray-100 flex justify-between items-center px-8 sticky top-0 z-40">
            
            <div class="flex items-center gap-4">
                {{-- HAMBURGER TOGGLE --}}
                <button @click="toggleSidebar()" class="text-gray-400 hover:text-orange-600 transition-colors p-1 focus:outline-none rounded-lg hover:bg-gray-200/50">
                    <i class="fas fa-bars text-xl"></i>
                </button>

                <h2 class="text-2xl font-medium text-gray-800">@yield('title', 'Staff Overview')</h2>
            </div>
            
            <div class="flex items-center space-x-6">

                {{-- NOTIFICATION BELL --}}
                <div class="relative" x-data="{ notifOpen: false }">
                    <button @click="notifOpen = !notifOpen" @click.away="notifOpen = false" class="flex items-center text-gray-500 hover:text-orange-600 focus:outline-none relative p-2 transition-colors">
                        <i class="fas fa-bell text-xl"></i>
                        @php $unreadCount = Auth::guard('staff')->user()->unreadNotifications->count(); @endphp
                        @if($unreadCount > 0)
                            <span class="absolute top-1 right-1 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full border-2 border-white">
                                {{ $unreadCount }}
                            </span>
                        @endif
                    </button>

                    <div x-show="notifOpen" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="absolute right-0 mt-3 w-80 bg-white border border-gray-100 rounded-2xl shadow-2xl z-50 overflow-hidden" 
                         style="display: none;">
                        
                        <div class="p-4 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                            <h4 class="text-xs font-black uppercase text-gray-400 tracking-wider">Notifications</h4>
                            @if($unreadCount > 0)
                                <span class="text-[10px] bg-orange-100 text-orange-600 px-2 py-0.5 rounded-full font-bold">{{ $unreadCount }} NEW</span>
                            @endif
                        </div>
                        
                        <div class="max-h-80 overflow-y-auto custom-scrollbar">
                            @forelse(Auth::guard('staff')->user()->unreadNotifications as $notification)
                                <a href="{{ route('staff.bookings.show', $notification->data['booking_id'] ?? '#') }}" class="block p-4 hover:bg-orange-50/50 transition border-b border-gray-50 group">
                                    <div class="flex gap-3">
                                        <div class="mt-1 shrink-0"><div class="w-2 h-2 bg-orange-500 rounded-full"></div></div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-800 leading-tight group-hover:text-orange-700">{{ $notification->data['message'] }}</p>
                                            <p class="text-[10px] text-gray-400 mt-1.5 flex items-center"><i class="far fa-clock mr-1"></i> {{ $notification->created_at->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <div class="p-10 text-center"><p class="text-xs text-gray-400 font-medium">No unread notifications</p></div>
                            @endforelse
                        </div>

                        @if($unreadCount > 0)
                            <div class="p-3 bg-gray-50 border-t border-gray-100 text-center">
                                <form action="{{ route('notifications.markRead') }}" method="POST">@csrf <button type="submit" class="text-[10px] text-orange-600 font-extrabold uppercase">Clear All Notifications</button></form>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- USER PROFILE --}}
                <a href="{{ route('staff.profile.edit') }}" class="flex items-center space-x-3 border-l border-gray-100 pl-6 hover:bg-gray-50 transition-colors duration-200 rounded-l-lg py-1 pr-2">
                    <div class="text-right hidden md:block">
                        <p class="text-sm font-bold text-gray-900 leading-none">{{ Auth::guard('staff')->user()->name }}</p>
                        <p class="text-xs text-gray-400 mt-1 uppercase">{{ Auth::guard('staff')->user()->role }}</p>
                    </div>
                    <div class="w-10 h-10 bg-orange-600 rounded-full flex items-center justify-center text-white font-bold text-lg shadow-md shadow-orange-500/20">
                        {{ substr(Auth::guard('staff')->user()->name, 0, 1) }}
                    </div>
                </a>
            </div>
        </header>

        {{-- PAGE CONTENT --}}
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-white p-4">
            
            {{-- GLOBAL BACK BUTTON (Pill Style) --}}
            @if(!request()->routeIs('staff.dashboard'))
                <div class="max-w-auto mx-auto mb-4 px-2 pt-1">
                    <a href="{{ url()->previous() }}" 
                       class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full border border-orange-200 text-orange-600 hover:bg-orange-50 hover:border-orange-300 transition-all group">
                        <i class="fas fa-chevron-left text-[10px] transform group-hover:-translate-x-0.5 transition-transform"></i>
                        <span class="text-xs font-bold uppercase tracking-wide">Back</span>
                    </a>
                </div>
            @endif

            @yield('content')
        </main>

    </div>
<div id="staff-loader" class="fixed inset-0 z-[99999] hidden bg-slate-900/80 backdrop-blur-sm flex items-center justify-center transition-opacity duration-300">
    <div class="bg-white rounded-2xl shadow-2xl p-8 flex flex-col items-center max-w-sm w-full mx-4">
        <div class="w-12 h-12 border-4 border-indigo-100 border-t-indigo-600 rounded-full animate-spin mb-4"></div>
        
        <h3 class="text-gray-900 font-bold text-lg">System Processing</h3>
        <p class="text-gray-500 text-xs text-center mt-2 leading-relaxed">
            Please wait while we process your request. This may take a few seconds.
            <br><span class="text-orange-500 text-xs">Please do not close this window.</span>
        </p>

        <div class="w-full bg-gray-100 rounded-full h-1.5 mt-6 overflow-hidden">
            <div class="bg-indigo-600 h-1.5 rounded-full animate-[loading_2s_ease-in-out_infinite] w-1/2"></div>
        </div>
    </div>
</div>

<style>
    @keyframes loading {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(200%); }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const loader = document.getElementById('staff-loader');

        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                // Only trigger for POST requests (Actions)
                if (this.method.toUpperCase() === 'POST' && this.checkValidity()) {
                    loader.classList.remove('hidden');
                }
            });
        });
        
        // Reset on back navigation
        window.addEventListener('pageshow', (event) => {
            if (event.persisted) loader.classList.add('hidden');
        });
    });
</script>

</body>
</html>