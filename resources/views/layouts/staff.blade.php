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
        
        <aside class="w-64 bg-white border-r border-gray-100 hidden md:flex flex-col justify-between fixed h-full z-20">
            <div>
                <div class="h-20 flex items-center px-8">
                     <img src="{{ asset('hasta.jpeg') }}" alt="Hasta Logo" class="h-12 w-auto object-contain">
                </div>

                <nav class="p-4 space-y-2 mt-2">
                    <a href="{{ route('staff.dashboard') }}" 
                       class="flex items-center px-4 py-3 rounded-xl transition font-bold text-sm
                       {{ request()->routeIs('staff.dashboard') ? 'bg-orange-600 text-white shadow-lg shadow-orange-500/30' : 'text-gray-500 hover:bg-orange-50 hover:text-orange-600' }}">
                        <i class="fas fa-th-large w-5 mr-3"></i> Dashboard
                    </a>

                    <a href="{{ route('staff.bookings.index') }}" 
                       class="flex items-center px-4 py-3 rounded-xl transition font-bold text-sm
                       {{ request()->routeIs('staff.bookings.index') ? 'bg-orange-600 text-white shadow-lg shadow-orange-500/30' : 'text-gray-500 hover:bg-orange-50 hover:text-orange-600' }}">
                        <i class="fas fa-file-invoice w-5 mr-3"></i> Manage Bookings
                    </a>

                    <a href="{{ route('staff.fleet.index') }}" 
                        class="flex items-center px-4 py-3 rounded-xl transition font-bold text-sm
                       {{ request()->routeIs('staff.fleet.index') ? 'bg-orange-600 text-white shadow-lg shadow-orange-500/30' : 'text-gray-500 hover:bg-orange-50 hover:text-orange-600' }}">
                        <i class="fas fa-car w-5 mr-3"></i> Fleet Management
                    </a>

                    <a href="#" class="flex items-center px-4 py-3 rounded-xl transition font-bold text-sm text-gray-500 hover:bg-orange-50 hover:text-orange-600">
                        <i class="fas fa-user w-5 mr-3"></i> Customer Management
                    </a>

                    <a href="#" class="flex items-center px-4 py-3 rounded-xl transition font-bold text-sm text-gray-500 hover:bg-orange-50 hover:text-orange-600">
                        <i class="fas fa-chart-bar w-5 mr-3"></i> Reporting & Analysis
                    </a>

                    <a href="#" class="flex items-center px-4 py-3 rounded-xl transition font-bold text-sm text-gray-500 hover:bg-orange-50 hover:text-orange-600">
                        <i class="fas fa-medal w-5 mr-3"></i> Loyalty & Rewards
                    </a>
                </nav>
            </div>

            <div class="p-4 pb-8">
                
                
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="flex items-center text-red-500 font-bold text-sm hover:text-red-700 px-4">
                        <i class="fas fa-sign-out-alt w-5 mr-3"></i> Sign Out
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex-1 flex flex-col h-screen overflow-hidden md:ml-64">
            
            <header class="h-20 bg-white border-b border-gray-100 flex justify-between items-center px-8 sticky top-0 z-10">
                <h2 class="text-2xl font-bold text-gray-800">@yield('title', 'Overview')</h2>
                
                <div class="flex items-center space-x-6">

                    {{-- Example notification bell/dropdown --}}
                    <div class="relative">
                        <button id="notif-bell" class="flex items-center text-gray-500 hover:text-gray-700 focus:outline-none relative p-2">
                            <i class="fas fa-bell text-xl"></i>
                            @php $unreadCount = Auth::guard('staff')->user()->unreadNotifications->count(); @endphp
                            @if($unreadCount > 0)
                                <span class="absolute top-1 right-1 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full border-2 border-white">
                                    {{ $unreadCount }}
                                </span>
                            @endif
                        </button>

                        <div id="notif-dropdown" class="absolute right-0 mt-2 w-80 bg-white border border-gray-100 rounded-xl shadow-xl hidden z-50 overflow-hidden">
                            <div class="p-4 border-b border-gray-50 flex justify-between items-center">
                                <h4 class="text-xs font-black uppercase text-gray-400 tracking-wider">Notifications</h4>
                                @if($unreadCount > 0)
                                    <span class="text-[10px] bg-orange-100 text-orange-600 px-2 py-0.5 rounded-full font-bold">{{ $unreadCount }} NEW</span>
                                @endif
                            </div>
                            
                            <div class="max-h-80 overflow-y-auto">
                                @forelse(Auth::guard('staff')->user()->unreadNotifications as $notification)
                                    <a href="{{ route('staff.bookings.show', $notification->data['booking_id']) }}" class="block p-4 hover:bg-gray-50 transition border-b border-gray-50 group">
                                        <p class="text-sm font-bold text-gray-800 group-hover:text-blue-600">{{ $notification->data['message'] }}</p>
                                        <p class="text-[10px] text-gray-400 mt-1 flex items-center">
                                            <i class="far fa-clock mr-1"></i> {{ $notification->created_at->diffForHumans() }}
                                        </p>
                                    </a>
                                @empty
                                    <div class="p-8 text-center">
                                        <div class="bg-gray-50 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3">
                                            <i class="fas fa-check text-gray-300"></i>
                                        </div>
                                        <p class="text-xs text-gray-400">All caught up!</p>
                                    </div>
                                @endforelse
                            </div>
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

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-8">
                @yield('content')
            </main>

        </div>
    </div>
<script>
    // Toggle dropdown on click
    const bell = document.getElementById('notif-bell');
    const dropdown = document.getElementById('notif-dropdown');

    bell.addEventListener('click', (e) => {
        e.stopPropagation();
        dropdown.classList.toggle('hidden');
    });

    // Close dropdown if clicking anywhere else
    window.addEventListener('click', () => {
        dropdown.classList.add('hidden');
    });
</script>
</body>
</html>