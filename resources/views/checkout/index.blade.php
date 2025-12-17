<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Checkout - NinjaWrekcs</title>
    <link rel="icon" type="image/png" href="{{ asset('img/fav.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-black text-white">
    @include('home.components.navigation')
    
    <section class="pt-32 pb-20 min-h-screen bg-gradient-to-b from-black via-violet-950/50 to-black">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-4xl md:text-5xl font-bold mb-8">
                <span class="glitch-text" data-text="Checkout">Checkout</span>
            </h1>

            @if(session('error'))
                <div class="mb-6 p-4 bg-red-500/20 border border-red-500/50 rounded-lg text-red-400">
                    {{ session('error') }}
                </div>
            @endif

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

                            <!-- Email (for non-logged users) -->
                            @guest
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-300 mb-2">Email *</label>
                                <input type="email" name="email" id="email" value="{{ old('email') }}" required style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;" class="w-full px-4 py-3 bg-black/50 border border-violet-500/30 rounded-lg text-white focus:border-violet-500 focus:ring-violet-500/50">
                                @error('email')
                                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Password (only for non-logged users) -->
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-300 mb-2">Password *</label>
                                <input type="password" name="password" id="password" required style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; -webkit-text-security: disc !important; text-security: disc !important;" class="w-full px-4 py-3 bg-black/50 border border-violet-500/30 rounded-lg text-white focus:border-violet-500 focus:ring-violet-500/50">
                                <p class="mt-1 text-xs text-gray-400">A new account will be created with this password</p>
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
                            <div class="p-4 bg-green-500/10 border border-green-500/50 rounded-lg">
                                <p class="text-green-300 font-bold text-lg mb-1">💰 Pre-Order Special</p>
                                <p class="text-white font-semibold">Pay only <span class="text-green-400 text-xl">৳200</span> now, pay rest on <span class="text-green-400">COD</span></p>
                            </div>
                            
                            <div class="p-4 bg-violet-500/10 border border-violet-500/30 rounded-lg">
                                <p class="text-violet-300 font-semibold mb-2">Send payment to:</p>
                                <p class="text-2xl font-bold text-white">01533133309</p>
                                <p class="text-sm text-gray-400 mt-1">bKash / Nagad</p>
                            </div>

                            <!-- Transaction Number -->
                            <div>
                                <label for="transaction_number" class="block text-sm font-medium text-gray-300 mb-2">Transaction Number *</label>
                                <input type="text" name="transaction_number" id="transaction_number" value="{{ old('transaction_number') }}" required style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;" class="w-full px-4 py-3 bg-black/50 border border-violet-500/30 rounded-lg text-white focus:border-violet-500 focus:ring-violet-500/50" placeholder="Enter your transaction ID">
                                @error('transaction_number')
                                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Sending Number -->
                            <div>
                                <label for="sending_number" class="block text-sm font-medium text-gray-300 mb-2">Sending Number *</label>
                                <input type="text" name="sending_number" id="sending_number" value="{{ old('sending_number') }}" required style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;" class="w-full px-4 py-3 bg-black/50 border border-violet-500/30 rounded-lg text-white focus:border-violet-500 focus:ring-violet-500/50" placeholder="Enter the number you sent from">
                                @error('sending_number')
                                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="p-3 bg-yellow-500/10 border border-yellow-500/30 rounded-lg">
                                <p class="text-yellow-300 text-sm">⚠️ We are working to get an automatic bKash payment system soon. For now, please send payment manually and enter the details above.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Options -->
                    <div class="bg-black/50 backdrop-blur-xl rounded-2xl border border-violet-500/30 p-6">
                        <div class="space-y-4">
                            @auth
                            <label class="flex items-start space-x-3 cursor-pointer">
                                <input type="checkbox" name="save_info" value="1" class="mt-1 rounded border-violet-500/50 bg-black/50 text-violet-600">
                                <span class="text-gray-300">Save this information for future orders</span>
                            </label>
                            @endauth

                            <label class="flex items-start space-x-3 cursor-pointer">
                                <input type="checkbox" name="terms_accepted" value="1" required class="mt-1 rounded border-violet-500/50 bg-black/50 text-violet-600">
                                <span class="text-gray-300">I accept that the order will take <span class="text-violet-400 font-semibold">10-15 days</span> to arrive *</span>
                            </label>
                            @error('terms_accepted')
                                <p class="text-sm text-red-400">{{ $message }}</p>
                            @enderror

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
                                <div class="flex justify-between text-violet-300">
                                    <span>Discount (100 taka + 10{!! '%' !!})</span>
                                    <span id="regular_discount_display">-৳{{ number_format($totalDiscount, 2) }}</span>
                                </div>
                                <div id="coupon_discount_row" class="flex justify-between text-green-400" style="display: none;">
                                    <span>Coupon Discount</span>
                                    <span id="coupon_discount_display">-৳0.00</span>
                                </div>
                                <div class="border-t border-violet-500/20 pt-2">
                                    <div class="flex justify-between text-xl font-bold">
                                        <span>Total</span>
                                        <span class="text-violet-400" id="total_display">৳{{ number_format($finalTotal, 2) }}</span>
                                    </div>
                                </div>
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
    
    <!-- Pre-Order Popup -->
    @include('components.pre-order-popup')
    
    @include('home.styles')

    <script>
        let appliedCoupon = null;
        const baseSubtotal = {{ $cartSubTotal }};
        const baseDiscount = {{ $totalDiscount }};
        const baseTotal = {{ $finalTotal }};

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
            const newTotal = Math.max(0, baseTotal - couponDiscount);
            
            document.getElementById('coupon_discount_row').style.display = 'flex';
            document.getElementById('coupon_discount_display').textContent = '-৳' + couponDiscount.toFixed(2);
            document.getElementById('total_display').textContent = '৳' + newTotal.toFixed(2);
        }

        function resetCoupon() {
            appliedCoupon = null;
            document.getElementById('coupon_discount_row').style.display = 'none';
            document.getElementById('total_display').textContent = '৳' + baseTotal.toFixed(2);
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

