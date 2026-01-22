<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Checkout - NinjaWrecks</title>
    <link rel="icon" type="image/png" href="{{ asset('img/fav.png') }}">
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
                            to checkout faster with saved information. Otherwise, we'll create a new account for you automatically.
                        </p>
                    </div>
                </div>
            @endguest

            <form action="{{ route('checkout.store') }}" method="POST" class="grid lg:grid-cols-3 gap-8">
                @csrf

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

                            <!-- Account Creation Section (for non-logged users) -->
                            @guest
                            <!-- Divider -->
                            <div class="relative my-6">
                                <div class="absolute inset-0 flex items-center">
                                    <div class="w-full border-t border-violet-500/30"></div>
                                </div>
                                <div class="relative flex justify-center text-sm">
                                    <span class="px-4 bg-black text-gray-400">Account Details</span>
                                </div>
                            </div>

                            <!-- Info Box -->
                            <div class="p-4 bg-violet-500/10 border-l-4 border-violet-500 rounded-lg">
                                <div class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-violet-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <div>
                                        <p class="text-violet-300 font-semibold text-sm">Creating New Account</p>
                                        <p class="text-gray-300 text-xs mt-1">We'll automatically create an account for you to track your order easily</p>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-300 mb-2">
                                    Email Address *
                                    <span class="text-xs font-normal text-gray-400">(for your new account)</span>
                                </label>
                                <input type="email" name="email" id="email" value="{{ old('email') }}" required style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;" class="w-full px-4 py-3 bg-black/50 border border-violet-500/30 rounded-lg text-white focus:border-violet-500 focus:ring-violet-500/50" placeholder="example@email.com">
                                @error('email')
                                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-300 mb-2">
                                    Create Password *
                                    <span class="text-xs font-normal text-gray-400">(min. 8 characters)</span>
                                </label>
                                <input type="password" name="password" id="password" required style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; -webkit-text-security: disc !important; text-security: disc !important;" class="w-full px-4 py-3 bg-black/50 border border-violet-500/30 rounded-lg text-white focus:border-violet-500 focus:ring-violet-500/50" placeholder="Choose a secure password">
                                <p class="mt-2 text-xs text-gray-400 flex items-start gap-2">
                                    <svg class="w-4 h-4 text-green-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>You'll be automatically logged in after placing your order</span>
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
                                            The remaining amount (Product price - ৳{{ number_format($totalBookingAmount, 2) }}) will be collected later when the product is ready.
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
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $item->attributes->image ? asset('storage/' . $item->attributes->image) : '/img/placeholder.jpg' }}" 
                                             alt="{{ $item->name }}" 
                                             class="w-16 h-16 object-cover rounded-lg border border-violet-500/30">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-semibold text-white truncate">{{ $item->name }}</p>
                                            <p class="text-xs text-gray-400">Qty: {{ $item->quantity }}</p>
                                            <p class="text-sm font-bold text-violet-400">৳{{ number_format($item->price * $item->quantity, 2) }}</p>
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
                                <div class="border-t border-violet-500/20 pt-2 pb-2">
                                    <div class="flex justify-between text-lg font-semibold">
                                        <span>Total</span>
                                        <span class="text-violet-400" id="total_before_booking">৳{{ number_format($finalTotal + 80, 2) }}</span>
                                    </div>
                                </div>
                                @if($hasBookableItems)
                                <div class="border-t border-purple-500/20 pt-2 space-y-2">
                                    <div class="flex justify-between text-purple-300">
                                        <span>Booking Fee</span>
                                        <span class="font-semibold">৳{{ number_format($totalBookingAmount, 2) }}</span>
                                    </div>
                                    <div class="border-t border-purple-500/20 pt-2">
                                        <div class="flex justify-between text-xl font-bold text-purple-300">
                                            <span>DUE</span>
                                            <span id="total_display">৳{{ number_format(($finalTotal + 80) - $totalBookingAmount, 2) }}</span>
                                        </div>
                                        <p class="text-xs text-purple-400 mt-1 italic">
                                            Remaining DUE will collected on Cash On Delivery
                                    </div>
                                </div>
                                @else
                                <div class="border-t border-violet-500/20 pt-2">
                                    <div class="flex justify-between text-xl font-bold">
                                        <span>Total</span>
                                        <span class="text-violet-400" id="total_display">৳{{ number_format($finalTotal + 80, 2) }}</span>
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
        const baseSubtotal = {{ $cartSubTotal }};
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
            const totalBeforeBooking = baseSubtotal + deliveryCharge - currentDiscount;
            
            // Update total before booking
            const totalBeforeBookingEl = document.getElementById('total_before_booking');
            if (totalBeforeBookingEl) {
                totalBeforeBookingEl.textContent = '৳' + totalBeforeBooking.toFixed(2);
            }
            
            // Update final total/DUE
            const totalDisplay = document.getElementById('total_display');
            if (totalDisplay) {
                if (bookingAmount > 0) {
                    // For bookings: DUE = Total - Booking Fee
                    const due = totalBeforeBooking - bookingAmount;
                    totalDisplay.textContent = '৳' + due.toFixed(2);
                } else {
                    // For normal orders: Total = Subtotal + DC - Discount
                    totalDisplay.textContent = '৳' + totalBeforeBooking.toFixed(2);
                }
            }
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

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            togglePaymentFields();
            updateDeliveryCharge(); // Set initial delivery charge styling
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
</body>
</html>

