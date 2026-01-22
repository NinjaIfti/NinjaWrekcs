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
    <section class="pt-16 md:pt-28 pb-20 min-h-screen bg-gradient-to-b from-black via-violet-950/50 to-black">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-8 md:mb-12">
                <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold mb-2 md:mb-4">
                    <span class="glitch-text" data-text="Shopping Cart">Shopping Cart</span>
                </h1>
                <p class="text-base md:text-xl text-gray-400">Review your Valorant collectibles</p>
            </div>

            @if(session('success'))
                <div class="mb-4 md:mb-6 p-3 md:p-4 bg-green-500/20 border border-green-500/50 rounded-lg text-green-400 text-sm md:text-base">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 md:mb-6 p-3 md:p-4 bg-red-500/20 border border-red-500/50 rounded-lg text-red-400 text-sm md:text-base">
                    {{ session('error') }}
                </div>
            @endif

            @if($cartItems->count() > 0)
                @if($hasBookableItems)
                    <div class="mb-4 md:mb-6 p-3 md:p-4 bg-purple-500/20 border border-purple-500/50 rounded-lg">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-purple-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <div>
                                <p class="text-purple-300 font-semibold mb-1">Pre-Order Cart</p>
                                <p class="text-purple-400 text-sm">This cart contains pre-order items. Cash on Delivery is not available for pre-orders. You'll pay a booking fee now and the remaining amount later.</p>
                            </div>
                        </div>
                    </div>
                @endif
                
                <div class="grid lg:grid-cols-3 gap-6 md:gap-8">
                    <!-- Cart Items -->
                    <div class="lg:col-span-2 space-y-4 md:space-y-6 order-2 lg:order-1">
                        @foreach($cartItems as $item)
                            <div class="bg-black/50 backdrop-blur-xl rounded-xl md:rounded-2xl border border-violet-500/30 p-4 md:p-6">
                                <div class="flex gap-4 md:gap-6">
                                    <!-- Product Image -->
                                    <div class="flex-shrink-0">
                                        <a href="{{ route('shop.show', $item->attributes->slug) }}">
                                            <img src="{{ $item->attributes->image ? asset('storage/' . $item->attributes->image) : '/img/placeholder.jpg' }}" 
                                                 alt="{{ $item->name }}" 
                                                 class="w-24 h-24 md:w-32 md:h-32 object-cover rounded-lg border border-violet-500/30">
                                        </a>
                                    </div>

                                    <!-- Product Details -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex justify-between items-start mb-3 md:mb-4 gap-2">
                                            <div class="flex-1 min-w-0">
                                                <a href="{{ route('shop.show', $item->attributes->slug) }}" class="text-lg md:text-xl font-bold text-white hover:text-violet-400 transition-colors line-clamp-2">
                                                    {{ $item->name }}
                                                </a>
                                                <p class="text-xs md:text-sm text-gray-400 mt-1">{{ $item->attributes->category }}</p>
                                            </div>
                                            <form action="{{ route('cart.remove', $item->id) }}" method="POST" class="flex-shrink-0">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-400 hover:text-red-300 transition-colors p-2 -mr-2 -mt-2">
                                                    <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>

                                        <!-- Mobile: Stack everything vertically -->
                                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                            <!-- Quantity Update -->
                                            <div class="flex items-center gap-3">
                                                <span class="text-sm md:text-base text-gray-400 whitespace-nowrap">Quantity:</span>
                                                <form action="{{ route('cart.update', $item->id) }}" method="POST" class="flex items-center gap-2">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="number" 
                                                           name="quantity" 
                                                           value="{{ $item->quantity }}" 
                                                           min="1" 
                                                           class="w-16 md:w-20 px-2 md:px-3 py-2 bg-black/50 border border-violet-500/30 rounded-lg text-white text-sm md:text-base focus:border-violet-500 focus:ring-violet-500/50">
                                                    <button type="submit" class="px-3 md:px-4 py-2 bg-violet-600 hover:bg-violet-700 text-white rounded-lg transition-colors text-sm md:text-base font-medium">
                                                        Update
                                                    </button>
                                                </form>
                                            </div>

                                            <!-- Price -->
                                            @php
                                                // Check if item is bookable from attributes or database
                                                $isBookable = false;
                                                if (isset($item->attributes->is_bookable)) {
                                                    $isBookable = (bool) $item->attributes->is_bookable;
                                                }
                                                
                                                // If not set in attributes, check database
                                                if (!$isBookable) {
                                                    $productCheck = \App\Models\Product::find($item->id);
                                                    $isBookable = $productCheck && (bool) $productCheck->is_bookable;
                                                }
                                                
                                                // For pre-order items, ALWAYS fetch original price from database
                                                if ($isBookable) {
                                                    $product = \App\Models\Product::find($item->id);
                                                    if ($product) {
                                                        // Always use the product's display_price or price (original, not reduced)
                                                        $displayPrice = (float) ($product->display_price ?? $product->price ?? 0);
                                                    } else {
                                                        // Fallback: use original_price from attributes if product not found
                                                        $displayPrice = (float) ($item->attributes->original_price ?? $item->price);
                                                    }
                                                } else {
                                                    // Regular items: use cart price as-is
                                                    $displayPrice = (float) $item->price;
                                                }
                                            @endphp
                                            <div class="text-left md:text-right">
                                                <p class="text-xl md:text-2xl font-bold text-violet-400">
                                                    ৳{{ number_format($displayPrice * $item->quantity, 2) }}
                                                </p>
                                                <p class="text-xs md:text-sm text-gray-400">
                                                    ৳{{ number_format($displayPrice, 2) }} each
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Cart Summary -->
                    <div class="lg:col-span-1 order-1 lg:order-2">
                        <div class="bg-black/50 backdrop-blur-xl rounded-xl md:rounded-2xl border border-violet-500/30 p-4 md:p-6 lg:sticky lg:top-24">
                            <h2 class="text-xl md:text-2xl font-bold mb-4 md:mb-6">Order Summary</h2>
                            
                            <div class="space-y-3 md:space-y-4 mb-4 md:mb-6">
                                <div class="flex justify-between text-sm md:text-base text-gray-300">
                                    <span>Subtotal</span>
                                    <span>৳{{ number_format($cartSubTotal, 2) }}</span>
                                </div>
                                <div class="flex justify-between text-sm md:text-base text-gray-300">
                                    <span>Items</span>
                                    <span>{{ $cartItems->count() }}</span>
                                </div>
                                <div class="border-t border-violet-500/20 pt-3 md:pt-4">
                                    <div class="flex justify-between text-lg md:text-xl font-bold">
                                        <span>Total</span>
                                        <span class="text-violet-400">৳{{ number_format($cartTotal, 2) }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-2 md:space-y-3">
                                <a href="{{ route('shop.index') }}" class="block w-full px-4 md:px-6 py-2.5 md:py-3 bg-transparent border-2 border-violet-500/50 text-violet-400 rounded-lg font-semibold hover:bg-violet-500/10 transition-all text-center text-sm md:text-base">
                                    Continue Shopping
                                </a>
                                
                                <form action="{{ route('cart.clear') }}" method="POST" class="w-full">
                                    @csrf
                                    <button type="submit" class="w-full px-4 md:px-6 py-2.5 md:py-3 bg-red-600/20 border-2 border-red-500/50 text-red-400 rounded-lg font-semibold hover:bg-red-500/10 transition-all text-sm md:text-base">
                                        Clear Cart
                                    </button>
                                </form>

                                <a href="{{ route('checkout.index') }}" class="block w-full px-4 md:px-6 py-3 md:py-3.5 bg-gradient-to-r from-violet-600 to-purple-600 text-white rounded-lg font-semibold hover:shadow-lg hover:shadow-violet-500/50 hover:scale-105 transition-all relative overflow-hidden group text-center">
                                    <span class="relative z-10 flex items-center justify-center text-sm md:text-base">
                                        <svg class="w-4 h-4 md:w-5 md:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                <div class="text-center py-12 md:py-20">
                    <div class="mb-6 md:mb-8">
                        <svg class="w-24 h-24 md:w-32 md:h-32 mx-auto text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <h2 class="text-2xl md:text-3xl font-bold mb-3 md:mb-4">Your cart is empty</h2>
                    <p class="text-base md:text-lg text-gray-400 mb-6 md:mb-8 px-4">Start adding some Valorant collectibles to your cart!</p>
                    <a href="{{ route('shop.index') }}" class="inline-block px-6 md:px-8 py-3 md:py-4 bg-gradient-to-r from-violet-600 to-purple-600 text-white rounded-lg font-semibold hover:shadow-lg hover:shadow-violet-500/50 hover:scale-105 transition-all text-sm md:text-base">
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

