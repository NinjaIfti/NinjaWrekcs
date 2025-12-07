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
    
    /* Custom Scrollbar */
    ::-webkit-scrollbar {
        width: 10px;
    }
    
    ::-webkit-scrollbar-track {
        background: #000;
    }
    
    ::-webkit-scrollbar-thumb {
        background: linear-gradient(to bottom, #7c3aed, #a78bfa);
        border-radius: 5px;
    }
    
    ::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(to bottom, #a78bfa, #c4b5fd);
    }
</style>

