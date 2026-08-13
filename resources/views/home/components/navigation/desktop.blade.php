<!-- Desktop Navigation (Hidden on Mobile) -->
<div class="hidden md:flex items-center space-x-8">
    <a href="{{ route('shop.index') }}" class="text-gray-300 hover:text-violet-400 transition-colors font-medium relative group">
        Shop
        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-violet-400 group-hover:w-full transition-all duration-300"></span>
    </a>
    <a href="{{ route('deals.index') }}" class="text-gray-300 hover:text-violet-400 transition-colors font-medium relative group">
        Deals
        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-violet-400 group-hover:w-full transition-all duration-300"></span>
    </a>
    <a href="{{ route('about') }}" class="text-gray-300 hover:text-violet-400 transition-colors font-medium relative group">
        About
        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-violet-400 group-hover:w-full transition-all duration-300"></span>
    </a>
    <a href="{{ route('giveaway') }}"
       class="giveaway-blink inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg font-bold
              text-black bg-gradient-to-r from-yellow-400 to-amber-500
              border border-yellow-300 shadow-lg shadow-yellow-500/30
              hover:brightness-110 hover:scale-105 transition-all">
        🎉 Giveaway
    </a>
</div>

