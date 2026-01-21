<div class="space-y-6 md:hidden" x-data="{ filtersOpen: false }">
    <!-- Breadcrumbs Mobile -->
    <nav class="flex items-center text-xs text-gray-400 mb-4" aria-label="Breadcrumb">
        <a href="{{ url('/') }}" class="hover:text-violet-400 transition-colors">Home</a>
        <svg class="w-3 h-3 mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        @if($selectedCategory)
            <a href="{{ route('shop.index') }}" class="hover:text-violet-400 transition-colors">Shop</a>
            <svg class="w-3 h-3 mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            @if($selectedCategory->parent)
                <span class="text-gray-400">{{ $selectedCategory->parent->name }}</span>
                <svg class="w-3 h-3 mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            @endif
            <span class="text-white font-semibold">{{ $selectedCategory->name }}</span>
        @else
            <span class="text-white font-semibold">Shop</span>
        @endif
    </nav>

    <!-- Header -->
    <div class="flex items-center justify-between mb-4">
        <div class="space-y-1">
            <span class="glitch-text-large block text-3xl font-bold" data-text="Shop">Shop</span>
            <p class="text-gray-400 text-sm">Browse by category</p>
        </div>
    </div>

    <!-- Main Category Cards (always visible on mobile) -->
    <div class="grid grid-cols-1 gap-4 mb-6">
        @foreach($categories as $parentCategory)
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
        <a href="{{ route('shop.index', ['category_id' => $parentCategory->id]) }}" 
           class="group relative overflow-hidden rounded-xl shadow-lg hover:shadow-2xl hover:shadow-violet-500/50 transition-all duration-300 border-2 border-violet-500/20">
            <div class="relative h-32 bg-cover bg-center bg-no-repeat" 
                 style="background-image: url('{{ asset('img/' . $categoryImage) }}');">
                
                <!-- Dark Gradient Overlay for Text Readability -->
                <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent"></div>
                
                <div class="absolute bottom-0 left-0 right-0 p-4 z-10 flex items-end justify-between">
                    <div>
                        <h3 class="text-xl font-bold text-white mb-1 group-hover:text-violet-300 transition-colors">
                            {{ $parentCategory->name }}
                        </h3>
                        <p class="text-gray-300 text-xs">
                            @php
                                $productCount = 0;
                                if ($parentCategory->hasChildren()) {
                                    foreach ($parentCategory->children as $child) {
                                        $productCount += $categoryCounts[$child->id] ?? 0;
                                    }
                                } else {
                                    $productCount = $categoryCounts[$parentCategory->id] ?? 0;
                                }
                            @endphp
                            {{ $productCount }} products
                        </p>
                    </div>
                </div>
            </div>
        </a>
        @endforeach
    </div>

    <!-- Search Bar -->
    <form action="{{ route('shop.index') }}" method="GET" class="relative">
        <label for="mobile-search" class="sr-only">Search products</label>
        <input type="text" 
               id="mobile-search"
               name="search" 
               value="{{ $search }}"
               placeholder="Search products..." 
               autocomplete="off"
               class="w-full px-4 py-3 pl-12 bg-gray-900 border border-violet-500/30 rounded-lg text-white placeholder-gray-500 text-sm focus:border-violet-500 focus:ring-2 focus:ring-violet-500/50">
        <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <!-- Preserve other filters -->
        @if($selectedCategoryId)<input type="hidden" name="category_id" value="{{ $selectedCategoryId }}">@endif
        @if($minPrice)<input type="hidden" name="min_price" value="{{ $minPrice }}">@endif
        @if($maxPrice)<input type="hidden" name="max_price" value="{{ $maxPrice }}">@endif
        @if($sort !== 'newest')<input type="hidden" name="sort" value="{{ $sort }}">@endif
        @if($inStock)<input type="hidden" name="in_stock" value="1">@endif
    </form>

    <!-- Filter & Sort Bar -->
    <div class="flex items-center gap-3">
        <button @click="filtersOpen = !filtersOpen" class="flex-1 px-4 py-2 bg-gray-900 border border-violet-500/30 rounded-lg text-white text-sm font-semibold flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
            </svg>
            Filters
            @if($selectedCategoryId || $minPrice || $maxPrice || $inStock)
                <span class="px-2 py-0.5 bg-violet-500 text-white rounded-full text-xs">{{ collect([$selectedCategoryId, $minPrice, $maxPrice, $inStock])->filter()->count() }}</span>
            @endif
        </button>
        
        <select name="sort" onchange="window.location.href = updateURLParameter(window.location.href, 'sort', this.value)" class="flex-1 px-4 py-2 bg-gray-900 border border-violet-500/30 rounded-lg text-white text-sm">
            <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Newest</option>
            <option value="price_asc" {{ $sort === 'price_asc' ? 'selected' : '' }}>Price: Low-High</option>
            <option value="price_desc" {{ $sort === 'price_desc' ? 'selected' : '' }}>Price: High-Low</option>
            <option value="name_asc" {{ $sort === 'name_asc' ? 'selected' : '' }}>Name: A-Z</option>
            <option value="popular" {{ $sort === 'popular' ? 'selected' : '' }}>Popular</option>
        </select>
    </div>

    <!-- Active Filters -->
    @if($search || $selectedCategory || $minPrice || $maxPrice || $inStock)
    <div class="flex items-center gap-2 flex-wrap">
        @if($search)
            <a href="{{ route('shop.index', array_merge(request()->except('search'), [])) }}" class="px-2 py-1 bg-violet-500/20 border border-violet-500/50 rounded-full text-xs text-violet-300 flex items-center gap-1">
                "{{ Str::limit($search, 15) }}"
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </a>
        @endif
        @if($selectedCategory)
            <a href="{{ route('shop.index', array_merge(request()->except('category_id'), [])) }}" class="px-2 py-1 bg-violet-500/20 border border-violet-500/50 rounded-full text-xs text-violet-300 flex items-center gap-1">
                {{ $selectedCategory->name }}
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </a>
        @endif
        @if($minPrice || $maxPrice)
            <a href="{{ route('shop.index', array_merge(request()->except(['min_price', 'max_price']), [])) }}" class="px-2 py-1 bg-violet-500/20 border border-violet-500/50 rounded-full text-xs text-violet-300 flex items-center gap-1">
                ৳{{ $minPrice ?: 0 }}-{{ $maxPrice ?: '∞' }}
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </a>
        @endif
        @if($inStock)
            <a href="{{ route('shop.index', array_merge(request()->except('in_stock'), [])) }}" class="px-2 py-1 bg-violet-500/20 border border-violet-500/50 rounded-full text-xs text-violet-300 flex items-center gap-1">
                In Stock
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </a>
        @endif
        <a href="{{ route('shop.index', $selectedCategoryId ? ['category_id' => $selectedCategoryId] : []) }}" class="px-2 py-1 text-xs text-red-400">Clear All</a>
    </div>
    @endif

    <!-- Filters Popup -->
    <div x-show="filtersOpen" 
         @click.away="filtersOpen = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-95"
         class="fixed inset-0 z-50 flex items-end"
         style="display: none;">
        
        <!-- Overlay -->
        <div class="absolute inset-0 bg-black/70" @click="filtersOpen = false"></div>
        
        <!-- Filter Panel -->
        <div class="relative w-full bg-gray-900 rounded-t-3xl max-h-[80vh] overflow-y-auto">
            <form action="{{ route('shop.index') }}" method="GET" class="p-6 space-y-6">
                <!-- Header -->
                <div class="flex items-center justify-between pb-4 border-b border-violet-500/30">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        <h3 class="text-xl font-bold text-white">Filters</h3>
                    </div>
                    <button type="button" @click="filtersOpen = false" class="w-10 h-10 bg-black/50 rounded-full flex items-center justify-center text-gray-400 hover:text-white hover:bg-red-500 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                @if($search)<input type="hidden" name="search" value="{{ $search }}">@endif
                
                <!-- Category -->
                <div>
                    <h4 class="text-sm font-semibold text-gray-300 mb-3 uppercase">Category</h4>
                    <div class="space-y-2">
                        <label class="flex items-center justify-between cursor-pointer group">
                            <div class="flex items-center">
                                <input type="radio" name="category_id" value="" {{ !$selectedCategoryId ? 'checked' : '' }} class="mr-3 text-violet-600">
                                <span class="text-gray-400 group-hover:text-white transition-colors">All Products</span>
                            </div>
                            <span class="px-2 py-0.5 bg-violet-500/20 text-violet-300 text-xs font-semibold rounded-full">
                                {{ array_sum($categoryCounts ?? []) }}
                            </span>
                        </label>
                        
                        @foreach($categories as $parentCategory)
                            @if($parentCategory->hasChildren())
                                <!-- Parent Category Header -->
                                <div class="mt-3 mb-2">
                                    <span class="text-xs font-bold text-violet-400 uppercase tracking-wider">{{ $parentCategory->name }}</span>
                                </div>
                                
                                @foreach($parentCategory->children as $childCategory)
                                    <label class="flex items-center justify-between cursor-pointer group pl-3">
                                        <div class="flex items-center">
                                            <input type="radio" name="category_id" value="{{ $childCategory->id }}" {{ $selectedCategoryId == $childCategory->id ? 'checked' : '' }} class="mr-3 text-violet-600">
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
                
                <!-- Price Range -->
                <div>
                    <h4 class="text-sm font-semibold text-gray-300 mb-3 uppercase">Price Range</h4>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block">Min (৳)</label>
                            <input type="number" name="min_price" value="{{ $minPrice }}" placeholder="0" class="w-full px-3 py-2 bg-black/50 border border-violet-500/30 rounded-lg text-white text-sm">
                        </div>
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block">Max (৳)</label>
                            <input type="number" name="max_price" value="{{ $maxPrice }}" placeholder="10000" class="w-full px-3 py-2 bg-black/50 border border-violet-500/30 rounded-lg text-white text-sm">
                        </div>
                    </div>
                    @if($priceRange)
                    <p class="text-xs text-gray-500 mt-2">Range: ৳{{ number_format($priceRange->min, 0) }} - ৳{{ number_format($priceRange->max, 0) }}</p>
                    @endif
                </div>
                
                <!-- Availability -->
                <div>
                    <h4 class="text-sm font-semibold text-gray-300 mb-3 uppercase">Availability</h4>
                    <label class="flex items-center">
                        <input type="checkbox" name="in_stock" value="1" {{ $inStock ? 'checked' : '' }} class="mr-3 rounded text-violet-600">
                        <span class="text-gray-400">In Stock Only</span>
                    </label>
                </div>
                
                @if($sort && $sort !== 'newest')<input type="hidden" name="sort" value="{{ $sort }}">@endif
                
                <!-- Buttons -->
                <div class="flex gap-3">
                    <a href="{{ route('shop.index') }}" class="flex-1 px-4 py-3 bg-gray-800 text-white rounded-lg text-center font-semibold">
                        Reset
                    </a>
                    <button type="submit" class="flex-1 px-4 py-3 bg-gradient-to-r from-violet-600 to-purple-600 text-white rounded-lg font-semibold">
                        Apply
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Products Grid -->
    <div id="mobile-products-container" class="grid grid-cols-2 gap-4">
        @forelse($products as $product)
            <div class="bg-gray-900 border border-violet-500/20 rounded-xl overflow-hidden">
                <a href="{{ route('shop.show', $product) }}" class="block">
                    <div class="relative">
                        @php
                            $cover = $product->images->first()->path ?? $product->image;
                        @endphp
                        <img src="{{ $cover ? asset('storage/' . $cover) : '/img/placeholder.jpg' }}" alt="{{ $product->name }}" class="w-full h-40 object-cover">
                        
                        <!-- Product Badges -->
                        <div class="absolute top-2 left-2 flex flex-col gap-1">
                            @if($product->has_discount)
                                <span class="px-2 py-0.5 bg-red-500 text-white text-[10px] font-bold rounded-full shadow-lg">
                                    -{{ $product->discount_percentage }}%
                                </span>
                            @endif
                            @if($product->is_new)
                                <span class="px-2 py-0.5 bg-green-500 text-white text-[10px] font-bold rounded-full shadow-lg">
                                    NEW
                                </span>
                            @endif
                            @if($product->is_bestseller)
                                <span class="px-2 py-0.5 bg-orange-500 text-white text-[10px] font-bold rounded-full shadow-lg">
                                    🔥
                                </span>
                            @endif
                            @if($product->is_limited_edition)
                                <span class="px-2 py-0.5 bg-purple-500 text-white text-[10px] font-bold rounded-full shadow-lg">
                                    ⭐
                                </span>
                            @endif
                        </div>
                        
                        <!-- Quick View Button -->
                        <button onclick="event.preventDefault(); openQuickView({{ json_encode([
                            'id' => $product->id,
                            'name' => $product->name,
                            'description' => $product->description,
                            'price' => $product->price,
                            'sale_price' => $product->sale_price,
                            'display_price' => $product->display_price,
                            'price_tba' => $product->price_tba,
                            'has_discount' => $product->has_discount,
                            'discount_percentage' => $product->discount_percentage,
                            'quantity' => $product->quantity,
                            'is_low_stock' => $product->is_low_stock,
                            'rating' => $product->rating,
                            'reviews' => $product->reviews,
                            'category_name' => $product->category_name,
                            'image' => $cover ? asset('storage/' . $cover) : '/img/placeholder.jpg',
                            'url' => route('shop.show', $product),
                            'add_to_cart_url' => route('cart.add', $product)
                        ]) }});" class="absolute top-2 right-2 w-8 h-8 bg-black/50 backdrop-blur-sm rounded-full flex items-center justify-center text-white hover:bg-violet-600 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                        
                        @if($product->quantity <= 0)
                            <div class="absolute inset-0 bg-black/70 flex items-center justify-center text-red-300 font-semibold text-sm">
                                Out of Stock
                            </div>
                        @endif
                    </div>
                </a>
                <div class="p-3 space-y-2">
                    <a href="{{ route('shop.show', $product) }}">
                        <h3 class="text-sm font-semibold text-white line-clamp-2">{{ $product->name }}</h3>
                    </a>
                    
                    <!-- Price with Discount -->
                    <div class="flex items-center gap-1 flex-wrap">
                        @if($product->has_discount)
                            <p class="text-base font-bold text-violet-400">৳{{ number_format($product->display_price, 2) }}</p>
                            <p class="text-xs text-gray-500 line-through">৳{{ number_format($product->price, 2) }}</p>
                        @else
                            <p class="text-base font-bold text-violet-400">৳{{ number_format($product->price, 2) }}</p>
                        @endif
                    </div>
                    
                    <!-- Stock Status with Urgency -->
                    <div class="flex items-center justify-between">
                        @if($product->quantity > 0)
                            @if($product->is_low_stock)
                                <div class="flex items-center gap-1">
                                    <span class="relative flex h-2 w-2">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-orange-500"></span>
                                    </span>
                                    <span class="text-xs font-semibold text-orange-400">{{ $product->quantity }} left</span>
                                </div>
                            @else
                                <span class="text-xs text-green-400">✓ In Stock</span>
                            @endif
                        @else
                            <span class="text-xs text-red-300">✗ Out of stock</span>
                        @endif
                        
                        @if($product->quantity > 0)
                            <form action="{{ route('cart.add', $product) }}" method="POST" onclick="event.stopPropagation();">
                                @csrf
                                <button type="submit" class="px-3 py-2 text-xs font-semibold bg-gradient-to-r from-violet-600 to-purple-600 text-white rounded-lg hover:scale-105 transition">
                                    Add
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-2 text-center py-10">
                <svg class="mx-auto w-16 h-16 text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-gray-400 text-sm mb-4">No products found</p>
                <a href="{{ route('shop.index') }}" class="inline-block px-5 py-2 bg-violet-600 text-white rounded-lg text-sm hover:bg-violet-700 transition">
                    Clear Filters
                </a>
            </div>
        @endforelse
    </div>

    <!-- Load More Button -->
    @if($products->hasMorePages())
    <div class="text-center mt-8 mb-8" id="mobile-load-more-container">
        <button id="mobile-load-more-btn" 
                class="w-full px-8 py-3 bg-gradient-to-r from-violet-600 to-purple-600 text-white rounded-lg font-semibold hover:shadow-lg hover:scale-105 transition-all inline-flex items-center justify-center gap-2"
                data-next-page="{{ $products->currentPage() + 1 }}"
                data-base-url="{{ route('shop.index') }}">
            <span>Load More Products</span>
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
        <div id="mobile-loading-spinner" class="hidden mt-4">
            <div class="inline-flex items-center gap-2 px-8 py-3 bg-gray-800 text-gray-400 rounded-lg">
                <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Loading...
            </div>
        </div>
    </div>
    @else
    <!-- Debug: No more pages -->
    <div class="text-center mt-8 mb-8 text-gray-500 text-sm">
        <!-- Showing all {{ $products->total() }} products (Page {{ $products->currentPage() }} of {{ $products->lastPage() }}) -->
    </div>
    @endif
