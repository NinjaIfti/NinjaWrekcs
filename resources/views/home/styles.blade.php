<!-- Violet Glitch Theme Styles -->
<style>
    /* Custom Font - Edo */
    @font-face {
        font-family: 'Edo';
        src: url('/fonts/edo.ttf') format('truetype');
        font-weight: normal;
        font-style: normal;
        font-display: swap;
    }
    
    /* Apply Edo Font to Body */
    body {
        font-family: 'Edo', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    }
    
    /* Edo Font Class (optional - use if you want to apply font to specific elements) */
    .edo-font {
        font-family: 'Edo', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    }
    
    /* Email inputs use default font to properly display @ symbol and other special characters */
    input[type="email"],
    input[type="email"]::placeholder {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif !important;
    }
    
    /* Glitch Text Effect */
    .glitch-text {
        position: relative;
        color: #a78bfa;
        text-transform: uppercase;
        font-weight: bold;
    }
    
    .glitch-text::before,
    .glitch-text::after {
        content: attr(data-text);
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }
    
    .glitch-text::before {
        left: 2px;
        text-shadow: -2px 0 #ff00c1;
        clip: rect(44px, 450px, 56px, 0);
        animation: glitch-anim 5s infinite linear alternate-reverse;
    }
    
    .glitch-text::after {
        left: -2px;
        text-shadow: -2px 0 #00fff9, 2px 2px #ff00c1;
        clip: rect(44px, 450px, 56px, 0);
        animation: glitch-anim2 1s infinite linear alternate-reverse;
    }
    
    .glitch-text-large {
        position: relative;
        color: #fff;
        text-transform: uppercase;
        font-weight: bold;
    }
    
    .glitch-text-large::before,
    .glitch-text-large::after {
        content: attr(data-text);
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }
    
    .glitch-text-large::before {
        left: 3px;
        text-shadow: -3px 0 #ff00c1;
        clip: rect(44px, 450px, 56px, 0);
        animation: glitch-anim 5s infinite linear alternate-reverse;
    }
    
    .glitch-text-large::after {
        left: -3px;
        text-shadow: -3px 0 #00fff9, 3px 3px #ff00c1;
        clip: rect(44px, 450px, 56px, 0);
        animation: glitch-anim2 1s infinite linear alternate-reverse;
    }
    
    @keyframes glitch-anim {
        0% { clip: rect(31px, 9999px, 94px, 0); }
        20% { clip: rect(54px, 9999px, 29px, 0); }
        40% { clip: rect(28px, 9999px, 85px, 0); }
        60% { clip: rect(2px, 9999px, 65px, 0); }
        80% { clip: rect(76px, 9999px, 102px, 0); }
        100% { clip: rect(27px, 9999px, 97px, 0); }
    }
    
    @keyframes glitch-anim2 {
        0% { clip: rect(65px, 9999px, 100px, 0); }
        20% { clip: rect(29px, 9999px, 54px, 0); }
        40% { clip: rect(94px, 9999px, 76px, 0); }
        60% { clip: rect(98px, 9999px, 14px, 0); }
        80% { clip: rect(9px, 9999px, 37px, 0); }
        100% { clip: rect(73px, 9999px, 85px, 0); }
    }
    
    /* Glitch Image Overlay */
    .glitch-image-wrapper {
        position: relative;
        overflow: hidden;
    }
    
    .glitch-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(
            90deg,
            transparent 0%,
            rgba(167, 139, 250, 0.1) 50%,
            transparent 100%
        );
        animation: glitch-scan 3s infinite;
        pointer-events: none;
    }
    
    @keyframes glitch-scan {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(100%); }
    }
    
    /* Glitch Background */
    .glitch-bg {
        background: 
            repeating-linear-gradient(
                0deg,
                rgba(167, 139, 250, 0.03) 0px,
                transparent 1px,
                transparent 2px,
                rgba(167, 139, 250, 0.03) 3px
            );
        animation: glitch-bg-move 0.1s infinite;
    }
    
    @keyframes glitch-bg-move {
        0% { background-position: 0 0; }
        100% { background-position: 0 2px; }
    }
    
    /* Grid Pattern */
    .grid-pattern {
        background-image: 
            linear-gradient(rgba(167, 139, 250, 0.1) 1px, transparent 1px),
            linear-gradient(90deg, rgba(167, 139, 250, 0.1) 1px, transparent 1px);
        background-size: 50px 50px;
        width: 100%;
        height: 100%;
    }
    
    /* Glitch Badge */
    .glitch-badge {
        animation: glitch-badge 3s infinite;
    }
    
    @keyframes glitch-badge {
        0%, 100% { transform: translate(0); }
        20% { transform: translate(-2px, 2px); }
        40% { transform: translate(-2px, -2px); }
        60% { transform: translate(2px, 2px); }
        80% { transform: translate(2px, -2px); }
    }
    
    /* Glitch Pulse */
    .glitch-pulse {
        animation: glitch-pulse 2s infinite;
    }
    
    @keyframes glitch-pulse {
        0%, 100% { 
            transform: scale(1);
            box-shadow: 0 0 0 0 rgba(167, 139, 250, 0.7);
        }
        50% { 
            transform: scale(1.1);
            box-shadow: 0 0 0 4px rgba(167, 139, 250, 0);
        }
    }
    
    /* Float Animation */
    @keyframes float {
        0%, 100% { transform: translate(0px, 0px) scale(1); }
        33% { transform: translate(30px, -50px) scale(1.1); }
        66% { transform: translate(-20px, 20px) scale(0.9); }
    }
    
    .animate-float {
        animation: float 7s infinite;
    }
    
    /* Glitch Particles */
    .glitch-particles::before,
    .glitch-particles::after {
        content: '';
        position: absolute;
        width: 4px;
        height: 4px;
        background: #a78bfa;
        border-radius: 50%;
        animation: particles 10s infinite;
    }
    
    .glitch-particles::before {
        top: 20%;
        left: 10%;
        animation-delay: 0s;
    }
    
    .glitch-particles::after {
        top: 60%;
        right: 15%;
        animation-delay: 2s;
    }
    
    @keyframes particles {
        0%, 100% { 
            transform: translate(0, 0);
            opacity: 0;
        }
        10% { opacity: 1; }
        90% { opacity: 1; }
        100% { 
            transform: translate(100px, -100px);
            opacity: 0;
        }
    }
    
    /* Smooth Scroll */
    html {
        scroll-behavior: smooth;
    }
    
    /* Hide scrollbar by default, show only when needed */
    html, body {
        overflow-x: hidden;
        scrollbar-gutter: stable;
    }
    
    /* Custom Scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
    }
    
    ::-webkit-scrollbar-track {
        background: transparent;
    }
    
    ::-webkit-scrollbar-thumb {
        background: linear-gradient(to bottom, #7c3aed, #a78bfa);
        border-radius: 5px;
    }
    
    ::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(to bottom, #a78bfa, #c4b5fd);
    }
    
    /* For Firefox */
    * {
        scrollbar-width: thin;
        scrollbar-color: #7c3aed transparent;
    }
    
    /* Cart Dropdown Styles */
    .cart-dropdown {
        pointer-events: auto;
    }
    
    /* Keep dropdown visible when hovering over cart icon or dropdown */
    .group\/cart:hover .cart-dropdown,
    .cart-dropdown:hover {
        opacity: 1 !important;
        visibility: visible !important;
    }
    
    /* Custom Scrollbar for Cart Dropdown */
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    
    .custom-scrollbar::-webkit-scrollbar-track {
        background: rgba(167, 139, 250, 0.1);
        border-radius: 3px;
    }
    
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(167, 139, 250, 0.5);
        border-radius: 3px;
    }
    
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: rgba(167, 139, 250, 0.7);
    }
    
    /* Profile Dropdown Styles */
    .group\/profile:hover .absolute {
        opacity: 1 !important;
        visibility: visible !important;
    }
    
    .group\/profile .absolute:hover {
        opacity: 1 !important;
        visibility: visible !important;
    }
    
    /* Mobile Menu Styles */
    #mobileMenu {
        transition: transform 0.3s ease-in-out;
    }
    
    #mobileMenu:not(.translate-x-full) {
        transform: translateX(0);
    }
    
    /* Mobile Bottom Nav - Ensure visibility */
    @media (max-width: 768px) {
        body {
            padding-bottom: 70px; /* Add padding to prevent content from being hidden behind bottom nav */
        }
    }
    
    /* Hero Slideshow Styles */
    .hero-slideshow-container {
        position: relative;
        width: 100%;
        height: 500px;
    }
    
    @media (min-width: 768px) {
        .hero-slideshow-container {
            height: 600px;
        }
    }
    
    .hero-slideshow {
        position: relative;
        width: 100%;
        height: 100%;
    }
    
    .hero-slide {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        transition: opacity 0.6s ease-in-out, transform 0.6s ease-in-out;
        transform: scale(1.05);
        z-index: 0;
        pointer-events: none;
    }
    
    .hero-slide.active {
        opacity: 1;
        transform: scale(1);
        z-index: 2;
        pointer-events: auto;
    }
    
    .hero-slide:not(.active) {
        z-index: 0;
    }
    
    .hero-slide img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transition: transform 8s ease-out;
    }
    
    .hero-slide.active img {
        transform: scale(1.05);
    }
    
    .glitch-image-wrapper {
        width: 100%;
        height: 100%;
        position: relative;
    }
    
    /* Navigation Dots */
    .hero-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        border: 2px solid rgba(167, 139, 250, 0.5);
        background: transparent;
        cursor: pointer;
        transition: all 0.3s ease;
        padding: 0;
    }
    
    .hero-dot:hover {
        background: rgba(167, 139, 250, 0.3);
        border-color: rgba(167, 139, 250, 0.8);
        transform: scale(1.2);
    }
    
    .hero-dot.active {
        background: #a78bfa;
        border-color: #a78bfa;
        box-shadow: 0 0 10px rgba(167, 139, 250, 0.8);
    }
    
    /* Navigation Arrows */
    .hero-nav {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        z-index: 20;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(10px);
        border: 2px solid rgba(167, 139, 250, 0.3);
        color: #a78bfa;
        width: 45px;
        height: 45px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        opacity: 0.7;
    }
    
    .hero-nav:hover {
        opacity: 1;
        background: rgba(167, 139, 250, 0.2);
        border-color: #a78bfa;
        transform: translateY(-50%) scale(1.1);
        box-shadow: 0 0 20px rgba(167, 139, 250, 0.5);
    }
    
    .hero-nav-prev {
        left: 15px;
    }
    
    .hero-nav-next {
        right: 15px;
    }
    
    /* Mobile Responsive Adjustments */
    @media (max-width: 768px) {
        .hero-nav {
            width: 35px;
            height: 35px;
        }
        
        .hero-nav svg {
            width: 18px;
            height: 18px;
        }
        
        .hero-nav-prev {
            left: 10px;
        }
        
        .hero-nav-next {
            right: 10px;
        }
        
        .hero-dot {
            width: 10px;
            height: 10px;
        }
    }
    
    /* Featured Products Slideshow */
    .featured-products-slideshow {
        position: relative;
    }
    
    .featured-products-track {
        display: flex;
    }
    
    .featured-products-slide {
        flex-shrink: 0;
    }
    
    /* Mobile Individual Product Cards */
    .featured-products-mobile-container,
    .featured-products-mobile-container-simple {
        position: relative;
        min-height: 450px;
    }
    
    .featured-products-mobile-card,
    .featured-products-mobile-card-simple {
        position: absolute !important;
        top: 0;
        left: 0;
        width: 100%;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.5s ease-in-out, visibility 0.5s ease-in-out;
        z-index: 1;
    }
    
    .featured-products-mobile-card.active,
    .featured-products-mobile-card-simple.active {
        opacity: 1;
        visibility: visible;
        z-index: 2;
    }
    
    @media (max-width: 768px) {
        .featured-products-prev,
        .featured-products-next {
            width: 36px;
            height: 36px;
            transform: translateY(-50%);
        }
        
        .featured-products-prev {
            left: -12px;
        }
        
        .featured-products-next {
            right: -12px;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize each slideshow container independently
    const containers = document.querySelectorAll('.hero-slideshow-container');
    
    containers.forEach((container) => {
        const slides = container.querySelectorAll('.hero-slide');
        const dots = container.querySelectorAll('.hero-dot');
        const prevBtn = container.querySelector('.hero-nav-prev');
        const nextBtn = container.querySelector('.hero-nav-next');
        let currentSlide = 0;
        let slideInterval;
    
        // Function to show a specific slide
        function showSlide(index) {
            if (slides.length === 0) return;
            
            // Ensure index is within bounds and loops properly
            const safeIndex = ((index % slides.length) + slides.length) % slides.length;
            
            // Add active class to new slide first (before removing from old)
            if (slides[safeIndex]) {
                slides[safeIndex].classList.add('active');
            }
            if (dots[safeIndex]) {
                dots[safeIndex].classList.add('active');
            }
            
            // Then remove active class from all other slides and dots
            slides.forEach((slide, idx) => {
                if (idx !== safeIndex) {
                    slide.classList.remove('active');
                }
            });
            dots.forEach((dot, idx) => {
                if (idx !== safeIndex) {
                    dot.classList.remove('active');
                }
            });
            
            currentSlide = safeIndex;
        }
        
        // Function to go to next slide
        function nextSlide() {
            if (slides.length === 0) return;
            const next = (currentSlide + 1) % slides.length;
            showSlide(next);
        }
        
        // Function to go to previous slide
        function prevSlide() {
            if (slides.length === 0) return;
            const prev = (currentSlide - 1 + slides.length) % slides.length;
            showSlide(prev);
        }
        
        // Auto-play slideshow
        function startSlideshow() {
            // Clear any existing interval first
            if (slideInterval) {
                clearInterval(slideInterval);
            }
            slideInterval = setInterval(() => {
                nextSlide();
            }, 5000); // Change slide every 5 seconds
        }
        
        function stopSlideshow() {
            if (slideInterval) {
                clearInterval(slideInterval);
                slideInterval = null;
            }
        }
        
        // Event listeners for navigation buttons
        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                nextSlide();
                stopSlideshow();
                startSlideshow();
            });
        }
        
        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                prevSlide();
                stopSlideshow();
                startSlideshow();
            });
        }
        
        // Event listeners for dots
        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                showSlide(index);
                stopSlideshow();
                startSlideshow();
            });
        });
        
        // Pause on hover (desktop only)
        if (container) {
            container.addEventListener('mouseenter', stopSlideshow);
            container.addEventListener('mouseleave', startSlideshow);
        }
        
        // Touch swipe support for mobile
        let touchStartX = 0;
        let touchEndX = 0;
        
        if (container) {
            container.addEventListener('touchstart', (e) => {
                touchStartX = e.changedTouches[0].screenX;
            });
            
            container.addEventListener('touchend', (e) => {
                touchEndX = e.changedTouches[0].screenX;
                handleSwipe();
            });
        }
        
        function handleSwipe() {
            if (touchEndX < touchStartX - 50) {
                nextSlide();
                stopSlideshow();
                startSlideshow();
            }
            if (touchEndX > touchStartX + 50) {
                prevSlide();
                stopSlideshow();
                startSlideshow();
            }
        }
        
        // Initialize slideshow
        if (slides.length > 0) {
            showSlide(0);
            startSlideshow();
        }
    });
});

