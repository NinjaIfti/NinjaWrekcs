@props([
    'title' => 'NinjaWrecks - Valorant Gaming Collectibles',
    'description' => 'Shop authentic Valorant collectibles including agent figures, knives, weapons, stickers, and keychains. Get 100 taka off plus 10% discount. Fast delivery across Bangladesh.',
    'image' => asset('img/fav.png'),
    'url' => url()->current(),
    'type' => 'website',
    'keywords' => 'Valorant collectibles, Valorant figures, Valorant merchandise, gaming collectibles, agent figures, Valorant knives, Valorant weapons, Valorant stickers, Valorant keychains, Valorant Bangladesh, Bangladesh gaming store',
    'product' => null
])

<!-- Primary Meta Tags -->
<meta name="title" content="{{ $title }}">
<meta name="description" content="{{ $description }}">
<meta name="keywords" content="{{ $keywords }}">
<meta name="author" content="NinjaWrecks">
<meta name="robots" content="index, follow">
<meta name="language" content="English">
<meta name="revisit-after" content="7 days">

<!-- Open Graph / Facebook -->
<meta property="og:type" content="{{ $type }}">
<meta property="og:url" content="{{ $url }}">
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:image" content="{{ $image }}">
<meta property="og:site_name" content="NinjaWrecks">
<meta property="og:locale" content="en_US">

<!-- Twitter -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:url" content="{{ $url }}">
<meta name="twitter:title" content="{{ $title }}">
<meta name="twitter:description" content="{{ $description }}">
<meta name="twitter:image" content="{{ $image }}">

<!-- Canonical URL -->
<link rel="canonical" href="{{ $url }}">

<!-- Structured Data (JSON-LD) -->
@if($product)
@php
    $productData = [
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $product->name,
        'description' => strip_tags($product->description ?? 'Authentic Valorant collectible'),
        'image' => $product->image ? asset('storage/' . $product->image) : asset('img/fav.png'),
        'brand' => [
            '@type' => 'Brand',
            'name' => 'NinjaWrecks'
        ],
        'offers' => [
            '@type' => 'Offer',
            'url' => $url,
            'priceCurrency' => 'BDT',
            'price' => $product->price ?? 0,
            'availability' => ($product->quantity ?? 0) > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
            'seller' => [
                '@type' => 'Organization',
                'name' => 'NinjaWrecks'
            ]
        ]
    ];
@endphp
<script type="application/ld+json">
{!! json_encode($productData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@else
@php
    $storeData = [
        '@context' => 'https://schema.org',
        '@type' => 'Store',
        'name' => 'NinjaWrecks',
        'description' => $description,
        'url' => url('/'),
        'logo' => asset('img/fav.png'),
        'image' => $image,
        'address' => [
            '@type' => 'PostalAddress',
            'addressCountry' => 'BD'
        ],
        'contactPoint' => [
            '@type' => 'ContactPoint',
            'telephone' => '+880-1533-133309',
            'contactType' => 'Customer Service',
            'availableLanguage' => 'English'
        ],
        'sameAs' => [
            'https://www.facebook.com/ninjawrecks',
            'https://www.instagram.com/ninja_wrecks'
        ],
        'priceRange' => '$$',
        'currenciesAccepted' => 'BDT'
    ];
@endphp
<script type="application/ld+json">
{!! json_encode($storeData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endif

