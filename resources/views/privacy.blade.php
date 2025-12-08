<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Privacy Policy - NinjaWrekcs</title>
    <link rel="icon" type="image/png" href="{{ asset('img/fav.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-black text-white">
    @include('home.components.navigation')
    
    <section class="pt-20 md:pt-32 pb-20 min-h-screen bg-gradient-to-b from-black via-violet-950/50 to-black">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-4xl md:text-5xl font-bold mb-8 text-center">
                <span class="glitch-text" data-text="Privacy Policy">Privacy Policy</span>
            </h1>

            <div class="bg-black/50 backdrop-blur-xl rounded-2xl border border-violet-500/30 p-8 md:p-12 space-y-6">
                <p class="text-gray-400 text-sm mb-6">Last updated: {{ date('F d, Y') }}</p>

                <div>
                    <h2 class="text-2xl font-bold text-violet-400 mb-4">Information We Collect</h2>
                    <p class="text-gray-300 leading-relaxed mb-4">
                        We collect information that you provide directly to us, including:
                    </p>
                    <ul class="list-disc list-inside text-gray-300 space-y-2 ml-4">
                        <li>Name, email address, phone number, and delivery address</li>
                        <li>Payment information (processed securely through payment gateways)</li>
                        <li>Order history and preferences</li>
                        <li>Account credentials (passwords are encrypted)</li>
                    </ul>
                </div>

                <div>
                    <h2 class="text-2xl font-bold text-violet-400 mb-4">How We Use Your Information</h2>
                    <p class="text-gray-300 leading-relaxed mb-4">
                        We use the information we collect to:
                    </p>
                    <ul class="list-disc list-inside text-gray-300 space-y-2 ml-4">
                        <li>Process and fulfill your orders</li>
                        <li>Communicate with you about your orders and account</li>
                        <li>Send you updates about new products and promotions (with your consent)</li>
                        <li>Improve our services and website experience</li>
                        <li>Comply with legal obligations</li>
                    </ul>
                </div>

                <div>
                    <h2 class="text-2xl font-bold text-violet-400 mb-4">Data Security</h2>
                    <p class="text-gray-300 leading-relaxed">
                        We implement appropriate security measures to protect your personal information. All sensitive data is encrypted and stored securely. However, no method of transmission over the internet is 100% secure.
                    </p>
                </div>

                <div>
                    <h2 class="text-2xl font-bold text-violet-400 mb-4">Sharing Your Information</h2>
                    <p class="text-gray-300 leading-relaxed">
                        We do not sell, trade, or rent your personal information to third parties. We may share information with service providers who assist us in operating our website and conducting business, but only to the extent necessary.
                    </p>
                </div>

                <div>
                    <h2 class="text-2xl font-bold text-violet-400 mb-4">Your Rights</h2>
                    <p class="text-gray-300 leading-relaxed mb-4">
                        You have the right to:
                    </p>
                    <ul class="list-disc list-inside text-gray-300 space-y-2 ml-4">
                        <li>Access and update your personal information</li>
                        <li>Request deletion of your account and data</li>
                        <li>Opt-out of marketing communications</li>
                        <li>Request a copy of your data</li>
                    </ul>
                </div>

                <div>
                    <h2 class="text-2xl font-bold text-violet-400 mb-4">Contact Us</h2>
                    <p class="text-gray-300 leading-relaxed">
                        If you have questions about this Privacy Policy, please contact us at <a href="tel:+8801533133309" class="text-violet-400 hover:text-violet-300">01533133309</a> or via <a href="https://wa.me/8801533133309" target="_blank" rel="noopener noreferrer" class="text-violet-400 hover:text-violet-300">WhatsApp</a>.
                    </p>
                </div>
            </div>
        </div>
    </section>

    @include('home.components.footer')
    @include('home.styles')
</body>
</html>


