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
                <a href="{{ route('shop.index') }}" class="text-gray-300 hover:text-violet-400 transition-colors font-medium relative group">
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
                <!-- Cart Dropdown -->
                <div class="relative group/cart">
                    <a href="{{ route('cart.index') }}" class="p-2 text-gray-300 hover:text-violet-400 transition-colors relative">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        @if(\Cart::getContent()->count() > 0)
                            <span class="absolute -top-1 -right-1 bg-violet-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center glitch-pulse">{{ \Cart::getContent()->count() }}</span>
                        @endif
                    </a>
                    
                    <!-- Cart Dropdown Menu -->
                    @if(\Cart::getContent()->count() > 0)
                    <div class="absolute right-0 top-full mt-2 w-80 bg-black/95 backdrop-blur-xl rounded-2xl border border-violet-500/30 shadow-2xl opacity-0 invisible group-hover/cart:opacity-100 group-hover/cart:visible transition-all duration-300 z-50 cart-dropdown">
                        <div class="p-4">
                            <div class="flex justify-between items-center mb-4 pb-4 border-b border-violet-500/20">
                                <h3 class="text-lg font-bold text-white">Shopping Cart</h3>
                                <span class="text-sm text-gray-400">{{ \Cart::getContent()->count() }} item(s)</span>
                            </div>
                            
                            <!-- Cart Items (Max 3) -->
                            <div class="space-y-3 max-h-64 overflow-y-auto custom-scrollbar">
                                @foreach(\Cart::getContent()->take(3) as $item)
                                    <a href="{{ route('cart.index') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-violet-500/10 transition-colors group/item">
                                        <div class="flex-shrink-0">
                                            <img src="{{ $item->attributes->image ? asset('storage/' . $item->attributes->image) : '/img/placeholder.jpg' }}" 
                                                 alt="{{ $item->name }}" 
                                                 class="w-16 h-16 object-cover rounded-lg border border-violet-500/30">
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h4 class="text-sm font-semibold text-white group-hover/item:text-violet-400 transition-colors truncate">{{ $item->name }}</h4>
                                            <p class="text-xs text-gray-400">Qty: {{ $item->quantity }}</p>
                                            <p class="text-sm font-bold text-violet-400">৳{{ number_format($item->price * $item->quantity, 2) }}</p>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                            
                            <!-- View Full Cart Button -->
                            <div class="mt-4 pt-4 border-t border-violet-500/20">
                                <div class="flex justify-between items-center mb-3">
                                    <span class="text-gray-300 font-semibold">Total:</span>
                                    <span class="text-xl font-bold text-violet-400">৳{{ number_format(\Cart::getTotal(), 2) }}</span>
                                </div>
                                <a href="{{ route('cart.index') }}" class="block w-full px-4 py-3 bg-gradient-to-r from-violet-600 to-purple-600 text-white rounded-lg font-semibold hover:shadow-lg hover:shadow-violet-500/50 transition-all text-center">
                                    View Full Cart
                                </a>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                @auth
                    <a href="{{ route('profile.index') }}" class="hidden md:block px-4 py-2 text-gray-300 hover:text-violet-400 font-medium transition-colors">Profile</a>
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


