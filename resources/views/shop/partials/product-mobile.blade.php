@foreach($products as $product)
<div class="bg-gray-900 border border-violet-500/20 rounded-xl overflow-hidden">
    <a href="{{ route('shop.show', $product) }}" class="block">
        <div class="relative">
            @php
                $cover = $product->images->first()?->path ?? $product->image;
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
                'has_discount' => $product->has_discount,
                'has_active_offer' => $product->has_active_offer,
                'offer_ends_at' => $product->has_active_offer ? $product->offer_ends_at->timestamp : null,
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
        
        <!-- Offer Countdown Timer -->
        @if($product->has_active_offer)
            <div class="bg-gradient-to-r from-orange-500/20 to-red-500/20 border border-orange-500/30 rounded-lg px-2 py-1.5">
                <div class="flex items-center gap-1 text-[10px]">
                    <svg class="w-3 h-3 text-orange-400 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-orange-300 font-semibold">Ends in:</span>
                </div>
                <div class="mt-0.5 offer-countdown text-[10px] font-bold text-white" 
                     data-end-time="{{ $product->offer_ends_at->timestamp }}">
                    <span class="countdown-timer">Calculating...</span>
                </div>
            </div>
        @endif
        
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
@endforeach
