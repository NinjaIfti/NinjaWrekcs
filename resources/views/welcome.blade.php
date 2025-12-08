<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>NinjaWrekcs - Valorant Gaming Collectibles | Pre-Order Now</title>
    <link rel="icon" type="image/png" href="{{ asset('img/fav.png') }}">
    
    @include('components.seo', [
        'title' => 'NinjaWrekcs - Valorant Gaming Collectibles | Pre-Order Now',
        'description' => 'Shop authentic Valorant collectibles including agent figures, knives, weapons, stickers, and keychains. Pre-order now and get 100 taka off plus 10% discount. Fast delivery across Bangladesh.',
        'url' => url('/')
    ])
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-black text-white">
    <!-- Include Navigation -->
    @include('home.components.navigation')
    
    <!-- Include Hero Sections -->
    @include('home.sections.hero-mobile')
    @include('home.sections.hero')
  
    
    <!-- Include Categories Section -->
    @include('home.sections.categories')
    
    <!-- Include Products Section -->
    @include('home.sections.products')
    
    <!-- Include Special Offers Section -->
    @include('home.sections.special-offers')
    
    <!-- Include Footer -->
    @include('home.components.footer')
    
    <!-- Pre-Order Popup -->
    @include('components.pre-order-popup')
    
    <!-- Include Styles -->
    @include('home.styles')
</body>
</html>