// Featured Products Slideshow (Desktop)
document.addEventListener('DOMContentLoaded', function() {
    const slideshow = document.querySelector('.featured-products-slideshow');
    if (!slideshow) return;
    
    const track = slideshow.querySelector('.featured-products-track');
    const slides = slideshow.querySelectorAll('.featured-products-slide');
    const dots = slideshow.querySelectorAll('.featured-products-dot');
    const prevBtn = slideshow.querySelector('.featured-products-prev');
    const nextBtn = slideshow.querySelector('.featured-products-next');
    
    if (!track || slides.length === 0) return;
    
    let currentSlide = 0;
    let slideInterval;
    
    function showSlide(index) {
        if (slides.length === 0) return;
        const safeIndex = ((index % slides.length) + slides.length) % slides.length;
        
        if (track) {
            track.style.transform = `translateX(-${safeIndex * 100}%)`;
        }
        
        dots.forEach((dot, i) => {
            dot.classList.toggle('bg-violet-400', i === safeIndex);
            dot.classList.toggle('bg-violet-500/40', i !== safeIndex);
        });
        
        currentSlide = safeIndex;
    }
    
    function nextSlide() {
        const next = (currentSlide + 1) % slides.length;
        showSlide(next);
    }
    
    function prevSlide() {
        const prev = (currentSlide - 1 + slides.length) % slides.length;
        showSlide(prev);
    }
    
    function startSlideshow() {
        if (slideInterval) clearInterval(slideInterval);
        slideInterval = setInterval(nextSlide, 5000);
    }
    
    function stopSlideshow() {
        if (slideInterval) {
            clearInterval(slideInterval);
            slideInterval = null;
        }
    }
    
    if (nextBtn) {
        nextBtn.addEventListener('click', (e) => {
            e.preventDefault();
            nextSlide();
            stopSlideshow();
            startSlideshow();
        });
    }
    
    if (prevBtn) {
        prevBtn.addEventListener('click', (e) => {
            e.preventDefault();
            prevSlide();
            stopSlideshow();
            startSlideshow();
        });
    }
    
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            showSlide(index);
            stopSlideshow();
            startSlideshow();
        });
    });
    
    // Pause on hover
    slideshow.addEventListener('mouseenter', stopSlideshow);
    slideshow.addEventListener('mouseleave', startSlideshow);
    
    // Touch swipe support
    let touchStartX = 0;
    let touchEndX = 0;
    
    slideshow.addEventListener('touchstart', (e) => {
        touchStartX = e.changedTouches[0].screenX;
    });
    
    slideshow.addEventListener('touchend', (e) => {
        touchEndX = e.changedTouches[0].screenX;
        if (touchEndX < touchStartX - 50) {
            nextSlide();
            stopSlideshow();
            startSlideshow();
        }
        if (touchEndX > touchStartX + 50) {
            prevSlide();
            stopSlideshow();
            startSlideshow();
        }
    });
    
    // Initialize
    showSlide(0);
    startSlideshow();
});

