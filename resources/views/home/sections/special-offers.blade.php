@php
    $specialOffer = \App\Models\SpecialOffer::getActive();
@endphp

@if($specialOffer)
<!-- Special Offers Banner -->
<section id="deals" class="py-20 relative overflow-hidden bg-gradient-to-r from-violet-900 via-purple-900 to-fuchsia-900">
    <!-- Glitch Background Effects -->
    <div class="absolute inset-0 glitch-bg"></div>
    <div class="absolute inset-0 bg-black/20"></div>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid md:grid-cols-2 gap-12 items-center">
            <div class="text-white space-y-6">
                <div class="inline-block px-4 py-2 bg-white/10 rounded-full text-sm font-semibold backdrop-blur-sm border border-white/20 glitch-badge">
                    {{ $specialOffer->badge_text }}
                </div>
                <h2 class="text-5xl md:text-6xl font-bold leading-tight">
                    <span class="glitch-text-large" data-text="{{ $specialOffer->main_title }}" style="text-transform: none;">
                        {{ $specialOffer->main_title }}
                    </span>
                    @if($specialOffer->subtitle)
                        <br>
                        <span class="text-violet-300">{{ $specialOffer->subtitle }}</span>
                    @endif
                </h2>
                <p class="text-xl text-violet-100">
                    {{ $specialOffer->description }}
                </p>
                @if($specialOffer->features && count($specialOffer->features) > 0)
                    <div class="flex flex-wrap items-center gap-6 pt-4">
                        @foreach($specialOffer->features as $feature)
                            <div class="flex items-center space-x-2">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span>{{ $feature }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="relative">
                <div class="glitch-image-wrapper">
                    <img src="{{ $specialOffer->image_path ? asset('storage/' . $specialOffer->image_path) : 'https://images.unsplash.com/photo-1542751371-adc38448a05e?w=600' }}" 
                         alt="{{ $specialOffer->main_title }}" 
                         class="rounded-2xl shadow-2xl border border-violet-500/30">
                    <div class="glitch-overlay"></div>
                </div>
            </div>
        </div>
    </div>
</section>
@endif