<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order Success - NinjaWrecks</title>
    <link rel="icon" type="image/png" href="{{ asset('img/fav.png') }}">
    @include('components.analytics')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-black text-white">
    @include('components.analytics-noscript')

    @php
        $order->loadMissing('items');
    @endphp
    <x-data-layer :payload="\App\Support\DataLayerHelper::purchasePayload($order)" />
    @include('home.components.navigation')
    
    <section class="pt-20 md:pt-32 pb-20 min-h-screen bg-gradient-to-b from-black via-violet-950/50 to-black">
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

                @if(session('account_created'))
                    <div class="mb-6 p-4 bg-green-500/10 border border-green-500/30 rounded-lg text-left">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-green-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <p class="text-green-400 text-sm font-semibold">🎉 Account Created!</p>
                                <p class="text-green-300 text-sm mt-1">
                                    Your account has been created and you're now logged in! 
                                    You can track your orders and save your information for faster checkout next time.
                                </p>
                                <p class="text-gray-400 text-xs mt-2">
                                    <strong>Email:</strong> {{ $order->email }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                @if(session('email_warning'))
                    <div class="mb-6 p-4 bg-yellow-500/10 border border-yellow-500/30 rounded-lg text-left">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-yellow-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <div>
                                <p class="text-yellow-400 text-sm font-semibold">Email Notice</p>
                                <p class="text-yellow-300 text-sm mt-1">{{ session('email_warning') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                @if($order->is_preorder_booking)
                    <div class="mb-6 p-4 bg-purple-500/10 border border-purple-500/30 rounded-lg text-left">
                        <div class="flex items-start gap-3">
                            <svg class="w-6 h-6 text-purple-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            <div>
                                <p class="text-purple-300 font-semibold mb-1">📦 Pre-Order Booking</p>
                                <p class="text-sm text-purple-400">This order will take <strong>2-3 weeks</strong> to deliver. You've paid the booking fee. The remaining DUE amount will be collected via Cash on Delivery when the product is ready.</p>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="space-y-4 mb-8 text-left">
                    @if($order->is_preorder_booking)
                        @php
                            $totalBeforeBooking = $order->subtotal + $order->delivery_charge - $order->discount;
                            $dueAmount = $totalBeforeBooking - ($order->booking_amount ?? 0);
                        @endphp
                        <div class="p-4 bg-purple-500/10 border border-purple-500/30 rounded-lg space-y-2">
                            <p class="text-sm text-gray-400 mb-2">Payment Summary</p>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-300">Subtotal:</span>
                                <span class="text-white">৳{{ number_format($order->subtotal, 2) }}</span>
                            </div>
                            @if($order->delivery_charge > 0)
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-300">Delivery Charge:</span>
                                    <span class="text-white">+৳{{ number_format($order->delivery_charge, 2) }}</span>
                                </div>
                            @endif
                            @if($order->discount > 0)
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-300">Discount:</span>
                                    <span class="text-green-400">-৳{{ number_format($order->discount, 2) }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between text-sm border-t border-purple-500/20 pt-2">
                                <span class="text-purple-300 font-semibold">Total:</span>
                                <span class="text-purple-400 font-semibold">৳{{ number_format($totalBeforeBooking, 2) }}</span>
                            </div>
                            @if($order->booking_amount > 0)
                                <div class="flex justify-between text-sm">
                                    <span class="text-purple-300">Booking Fee Paid:</span>
                                    <span class="text-purple-400 font-semibold">-৳{{ number_format($order->booking_amount, 2) }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between text-lg font-bold border-t border-purple-500/20 pt-2">
                                <span class="text-purple-300">DUE Amount:</span>
                                <span class="text-purple-400">৳{{ number_format($dueAmount, 2) }}</span>
                            </div>
                            <div class="text-xs text-purple-400 italic mt-2">
                                DUE amount will be collected via Cash on Delivery
                            </div>
                            <div class="flex justify-between text-sm border-t border-purple-500/20 pt-2 mt-2">
                                <span class="text-gray-300">Amount Paid (Booking Fee):</span>
                                <span class="text-2xl font-bold text-purple-400">৳{{ number_format($order->total, 2) }}</span>
                            </div>
                        </div>
                    @else
                        <div class="p-4 bg-violet-500/10 border border-violet-500/30 rounded-lg">
                            <p class="text-sm text-gray-400 mb-1">Total Amount</p>
                            <p class="text-3xl font-bold text-violet-400">৳{{ number_format($order->total, 2) }}</p>
                        </div>
                    @endif

                    <div class="p-4 bg-black/30 rounded-lg space-y-2">
                        <p class="text-sm text-gray-400 mb-2">Order Details</p>
                        <p class="text-white"><strong>Name:</strong> {{ $order->name }}</p>
                        <p class="text-white"><strong>Phone:</strong> {{ $order->phone }}</p>
                        <p class="text-white"><strong>Address:</strong> {{ $order->address }}</p>
                        @if($order->transaction_number)
                            <p class="text-white"><strong>Transaction #:</strong> {{ $order->transaction_number }}</p>
                        @endif
                        @if($order->sending_number)
                            <p class="text-white"><strong>Sending #:</strong> {{ $order->sending_number }}</p>
                        @endif
                        <p class="text-white"><strong>Payment Method:</strong> <span class="text-violet-400 uppercase">{{ $order->payment_method }}</span></p>
                        <p class="text-white"><strong>Status:</strong> <span class="text-violet-400">{{ ucfirst($order->status) }}</span></p>
                    </div>
                </div>

                <div class="space-y-3">
                    <a href="{{ route('profile.orders.show', $order) }}" class="block w-full px-6 py-3 bg-gradient-to-r from-violet-600 to-purple-600 text-white rounded-lg font-semibold hover:shadow-lg hover:shadow-violet-500/50 transition-all">
                        View Order Details
                    </a>
                    <a href="{{ route('profile.index') }}" class="block w-full px-6 py-3 bg-transparent border-2 border-violet-500/50 text-violet-400 rounded-lg font-semibold hover:bg-violet-500/10 transition-all">
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

