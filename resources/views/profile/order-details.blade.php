<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order #{{ $order->id }} - NinjaWrekcs</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-black text-white">
    @include('home.components.navigation')
    
    <section class="pt-32 pb-20 min-h-screen bg-gradient-to-b from-black via-violet-950/50 to-black">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Back Button -->
            <a href="{{ route('profile.index') }}" class="inline-flex items-center text-violet-400 hover:text-violet-300 mb-6 transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Profile
            </a>

            <h1 class="text-4xl md:text-5xl font-bold mb-8">
                <span class="glitch-text" data-text="Order #{{ $order->id }}">Order #{{ $order->id }}</span>
            </h1>

            <!-- Order Status Card -->
            <div class="bg-black/50 backdrop-blur-xl rounded-2xl border border-violet-500/30 p-6 mb-6">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <p class="text-sm text-gray-400 mb-1">Order Status</p>
                        <div class="flex items-center gap-3">
                            <span class="px-4 py-2 rounded-full text-sm font-semibold
                                {{ $order->status === 'pending' ? 'bg-yellow-500/20 text-yellow-400 border border-yellow-500/50' : '' }}
                                {{ $order->status === 'confirmed' ? 'bg-blue-500/20 text-blue-400 border border-blue-500/50' : '' }}
                                {{ $order->status === 'processing' ? 'bg-purple-500/20 text-purple-400 border border-purple-500/50' : '' }}
                                {{ $order->status === 'shipped' ? 'bg-indigo-500/20 text-indigo-400 border border-indigo-500/50' : '' }}
                                {{ $order->status === 'delivered' ? 'bg-green-500/20 text-green-400 border border-green-500/50' : '' }}
                                {{ $order->status === 'cancelled' ? 'bg-red-500/20 text-red-400 border border-red-500/50' : '' }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-400 mb-1">Order Date</p>
                        <p class="text-white font-semibold">{{ $order->created_at->format('M d, Y h:i A') }}</p>
                    </div>
                </div>
            </div>

            <!-- Order Details -->
            <div class="bg-black/50 backdrop-blur-xl rounded-2xl border border-violet-500/30 p-6 mb-6">
                <h2 class="text-2xl font-bold mb-6">Order Details</h2>
                
                <!-- Order Items -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold mb-4">Items Ordered</h3>
                    <div class="space-y-4">
                        @foreach($order->items as $item)
                            <div class="flex items-center gap-4 p-4 bg-black/30 rounded-lg border border-violet-500/20">
                                @if($item->product && $item->product->image)
                                    <img src="{{ asset('storage/' . $item->product->image) }}" 
                                         alt="{{ $item->product_name }}" 
                                         class="w-20 h-20 object-cover rounded-lg border border-violet-500/30">
                                @else
                                    <div class="w-20 h-20 bg-violet-500/20 rounded-lg border border-violet-500/30 flex items-center justify-center">
                                        <svg class="w-10 h-10 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                @endif
                                <div class="flex-1">
                                    <h4 class="font-semibold text-white mb-1">{{ $item->product_name }}</h4>
                                    <p class="text-sm text-gray-400">Quantity: {{ $item->quantity }}</p>
                                    <p class="text-sm text-gray-400">Price: ৳{{ number_format($item->price, 2) }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-bold text-violet-400">৳{{ number_format($item->subtotal, 2) }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="border-t border-violet-500/20 pt-6">
                    <div class="space-y-3">
                        <div class="flex justify-between text-gray-300">
                            <span>Subtotal</span>
                            <span>৳{{ number_format($order->subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-green-400">
                            <span>Discount</span>
                            <span>-৳{{ number_format($order->discount, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-xl font-bold text-white pt-3 border-t border-violet-500/20">
                            <span>Total</span>
                            <span>৳{{ number_format($order->total, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Delivery Information -->
            <div class="bg-black/50 backdrop-blur-xl rounded-2xl border border-violet-500/30 p-6 mb-6">
                <h2 class="text-2xl font-bold mb-6">Delivery Information</h2>
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-400 mb-1">Full Name</p>
                        <p class="text-white font-semibold">{{ $order->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-400 mb-1">Phone Number</p>
                        <p class="text-white font-semibold">{{ $order->phone }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-400 mb-1">Email</p>
                        <p class="text-white font-semibold">{{ $order->email }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-400 mb-1">Delivery Address</p>
                        <p class="text-white font-semibold">{{ $order->address }}</p>
                    </div>
                </div>
            </div>

            <!-- Payment Information -->
            <div class="bg-black/50 backdrop-blur-xl rounded-2xl border border-violet-500/30 p-6 mb-6">
                <h2 class="text-2xl font-bold mb-6">Payment Information</h2>
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-400 mb-1">Payment Method</p>
                        <p class="text-white font-semibold uppercase">{{ $order->payment_method }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-400 mb-1">Transaction Number</p>
                        <p class="text-white font-semibold">{{ $order->transaction_number }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-400 mb-1">Sending Number</p>
                        <p class="text-white font-semibold">{{ $order->sending_number }}</p>
                    </div>
                </div>
            </div>

            <!-- Order Notes -->
            @if($order->notes)
                <div class="bg-black/50 backdrop-blur-xl rounded-2xl border border-violet-500/30 p-6 mb-6">
                    <h2 class="text-2xl font-bold mb-4">Order Notes</h2>
                    <p class="text-gray-300">{{ $order->notes }}</p>
                </div>
            @endif

            <!-- Status Information -->
            <div class="bg-violet-500/10 backdrop-blur-xl rounded-2xl border border-violet-500/30 p-6">
                <h3 class="text-lg font-semibold mb-3 text-violet-400">Order Status Information</h3>
                <div class="space-y-2 text-sm text-gray-300">
                    @if($order->status === 'pending')
                        <p>Your order is pending confirmation. We'll process it soon!</p>
                    @elseif($order->status === 'confirmed')
                        <p>Your order has been confirmed and is being prepared.</p>
                    @elseif($order->status === 'processing')
                        <p>Your order is currently being processed.</p>
                    @elseif($order->status === 'shipped')
                        <p>Your order has been shipped and is on its way!</p>
                    @elseif($order->status === 'delivered')
                        <p>Your order has been delivered. Thank you for shopping with us!</p>
                    @elseif($order->status === 'cancelled')
                        <p>This order has been cancelled. If you have any questions, please contact support.</p>
                    @endif
                    <p class="mt-4 text-xs text-gray-400">Note: Orders typically take 10-15 days to arrive.</p>
                </div>
            </div>
        </div>
    </section>

    @include('home.components.footer')
    @include('home.styles')
</body>
</html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order #{{ $order->id }} - NinjaWrekcs</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-black text-white">
    @include('home.components.navigation')
    
    <section class="pt-32 pb-20 min-h-screen bg-gradient-to-b from-black via-violet-950/50 to-black">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Back Button -->
            <a href="{{ route('profile.index') }}" class="inline-flex items-center text-violet-400 hover:text-violet-300 mb-6 transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Profile
            </a>

            <h1 class="text-4xl md:text-5xl font-bold mb-8">
                <span class="glitch-text" data-text="Order #{{ $order->id }}">Order #{{ $order->id }}</span>
            </h1>

            <!-- Order Status Card -->
            <div class="bg-black/50 backdrop-blur-xl rounded-2xl border border-violet-500/30 p-6 mb-6">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <p class="text-sm text-gray-400 mb-1">Order Status</p>
                        <div class="flex items-center gap-3">
                            <span class="px-4 py-2 rounded-full text-sm font-semibold
                                {{ $order->status === 'pending' ? 'bg-yellow-500/20 text-yellow-400 border border-yellow-500/50' : '' }}
                                {{ $order->status === 'confirmed' ? 'bg-blue-500/20 text-blue-400 border border-blue-500/50' : '' }}
                                {{ $order->status === 'processing' ? 'bg-purple-500/20 text-purple-400 border border-purple-500/50' : '' }}
                                {{ $order->status === 'shipped' ? 'bg-indigo-500/20 text-indigo-400 border border-indigo-500/50' : '' }}
                                {{ $order->status === 'delivered' ? 'bg-green-500/20 text-green-400 border border-green-500/50' : '' }}
                                {{ $order->status === 'cancelled' ? 'bg-red-500/20 text-red-400 border border-red-500/50' : '' }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-400 mb-1">Order Date</p>
                        <p class="text-white font-semibold">{{ $order->created_at->format('M d, Y h:i A') }}</p>
                    </div>
                </div>
            </div>

            <!-- Order Details -->
            <div class="bg-black/50 backdrop-blur-xl rounded-2xl border border-violet-500/30 p-6 mb-6">
                <h2 class="text-2xl font-bold mb-6">Order Details</h2>
                
                <!-- Order Items -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold mb-4">Items Ordered</h3>
                    <div class="space-y-4">
                        @foreach($order->items as $item)
                            <div class="flex items-center gap-4 p-4 bg-black/30 rounded-lg border border-violet-500/20">
                                @if($item->product && $item->product->image)
                                    <img src="{{ asset('storage/' . $item->product->image) }}" 
                                         alt="{{ $item->product_name }}" 
                                         class="w-20 h-20 object-cover rounded-lg border border-violet-500/30">
                                @else
                                    <div class="w-20 h-20 bg-violet-500/20 rounded-lg border border-violet-500/30 flex items-center justify-center">
                                        <svg class="w-10 h-10 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                @endif
                                <div class="flex-1">
                                    <h4 class="font-semibold text-white mb-1">{{ $item->product_name }}</h4>
                                    <p class="text-sm text-gray-400">Quantity: {{ $item->quantity }}</p>
                                    <p class="text-sm text-gray-400">Price: ৳{{ number_format($item->price, 2) }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-bold text-violet-400">৳{{ number_format($item->subtotal, 2) }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="border-t border-violet-500/20 pt-6">
                    <div class="space-y-3">
                        <div class="flex justify-between text-gray-300">
                            <span>Subtotal</span>
                            <span>৳{{ number_format($order->subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-green-400">
                            <span>Discount</span>
                            <span>-৳{{ number_format($order->discount, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-xl font-bold text-white pt-3 border-t border-violet-500/20">
                            <span>Total</span>
                            <span>৳{{ number_format($order->total, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Delivery Information -->
            <div class="bg-black/50 backdrop-blur-xl rounded-2xl border border-violet-500/30 p-6 mb-6">
                <h2 class="text-2xl font-bold mb-6">Delivery Information</h2>
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-400 mb-1">Full Name</p>
                        <p class="text-white font-semibold">{{ $order->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-400 mb-1">Phone Number</p>
                        <p class="text-white font-semibold">{{ $order->phone }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-400 mb-1">Email</p>
                        <p class="text-white font-semibold">{{ $order->email }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-400 mb-1">Delivery Address</p>
                        <p class="text-white font-semibold">{{ $order->address }}</p>
                    </div>
                </div>
            </div>

            <!-- Payment Information -->
            <div class="bg-black/50 backdrop-blur-xl rounded-2xl border border-violet-500/30 p-6 mb-6">
                <h2 class="text-2xl font-bold mb-6">Payment Information</h2>
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-400 mb-1">Payment Method</p>
                        <p class="text-white font-semibold uppercase">{{ $order->payment_method }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-400 mb-1">Transaction Number</p>
                        <p class="text-white font-semibold">{{ $order->transaction_number }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-400 mb-1">Sending Number</p>
                        <p class="text-white font-semibold">{{ $order->sending_number }}</p>
                    </div>
                </div>
            </div>

            <!-- Order Notes -->
            @if($order->notes)
                <div class="bg-black/50 backdrop-blur-xl rounded-2xl border border-violet-500/30 p-6 mb-6">
                    <h2 class="text-2xl font-bold mb-4">Order Notes</h2>
                    <p class="text-gray-300">{{ $order->notes }}</p>
                </div>
            @endif

            <!-- Status Information -->
            <div class="bg-violet-500/10 backdrop-blur-xl rounded-2xl border border-violet-500/30 p-6">
                <h3 class="text-lg font-semibold mb-3 text-violet-400">Order Status Information</h3>
                <div class="space-y-2 text-sm text-gray-300">
                    @if($order->status === 'pending')
                        <p>Your order is pending confirmation. We'll process it soon!</p>
                    @elseif($order->status === 'confirmed')
                        <p>Your order has been confirmed and is being prepared.</p>
                    @elseif($order->status === 'processing')
                        <p>Your order is currently being processed.</p>
                    @elseif($order->status === 'shipped')
                        <p>Your order has been shipped and is on its way!</p>
                    @elseif($order->status === 'delivered')
                        <p>Your order has been delivered. Thank you for shopping with us!</p>
                    @elseif($order->status === 'cancelled')
                        <p>This order has been cancelled. If you have any questions, please contact support.</p>
                    @endif
                    <p class="mt-4 text-xs text-gray-400">Note: Orders typically take 10-15 days to arrive.</p>
                </div>
            </div>
        </div>
    </section>

    @include('home.components.footer')
    @include('home.styles')
</body>
</html>

