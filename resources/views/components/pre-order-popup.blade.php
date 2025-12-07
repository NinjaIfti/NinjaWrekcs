<!-- Pre-Order Popup Modal -->
<div id="preOrderModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/80 backdrop-blur-sm opacity-0 invisible transition-all duration-300" style="display: none;">
    <div class="relative max-w-md w-full mx-4 bg-gradient-to-br from-black via-violet-950 to-purple-950 rounded-2xl border-2 border-violet-500/50 shadow-2xl overflow-hidden transform scale-95 transition-transform duration-300">
        <!-- Glitch Background Overlay -->
        <div class="absolute inset-0 glitch-bg opacity-30"></div>
        
        <!-- Close Button -->
        <button onclick="closePreOrderModal()" class="absolute top-4 right-4 z-20 w-8 h-8 bg-black/50 backdrop-blur-sm rounded-full flex items-center justify-center hover:bg-violet-600 hover:text-white transition-colors border border-violet-500/30">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        <!-- Content -->
        <div class="relative z-10 p-8">
            <!-- Badge -->
            <div class="text-center mb-6">
                <span class="inline-block px-4 py-2 bg-gradient-to-r from-violet-600 to-purple-600 text-white rounded-full text-sm font-bold glitch-pulse">
                    🔥 LIMITED TIME OFFER
                </span>
            </div>

            <!-- Heading -->
            <h2 class="text-3xl md:text-4xl font-bold text-center mb-4">
                <span class="glitch-text-large" data-text="PRE-ORDER NOW" style="text-transform: none;">PRE-ORDER NOW</span>
            </h2>

            <!-- Discount Badge -->
            <div class="text-center mb-6 space-y-3">
                <div class="inline-block px-6 py-3 bg-violet-500/20 border-2 border-violet-400 rounded-lg">
                    <p class="text-2xl font-bold text-violet-300">
                        Get <span class="text-white">100 taka</span> Off!
                    </p>
                </div>
                <div class="inline-block px-6 py-3 bg-purple-500/20 border-2 border-purple-400 rounded-lg">
                    <p class="text-2xl font-bold text-purple-300" style="text-transform: none;">
                        Plus <span class="text-white" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;">10&#37;</span> Discount!
                    </p>
                </div>
            </div>

            <!-- Offer Details -->
            <div class="space-y-4 mb-6">
                <div class="flex items-start space-x-3 p-4 bg-black/30 rounded-lg border border-violet-500/20">
                    <svg class="w-6 h-6 text-violet-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <p class="text-white font-semibold">Pre-Order Special</p>
                        <p class="text-gray-300 text-sm">Only need to advance <span class="text-violet-400 font-bold">200 taka</span> to secure your order!</p>
                    </div>
                </div>

                <div class="flex items-start space-x-3 p-4 bg-black/30 rounded-lg border border-violet-500/20">
                    <svg class="w-6 h-6 text-violet-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <p class="text-white font-semibold">Save 100 taka Plus <span style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">10&#37;</span> Off</p>
                        <p class="text-gray-300 text-sm">Get instant 100 taka discount plus an additional <span style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">10&#37;</span> off on your pre-order purchase</p>
                    </div>
                </div>

                <div class="flex items-start space-x-3 p-4 bg-black/30 rounded-lg border border-violet-500/20">
                    <svg class="w-6 h-6 text-violet-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <div>
                        <p class="text-white font-semibold">Early Access</p>
                        <p class="text-gray-300 text-sm">Be among the first to receive your Valorant collectibles</p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-3">
                <a href="{{ route('shop.index') }}" onclick="closePreOrderModal()" class="block w-full px-6 py-4 bg-gradient-to-r from-violet-600 to-purple-600 text-white rounded-lg font-semibold hover:shadow-lg hover:shadow-violet-500/50 hover:scale-105 transition-all text-center relative overflow-hidden group">
                    <span class="relative z-10 flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        Pre-Order Now
                    </span>
                    <span class="absolute inset-0 bg-gradient-to-r from-purple-600 to-violet-600 opacity-0 group-hover:opacity-100 transition-opacity"></span>
                </a>
                <button onclick="closePreOrderModal()" class="w-full px-6 py-3 bg-transparent border-2 border-violet-500/50 text-violet-400 rounded-lg font-semibold hover:bg-violet-500/10 transition-all">
                    Maybe Later
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Show modal on page load (only once per session)
    document.addEventListener('DOMContentLoaded', function() {
        // Check if user has already seen the popup in this session
        if (!sessionStorage.getItem('preOrderModalShown')) {
            setTimeout(() => {
                showPreOrderModal();
            }, 1000); // Show after 1 second
        }
    });

    function showPreOrderModal() {
        const modal = document.getElementById('preOrderModal');
        if (modal) {
            modal.style.display = 'flex';
            setTimeout(() => {
                modal.classList.remove('opacity-0', 'invisible');
                modal.classList.add('opacity-100', 'visible');
                const content = modal.querySelector('.max-w-md');
                if (content) {
                    content.classList.remove('scale-95');
                    content.classList.add('scale-100');
                }
            }, 10);
        }
    }

    function closePreOrderModal() {
        const modal = document.getElementById('preOrderModal');
        if (modal) {
            modal.classList.remove('opacity-100', 'visible');
            modal.classList.add('opacity-0', 'invisible');
            const content = modal.querySelector('.max-w-md');
            if (content) {
                content.classList.remove('scale-100');
                content.classList.add('scale-95');
            }
            setTimeout(() => {
                modal.style.display = 'none';
                // Mark as shown in session storage
                sessionStorage.setItem('preOrderModalShown', 'true');
            }, 300);
        }
    }

    // Close on background click
    document.getElementById('preOrderModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closePreOrderModal();
        }
    });

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closePreOrderModal();
        }
    });
</script>

