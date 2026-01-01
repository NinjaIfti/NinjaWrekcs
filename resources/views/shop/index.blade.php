<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Shop Valorant Collectibles - NinjaWrekcs</title>
    <link rel="icon" type="image/png" href="{{ asset('img/fav.png') }}">
    
    @include('components.seo', [
        'title' => 'Shop Valorant Collectibles - NinjaWrekcs | Agent Figures, Knives & Weapons',
        'description' => 'Browse our complete collection of Valorant collectibles. Shop agent figures, knives, weapons, stickers, and keychains. Get special discounts.',
        'url' => route('shop.index'),
        'keywords' => 'Valorant shop, buy Valorant collectibles, Valorant agent figures, Valorant knives, Valorant weapons, Valorant merchandise store, Bangladesh'
    ])
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-black text-white">
    <!-- Include Navigation -->
    @include('home.components.navigation')
    
    <!-- Shop Section -->
    <section class="pt-24 md:pt-32 pb-20 min-h-screen bg-gradient-to-b from-black via-violet-950/50 to-black">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Header with Search -->
            <div class="mb-8 hidden lg:block">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">
                    <span class="glitch-text" data-text="Shop">Shop</span>
                </h1>
                <p class="text-xl text-gray-400 mb-6">Browse our Valorant collectibles</p>
                
                <!-- Search and Sort Bar -->
                <div class="flex gap-4 items-center">
                    <!-- Search Box -->
                    <form action="{{ route('shop.index') }}" method="GET" class="flex-1 max-w-xl">
                        <div class="relative">
                            <input type="text" 
                                   name="search" 
                                   value="{{ $search }}"
                                   placeholder="Search products..." 
                                   class="w-full px-4 py-3 pl-12 bg-black/50 border border-violet-500/30 rounded-lg text-white placeholder-gray-500 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/50 transition">
                            <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            @if($search)
                                <a href="{{ route('shop.index') }}" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-white">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </a>
                            @endif
                        </div>
                        <!-- Hidden inputs to preserve other filters -->
                        @if($selectedCategory)<input type="hidden" name="category" value="{{ $selectedCategory }}">@endif
                        @if($minPrice)<input type="hidden" name="min_price" value="{{ $minPrice }}">@endif
                        @if($maxPrice)<input type="hidden" name="max_price" value="{{ $maxPrice }}">@endif
                        @if($sort !== 'newest')<input type="hidden" name="sort" value="{{ $sort }}">@endif
                        @if($inStock)<input type="hidden" name="in_stock" value="1">@endif
                    </form>
                    
                    <!-- Sort Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="px-4 py-3 bg-black/50 border border-violet-500/30 rounded-lg text-white hover:border-violet-500 transition flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/>
                            </svg>
                            <span>Sort</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        
                        <div x-show="open" 
                             @click.away="open = false"
                             x-transition
                             class="absolute right-0 mt-2 w-56 bg-gray-900 border border-violet-500/30 rounded-lg shadow-xl z-50"
                             style="display: none;">
                            <div class="py-2">
                                <a href="{{ route('shop.index', array_merge(request()->except('sort'), [])) }}" class="block px-4 py-2 hover:bg-violet-500/20 transition {{ $sort === 'newest' ? 'text-violet-400' : 'text-gray-300' }}">
                                    Newest First
                                </a>
                                <a href="{{ route('shop.index', array_merge(request()->all(), ['sort' => 'price_asc'])) }}" class="block px-4 py-2 hover:bg-violet-500/20 transition {{ $sort === 'price_asc' ? 'text-violet-400' : 'text-gray-300' }}">
                                    Price: Low to High
                                </a>
                                <a href="{{ route('shop.index', array_merge(request()->all(), ['sort' => 'price_desc'])) }}" class="block px-4 py-2 hover:bg-violet-500/20 transition {{ $sort === 'price_desc' ? 'text-violet-400' : 'text-gray-300' }}">
                                    Price: High to Low
                                </a>
                                <a href="{{ route('shop.index', array_merge(request()->all(), ['sort' => 'name_asc'])) }}" class="block px-4 py-2 hover:bg-violet-500/20 transition {{ $sort === 'name_asc' ? 'text-violet-400' : 'text-gray-300' }}">
                                    Name: A to Z
                                </a>
                                <a href="{{ route('shop.index', array_merge(request()->all(), ['sort' => 'name_desc'])) }}" class="block px-4 py-2 hover:bg-violet-500/20 transition {{ $sort === 'name_desc' ? 'text-violet-400' : 'text-gray-300' }}">
                                    Name: Z to A
                                </a>
                                <a href="{{ route('shop.index', array_merge(request()->all(), ['sort' => 'popular'])) }}" class="block px-4 py-2 hover:bg-violet-500/20 transition {{ $sort === 'popular' ? 'text-violet-400' : 'text-gray-300' }}">
                                    Most Popular
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Active Filters -->
                @if($search || $selectedCategory || $minPrice || $maxPrice || $inStock)
                <div class="mt-4 flex items-center gap-2 flex-wrap">
                    <span class="text-sm text-gray-400">Active filters:</span>
                    @if($search)
                        <a href="{{ route('shop.index', array_merge(request()->except('search'), [])) }}" class="px-3 py-1 bg-violet-500/20 border border-violet-500/50 rounded-full text-sm text-violet-300 hover:bg-violet-500/30 transition flex items-center gap-2">
                            Search: "{{ $search }}"
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </a>
                    @endif
                    @if($selectedCategory)
                        <a href="{{ route('shop.index', array_merge(request()->except('category'), [])) }}" class="px-3 py-1 bg-violet-500/20 border border-violet-500/50 rounded-full text-sm text-violet-300 hover:bg-violet-500/30 transition flex items-center gap-2">
                            {{ $categories[$selectedCategory] }}
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </a>
                    @endif
                    @if($minPrice || $maxPrice)
                        <a href="{{ route('shop.index', array_merge(request()->except(['min_price', 'max_price']), [])) }}" class="px-3 py-1 bg-violet-500/20 border border-violet-500/50 rounded-full text-sm text-violet-300 hover:bg-violet-500/30 transition flex items-center gap-2">
                            Price: ৳{{ $minPrice ?: 0 }} - ৳{{ $maxPrice ?: '∞' }}
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </a>
                    @endif
                    @if($inStock)
                        <a href="{{ route('shop.index', array_merge(request()->except('in_stock'), [])) }}" class="px-3 py-1 bg-violet-500/20 border border-violet-500/50 rounded-full text-sm text-violet-300 hover:bg-violet-500/30 transition flex items-center gap-2">
                            In Stock Only
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </a>
                    @endif
                    <a href="{{ route('shop.index') }}" class="px-3 py-1 text-sm text-red-400 hover:text-red-300 transition">
                        Clear All
                    </a>
                </div>
                @endif
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

            <!-- Mobile Layout -->
            <div class="lg:hidden">
                @include('shop.mobile', [
                    'categories' => $categories,
                    'selectedCategory' => $selectedCategory,
                    'products' => $products,
                    'search' => $search,
                    'minPrice' => $minPrice,
                    'maxPrice' => $maxPrice,
                    'sort' => $sort,
                    'inStock' => $inStock,
                    'priceRange' => $priceRange,
                ])
            </div>

            <!-- Desktop Layout -->
            <div class="hidden lg:flex flex-col lg:flex-row gap-8">
                <!-- Filters Sidebar -->
                <aside class="lg:w-72 flex-shrink-0">
                    <div class="bg-black/50 backdrop-blur-xl rounded-2xl border border-violet-500/30 p-6 sticky top-24">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-xl font-bold text-white">Filters</h2>
                            @if($search || $selectedCategory || $minPrice || $maxPrice || $inStock)
                                <a href="{{ route('shop.index') }}" class="text-sm text-red-400 hover:text-red-300 transition">
                                    Clear All
                                </a>
                            @endif
                        </div>
                        
                        <form action="{{ route('shop.index') }}" method="GET" class="space-y-6">
                            <!-- Preserve search -->
                            @if($search)
                                <input type="hidden" name="search" value="{{ $search }}">
                            @endif
                            
                            <!-- Category Filter -->
                            <div>
                                <h3 class="text-sm font-semibold text-gray-300 mb-3 uppercase">Category</h3>
                                <div class="space-y-2">
                                    <label class="flex items-center cursor-pointer">
                                        <input type="radio" name="category" value="" {{ !$selectedCategory ? 'checked' : '' }} class="mr-3 text-violet-600 focus:ring-violet-500" onchange="this.form.submit()">
                                        <span class="text-gray-400">All Products</span>
                                    </label>
                                    @foreach($categories as $key => $name)
                                    <label class="flex items-center cursor-pointer">
                                        <input type="radio" name="category" value="{{ $key }}" {{ $selectedCategory === $key ? 'checked' : '' }} class="mr-3 text-violet-600 focus:ring-violet-500" onchange="this.form.submit()">
                                        <span class="text-gray-400">{{ $name }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                            
                            <!-- Price Range Filter -->
                            <div>
                                <h3 class="text-sm font-semibold text-gray-300 mb-3 uppercase">Price Range</h3>
                                <div class="space-y-3">
                                    <div>
                                        <label class="text-xs text-gray-400 mb-1 block">Min Price (৳)</label>
                                        <input type="number" 
                                               name="min_price" 
                                               value="{{ $minPrice }}"
                                               placeholder="{{ $priceRange->min ?? 0 }}"
                                               min="0"
                                               class="w-full px-3 py-2 bg-black/50 border border-violet-500/30 rounded-lg text-white text-sm focus:border-violet-500 focus:ring-1 focus:ring-violet-500">
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-400 mb-1 block">Max Price (৳)</label>
                                        <input type="number" 
                                               name="max_price" 
                                               value="{{ $maxPrice }}"
                                               placeholder="{{ $priceRange->max ?? 10000 }}"
                                               min="0"
                                               class="w-full px-3 py-2 bg-black/50 border border-violet-500/30 rounded-lg text-white text-sm focus:border-violet-500 focus:ring-1 focus:ring-violet-500">
                                    </div>
                                    @if($priceRange)
                                    <p class="text-xs text-gray-500">Range: ৳{{ number_format($priceRange->min, 0) }} - ৳{{ number_format($priceRange->max, 0) }}</p>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Availability Filter -->
                            <div>
                                <h3 class="text-sm font-semibold text-gray-300 mb-3 uppercase">Availability</h3>
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" 
                                           name="in_stock" 
                                           value="1" 
                                           {{ $inStock ? 'checked' : '' }}
                                           class="mr-3 rounded text-violet-600 focus:ring-violet-500">
                                    <span class="text-gray-400">In Stock Only</span>
                                </label>
                            </div>
                            
                            <!-- Sort (hidden, preserve from URL) -->
                            @if($sort && $sort !== 'newest')
                                <input type="hidden" name="sort" value="{{ $sort }}">
                            @endif
                            
                            <!-- Apply Filters Button -->
                            <button type="submit" class="w-full px-4 py-2 bg-gradient-to-r from-violet-600 to-purple-600 text-white rounded-lg font-semibold hover:shadow-lg hover:shadow-violet-500/50 transition">
                                Apply Filters
                            </button>
                        </form>
                    </div>
                </aside>

                <!-- Products Grid -->
                <div class="flex-1">
                    <!-- Results Count -->
                    <div class="mb-6 flex items-center justify-between">
                        <p class="text-gray-400">
                            Showing <span class="text-white font-semibold">{{ $products->count() }}</span> {{ $products->count() === 1 ? 'product' : 'products' }}
                        </p>
                    </div>

                    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
                        @if($products->count() > 0)
                            @foreach($products as $product)
                            <div class="group">
                                <a href="{{ route('shop.show', $product) }}" class="block">
                                    <div class="relative overflow-hidden rounded-xl mb-4 bg-gray-900 border border-violet-500/20">
                                        @php
                                            $cover = $product->images->first()->path ?? $product->image;
                                        @endphp
                                        <img src="{{ $cover ? asset('storage/' . $cover) : '/img/placeholder.jpg' }}" alt="{{ $product->name }}" class="w-full h-72 object-cover group-hover:scale-110 transition-transform duration-500">
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
    
    
    <!-- Include Styles -->
    @include('home.styles')
</body>
</html>

