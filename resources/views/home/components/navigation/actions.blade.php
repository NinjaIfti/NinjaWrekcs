<!-- Action Buttons (Desktop Only) -->
<div class="hidden md:flex items-center space-x-4">
    <!-- Search Button -->
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
        <!-- Email Verification Notice (if not verified) -->
        @if(!auth()->user()->hasVerifiedEmail())
            <a href="{{ route('verification.notice') }}" class="hidden md:flex items-center gap-2 px-3 py-2 bg-yellow-500/10 border border-yellow-500/30 rounded-lg text-yellow-400 hover:bg-yellow-500/20 transition-colors text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span>Verify Email</span>
            </a>
        @endif

        <!-- Notifications Bell -->
        <a href="{{ route('notifications.index') }}" class="p-2 text-gray-300 hover:text-violet-400 transition-colors relative group">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            @php
                $unreadCount = \App\Services\NotificationService::getUnreadCount(auth()->user());
            @endphp
            @if($unreadCount > 0)
                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center font-bold animate-pulse">
                    {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                </span>
            @endif
            <span class="absolute inset-0 bg-violet-500/20 rounded-lg scale-0 group-hover:scale-100 transition-transform duration-300"></span>
        </a>
    @endif

        <!-- Profile Dropdown -->
        <div class="relative group/profile">
            <a href="{{ route('profile.index') }}" class="hidden md:flex items-center px-4 py-2 text-gray-300 hover:text-violet-400 font-medium transition-colors relative">
                <span>Profile</span>
                @if(!auth()->user()->hasVerifiedEmail())
                    <span class="absolute -top-1 -right-1 w-2 h-2 bg-yellow-500 rounded-full animate-pulse"></span>
                @endif
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </a>
            
            <!-- Dropdown Menu -->
            <div class="absolute right-0 top-full mt-2 w-64 bg-black/95 backdrop-blur-xl rounded-lg border border-violet-500/30 shadow-2xl opacity-0 invisible group-hover/profile:opacity-100 group-hover/profile:visible transition-all duration-300 z-50">
                <div class="p-2">
                    @if(!auth()->user()->hasVerifiedEmail())
                        <a href="{{ route('verification.notice') }}" class="block px-4 py-3 mb-2 bg-yellow-500/10 border border-yellow-500/30 rounded-lg hover:bg-yellow-500/20 transition-colors">
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-yellow-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <div>
                                    <p class="text-yellow-400 text-sm font-semibold">Verify Email</p>
                                    <p class="text-yellow-300 text-xs mt-0.5">Click to verify your email</p>
                                </div>
                            </div>
                        </a>
                    @endif
                    
                    <a href="{{ route('profile.index') }}" class="block px-4 py-2 text-gray-300 hover:bg-violet-500/10 hover:text-violet-400 rounded-lg transition-colors">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            My Profile
                        </div>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2 text-gray-300 hover:bg-red-500/10 hover:text-red-400 rounded-lg transition-colors">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                Logout
                            </div>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @else
        <a href="{{ route('login') }}" class="hidden md:block px-4 py-2 text-gray-300 hover:text-violet-400 font-medium transition-colors">Login</a>
        <a href="{{ route('register') }}" class="hidden md:block px-6 py-2 bg-gradient-to-r from-violet-600 to-purple-600 text-white rounded-lg font-medium hover:shadow-lg hover:shadow-violet-500/50 hover:scale-105 transition-all relative overflow-hidden group">
            <span class="relative z-10">Sign Up</span>
            <span class="absolute inset-0 bg-gradient-to-r from-purple-600 to-violet-600 opacity-0 group-hover:opacity-100 transition-opacity"></span>
        </a>
    @endauth
</div>

