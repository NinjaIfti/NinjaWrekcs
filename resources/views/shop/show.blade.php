<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $product->name }} - NinjaWrekcs</title>
    <link rel="icon" type="image/png" href="{{ asset('img/fav.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-black text-white">
    <!-- Include Navigation -->
    @include('home.components.navigation')
    
    <!-- Product Detail Section -->
    <section class="pt-32 pb-20 min-h-screen bg-gradient-to-b from-black via-violet-950/50 to-black">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Back Button -->
            <a href="{{ route('shop.index') }}" class="inline-flex items-center text-violet-400 hover:text-violet-300 mb-8 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Shop
            </a>

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

            <div class="grid lg:grid-cols-2 gap-12">
                <!-- Product Image -->
                <div class="relative">
                    <div class="relative rounded-2xl overflow-hidden border border-violet-500/30 bg-gray-900">
                        <img src="{{ $product->image ? asset('storage/' . $product->image) : '/img/placeholder.jpg' }}" alt="{{ $product->name }}" class="w-full h-auto object-cover">
                        <div class="absolute inset-0 glitch-overlay opacity-30"></div>
                    </div>
                </div>

                <!-- Product Details -->
                <div class="space-y-6">
                    <!-- Category Badge -->
                    <div>
                        <span class="px-4 py-2 bg-violet-500/20 text-violet-300 rounded-full text-sm font-semibold border border-violet-500/30">
                            {{ $product->category_name }}
                        </span>
                    </div>

                    <!-- Product Name -->
                    <h1 class="text-4xl md:text-5xl font-bold">
                        <span class="glitch-text" data-text="{{ $product->name }}">{{ $product->name }}</span>
                    </h1>

                    <!-- Rating -->
                    <div class="flex items-center space-x-2">
                        <div class="flex items-center space-x-1">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="w-5 h-5 {{ $i <= $product->rating ? 'text-yellow-400' : 'text-gray-600' }} fill-current" viewBox="0 0 20 20">
                                    <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                </svg>
                            @endfor
                        </div>
                        <span class="text-gray-400">({{ $product->reviews }} reviews)</span>
                    </div>

                    <!-- Price -->
                    @if($product->price)
                    <div class="text-3xl font-bold text-violet-400">
                        ৳{{ number_format($product->price, 2) }}
                    </div>
                    @endif

                    <!-- Stock Status -->
                    <div>
                        @if($product->quantity > 0)
                            <p class="text-lg text-violet-400 font-semibold">✓ In Stock ({{ $product->quantity }} available)</p>
                        @else
                            <p class="text-lg text-red-400 font-semibold">✗ Out of Stock</p>
                        @endif
                    </div>

                    <!-- Description -->
                    @if($product->description)
                    <div class="border-t border-violet-500/20 pt-6">
                        <h2 class="text-xl font-bold mb-4">Description</h2>
                        <p class="text-gray-300 leading-relaxed">{{ $product->description }}</p>
                    </div>
                    @endif

                    <!-- Notes -->
                    @if($product->notes)
                    <div class="border-t border-violet-500/20 pt-6">
                        <h2 class="text-xl font-bold mb-4">Additional Notes</h2>
                        <p class="text-gray-300 leading-relaxed">{{ $product->notes }}</p>
                    </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="border-t border-violet-500/20 pt-6 space-y-4">
                        @if($product->quantity > 0)
                            <form action="{{ route('cart.add', $product) }}" method="POST" class="space-y-4">
                                @csrf
                                <div class="flex items-center space-x-4">
                                    <label for="quantity" class="text-gray-300">Quantity:</label>
                                    <input type="number" 
                                           id="quantity" 
                                           name="quantity" 
                                           value="1" 
                                           min="1" 
                                           max="{{ $product->quantity }}"
                                           class="w-24 px-3 py-2 bg-black/50 border border-violet-500/30 rounded-lg text-white focus:border-violet-500 focus:ring-violet-500/50">
                                </div>
                                <button type="submit" class="w-full px-8 py-4 bg-gradient-to-r from-violet-600 to-purple-600 text-white rounded-lg font-semibold hover:shadow-lg hover:shadow-violet-500/50 hover:scale-105 transition-all relative overflow-hidden group">
                                    <span class="relative z-10 flex items-center justify-center">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                        Add to Cart
                                    </span>
                                    <span class="absolute inset-0 bg-gradient-to-r from-purple-600 to-violet-600 opacity-0 group-hover:opacity-100 transition-opacity"></span>
                                </button>
                            </form>
                        @else
                            <button disabled class="w-full px-8 py-4 bg-gray-600 text-gray-400 rounded-lg font-semibold cursor-not-allowed">
                                Out of Stock
                            </button>
                        @endif
                        
                        <button class="w-full px-8 py-4 bg-transparent border-2 border-violet-500/50 text-violet-400 rounded-lg font-semibold hover:bg-violet-500/10 transition-all">
                            <span class="flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                </svg>
                                Add to Wishlist
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Include Footer -->
    @include('home.components.footer')
    
    <!-- Pre-Order Popup -->
    @include('components.pre-order-popup')
    
    <!-- Include Styles -->
    @include('home.styles')
</body>
</html>

