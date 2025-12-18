@php
    $popupSettings = \App\Models\PopupSetting::getSettings();
@endphp

@if($popupSettings->is_active)
<!-- Pre-Order Popup Modal -->
<div id="preOrderModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/80 backdrop-blur-sm opacity-0 invisible transition-all duration-300 p-2 md:p-4" style="display: none;">
    <div class="relative max-w-md w-full mx-2 md:mx-4 bg-gradient-to-br from-black via-violet-950 to-purple-950 rounded-xl md:rounded-2xl border-2 border-violet-500/50 shadow-2xl overflow-hidden transform scale-95 transition-transform duration-300 max-h-[90vh] overflow-y-auto">
        <!-- Glitch Background Overlay -->
        <div class="absolute inset-0 glitch-bg opacity-30"></div>
        
        <!-- Close Button -->
        <button onclick="closePreOrderModal()" class="absolute top-4 right-4 z-20 w-8 h-8 bg-black/50 backdrop-blur-sm rounded-full flex items-center justify-center hover:bg-violet-600 hover:text-white transition-colors border border-violet-500/30">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        <!-- Content -->
        <div class="relative z-10 p-4 md:p-8">
            <!-- Badge -->
            @if($popupSettings->badge_text)
            <div class="text-center mb-3 md:mb-6">
                <span class="inline-block px-3 py-1 md:px-4 md:py-2 bg-gradient-to-r from-violet-600 to-purple-600 text-white rounded-full text-xs md:text-sm font-bold glitch-pulse">
                    {{ $popupSettings->badge_text }}
                </span>
            </div>
            @endif

            <!-- Main Heading -->
            @if($popupSettings->main_heading)
            <h2 class="text-2xl md:text-4xl font-bold text-center mb-3 md:mb-4">
                <span class="glitch-text-large" data-text="{{ $popupSettings->main_heading }}" style="text-transform: none;">{{ $popupSettings->main_heading }}</span>
            </h2>
            @endif

            <!-- Subheading -->
            @if($popupSettings->subheading)
            <p class="text-center text-violet-300 text-base md:text-lg mb-4 md:mb-6">
                {{ $popupSettings->subheading }}
            </p>
            @endif

            <!-- Discount Section -->
            @if($popupSettings->discount_text || $popupSettings->discount_amount)
            <div class="text-center mb-4 md:mb-6">
                <div class="inline-block px-4 py-2 md:px-6 md:py-3 bg-violet-500/20 border-2 border-violet-400 rounded-lg">
                    <p class="text-lg md:text-2xl font-bold text-violet-300">
                        @if($popupSettings->discount_text)
                            {{ $popupSettings->discount_text }} 
                        @endif
                        @if($popupSettings->discount_amount)
                            <span class="text-white">{{ $popupSettings->discount_amount }}</span>
                        @endif
                    </p>
                </div>
            </div>
            @endif

            <!-- Description -->
            @if($popupSettings->description)
            <div class="mb-4 md:mb-6">
                <div class="p-2 md:p-4 bg-black/30 rounded-lg border border-violet-500/20">
                    <p class="text-gray-300 text-xs md:text-sm text-center">
                        {{ $popupSettings->description }}
                    </p>
                </div>
            </div>
            @endif

            <!-- Action Button -->
            <div class="space-y-2 md:space-y-3">
                <a href="{{ $popupSettings->button_url }}" onclick="closePreOrderModal()" class="block w-full px-4 py-3 md:px-6 md:py-4 bg-gradient-to-r from-violet-600 to-purple-600 text-white rounded-lg font-semibold hover:shadow-lg hover:shadow-violet-500/50 hover:scale-105 transition-all text-center relative overflow-hidden group text-sm md:text-base">
                    <span class="relative z-10 flex items-center justify-center">
                        <svg class="w-4 h-4 md:w-5 md:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        {{ $popupSettings->button_text }}
                    </span>
                    <span class="absolute inset-0 bg-gradient-to-r from-purple-600 to-violet-600 opacity-0 group-hover:opacity-100 transition-opacity"></span>
                </a>
                <button onclick="closePreOrderModal()" class="w-full px-4 py-2 md:px-6 md:py-3 bg-transparent border-2 border-violet-500/50 text-violet-400 rounded-lg font-semibold hover:bg-violet-500/10 transition-all text-sm md:text-base">
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
            }, {{ $popupSettings->display_delay }}); // Dynamic delay from admin settings
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
@endif
