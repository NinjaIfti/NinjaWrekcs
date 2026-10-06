<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your Discount Code - NinjaWrecks</title>
    <link rel="icon" type="image/png" href="{{ asset('img/fav.png') }}">

    {{-- Scanned from a printed card, so it should not be indexed or shared on. --}}
    <meta name="robots" content="noindex, nofollow">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-black text-white">
    @include('home.components.navigation')

    {{-- Phone first: this page is only ever reached by scanning a QR code. --}}
    <section class="pt-32 pb-20 min-h-screen bg-gradient-to-b from-black via-violet-950/50 to-black">
        <div class="max-w-lg mx-auto px-4 sm:px-6">
            <div class="bg-black/50 backdrop-blur-xl rounded-2xl border border-violet-500/30 p-6 md:p-8 text-center">

                @php
                    $discount = $coupon
                        ? ($coupon->type === 'percentage'
                            ? rtrim(rtrim(number_format((float) $coupon->value, 2), '0'), '.') . '%'
                            : '৳' . rtrim(rtrim(number_format((float) $coupon->value, 2), '0'), '.'))
                        : null;
                @endphp

                @if($coupon && $isUsable)
                    <p class="text-sm uppercase tracking-widest text-violet-400 mb-2">Your discount</p>
                    <h1 class="text-3xl md:text-4xl font-bold mb-4">
                        <span class="glitch-text" data-text="{{ $discount }} off">{{ $discount }} off</span>
                    </h1>

                    <p class="text-gray-300 mb-6">Use this code at checkout.</p>

                    <div class="flex gap-2 mb-4">
                        <code id="agent-code-value"
                              class="flex-1 min-w-0 rounded-lg border-2 border-dashed border-violet-500/60 bg-black/60 px-4 py-4 text-2xl font-bold tracking-widest text-white">{{ $coupon->code }}</code>
                        <button type="button"
                                id="agent-code-copy"
                                data-code="{{ $coupon->code }}"
                                class="shrink-0 px-4 py-4 bg-violet-600 hover:bg-violet-700 rounded-lg font-semibold transition">
                            Copy
                        </button>
                    </div>

                    <ul class="text-left text-sm text-gray-400 space-y-1 mb-8">
                        @if($coupon->minimum_order)
                            <li>• Minimum order ৳{{ number_format((float) $coupon->minimum_order, 0) }}</li>
                        @endif
                        @if($coupon->type === 'percentage' && $coupon->maximum_discount)
                            <li>• Saves up to ৳{{ number_format((float) $coupon->maximum_discount, 0) }}</li>
                        @endif
                        @if($coupon->valid_until)
                            <li>• Valid until {{ $coupon->valid_until->format('j M Y') }}</li>
                        @endif
                        <li>• One code per order</li>
                    </ul>

                    <a href="{{ route('shop.index') }}"
                       class="block w-full px-6 py-4 bg-gradient-to-r from-violet-600 to-purple-600 rounded-lg font-semibold hover:shadow-lg hover:shadow-violet-500/50 transition">
                        Start shopping
                    </a>
                @else
                    {{-- Deactivated, used up or expired. Say so plainly rather than
                         printing a code the checkout would reject. --}}
                    <h1 class="text-2xl md:text-3xl font-bold mb-4">
                        <span class="glitch-text" data-text="Offer ended">Offer ended</span>
                    </h1>
                    <p class="text-gray-300 mb-8">
                        This code is not available any more, but there are deals on right now.
                    </p>
                    <a href="{{ route('deals.index') }}"
                       class="block w-full px-6 py-4 bg-gradient-to-r from-violet-600 to-purple-600 rounded-lg font-semibold hover:shadow-lg hover:shadow-violet-500/50 transition mb-3">
                        See today's deals
                    </a>
                    <a href="{{ route('shop.index') }}" class="block w-full px-6 py-4 border border-violet-500/40 rounded-lg font-semibold hover:border-violet-500 transition">
                        Browse the shop
                    </a>
                @endif
            </div>
        </div>
    </section>

    @include('home.components.footer')

    <script>
        document.getElementById('agent-code-copy')?.addEventListener('click', async function () {
            const code = this.dataset.code;
            const original = this.textContent;
            try {
                await navigator.clipboard.writeText(code);
            } catch {
                // clipboard is blocked without https or permission - select the
                // code instead so a long-press copy still works.
                const node = document.getElementById('agent-code-value');
                if (node) {
                    const range = document.createRange();
                    range.selectNodeContents(node);
                    const sel = window.getSelection();
                    sel.removeAllRanges();
                    sel.addRange(range);
                }
            }
            this.textContent = 'Copied';
            setTimeout(() => { this.textContent = original; }, 1500);
        });
    </script>
</body>
</html>
