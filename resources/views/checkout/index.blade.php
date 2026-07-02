<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Checkout - NinjaWrecks</title>
    <link rel="icon" type="image/png" href="{{ asset('img/fav.png') }}">
    @include('components.analytics')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .delivery-option {
            position: relative;
        }
        .delivery-option input[type="radio"]:checked ~ .radio-indicator::after {
            content: '✓';
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            color: #8b5cf6;
            font-weight: bold;
            font-size: 1.25rem;
        }
    </style>
</head>
<body class="antialiased bg-black text-white">
    @include('components.analytics-noscript')

    @if($cartItems->count() > 0)
        <x-data-layer :payload="\App\Support\DataLayerHelper::beginCheckoutPayload($cartItems, (float) $cartSubTotal, $hasBookableItems, (float) $totalBookingAmount)" />
    @endif

    @include('home.components.navigation')
    
    <section class="pt-16 md:pt-28 pb-20 min-h-screen bg-gradient-to-b from-black via-violet-950/50 to-black">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-4xl md:text-5xl font-bold mb-8">
                <span class="glitch-text" data-text="Checkout">Checkout</span>
            </h1>

            @if(session('error'))
                <div class="mb-6 p-4 bg-red-500/20 border border-red-500/50 rounded-lg text-red-400">
                    {{ session('error') }}
                </div>
            @endif

            @guest
                <div class="mb-6 p-4 bg-blue-500/10 border border-blue-500/30 rounded-lg flex items-start gap-3">
                    <svg class="w-5 h-5 text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <p class="text-blue-300 font-semibold">Already have an account?</p>
                        <p class="text-gray-300 text-sm mt-1">
                            <a href="{{ route('login') }}" class="text-violet-400 hover:text-violet-300 underline">Log in here</a>
                            to checkout faster with saved information. You can also place an order as a guest (name, phone, and address only), or tick “Create account” below to register with email and password.
                        </p>
                    </div>
                </div>
            @endguest

            <form action="{{ route('checkout.store') }}" method="POST" class="grid lg:grid-cols-3 gap-8" data-user-email="{{ auth()->user()?->email ?? '' }}">
                @csrf
                <input type="hidden" id="checkout_discount_value" value="0">

                <!-- Left Column - Form -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Customer Information -->
                    <div class="bg-black/50 backdrop-blur-xl rounded-2xl border border-violet-500/30 p-6">
                        <h2 class="text-2xl font-bold mb-6">Customer Information</h2>
                        
                        <div class="space-y-4">
                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-300 mb-2">Full Name *</label>
                                <input type="text" name="name" id="name" value="{{ old('name', auth()->user()->name ?? '') }}" required style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;" class="w-full px-4 py-3 bg-black/50 border border-violet-500/30 rounded-lg text-white focus:border-violet-500 focus:ring-violet-500/50">
                                @error('name')
                                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Phone -->
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-300 mb-2">Phone Number *</label>
                                <input type="text" name="phone" id="phone" value="{{ old('phone', auth()->user()->phone ?? '') }}" required style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;" class="w-full px-4 py-3 bg-black/50 border border-violet-500/30 rounded-lg text-white focus:border-violet-500 focus:ring-violet-500/50">
                                @error('phone')
                                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Address -->
                            <div>
                                <label for="address" class="block text-sm font-medium text-gray-300 mb-2">Delivery Address *</label>
                                <textarea name="address" id="address" rows="3" required class="w-full px-4 py-3 bg-black/50 border border-violet-500/30 rounded-lg text-white focus:border-violet-500 focus:ring-violet-500/50">{{ old('address', auth()->user()->address ?? '') }}</textarea>
                                @error('address')
                                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Delivery Location -->
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-3">Delivery Location *</label>
                                <div class="grid grid-cols-2 gap-4">
                                    <label class="relative flex items-center p-4 bg-black/30 border-2 border-violet-500/30 rounded-lg cursor-pointer hover:border-violet-500/60 transition delivery-option">
                                        <input type="radio" name="delivery_location" value="inside_dhaka" data-charge="80" checked onchange="updateDeliveryCharge()" class="sr-only">
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between">
                                                <span class="text-white font-semibold">Inside Dhaka</span>
                                                <span class="text-violet-400 font-bold">৳80</span>
                                            </div>
                                            <p class="text-gray-400 text-xs mt-1">Dhaka city delivery</p>
                                        </div>
                                        <div class="radio-indicator"></div>
                                    </label>

                                    <label class="relative flex items-center p-4 bg-black/30 border-2 border-violet-500/30 rounded-lg cursor-pointer hover:border-violet-500/60 transition delivery-option">
                                        <input type="radio" name="delivery_location" value="outside_dhaka" data-charge="120" onchange="updateDeliveryCharge()" class="sr-only">
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between">
                                                <span class="text-white font-semibold">Outside Dhaka</span>
                                                <span class="text-violet-400 font-bold">৳120</span>
                                            </div>
                                            <p class="text-gray-400 text-xs mt-1">Nationwide delivery</p>
                                        </div>
                                        <div class="radio-indicator"></div>
                                    </label>
                                </div>
                                @error('delivery_location')
                                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Account (guests only): optional email, or create account with password -->
                            @guest
                            <div class="relative my-6">
                                <div class="absolute inset-0 flex items-center">
                                    <div class="w-full border-t border-violet-500/30"></div>
                                </div>
                                <div class="relative flex justify-center text-sm">
                                    <span class="px-4 bg-black text-gray-400">Email &amp; account (optional)</span>
                                </div>
                            </div>

                            <div class="p-4 bg-violet-500/10 border-l-4 border-violet-500 rounded-lg">
                                <div class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-violet-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <div>
                                        <p class="text-violet-300 font-semibold text-sm">Guest checkout</p>
                                        <p class="text-gray-300 text-xs mt-1">You can complete the order with name, phone, and address only. Add an email if you want order updates by mail. Tick “Create account” if you want a password and to log in after checkout.</p>
                                    </div>
                                </div>
                            </div>

                            <label class="flex items-start gap-3 cursor-pointer p-4 bg-black/30 border border-violet-500/30 rounded-lg">
                                <input type="checkbox" name="create_account" id="create_account" value="1" {{ old('create_account') ? 'checked' : '' }} class="mt-1 rounded border-violet-500/50 bg-black/50 text-violet-600 focus:ring-violet-500">
                                <span class="text-gray-300 text-sm"><span class="font-semibold text-white">Create account</span> — email and password required; you’ll be logged in after placing the order.</span>
                            </label>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-300 mb-2">
                                    <span id="email-label-required" class="hidden">Email address *</span>
                                    <span id="email-label-optional">Email address <span class="text-gray-500 font-normal">(optional)</span></span>
                                </label>
                                <input type="email" name="email" id="email" value="{{ old('email') }}" autocomplete="email" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;" class="w-full px-4 py-3 bg-black/50 border border-violet-500/30 rounded-lg text-white focus:border-violet-500 focus:ring-violet-500/50" placeholder="example@email.com">
                                @error('email')
                                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div id="create-account-password-block" class="space-y-2 {{ old('create_account') ? '' : 'hidden' }}">
                                <label for="password" class="block text-sm font-medium text-gray-300 mb-2">
                                    Create password *
                                    <span class="text-xs font-normal text-gray-400">(min. 8 characters)</span>
                                </label>
                                <input type="password" name="password" id="password" autocomplete="new-password" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; -webkit-text-security: disc !important; text-security: disc !important;" class="w-full px-4 py-3 bg-black/50 border border-violet-500/30 rounded-lg text-white focus:border-violet-500 focus:ring-violet-500/50" placeholder="Choose a secure password">
                                <p class="text-xs text-gray-400 flex items-start gap-2">
                                    <svg class="w-4 h-4 text-green-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>You’ll be logged in after placing your order.</span>
                                </p>
                                @error('password')
                                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            @endguest
                        </div>
                    </div>

                    <!-- Payment Information -->
                    <div class="bg-black/50 backdrop-blur-xl rounded-2xl border border-violet-500/30 p-6">
                        <h2 class="text-2xl font-bold mb-6">Payment Information</h2>
                        
                        <div class="space-y-4">
                            <!-- Payment Method Selection -->
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-3">Select Payment Method *</label>
                                <div class="space-y-3">
                                    <!-- Mobile Banking Option -->
                                    <label class="flex items-start space-x-3 p-4 bg-black/30 border-2 border-violet-500/30 rounded-lg cursor-pointer hover:border-violet-500/60 transition payment-method-option">
                                        <input type="radio" name="payment_method" value="bkash" checked onchange="togglePaymentFields()" class="mt-1 text-violet-600 focus:ring-violet-500">
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-2">
                                                <span class="text-white font-semibold">Mobile Banking</span>
                                                <span class="px-2 py-1 bg-violet-500/20 text-violet-300 text-xs rounded">Recommended</span>
                                            </div>
                                            <p class="text-gray-400 text-sm mt-1">bKash / Nagad</p>
                                        </div>
                                    </label>

                                    <!-- Cash on Delivery Option -->
                                    <label class="flex items-start space-x-3 p-4 bg-black/30 border-2 border-violet-500/30 rounded-lg transition payment-method-option {{ $hasBookableItems ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer hover:border-violet-500/60' }}">
                                        <input type="radio" name="payment_method" value="cod" onchange="togglePaymentFields()" class="mt-1 text-violet-600 focus:ring-violet-500" {{ $hasBookableItems ? 'disabled' : '' }}>
                                        <div class="flex-1">
                                            <span class="text-white font-semibold">Cash on Delivery</span>
                                            <p class="text-gray-400 text-sm mt-1">
                                                @if($hasBookableItems)
                                                    Not available for pre-order bookings
                                                @else
                                                    Pay when you receive your order
                                                @endif
                                            </p>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            @if($hasBookableItems)
                            <!-- Pre-order Booking Information -->
                            <div class="p-4 bg-purple-500/10 border border-purple-500/30 rounded-lg">
                                <div class="flex items-start space-x-3">
                                    <svg class="w-6 h-6 text-purple-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <div>
                                        <p class="text-purple-300 font-semibold mb-1">Pre-order Booking</p>
                                        <p class="text-gray-300 text-sm">
                                            You are booking pre-order products. You will pay ৳{{ number_format($totalBookingAmount, 2) }} as booking fee now.
                                            The remaining DUE amount will be collected via Cash on Delivery when the product is ready.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Mobile Banking Payment Details (Hidden when COD selected) -->
                            <div id="mobile-banking-details" class="space-y-4">
                                <div class="p-4 bg-violet-500/10 border border-violet-500/30 rounded-lg">
                                    <p class="text-violet-300 font-semibold mb-2">Send Moeny:</p>
                                    <p class="text-2xl font-bold text-white">01533133309</p>
                                    <p class="text-sm text-gray-400 mt-1">bKash / Nagad</p>
                                </div>

                                <!-- Transaction Number -->
                                <div>
                                    <label for="transaction_number" class="block text-sm font-medium text-gray-300 mb-2">Transaction Number <span class="text-red-400">*</span></label>
                                    <input type="text" name="transaction_number" id="transaction_number" value="{{ old('transaction_number') }}" required style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;" class="w-full px-4 py-3 bg-black/50 border border-violet-500/30 rounded-lg text-white focus:border-violet-500 focus:ring-violet-500/50" placeholder="Enter your transaction ID">
                                    @error('transaction_number')
                                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Sending Number -->
                                <div>
                                    <label for="sending_number" class="block text-sm font-medium text-gray-300 mb-2">Sending Number <span class="text-red-400">*</span></label>
                                    <input type="text" name="sending_number" id="sending_number" value="{{ old('sending_number') }}" required style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;" class="w-full px-4 py-3 bg-black/50 border border-violet-500/30 rounded-lg text-white focus:border-violet-500 focus:ring-violet-500/50" placeholder="Enter the number you sent from">
                                    @error('sending_number')
                                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                               
                            </div>

                            <!-- COD Information (Shown when COD selected) -->
                            <div id="cod-details" class="hidden">
                                <div class="p-4 bg-green-500/10 border border-green-500/30 rounded-lg">
                                    <div class="flex items-start space-x-3">
                                        <svg class="w-6 h-6 text-green-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <div>
                                            <p class="text-green-300 font-semibold">Cash on Delivery Selected</p>
                                            <p class="text-gray-300 text-sm mt-1">You will pay the full amount when you receive your order. Our delivery person will collect the payment.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if($hasBookableItems)
                            <!-- DUE Amount Information for Pre-orders -->
                            <div class="mt-4 p-4 bg-purple-500/10 border border-purple-500/30 rounded-lg">
                                <div class="flex items-start space-x-3">
                                    <svg class="w-6 h-6 text-purple-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    <div>
                                        <p class="text-purple-300 font-semibold mb-1">DUE Amount</p>
                                        <p class="text-gray-300 text-sm">
                                            The remaining DUE amount of <span class="text-purple-400 font-bold" id="due-amount-display">৳{{ number_format(($cartSubTotal + 80) - $totalBookingAmount, 2) }}</span> will be collected via Cash on Delivery when the product is ready.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Additional Options -->
                    <div class="bg-black/50 backdrop-blur-xl rounded-2xl border border-violet-500/30 p-6">
                        <div class="space-y-4">
                            @auth
                            <label class="flex items-start space-x-3 cursor-pointer">
                                <input type="checkbox" name="save_info" value="1" checked class="mt-1 rounded border-violet-500/50 bg-black/50 text-violet-600">
                                <span class="text-gray-300">Save this information for future orders</span>
                            </label>
                            @endauth

                            @if(!$hasBookableItems)
                            <label class="flex items-start space-x-3 cursor-pointer">
                                <input type="checkbox" name="terms_accepted" value="1" required class="mt-1 rounded border-violet-500/50 bg-black/50 text-violet-600">
                                <span class="text-gray-300">I accept that the order will take <span class="text-violet-400 font-semibold">1-2 days</span> to arrive *</span>
                            </label>
                            @error('terms_accepted')
                                <p class="text-sm text-red-400">{{ $message }}</p>
                            @enderror
                            @else
                            <!-- Hidden input to always accept terms for pre-orders -->
                            <input type="hidden" name="terms_accepted" value="1">
                            @endif

                            <!-- Notes -->
                            <div>
                                <label for="notes" class="block text-sm font-medium text-gray-300 mb-2">Additional Notes (Optional)</label>
                                <textarea name="notes" id="notes" rows="3" class="w-full px-4 py-3 bg-black/50 border border-violet-500/30 rounded-lg text-white focus:border-violet-500 focus:ring-violet-500/50" placeholder="Any special instructions...">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Order Summary -->
                <div class="lg:col-span-1">
                    <div class="bg-black/50 backdrop-blur-xl rounded-2xl border border-violet-500/30 p-6 sticky top-24">
                        <h2 class="text-2xl font-bold mb-6">Order Summary</h2>
                        
                        <div class="space-y-4 mb-6">
                            <!-- Cart Items -->
                            <div class="space-y-3 max-h-64 overflow-y-auto">
                                @foreach($cartItems as $item)
                                    @php
                                        // Check if item is bookable from attributes first
                                        $isBookable = isset($item->attributes->is_bookable) && (bool) $item->attributes->is_bookable;
                                        
                                        // Fetch product from database (we'll use it for both checking and getting price)
                                        $product = \App\Models\Product::find($item->id);
                                        
                                        // If not bookable from attributes, check database
                                        if (!$isBookable && $product) {
                                            $isBookable = (bool) $product->is_bookable;
                                        }
                                        
                                        // For pre-order items, ALWAYS use original price from database
                                        if ($isBookable && $product) {
                                            // Always use the product's price directly (original, not reduced)
                                            // display_price might have discounts, so use price field directly
                                            $displayPrice = (float) ($product->price ?? 0);
                                            // If price is 0 or null, try display_price as fallback
                                            if ($displayPrice == 0) {
                                                $displayPrice = (float) ($product->display_price ?? 0);
                                            }
                                        } elseif ($isBookable) {
                                            // Product not found but marked as bookable - use original_price from attributes
                                            $displayPrice = (float) ($item->attributes->original_price ?? $item->price);
                                        } else {
                                            // Regular items: use cart price as-is
                                            $displayPrice = (float) $item->price;
                                        }
                                    @endphp
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $item->attributes->image ? asset('storage/' . $item->attributes->image) : '/img/placeholder.jpg' }}" 
                                             alt="{{ $item->name }}" 
                                             class="w-16 h-16 object-cover rounded-lg border border-violet-500/30">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-semibold text-white truncate">{{ $item->name }}</p>
                                            <p class="text-xs text-gray-400">Qty: {{ $item->quantity }}</p>
                                            <p class="text-sm font-bold text-violet-400">৳{{ number_format($displayPrice * $item->quantity, 2) }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Coupon Code -->
                            <div class="border-t border-violet-500/20 pt-4">
                                <label class="block text-sm font-medium text-gray-300 mb-2">Have a Coupon?</label>
                                <div class="flex gap-2">
                                    <input type="text" 
                                           id="coupon_code_input" 
                                           name="coupon_code"
                                           placeholder="Enter coupon code" 
                                           class="flex-1 px-4 py-2 bg-black/50 border border-violet-500/30 rounded-lg text-white focus:border-violet-500 focus:ring-violet-500/50 uppercase"
                                           style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;">
                                    <button type="button" 
                                            onclick="applyCoupon()" 
                                            class="px-4 py-2 bg-violet-600 hover:bg-violet-700 text-white rounded-lg font-semibold transition">
                                        Apply
                                    </button>
                                </div>
                                <div id="coupon_message" class="mt-2 text-sm"></div>
                            </div>

                            <div class="border-t border-violet-500/20 pt-4 space-y-2">
                                <div class="flex justify-between text-gray-300">
                                    <span>Subtotal</span>
                                    <span id="subtotal_display">৳{{ number_format($cartSubTotal, 2) }}</span>
                                </div>
                                <div class="flex justify-between text-gray-300">
                                    <span>Delivery Charge</span>
                                    <span id="delivery_charge_display" class="text-violet-400 font-semibold">+৳80.00</span>
                                </div>
                                <div id="coupon_discount_row" class="flex justify-between text-green-400" style="display: none;">
                                    <span>Coupon Discount</span>
                                    <span id="coupon_discount_display">-৳0.00</span>
                                </div>
                                @if($hasBookableItems)
                                <div class="border-t border-violet-500/20 pt-2 pb-2">
                                    <div class="flex justify-between text-lg font-semibold">
                                        <span>Total</span>
                                        <span class="text-violet-400" id="total_before_booking">৳{{ number_format($cartSubTotal + 80, 2) }}</span>
                                    </div>
                                </div>
                                <div class="border-t border-purple-500/20 pt-2 space-y-2">
                                    <div class="flex justify-between text-purple-300">
                                        <span>Booking Fee</span>
                                        <span class="font-semibold">৳{{ number_format($totalBookingAmount, 2) }}</span>
                                    </div>
                                    <div class="border-t border-purple-500/20 pt-2">
                                        <div class="flex justify-between text-xl font-bold text-purple-300">
                                            <span>DUE</span>
                                            <span id="total_display">৳{{ number_format(($cartSubTotal + 80) - $totalBookingAmount, 2) }}</span>
                                        </div>
                                        <p class="text-xs text-purple-400 mt-1 italic">
                                            Remaining DUE will collected on Cash On Delivery
                                        </p>
                                    </div>
                                </div>
                                @else
                                <div class="border-t border-violet-500/20 pt-2">
                                    <div class="flex justify-between text-xl font-bold">
                                        <span>Total</span>
                                        <span class="text-violet-400" id="total_display">৳{{ number_format($cartSubTotal + 80, 2) }}</span>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        <button type="submit" class="w-full px-6 py-4 bg-gradient-to-r from-violet-600 to-purple-600 text-white rounded-lg font-semibold hover:shadow-lg hover:shadow-violet-500/50 hover:scale-105 transition-all relative overflow-hidden group">
                            <span class="relative z-10 flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Place Order
                            </span>
                            <span class="absolute inset-0 bg-gradient-to-r from-purple-600 to-violet-600 opacity-0 group-hover:opacity-100 transition-opacity"></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </section>

    @include('home.components.footer')
    
    
    
    @include('home.styles')

    <script>
        let deliveryCharge = 80; // Default: Inside Dhaka
        const baseSubtotal = {{ $cartSubTotal }}; // This is already calculated with original prices for pre-order items
        let currentDiscount = 0;

        // Update delivery charge
        function updateDeliveryCharge() {
            const selectedLocation = document.querySelector('input[name="delivery_location"]:checked');
            deliveryCharge = parseFloat(selectedLocation.dataset.charge);
            
            // Update delivery charge display
            document.getElementById('delivery_charge_display').textContent = '+৳' + deliveryCharge.toFixed(2);
            
            // Update visual selection
            document.querySelectorAll('.delivery-option').forEach(option => {
                option.classList.remove('border-violet-500', 'bg-violet-500/10');
                option.classList.add('border-violet-500/30');
            });
            selectedLocation.closest('.delivery-option').classList.remove('border-violet-500/30');
            selectedLocation.closest('.delivery-option').classList.add('border-violet-500', 'bg-violet-500/10');
            
            updateTotal();
        }

        // Update total calculation
        function updateTotal() {
            const bookingAmount = {{ $hasBookableItems ? $totalBookingAmount : 0 }};
            const total = baseSubtotal + deliveryCharge - currentDiscount;
            const discountField = document.getElementById('checkout_discount_value');
            if (discountField) {
                discountField.value = currentDiscount;
            }
            
            @if($hasBookableItems)
            // For pre-order items: Total = Subtotal + DC, DUE = Total - Booking Fee
            const totalBeforeBookingEl = document.getElementById('total_before_booking');
            if (totalBeforeBookingEl) {
                totalBeforeBookingEl.textContent = '৳' + total.toFixed(2);
            }
            
            const totalDisplay = document.getElementById('total_display');
            if (totalDisplay) {
                const due = total - bookingAmount;
                totalDisplay.textContent = '৳' + due.toFixed(2);
            }
            
            // Update DUE amount display in payment info box
            const dueAmountDisplay = document.getElementById('due-amount-display');
            if (dueAmountDisplay) {
                const due = total - bookingAmount;
                dueAmountDisplay.textContent = '৳' + due.toFixed(2);
            }
            @else
            // For regular items: Total = Subtotal + DC - Discount
            const totalDisplay = document.getElementById('total_display');
            if (totalDisplay) {
                totalDisplay.textContent = '৳' + total.toFixed(2);
            }
            @endif
        }

        // Toggle payment fields based on payment method
        function togglePaymentFields() {
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
            if (!paymentMethod) return;
            
            const hasBookableItems = {{ $hasBookableItems ? 'true' : 'false' }};
            
            // If bookable items, ensure COD cannot be selected
            if (hasBookableItems && paymentMethod.value === 'cod') {
                // Find and check mobile banking option instead
                const mobileBankingOption = document.querySelector('input[name="payment_method"][value="bkash"]');
                if (mobileBankingOption) {
                    mobileBankingOption.checked = true;
                    paymentMethod.checked = false;
                    alert('Cash on Delivery is not available for pre-order bookings. Please use Mobile Banking.');
                }
                return;
            }
            
            const mobileBankingDetails = document.getElementById('mobile-banking-details');
            const codDetails = document.getElementById('cod-details');
            const transactionNumber = document.getElementById('transaction_number');
            const sendingNumber = document.getElementById('sending_number');

            if (paymentMethod.value === 'cod') {
                // Hide mobile banking fields
                mobileBankingDetails.classList.add('hidden');
                // Show COD details
                codDetails.classList.remove('hidden');
                // Remove required attribute and clear values
                transactionNumber.removeAttribute('required');
                sendingNumber.removeAttribute('required');
                transactionNumber.value = '';
                sendingNumber.value = '';
            } else {
                // Show mobile banking fields
                mobileBankingDetails.classList.remove('hidden');
                // Hide COD details
                codDetails.classList.add('hidden');
                // Add required attribute
                transactionNumber.setAttribute('required', 'required');
                sendingNumber.setAttribute('required', 'required');
            }
        }

        function syncCreateAccountFields() {
            const cb = document.getElementById('create_account');
            const email = document.getElementById('email');
            const pwdBlock = document.getElementById('create-account-password-block');
            const pwd = document.getElementById('password');
            const labelReq = document.getElementById('email-label-required');
            const labelOpt = document.getElementById('email-label-optional');
            if (!cb || !email || !pwdBlock) return;

            const on = cb.checked;
            pwdBlock.classList.toggle('hidden', !on);

            if (on) {
                email.setAttribute('required', 'required');
                if (pwd) {
                    pwd.setAttribute('required', 'required');
                    pwd.removeAttribute('disabled');
                }
                if (labelReq) labelReq.classList.remove('hidden');
                if (labelOpt) labelOpt.classList.add('hidden');
            } else {
                email.removeAttribute('required');
                if (pwd) {
                    pwd.removeAttribute('required');
                    pwd.setAttribute('disabled', 'disabled');
                    pwd.value = '';
                }
                if (labelReq) labelReq.classList.add('hidden');
                if (labelOpt) labelOpt.classList.remove('hidden');
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            togglePaymentFields();
            updateDeliveryCharge();
            syncCreateAccountFields();
            document.getElementById('create_account')?.addEventListener('change', syncCreateAccountFields);
        });
    </script>

    <script>
        let appliedCoupon = null;

        function applyCoupon() {
            const couponInput = document.getElementById('coupon_code_input');
            const couponCode = couponInput.value.trim().toUpperCase();
            const messageDiv = document.getElementById('coupon_message');

            if (!couponCode) {
                showMessage('Please enter a coupon code.', 'error');
                return;
            }

            // Show loading state
            showMessage('Validating coupon...', 'info');

            fetch('{{ route("checkout.validate-coupon") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    coupon_code: couponCode,
                    subtotal: baseSubtotal
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    appliedCoupon = data;
                    updatePricing(data.discount);
                    showMessage(data.message, 'success');
                    couponInput.value = data.coupon_code;
                } else {
                    showMessage(data.message, 'error');
                    resetCoupon();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('Failed to validate coupon. Please try again.', 'error');
                resetCoupon();
            });
        }

        function updatePricing(couponDiscount) {
            currentDiscount = couponDiscount;
            
            document.getElementById('coupon_discount_row').style.display = 'flex';
            document.getElementById('coupon_discount_display').textContent = '-৳' + couponDiscount.toFixed(2);
            
            updateTotal();
        }

        function resetCoupon() {
            appliedCoupon = null;
            currentDiscount = 0;
            document.getElementById('coupon_discount_row').style.display = 'none';
            updateTotal();
        }

        function showMessage(message, type) {
            const messageDiv = document.getElementById('coupon_message');
            let className = '';
            
            if (type === 'success') {
                className = 'text-green-400';
            } else if (type === 'error') {
                className = 'text-red-400';
            } else {
                className = 'text-yellow-400';
            }
            
            messageDiv.className = 'mt-2 text-sm ' + className;
            messageDiv.textContent = message;
        }

        // Allow Enter key to apply coupon
        document.getElementById('coupon_code_input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                applyCoupon();
            }
        });
    </script>

    <script>
        // Autosave in-progress checkout details so abandoned checkouts show up
        // in the admin "Incomplete Orders" tab, even if the customer never presses Place Order.
        (function () {
            const saveProgressUrl = '{{ route("checkout.save-progress") }}';
            const csrfToken = '{{ csrf_token() }}';

            function collectProgressData() {
                return {
                    name: document.getElementById('name')?.value.trim() || '',
                    phone: document.getElementById('phone')?.value.trim() || '',
                    address: document.getElementById('address')?.value.trim() || '',
                    email: document.getElementById('email')?.value.trim() || '',
                    delivery_location: document.querySelector('input[name="delivery_location"]:checked')?.value || '',
                };
            }

            function saveProgress(useBeacon) {
                const data = collectProgressData();
                if (!data.name && !data.phone) return;

                if (useBeacon && navigator.sendBeacon) {
                    const formData = new FormData();
                    formData.append('_token', csrfToken);
                    Object.entries(data).forEach(([key, value]) => formData.append(key, value));
                    navigator.sendBeacon(saveProgressUrl, formData);
                    return;
                }

                fetch(saveProgressUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify(data),
                    keepalive: true,
                }).catch(() => {});
            }

            let debounceTimer = null;
            ['name', 'phone', 'address', 'email'].forEach(function (fieldId) {
                const field = document.getElementById(fieldId);
                if (!field) return;
                field.addEventListener('input', function () {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(function () { saveProgress(false); }, 1500);
                });
                field.addEventListener('blur', function () { saveProgress(false); });
            });

            document.addEventListener('visibilitychange', function () {
                if (document.visibilityState === 'hidden') saveProgress(true);
            });
        })();
    </script>

    @if($cartItems->count() > 0)
        @include('components.checkout-data-layer-scripts')
    @endif
</body>
</html>

