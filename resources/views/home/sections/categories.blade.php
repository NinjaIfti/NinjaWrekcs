<!-- Categories Section with Products -->
<section id="categories" class="py-20 bg-gradient-to-b from-black via-violet-950/50 to-black">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl md:text-5xl font-bold mb-4">
                <span class="glitch-text" data-text="Shop By Category">Shop By Category</span>
            </h2>
            <p class="text-xl text-gray-400">Explore our gaming collectibles</p>
        </div>
        
        @foreach($categories as $category)
        <div class="mb-20 last:mb-0">
            <!-- Category Header -->
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h3 class="text-3xl md:text-4xl font-bold text-white">
                        <span class="glitch-text-small" data-text="{{ $category->name }}">{{ $category->name }}</span>
                    </h3>
                    <p class="text-gray-400 mt-2">{{ $category->products->count() }} products available</p>
                </div>
                <a href="{{ route('shop.index', ['category_id' => $category->id]) }}" 
                   class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-violet-600 to-purple-600 text-white rounded-lg font-semibold hover:shadow-lg hover:shadow-violet-500/50 transition-all">
                    View All
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                </a>
            </div>

            <!-- Products Grid -->
            @if($category->products->count() > 0)
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                @foreach($category->products as $product)
                <a href="{{ route('shop.show', $product->id) }}" 
                   class="group relative bg-gray-900/50 border border-violet-500/20 rounded-xl overflow-hidden hover:border-violet-500/50 hover:shadow-lg hover:shadow-violet-500/30 transition-all duration-300">
                    <!-- Product Image -->
                    <div class="relative aspect-square overflow-hidden bg-black/50">
                        @if($product->images && $product->images->first())
                            <img src="{{ Storage::url($product->images->first()->path) }}" 
                                 alt="{{ $product->name }}" 
                                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-gray-500">
                                No Image
                            </div>
                        @endif
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        
                        <!-- Badges -->
                        <div class="absolute top-2 left-2 flex flex-col gap-1">
                            @if($product->is_preorder)
                                <span class="px-2 py-1 bg-purple-500 text-white text-xs font-bold rounded">PRE-ORDER</span>
                            @endif
                            @if($product->is_upcoming)
                                <span class="px-2 py-1 bg-blue-500 text-white text-xs font-bold rounded">UPCOMING</span>
                            @endif
                            @if($product->has_discount)
                                <span class="px-2 py-1 bg-red-500 text-white text-xs font-bold rounded">-{{ $product->discount_percentage }}%</span>
                            @endif
                        </div>
                    </div>

                    <!-- Product Info -->
                    <div class="p-4">
                        <h4 class="text-white font-semibold mb-2 line-clamp-2 group-hover:text-violet-400 transition-colors">
                            {{ $product->name }}
                        </h4>
                        
                        @if($product->price_tba)
                            <p class="text-yellow-400 text-sm font-semibold">⏳ Price will be announced soon</p>
                        @elseif($product->display_price)
                            <div class="flex items-center gap-2">
                                @if($product->has_discount)
                                    <span class="text-gray-500 text-sm line-through">৳{{ number_format($product->price, 2) }}</span>
                                    <span class="text-violet-400 font-bold">৳{{ number_format($product->display_price, 2) }}</span>
                                @else
                                    <span class="text-violet-400 font-bold">৳{{ number_format($product->price, 2) }}</span>
                                @endif
                            </div>
                        @else
                            <p class="text-yellow-400 text-sm font-semibold">⏳ Price will be announced soon</p>
                        @endif
                        
                        <!-- Stock Status -->
                        <div class="mt-2">
                            @if($product->quantity > 0)
                                @if($product->is_low_stock)
                                    <span class="text-xs text-orange-400">Only {{ $product->quantity }} left</span>
                                @else
                                    <span class="text-xs text-green-400">In Stock</span>
                                @endif
                            @else
                                <span class="text-xs text-red-400">Out of Stock</span>
                            @endif
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
            @else
            <div class="text-center py-12 border-2 border-dashed border-gray-700 rounded-xl">
                <svg class="w-16 h-16 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                <p class="text-gray-500">No products in this category yet</p>
            </div>
            @endif
        </div>
        @endforeach
    </div>
</section>

