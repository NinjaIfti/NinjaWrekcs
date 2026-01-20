<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Deals & Offers - NinjaWrecks</title>
    <link rel="icon" type="image/png" href="{{ asset('img/fav.png') }}">
    
    @include('components.seo', [
        'title' => 'Hot Deals & Special Offers - NinjaWrecks',
        'description' => 'Check out our amazing deals and special offers on gaming collectibles. Limited time offers, flash sales, and exclusive discounts.',
        'url' => route('deals.index')
    ])
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-black text-white">
    <!-- Include Navigation -->
    @include('home.components.navigation')
    
    <!-- Deals Page -->
    <section class="pt-24 md:pt-32 pb-20 min-h-screen bg-gradient-to-b from-black via-violet-950/50 to-black">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="text-center mb-12">
                <h1 class="text-4xl md:text-6xl font-bold mb-4">
                    <span class="glitch-text-large" data-text="Hot Deals & Offers">Hot Deals & Offers</span>
                </h1>
                <p class="text-xl text-gray-400">Limited time offers you don't want to miss</p>
            </div>

            <!-- Featured Deals Section -->
            @if($featuredDeals->count() > 0)
            <div class="mb-16">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-3xl font-bold text-white">
                        <span class="glitch-text" data-text="Featured Deals">Featured Deals</span>
                    </h2>
                    <span class="px-4 py-2 bg-red-500/20 text-red-400 rounded-full text-sm font-bold border border-red-500/30">
                        🔥 HOT PICKS
                    </span>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    @foreach($featuredDeals as $product)
                    @include('deals.partials.product-card', ['product' => $product])
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Limited Time Offers Section -->
            @if($offerProducts->count() > 0)
            <div class="mb-16">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h2 class="text-3xl font-bold text-white">
                            <span class="glitch-text" data-text="Limited Time Offers">Limited Time Offers</span>
                        </h2>
                        <p class="text-gray-400 mt-2">⏰ Hurry! These deals end soon</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    @foreach($offerProducts as $product)
                    @include('deals.partials.product-card', ['product' => $product, 'showTimer' => true])
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Sale Products Section -->
            @if($saleProducts->count() > 0)
            <div class="mb-16">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h2 class="text-3xl font-bold text-white">
                            <span class="glitch-text" data-text="On Sale">On Sale</span>
                        </h2>
                        <p class="text-gray-400 mt-2">💰 Great prices, always available</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    @foreach($saleProducts as $product)
                    @include('deals.partials.product-card', ['product' => $product])
                    @endforeach
                </div>
            </div>
            @endif

            <!-- No Deals Available -->
            @if($featuredDeals->count() === 0 && $offerProducts->count() === 0 && $saleProducts->count() === 0)
            <div class="text-center py-20">
                <svg class="w-24 h-24 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/>
                </svg>
                <h3 class="text-2xl text-gray-400 mb-2">No active deals right now</h3>
                <p class="text-gray-500 mb-6">Check back soon for amazing offers!</p>
                <a href="{{ route('shop.index') }}" 
                   class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-violet-600 to-purple-600 text-white rounded-lg font-semibold hover:shadow-lg hover:shadow-violet-500/50 transition-all">
                    Browse All Products
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                </a>
            </div>
            @endif
        </div>
    </section>
    
    <!-- Include Footer -->
    @include('home.components.footer')
    
    <!-- Include Styles -->
    @include('home.styles')
    
    <!-- Countdown Timer Script -->
    <script>
        function initCountdownTimers() {
            document.querySelectorAll('[data-countdown]').forEach(function(element) {
                const endTime = new Date(element.dataset.countdown).getTime();
                
                function updateCountdown() {
                    const now = new Date().getTime();
                    const distance = endTime - now;
                    
                    if (distance < 0) {
                        element.innerHTML = '<span class="text-red-400">EXPIRED</span>';
                        return;
                    }
                    
                    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                    
                    let html = '';
                    if (days > 0) html += days + 'd ';
                    html += hours.toString().padStart(2, '0') + ':' + 
                            minutes.toString().padStart(2, '0') + ':' + 
                            seconds.toString().padStart(2, '0');
                    
                    element.innerHTML = html;
                }
                
                updateCountdown();
                setInterval(updateCountdown, 1000);
            });
        }
        
        document.addEventListener('DOMContentLoaded', initCountdownTimers);
    </script>
</body>
</html>
