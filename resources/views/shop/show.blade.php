<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $product->name }} - NinjaWrecks | Valorant Collectibles</title>
    <link rel="icon" type="image/png" href="{{ asset('img/fav.png') }}">
    
    @include('components.seo', [
        'title' => $product->name . ' - NinjaWrecks | Valorant Collectibles',
        'description' => $product->description ? \Illuminate\Support\Str::limit(strip_tags($product->description), 160) : 'Buy ' . $product->name . ' - Authentic Valorant collectible. Get 100 taka off plus 10% discount. Fast delivery across Bangladesh.',
        'image' => $product->image ? asset('storage/' . $product->image) : asset('img/fav.png'),
        'url' => route('shop.show', $product->slug ?? $product->id),
        'type' => 'product',
        'keywords' => $product->name . ', Valorant collectibles, ' . ($product->category ?? 'gaming merchandise') . ', Valorant Bangladesh, Bangladesh gaming store',
        'product' => $product
    ])
    
    @include('components.analytics')
    
    @php
        $viewItemPrice = $product->variants->first()->price ?? $product->display_price ?? $product->price;
    @endphp
    <x-data-layer :payload="[
        'event' => 'view_item',
        'ecommerce' => [
            'currency' => 'BDT',
            'value' => (float) $viewItemPrice,
            'items' => [[
                'item_id' => (string) $product->id,
                'item_name' => $product->name,
                'item_category' => $product->category ?? 'Valorant Collectibles',
                'price' => (float) $viewItemPrice,
                'quantity' => 1,
            ]],
        ],
    ]" />
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-black text-white">
    @include('components.analytics-noscript')
    <!-- Include Navigation -->
    @include('home.components.navigation')
    
    <!-- Product Detail Section -->
    <section class="pt-16 md:pt-28 pb-20 min-h-screen bg-gradient-to-b from-black via-violet-950/50 to-black">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Back Button -->
            <a href="{{ route('shop.index') }}" class="inline-flex items-center text-violet-400 hover:text-violet-300 mb-8 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Shop
            </a>

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

            <div class="grid lg:grid-cols-2 gap-12">
                <!-- Product Images -->
                <div class="relative">
                    @php
                        $hasVariants = $product->variants->isNotEmpty();
                        if ($hasVariants && $product->cover_photo) {
                            $gallery = collect([(object)['path' => $product->cover_photo]]);
                            $firstVariant = $product->variants->first();
                            foreach ($firstVariant->images as $img) {
                                $gallery->push($img);
                            }
                        } elseif ($hasVariants && $product->variants->first() && $product->variants->first()->images->isNotEmpty()) {
                            $gallery = $product->variants->first()->images;
                        } else {
                            $gallery = $product->images->count() ? $product->images : collect($product->image ? [(object)['path' => $product->image]] : []);
                        }
                    @endphp
                    <div class="relative rounded-2xl overflow-hidden border border-violet-500/30 bg-gray-900" id="product-gallery-wrap">
                        @if($gallery->count())
                            <div class="product-slideshow" id="product-slideshow">
                                @foreach($gallery as $idx => $img)
                                    <div class="product-slide {{ $idx === 0 ? 'active' : '' }}">
                                        <img src="{{ asset('storage/' . $img->path) }}" alt="{{ $product->name }}" class="w-full h-auto object-cover">
                                        <div class="absolute inset-0 glitch-overlay opacity-30"></div>
                                    </div>
                                @endforeach
                            </div>
                            @if($gallery->count() > 1)
                            <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex space-x-2 z-10">
                                @foreach($gallery as $idx => $_)
                                    <button class="product-dot {{ $idx === 0 ? 'bg-violet-400' : 'bg-violet-500/40' }}" data-slide="{{ $idx }}" aria-label="Slide {{ $idx + 1 }}"></button>
                                @endforeach
                            </div>
                            <button class="product-nav product-prev" aria-label="Previous">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </button>
                            <button class="product-nav product-next" aria-label="Next">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                            @endif
                        @else
                            <img src="/img/placeholder.jpg" alt="{{ $product->name }}" class="w-full h-auto object-cover">
                        @endif
                    </div>
                </div>

                <!-- Product Details -->
                <div class="space-y-6">
                    <!-- Category Badge -->
                    <div>
                        <span class="px-4 py-2 bg-violet-500/20 text-violet-300 rounded-full text-sm font-semibold border border-violet-500/30">
                            {{ $product->category_name }}
                        </span>
                    </div>

                    <!-- Product Name -->
                    <h1 class="text-4xl md:text-5xl font-bold text-white">
                        {{ $product->name }}
                    </h1>

                    @php
                        $activeVariants = $product->variants->where('is_active', true);
                        // Never preselect a sold-out option.
                        $defaultVariant = $activeVariants->firstWhere('quantity', '>', 0);
                    @endphp

                    @if($hasVariants)
                    <!-- Variant swatches -->
                    <div class="space-y-3">
                        <label class="block text-sm font-medium text-gray-400">Choose an option</label>

                        <div class="flex flex-wrap gap-3" id="variant-swatches">
                            @foreach($activeVariants as $v)
                                @php
                                    $inStock = $v->quantity > 0;
                                    $vPrice = $v->sale_price && $v->sale_price < $v->price ? $v->sale_price : $v->price;
                                @endphp
                                <button type="button"
                                        class="variant-swatch relative px-3 py-3 rounded-lg border-2 text-center transition
                                               {{ $inStock ? 'border-violet-500/30 hover:border-violet-500 cursor-pointer' : 'border-gray-700 opacity-40 cursor-not-allowed' }}
                                               {{ $defaultVariant && $defaultVariant->id === $v->id ? 'border-violet-500 bg-violet-500/10' : '' }}"
                                        data-variant-id="{{ $v->id }}"
                                        data-price="{{ $vPrice }}"
                                        data-in-stock="{{ $inStock ? 1 : 0 }}"
                                        data-stock="{{ $v->quantity }}"
                                        data-images="{{ $v->images->map(fn($i) => asset('storage/'.$i->path))->values()->toJson() }}"
                                        @disabled(! $inStock)>
                                    @if($v->images->isNotEmpty())
                                        <img src="{{ asset('storage/' . $v->images->first()->path) }}" alt="{{ $v->name }}"
                                             class="w-14 h-14 object-cover rounded mb-1 mx-auto">
                                    @endif
                                    <span class="block text-sm {{ $inStock ? 'text-white' : 'text-gray-500 line-through' }}">{{ $v->name }}</span>
                                    <span class="block text-xs text-violet-300">৳{{ number_format($vPrice, 2) }}</span>
                                    @unless($inStock)
                                        <span class="block text-[10px] text-red-400 uppercase tracking-wide">Sold out</span>
                                    @endunless
                                </button>
                            @endforeach
                        </div>

                        @unless($defaultVariant)
                            <p class="text-sm text-red-400">Every option is currently sold out.</p>
                        @endunless
                    </div>
                    @endif

                    <!-- Price -->
                    {{-- A variant product's price lives on its variants, so its own
                         price column is 0 by design - $hasVariants, not the column,
                         decides whether there is a price to show. --}}
                    @if($hasVariants || $product->display_price)
                    <div class="space-y-3" id="price-block">
                        <div class="flex items-center gap-3">
                            @if($hasVariants)
                                <div class="text-3xl font-bold text-violet-400" id="variant-price">৳{{ number_format($defaultVariant ? ($defaultVariant->sale_price && $defaultVariant->sale_price < $defaultVariant->price ? $defaultVariant->sale_price : $defaultVariant->price) : ($activeVariants->first()->price ?? 0), 2) }}</div>
                            @elseif($product->has_discount)
                                <div class="text-3xl font-bold text-violet-400">
                                    ৳{{ number_format($product->display_price, 2) }}
                                </div>
                                <div class="text-xl text-gray-500 line-through">
                                    ৳{{ number_format($product->price, 2) }}
                                </div>
                                <span class="px-3 py-1 bg-red-500/20 text-red-400 text-sm font-bold rounded-full">
                                    Save {{ $product->discount_percentage }}%
                                </span>
                            @else
                                <div class="text-3xl font-bold text-violet-400">
                                    ৳{{ number_format($product->price, 2) }}
                                </div>
                            @endif
                        </div>
                        
                        <!-- Offer Countdown Timer -->
                        @if($product->has_active_offer)
                            <div class="bg-gradient-to-r from-orange-500/20 to-red-500/20 border-2 border-orange-500/40 rounded-xl px-5 py-4 shadow-lg">
                                <div class="flex items-center gap-3 mb-3">
                                    <svg class="w-6 h-6 text-orange-400 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span class="text-orange-300 font-bold text-lg">Limited Time Offer!</span>
                                </div>
                                <div class="flex items-center gap-2 text-sm text-orange-200 mb-2">
                                    <span>⚡ This special price ends in:</span>
                                </div>
                                <div class="offer-countdown text-2xl font-bold text-white" 
                                     data-end-time="{{ $product->offer_ends_at->timestamp }}">
                                    <span class="countdown-timer">Calculating...</span>
                                </div>
                            </div>
                        @endif
                    </div>
                    @endif

                    <!-- Stock Status -->
                    {{-- A variant product holds no stock of its own - its quantity
                         column is 0 by design, so read the selected variant. The
                         swatch JS rewrites this block when another colour is picked. --}}
                    @php $stockOnShow = $hasVariants ? $product->availableStock($defaultVariant) : $product->availableStock(); @endphp
                    <div id="stock-status">
                        @if($stockOnShow > 0)
                            <p class="text-lg text-violet-400 font-semibold">✓ In Stock ({{ $stockOnShow }} available)</p>
                        @else
                            <p class="text-lg text-red-400 font-semibold">✗ Out of Stock</p>
                        @endif
                    </div>

                    <!-- Description -->
                    @if($product->description)
                    <div class="border-t border-violet-500/20 pt-6">
                        <h2 class="text-xl font-bold mb-4">Description</h2>
                        <p class="text-gray-300 leading-relaxed">{{ $product->description }}</p>
                    </div>
                    @endif

                    <!-- Notes -->
                    @if($product->notes)
                    <div class="border-t border-violet-500/20 pt-6">
                        <h2 class="text-xl font-bold mb-4">Additional Notes</h2>
                        <p class="text-gray-300 leading-relaxed">{{ $product->notes }}</p>
                    </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="border-t border-violet-500/20 pt-6 space-y-4">
                        @php $canAddToCart = $stockOnShow > 0 && !$product->is_upcoming && ($hasVariants || ($product->price > 0 && $product->display_price)); @endphp
                        @if($canAddToCart)
                            <form action="{{ route('cart.add', $product) }}" method="POST" class="space-y-4" id="add-to-cart-form">
                                @csrf
                                @if($hasVariants)
                                    <input type="hidden" name="variant_id" id="form_variant_id" value="{{ $defaultVariant?->id }}">
                                @endif
                                <div class="flex items-center space-x-4">
                                    <label for="quantity" class="text-gray-300">Quantity:</label>
                                    <input type="number" 
                                           id="quantity" 
                                           name="quantity" 
                                           value="1" 
                                           min="1" 
                                           max="{{ $stockOnShow }}"
                                           class="w-24 px-3 py-2 bg-black/50 border border-violet-500/30 rounded-lg text-white focus:border-violet-500 focus:ring-violet-500/50">
                                </div>
                                <button type="submit" id="add-to-cart-btn" @disabled($hasVariants && ! $defaultVariant) class="w-full px-8 py-4 bg-gradient-to-r from-violet-600 to-purple-600 text-white rounded-lg font-semibold hover:shadow-lg hover:shadow-violet-500/50 hover:scale-105 transition-all relative overflow-hidden group disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:scale-100">
                                    <span class="relative z-10 flex items-center justify-center">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                        Add to Cart
                                    </span>
                                    <span class="absolute inset-0 bg-gradient-to-r from-purple-600 to-violet-600 opacity-0 group-hover:opacity-100 transition-opacity"></span>
                                </button>
                            </form>
                        @else
                            <button disabled class="w-full px-8 py-4 bg-gray-600 text-gray-400 rounded-lg font-semibold cursor-not-allowed">
                                @if($stockOnShow <= 0)
                                    Out of Stock
                                @else
                                    Not Available
                                @endif
                            </button>
                        @endif
                        
                        <button class="w-full px-8 py-4 bg-transparent border-2 border-violet-500/50 text-violet-400 rounded-lg font-semibold hover:bg-violet-500/10 transition-all">
                            <span class="flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                </svg>
                                Add to Wishlist
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Include Footer -->
    @include('home.components.footer')
    
    
    <!-- Include Styles -->
    @include('home.styles')

    <style>
        .product-slideshow { position: relative; min-height: 320px; }
        .product-slide { display: none; }
        .product-slide.active { display: block; }
        .product-nav { position: absolute; top: 50%; transform: translateY(-50%); background: rgba(0,0,0,0.6); color: #a78bfa; border: 1px solid rgba(167,139,250,0.4); width: 42px; height: 42px; border-radius: 9999px; display: flex; align-items: center; justify-content: center; }
        .product-prev { left: 12px; }
        .product-next { right: 12px; }
        .product-dot { width: 10px; height: 10px; border-radius: 9999px; border: 1px solid rgba(167,139,250,0.5); transition: background 0.3s ease; padding: 0; }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const slideshow = document.getElementById('product-slideshow');
            const wrap = document.getElementById('product-gallery-wrap');
            const variantSelect = document.getElementById('variant_id');
            const variantPriceEl = document.getElementById('variant-price');
            const formVariantInput = document.getElementById('form_variant_id');

            const rebuildSlideshow = (imageUrls) => {
                if (!wrap || !imageUrls || !imageUrls.length) return;
                let html = '<div class="product-slideshow" id="product-slideshow">';
                imageUrls.forEach((url, i) => {
                    html += '<div class="product-slide' + (i === 0 ? ' active' : '') + '"><img src="' + url + '" alt="" class="w-full h-auto object-cover"><div class="absolute inset-0 glitch-overlay opacity-30"></div></div>';
                });
                html += '</div>';
                if (imageUrls.length > 1) {
                    html += '<div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex space-x-2 z-10">';
                    imageUrls.forEach((_, i) => {
                        html += '<button class="product-dot ' + (i === 0 ? 'bg-violet-400' : 'bg-violet-500/40') + '" data-slide="' + i + '" aria-label="Slide ' + (i+1) + '"></button>';
                    });
                    html += '</div><button class="product-nav product-prev" aria-label="Previous"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg></button><button class="product-nav product-next" aria-label="Next"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></button>';
                }
                wrap.innerHTML = html;
                initSlideshow();
            };

            const initSlideshow = () => {
                const slides = Array.from(document.querySelectorAll('.product-slide'));
                if (slides.length === 0) return;
                const dots = Array.from(document.querySelectorAll('.product-dot'));
                const prev = document.querySelector('.product-prev');
                const next = document.querySelector('.product-next');
                let current = 0;
                const showSlide = (index) => {
                    slides.forEach((s, i) => s.classList.toggle('active', i === index));
                    dots.forEach((d, i) => {
                        d.classList.toggle('bg-violet-400', i === index);
                        d.classList.toggle('bg-violet-500/40', i !== index);
                    });
                    current = index;
                };
                dots.forEach((dot, i) => dot.addEventListener('click', () => showSlide(i)));
                if (next) next.addEventListener('click', () => showSlide((current + 1) % slides.length));
                if (prev) prev.addEventListener('click', () => showSlide((current - 1 + slides.length) % slides.length));
            };

            const swatches = document.querySelectorAll('.variant-swatch');
            const addToCartBtn = document.getElementById('add-to-cart-btn');
            const qtyInput = document.querySelector('input[name="quantity"]');
            const stockStatusEl = document.getElementById('stock-status');

            swatches.forEach((swatch) => {
                swatch.addEventListener('click', function () {
                    if (this.dataset.inStock !== '1') return;

                    swatches.forEach((s) => s.classList.remove('border-violet-500', 'bg-violet-500/10'));
                    this.classList.add('border-violet-500', 'bg-violet-500/10');

                    if (formVariantInput) formVariantInput.value = this.dataset.variantId;
                    if (variantPriceEl && this.dataset.price) {
                        variantPriceEl.textContent = '৳' + parseFloat(this.dataset.price).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                    }
                    if (addToCartBtn) addToCartBtn.disabled = false;

                    // The stock line belongs to the selected colour, not the product.
                    if (stockStatusEl) {
                        const stock = parseInt(this.dataset.stock, 10) || 0;
                        stockStatusEl.innerHTML = stock > 0
                            ? '<p class="text-lg text-violet-400 font-semibold">✓ In Stock (' + stock + ' available)</p>'
                            : '<p class="text-lg text-red-400 font-semibold">✗ Out of Stock</p>';
                    }

                    // Never let the quantity box exceed this variant's stock.
                    if (qtyInput) {
                        qtyInput.max = this.dataset.stock;
                        if (parseInt(qtyInput.value, 10) > parseInt(this.dataset.stock, 10)) {
                            qtyInput.value = this.dataset.stock;
                        }
                    }

                    const imagesJson = this.dataset.images;
                    if (imagesJson) {
                        const urls = JSON.parse(imagesJson);
                        if (urls.length) rebuildSlideshow(urls);
                    }
                });
            });

            initSlideshow();
        });
    </script>
    
    <!-- Offer Countdown Timer Script -->
    <script>
        function updateCountdowns() {
            const countdowns = document.querySelectorAll('.offer-countdown');
            const now = Math.floor(Date.now() / 1000);
            
            countdowns.forEach(countdown => {
                const endTime = parseInt(countdown.dataset.endTime);
                const timeLeft = endTime - now;
                
                if (timeLeft <= 0) {
                    countdown.querySelector('.countdown-timer').textContent = 'Offer Ended - Refreshing...';
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
                    timerHTML = `<span class="bg-orange-600/40 px-3 py-2 rounded-lg">${days} Days</span> <span class="bg-orange-600/40 px-3 py-2 rounded-lg">${hours} Hours</span> <span class="bg-orange-600/40 px-3 py-2 rounded-lg">${minutes} Minutes</span>`;
                } else if (hours > 0) {
                    timerHTML = `<span class="bg-orange-600/40 px-3 py-2 rounded-lg">${hours} Hours</span> <span class="bg-orange-600/40 px-3 py-2 rounded-lg">${minutes} Minutes</span> <span class="bg-orange-600/40 px-3 py-2 rounded-lg">${seconds} Seconds</span>`;
                } else {
                    timerHTML = `<span class="bg-red-600/40 px-3 py-2 rounded-lg animate-pulse">${minutes} Minutes</span> <span class="bg-red-600/40 px-3 py-2 rounded-lg animate-pulse">${seconds} Seconds</span>`;
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
</body>
</html>

