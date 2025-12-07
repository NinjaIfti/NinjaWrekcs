<!-- Navigation -->
<nav class="fixed w-full bg-black/80 backdrop-blur-xl shadow-lg z-50 border-b border-violet-500/20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-20">
            <!-- Logo with Glitch Effect -->
            <div class="flex items-center">
                <a href="/" class="text-2xl font-bold glitch-text" data-text="NinjaWrekcs">
                    NinjaWrekcs
                </a>
            </div>

            <!-- Navigation Links -->
            <div class="hidden md:flex items-center space-x-8">
                <a href="#products" class="text-gray-300 hover:text-violet-400 transition-colors font-medium relative group">
                    Shop
                    <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-violet-400 group-hover:w-full transition-all duration-300"></span>
                </a>
                <a href="#categories" class="text-gray-300 hover:text-violet-400 transition-colors font-medium relative group">
                    Categories
                    <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-violet-400 group-hover:w-full transition-all duration-300"></span>
                </a>
                <a href="#deals" class="text-gray-300 hover:text-violet-400 transition-colors font-medium relative group">
                    Deals
                    <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-violet-400 group-hover:w-full transition-all duration-300"></span>
                </a>
                <a href="#about" class="text-gray-300 hover:text-violet-400 transition-colors font-medium relative group">
                    About
                    <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-violet-400 group-hover:w-full transition-all duration-300"></span>
                </a>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center space-x-4">
                <button class="p-2 text-gray-300 hover:text-violet-400 transition-colors relative group">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <span class="absolute inset-0 bg-violet-500/20 rounded-lg scale-0 group-hover:scale-100 transition-transform duration-300"></span>
                </button>
                <button class="p-2 text-gray-300 hover:text-violet-400 transition-colors relative group">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span class="absolute -top-1 -right-1 bg-violet-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center glitch-pulse">3</span>
                </button>
                @auth
                    <a href="{{ url('/dashboard') }}" class="hidden md:block px-4 py-2 text-gray-300 hover:text-violet-400 font-medium transition-colors">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="hidden md:block px-4 py-2 text-gray-300 hover:text-violet-400 font-medium transition-colors">Login</a>
                    <a href="{{ route('register') }}" class="hidden md:block px-6 py-2 bg-gradient-to-r from-violet-600 to-purple-600 text-white rounded-lg font-medium hover:shadow-lg hover:shadow-violet-500/50 hover:scale-105 transition-all relative overflow-hidden group">
                        <span class="relative z-10">Sign Up</span>
                        <span class="absolute inset-0 bg-gradient-to-r from-purple-600 to-violet-600 opacity-0 group-hover:opacity-100 transition-opacity"></span>
                    </a>
                @endauth
            </div>
        </div>
    </div>
</nav>


