<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FAQ - NinjaWrecks</title>
    <link rel="icon" type="image/png" href="{{ asset('img/fav.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-black text-white">
    @include('home.components.navigation')
    
    <section class="pt-32 pb-20 min-h-screen bg-gradient-to-b from-black via-violet-950/50 to-black">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-4xl md:text-5xl font-bold mb-8 text-center">
                <span class="glitch-text" data-text="Frequently Asked Questions">Frequently Asked Questions</span>
            </h1>

            <div class="bg-black/50 backdrop-blur-xl rounded-2xl border border-violet-500/30 p-8 md:p-12 space-y-6">
                <div>
                    <h2 class="text-xl font-bold text-violet-400 mb-3">How long does delivery take?</h2>
                    <p class="text-gray-300 leading-relaxed">
                        Orders typically take 10-15 business days to arrive after order confirmation. We'll keep you updated on your order status.
                    </p>
                </div>

                <div>
                    <h2 class="text-xl font-bold text-violet-400 mb-3">What payment methods do you accept?</h2>
                    <p class="text-gray-300 leading-relaxed">
                        We accept bKash, Nagad, and Cash on Delivery (COD) payment methods. You can choose your preferred payment method at checkout.
                    </p>
                </div>

                <div>
                    <h2 class="text-xl font-bold text-violet-400 mb-3">Are the products authentic?</h2>
                    <p class="text-gray-300 leading-relaxed">
                        Yes! All our collectibles are 100% authentic. We only source genuine Valorant merchandise to ensure quality and authenticity.
                    </p>
                </div>

                <div>
                    <h2 class="text-xl font-bold text-violet-400 mb-3">Can I cancel my order?</h2>
                    <p class="text-gray-300 leading-relaxed">
                        Yes, you can cancel your order before it ships. Contact us at 01533133309 or via WhatsApp to cancel. Refunds will be processed within 5-7 business days.
                    </p>
                </div>

                <div>
                    <h2 class="text-xl font-bold text-violet-400 mb-3">Do you ship outside Bangladesh?</h2>
                    <p class="text-gray-300 leading-relaxed">
                        Currently, we only ship within Bangladesh. We're working on expanding our shipping options in the future.
                    </p>
                </div>

                <div>
                    <h2 class="text-xl font-bold text-violet-400 mb-3">How do I track my order?</h2>
                    <p class="text-gray-300 leading-relaxed">
                        Once your order ships, you'll receive a tracking number. You can also check your order status in your profile under "Order History".
                    </p>
                </div>

                <div class="bg-violet-900/20 rounded-xl border border-violet-500/30 p-6 mt-8">
                    <p class="text-gray-300 text-center">
                        <strong class="text-white">Still have questions?</strong><br>
                        Contact us at <a href="tel:+8801533133309" class="text-violet-400 hover:text-violet-300">01533133309</a> or via <a href="https://wa.me/8801533133309" target="_blank" rel="noopener noreferrer" class="text-violet-400 hover:text-violet-300">WhatsApp</a>.
                    </p>
                </div>
            </div>
        </div>
    </section>

    @include('home.components.footer')
    @include('home.styles')
</body>
</html>




















