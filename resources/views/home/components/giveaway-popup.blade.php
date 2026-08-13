{{--
    Giveaway poster popup.

    Shown once, then suppressed for 3 minutes. The timestamp is written when the
    popup is *shown* (not when closed), so navigating away without closing it still
    counts as "seen". localStorage rather than sessionStorage so the cooldown holds
    across page loads and tabs.
--}}
<div id="giveawayPopup"
     class="fixed inset-0 z-[120] hidden items-center justify-center bg-black/85 backdrop-blur-sm p-4"
     role="dialog" aria-modal="true" aria-label="Xiaomi RC Drift Car giveaway">

    <div class="relative w-full max-w-sm sm:max-w-md max-h-[90vh] overflow-y-auto rounded-2xl border-2 border-yellow-500/60 shadow-2xl shadow-yellow-500/20 bg-black">

        <!-- Close -->
        <button type="button" onclick="closeGiveawayPopup()" aria-label="Close giveaway popup"
                class="absolute top-2 right-2 z-10 w-9 h-9 flex items-center justify-center rounded-full
                       bg-black/70 hover:bg-black text-white border border-white/30 hover:border-white
                       transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        <a href="{{ route('giveaway') }}" class="block">
            <picture>
                <source srcset="{{ asset('img/giveaway.webp') }}" type="image/webp">
                <img src="{{ asset('img/giveaway.png') }}"
                     alt="Win a Xiaomi RC Drift Car - NinjaWrecks giveaway. Minimum order 1500 taka, winner announced live on 30 August."
                     class="w-full h-auto" width="1024" height="1536" loading="lazy">
            </picture>
        </a>

        <div class="p-4 bg-gradient-to-r from-yellow-500/10 to-violet-500/10 border-t border-yellow-500/30">
            <a href="{{ route('giveaway') }}"
               class="block w-full text-center px-6 py-3 rounded-lg font-bold text-black
                      bg-gradient-to-r from-yellow-400 to-yellow-500
                      hover:shadow-lg hover:shadow-yellow-500/50 hover:scale-105 transition-all">
                See how to enter →
            </a>
        </div>
    </div>
</div>

<script>
(function () {
    const KEY = 'giveawayPopupSeenAt';
    const COOLDOWN_MS = 3 * 60 * 1000; // 3 minutes
    const popup = document.getElementById('giveawayPopup');
    if (!popup) return;

    function seenRecently() {
        try {
            const last = parseInt(localStorage.getItem(KEY) || '0', 10);
            return Number.isFinite(last) && (Date.now() - last) < COOLDOWN_MS;
        } catch (e) {
            // Private mode / storage blocked: don't nag, treat as already seen
            return true;
        }
    }

    function markSeen() {
        try { localStorage.setItem(KEY, String(Date.now())); } catch (e) { /* ignore */ }
    }

    window.closeGiveawayPopup = function () {
        popup.classList.add('hidden');
        popup.classList.remove('flex');
        document.body.style.overflow = '';
        markSeen();
    };

    function openGiveawayPopup() {
        popup.classList.remove('hidden');
        popup.classList.add('flex');
        document.body.style.overflow = 'hidden';
        markSeen(); // counts as seen the moment it is displayed
    }

    if (seenRecently()) return;

    // Small delay so it doesn't fight with the page painting
    setTimeout(openGiveawayPopup, 1200);

    // Backdrop click closes
    popup.addEventListener('click', function (e) {
        if (e.target === popup) window.closeGiveawayPopup();
    });

    // Escape closes
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !popup.classList.contains('hidden')) {
            window.closeGiveawayPopup();
        }
    });
})();
</script>
