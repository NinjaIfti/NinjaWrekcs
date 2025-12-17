<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Terms of Service - NinjaWrekcs</title>
    <link rel="icon" type="image/png" href="{{ asset('img/fav.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-black text-white">
    @include('home.components.navigation')
    
    <section class="pt-32 pb-20 min-h-screen bg-gradient-to-b from-black via-violet-950/50 to-black">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-4xl md:text-5xl font-bold mb-8 text-center">
                <span class="glitch-text" data-text="Terms of Service">Terms of Service</span>
            </h1>

            <div class="bg-black/50 backdrop-blur-xl rounded-2xl border border-violet-500/30 p-8 md:p-12 space-y-6">
                <p class="text-gray-400 text-sm mb-6">Last updated: {{ date('F d, Y') }}</p>

                <div>
                    <h2 class="text-2xl font-bold text-violet-400 mb-4">Acceptance of Terms</h2>
                    <p class="text-gray-300 leading-relaxed">
                        By accessing and using NinjaWrekcs, you accept and agree to be bound by these Terms of Service. If you do not agree, please do not use our services.
                    </p>
                </div>

                <div>
                    <h2 class="text-2xl font-bold text-violet-400 mb-4">Pre-Orders</h2>
                    <p class="text-gray-300 leading-relaxed mb-4">
                        When placing a pre-order:
                    </p>
                    <ul class="list-disc list-inside text-gray-300 space-y-2 ml-4">
                        <li>You agree to pay ৳200 upfront as a deposit</li>
                        <li>The remaining balance will be collected via Cash on Delivery (COD) upon delivery</li>
                        <li>Delivery typically takes 10-15 business days</li>
                        <li>You accept that pre-orders may be subject to availability</li>
                    </ul>
                </div>

                <div>
                    <h2 class="text-2xl font-bold text-violet-400 mb-4">Payment Terms</h2>
                    <p class="text-gray-300 leading-relaxed">
                        All payments must be made through approved methods (bKash/Nagad). Payment information is processed securely. By providing payment information, you represent that you are authorized to use the payment method.
                    </p>
                </div>

                <div>
                    <h2 class="text-2xl font-bold text-violet-400 mb-4">Product Information</h2>
                    <p class="text-gray-300 leading-relaxed">
                        We strive to provide accurate product descriptions and images. However, we do not warrant that product descriptions or images are error-free. Prices are subject to change without notice.
                    </p>
                </div>

                <div>
                    <h2 class="text-2xl font-bold text-violet-400 mb-4">Order Cancellation</h2>
                    <p class="text-gray-300 leading-relaxed">
                        You may cancel your order before it ships. Once an order has shipped, standard return policies apply. Refunds will be processed according to our Returns Policy.
                    </p>
                </div>

                <div>
                    <h2 class="text-2xl font-bold text-violet-400 mb-4">Limitation of Liability</h2>
                    <p class="text-gray-300 leading-relaxed">
                        NinjaWrekcs shall not be liable for any indirect, incidental, or consequential damages arising from the use of our services or products.
                    </p>
                </div>

                <div>
                    <h2 class="text-2xl font-bold text-violet-400 mb-4">Changes to Terms</h2>
                    <p class="text-gray-300 leading-relaxed">
                        We reserve the right to modify these terms at any time. Changes will be effective immediately upon posting. Continued use of our services constitutes acceptance of modified terms.
                    </p>
                </div>

                <div>
                    <h2 class="text-2xl font-bold text-violet-400 mb-4">Contact</h2>
                    <p class="text-gray-300 leading-relaxed">
                        For questions about these Terms of Service, contact us at <a href="tel:+8801533133309" class="text-violet-400 hover:text-violet-300">01533133309</a> or via <a href="https://wa.me/8801533133309" target="_blank" rel="noopener noreferrer" class="text-violet-400 hover:text-violet-300">WhatsApp</a>.
                    </p>
                </div>
            </div>
        </div>
    </section>

    @include('home.components.footer')
    @include('home.styles')
</body>
</html>












