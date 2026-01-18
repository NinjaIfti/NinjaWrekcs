@foreach($products as $product)
<div class="product-item group">
    <a href="{{ route('shop.show', $product) }}" class="block">
        <div class="relative overflow-hidden rounded-xl mb-4 bg-gray-900 border border-violet-500/20 product-card-zoom">
            @php
                $cover = $product->images->first()->path ?? $product->image;
            @endphp
            <img src="{{ $cover ? asset('storage/' . $cover) : '/img/placeholder.jpg' }}" 
                 alt="{{ $product->name }}" 
                 class="w-full h-72 object-cover group-hover:scale-110 transition-transform duration-500 product-image-zoom">
            <div class="absolute inset-0 glitch-overlay opacity-0 group-hover:opacity-100 transition-opacity"></div>
            
            <!-- Product Badges -->
            <div class="absolute top-4 left-4 z-10 flex flex-col gap-2">
                @if($product->has_discount)
                    <span class="px-3 py-1 bg-red-500 text-white text-xs font-bold rounded-full shadow-lg animate-pulse">
                        -{{ $product->discount_percentage }}% OFF
                    </span>
                @endif
                @if($product->is_new)
                    <span class="px-3 py-1 bg-gradient-to-r from-green-500 to-emerald-500 text-white text-xs font-bold rounded-full shadow-lg">
                        🆕 NEW
                    </span>
                @endif
                @if($product->is_bestseller)
                    <span class="px-3 py-1 bg-gradient-to-r from-yellow-500 to-orange-500 text-white text-xs font-bold rounded-full shadow-lg">
                        🔥 BEST SELLER
                    </span>
                @endif
                @if($product->is_limited_edition)
                    <span class="px-3 py-1 bg-gradient-to-r from-purple-500 to-pink-500 text-white text-xs font-bold rounded-full shadow-lg">
                        ⭐ LIMITED
                    </span>
                @endif
            </div>
            
            <!-- Wishlist & Quick View Buttons -->
            <div class="absolute top-4 right-4 z-10 flex gap-2">
                <button class="w-10 h-10 bg-black/50 backdrop-blur-sm rounded-full flex items-center justify-center hover:bg-violet-600 hover:text-white transition-colors border border-violet-500/30" onclick="event.preventDefault();" title="Add to Wishlist">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </button>
                <button class="w-10 h-10 bg-black/50 backdrop-blur-sm rounded-full flex items-center justify-center hover:bg-violet-600 hover:text-white transition-colors border border-violet-500/30 quick-view-btn" 
                        onclick="event.preventDefault(); openQuickView({{ json_encode([
                            'id' => $product->id,
                            'name' => $product->name,
                            'description' => $product->description,
                            'price' => $product->price,
                            'sale_price' => $product->sale_price,
                            'display_price' => $product->display_price,
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
                        ]) }});" 
                        title="Quick View">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
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
        
        <!-- Price with Discount -->
        <div class="flex items-center gap-2">
            @if($product->has_discount)
                <p class="text-lg font-bold text-violet-400">৳{{ number_format($product->display_price, 2) }}</p>
                <p class="text-sm text-gray-500 line-through">৳{{ number_format($product->price, 2) }}</p>
            @else
                <p class="text-lg font-bold text-violet-400">৳{{ number_format($product->price, 2) }}</p>
            @endif
        </div>
        
        <!-- Stock Status with Urgency -->
        @if($product->quantity > 0)
            @if($product->is_low_stock)
                <div class="flex items-center gap-2">
                    <span class="relative flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-orange-500"></span>
                    </span>
                    <p class="text-sm font-semibold text-orange-400">Only {{ $product->quantity }} left!</p>
                </div>
            @else
                <p class="text-sm text-green-400">✓ In Stock</p>
            @endif
        @else
            <p class="text-sm text-red-400">✗ Out of Stock</p>
        @endif
    </div>
</div>
@endforeach
