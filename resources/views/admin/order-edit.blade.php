<x-admin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Edit Order #') }}{{ $order->id }}
            </h2>
            <a href="{{ route('admin.orders') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                Back to Orders
            </a>
        </div>
    </x-slot>

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    <!-- Change History (if exists) -->
    @if($order->changes->isNotEmpty())
        <div class="mb-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
            <h3 class="font-semibold text-blue-900 dark:text-blue-200 mb-3 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Edit History
            </h3>
            <div class="space-y-2 max-h-60 overflow-y-auto">
                @foreach($order->changes->sortByDesc('created_at') as $change)
                    <div class="flex items-start gap-3 text-sm bg-white dark:bg-gray-800 p-3 rounded border border-gray-200 dark:border-gray-700">
                        <div class="flex-shrink-0">
                            @if($change->change_type === 'item_added')
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400">+</span>
                            @elseif($change->change_type === 'item_removed')
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400">−</span>
                            @else
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400">↻</span>
                            @endif
                        </div>
                        <div class="flex-1">
                            <p class="text-gray-900 dark:text-white font-medium">{{ $change->description }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                By {{ $change->user->name ?? 'System' }} • {{ $change->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <form action="{{ route('admin.orders.update', $order) }}" method="POST" id="orderForm">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Left Column - Customer Info -->
                    <div class="space-y-6">
                        <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Customer Information</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                @if($order->user_id)
                                    User Order - Account ID: {{ $order->user_id }}
                                @else
                                    Guest Order
                                @endif
                            </p>
                        </div>

                        <!-- Customer Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Customer Name *</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $order->name) }}" required 
                                class="w-full border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Phone -->
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Phone Number *</label>
                            <input type="tel" name="phone" id="phone" value="{{ old('phone', $order->phone) }}" required 
                                class="w-full border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('phone')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email (Optional)</label>
                            <input type="email" name="email" id="email" value="{{ old('email', $order->email) }}" 
                                class="w-full border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Address -->
                        <div>
                            <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Delivery Address *</label>
                            <textarea name="address" id="address" rows="3" required 
                                class="w-full border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('address', $order->address) }}</textarea>
                            @error('address')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Payment Information -->
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4 mt-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Payment Information</h3>
                            
                            <!-- Payment Method -->
                            <div class="mb-4">
                                <label for="payment_method" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Payment Method *</label>
                                <select name="payment_method" id="payment_method" required 
                                    class="w-full border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select Payment Method</option>
                                    <option value="bkash" {{ old('payment_method', $order->payment_method) === 'bkash' ? 'selected' : '' }}>bKash</option>
                                    <option value="nagad" {{ old('payment_method', $order->payment_method) === 'nagad' ? 'selected' : '' }}>Nagad</option>
                                    <option value="rocket" {{ old('payment_method', $order->payment_method) === 'rocket' ? 'selected' : '' }}>Rocket</option>
                                    <option value="cod" {{ old('payment_method', $order->payment_method) === 'cod' ? 'selected' : '' }}>Cash on Delivery</option>
                                </select>
                                @error('payment_method')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Transaction Number -->
                            <div class="mb-4">
                                <label for="transaction_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Transaction Number</label>
                                <input type="text" name="transaction_number" id="transaction_number" value="{{ old('transaction_number', $order->transaction_number) }}" 
                                    class="w-full border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('transaction_number')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Sending Number -->
                            <div>
                                <label for="sending_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Sending Number</label>
                                <input type="tel" name="sending_number" id="sending_number" value="{{ old('sending_number', $order->sending_number) }}" 
                                    class="w-full border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('sending_number')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Products & Order Details -->
                    <div class="space-y-6">
                        <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Order Items</h3>
                        </div>

                        <!-- Products Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Products *</label>
                            <div id="product-items" class="space-y-3">
                                <!-- Product items will be added here -->
                            </div>
                            <button type="button" id="add-product" 
                                class="mt-3 inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                Add Product
                            </button>
                            @error('products')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Order Summary -->
                        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                            <h4 class="font-semibold text-gray-900 dark:text-white mb-3">Order Summary</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Subtotal:</span>
                                    <span class="font-semibold text-gray-900 dark:text-white">৳<span id="subtotal-amount">0.00</span></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Discount:</span>
                                    <span class="font-semibold text-green-600 dark:text-green-400">৳<span id="discount-amount">0.00</span></span>
                                </div>
                                <div class="border-t border-gray-200 dark:border-gray-700 pt-2 mt-2">
                                    <div class="flex justify-between text-lg">
                                        <span class="font-bold text-gray-900 dark:text-white">Total:</span>
                                        <span class="font-bold text-blue-600 dark:text-blue-400">৳<span id="total-amount">0.00</span></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Coupon Code -->
                        <div>
                            <label for="coupon_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Coupon Code (Optional)</label>
                            <div class="flex gap-2">
                                <input type="text" name="coupon_code" id="coupon_code" value="{{ old('coupon_code', $order->coupon_code) }}" 
                                    class="flex-1 border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <button type="button" id="apply-coupon" 
                                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                                    Apply
                                </button>
                            </div>
                            <div id="coupon-message" class="mt-2 text-sm"></div>
                            @error('coupon_code')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Order Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Order Status *</label>
                            <select name="status" id="status" required 
                                class="w-full border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="pending" {{ old('status', $order->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="confirmed" {{ old('status', $order->status) === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                <option value="processing" {{ old('status', $order->status) === 'processing' ? 'selected' : '' }}>Processing</option>
                                <option value="shipped" {{ old('status', $order->status) === 'shipped' ? 'selected' : '' }}>Shipped</option>
                                <option value="delivered" {{ old('status', $order->status) === 'delivered' ? 'selected' : '' }}>Delivered</option>
                                <option value="cancelled" {{ old('status', $order->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Notes -->
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Order Notes (Optional)</label>
                            <textarea name="notes" id="notes" rows="3" 
                                class="w-full border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('notes', $order->notes) }}</textarea>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="mt-6 flex justify-end gap-4">
                    <a href="{{ route('admin.orders') }}" class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        Update Order
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Product Selection Template -->
    <template id="product-item-template">
        <div class="product-item flex gap-3 items-start bg-gray-50 dark:bg-gray-900 p-3 rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="flex-1">
                <select name="products[INDEX][id]" class="product-select w-full border border-gray-300 dark:border-gray-700 rounded-lg px-3 py-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm mb-2" required>
                    <option value="">Select Product</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" data-price="{{ $product->price }}" data-stock="{{ $product->quantity }}">
                            {{ $product->name }} - ৳{{ number_format($product->price, 2) }} (Stock: {{ $product->quantity }})
                        </option>
                    @endforeach
                </select>
                <div class="flex items-center gap-2">
                    <label class="text-xs text-gray-600 dark:text-gray-400">Qty:</label>
                    <input type="number" name="products[INDEX][quantity]" class="product-quantity w-20 border border-gray-300 dark:border-gray-700 rounded px-2 py-1 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm" min="1" value="1" required>
                    <span class="text-xs text-gray-500 dark:text-gray-400 ml-2">Price: ৳<span class="item-price">0.00</span></span>
                </div>
            </div>
            <button type="button" class="remove-product text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 mt-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </template>

    @push('scripts')
    <script>
        let productIndex = 0;
        const productsData = @json($products);
        const couponsData = @json($coupons);
        const orderItems = @json($order->items);
        let appliedCoupon = null;

        // Add product row
        document.getElementById('add-product').addEventListener('click', function() {
            addProductRow();
        });

        function addProductRow(productId = null, quantity = 1) {
            const template = document.getElementById('product-item-template');
            const clone = template.content.cloneNode(true);
            
            const html = clone.querySelector('.product-item').outerHTML.replace(/INDEX/g, productIndex);
            document.getElementById('product-items').insertAdjacentHTML('beforeend', html);
            
            // Set selected product if provided
            if (productId) {
                const lastItem = document.getElementById('product-items').lastElementChild;
                const select = lastItem.querySelector('.product-select');
                const quantityInput = lastItem.querySelector('.product-quantity');
                
                select.value = productId;
                quantityInput.value = quantity;
                
                // Update price display
                const option = select.options[select.selectedIndex];
                const price = parseFloat(option.getAttribute('data-price')) || 0;
                const itemTotal = price * quantity;
                lastItem.querySelector('.item-price').textContent = itemTotal.toFixed(2);
            }
            
            productIndex++;
            updateOrderSummary();
        }

        // Remove product row
        document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-product')) {
                e.target.closest('.product-item').remove();
                updateOrderSummary();
            }
        });

        // Update price on product/quantity change
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('product-select') || e.target.classList.contains('product-quantity')) {
                const item = e.target.closest('.product-item');
                const select = item.querySelector('.product-select');
                const quantity = parseInt(item.querySelector('.product-quantity').value) || 0;
                const option = select.options[select.selectedIndex];
                const price = parseFloat(option.getAttribute('data-price')) || 0;
                const stock = parseInt(option.getAttribute('data-stock')) || 0;
                
                if (quantity > stock) {
                    alert(`Only ${stock} items available in stock!`);
                    item.querySelector('.product-quantity').value = stock;
                    return;
                }
                
                const itemTotal = price * quantity;
                item.querySelector('.item-price').textContent = itemTotal.toFixed(2);
                
                updateOrderSummary();
            }
        });

        function updateOrderSummary() {
            let subtotal = 0;
            
            document.querySelectorAll('.product-item').forEach(item => {
                const select = item.querySelector('.product-select');
                const quantity = parseInt(item.querySelector('.product-quantity').value) || 0;
                const option = select.options[select.selectedIndex];
                const price = parseFloat(option.getAttribute('data-price')) || 0;
                subtotal += price * quantity;
            });
            
            let discount = 0;
            if (appliedCoupon) {
                if (appliedCoupon.type === 'percentage') {
                    discount = subtotal * (appliedCoupon.value / 100);
                } else {
                    discount = appliedCoupon.value;
                }
                discount = Math.min(discount, subtotal);
            }
            
            const total = Math.max(0, subtotal - discount);
            
            document.getElementById('subtotal-amount').textContent = subtotal.toFixed(2);
            document.getElementById('discount-amount').textContent = discount.toFixed(2);
            document.getElementById('total-amount').textContent = total.toFixed(2);
        }

        // Apply coupon
        document.getElementById('apply-coupon').addEventListener('click', function() {
            const couponCode = document.getElementById('coupon_code').value.trim().toUpperCase();
            const messageDiv = document.getElementById('coupon-message');
            
            if (!couponCode) {
                messageDiv.innerHTML = '<span class="text-red-600">Please enter a coupon code</span>';
                return;
            }
            
            const coupon = couponsData.find(c => c.code.toUpperCase() === couponCode);
            
            if (!coupon) {
                appliedCoupon = null;
                messageDiv.innerHTML = '<span class="text-red-600">Invalid coupon code</span>';
                updateOrderSummary();
                return;
            }
            
            if (!coupon.is_active) {
                appliedCoupon = null;
                messageDiv.innerHTML = '<span class="text-red-600">This coupon is no longer active</span>';
                updateOrderSummary();
                return;
            }
            
            const subtotal = parseFloat(document.getElementById('subtotal-amount').textContent);
            
            if (coupon.minimum_order && subtotal < coupon.minimum_order) {
                appliedCoupon = null;
                messageDiv.innerHTML = `<span class="text-red-600">Minimum order of ৳${coupon.minimum_order} required</span>`;
                updateOrderSummary();
                return;
            }
            
            appliedCoupon = coupon;
            messageDiv.innerHTML = `<span class="text-green-600">✓ Coupon applied successfully!</span>`;
            updateOrderSummary();
        });

        // Load existing order items on page load
        document.addEventListener('DOMContentLoaded', function() {
            orderItems.forEach(item => {
                addProductRow(item.product_id, item.quantity);
            });
            
            // If no items loaded (deleted products), add at least one empty row
            if (orderItems.length === 0) {
                addProductRow();
            }
        });

        // Form validation
        document.getElementById('orderForm').addEventListener('submit', function(e) {
            const products = document.querySelectorAll('.product-item');
            if (products.length === 0) {
                e.preventDefault();
                alert('Please add at least one product to the order');
                return false;
            }
            
            let valid = true;
            products.forEach(item => {
                const select = item.querySelector('.product-select');
                if (!select.value) {
                    valid = false;
                }
            });
            
            if (!valid) {
                e.preventDefault();
                alert('Please select a product for all items');
                return false;
            }
        });
    </script>
    @endpush
</x-admin-layout>




