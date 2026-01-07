<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Shipping Info - NinjaWrecks</title>
    <link rel="icon" type="image/png" href="{{ asset('img/fav.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-black text-white">
    @include('home.components.navigation')
    
    <section class="pt-20 md:pt-32 pb-20 min-h-screen bg-gradient-to-b from-black via-violet-950/50 to-black">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-4xl md:text-5xl font-bold mb-8 text-center">
                <span class="glitch-text" data-text="Shipping Information">Shipping Information</span>
            </h1>

            <div class="bg-black/50 backdrop-blur-xl rounded-2xl border border-violet-500/30 p-8 md:p-12 space-y-6">
                <div>
                    <h2 class="text-2xl font-bold text-violet-400 mb-4">Delivery Time</h2>
                    <p class="text-gray-300 leading-relaxed">
                        All orders will be delivered within <strong class="text-white">10-15 business days</strong> after order confirmation. We process orders as quickly as possible to ensure you receive your collectibles in perfect condition.
                    </p>
                </div>

                <div>
                    <h2 class="text-2xl font-bold text-violet-400 mb-4">Shipping Methods</h2>
                    <p class="text-gray-300 leading-relaxed mb-4">
                        We offer secure shipping throughout Bangladesh. All items are carefully packaged to ensure they arrive safely.
                    </p>
                    <ul class="list-disc list-inside text-gray-300 space-y-2 ml-4">
                        <li>Standard delivery via trusted courier services</li>
                        <li>Secure packaging for all collectibles</li>
                        <li>Tracking number provided for all orders</li>
                    </ul>
                </div>

                <div>
                    <h2 class="text-2xl font-bold text-violet-400 mb-4">Shipping Costs</h2>
                    <p class="text-gray-300 leading-relaxed">
                        Shipping costs are calculated at checkout based on your delivery address. We strive to keep shipping costs affordable while ensuring safe delivery.
                    </p>
                </div>

                <div>
                    <h2 class="text-2xl font-bold text-violet-400 mb-4">Order Tracking</h2>
                    <p class="text-gray-300 leading-relaxed">
                        Once your order is shipped, you will receive a tracking number via email or SMS. You can track your order status in your profile under "Order History".
                    </p>
                </div>

                <div class="bg-violet-900/20 rounded-xl border border-violet-500/30 p-6 mt-8">
                    <p class="text-gray-300">
                        <strong class="text-white">Questions about shipping?</strong> Contact us at <a href="tel:+8801533133309" class="text-violet-400 hover:text-violet-300">01533133309</a> or via <a href="https://wa.me/8801533133309" target="_blank" rel="noopener noreferrer" class="text-violet-400 hover:text-violet-300">WhatsApp</a>.
                    </p>
                </div>
            </div>
        </div>
    </section>

    @include('home.components.footer')
    @include('home.styles')
</body>
</html>


