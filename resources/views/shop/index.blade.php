<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Shop Valorant Collectibles - NinjaWrekcs</title>
    <link rel="icon" type="image/png" href="{{ asset('img/fav.png') }}">
    
    @include('components.seo', [
        'title' => 'Shop Valorant Collectibles - NinjaWrekcs | Agent Figures, Knives & Weapons',
        'description' => 'Browse our complete collection of Valorant collectibles. Shop agent figures, knives, weapons, stickers, and keychains. Pre-order now with special discounts.',
        'url' => route('shop.index'),
        'keywords' => 'Valorant shop, buy Valorant collectibles, Valorant agent figures, Valorant knives, Valorant weapons, Valorant merchandise store, Bangladesh'
    ])
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-black text-white">
    <!-- Include Navigation -->
    @include('home.components.navigation')
    
    <!-- Shop Section -->
    <section class="pt-32 pb-20 min-h-screen bg-gradient-to-b from-black via-violet-950/50 to-black">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-12">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">
                    <span class="glitch-text" data-text="Shop">Shop</span>
                </h1>
                <p class="text-xl text-gray-400">Browse our Valorant collectibles</p>
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

            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Filters Sidebar -->
                <aside class="lg:w-64 flex-shrink-0">
                    <div class="bg-black/50 backdrop-blur-xl rounded-2xl border border-violet-500/30 p-6 sticky top-24">
                        <h2 class="text-xl font-bold text-white mb-6">Filters</h2>
                        
                        <div class="space-y-4">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-300 mb-3 uppercase">Category</h3>
                                <div class="space-y-2">
                                    <a href="{{ route('shop.index') }}" class="block px-4 py-2 rounded-lg transition {{ !$selectedCategory ? 'bg-violet-600 text-white' : 'text-gray-400 hover:bg-violet-500/20 hover:text-violet-400' }}">
                                        All Products
                                    </a>
                                    @foreach($categories as $key => $name)
                                    <a href="{{ route('shop.index', ['category' => $key]) }}" class="block px-4 py-2 rounded-lg transition {{ $selectedCategory === $key ? 'bg-violet-600 text-white' : 'text-gray-400 hover:bg-violet-500/20 hover:text-violet-400' }}">
                                        {{ $name }}
                                    </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </aside>

                <!-- Products Grid -->
                <div class="flex-1">
                    @if($selectedCategory)
                        <div class="mb-6">
                            <p class="text-gray-400">
                                Showing results for: <span class="text-violet-400 font-semibold">{{ $categories[$selectedCategory] }}</span>
                                <a href="{{ route('shop.index') }}" class="ml-4 text-violet-400 hover:text-violet-300 text-sm">Clear filter</a>
                            </p>
                        </div>
                    @endif

                    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
                        @if($products->count() > 0)
                            @foreach($products as $product)
                            <div class="group">
                                <a href="{{ route('shop.show', $product) }}" class="block">
                                    <div class="relative overflow-hidden rounded-xl mb-4 bg-gray-900 border border-violet-500/20">
                                        <img src="{{ $product->image ? asset('storage/' . $product->image) : '/img/placeholder.jpg' }}" alt="{{ $product->name }}" class="w-full h-72 object-cover group-hover:scale-110 transition-transform duration-500">
                                        <div class="absolute inset-0 glitch-overlay opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                        <div class="absolute top-4 right-4 z-10">
                                            <button class="w-10 h-10 bg-black/50 backdrop-blur-sm rounded-full flex items-center justify-center hover:bg-violet-600 hover:text-white transition-colors border border-violet-500/30" onclick="event.preventDefault();">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                                </svg>
                                            </button>
                                        </div>
                                        
                                        <!-- Add to Cart Button (Shows on Hover) -->
                                        @if($product->quantity > 0)
                                        <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300 z-20">
                                            <form action="{{ route('cart.add', $product) }}" method="POST" class="w-full px-4" onclick="event.stopPropagation();">
                                                @csrf
                                                <button type="submit" class="w-full px-6 py-3 bg-gradient-to-r from-violet-600 to-purple-600 text-white rounded-lg font-semibold hover:shadow-lg hover:shadow-violet-500/50 hover:scale-105 transition-all relative overflow-hidden group/btn">
                                                    <span class="relative z-10 flex items-center justify-center">
                                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                                        </svg>
                                                        Add to Cart
                                                    </span>
                                                    <span class="absolute inset-0 bg-gradient-to-r from-purple-600 to-violet-600 opacity-0 group-hover/btn:opacity-100 transition-opacity"></span>
                                                </button>
                                            </form>
                                        </div>
                                        @else
                                        <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300 z-20">
                                            <div class="w-full px-4">
                                                <button disabled class="w-full px-6 py-3 bg-gray-600 text-gray-400 rounded-lg font-semibold cursor-not-allowed">
                                                    Out of Stock
                                                </button>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </a>
                                <div class="space-y-2">
                                    <div class="flex items-center space-x-1">
                                        @for($i = 1; $i <= 5; $i++)
                                            <svg class="w-4 h-4 {{ $i <= $product->rating ? 'text-yellow-400' : 'text-gray-600' }} fill-current" viewBox="0 0 20 20">
                                                <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                            </svg>
                                        @endfor
                                        <span class="text-sm text-gray-400">({{ $product->reviews }})</span>
                                    </div>
                                    <a href="{{ route('shop.show', $product) }}">
                                        <h3 class="font-semibold text-white group-hover:text-violet-400 transition-colors">{{ $product->name }}</h3>
                                    </a>
                                    @if($product->description)
                                        <p class="text-sm text-gray-400 line-clamp-2">{{ $product->description }}</p>
                                    @endif
                                    @if($product->price)
                                        <p class="text-lg font-bold text-violet-400">৳{{ number_format($product->price, 2) }}</p>
                                    @endif
                                    @if($product->quantity > 0)
                                        <p class="text-sm text-violet-400">In Stock: {{ $product->quantity }}</p>
                                    @else
                                        <p class="text-sm text-red-400">Out of Stock</p>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        @else
                            <div class="col-span-full text-center py-12">
                                <p class="text-gray-400 text-lg">No products found in this category.</p>
                                <a href="{{ route('shop.index') }}" class="mt-4 inline-block px-6 py-3 bg-violet-600 text-white rounded-lg hover:bg-violet-700 transition">
                                    View All Products
                                </a>
                            </div>
                        @endif
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

