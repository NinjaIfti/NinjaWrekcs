<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cart - NinjaWrecks</title>
    <link rel="icon" type="image/png" href="{{ asset('img/fav.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-black text-white">
    <!-- Include Navigation -->
    @include('home.components.navigation')
    
    <!-- Cart Section -->
    <section class="pt-32 pb-20 min-h-screen bg-gradient-to-b from-black via-violet-950/50 to-black">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-12">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">
                    <span class="glitch-text" data-text="Shopping Cart">Shopping Cart</span>
                </h1>
                <p class="text-xl text-gray-400">Review your Valorant collectibles</p>
            </div>

            @if(session('success'))
                <div class="mb-6 p-4 bg-green-500/20 border border-green-500/50 rounded-lg text-green-400">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 bg-red-500/20 border border-red-500/50 rounded-lg text-red-400">
                    {{ session('error') }}
                </div>
            @endif

            @if($cartItems->count() > 0)
                <div class="grid lg:grid-cols-3 gap-8">
                    <!-- Cart Items -->
                    <div class="lg:col-span-2 space-y-4">
                        @foreach($cartItems as $item)
                            <div class="bg-black/50 backdrop-blur-xl rounded-2xl border border-violet-500/30 p-6">
                                <div class="flex flex-col md:flex-row gap-6">
                                    <!-- Product Image -->
                                    <div class="flex-shrink-0">
                                        <a href="{{ route('shop.show', $item->attributes->slug) }}">
                                            <img src="{{ $item->attributes->image ? asset('storage/' . $item->attributes->image) : '/img/placeholder.jpg' }}" 
                                                 alt="{{ $item->name }}" 
                                                 class="w-32 h-32 object-cover rounded-lg border border-violet-500/30">
                                        </a>
                                    </div>

                                    <!-- Product Details -->
                                    <div class="flex-1">
                                        <div class="flex justify-between items-start mb-4">
                                            <div>
                                                <a href="{{ route('shop.show', $item->attributes->slug) }}" class="text-xl font-bold text-white hover:text-violet-400 transition-colors">
                                                    {{ $item->name }}
                                                </a>
                                                <p class="text-sm text-gray-400 mt-1">{{ $item->attributes->category }}</p>
                                            </div>
                                            <form action="{{ route('cart.remove', $item->id) }}" method="POST" class="ml-4">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-400 hover:text-red-300 transition-colors">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>

                                        <div class="flex items-center justify-between">
                                            <!-- Quantity Update -->
                                            <div class="flex items-center space-x-4">
                                                <span class="text-gray-400">Quantity:</span>
                                                <form action="{{ route('cart.update', $item->id) }}" method="POST" class="flex items-center space-x-2">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="number" 
                                                           name="quantity" 
                                                           value="{{ $item->quantity }}" 
                                                           min="1" 
                                                           class="w-20 px-3 py-2 bg-black/50 border border-violet-500/30 rounded-lg text-white focus:border-violet-500 focus:ring-violet-500/50">
                                                    <button type="submit" class="px-4 py-2 bg-violet-600 hover:bg-violet-700 text-white rounded-lg transition-colors">
                                                        Update
                                                    </button>
                                                </form>
                                            </div>

                                            <!-- Price -->
                                            <div class="text-right">
                                                <p class="text-2xl font-bold text-violet-400">
                                                    ৳{{ number_format($item->price * $item->quantity, 2) }}
                                                </p>
                                                <p class="text-sm text-gray-400">
                                                    ৳{{ number_format($item->price, 2) }} each
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Cart Summary -->
                    <div class="lg:col-span-1">
                        <div class="bg-black/50 backdrop-blur-xl rounded-2xl border border-violet-500/30 p-6 sticky top-24">
                            <h2 class="text-2xl font-bold mb-6">Order Summary</h2>
                            
                            <div class="space-y-4 mb-6">
                                <div class="flex justify-between text-gray-300">
                                    <span>Subtotal</span>
                                    <span>৳{{ number_format($cartSubTotal, 2) }}</span>
                                </div>
                                <div class="flex justify-between text-gray-300">
                                    <span>Items</span>
                                    <span>{{ $cartItems->count() }}</span>
                                </div>
                                <div class="border-t border-violet-500/20 pt-4">
                                    <div class="flex justify-between text-xl font-bold">
                                        <span>Total</span>
                                        <span class="text-violet-400">৳{{ number_format($cartTotal, 2) }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <a href="{{ route('shop.index') }}" class="block w-full px-6 py-3 bg-transparent border-2 border-violet-500/50 text-violet-400 rounded-lg font-semibold hover:bg-violet-500/10 transition-all text-center">
                                    Continue Shopping
                                </a>
                                
                                <form action="{{ route('cart.clear') }}" method="POST" class="w-full">
                                    @csrf
                                    <button type="submit" class="w-full px-6 py-3 bg-red-600/20 border-2 border-red-500/50 text-red-400 rounded-lg font-semibold hover:bg-red-500/10 transition-all">
                                        Clear Cart
                                    </button>
                                </form>

                                <a href="{{ route('checkout.index') }}" class="block w-full px-6 py-3 bg-gradient-to-r from-violet-600 to-purple-600 text-white rounded-lg font-semibold hover:shadow-lg hover:shadow-violet-500/50 hover:scale-105 transition-all relative overflow-hidden group text-center">
                                    <span class="relative z-10 flex items-center justify-center">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                        </svg>
                                        Proceed to Checkout
                                    </span>
                                    <span class="absolute inset-0 bg-gradient-to-r from-purple-600 to-violet-600 opacity-0 group-hover:opacity-100 transition-opacity"></span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <!-- Empty Cart -->
                <div class="text-center py-20">
                    <div class="mb-8">
                        <svg class="w-32 h-32 mx-auto text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <h2 class="text-3xl font-bold mb-4">Your cart is empty</h2>
                    <p class="text-gray-400 mb-8">Start adding some Valorant collectibles to your cart!</p>
                    <a href="{{ route('shop.index') }}" class="inline-block px-8 py-4 bg-gradient-to-r from-violet-600 to-purple-600 text-white rounded-lg font-semibold hover:shadow-lg hover:shadow-violet-500/50 hover:scale-105 transition-all">
                        Browse Products
                    </a>
                </div>
            @endif
        </div>
    </section>

    <!-- Include Footer -->
    @include('home.components.footer')
    
    <!-- Include Styles -->
    @include('home.styles')
</body>
</html>

