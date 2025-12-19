<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasta Staff Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
                     <div class="border-2 border-orange-600 px-2 py-1 rounded">
                        <span class="text-3xl font-black text-orange-600 tracking-tighter">HASTA</span>
                     </div>
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

                    <a href="#" class="flex items-center px-4 py-3 rounded-xl transition font-bold text-sm text-gray-500 hover:bg-orange-50 hover:text-orange-600">
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
                <button class="w-full bg-orange-50 text-orange-600 font-bold py-3 rounded-xl mb-6 hover:bg-orange-100 transition flex items-center justify-center border border-orange-100">
                    <i class="fas fa-clipboard-check mr-2"></i> Inspection Mode
                </button>
                
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
                    <div class="relative hidden md:block">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-sm"></i>
                        <input type="text" placeholder="Search anything..." class="bg-gray-50 rounded-full pl-10 pr-4 py-2.5 text-sm w-64 focus:outline-none focus:ring-2 focus:ring-orange-100 text-gray-600 transition">
                    </div>

                    <div class="relative cursor-pointer">
                        <i class="fas fa-bell text-gray-400 text-xl"></i>
                        <span class="absolute top-0 right-0 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-white"></span>
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

</body>
</html>