// Mobile Featured Products Slideshow
document.addEventListener('DOMContentLoaded', function() {
    // Handle mobile slideshow for products > 4
    const mobileContainer = document.querySelector('.featured-products-mobile-container');
    if (mobileContainer) {
        const mobileCards = mobileContainer.querySelectorAll('.featured-products-mobile-card');
        const mobileDots = document.querySelectorAll('.featured-mobile-dot');
        let currentMobileSlide = 0;
        
        function showMobileSlide(index) {
            mobileCards.forEach((card, i) => {
                if (i === index) {
                    card.classList.add('active');
                } else {
                    card.classList.remove('active');
                }
            });
            
            mobileDots.forEach((dot, i) => {
                dot.classList.toggle('bg-violet-400', i === index);
                dot.classList.toggle('bg-violet-500/40', i !== index);
            });
            
            currentMobileSlide = index;
        }
        
        function nextMobileSlide() {
            currentMobileSlide = (currentMobileSlide + 1) % mobileCards.length;
            showMobileSlide(currentMobileSlide);
        }
        
        function prevMobileSlide() {
            currentMobileSlide = (currentMobileSlide - 1 + mobileCards.length) % mobileCards.length;
            showMobileSlide(currentMobileSlide);
        }
        
        mobileDots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                showMobileSlide(index);
            });
        });
        
        // Touch swipe support
        let touchStartX = 0;
        let touchEndX = 0;
        
        mobileContainer.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
        });
        
        mobileContainer.addEventListener('touchend', (e) => {
            touchEndX = e.changedTouches[0].screenX;
            if (touchEndX < touchStartX - 50) {
                nextMobileSlide();
            }
            if (touchEndX > touchStartX + 50) {
                prevMobileSlide();
            }
        });
    }
    
    // Handle simple grid mobile slideshow (4 or fewer products)
    const simpleContainer = document.querySelector('.featured-products-mobile-container-simple');
    if (simpleContainer) {
        const simpleCards = simpleContainer.querySelectorAll('.featured-products-mobile-card-simple');
        const simpleDots = document.querySelectorAll('.featured-simple-dot');
        const simplePrev = document.querySelector('.featured-simple-prev');
        const simpleNext = document.querySelector('.featured-simple-next');
        let currentSimpleSlide = 0;
        
        function showSimpleSlide(index) {
            simpleCards.forEach((card, i) => {
                if (i === index) {
                    card.classList.add('active');
                } else {
                    card.classList.remove('active');
                }
            });
            
            simpleDots.forEach((dot, i) => {
                dot.classList.toggle('bg-violet-400', i === index);
                dot.classList.toggle('bg-violet-500/40', i !== index);
            });
            
            currentSimpleSlide = index;
        }
        
        function nextSimpleSlide() {
            currentSimpleSlide = (currentSimpleSlide + 1) % simpleCards.length;
            showSimpleSlide(currentSimpleSlide);
        }
        
        function prevSimpleSlide() {
            currentSimpleSlide = (currentSimpleSlide - 1 + simpleCards.length) % simpleCards.length;
            showSimpleSlide(currentSimpleSlide);
        }
        
        if (simplePrev) {
            simplePrev.addEventListener('click', prevSimpleSlide);
        }
        
        if (simpleNext) {
            simpleNext.addEventListener('click', nextSimpleSlide);
        }
        
        simpleDots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                showSimpleSlide(index);
            });
        });
        
        // Touch swipe support
        let touchStartX = 0;
        let touchEndX = 0;
        
        simpleContainer.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
        });
        
        simpleContainer.addEventListener('touchend', (e) => {
            touchEndX = e.changedTouches[0].screenX;
            if (touchEndX < touchStartX - 50) {
                nextSimpleSlide();
            }
            if (touchEndX > touchStartX + 50) {
                prevSimpleSlide();
            }
        });
    }
});
</script>

