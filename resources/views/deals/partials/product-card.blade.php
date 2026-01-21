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
            @if($product->has_active_offer)
                <span class="px-2 py-1 bg-gradient-to-r from-orange-500 to-red-500 text-white text-xs font-bold rounded animate-pulse">
                    ⚡ FLASH DEAL
                </span>
            @endif
            @if($product->is_featured)
                <span class="px-2 py-1 bg-gradient-to-r from-yellow-500 to-orange-500 text-white text-xs font-bold rounded">
                    ⭐ FEATURED
                </span>
            @endif
            @if($product->has_discount)
                <span class="px-2 py-1 bg-red-500 text-white text-xs font-bold rounded">
                    -{{ $product->discount_percentage }}% OFF
                </span>
            @endif
        </div>
        
        <!-- Category Badge -->
        @if($product->category_name)
        <div class="absolute top-2 right-2">
            <span class="px-2 py-1 bg-black/60 backdrop-blur-sm text-violet-300 text-xs font-semibold rounded border border-violet-500/30">
                {{ $product->category_name }}
            </span>
        </div>
        @endif
    </div>

    <!-- Product Info -->
    <div class="p-4">
        <h4 class="text-white font-semibold mb-2 line-clamp-2 group-hover:text-violet-400 transition-colors">
            {{ $product->name }}
        </h4>
        
        @if($product->price_tba || $product->price == 0 || !$product->display_price)
            <div class="py-2">
                <p class="text-yellow-400 text-sm font-semibold">⏳ Price to be announced</p>
            </div>
        @elseif($product->display_price)
            <!-- Price Display -->
            <div class="flex items-center gap-2 mb-2">
                @if($product->has_discount)
                    <span class="text-gray-500 text-sm line-through">৳{{ number_format($product->price, 2) }}</span>
                    <span class="text-violet-400 font-bold text-lg">৳{{ number_format($product->display_price, 2) }}</span>
                @else
                    <span class="text-violet-400 font-bold text-lg">৳{{ number_format($product->price, 2) }}</span>
                @endif
            </div>
            
            <!-- Savings -->
            @if($product->has_discount)
            <p class="text-green-400 text-xs font-semibold mb-2">
                💰 Save ৳{{ number_format($product->price - $product->display_price, 2) }}
            </p>
            @endif
        @else
            <div class="py-2">
                <p class="text-yellow-400 text-sm font-semibold">⏳ Price to be announced</p>
            </div>
        @endif
        
        <!-- Countdown Timer for Limited Time Offers -->
        @if(isset($showTimer) && $showTimer && $product->has_active_offer)
        <div class="mt-2 p-2 bg-orange-500/10 border border-orange-500/30 rounded">
            <p class="text-xs text-orange-400 mb-1">⏰ Offer ends in:</p>
            <p class="text-orange-300 font-mono font-bold text-sm" data-countdown="{{ $product->offer_ends_at->toIso8601String() }}">
                Loading...
            </p>
        </div>
        @endif
        
        <!-- Stock Status -->
        <div class="mt-2">
            @if($product->quantity > 0)
                @if($product->is_low_stock)
                    <span class="text-xs text-orange-400 flex items-center gap-1">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-orange-500"></span>
                        </span>
                        Only {{ $product->quantity }} left!
                    </span>
                @else
                    <span class="text-xs text-green-400">✓ In Stock</span>
                @endif
            @else
                <span class="text-xs text-red-400">✗ Out of Stock</span>
            @endif
        </div>
    </div>
</a>
