<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - HASTA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-900">

    <div class="bg-[#d32f2f] h-16 w-full fixed top-0 z-50 flex items-center px-8 justify-between shadow-md">
        <h1 class="text-white text-2xl font-black tracking-wide">HASTA</h1>
        <div class="text-white text-sm font-medium space-x-6">
            <a href="#" class="hover:text-gray-200">Home</a>
            <a href="#" class="hover:text-gray-200">About Us</a>
            <a href="#" class="hover:text-gray-200">FAQ</a>
            <a href="#" class="hover:text-gray-200">Contact Us</a>
        </div>
        <div class="flex items-center space-x-4">
            <i class="fas fa-bell text-white"></i>
            <i class="fas fa-user-circle text-white text-xl"></i>
        </div>
    </div>

    <div class="relative min-h-screen flex items-center justify-center bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1449965408869-eaa3f722e40d?q=80&w=1470&auto=format&fit=crop');">
        
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>

        <div class="relative z-10 w-full max-w-md p-6">
            
            <div class="bg-white w-32 mx-auto py-2 rounded shadow-lg text-center mb-6">
                <span class="text-orange-600 text-2xl font-black tracking-widest">HASTA</span>
            </div>

            <h2 class="text-center text-white text-2xl font-bold mb-6">Welcome back!</h2>

            <form action="{{ route('login') }}" method="POST">
                @csrf
                
                <input type="hidden" name="login_type" id="login_type" value="customer">

                <div class="flex justify-center mb-8">
                    <div class="bg-white/20 p-1 rounded-full flex relative">
                        <button type="button" onclick="setRole('customer')" id="btn-customer" class="px-6 py-2 rounded-full text-sm font-bold transition-all duration-300 bg-orange-500 text-white shadow-md">
                            Customer
                        </button>
                        <button type="button" onclick="setRole('staff')" id="btn-staff" class="px-6 py-2 rounded-full text-sm font-bold transition-all duration-300 text-white hover:bg-white/10">
                            Staff
                        </button>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="relative">
                        <input type="email" name="email" required placeholder="Email Address" 
                            class="w-full bg-transparent border border-gray-400 rounded-lg px-4 py-3 text-white placeholder-gray-300 focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 transition">
                    </div>
                    
                    <div class="relative">
                        <input type="password" name="password" required placeholder="Enter password" 
                            class="w-full bg-transparent border border-gray-400 rounded-lg px-4 py-3 text-white placeholder-gray-300 focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 transition">
                    </div>

                    <div class="text-right">
                        <a href="{{ route('password.request') }}" class="text-xs text-gray-300 hover:text-white">Forgot password?</a>                    </div>
                </div>

                <button type="submit" class="w-full mt-8 bg-orange-600 hover:bg-orange-700 text-white font-bold py-3 rounded-lg shadow-lg transition transform hover:scale-[1.02]">
                    Log in
                </button>
                
                <div class="text-center mt-6" id="register-section">
                    <p class="text-gray-300 text-sm">Don't have an account? <a href="{{ route('register') }}" class="text-orange-400 font-bold hover:underline">Register</a></p>
                </div>
            </form>
        </div>
    </div>

    <script>
        function setRole(role) {
            // Update Hidden Input
            document.getElementById('login_type').value = role;

            // Visual Updates
            const btnCustomer = document.getElementById('btn-customer');
            const btnStaff = document.getElementById('btn-staff');
            
            // Get the register section
            const registerSection = document.getElementById('register-section');

            if(role === 'customer') {
                btnCustomer.classList.add('bg-orange-500', 'text-white', 'shadow-md');
                btnCustomer.classList.remove('text-gray-300', 'hover:bg-white/10');
                
                btnStaff.classList.remove('bg-orange-500', 'text-white', 'shadow-md');
                btnStaff.classList.add('text-white', 'hover:bg-white/10');

                // SHOW registration link
                registerSection.style.display = 'block';

            } else {
                btnStaff.classList.add('bg-orange-500', 'text-white', 'shadow-md');
                btnStaff.classList.remove('text-gray-300', 'hover:bg-white/10');

                btnCustomer.classList.remove('bg-orange-500', 'text-white', 'shadow-md');
                btnCustomer.classList.add('text-white', 'hover:bg-white/10');
                
                // HIDE registration link
                registerSection.style.display = 'none';
            }
        }
    </script>
</body>
</html>