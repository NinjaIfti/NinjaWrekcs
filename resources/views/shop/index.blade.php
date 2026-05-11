<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        @if($selectedCategory && $selectedCategory->parent)
            {{ $selectedCategory->parent->name }} - {{ $selectedCategory->name }} | NinjaWrecks
        @elseif($selectedCategory)
            {{ $selectedCategory->name }} - NinjaWrecks
        @else
            Shop - NinjaWrecks
        @endif
    </title>
    <link rel="icon" type="image/png" href="{{ asset('img/fav.png') }}">
    
    @include('components.seo', [
        'title' => 'Shop Valorant Collectibles - NinjaWrecks | Agent Figures, Knives & Weapons',
        'description' => 'Browse our complete collection of Valorant collectibles. Shop agent figures, knives, weapons, stickers, and keychains. Get special discounts.',
        'url' => route('shop.index'),
        'keywords' => 'Valorant shop, buy Valorant collectibles, Valorant agent figures, Valorant knives, Valorant weapons, Valorant merchandise store, Bangladesh'
    ])
    
    @include('components.analytics')
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="antialiased bg-black text-white">
    <!-- Include Navigation -->
    @include('home.components.navigation')
    
    <!-- Shop Section -->
    <section class="pt-20 md:pt-28 pb-20 min-h-screen bg-gradient-to-b from-black via-violet-950/50 to-black overflow-visible" x-data="{ 
        filtersCollapsed: true 
    }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 overflow-visible">
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
                    @if($selectedCategory->parent)
                        <span class="text-gray-400">{{ $selectedCategory->parent->name }}</span>
                        <svg class="w-4 h-4 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    @endif
                    <span class="text-white font-semibold">{{ $selectedCategory->name }}</span>
                @else
                    <span class="text-white font-semibold">Shop</span>
                @endif
            </nav>

            <!-- Show Main Categories OR Subcategories based on selection -->
            @if(!$selectedCategoryId && !$search)
                <!-- Main Category Cards - Show when NO category selected and not searching -->
                <div class="mb-12">
                    <div class="text-center mb-12">
                        <h1 class="text-5xl md:text-6xl font-bold mb-4">
                            <span class="glitch-text-large" data-text="Shop">Shop</span>
                        </h1>
                        <p class="text-xl text-gray-400">Choose a category to start shopping</p>
                    </div>
                    
                    <div class="grid md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                        @forelse($categories as $parentCategory)
                        @php
                            $categoryImage = 'val.jpeg';
                            if ($parentCategory->slug === 'valorant') {
                                $categoryImage = 'val.jpeg';
                            } elseif ($parentCategory->slug === 'csgo') {
                                $categoryImage = 'csgo.jpg';
                            } elseif ($parentCategory->slug === 'toys') {
                                $categoryImage = 'toys.jpg';
                            } elseif ($parentCategory->slug === 'pre-order-upcoming') {
                                $categoryImage = 'up.jpeg';
                            }
                        @endphp
                        <a href="{{ route('shop.index', ['category_id' => $parentCategory->id]) }}#products-section" 
                           class="group relative overflow-hidden rounded-3xl shadow-2xl hover:shadow-violet-500/50 transition-all duration-500 border-2 border-violet-500/30 hover:border-violet-500 hover:scale-105 transform {{ $parentCategory->slug === 'pre-order-upcoming' ? 'md:col-span-3' : '' }}"
                           onclick="handleCategoryClick(event, {{ $parentCategory->id }})">
                            <div class="relative h-80 bg-cover bg-center bg-no-repeat" 
                                 style="background-image: url('{{ asset('img/' . $categoryImage) }}');">
                                <!-- Animated Background Overlay -->
                                <div class="absolute inset-0 opacity-30">
                                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent transform -skew-x-12 group-hover:translate-x-full transition-transform duration-1000"></div>
                                </div>
                                
                                <!-- Dark Gradient Overlay for Text Readability -->
                                <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/50 to-transparent"></div>
                                <div class="absolute inset-0 glitch-overlay opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                
                                <div class="absolute bottom-0 left-0 right-0 p-8 z-10">
                                    <h3 class="text-3xl font-bold text-white mb-3 group-hover:text-violet-300 transition-colors">
                                        {{ $parentCategory->name }}
                                    </h3>
                                    <p class="text-gray-300 mb-4">
                                       
                                            Browse collection
                                        
                                    </p>
                                    <div class="flex items-center text-violet-400 font-semibold group-hover:translate-x-2 transition-transform">
                                        Explore
                                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </a>
                        @empty
                        <div class="col-span-full text-center py-12">
                            <p class="text-gray-400 text-lg mb-4">No categories available at the moment.</p>
                            <p class="text-gray-500 text-sm">Please check that categories are marked as active in the database.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            @else
                <!-- Show Selected Category Products -->
            <div id="products-section" class="mb-8 overflow-visible">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">
                        <span class="glitch-text" data-text="{{ $selectedCategory ? $selectedCategory->name : ($search ? 'Search Results' : 'Shop') }}">
                            {{ $selectedCategory ? $selectedCategory->name : ($search ? 'Search Results' : 'Shop') }}
                        </span>
                </h1>
                    <div class="flex items-center gap-4 mb-8">
                        <a href="{{ route('shop.index') }}" class="text-violet-400 hover:text-violet-300 transition flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Back to Categories
                        </a>
                    </div>
                </div>
                
                <!-- Search and Sort Bar -->
                <div class="flex gap-4 items-center overflow-visible">
                    <!-- Search Box -->
                    <form action="{{ route('shop.index') }}" method="GET" class="flex-1 max-w-xl">
                        <div class="relative">
                            <label for="desktop-search" class="sr-only">Search products</label>
                            <input type="text" 
                                   id="desktop-search"
                                   name="search" 
                                   value="{{ $search }}"
                                   placeholder="Search products..." 
                                   autocomplete="off"
                                   class="w-full px-4 py-3 pl-12 bg-black/50 border border-violet-500/30 rounded-lg text-white placeholder-gray-500 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/50 transition">
                            <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            @if($search)
                                <a href="{{ route('shop.index', $selectedCategoryId ? ['category_id' => $selectedCategoryId] : []) }}" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-white">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </a>
                            @endif
                        </div>
                        <!-- Hidden inputs to preserve other filters -->
                        @if($selectedCategoryId)<input type="hidden" name="category_id" value="{{ $selectedCategoryId }}">@endif
                        @if($minPrice)<input type="hidden" name="min_price" value="{{ $minPrice }}">@endif
                        @if($maxPrice)<input type="hidden" name="max_price" value="{{ $maxPrice }}">@endif
                        @if($sort !== 'newest')<input type="hidden" name="sort" value="{{ $sort }}">@endif
                        @if($inStock)<input type="hidden" name="in_stock" value="1">@endif
                    </form>
                    
                    <!-- Sort Dropdown -->
                    <div class="relative" x-data="{ open: false }" style="z-index: 50;">
                        <button @click.stop="open = !open" 
                                type="button"
                                class="px-4 py-3 bg-black/50 border border-violet-500/30 rounded-lg text-white hover:border-violet-500 transition flex items-center gap-2 whitespace-nowrap">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/>
                            </svg>
                            <span>Sort</span>
                            <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        
                        <div x-show="open" 
                             x-cloak
                             @click.away="open = false"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 transform scale-100"
                             x-transition:leave-end="opacity-0 transform scale-95"
                             class="absolute right-0 top-full mt-2 w-48 sm:w-56 bg-gray-900 border border-violet-500/30 rounded-lg shadow-xl"
                             style="z-index: 9999;">
                            <div class="py-2">
                                <a href="{{ route('shop.index', array_merge(request()->except('sort'), [])) }}" 
                                   @click="open = false"
                                   class="block px-4 py-2 hover:bg-violet-500/20 transition {{ $sort === 'newest' ? 'text-violet-400' : 'text-gray-300' }}">
                                    Newest First
                                </a>
                                <a href="{{ route('shop.index', array_merge(request()->all(), ['sort' => 'price_asc'])) }}" 
                                   @click="open = false"
                                   class="block px-4 py-2 hover:bg-violet-500/20 transition {{ $sort === 'price_asc' ? 'text-violet-400' : 'text-gray-300' }}">
                                    Price: Low to High
                                </a>
                                <a href="{{ route('shop.index', array_merge(request()->all(), ['sort' => 'price_desc'])) }}" 
                                   @click="open = false"
                                   class="block px-4 py-2 hover:bg-violet-500/20 transition {{ $sort === 'price_desc' ? 'text-violet-400' : 'text-gray-300' }}">
                                    Price: High to Low
                                </a>
                                <a href="{{ route('shop.index', array_merge(request()->all(), ['sort' => 'name_asc'])) }}" 
                                   @click="open = false"
                                   class="block px-4 py-2 hover:bg-violet-500/20 transition {{ $sort === 'name_asc' ? 'text-violet-400' : 'text-gray-300' }}">
                                    Name: A to Z
                                </a>
                                <a href="{{ route('shop.index', array_merge(request()->all(), ['sort' => 'name_desc'])) }}" 
                                   @click="open = false"
                                   class="block px-4 py-2 hover:bg-violet-500/20 transition {{ $sort === 'name_desc' ? 'text-violet-400' : 'text-gray-300' }}">
                                    Name: Z to A
                                </a>
                                <a href="{{ route('shop.index', array_merge(request()->all(), ['sort' => 'popular'])) }}" 
                                   @click="open = false"
                                   class="block px-4 py-2 hover:bg-violet-500/20 transition {{ $sort === 'popular' ? 'text-violet-400' : 'text-gray-300' }}">
                                    Most Popular
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
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

            <!-- Main Layout (responsive for all devices) -->
            <div class="flex flex-col lg:flex-row gap-8 mt-6 max-w-6xl mx-auto">
                @php
                    // Check if we're viewing Valorant category or its subcategories
                    $isValorantCategory = false;
                    $valorantParentId = null;
                    
                    if ($selectedCategory) {
                        if ($selectedCategory->slug === 'valorant') {
                            $isValorantCategory = true;
                            $valorantParentId = $selectedCategory->id;
                        } elseif ($selectedCategory->parent && $selectedCategory->parent->slug === 'valorant') {
                            $isValorantCategory = true;
                            $valorantParentId = $selectedCategory->parent->id;
                        }
                    }
                @endphp

                <!-- Filters Sidebar - Show for all categories -->
                <aside class="lg:w-80 flex-shrink-0">
                    <!-- Mobile Filter Toggle Button -->
                    <div class="lg:hidden mb-4">
                        <button @click="filtersCollapsed = !filtersCollapsed" 
                                class="w-full flex items-center justify-between px-4 py-3 bg-black/40 backdrop-blur-xl rounded-xl border border-violet-500/20 text-white hover:bg-violet-500/10 transition">
                            <span class="font-semibold">{{ $isValorantCategory ? 'Valorant Filters' : 'Filters' }}</span>
                            <svg class="w-5 h-5 transition-transform" :class="{ 'rotate-180': !filtersCollapsed }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Filter Content -->
                    <div class="bg-black/40 backdrop-blur-xl rounded-2xl border border-violet-500/20 shadow-xl shadow-violet-500/10 p-6 sticky top-28 lg:block"
                         :class="{ 'hidden': filtersCollapsed }"
                         x-show="!filtersCollapsed || window.innerWidth >= 1024"
                         x-transition>
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-xl font-bold text-white">{{ $isValorantCategory ? 'Valorant Filters' : 'Filters' }}</h2>
                            @if($search || ($selectedCategoryId && !$isValorantCategory) || ($isValorantCategory && $selectedCategoryId) || $minPrice || $maxPrice || $inStock)
                                @php
                                    // Preserve category when clearing filters
                                    $clearParams = [];
                                    if ($isValorantCategory && $valorantParentId) {
                                        // For Valorant, preserve parent category
                                        $clearParams['category_id'] = $valorantParentId;
                                    } elseif ($selectedCategoryId && !$isValorantCategory) {
                                        // For non-Valorant, preserve current category
                                        $clearParams['category_id'] = $selectedCategoryId;
                                    }
                                @endphp
                                <a href="{{ route('shop.index', $clearParams) }}" class="text-sm text-red-400 hover:text-red-300 transition">
                                    Clear
                                </a>
                            @endif
                        </div>
                        
                        <form action="{{ route('shop.index') }}" method="GET" class="space-y-6">
                            <!-- Preserve search -->
                            @if($search)
                                <input type="hidden" name="search" value="{{ $search }}">
                            @endif
                            
                            <!-- Valorant Type Filter - Only for Valorant -->
                            @if($isValorantCategory)
                            <div>
                                <h3 class="text-sm font-semibold text-gray-300 mb-3 uppercase">Type</h3>
                                <div class="space-y-2">
                                    <!-- Preserve parent category -->
                                    <input type="hidden" name="category_id" value="{{ $valorantParentId }}">
                                    
                                    @foreach($categories as $parentCategory)
                                        @if($parentCategory->slug === 'valorant' && $parentCategory->hasChildren())
                                            @foreach($parentCategory->children as $childCategory)
                                                <label class="flex items-center justify-between cursor-pointer group">
                                                    <div class="flex items-center">
                                                        <input type="radio" name="category_id" value="{{ $childCategory->id }}" {{ $selectedCategoryId == $childCategory->id ? 'checked' : '' }} class="mr-3 text-violet-600 focus:ring-violet-500" onchange="this.form.submit()">
                                                        <span class="text-gray-400 group-hover:text-white transition-colors">{{ $childCategory->name }}</span>
                                                    </div>
                                                    <span class="px-2 py-0.5 bg-violet-500/20 text-violet-300 text-xs font-semibold rounded-full">
                                                        {{ $categoryCounts[$childCategory->id] ?? 0 }}
                                                    </span>
                                                </label>
                                            @endforeach
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                            @else
                            <!-- Preserve category for non-Valorant -->
                            @if($selectedCategoryId)
                                <input type="hidden" name="category_id" value="{{ $selectedCategoryId }}">
                            @endif
                            @endif
                            
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
                <div class="flex-1 lg:px-0">
                    <!-- Results Count -->
                    <div class="mb-6 px-4 sm:px-0">
                        <p class="text-gray-400">
                            Showing <span class="text-white font-semibold">{{ $products->count() }}</span> of <span class="text-white font-semibold">{{ $products->total() }}</span> {{ $products->total() === 1 ? 'product' : 'products' }}
                        </p>
                    </div>

                    <div id="products-container" 
                         class="grid grid-cols-2 lg:grid-cols-2 gap-2.5 sm:gap-3 lg:gap-6 px-2 sm:px-0">
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
                        <a id="qv-add-to-cart-link" href="#" class="flex-1 hidden px-6 py-3 bg-gradient-to-r from-violet-600 to-purple-600 text-white rounded-lg font-semibold hover:shadow-lg hover:shadow-violet-500/50 transition-all flex items-center justify-center text-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            Add to Cart
                        </a>
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
            const price = parseFloat(product.price) || 0;
            const displayPrice = product.display_price ? parseFloat(product.display_price) : null;
            
            if (product.price_tba || price === 0 || !displayPrice) {
                priceHTML = `
                    <p class="text-2xl font-bold text-yellow-400 mb-2">⏳ Price to be announced</p>
                    <p class="text-sm text-gray-400">We're finalizing the pricing for this product. Please check back soon!</p>
                `;
            } else if (product.has_discount) {
                priceHTML = `
                    <p class="text-3xl font-bold text-violet-400">৳${displayPrice.toFixed(2)}</p>
                    <p class="text-lg text-gray-500 line-through">৳${price.toFixed(2)}</p>
                    <span class="px-2 py-1 bg-red-500/20 text-red-400 text-sm font-bold rounded">Save ${product.discount_percentage}%</span>
                `;
            } else {
                priceHTML = `<p class="text-3xl font-bold text-violet-400">৳${price.toFixed(2)}</p>`;
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
            
            // Set form / link for Add to Cart (keychains go to product page)
            const form = document.getElementById('qv-add-to-cart-form');
            const addLink = document.getElementById('qv-add-to-cart-link');
            const addButton = document.getElementById('qv-add-to-cart-btn');
            
            if (product.is_keychain && product.quantity > 0) {
                addLink.href = product.url;
                addLink.classList.remove('hidden');
                form.classList.add('hidden');
            } else {
                addLink.classList.add('hidden');
                form.classList.remove('hidden');
                form.action = product.add_to_cart_url;
                const canAddToCart = product.quantity > 0 && !product.price_tba && price > 0 && displayPrice;
                if (canAddToCart) {
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
                    if (product.quantity <= 0) {
                        addButton.innerHTML = 'Out of Stock';
                    } else if (product.price_tba || price === 0 || !displayPrice) {
                        addButton.innerHTML = 'Price to be announced';
                    } else {
                        addButton.innerHTML = 'Unavailable';
                    }
                }
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
            
            /* Fix sort dropdown on mobile - ensure containers allow overflow */
            .flex.gap-4.items-center,
            .flex.gap-4.items-center > *,
            #products-section,
            #products-section > * {
                overflow: visible !important;
            }
            
            /* Ensure sort dropdown displays correctly */
            [x-data] .absolute.top-full {
                position: absolute !important;
                z-index: 9999 !important;
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
        
        // Auto-scroll to products section on mobile when category is selected
        function scrollToProductsOnMobile() {
            // Check if we're on mobile and have a category selected
            const urlParams = new URLSearchParams(window.location.search);
            const categoryId = urlParams.get('category_id');
            const hash = window.location.hash;
            
            // Check if we should scroll (category selected or hash present)
            const shouldScroll = (categoryId || hash === '#products-section') && window.innerWidth < 768;
            
            if (!shouldScroll) return;
            
            // Try to find products section with multiple attempts
            function attemptScroll(attempt = 0) {
                const productsSection = document.getElementById('products-section');
                
                if (productsSection) {
                    // Element found, scroll to it
                    const topOffset = 80; // Offset for mobile logo bar
                    const elementPosition = productsSection.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.pageYOffset - topOffset;
                    
                    window.scrollTo({
                        top: Math.max(0, offsetPosition),
                        behavior: 'smooth'
                    });
                } else if (attempt < 10) {
                    // Element not found yet, try again after a short delay
                    setTimeout(() => attemptScroll(attempt + 1), 100);
                }
            }
            
            // Start attempting to scroll after a small initial delay
            setTimeout(() => attemptScroll(0), 200);
        }
        
        // Handle category card click
        function handleCategoryClick(event, categoryId) {
            // On mobile, prevent default and handle navigation manually with scroll
            if (window.innerWidth < 768) {
                event.preventDefault();
                const url = new URL(window.location.href);
                url.searchParams.set('category_id', categoryId);
                // Remove hash, we'll scroll with JS
                url.hash = '';
                window.location.href = url.toString();
            }
            // On desktop, let the default link behavior work
        }
        
        // Run on page load
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                scrollToProductsOnMobile();
            });
        } else {
            // DOM already loaded, run immediately
            scrollToProductsOnMobile();
        }
        
        // Also run when page is fully loaded (all images, etc.)
        window.addEventListener('load', function() {
            scrollToProductsOnMobile();
        });
        
        // Run multiple times with delays to catch dynamic content
        setTimeout(scrollToProductsOnMobile, 300);
        setTimeout(scrollToProductsOnMobile, 600);
        setTimeout(scrollToProductsOnMobile, 1000);
    </script>
    
    <!-- Include Styles -->
    @include('home.styles')
</body>
</html>

