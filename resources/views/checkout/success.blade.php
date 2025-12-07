<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order Success - NinjaWrekcs</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-black text-white">
    @include('home.components.navigation')
    
    <section class="pt-32 pb-20 min-h-screen bg-gradient-to-b from-black via-violet-950/50 to-black">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="bg-black/50 backdrop-blur-xl rounded-2xl border border-violet-500/30 p-8">
                <div class="mb-6">
                    <div class="w-20 h-20 bg-green-500/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-12 h-12 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <h1 class="text-4xl font-bold mb-2">Order Placed Successfully!</h1>
                    <p class="text-gray-400">Order #{{ $order->id }}</p>
                </div>

                <div class="space-y-4 mb-8 text-left">
                    <div class="p-4 bg-violet-500/10 border border-violet-500/30 rounded-lg">
                        <p class="text-sm text-gray-400 mb-1">Total Amount</p>
                        <p class="text-3xl font-bold text-violet-400">৳{{ number_format($order->total, 2) }}</p>
                    </div>

                    <div class="p-4 bg-black/30 rounded-lg">
                        <p class="text-sm text-gray-400 mb-2">Order Details</p>
                        <p class="text-white"><strong>Name:</strong> {{ $order->name }}</p>
                        <p class="text-white"><strong>Phone:</strong> {{ $order->phone }}</p>
                        <p class="text-white"><strong>Address:</strong> {{ $order->address }}</p>
                        <p class="text-white"><strong>Status:</strong> <span class="text-violet-400">{{ ucfirst($order->status) }}</span></p>
                    </div>
                </div>

                <div class="space-y-3">
                    <a href="{{ route('profile.index') }}" class="block w-full px-6 py-3 bg-gradient-to-r from-violet-600 to-purple-600 text-white rounded-lg font-semibold hover:shadow-lg hover:shadow-violet-500/50 transition-all">
                        View Order History
                    </a>
                    <a href="{{ route('shop.index') }}" class="block w-full px-6 py-3 bg-transparent border-2 border-violet-500/50 text-violet-400 rounded-lg font-semibold hover:bg-violet-500/10 transition-all">
                        Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    </section>

    @include('home.components.footer')
    @include('home.styles')
</body>
</html>

