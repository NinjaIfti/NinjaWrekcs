@foreach($products as $product)
<div class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 border border-gray-700/50 rounded-2xl overflow-hidden shadow-lg hover:shadow-xl hover:shadow-violet-500/10 transition-all duration-300 aspect-square flex flex-col">
    <a href="{{ route('shop.show', $product) }}" class="block flex-1 flex flex-col">
        <div class="relative overflow-hidden bg-gradient-to-br from-black to-gray-900 flex-1">
            @php
                $cover = $product->images->first()?->path ?? $product->image;
            @endphp
            <img src="{{ $cover ? asset('storage/' . $cover) : '/img/placeholder.jpg' }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
            
            <!-- Product Badges -->
            <div class="absolute top-3 left-3 flex flex-col gap-2 z-10">
                @if($product->has_discount)
                    <span class="px-2.5 py-1 bg-red-500 text-white text-[10px] font-bold rounded-full shadow-lg backdrop-blur-sm">
                        -{{ $product->discount_percentage }}%
                    </span>
                @endif
                @if($product->is_new)
                    <span class="px-2.5 py-1 bg-green-500 text-white text-[10px] font-bold rounded-full shadow-lg backdrop-blur-sm">
                        NEW
                    </span>
                @endif
            </div>
            
            <!-- Out of Stock Overlay -->
            @if($product->quantity <= 0)
                <div class="absolute inset-0 bg-black/85 flex items-center justify-center backdrop-blur-sm">
                    <span class="text-red-400 font-bold text-base uppercase tracking-wider px-4 py-2 bg-red-500/20 rounded-lg border border-red-500/30">
                        Out of Stock
                    </span>
                </div>
            @endif
            
            <!-- Quick View Button (Eye Icon) -->
            <button onclick="event.preventDefault(); openQuickView({{ json_encode([
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'sale_price' => $product->sale_price,
                'display_price' => $product->display_price,
                'price_tba' => $product->price_tba,
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
            ]) }});" 
            class="absolute top-3 right-3 w-9 h-9 bg-black/70 backdrop-blur-md rounded-full flex items-center justify-center text-white hover:bg-violet-600/90 hover:scale-110 transition-all shadow-lg border border-white/10 z-10">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
            </button>
        </div>
    </a>
    
    <div class="p-3 space-y-2 flex-shrink-0">
        <!-- Product Name -->
        <a href="{{ route('shop.show', $product) }}">
            <h3 class="text-xs font-semibold text-white line-clamp-2 leading-tight hover:text-violet-400 transition-colors">
                {{ $product->name }}
            </h3>
        </a>
        
        <!-- Price -->
        <div class="flex items-center gap-2">
            @if($product->price_tba || $product->price == 0 || !$product->display_price)
                <p class="text-[10px] font-medium text-yellow-400 bg-yellow-500/10 px-2 py-0.5 rounded-full border border-yellow-500/20">
                    ⏳ Price TBA
                </p>
            @elseif($product->has_discount)
                <div class="flex items-baseline gap-1.5">
                    <p class="text-sm font-bold text-white">৳{{ number_format($product->display_price, 2) }}</p>
                    <p class="text-[10px] text-gray-500 line-through">৳{{ number_format($product->price, 2) }}</p>
                </div>
            @else
                <p class="text-sm font-bold text-white">৳{{ number_format($product->price, 2) }}</p>
            @endif
        </div>
        
        <!-- Stock Status & Add Button -->
        <div class="flex items-center justify-between pt-1.5 border-t border-gray-700/50">
            <!-- Stock Status -->
            <div class="flex items-center gap-1.5">
                @if($product->quantity > 0)
                    @if($product->is_low_stock)
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-500 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-orange-500"></span>
                        </span>
                        <span class="text-[11px] font-semibold text-orange-400">{{ $product->quantity }} Left</span>
                    @else
                        <span class="text-[11px] font-medium text-green-400">In Stock</span>
                    @endif
                @else
                    <span class="text-[11px] font-semibold text-red-400">✗ Out of Stock</span>
                @endif
            </div>
            
            <!-- Add Button -->
            @if($product->quantity > 0 && !$product->price_tba && $product->price > 0 && $product->display_price)
                <form action="{{ route('cart.add', $product) }}" method="POST" onclick="event.stopPropagation();" class="ml-auto">
                    @csrf
                    <button type="submit" class="px-4 py-1.5 text-xs font-semibold bg-gradient-to-r from-violet-600 to-pink-600 text-white rounded-full hover:from-violet-500 hover:to-pink-500 hover:shadow-lg hover:shadow-violet-500/50 hover:scale-105 transition-all shadow-md">
                        ADD
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
@endforeach
