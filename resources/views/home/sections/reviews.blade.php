<!-- Customer Reviews Section -->
<section class="py-16 md:py-24 bg-gradient-to-b from-black via-purple-950 to-black overflow-hidden">
    <!-- Background Effects -->
    <div class="absolute inset-0 opacity-30">
        <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-violet-500/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-purple-500/20 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s;"></div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <!-- Section Header -->
        <div class="text-center mb-12 md:mb-16">
            <span class="px-4 py-2 bg-violet-500/20 text-violet-300 rounded-full text-sm font-semibold border border-violet-500/30 backdrop-blur-sm inline-block mb-4">
                Happy Customers
            </span>
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-4">
                <span class="glitch-text-large" data-text="Customer Reviews">Customer Reviews</span>
            </h2>
            <p class="text-gray-400 text-lg max-w-2xl mx-auto">
                See what our valued customers have to say about their experience
            </p>
        </div>

        @php
            $reviews = \App\Models\Review::active()->ordered()->get();
        @endphp

        @if($reviews->count() > 0)
        <!-- Reviews Slideshow -->
        <div class="relative max-w-5xl mx-auto">
            <!-- Slideshow Container -->
            <div class="reviews-slideshow-container relative rounded-2xl overflow-hidden shadow-2xl border border-violet-500/30">
                <div class="reviews-slideshow relative" style="min-height: 500px;">
                    @foreach($reviews as $index => $review)
                    <!-- Review Slide -->
                    <div class="review-slide {{ $index === 0 ? 'active' : '' }} absolute inset-0 opacity-0 transition-opacity duration-700">
                        <div class="relative h-full bg-gradient-to-br from-violet-900/20 to-purple-900/20">
                            <!-- Review Image -->
                            <img src="{{ asset('storage/' . $review->image_path) }}" 
                                 alt="{{ $review->customer_name }}" 
                                 class="w-full h-full object-contain"
                                 loading="lazy">
                            
                            <!-- Gradient Overlay -->
                            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent"></div>

                            <!-- Review Info Overlay -->
                            <div class="absolute bottom-0 left-0 right-0 p-6 md:p-8 text-center">
                                <!-- Rating Stars -->
                                <div class="flex justify-center items-center mb-3">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="w-5 h-5 md:w-6 md:h-6 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-500' }}" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    @endfor
                                </div>

                                <!-- Review Text -->
                                @if($review->review_text)
                                <p class="text-white text-sm md:text-base lg:text-lg mb-4 max-w-3xl mx-auto leading-relaxed italic">
                                    "{{ $review->review_text }}"
                                </p>
                                @endif

                                <!-- Customer Name -->
                                <h4 class="text-violet-300 font-bold text-lg md:text-xl">
                                    {{ $review->customer_name }}
                                </h4>
                                <p class="text-gray-400 text-sm">Verified Customer</p>
                            </div>

                            <!-- Glitch Effect Border -->
                            <div class="absolute inset-0 pointer-events-none glitch-border"></div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Navigation Arrows -->
                <button class="review-nav review-nav-prev absolute left-4 top-1/2 -translate-y-1/2 z-20 bg-black/50 hover:bg-violet-600/80 text-white p-3 rounded-full backdrop-blur-sm transition-all duration-300 hover:scale-110"
                        onclick="changeReviewSlide(-1)">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <button class="review-nav review-nav-next absolute right-4 top-1/2 -translate-y-1/2 z-20 bg-black/50 hover:bg-violet-600/80 text-white p-3 rounded-full backdrop-blur-sm transition-all duration-300 hover:scale-110"
                        onclick="changeReviewSlide(1)">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>

                <!-- Slide Indicators -->
                <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex space-x-2 z-20">
                    @foreach($reviews as $index => $review)
                    <button class="review-dot {{ $index === 0 ? 'active' : '' }} w-3 h-3 rounded-full bg-white/30 hover:bg-white/60 transition-all duration-300"
                            data-slide="{{ $index }}"
                            onclick="showReviewSlide({{ $index }})"></button>
                    @endforeach
                </div>
            </div>
        </div>
        @else
        <!-- Empty State (visible only to admins - hidden for regular users) -->
        @auth
            @if(auth()->user()->email === 'ifti3061@gmail.com')
            <div class="text-center py-12">
                <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-violet-500/10 flex items-center justify-center">
                    <svg class="w-10 h-10 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                </div>
                <p class="text-gray-400 text-lg mb-4">No customer reviews yet</p>
                <a href="{{ route('admin.reviews') }}" class="text-violet-400 hover:text-violet-300 underline">
                    Add reviews from admin panel →
                </a>
            </div>
            @endif
        @endauth
        @endif
    </div>
</section>

<style>
    .review-slide {
        transition: opacity 0.7s ease-in-out;
    }

    .review-slide.active {
        opacity: 1 !important;
        z-index: 1;
    }

    .review-dot {
        transition: all 0.3s ease;
    }

    .review-dot.active {
        background-color: rgba(255, 255, 255, 0.9);
        width: 2rem;
    }

    .glitch-border {
        box-shadow: inset 0 0 0 1px rgba(167, 139, 250, 0.3);
    }

    .review-nav {
        opacity: 0.7;
    }

    .review-nav:hover {
        opacity: 1;
    }
</style>

<script>
    let currentReviewSlide = 0;
    const reviewSlides = document.querySelectorAll('.review-slide');
    const reviewDots = document.querySelectorAll('.review-dot');
    let reviewSlideInterval;

    function showReviewSlide(index) {
        // Hide all slides
        reviewSlides.forEach(slide => {
            slide.classList.remove('active');
        });

        // Remove active class from all dots
        reviewDots.forEach(dot => {
            dot.classList.remove('active');
        });

        // Show the selected slide
        if (reviewSlides[index]) {
            reviewSlides[index].classList.add('active');
            reviewDots[index]?.classList.add('active');
            currentReviewSlide = index;
        }

        // Reset auto-slide timer
        resetReviewSlideTimer();
    }

    function changeReviewSlide(direction) {
        let newIndex = currentReviewSlide + direction;

        if (newIndex >= reviewSlides.length) {
            newIndex = 0;
        } else if (newIndex < 0) {
            newIndex = reviewSlides.length - 1;
        }

        showReviewSlide(newIndex);
    }

    function autoSlideReviews() {
        changeReviewSlide(1);
    }

    function resetReviewSlideTimer() {
        clearInterval(reviewSlideInterval);
        reviewSlideInterval = setInterval(autoSlideReviews, 5000);
    }

    // Start auto-slide
    if (reviewSlides.length > 1) {
        reviewSlideInterval = setInterval(autoSlideReviews, 5000);
    }
</script>



