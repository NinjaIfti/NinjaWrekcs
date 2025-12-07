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
            <a href="#" class="hidden md:inline-flex items-center px-6 py-3 bg-gradient-to-r from-violet-600 to-purple-600 text-white rounded-lg font-semibold hover:shadow-lg hover:shadow-violet-500/50 transition-all">
                View All
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-8">
            @php
                $products = [
                    ['name' => 'Youru Butterfly Comb',  'discount' => 17, 'rating' => 5, 'reviews' => 128, 'image' => '/img/f1.jpg', 'badge' => 'sale'],
                    ['name' => 'Karambit Knife Replica', 'price' => 79.99, 'old_price' => null, 'discount' => null, 'rating' => 5, 'reviews' => 89, 'image' => '/img/f2.jpg', 'badge' => 'new'],
                    ['name' => 'Kuronami', 'price' => 19.99, 'old_price' => null, 'discount' => null, 'rating' => 4, 'reviews' => 256, 'image' => '/img/f3.jpg', 'badge' => null],
                    ['name' => 'Gekko Figure with his pets', 'price' => 24.99, 'old_price' => 34.99, 'discount' => 29, 'rating' => 4, 'reviews' => 74, 'image' => '/img/f4.jpg', 'badge' => 'sale'],
                ];
            @endphp

            @foreach($products as $product)
            <div class="group">
                <div class="relative overflow-hidden rounded-xl mb-4 bg-gray-900 border border-violet-500/20">
                    <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" class="w-full h-72 object-cover group-hover:scale-110 transition-transform duration-500">
                    <div class="absolute inset-0 glitch-overlay opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="absolute top-4 right-4 z-10">
                        <button class="w-10 h-10 bg-black/50 backdrop-blur-sm rounded-full flex items-center justify-center hover:bg-violet-600 hover:text-white transition-colors border border-violet-500/30">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                        </button>
                    </div>
                    {{-- @if($product['badge'])
                    <div class="absolute top-4 left-4 z-10">
                        <span class="px-3 py-1 {{ $product['badge'] === 'sale' ? 'bg-red-500' : 'bg-green-500' }} text-white text-sm font-semibold rounded-full">
                            {{ $product['badge'] === 'sale' ? '-'.$product['discount'].'%' : 'New' }}
                        </span>
                    </div>
                    @endif --}}
                </div>
                <div class="space-y-2">
                    <div class="flex items-center space-x-1">
                        @for($i = 1; $i <= 5; $i++)
                            <svg class="w-4 h-4 {{ $i <= $product['rating'] ? 'text-yellow-400' : 'text-gray-600' }} fill-current" viewBox="0 0 20 20">
                                <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                            </svg>
                        @endfor
                        <span class="text-sm text-gray-400">({{ $product['reviews'] }})</span>
                    </div>
                    <h3 class="font-semibold text-white group-hover:text-violet-400 transition-colors">{{ $product['name'] }}</h3>
                    {{-- <div class="flex items-center space-x-2">
                        <span class="text-lg font-bold text-white">${{ number_format($product['price'], 2) }}</span>
                        @if($product['old_price'])
                            <span class="text-sm text-gray-500 line-through">${{ number_format($product['old_price'], 2) }}</span>
                        @endif
                    </div> --}}
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

