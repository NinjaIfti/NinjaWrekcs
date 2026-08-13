{{--
    Site-wide giveaway announcement strip.

    Sits above the fixed navs, so it ships with the CSS that makes room for itself:
    body gets 32px of padding and both fixed navs move down by the same amount.
    Keeping that rule here means the offset can never drift away from the banner.
--}}
<style>
    :root { --giveaway-bar-h: 32px; }
    body { padding-top: var(--giveaway-bar-h); }
    /* Push the fixed desktop navbar and mobile top bar below the strip */
    nav.fixed.w-full,
    .md\:hidden.fixed.top-0 { top: var(--giveaway-bar-h); }

    @keyframes giveawayBlink { 0%, 100% { opacity: 1; } 50% { opacity: .45; } }
    .giveaway-blink { animation: giveawayBlink 1.2s ease-in-out infinite; }

    @media (prefers-reduced-motion: reduce) {
        .giveaway-blink { animation: none; }
    }
</style>

<a href="{{ route('giveaway') }}"
   class="fixed top-0 left-0 right-0 z-[60] flex items-center justify-center gap-2 h-8 px-3
          bg-gradient-to-r from-yellow-500 via-amber-400 to-yellow-500
          text-black text-xs sm:text-sm font-bold tracking-wide
          hover:brightness-110 transition-all overflow-hidden whitespace-nowrap">
    <span class="giveaway-blink">🎉</span>
    <span class="truncate">GIVEAWAY GOING ON — Win a Xiaomi RC Drift Car!</span>
    <span class="hidden sm:inline underline underline-offset-2">See details</span>
    <span class="giveaway-blink">🏎️</span>
</a>
