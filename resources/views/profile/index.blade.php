<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Profile - NinjaWrecks</title>
    <link rel="icon" type="image/png" href="{{ asset('img/fav.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-black text-white">
    @include('home.components.navigation')
    
    <section class="pt-16 md:pt-28 pb-20 min-h-screen bg-gradient-to-b from-black via-violet-950/50 to-black">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-4xl md:text-5xl font-bold mb-8">
                <span class="glitch-text" data-text="My Profile">My Profile</span>
            </h1>

            @if(request('verified'))
                <div class="mb-6 p-4 bg-green-500/20 border border-green-500/50 rounded-lg flex items-start gap-3">
                    <svg class="w-6 h-6 text-green-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <p class="text-green-400 font-semibold">Email Verified Successfully!</p>
                        <p class="text-green-300 text-sm mt-1">Your email has been verified. You can now place orders and access all features.</p>
                    </div>
                </div>
            @endif

            @if(session('success'))
                <div class="mb-6 p-4 bg-green-500/20 border border-green-500/50 rounded-lg text-green-400">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 bg-red-500/20 border border-red-500/50 rounded-lg text-red-400">
                    {{ session('error') }}
                </div>
            @endif

            <div class="grid lg:grid-cols-4 gap-8">
                <!-- Sidebar Navigation -->
                <div class="lg:col-span-1">
                    <div class="bg-black/50 backdrop-blur-xl rounded-2xl border border-violet-500/30 p-6 sticky top-24">
                        <nav class="space-y-2">
                            <a href="#orders" class="block px-4 py-2 rounded-lg bg-violet-600 text-white font-semibold">
                                Order History
                            </a>
                            <a href="#payments" class="block px-4 py-2 rounded-lg text-gray-300 hover:bg-violet-500/20 hover:text-violet-400 transition">
                                Payment History
                            </a>
                            <a href="#settings" class="block px-4 py-2 rounded-lg text-gray-300 hover:bg-violet-500/20 hover:text-violet-400 transition">
                                Settings
                            </a>
                        </nav>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="lg:col-span-3 space-y-6">
                    <!-- Order History -->
                    <div id="orders" class="bg-black/50 backdrop-blur-xl rounded-2xl border border-violet-500/30 p-6">
                        <h2 class="text-2xl font-bold mb-6">Order History</h2>
                        
                        @if($orders->isEmpty())
                            <p class="text-gray-400 text-center py-8">No orders yet.</p>
                        @else
                            <div class="space-y-4">
                                @foreach($orders as $order)
                                    <div class="p-4 bg-black/30 rounded-lg border border-violet-500/20">
                                        <div class="flex justify-between items-start mb-4">
                                            <div>
                                                <div class="flex items-center gap-2 mb-1">
                                                    <p class="text-lg font-bold text-white">Order #{{ $order->id }}</p>
                                                    @if($order->is_preorder_booking)
                                                        <span class="px-2 py-0.5 bg-purple-500/20 text-purple-300 rounded-full text-xs font-semibold border border-purple-500/30">
                                                            📦 Pre-Order
                                                        </span>
                                                    @endif
                                                </div>
                                                <p class="text-sm text-gray-400">{{ $order->created_at->format('M d, Y h:i A') }}</p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-xl font-bold text-violet-400">৳{{ number_format($order->total, 2) }}</p>
                                                @if($order->is_preorder_booking)
                                                    <p class="text-xs text-purple-400 mt-1">Booking Fee Paid</p>
                                                @endif
                                                <span class="px-3 py-1 bg-violet-500/20 text-violet-300 rounded-full text-xs mt-2 inline-block">
                                                    {{ ucfirst($order->status) }}
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div class="space-y-2 mb-4">
                                            @foreach($order->items as $item)
                                                <div class="flex justify-between text-sm">
                                                    <span class="text-gray-300">{{ $item->product_name }} x{{ $item->quantity }}</span>
                                                    <span class="text-violet-400">৳{{ number_format($item->subtotal, 2) }}</span>
                                                </div>
                                            @endforeach
                                            @if($order->delivery_charge && $order->delivery_charge > 0)
                                                <div class="flex justify-between text-sm border-t border-violet-500/20 pt-2">
                                                    <span class="text-gray-400">Delivery Charge ({{ $order->delivery_location === 'outside_dhaka' ? 'Outside Dhaka' : 'Inside Dhaka' }}):</span>
                                                    <span class="text-violet-400">+৳{{ number_format($order->delivery_charge, 2) }}</span>
                                                </div>
                                            @endif
                                            @if($order->discount && $order->discount > 0)
                                                <div class="flex justify-between text-sm">
                                                    <span class="text-gray-400">Discount:</span>
                                                    <span class="text-green-400">-৳{{ number_format($order->discount, 2) }}</span>
                                                </div>
                                            @endif
                                            @if($order->is_preorder_booking && $order->booking_amount && $order->booking_amount > 0)
                                                <div class="flex justify-between text-sm border-t border-purple-500/20 pt-2">
                                                    <span class="text-purple-300">Booking Fee Paid:</span>
                                                    <span class="text-purple-400 font-semibold">৳{{ number_format($order->booking_amount, 2) }}</span>
                                                </div>
                                                <div class="text-xs text-purple-400 italic mt-1">
                                                    Remaining amount will be collected later
                                                </div>
                                            @endif
                                        </div>

                                        <div class="pt-4 border-t border-violet-500/20 flex justify-between items-center">
                                            <div>
                                                <p class="text-sm text-gray-400"><strong>Delivery:</strong> {{ $order->address }}</p>
                                                <p class="text-sm text-gray-400"><strong>Phone:</strong> {{ $order->phone }}</p>
                                            </div>
                                            <a href="{{ route('profile.orders.show', $order) }}" class="px-4 py-2 bg-violet-600 hover:bg-violet-700 text-white rounded-lg text-sm font-semibold transition">
                                                View Details
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Payment History -->
                    <div id="payments" class="bg-black/50 backdrop-blur-xl rounded-2xl border border-violet-500/30 p-6">
                        <h2 class="text-2xl font-bold mb-6">Payment History</h2>
                        
                        @if($orders->isEmpty())
                            <p class="text-gray-400 text-center py-8">No payment history yet.</p>
                        @else
                            <div class="space-y-4">
                                @foreach($orders as $order)
                                    <div class="p-4 bg-black/30 rounded-lg border border-violet-500/20">
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <div class="flex items-center gap-2 mb-1">
                                                    <p class="font-semibold text-white">Order #{{ $order->id }}</p>
                                                    @if($order->is_preorder_booking)
                                                        <span class="px-2 py-0.5 bg-purple-500/20 text-purple-300 rounded-full text-xs font-semibold border border-purple-500/30">
                                                            Pre-Order
                                                        </span>
                                                    @endif
                                                </div>
                                                <p class="text-sm text-gray-400">{{ $order->created_at->format('M d, Y') }}</p>
                                                <p class="text-sm text-gray-400">Transaction: {{ $order->transaction_number }}</p>
                                                @if($order->is_preorder_booking && $order->booking_amount && $order->booking_amount > 0)
                                                    <p class="text-xs text-purple-400 mt-1">Booking Fee Paid: ৳{{ number_format($order->booking_amount, 2) }}</p>
                                                @endif
                                            </div>
                                            <div class="text-right">
                                                <p class="text-lg font-bold text-violet-400">৳{{ number_format($order->total, 2) }}</p>
                                                @if($order->is_preorder_booking)
                                                    <p class="text-xs text-purple-400">Booking Payment</p>
                                                @else
                                                    <p class="text-xs text-gray-400">{{ strtoupper($order->payment_method) }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Settings -->
                    <div id="settings" class="bg-black/50 backdrop-blur-xl rounded-2xl border border-violet-500/30 p-6">
                        <h2 class="text-2xl font-bold mb-6">Settings</h2>
                        
                        <div class="space-y-6">
                            <!-- Personal Information -->
                            <div>
                                <h3 class="text-xl font-semibold mb-4">Personal Information</h3>
                                <form action="{{ route('profile.update.personal') }}" method="POST" class="space-y-4">
                                    @csrf
                                    <div>
                                        <label for="name" class="block text-sm font-medium text-gray-300 mb-2">Full Name</label>
                                        <input type="text" name="name" id="name" value="{{ $user->name }}" required class="w-full px-4 py-3 bg-black/50 border border-violet-500/30 rounded-lg text-white focus:border-violet-500 focus:ring-violet-500/50">
                                    </div>
                                    <div>
                                        <label for="email" class="block text-sm font-medium text-gray-300 mb-2">Email</label>
                                        <input type="email" name="email" id="email" value="{{ $user->email }}" required class="w-full px-4 py-3 bg-black/50 border border-violet-500/30 rounded-lg text-white focus:border-violet-500 focus:ring-violet-500/50">
                                    </div>
                                    <div>
                                        <label for="phone" class="block text-sm font-medium text-gray-300 mb-2">Phone Number</label>
                                        <input type="text" name="phone" id="phone" value="{{ $user->phone }}" required class="w-full px-4 py-3 bg-black/50 border border-violet-500/30 rounded-lg text-white focus:border-violet-500 focus:ring-violet-500/50">
                                    </div>
                                    <button type="submit" class="px-6 py-3 bg-violet-600 hover:bg-violet-700 text-white rounded-lg font-semibold transition">
                                        Update Personal Information
                                    </button>
                                </form>
                            </div>

                            <!-- Address Book -->
                            <div>
                                <h3 class="text-xl font-semibold mb-4">Address Book</h3>
                                <form action="{{ route('profile.update.address') }}" method="POST" class="space-y-4">
                                    @csrf
                                    <div>
                                        <label for="address" class="block text-sm font-medium text-gray-300 mb-2">Delivery Address</label>
                                        <textarea name="address" id="address" rows="3" required class="w-full px-4 py-3 bg-black/50 border border-violet-500/30 rounded-lg text-white focus:border-violet-500 focus:ring-violet-500/50">{{ $user->address }}</textarea>
                                    </div>
                                    <button type="submit" class="px-6 py-3 bg-violet-600 hover:bg-violet-700 text-white rounded-lg font-semibold transition">
                                        Update Address
                                    </button>
                                </form>
                            </div>

                            <!-- Password Reset -->
                            <div>
                                <h3 class="text-xl font-semibold mb-4">Password Reset</h3>
                                <form action="{{ route('profile.update.password') }}" method="POST" class="space-y-4">
                                    @csrf
                                    <div>
                                        <label for="current_password" class="block text-sm font-medium text-gray-300 mb-2">Current Password</label>
                                        <input type="password" name="current_password" id="current_password" required class="w-full px-4 py-3 bg-black/50 border border-violet-500/30 rounded-lg text-white focus:border-violet-500 focus:ring-violet-500/50">
                                    </div>
                                    <div>
                                        <label for="password" class="block text-sm font-medium text-gray-300 mb-2">New Password</label>
                                        <input type="password" name="password" id="password" required class="w-full px-4 py-3 bg-black/50 border border-violet-500/30 rounded-lg text-white focus:border-violet-500 focus:ring-violet-500/50">
                                    </div>
                                    <div>
                                        <label for="password_confirmation" class="block text-sm font-medium text-gray-300 mb-2">Confirm New Password</label>
                                        <input type="password" name="password_confirmation" id="password_confirmation" required class="w-full px-4 py-3 bg-black/50 border border-violet-500/30 rounded-lg text-white focus:border-violet-500 focus:ring-violet-500/50">
                                    </div>
                                    <button type="submit" class="px-6 py-3 bg-violet-600 hover:bg-violet-700 text-white rounded-lg font-semibold transition">
                                        Update Password
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('home.components.footer')
    @include('home.styles')
</body>
</html>

