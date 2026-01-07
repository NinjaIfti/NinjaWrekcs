<!-- Mobile Footer -->
<footer class="md:hidden bg-black border-t border-violet-500/20 text-gray-300 pt-10 pb-16">
    <div class="max-w-4xl mx-auto px-4 space-y-8">
        <div class="flex items-center space-x-3">
            <img src="{{ asset('img/fav.png') }}" alt="NinjaWrecks" class="h-10 w-10 rounded-lg border border-violet-500/50">
            <span class="text-2xl font-bold glitch-text-large" data-text="NinjaWrecks">NinjaWrecks</span>
        </div>

        <p class="text-gray-400 leading-relaxed">
            Premium Valorant collectibles with fast delivery across Bangladesh.
        </p>

        <div class="grid grid-cols-3 gap-4">
            <div class="space-y-3">
                <h4 class="text-white font-semibold text-sm">Shop</h4>
                <ul class="space-y-2 text-xs">
                    <li><a href="{{ route('shop.index') }}" class="hover:text-violet-400 transition-colors">All</a></li>
                    <li><a href="{{ route('shop.index', ['category' => 'figures']) }}" class="hover:text-violet-400 transition-colors">Figures</a></li>
                    <li><a href="{{ route('shop.index', ['category' => 'knives']) }}" class="hover:text-violet-400 transition-colors">Knives</a></li>
                </ul>
            </div>

            <div class="space-y-3">
                <h4 class="text-white font-semibold text-sm">Support</h4>
                <ul class="space-y-2 text-xs">
                    <li><a href="{{ route('contact') }}" class="hover:text-violet-400 transition-colors">Contact</a></li>
                    <li><a href="{{ route('shipping') }}" class="hover:text-violet-400 transition-colors">Shipping</a></li>
                    <li><a href="{{ route('returns') }}" class="hover:text-violet-400 transition-colors">Returns</a></li>
                    <li><a href="{{ route('faq') }}" class="hover:text-violet-400 transition-colors">FAQ</a></li>
                </ul>
            </div>

            <div class="space-y-3">
                <h4 class="text-white font-semibold text-sm">Contact</h4>
                <div class="space-y-2 text-xs">
                    <a href="tel:+8801533133309" class="block hover:text-violet-300 transition">
                        <span>Phone</span>
                    </a>
                    <a href="https://wa.me/8801533133309" target="_blank" rel="noopener noreferrer" class="block hover:text-violet-300 transition">
                        <span>WhatsApp</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="flex items-center space-x-4">
            <a href="https://www.facebook.com/ninjawrecks" target="_blank" rel="noopener noreferrer" class="w-10 h-10 bg-violet-900/50 rounded-lg flex items-center justify-center hover:bg-violet-600 transition-colors border border-violet-500/30" title="Facebook">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                </svg>
            </a>
            <a href="https://www.instagram.com/ninja_wrecks?igsh=MXhqM3hldHpld25xNw==" target="_blank" rel="noopener noreferrer" class="w-10 h-10 bg-violet-900/50 rounded-lg flex items-center justify-center hover:bg-violet-600 transition-colors border border-violet-500/30" title="Instagram">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.319 1.347 20.651.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227-.224.562-.479.96-.899 1.382-.419.419-.824.679-1.38.896-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421-.569-.224-.96-.479-1.379-.899-.421-.419-.69-.824-.9-1.38-.165-.42-.359-1.065-.42-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.045 1.65-.06 4.859-.06l.045.03zm0 3.678c-3.405 0-6.162 2.76-6.162 6.162 0 3.405 2.76 6.162 6.162 6.162 3.405 0 6.162-2.76 6.162-6.162 0-3.405-2.76-6.162-6.162-6.162zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405c0 .795-.646 1.44-1.44 1.44-.795 0-1.44-.646-1.44-1.44 0-.794.646-1.439 1.44-1.439.793-.001 1.44.645 1.44 1.439z"/>
                </svg>
            </a>
        </div>

        <div class="border-t border-violet-500/20 pt-4 flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
            <p class="text-gray-400 text-sm">© 2025 NinjaWrecks. All rights reserved.</p>
            <div class="flex space-x-4">
                <img src="https://upload.wikimedia.org/wikipedia/commons/0/04/Visa.svg" alt="Visa" class="h-6 opacity-70">
                <img src="https://upload.wikimedia.org/wikipedia/commons/2/2a/Mastercard-logo.svg" alt="Mastercard" class="h-6 opacity-70">
                <img src="https://upload.wikimedia.org/wikipedia/en/6/68/BKash_logo.svg" alt="bKash" class="h-6 opacity-70">
            </div>
        </div>
    </div>
</footer>
