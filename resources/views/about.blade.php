<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>About Us - NinjaWrekcs | Valorant Collectibles Store</title>
    <link rel="icon" type="image/png" href="{{ asset('img/fav.png') }}">
    
    @include('components.seo', [
        'title' => 'About Us - NinjaWrekcs | Valorant Collectibles Store',
        'description' => 'Learn about NinjaWrekcs - your trusted source for premium Valorant gaming collectibles. We bring authentic agent figures, knives, weapons, and more to Valorant fans in Bangladesh.',
        'url' => route('about')
    ])
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-black text-white">
    @include('home.components.navigation')
    
    <!-- Hero Section -->
    <section class="relative pt-32 pb-20 min-h-[60vh] flex items-center overflow-hidden bg-gradient-to-br from-black via-violet-950 to-purple-950">
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-violet-500/20 rounded-full blur-3xl animate-pulse"></div>
            <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-purple-500/20 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s;"></div>
        </div>
        
        <div class="absolute inset-0 opacity-10">
            <div class="grid-pattern"></div>
        </div>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
            <h1 class="text-5xl md:text-7xl font-bold mb-6">
                <span class="glitch-text-large" data-text="About NinjaWrekcs">About NinjaWrekcs</span>
            </h1>
            <p class="text-xl md:text-2xl text-gray-300 max-w-3xl mx-auto">
                Your trusted source for premium Valorant gaming collectibles
            </p>
        </div>
    </section>

    <!-- Our Story Section -->
    <section class="py-20 bg-gradient-to-b from-black to-violet-950/30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div>
                    <h2 class="text-4xl md:text-5xl font-bold mb-6">
                        <span class="glitch-text" data-text="Our Story">Our Story</span>
                    </h2>
                    <p class="text-lg text-gray-300 leading-relaxed mb-4">
                        NinjaWrekcs was born from a passion for Valorant and the gaming community. We started with a simple mission: to bring high-quality, authentic Valorant collectibles to fans who share our love for the game.
                    </p>
                    <p class="text-lg text-gray-300 leading-relaxed mb-4">
                        From agent figures to weapon replicas, stickers, and keychains, we curate the finest collection of Valorant merchandise. Every product is carefully selected to ensure authenticity and quality that matches the excellence of the game itself.
                    </p>
                    <p class="text-lg text-gray-300 leading-relaxed">
                        We're not just a store—we're fellow players, collectors, and fans dedicated to celebrating the world of Valorant.
                    </p>
                </div>
                <div class="relative">
                    <div class="bg-black/50 backdrop-blur-xl rounded-2xl border border-violet-500/30 p-8">
                        <div class="space-y-6">
                            <div class="flex items-center space-x-4">
                                <div class="w-16 h-16 bg-violet-600 rounded-full flex items-center justify-center">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-white">Premium Quality</h3>
                                    <p class="text-gray-400">Authentic collectibles only</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-4">
                                <div class="w-16 h-16 bg-purple-600 rounded-full flex items-center justify-center">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-white">Community First</h3>
                                    <p class="text-gray-400">Built by players, for players</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-4">
                                <div class="w-16 h-16 bg-fuchsia-600 rounded-full flex items-center justify-center">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-white">Fast Delivery</h3>
                                    <p class="text-gray-400">Quick and secure shipping</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Mission & Values Section -->
    <section class="py-20 bg-gradient-to-b from-violet-950/30 to-black">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold mb-4">
                    <span class="glitch-text" data-text="Our Mission">Our Mission</span>
                </h2>
                <p class="text-xl text-gray-300 max-w-3xl mx-auto">
                    To provide Valorant fans with the highest quality collectibles while building a community of passionate players and collectors.
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-black/50 backdrop-blur-xl rounded-2xl border border-violet-500/30 p-8 text-center">
                    <div class="w-20 h-20 bg-violet-600 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-4">Excellence</h3>
                    <p class="text-gray-300">We strive for excellence in every product we offer, ensuring only the best quality reaches our customers.</p>
                </div>

                <div class="bg-black/50 backdrop-blur-xl rounded-2xl border border-violet-500/30 p-8 text-center">
                    <div class="w-20 h-20 bg-purple-600 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-4">Passion</h3>
                    <p class="text-gray-300">Our passion for Valorant drives us to continuously expand our collection and serve the community better.</p>
                </div>

                <div class="bg-black/50 backdrop-blur-xl rounded-2xl border border-violet-500/30 p-8 text-center">
                    <div class="w-20 h-20 bg-fuchsia-600 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-4">Trust</h3>
                    <p class="text-gray-300">We build trust through transparent communication, secure transactions, and reliable customer service.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose Us Section -->
    <section class="py-20 bg-gradient-to-b from-black to-violet-950/30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold mb-4">
                    <span class="glitch-text" data-text="Why Choose Us">Why Choose Us</span>
                </h2>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-black/50 backdrop-blur-xl rounded-xl border border-violet-500/30 p-6">
                    <div class="text-4xl font-bold text-violet-400 mb-2">50K+</div>
                    <p class="text-gray-300">Happy Customers</p>
                </div>
                <div class="bg-black/50 backdrop-blur-xl rounded-xl border border-violet-500/30 p-6">
                    <div class="text-4xl font-bold text-purple-400 mb-2">4.9/5</div>
                    <p class="text-gray-300">Customer Rating</p>
                </div>
                <div class="bg-black/50 backdrop-blur-xl rounded-xl border border-violet-500/30 p-6">
                    <div class="text-4xl font-bold text-fuchsia-400 mb-2">100%</div>
                    <p class="text-gray-300">Authentic Products</p>
                </div>
                <div class="bg-black/50 backdrop-blur-xl rounded-xl border border-violet-500/30 p-6">
                    <div class="text-4xl font-bold text-violet-400 mb-2">24/7</div>
                    <p class="text-gray-300">Support Available</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="py-20 bg-gradient-to-b from-violet-950/30 to-black">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-black/50 backdrop-blur-xl rounded-2xl border border-violet-500/30 p-8 md:p-12 text-center">
                <h2 class="text-3xl md:text-4xl font-bold mb-6">
                    <span class="glitch-text" data-text="Get In Touch">Get In Touch</span>
                </h2>
                <p class="text-lg text-gray-300 mb-8">
                    Have questions? We'd love to hear from you. Reach out to us and we'll respond as soon as possible.
                </p>
                <div class="space-y-4">
                    <div class="flex items-center justify-center space-x-3">
                        <svg class="w-6 h-6 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <a href="mailto:support@ninjawrekcs.com" class="text-violet-400 hover:text-violet-300 transition">support@ninjawrekcs.com</a>
                    </div>
                    <div class="flex items-center justify-center space-x-3">
                        <svg class="w-6 h-6 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        <a href="tel:+8801533133309" class="text-violet-400 hover:text-violet-300 transition">+880 1533 133309</a>
                    </div>
                    <div class="flex items-center justify-center space-x-3">
                        <svg class="w-6 h-6 text-violet-400" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                        </svg>
                        <a href="https://wa.me/8801533133309" target="_blank" rel="noopener noreferrer" class="text-violet-400 hover:text-violet-300 transition">WhatsApp: 01533133309</a>
                    </div>
                </div>
                
                <!-- Social Media Links -->
                <div class="mt-8 pt-8 border-t border-violet-500/20">
                    <p class="text-gray-400 mb-4">Follow us on social media:</p>
                    <div class="flex justify-center space-x-4">
                        <a href="https://www.facebook.com/ninjawrecks" target="_blank" rel="noopener noreferrer" class="w-12 h-12 bg-violet-900/50 rounded-lg flex items-center justify-center hover:bg-violet-600 transition-colors border border-violet-500/30" title="Facebook Page">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>
                        <a href="https://www.instagram.com/ninja_wrecks?igsh=MXhqM3hldHpld25xNw==" target="_blank" rel="noopener noreferrer" class="w-12 h-12 bg-violet-900/50 rounded-lg flex items-center justify-center hover:bg-violet-600 transition-colors border border-violet-500/30" title="Instagram">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.319 1.347 20.651.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227-.224.562-.479.96-.899 1.382-.419.419-.824.679-1.38.896-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421-.569-.224-.96-.479-1.379-.899-.421-.419-.69-.824-.9-1.38-.165-.42-.359-1.065-.42-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.045 1.65-.06 4.859-.06l.045.03zm0 3.678c-3.405 0-6.162 2.76-6.162 6.162 0 3.405 2.76 6.162 6.162 6.162 3.405 0 6.162-2.76 6.162-6.162 0-3.405-2.76-6.162-6.162-6.162zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405c0 .795-.646 1.44-1.44 1.44-.795 0-1.44-.646-1.44-1.44 0-.794.646-1.439 1.44-1.439.793-.001 1.44.645 1.44 1.439z"/>
                            </svg>
                        </a>
                        <a href="https://wa.me/8801533133309" target="_blank" rel="noopener noreferrer" class="w-12 h-12 bg-violet-900/50 rounded-lg flex items-center justify-center hover:bg-violet-600 transition-colors border border-violet-500/30" title="WhatsApp">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('home.components.footer')
    @include('home.styles')
</body>
</html>

