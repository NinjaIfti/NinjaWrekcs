<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Xiaomi RC Drift Car Giveaway - NinjaWrecks</title>
    <link rel="icon" type="image/png" href="{{ asset('img/fav.png') }}">

    @include('components.seo', [
        'title' => 'Xiaomi RC Drift Car Giveaway - NinjaWrecks',
        'description' => 'Win a Xiaomi RC Drift Car from NinjaWrecks. Place an order of at least 1500 taka, share our post and follow our page. Winner announced live on 30 August. Cash on Delivery all over Bangladesh.',
        'image' => asset('img/giveaway.png'),
        'url' => route('giveaway')
    ])

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-black text-white">
    @include('home.components.navigation')

    <div class="pt-16 md:pt-28 pb-24 md:pb-16">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Poster -->
            <div class="rounded-2xl overflow-hidden border-2 border-yellow-500/40 shadow-2xl shadow-yellow-500/10 bg-black">
                <picture>
                    <source srcset="{{ asset('img/giveaway.webp') }}" type="image/webp">
                    <img src="{{ asset('img/giveaway.png') }}"
                         alt="Win a Xiaomi RC Drift Car - NinjaWrecks giveaway. Minimum order 1500 taka, winner announced live on 30 August."
                         class="w-full h-auto" width="1024" height="1536">
                </picture>
            </div>

            <!-- Headline -->
            <div class="mt-10 text-center">
                <h1 class="text-3xl md:text-5xl font-extrabold text-white leading-tight">
                    🎉 XIAOMI RC DRIFT CAR GIVEAWAY! 🏎️🔥
                </h1>
                <p class="mt-4 text-lg md:text-xl text-gray-300">
                    Want to take home an awesome Xiaomi RC Drift Car? This one's for you! 👀
                </p>
            </div>

            <div class="mt-10 grid gap-6 md:grid-cols-2">
                <!-- How to enter -->
                <div class="bg-gradient-to-br from-gray-900 to-black rounded-2xl border border-yellow-500/30 p-6 md:p-8">
                    <h2 class="text-2xl font-bold text-yellow-400 mb-5">🎁 HOW TO ENTER</h2>
                    <ul class="space-y-4 text-gray-200">
                        <li class="flex gap-3">
                            <span class="text-xl shrink-0">🛒</span>
                            <span>Place an order of at least <strong class="text-white">৳1,500</strong></span>
                        </li>
                        <li class="flex gap-3">
                            <span class="text-xl shrink-0">📱</span>
                            <span>Share our giveaway post on your story — <strong class="text-yellow-400">MUST</strong></span>
                        </li>
                        <li class="flex gap-3">
                            <span class="text-xl shrink-0">❤️</span>
                            <span>Follow our page — <strong class="text-yellow-400">MUST</strong></span>
                        </li>
                        <li class="flex gap-3">
                            <span class="text-xl shrink-0">🎟️</span>
                            <span>Orders placed from <strong class="text-white">1 August</strong> are automatically eligible!</span>
                        </li>
                    </ul>

                    <div class="mt-6 pt-6 border-t border-yellow-500/20 space-y-2">
                        <p class="text-gray-200">🔥 Every qualifying order = <strong class="text-white">1 entry</strong></p>
                        <p class="text-yellow-400 font-semibold">👉 More qualifying orders = more chances to win!</p>
                    </div>
                </div>

                <!-- Winner selection -->
                <div class="bg-gradient-to-br from-gray-900 to-black rounded-2xl border border-violet-500/30 p-6 md:p-8">
                    <h2 class="text-2xl font-bold text-violet-400 mb-5">🎡 WINNER SELECTION</h2>
                    <ul class="space-y-4 text-gray-200">
                        <li class="flex gap-3"><span class="text-xl shrink-0">📅</span><span class="text-2xl font-extrabold text-white">30 AUG</span></li>
                        <li class="flex gap-3"><span class="text-xl shrink-0">🔴</span><span><strong class="text-white">LIVE SPIN</strong> on our page</span></li>
                        <li class="flex gap-3"><span class="text-xl shrink-0">🏆</span><span>Winner will be announced <strong class="text-white">LIVE!</strong></span></li>
                    </ul>

                    <div class="mt-6 pt-6 border-t border-violet-500/20">
                        <p class="text-gray-200">🚚 Cash on Delivery available all over Bangladesh 🇧🇩</p>
                    </div>
                </div>
            </div>

            <!-- Follow / CTA -->
            <div class="mt-10 bg-gradient-to-r from-yellow-500/10 to-violet-500/10 rounded-2xl border border-yellow-500/30 p-6 md:p-8 text-center">
                <p class="text-xl md:text-2xl font-bold text-white">
                    Don't miss your chance to drift home with your own RC car! 🏎️💨
                </p>
                <p class="mt-2 text-gray-300">Follow us so you don't miss the live draw — following is required to win.</p>

                <div class="mt-6 flex flex-col sm:flex-row gap-3 justify-center">
                    <a href="https://www.instagram.com/ninja_wrecks/" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-lg font-semibold text-white bg-gradient-to-r from-fuchsia-600 to-pink-600 hover:shadow-lg hover:shadow-pink-500/40 hover:scale-105 transition-all">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M12 2.16c3.2 0 3.58.01 4.85.07 1.17.05 1.8.25 2.23.41.56.22.96.48 1.38.9.42.42.68.82.9 1.38.16.42.36 1.06.41 2.23.06 1.27.07 1.65.07 4.85s-.01 3.58-.07 4.85c-.05 1.17-.25 1.8-.41 2.23-.22.56-.48.96-.9 1.38-.42.42-.82.68-1.38.9-.42.16-1.06.36-2.23.41-1.27.06-1.65.07-4.85.07s-3.58-.01-4.85-.07c-1.17-.05-1.8-.25-2.23-.41-.56-.22-.96-.48-1.38-.9-.42-.42-.68-.82-.9-1.38-.16-.42-.36-1.06-.41-2.23C2.17 15.58 2.16 15.2 2.16 12s.01-3.58.07-4.85c.05-1.17.25-1.8.41-2.23.22-.56.48-.96.9-1.38.42-.42.82-.68 1.38-.9.42-.16 1.06-.36 2.23-.41C8.42 2.17 8.8 2.16 12 2.16zm0 3.68a6.16 6.16 0 100 12.32 6.16 6.16 0 000-12.32zm0 10.16a4 4 0 110-8 4 4 0 010 8zm7.85-10.4a1.44 1.44 0 11-2.88 0 1.44 1.44 0 012.88 0z"/>
                        </svg>
                        Follow on Instagram
                    </a>
                    <a href="https://www.facebook.com/ninjawrecks" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-lg font-semibold text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:shadow-lg hover:shadow-blue-500/40 hover:scale-105 transition-all">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M22 12a10 10 0 10-11.56 9.88v-6.99H7.9V12h2.54V9.8c0-2.5 1.49-3.89 3.77-3.89 1.09 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56V12h2.78l-.45 2.89h-2.33v6.99A10 10 0 0022 12z"/>
                        </svg>
                        Follow on Facebook
                    </a>
                </div>

                <a href="{{ route('shop.index') }}"
                   class="mt-6 inline-flex items-center justify-center gap-2 px-8 py-4 rounded-lg font-bold text-black bg-gradient-to-r from-yellow-400 to-yellow-500 hover:shadow-lg hover:shadow-yellow-500/50 hover:scale-105 transition-all">
                    🛒 Shop now to enter
                </a>

                <p class="mt-6 text-2xl">Good luck! 🍀</p>
            </div>

            <!-- Hashtags -->
            <p class="mt-8 text-center text-sm text-gray-500 break-words">
                #Giveaway #Xiaomi #RCDiftCar #RCDrift #BangladeshGiveaway #GiveawayBD #ContestBD #XiaomiRC #RCCar #Gaming #Bangladesh #CashOnDelivery
            </p>
        </div>
    </div>

    @include('home.components.footer')
    @include('home.styles')
</body>
</html>
