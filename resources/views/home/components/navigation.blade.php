<!-- Navigation (Desktop Only) -->
<nav class="hidden md:block fixed w-full bg-black/95 backdrop-blur-xl shadow-lg z-50 border-b border-violet-500/30">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-20">
            <!-- Logo with Glitch Effect -->
            <div class="flex items-center">
                <a href="/" class="text-2xl font-bold glitch-text" data-text="NinjaWrekcs">
                    NinjaWrekcs
                </a>
            </div>

            <!-- Desktop Navigation Links -->
            @include('home.components.navigation.desktop')

            <!-- Action Buttons (Desktop) -->
            <div class="flex items-center space-x-4">
                @include('home.components.navigation.actions')
            </div>
        </div>
    </div>
</nav>

<!-- Mobile Bottom Navigation -->
@include('home.components.navigation.mobile')
