<!-- Featured Products Section -->
<section id="products" class="py-20 bg-black">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-end mb-12">
            <div>
                <h2 class="text-4xl md:text-5xl font-bold mb-4">
                    <span class="glitch-text" data-text="Featured Products">Featured Products</span>
                </h2>
                <p class="text-xl text-gray-400">Premium Valorant collectibles for true fans</p>
            </div>
            <a href="{{ route('shop.index') }}" class="hidden md:inline-flex items-center px-6 py-3 bg-gradient-to-r from-violet-600 to-purple-600 text-white rounded-lg font-semibold hover:shadow-lg hover:shadow-violet-500/50 transition-all">
                View All
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>
        </div>

        @if($products->count() > 0)
            @if($products->count() > 4)
                <!-- Slideshow for more than 4 products -->
                <div class="relative featured-products-slideshow">
                    <div class="overflow-hidden">
                        <div class="featured-products-track flex transition-transform duration-500 ease-in-out">
                            @foreach($products->chunk(4) as $chunkIndex => $productChunk)
                                <div class="featured-products-slide w-full flex-shrink-0">
                                    <div class="hidden md:grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                                        @foreach($productChunk as $product)
                                            <a href="{{ route('shop.show', $product) }}" class="group block">
                                                <div class="relative overflow-hidden rounded-xl mb-4 bg-gray-900 border border-violet-500/20">
                                                    @php
                                                        $coverImage = $product->images->first()->path ?? $product->image;
                                                    @endphp
                                                    <img src="{{ $coverImage ? asset('storage/' . $coverImage) : '/img/placeholder.jpg' }}" alt="{{ $product->name }}" class="w-full h-72 object-cover group-hover:scale-110 transition-transform duration-500">
                                                    <div class="absolute inset-0 glitch-overlay opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                                    <div class="absolute top-4 right-4 z-10">
                                                        <button type="button" onclick="event.preventDefault(); event.stopPropagation();" class="w-10 h-10 bg-black/50 backdrop-blur-sm rounded-full flex items-center justify-center hover:bg-violet-600 hover:text-white transition-colors border border-violet-500/30">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="space-y-2">
                                                    <div class="flex items-center space-x-1">
                                                        @for($i = 1; $i <= 5; $i++)
                                                            <svg class="w-4 h-4 {{ $i <= $product->rating ? 'text-yellow-400' : 'text-gray-600' }} fill-current" viewBox="0 0 20 20">
                                                                <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                                            </svg>
                                                        @endfor
                                                        <span class="text-sm text-gray-400">({{ $product->reviews }})</span>
                                                    </div>
                                                    <h3 class="font-semibold text-white group-hover:text-violet-400 transition-colors">{{ $product->name }}</h3>
                                                    @if($product->price)
                                                        <p class="text-lg font-bold text-violet-400">৳{{ number_format($product->price, 2) }}</p>
                                                    @endif
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <!-- Mobile: Individual Product Cards -->
                    <div class="md:hidden featured-products-mobile-container relative">
                        @foreach($products as $index => $product)
                            <div class="featured-products-mobile-card {{ $index === 0 ? 'active' : '' }}" data-index="{{ $index }}">
                                <a href="{{ route('shop.show', $product) }}" class="group block">
                                    <div class="relative overflow-hidden rounded-xl mb-4 bg-gray-900 border border-violet-500/20">
                                        @php
                                            $coverImage = $product->images->first()->path ?? $product->image;
                                        @endphp
                                        <img src="{{ $coverImage ? asset('storage/' . $coverImage) : '/img/placeholder.jpg' }}" alt="{{ $product->name }}" class="w-full h-72 object-cover group-hover:scale-110 transition-transform duration-500">
                                        <div class="absolute inset-0 glitch-overlay opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                        <div class="absolute top-4 right-4 z-10">
                                            <button type="button" onclick="event.preventDefault(); event.stopPropagation();" class="w-10 h-10 bg-black/50 backdrop-blur-sm rounded-full flex items-center justify-center hover:bg-violet-600 hover:text-white transition-colors border border-violet-500/30">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="flex items-center space-x-1">
                                            @for($i = 1; $i <= 5; $i++)
                                                <svg class="w-4 h-4 {{ $i <= $product->rating ? 'text-yellow-400' : 'text-gray-600' }} fill-current" viewBox="0 0 20 20">
                                                    <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                                </svg>
                                            @endfor
                                            <span class="text-sm text-gray-400">({{ $product->reviews }})</span>
                                        </div>
                                        <h3 class="font-semibold text-white group-hover:text-violet-400 transition-colors">{{ $product->name }}</h3>
                                        @if($product->price)
                                            <p class="text-lg font-bold text-violet-400">৳{{ number_format($product->price, 2) }}</p>
                                        @endif
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                    
                    <!-- Navigation Arrows -->
                    <button class="featured-products-prev absolute left-0 top-1/2 -translate-y-1/2 -translate-x-4 bg-black/70 backdrop-blur-sm text-violet-400 w-12 h-12 rounded-full flex items-center justify-center hover:bg-violet-600 hover:text-white transition-all border border-violet-500/30 z-10">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <button class="featured-products-next absolute right-0 top-1/2 -translate-y-1/2 translate-x-4 bg-black/70 backdrop-blur-sm text-violet-400 w-12 h-12 rounded-full flex items-center justify-center hover:bg-violet-600 hover:text-white transition-all border border-violet-500/30 z-10">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                    
                    <!-- Dots Navigation (Desktop) -->
                    <div class="hidden md:flex justify-center mt-8 space-x-2">
                        @for($i = 0; $i < ceil($products->count() / 4); $i++)
                            <button class="featured-products-dot w-3 h-3 rounded-full {{ $i === 0 ? 'bg-violet-400' : 'bg-violet-500/40' }} transition-all" data-slide="{{ $i }}"></button>
                        @endfor
                    </div>
                    
                    <!-- Mobile Dots Navigation -->
                    <div class="md:hidden flex justify-center mt-8 space-x-2">
                        @foreach($products as $index => $product)
                            <button class="featured-mobile-dot w-3 h-3 rounded-full {{ $index === 0 ? 'bg-violet-400' : 'bg-violet-500/40' }} transition-all" data-slide="{{ $index }}"></button>
                        @endforeach
                    </div>
                </div>
            @else
                <!-- Grid for 4 or fewer products -->
                <div class="hidden md:grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                    @foreach($products as $product)
                        <a href="{{ route('shop.show', $product) }}" class="group block">
                            <div class="relative overflow-hidden rounded-xl mb-4 bg-gray-900 border border-violet-500/20">
                                @php
                                    $coverImage = $product->images->first()->path ?? $product->image;
                                @endphp
                                <img src="{{ $coverImage ? asset('storage/' . $coverImage) : '/img/placeholder.jpg' }}" alt="{{ $product->name }}" class="w-full h-72 object-cover group-hover:scale-110 transition-transform duration-500">
                                <div class="absolute inset-0 glitch-overlay opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                <div class="absolute top-4 right-4 z-10">
                                    <button type="button" onclick="event.preventDefault(); event.stopPropagation();" class="w-10 h-10 bg-black/50 backdrop-blur-sm rounded-full flex items-center justify-center hover:bg-violet-600 hover:text-white transition-colors border border-violet-500/30">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <div class="flex items-center space-x-1">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="w-4 h-4 {{ $i <= $product->rating ? 'text-yellow-400' : 'text-gray-600' }} fill-current" viewBox="0 0 20 20">
                                            <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                        </svg>
                                    @endfor
                                    <span class="text-sm text-gray-400">({{ $product->reviews }})</span>
                                </div>
                                <h3 class="font-semibold text-white group-hover:text-violet-400 transition-colors">{{ $product->name }}</h3>
                                @if($product->price)
                                    <p class="text-lg font-bold text-violet-400">৳{{ number_format($product->price, 2) }}</p>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
                
                <!-- Mobile: Individual Product Cards for 4 or fewer -->
                <div class="md:hidden featured-products-mobile-container-simple relative">
                    @foreach($products as $index => $product)
                        <div class="featured-products-mobile-card-simple {{ $index === 0 ? 'active' : '' }}" data-index="{{ $index }}">
                            <a href="{{ route('shop.show', $product) }}" class="group block">
                                <div class="relative overflow-hidden rounded-xl mb-4 bg-gray-900 border border-violet-500/20">
                                    @php
                                        $coverImage = $product->images->first()->path ?? $product->image;
                                    @endphp
                                    <img src="{{ $coverImage ? asset('storage/' . $coverImage) : '/img/placeholder.jpg' }}" alt="{{ $product->name }}" class="w-full h-72 object-cover group-hover:scale-110 transition-transform duration-500">
                                    <div class="absolute inset-0 glitch-overlay opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                    <div class="absolute top-4 right-4 z-10">
                                        <button type="button" onclick="event.preventDefault(); event.stopPropagation();" class="w-10 h-10 bg-black/50 backdrop-blur-sm rounded-full flex items-center justify-center hover:bg-violet-600 hover:text-white transition-colors border border-violet-500/30">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex items-center space-x-1">
                                        @for($i = 1; $i <= 5; $i++)
                                            <svg class="w-4 h-4 {{ $i <= $product->rating ? 'text-yellow-400' : 'text-gray-600' }} fill-current" viewBox="0 0 20 20">
                                                <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                            </svg>
                                        @endfor
                                        <span class="text-sm text-gray-400">({{ $product->reviews }})</span>
                                    </div>
                                    <h3 class="font-semibold text-white group-hover:text-violet-400 transition-colors">{{ $product->name }}</h3>
                                    @if($product->price)
                                        <p class="text-lg font-bold text-violet-400">৳{{ number_format($product->price, 2) }}</p>
                                    @endif
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
                
                <!-- Mobile Navigation Buttons for simple grid -->
                @if($products->count() > 1)
                    <div class="md:hidden relative mt-8">
                        <button class="featured-simple-prev absolute left-0 top-1/2 -translate-y-1/2 -translate-x-4 bg-black/70 backdrop-blur-sm text-violet-400 w-10 h-10 rounded-full flex items-center justify-center hover:bg-violet-600 hover:text-white transition-all border border-violet-500/30 z-10">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </button>
                        <button class="featured-simple-next absolute right-0 top-1/2 -translate-y-1/2 translate-x-4 bg-black/70 backdrop-blur-sm text-violet-400 w-10 h-10 rounded-full flex items-center justify-center hover:bg-violet-600 hover:text-white transition-all border border-violet-500/30 z-10">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                        
                        <!-- Dots -->
                        <div class="flex justify-center space-x-2">
                            @foreach($products as $index => $product)
                                <button class="featured-simple-dot w-3 h-3 rounded-full {{ $index === 0 ? 'bg-violet-400' : 'bg-violet-500/40' }} transition-all" data-slide="{{ $index }}"></button>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif
        @else
            <div class="text-center py-12">
                <p class="text-gray-400 text-lg">No featured products available.</p>
            </div>
        @endif
    </div>
</section>







