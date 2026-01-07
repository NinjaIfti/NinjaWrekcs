<!-- Mobile Menu Overlay -->
<div id="mobileMenu" class="fixed inset-0 bg-black/95 backdrop-blur-xl z-[60] transform translate-x-full transition-transform duration-300">
    <div class="flex flex-col h-full">
        <!-- Header -->
        <div class="flex justify-between items-center p-4 border-b border-violet-500/20 bg-black/50">
            <div class="flex items-center space-x-2">
                <img src="{{ asset('img/fav.png') }}" alt="NinjaWrecks" class="h-10 w-auto">
                <span class="text-xl font-bold glitch-text" data-text="NinjaWrecks">NinjaWrecks</span>
            </div>
            <button id="mobileMenuClose" class="p-2 text-gray-300 hover:text-violet-400 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Menu Items -->
        <nav class="flex-1 overflow-y-auto p-4 space-y-4">
            <a href="{{ route('shop.index') }}" class="block px-4 py-3 text-gray-300 hover:bg-violet-500/10 hover:text-violet-400 rounded-lg transition-colors">
                Shop
            </a>
            <a href="#categories" class="block px-4 py-3 text-gray-300 hover:bg-violet-500/10 hover:text-violet-400 rounded-lg transition-colors">
                Categories
            </a>
            <a href="#deals" class="block px-4 py-3 text-gray-300 hover:bg-violet-500/10 hover:text-violet-400 rounded-lg transition-colors">
                Deals
            </a>
            <a href="{{ route('about') }}" class="block px-4 py-3 text-gray-300 hover:bg-violet-500/10 hover:text-violet-400 rounded-lg transition-colors">
                About
            </a>
        </nav>

        <!-- Action Buttons -->
        <div class="p-4 border-t border-violet-500/20 space-y-3 bg-black/50">
            @auth
                <!-- Profile -->
                <a href="{{ route('profile.index') }}" class="flex items-center px-4 py-3 bg-violet-500/10 border border-violet-500/30 rounded-lg text-gray-300 hover:text-violet-400 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span>Profile</span>
                </a>
                
                <!-- Logout -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center px-4 py-3 bg-red-500/10 border border-red-500/30 rounded-lg text-gray-300 hover:text-red-400 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span>Logout</span>
                    </button>
                </form>
            @else
                <!-- Login -->
                <a href="{{ route('login') }}" class="block px-4 py-3 bg-violet-500/10 border border-violet-500/30 rounded-lg text-gray-300 hover:text-violet-400 transition-colors text-center">
                    Login
                </a>
                <!-- Sign Up -->
                <a href="{{ route('register') }}" class="block px-4 py-3 bg-gradient-to-r from-violet-600 to-purple-600 text-white rounded-lg font-medium hover:shadow-lg hover:shadow-violet-500/50 transition-all text-center">
                    Sign Up
                </a>
            @endauth
        </div>
    </div>
</div>

<script>
    // Mobile Menu Toggle
    document.addEventListener('DOMContentLoaded', function() {
        const menuButton = document.getElementById('mobileMenuButton');
        const menuClose = document.getElementById('mobileMenuClose');
        const mobileMenu = document.getElementById('mobileMenu');

        if (menuButton && menuClose && mobileMenu) {
            menuButton.addEventListener('click', function() {
                mobileMenu.classList.remove('translate-x-full');
                document.body.style.overflow = 'hidden';
            });

            menuClose.addEventListener('click', function() {
                mobileMenu.classList.add('translate-x-full');
                document.body.style.overflow = '';
            });

            // Close menu when clicking on a link
            mobileMenu.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', function() {
                    mobileMenu.classList.add('translate-x-full');
                    document.body.style.overflow = '';
                });
            });

            // Close menu on escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && !mobileMenu.classList.contains('translate-x-full')) {
                    mobileMenu.classList.add('translate-x-full');
                    document.body.style.overflow = '';
                }
            });
        }
    });
