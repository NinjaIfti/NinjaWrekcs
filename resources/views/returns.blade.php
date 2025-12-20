<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Returns Policy - NinjaWrekcs</title>
    <link rel="icon" type="image/png" href="{{ asset('img/fav.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-black text-white">
    @include('home.components.navigation')
    
    <section class="pt-32 pb-20 min-h-screen bg-gradient-to-b from-black via-violet-950/50 to-black">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-4xl md:text-5xl font-bold mb-8 text-center">
                <span class="glitch-text" data-text="Returns Policy">Returns Policy</span>
            </h1>

            <div class="bg-black/50 backdrop-blur-xl rounded-2xl border border-violet-500/30 p-8 md:p-12 space-y-6">
                <div>
                    <h2 class="text-2xl font-bold text-violet-400 mb-4">Return Eligibility</h2>
                    <p class="text-gray-300 leading-relaxed mb-4">
                        We want you to be completely satisfied with your purchase. Items can be returned within <strong class="text-white">7 days</strong> of delivery, provided they are:
                    </p>
                    <ul class="list-disc list-inside text-gray-300 space-y-2 ml-4">
                        <li>In original, unopened condition</li>
                        <li>In original packaging with all accessories</li>
                        <li>Not damaged or used</li>
                    </ul>
                </div>

                <div>
                    <h2 class="text-2xl font-bold text-violet-400 mb-4">How to Return</h2>
                    <ol class="list-decimal list-inside text-gray-300 space-y-2 ml-4">
                        <li>Contact us at <a href="tel:+8801533133309" class="text-violet-400 hover:text-violet-300">01533133309</a> or via <a href="https://wa.me/8801533133309" target="_blank" rel="noopener noreferrer" class="text-violet-400 hover:text-violet-300">WhatsApp</a> to initiate a return</li>
                        <li>Provide your order number and reason for return</li>
                        <li>We will provide return instructions and shipping address</li>
                        <li>Package the item securely in its original packaging</li>
                        <li>Ship the item back to us</li>
                    </ol>
                </div>

                <div>
                    <h2 class="text-2xl font-bold text-violet-400 mb-4">Refund Process</h2>
                    <p class="text-gray-300 leading-relaxed mb-4">
                        Once we receive and inspect the returned item:
                    </p>
                    <ul class="list-disc list-inside text-gray-300 space-y-2 ml-4">
                        <li>Refunds will be processed within 5-7 business days</li>
                        <li>Refunds will be issued to the original payment method</li>
                        <li>Shipping costs are non-refundable unless the item was defective</li>
                    </ul>
                </div>

                <div>
                    <h2 class="text-2xl font-bold text-violet-400 mb-4">Damaged or Defective Items</h2>
                    <p class="text-gray-300 leading-relaxed">
                        If you receive a damaged or defective item, please contact us immediately. We will arrange for a replacement or full refund, including return shipping costs.
                    </p>
                </div>

                <div class="bg-violet-900/20 rounded-xl border border-violet-500/30 p-6 mt-8">
                    <p class="text-gray-300">
                        <strong class="text-white">Need help with a return?</strong> Contact us at <a href="tel:+8801533133309" class="text-violet-400 hover:text-violet-300">01533133309</a> or via <a href="https://wa.me/8801533133309" target="_blank" rel="noopener noreferrer" class="text-violet-400 hover:text-violet-300">WhatsApp</a>.
                    </p>
                </div>
            </div>
        </div>
    </section>

    @include('home.components.footer')
    @include('home.styles')
</body>
</html>















