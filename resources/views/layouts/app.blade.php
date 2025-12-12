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
        .form-input-transparent {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.6);
            color: white;
        }
        .form-input-transparent::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }
    </style>
</head>
<body class="bg-gray-50 flex flex-col min-h-screen">

    <nav class="bg-red-600 text-white p-4 shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <a href="{{ route('home') }}" class="text-2xl font-bold tracking-wider">HASTA</a>
            
            <div class="hidden md:flex space-x-6 text-sm font-medium">
                <a href="{{ route('home') }}" class="hover:text-gray-200">Home</a>
                <a href="#" class="hover:text-gray-200">About Us</a>
                <a href="#" class="hover:text-gray-200">FAQ</a>
                <a href="#" class="hover:text-gray-200">Contact Us</a>
            </div>

            <div class="flex items-center space-x-4">
                <i class="fas fa-bell text-xl cursor-pointer"></i>
                @auth
                    <a href="{{ route('profile.edit') }}">
                        <i class="fas fa-user-circle text-2xl cursor-pointer"></i>
                    </a>
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-xs ml-2 hover:underline">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}">
                        <i class="far fa-user-circle text-2xl cursor-pointer"></i>
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    <main class="flex-grow">
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative text-center" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif
        
        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative text-center">
                <ul>
                    @foreach ($errors->all() as $error)
                         <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="bg-orange-100 text-gray-800 p-8 text-xs">
        <div class="container mx-auto grid grid-cols-1 md:grid-cols-4 gap-8">
            <div>
                <h3 class="font-bold text-lg text-orange-600 mb-2">HASTA</h3>
                <p>Hasta Travel & Tours Sdn. Bhd.</p>
                <p>SSM: 1234567 / 20240100987</p>
            </div>
            <div>
                 <h4 class="font-bold mb-2">Company</h4>
                 <ul class="space-y-1">
                     <li>About Us</li>
                     <li>Testimonials</li>
                 </ul>
            </div>
             <div>
                 <h4 class="font-bold mb-2">Support</h4>
                 <ul class="space-y-1">
                     <li>Account</li>
                     <li>FAQ</li>
                 </ul>
            </div>
            <div>
                <h4 class="font-bold mb-2">Need any help?</h4>
                <p class="text-orange-600 font-bold">+60 11-1090 0700</p>
            </div>
        </div>
    </footer>
</body>
</html>