</script>

<div id="mobileMenu" class="fixed inset-0 bg-black/95 backdrop-blur-xl z-[60] transform translate-x-full transition-transform duration-300">
    <div class="flex flex-col h-full">
        <!-- Header -->
        <div class="flex justify-between items-center p-4 border-b border-violet-500/20 bg-black/50">
            <div class="flex items-center space-x-2">
                <img src="{{ asset('img/fav.png') }}" alt="NinjaWrecks" class="h-10 w-auto">
                <span class="text-xl font-bold glitch-text" data-text="NinjaWrecks">NinjaWrecks</span>
            </div>
            <button id="mobileMenuClose" class="p-2 text-gray-300 hover:text-violet-400 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Menu Items -->
        <nav class="flex-1 overflow-y-auto p-4 space-y-4">
            <a href="{{ route('shop.index') }}" class="block px-4 py-3 text-gray-300 hover:bg-violet-500/10 hover:text-violet-400 rounded-lg transition-colors">
                Shop
            </a>
            <a href="#categories" class="block px-4 py-3 text-gray-300 hover:bg-violet-500/10 hover:text-violet-400 rounded-lg transition-colors">
                Categories
            </a>
            <a href="#deals" class="block px-4 py-3 text-gray-300 hover:bg-violet-500/10 hover:text-violet-400 rounded-lg transition-colors">
                Deals
            </a>
            <a href="{{ route('about') }}" class="block px-4 py-3 text-gray-300 hover:bg-violet-500/10 hover:text-violet-400 rounded-lg transition-colors">
                About
            </a>
        </nav>

        <!-- Action Buttons -->
        <div class="p-4 border-t border-violet-500/20 space-y-3 bg-black/50">
            @auth
                <!-- Profile -->
                <a href="{{ route('profile.index') }}" class="flex items-center px-4 py-3 bg-violet-500/10 border border-violet-500/30 rounded-lg text-gray-300 hover:text-violet-400 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span>Profile</span>
                </a>
                
                <!-- Logout -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center px-4 py-3 bg-red-500/10 border border-red-500/30 rounded-lg text-gray-300 hover:text-red-400 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span>Logout</span>
                    </button>
                </form>
            @else
                <!-- Login -->
                <a href="{{ route('login') }}" class="block px-4 py-3 bg-violet-500/10 border border-violet-500/30 rounded-lg text-gray-300 hover:text-violet-400 transition-colors text-center">
                    Login
                </a>
                <!-- Sign Up -->
                <a href="{{ route('register') }}" class="block px-4 py-3 bg-gradient-to-r from-violet-600 to-purple-600 text-white rounded-lg font-medium hover:shadow-lg hover:shadow-violet-500/50 transition-all text-center">
                    Sign Up
                </a>
            @endauth
        </div>
    </div>
</div>

<script>
    // Mobile Menu Toggle
    document.addEventListener('DOMContentLoaded', function() {
        const menuButton = document.getElementById('mobileMenuButton');
        const menuClose = document.getElementById('mobileMenuClose');
        const mobileMenu = document.getElementById('mobileMenu');

        if (menuButton && menuClose && mobileMenu) {
            menuButton.addEventListener('click', function() {
                mobileMenu.classList.remove('translate-x-full');
                document.body.style.overflow = 'hidden';
            });

            menuClose.addEventListener('click', function() {
                mobileMenu.classList.add('translate-x-full');
                document.body.style.overflow = '';
            });

            // Close menu when clicking on a link
            mobileMenu.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', function() {
                    mobileMenu.classList.add('translate-x-full');
                    document.body.style.overflow = '';
                });
            });

            // Close menu on escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && !mobileMenu.classList.contains('translate-x-full')) {
                    mobileMenu.classList.add('translate-x-full');
                    document.body.style.overflow = '';
                }
            });
        }
    });
</script>


