<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Shop Valorant Collectibles - NinjaWrecks</title>
    <link rel="icon" type="image/png" href="{{ asset('img/fav.png') }}">
    
    @include('components.seo', [
        'title' => 'Shop Valorant Collectibles - NinjaWrecks | Agent Figures, Knives & Weapons',
        'description' => 'Browse our complete collection of Valorant collectibles. Shop agent figures, knives, weapons, stickers, and keychains. Get special discounts.',
        'url' => route('shop.index'),
        'keywords' => 'Valorant shop, buy Valorant collectibles, Valorant agent figures, Valorant knives, Valorant weapons, Valorant merchandise store, Bangladesh'
    ])
    
    @include('components.analytics')
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-black text-white">
    <!-- Include Navigation -->
    @include('home.components.navigation')
    
    <!-- Shop Section -->
    <section class="pt-24 md:pt-32 pb-20 min-h-screen bg-gradient-to-b from-black via-violet-950/50 to-black" x-data="{ 
        viewMode: localStorage.getItem('shopViewMode') || 'grid-3',
        filtersCollapsed: false 
    }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Breadcrumbs -->
            <nav class="mb-6 hidden lg:flex items-center text-sm text-gray-400" aria-label="Breadcrumb">
                <a href="{{ url('/') }}" class="hover:text-violet-400 transition-colors">Home</a>
                <svg class="w-4 h-4 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                @if($selectedCategory)
                    <a href="{{ route('shop.index') }}" class="hover:text-violet-400 transition-colors">Shop</a>
                    <svg class="w-4 h-4 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                    <span class="text-white font-semibold">{{ $categories[$selectedCategory] }}</span>
                @else
                    <span class="text-white font-semibold">Shop</span>
                @endif
            </nav>

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
                    'categoryCounts' => $categoryCounts,
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
                                    <label class="flex items-center justify-between cursor-pointer group">
                                        <div class="flex items-center">
                                        <input type="radio" name="category" value="" {{ !$selectedCategory ? 'checked' : '' }} class="mr-3 text-violet-600 focus:ring-violet-500" onchange="this.form.submit()">
                                            <span class="text-gray-400 group-hover:text-white transition-colors">All Products</span>
                                        </div>
                                        <span class="px-2 py-0.5 bg-violet-500/20 text-violet-300 text-xs font-semibold rounded-full">
                                            {{ array_sum($categoryCounts) }}
                                        </span>
                                    </label>
                                    @foreach($categories as $key => $name)
                                    <label class="flex items-center justify-between cursor-pointer group">
                                        <div class="flex items-center">
                                        <input type="radio" name="category" value="{{ $key }}" {{ $selectedCategory === $key ? 'checked' : '' }} class="mr-3 text-violet-600 focus:ring-violet-500" onchange="this.form.submit()">
                                            <span class="text-gray-400 group-hover:text-white transition-colors">{{ $name }}</span>
                                        </div>
                                        <span class="px-2 py-0.5 bg-violet-500/20 text-violet-300 text-xs font-semibold rounded-full">
                                            {{ $categoryCounts[$key] ?? 0 }}
                                        </span>
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
                    <!-- Results Count & View Toggle -->
                    <div class="mb-6 flex items-center justify-between">
                        <p class="text-gray-400">
                            Showing <span class="text-white font-semibold">{{ $products->count() }}</span> of <span class="text-white font-semibold">{{ $products->total() }}</span> {{ $products->total() === 1 ? 'product' : 'products' }}
                        </p>
                        
                        <!-- View Toggle -->
                        <div class="flex items-center gap-2 bg-black/50 border border-violet-500/30 rounded-lg p-1">
                            <button @click="viewMode = 'grid-2'; localStorage.setItem('shopViewMode', 'grid-2')" 
                                    :class="viewMode === 'grid-2' ? 'bg-violet-600 text-white' : 'text-gray-400 hover:text-white'"
                                    class="p-2 rounded transition-colors"
                                    title="2 Columns">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h4a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h4a2 2 0 012 2v4a2 2 0 01-2 2h-4a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h4a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h4a2 2 0 012 2v4a2 2 0 01-2 2h-4a2 2 0 01-2-2v-4z"/>
                                </svg>
                            </button>
                            <button @click="viewMode = 'grid-3'; localStorage.setItem('shopViewMode', 'grid-3')" 
                                    :class="viewMode === 'grid-3' ? 'bg-violet-600 text-white' : 'text-gray-400 hover:text-white'"
                                    class="p-2 rounded transition-colors"
                                    title="3 Columns">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM11 5a1 1 0 011-1h1a1 1 0 011 1v4a1 1 0 01-1 1h-1a1 1 0 01-1-1V5zM11 15a1 1 0 011-1h1a1 1 0 011 1v4a1 1 0 01-1 1h-1a1 1 0 01-1-1v-4zM14 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/>
                                                </svg>
                                            </button>
                            <button @click="viewMode = 'grid-4'; localStorage.setItem('shopViewMode', 'grid-4')" 
                                    :class="viewMode === 'grid-4' ? 'bg-violet-600 text-white' : 'text-gray-400 hover:text-white'"
                                    class="p-2 rounded transition-colors"
                                    title="4 Columns">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM10 5a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1h-2a1 1 0 01-1-1V5zM16 5a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1h-2a1 1 0 01-1-1V5zM4 11a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1v-2zM10 11a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1h-2a1 1 0 01-1-1v-2zM16 11a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1h-2a1 1 0 01-1-1v-2z"/>
                                                        </svg>
                                                </button>
                            <button @click="viewMode = 'list'; localStorage.setItem('shopViewMode', 'list')" 
                                    :class="viewMode === 'list' ? 'bg-violet-600 text-white' : 'text-gray-400 hover:text-white'"
                                    class="p-2 rounded transition-colors"
                                    title="List View">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                                </svg>
                                                </button>
                                            </div>
                                        </div>

                    <div id="products-container" 
                         class="grid gap-8 transition-all duration-300"
                         :class="{
                             'sm:grid-cols-2': viewMode === 'grid-2',
                             'sm:grid-cols-2 lg:grid-cols-3': viewMode === 'grid-3',
                             'sm:grid-cols-2 lg:grid-cols-4': viewMode === 'grid-4',
                             'grid-cols-1': viewMode === 'list'
                         }">
                        @if($products->count() > 0)
                            @include('shop.partials.product-grid', ['products' => $products])
                        @else
                            <div class="col-span-full text-center py-12">
                                <p class="text-gray-400 text-lg">No products found in this category.</p>
                                <a href="{{ route('shop.index') }}" class="mt-4 inline-block px-6 py-3 bg-violet-600 text-white rounded-lg hover:bg-violet-700 transition">
                                    View All Products
                                </a>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Load More Button -->
                    @if($products->hasMorePages())
                    <div class="mt-12 text-center">
                        <button id="load-more-btn" 
                                class="px-8 py-4 bg-gradient-to-r from-violet-600 to-purple-600 text-white rounded-lg font-semibold hover:shadow-lg hover:shadow-violet-500/50 hover:scale-105 transition-all inline-flex items-center gap-2"
                                data-next-page="{{ $products->currentPage() + 1 }}"
                                data-base-url="{{ route('shop.index') }}">
                            <span>Load More Products</span>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div id="loading-spinner" class="hidden mt-4">
                            <svg class="animate-spin h-8 w-8 text-violet-500 mx-auto" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <p class="text-gray-400 text-sm mt-2">Loading more products...</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <!-- Quick View Modal -->
    <div id="quickViewModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/80 backdrop-blur-sm" style="display: none;">
        <div class="relative bg-gray-900 rounded-2xl border border-violet-500/30 max-w-4xl w-full max-h-[90vh] overflow-y-auto shadow-2xl shadow-violet-500/20" onclick="event.stopPropagation();">
            <!-- Close Button -->
            <button onclick="closeQuickView()" class="absolute top-4 right-4 z-10 w-10 h-10 bg-black/50 backdrop-blur-sm rounded-full flex items-center justify-center hover:bg-red-500 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            
            <!-- Modal Content -->
            <div class="grid md:grid-cols-2 gap-8 p-8">
                <!-- Product Image -->
                <div class="relative">
                    <div class="relative rounded-xl overflow-hidden border border-violet-500/20 bg-black/50">
                        <img id="qv-image" src="" alt="" class="w-full h-auto object-cover">
                        <div id="qv-badges" class="absolute top-4 left-4 flex flex-col gap-2"></div>
                    </div>
                </div>
                
                <!-- Product Info -->
                <div class="space-y-4">
                    <div>
                        <span id="qv-category" class="px-3 py-1 bg-violet-500/20 text-violet-300 rounded-full text-sm font-semibold border border-violet-500/30"></span>
                    </div>
                    
                    <h2 id="qv-name" class="text-3xl font-bold text-white"></h2>
                    
                    <!-- Price -->
                    <div id="qv-price-container" class="flex items-center gap-3"></div>
                    
                    <!-- Stock Status -->
                    <div id="qv-stock" class="flex items-center gap-2"></div>
                    
                    <!-- Description -->
                    <div class="border-t border-violet-500/20 pt-4">
                        <p id="qv-description" class="text-gray-300 leading-relaxed"></p>
                    </div>
                    
                    <!-- Actions -->
                    <div class="flex gap-3 pt-4">
                        <form id="qv-add-to-cart-form" method="POST" class="flex-1">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <button type="submit" id="qv-add-to-cart-btn" class="w-full px-6 py-3 bg-gradient-to-r from-violet-600 to-purple-600 text-white rounded-lg font-semibold hover:shadow-lg hover:shadow-violet-500/50 transition-all flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                Add to Cart
                            </button>
                        </form>
                        <a id="qv-view-details" href="#" class="px-6 py-3 bg-black/50 border border-violet-500/30 text-white rounded-lg font-semibold hover:bg-violet-500/20 transition-all">
                            View Details
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Notification Modal -->
    <div id="notifyModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/80 backdrop-blur-sm" style="display: none;">
        <div class="relative bg-gray-900 rounded-2xl border border-violet-500/30 max-w-md w-full shadow-2xl shadow-violet-500/20" onclick="event.stopPropagation();">
            <!-- Close Button -->
            <button onclick="closeNotifyModal()" class="absolute top-4 right-4 z-10 w-10 h-10 bg-black/50 backdrop-blur-sm rounded-full flex items-center justify-center hover:bg-red-500 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            
            <!-- Modal Content -->
            <div class="p-8">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-blue-500/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </div>
                    <h3 id="notify-product-name" class="text-2xl font-bold text-white mb-2"></h3>
                    <p class="text-gray-400">Get notified when this product is back in stock</p>
                </div>
                
                <form id="notifyForm" class="space-y-4">
                    @csrf
                    <input type="hidden" id="notify-product-id" name="product_id">
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-300 mb-2">Email Address</label>
                        <input type="email" name="email" required 
                               class="w-full px-4 py-3 bg-black/50 border border-violet-500/30 rounded-lg text-white focus:outline-none focus:border-violet-500 focus:ring-2 focus:ring-violet-500/50 transition-all"
                               placeholder="your@email.com"
                               value="{{ auth()->user()->email ?? '' }}">
                    </div>
                    
                    <button type="submit" class="w-full px-6 py-3 bg-gradient-to-r from-blue-600 to-cyan-600 text-white rounded-lg font-semibold hover:shadow-lg hover:shadow-blue-500/50 transition-all">
                        <span class="flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            Notify Me
                        </span>
                    </button>
                </form>
                
                <div id="notify-success" class="hidden mt-4 p-4 bg-green-500/20 border border-green-500/30 rounded-lg">
                    <p class="text-green-400 text-center font-semibold">✓ You'll be notified when this product is back in stock!</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Live Purchase Notification -->
    <div id="liveNotification" class="fixed bottom-6 left-6 z-[9999] transition-all duration-500 ease-out" style="display: none;">
        <div class="bg-gray-900 border border-violet-500/30 rounded-xl shadow-2xl shadow-violet-500/20 p-4 flex items-center gap-4 max-w-sm backdrop-blur-sm">
            <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-gradient-to-br from-violet-500 to-purple-600 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-white truncate" id="live-customer-name"></p>
                <p class="text-xs text-gray-400" id="live-product-name"></p>
                <p class="text-xs text-violet-400 mt-1">
                    <span id="live-time-ago"></span> • 
                    <span id="live-location"></span>
                </p>
            </div>
            <button onclick="closeLiveNotification()" class="flex-shrink-0 text-gray-500 hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Include Footer -->
    @include('home.components.footer')
    
    <!-- Infinite Scroll Load More JavaScript -->
    <script>
        // Load More Functionality
        document.addEventListener('DOMContentLoaded', function() {
            const loadMoreBtn = document.getElementById('load-more-btn');
            const loadingSpinner = document.getElementById('loading-spinner');
            const productsContainer = document.getElementById('products-container');
            
            if (loadMoreBtn) {
                loadMoreBtn.addEventListener('click', function() {
                    const nextPage = this.getAttribute('data-next-page');
                    const baseUrl = this.getAttribute('data-base-url');
                    
                    // Build URL with current filters
                    const urlParams = new URLSearchParams(window.location.search);
                    urlParams.set('page', nextPage);
                    const fetchUrl = `${baseUrl}?${urlParams.toString()}`;
                    
                    // Show loading, hide button
                    loadMoreBtn.style.display = 'none';
                    loadingSpinner.classList.remove('hidden');
                    
                    // Fetch next page
                    fetch(fetchUrl, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Append new products
                        const tempDiv = document.createElement('div');
                        tempDiv.innerHTML = data.html;
                        const newProducts = tempDiv.querySelectorAll('.product-item');
                        newProducts.forEach(product => {
                            productsContainer.appendChild(product);
                        });
                        
                        // Update button state
                        if (data.hasMore) {
                            loadMoreBtn.setAttribute('data-next-page', data.nextPage);
                            loadMoreBtn.style.display = 'inline-flex';
                        }
                        
                        loadingSpinner.classList.add('hidden');
                        
                        // Smooth scroll animation
                        setTimeout(() => {
                            newProducts[0]?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        }, 100);
                    })
                    .catch(error => {
                        console.error('Error loading products:', error);
                        loadingSpinner.classList.add('hidden');
                        loadMoreBtn.style.display = 'inline-flex';
                        alert('Failed to load more products. Please try again.');
                    });
                });
            }
        });
    </script>
    
    <!-- Quick View & Image Zoom JavaScript -->
    <script>
        function openQuickView(product) {
            const modal = document.getElementById('quickViewModal');
            
            // Set product data
            document.getElementById('qv-image').src = product.image;
            document.getElementById('qv-image').alt = product.name;
            document.getElementById('qv-name').textContent = product.name;
            document.getElementById('qv-category').textContent = product.category_name;
            document.getElementById('qv-description').textContent = product.description || 'No description available.';
            
            // Set badges
            const badgesContainer = document.getElementById('qv-badges');
            badgesContainer.innerHTML = '';
            if (product.has_discount) {
                badgesContainer.innerHTML += `<span class="px-3 py-1 bg-red-500 text-white text-xs font-bold rounded-full shadow-lg animate-pulse">-${product.discount_percentage}% OFF</span>`;
            }
            
            // Set price
            const priceContainer = document.getElementById('qv-price-container');
            let priceHTML = '';
            if (product.has_discount) {
                priceHTML = `
                    <p class="text-3xl font-bold text-violet-400">৳${parseFloat(product.display_price).toFixed(2)}</p>
                    <p class="text-lg text-gray-500 line-through">৳${parseFloat(product.price).toFixed(2)}</p>
                    <span class="px-2 py-1 bg-red-500/20 text-red-400 text-sm font-bold rounded">Save ${product.discount_percentage}%</span>
                `;
            } else {
                priceHTML = `<p class="text-3xl font-bold text-violet-400">৳${parseFloat(product.price).toFixed(2)}</p>`;
            }
            
            // Add offer countdown if active
            if (product.has_active_offer && product.offer_ends_at) {
                priceHTML += `
                    <div class="mt-3 bg-gradient-to-r from-orange-500/20 to-red-500/20 border border-orange-500/30 rounded-lg px-4 py-3">
                        <div class="flex items-center gap-2 text-sm mb-2">
                            <svg class="w-5 h-5 text-orange-400 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="text-orange-300 font-semibold">Limited Time Offer!</span>
                        </div>
                        <div class="offer-countdown" data-end-time="${product.offer_ends_at}">
                            <span class="countdown-timer text-white font-bold">Calculating...</span>
                        </div>
                    </div>
                `;
            }
            
            priceContainer.innerHTML = priceHTML;
            
            // Set stock status
            const stockContainer = document.getElementById('qv-stock');
            if (product.quantity > 0) {
                if (product.is_low_stock) {
                    stockContainer.innerHTML = `
                        <span class="relative flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-orange-500"></span>
                        </span>
                        <p class="text-sm font-semibold text-orange-400">⚠️ Only ${product.quantity} left in stock!</p>
                    `;
                } else {
                    stockContainer.innerHTML = `<p class="text-sm text-green-400">✓ In Stock (${product.quantity} available)</p>`;
                }
            } else {
                stockContainer.innerHTML = `<p class="text-sm text-red-400">✗ Out of Stock</p>`;
            }
            
            // Set form action and button
            const form = document.getElementById('qv-add-to-cart-form');
            const addButton = document.getElementById('qv-add-to-cart-btn');
            form.action = product.add_to_cart_url;
            
            if (product.quantity > 0) {
                addButton.disabled = false;
                addButton.classList.remove('opacity-50', 'cursor-not-allowed');
                addButton.innerHTML = `
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Add to Cart
                `;
            } else {
                addButton.disabled = true;
                addButton.classList.add('opacity-50', 'cursor-not-allowed');
                addButton.innerHTML = 'Out of Stock';
            }
            
            // Set view details link
            document.getElementById('qv-view-details').href = product.url;
            
            // Show modal
            modal.style.display = 'flex';
            setTimeout(() => modal.classList.add('modal-show'), 10);
        }
        
        function closeQuickView() {
            const modal = document.getElementById('quickViewModal');
            modal.classList.remove('modal-show');
            setTimeout(() => modal.style.display = 'none', 300);
        }
        
        // Close modal on background click
        document.getElementById('quickViewModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeQuickView();
            }
        });
        
        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeQuickView();
            }
        });
    </script>
    
    <!-- Stock Notification JavaScript -->
    <script>
        function openNotifyModal(productId, productName) {
            const modal = document.getElementById('notifyModal');
            document.getElementById('notify-product-id').value = productId;
            document.getElementById('notify-product-name').textContent = productName;
            document.getElementById('notify-success').classList.add('hidden');
            document.getElementById('notifyForm').reset();
            document.getElementById('notify-product-id').value = productId;
            @auth
            document.querySelector('#notifyForm input[name="email"]').value = '{{ auth()->user()->email }}';
            @endauth
            modal.style.display = 'flex';
            modal.classList.add('modal-show');
        }
        
        function closeNotifyModal() {
            const modal = document.getElementById('notifyModal');
            modal.classList.remove('modal-show');
            setTimeout(() => modal.style.display = 'none', 300);
        }
        
        // Handle form submission
        document.getElementById('notifyForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="flex items-center justify-center"><svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Processing...</span>';
            
            fetch('{{ route("stock-notification.store") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': formData.get('_token'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    product_id: formData.get('product_id'),
                    email: formData.get('email')
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('notifyForm').classList.add('hidden');
                    document.getElementById('notify-success').classList.remove('hidden');
                    setTimeout(() => {
                        closeNotifyModal();
                        setTimeout(() => {
                            document.getElementById('notifyForm').classList.remove('hidden');
                        }, 500);
                    }, 2000);
                } else {
                    alert(data.message || 'Something went wrong. Please try again.');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to submit request. Please try again.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
        
        // Close modal on background click
        document.getElementById('notifyModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeNotifyModal();
            }
        });
        
        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeNotifyModal();
            }
        });
    </script>
    
    <!-- Live Purchase Notification JavaScript -->
    <script>
        const locations = ['Dhaka', 'Chittagong', 'Sylhet', 'Rajshahi', 'Khulna', 'Barisal', 'Rangpur', 'Mymensingh'];
        const names = ['Fahim', 'Rafi', 'Adnan', 'Sakib', 'Mehedi', 'Tasnim', 'Nusrat', 'Anika', 'Rifat', 'Imran', 'Sadman', 'Tanvir'];
        
        let notificationShown = false;
        let notificationTimeout;
        
        function showLiveNotification() {
            if (notificationShown) return;
            
            // Fetch recent orders
            fetch('{{ route("shop.recent-purchases") }}')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.purchase) {
                        const purchase = data.purchase;
                        const notification = document.getElementById('liveNotification');
                        
                        // Use actual customer name or generate a random one
                        const customerName = purchase.customer_name || names[Math.floor(Math.random() * names.length)];
                        const location = purchase.location || locations[Math.floor(Math.random() * locations.length)];
                        
                        document.getElementById('live-customer-name').textContent = customerName;
                        document.getElementById('live-product-name').textContent = `just purchased ${purchase.product_name}`;
                        document.getElementById('live-time-ago').textContent = purchase.time_ago;
                        document.getElementById('live-location').textContent = location;
                        
                        // Show notification
                        notification.style.display = 'block';
                        setTimeout(() => {
                            notification.classList.add('show');
                            notification.style.transform = '';
                        }, 100);
                        
                        notificationShown = true;
                        
                        // Auto-hide after 6 seconds
                        notificationTimeout = setTimeout(() => {
                            closeLiveNotification();
                        }, 6000);
                    }
                })
                .catch(error => console.log('Failed to fetch recent purchases:', error));
        }
        
        function closeLiveNotification() {
            const notification = document.getElementById('liveNotification');
            notification.classList.remove('show');
            setTimeout(() => {
                notification.style.display = 'none';
                notificationShown = false;
            }, 500);
            clearTimeout(notificationTimeout);
        }
        
        // Show first notification after 5 seconds
        setTimeout(showLiveNotification, 5000);
        
        // Show subsequent notifications every 30 seconds
        setInterval(function() {
            if (!notificationShown) {
                showLiveNotification();
            }
        }, 30000);
    </script>
    
    <!-- Image Zoom CSS -->
    <style>
        /* Quick View Modal Animation */
        #quickViewModal {
            animation: fadeIn 0.3s ease-out;
        }
        
        #quickViewModal.modal-show {
            display: flex !important;
        }
        
        #quickViewModal > div {
            animation: slideUp 0.3s ease-out;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        
        @keyframes slideUp {
            from {
                transform: translateY(50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        /* Image Zoom on Hover */
        .product-card-zoom {
            cursor: pointer;
        }
        
        .product-image-zoom {
            transition: transform 0.5s ease;
        }
        
        .product-card-zoom:hover .product-image-zoom {
            transform: scale(1.15);
        }
        
        /* Smooth badge animations */
        @keyframes badge-pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }
        
        .animate-pulse {
            animation: badge-pulse 2s ease-in-out infinite;
        }
        
        /* Low stock urgency animation */
        @keyframes ping {
            75%, 100% {
                transform: scale(2);
                opacity: 0;
            }
        }
        
        .animate-ping {
            animation: ping 1s cubic-bezier(0, 0, 0.2, 1) infinite;
        }
        
        /* Desktop notification positioning */
        #liveNotification {
            transform: translateY(150%);
        }
        
        #liveNotification.show {
            transform: translateY(0);
        }
        
        /* Mobile Responsive Styles */
        @media (max-width: 768px) {
            /* Make Quick View Modal smaller on mobile */
            #quickViewModal > div {
                max-w-full;
                margin: 1rem;
                max-height: 85vh;
            }
            
            #quickViewModal .grid {
                grid-template-columns: 1fr;
                gap: 1rem;
                padding: 1rem;
            }
            
            #quickViewModal h1 {
                font-size: 1.25rem;
                line-height: 1.75rem;
            }
            
            #quickViewModal p {
                font-size: 0.875rem;
            }
            
            #quickViewModal button {
                padding: 0.5rem 1rem;
                font-size: 0.875rem;
            }
            
            #quickViewModal .absolute.top-4.right-4 {
                top: 0.5rem;
                right: 0.5rem;
            }
            
            #quickViewModal img {
                height: 200px;
            }
            
            /* Move live notification to top and make smaller on mobile */
            #liveNotification {
                bottom: auto !important;
                top: 1rem !important;
                left: 50% !important;
                transform: translateX(-50%) translateY(-200%) !important;
                width: calc(100% - 2rem);
                max-width: 320px;
            }
            
            #liveNotification.show {
                transform: translateX(-50%) translateY(0) !important;
            }
            
            #liveNotification .bg-gray-900 {
                padding: 0.75rem;
            }
            
            #liveNotification .flex-shrink-0 > div {
                width: 2.5rem;
                height: 2.5rem;
            }
            
            #liveNotification .flex-shrink-0 svg {
                width: 1.25rem;
                height: 1.25rem;
            }
            
            #liveNotification .text-sm {
                font-size: 0.75rem;
            }
            
            #liveNotification .text-xs {
                font-size: 0.625rem;
            }
            
            #liveNotification button svg {
                width: 1rem;
                height: 1rem;
            }
        }
    </style>
    
    <!-- Offer Countdown Timer Script -->
    <script>
        // Update all countdown timers
        function updateCountdowns() {
            const countdowns = document.querySelectorAll('.offer-countdown');
            const now = Math.floor(Date.now() / 1000);
            
            countdowns.forEach(countdown => {
                const endTime = parseInt(countdown.dataset.endTime);
                const timeLeft = endTime - now;
                
                if (timeLeft <= 0) {
                    countdown.querySelector('.countdown-timer').textContent = 'Offer Ended';
                    countdown.closest('.product-item')?.classList.add('offer-expired');
                    // Reload page to update prices
                    setTimeout(() => location.reload(), 2000);
                    return;
                }
                
                const days = Math.floor(timeLeft / 86400);
                const hours = Math.floor((timeLeft % 86400) / 3600);
                const minutes = Math.floor((timeLeft % 3600) / 60);
                const seconds = timeLeft % 60;
                
                let timerHTML = '';
                if (days > 0) {
                    timerHTML = `<span class="bg-orange-600/30 px-2 py-1 rounded">${days}d</span> <span class="bg-orange-600/30 px-2 py-1 rounded">${hours}h</span> <span class="bg-orange-600/30 px-2 py-1 rounded">${minutes}m</span>`;
                } else if (hours > 0) {
                    timerHTML = `<span class="bg-orange-600/30 px-2 py-1 rounded">${hours}h</span> <span class="bg-orange-600/30 px-2 py-1 rounded">${minutes}m</span> <span class="bg-orange-600/30 px-2 py-1 rounded">${seconds}s</span>`;
                } else {
                    timerHTML = `<span class="bg-red-600/30 px-2 py-1 rounded animate-pulse">${minutes}m</span> <span class="bg-red-600/30 px-2 py-1 rounded animate-pulse">${seconds}s</span>`;
                }
                
                countdown.querySelector('.countdown-timer').innerHTML = timerHTML;
            });
        }
        
        // Update every second
        if (document.querySelectorAll('.offer-countdown').length > 0) {
            updateCountdowns();
            setInterval(updateCountdowns, 1000);
        }
    </script>
    
    <!-- Include Styles -->
    @include('home.styles')
</body>
</html>