</div>

<script>
function updateURLParameter(url, param, value) {
    const urlObj = new URL(url);
    if (value) {
        urlObj.searchParams.set(param, value);
    } else {
        urlObj.searchParams.delete(param);
    }
    return urlObj.toString();
}

// Mobile Load More Functionality
document.addEventListener('DOMContentLoaded', function() {
    const loadMoreBtn = document.getElementById('mobile-load-more-btn');
    const loadingSpinner = document.getElementById('mobile-loading-spinner');
    const productsContainer = document.getElementById('mobile-products-container');
    
    console.log('Mobile Load More:', { 
        button: !!loadMoreBtn, 
        spinner: !!loadingSpinner, 
        container: !!productsContainer 
    });
    
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function() {
            const nextPage = this.getAttribute('data-next-page');
            const baseUrl = this.getAttribute('data-base-url');
            
            // Build URL with current filters and mobile view type
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('page', nextPage);
            urlParams.set('view_type', 'mobile');
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
                const newProducts = tempDiv.children;
                
                Array.from(newProducts).forEach(product => {
                    productsContainer.appendChild(product);
                });
                
                // Update button state
                if (data.hasMore) {
                    loadMoreBtn.setAttribute('data-next-page', data.nextPage);
                    loadMoreBtn.style.display = 'inline-flex';
                } else {
                    // Remove button container if no more pages
                    document.getElementById('mobile-load-more-container').remove();
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

// Offer Countdown Timer Script
function updateCountdowns() {
    const countdowns = document.querySelectorAll('.offer-countdown');
    const now = Math.floor(Date.now() / 1000);
    
    countdowns.forEach(countdown => {
        const endTime = parseInt(countdown.dataset.endTime);
        const timeLeft = endTime - now;
        
        if (timeLeft <= 0) {
            countdown.querySelector('.countdown-timer').textContent = 'Offer Ended';
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
            timerHTML = `<span class="bg-orange-600/30 px-1 py-0.5 rounded">${days}d</span> <span class="bg-orange-600/30 px-1 py-0.5 rounded">${hours}h</span>`;
        } else if (hours > 0) {
            timerHTML = `<span class="bg-orange-600/30 px-1 py-0.5 rounded">${hours}h</span> <span class="bg-orange-600/30 px-1 py-0.5 rounded">${minutes}m</span>`;
        } else {
            timerHTML = `<span class="bg-red-600/30 px-1 py-0.5 rounded animate-pulse">${minutes}m</span> <span class="bg-red-600/30 px-1 py-0.5 rounded animate-pulse">${seconds}s</span>`;
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
