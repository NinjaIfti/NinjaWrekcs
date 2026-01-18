<!-- Mobile Top Brand -->
<div class="md:hidden fixed top-0 left-0 right-0 z-50 bg-black/95 backdrop-blur-xl border-b border-violet-500/40 shadow-lg">
    <div class="flex items-center justify-center px-4 py-3">
        <a href="/" class="flex items-center space-x-3">
            <img src="{{ asset('img/fav.png') }}" alt="NinjaWrecks" class="h-10 w-10 rounded-lg border border-violet-500/50">
            <span class="text-xl font-bold glitch-text-large" data-text="NinjaWrecks">NinjaWrecks</span>
        </a>
    </div>
</div>

<!-- Mobile Bottom Navigation -->
<div class="md:hidden fixed bottom-0 left-0 right-0 z-50 bg-black backdrop-blur-xl border-t-2 border-violet-500/50 shadow-2xl overflow-hidden">
    <div class="flex items-center justify-evenly py-3 bg-black/95 max-w-full">
        <!-- Home -->
        @php
            $activeHome = request()->is('/');
            $activeShop = request()->routeIs('shop.*');
            $activeCart = request()->routeIs('cart.*');
            $activeProfile = request()->routeIs('profile.*');
            $activeLogin = request()->routeIs('login');
        @endphp

        <a href="/" class="flex flex-col items-center justify-center flex-1 py-2 transition-all {{ $activeHome ? 'text-violet-300 glitch-pulse' : 'text-gray-300 hover:text-violet-400' }}">
            <svg class="w-6 h-6 mb-1 {{ $activeHome ? 'text-violet-300 glitch-pulse' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span class="text-xs font-medium">Home</span>
        </a>

        <!-- Shop -->
        <a href="{{ route('shop.index') }}" class="flex flex-col items-center justify-center flex-1 py-2 transition-all {{ $activeShop ? 'text-violet-300 glitch-pulse' : 'text-gray-300 hover:text-violet-400' }}">
            <svg class="w-6 h-6 mb-1 {{ $activeShop ? 'text-violet-300 glitch-pulse' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
            </svg>
            <span class="text-xs font-medium">Shop</span>
        </a>

        <!-- Cart -->
        <a href="{{ route('cart.index') }}" class="flex flex-col items-center justify-center flex-1 py-2 transition-all relative {{ $activeCart ? 'text-violet-300 glitch-pulse' : 'text-gray-300 hover:text-violet-400' }}">
            <svg class="w-6 h-6 mb-1 {{ $activeCart ? 'text-violet-300 glitch-pulse' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            @if(\Cart::getContent()->count() > 0)
                <span class="absolute top-1 right-2 bg-violet-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center font-bold">{{ \Cart::getContent()->count() }}</span>
            @endif
            <span class="text-xs font-medium">Cart</span>
        </a>

        @auth
            <!-- Profile -->
            <a href="{{ route('profile.index') }}" class="flex flex-col items-center justify-center flex-1 py-2 transition-all {{ $activeProfile ? 'text-violet-300 glitch-pulse' : 'text-gray-300 hover:text-violet-400' }}">
                <svg class="w-6 h-6 mb-1 {{ $activeProfile ? 'text-violet-300 glitch-pulse' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <span class="text-xs font-medium">Profile</span>
            </a>
        @else
            <!-- Login -->
            <a href="{{ route('login') }}" class="flex flex-col items-center justify-center flex-1 py-2 transition-all {{ $activeLogin ? 'text-violet-300 glitch-pulse' : 'text-gray-300 hover:text-violet-400' }}">
                <svg class="w-6 h-6 mb-1 {{ $activeLogin ? 'text-violet-300 glitch-pulse' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                </svg>
                <span class="text-xs font-medium">Login</span>
            </a>
        @endauth
    </div>
</div>
