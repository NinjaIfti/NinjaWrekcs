<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Coupon') }}
        </h2>
    </x-slot>

    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <form action="{{ route('admin.coupons.update', $coupon) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Coupon Code -->
                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Coupon Code <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="code" 
                               id="code" 
                               value="{{ old('code', $coupon->code) }}"
                               placeholder="e.g., SUMMER2024"
                               class="w-full border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 uppercase"
                               required>
                        @error('code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Discount Type -->
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Discount Type <span class="text-red-500">*</span>
                        </label>
                        <select name="type" 
                                id="type" 
                                class="w-full border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100"
                                required
                                onchange="toggleDiscountFields()">
                            <option value="percentage" {{ old('type', $coupon->type) === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                            <option value="fixed" {{ old('type', $coupon->type) === 'fixed' ? 'selected' : '' }}>Fixed Amount (৳)</option>
                        </select>
                        @error('type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Discount Value -->
                    <div>
                        <label for="value" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Discount Value <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="number" 
                                   name="value" 
                                   id="value" 
                                   value="{{ old('value', $coupon->value) }}"
                                   step="0.01"
                                   min="0"
                                   class="w-full border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100"
                                   required>
                            <span id="value-symbol" class="absolute right-3 top-2 text-gray-500">%</span>
                        </div>
                        @error('value')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Maximum Discount -->
                    <div id="max-discount-field">
                        <label for="maximum_discount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Maximum Discount (৳)
                        </label>
                        <input type="number" 
                               name="maximum_discount" 
                               id="maximum_discount" 
                               value="{{ old('maximum_discount', $coupon->maximum_discount) }}"
                               step="0.01"
                               min="0"
                               class="w-full border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100">
                        @error('maximum_discount')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Minimum Order -->
                    <div>
                        <label for="minimum_order" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Minimum Order Amount (৳)
                        </label>
                        <input type="number" 
                               name="minimum_order" 
                               id="minimum_order" 
                               value="{{ old('minimum_order', $coupon->minimum_order) }}"
                               step="0.01"
                               min="0"
                               class="w-full border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100">
                        @error('minimum_order')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Usage Limit -->
                    <div>
                        <label for="usage_limit" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Usage Limit
                        </label>
                        <input type="number" 
                               name="usage_limit" 
                               id="usage_limit" 
                               value="{{ old('usage_limit', $coupon->usage_limit) }}"
                               min="1"
                               class="w-full border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100">
                        @error('usage_limit')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Currently used: {{ $coupon->used_count }} times</p>
                    </div>

                    <!-- Valid From -->
                    <div>
                        <label for="valid_from" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Valid From
                        </label>
                        <input type="date" 
                               name="valid_from" 
                               id="valid_from" 
                               value="{{ old('valid_from', $coupon->valid_from?->format('Y-m-d')) }}"
                               class="w-full border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100">
                        @error('valid_from')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Valid Until -->
                    <div>
                        <label for="valid_until" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Valid Until
                        </label>
                        <input type="date" 
                               name="valid_until" 
                               id="valid_until" 
                               value="{{ old('valid_until', $coupon->valid_until?->format('Y-m-d')) }}"
                               class="w-full border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100">
                        @error('valid_until')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Description -->
                <div class="mt-6">
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Description
                    </label>
                    <textarea name="description" 
                              id="description" 
                              rows="3"
                              class="w-full border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100">{{ old('description', $coupon->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Is Active -->
                <div class="mt-6">
                    <label class="flex items-center">
                        <input type="checkbox" 
                               name="is_active" 
                               value="1"
                               {{ old('is_active', $coupon->is_active) ? 'checked' : '' }}
                               class="rounded border-gray-300 dark:border-gray-700 text-blue-600 focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Active (users can apply this coupon)</span>
                    </label>
                </div>

                <!-- Submit Buttons -->
                <div class="mt-6 flex justify-end gap-4">
                    <a href="{{ route('admin.coupons') }}" class="px-6 py-2 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        Update Coupon
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleDiscountFields() {
            const type = document.getElementById('type').value;
            const valueSymbol = document.getElementById('value-symbol');
            const maxDiscountField = document.getElementById('max-discount-field');
            
            if (type === 'percentage') {
                valueSymbol.textContent = '%';
                maxDiscountField.style.display = 'block';
            } else {
                valueSymbol.textContent = '৳';
                maxDiscountField.style.display = 'none';
            }
        }

        // Initialize on page load
        toggleDiscountFields();
    </script>
</x-admin-layout>

















