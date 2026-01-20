<x-admin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Add New Product') }}
            </h2>
            <a href="{{ route('admin.products') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                Back to Products
            </a>
        </div>
    </x-slot>

    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Left Column -->
                    <div class="space-y-6">
                        <!-- Product Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Product Name *</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required class="w-full border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Category -->
                        <div>
                            <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Category *</label>
                            <select name="category_id" id="category_id" required class="w-full border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select Category</option>
                                @foreach($categories as $parentCategory)
                                    <optgroup label="{{ $parentCategory->name }}">
                                        @if($parentCategory->hasChildren())
                                            @foreach($parentCategory->children as $childCategory)
                                                <option value="{{ $childCategory->id }}" {{ old('category_id') == $childCategory->id ? 'selected' : '' }}>
                                                    {{ $childCategory->name }}
                                                </option>
                                            @endforeach
                                        @else
                                            <option value="{{ $parentCategory->id }}" {{ old('category_id') == $parentCategory->id ? 'selected' : '' }}>
                                                {{ $parentCategory->name }}
                                            </option>
                                        @endif
                                    </optgroup>
                                @endforeach
                            </select>
                            @error('category_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Quantity -->
                        <div>
                            <label for="quantity" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Quantity *</label>
                            <input type="number" name="quantity" id="quantity" value="{{ old('quantity', 0) }}" min="0" required class="w-full border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('quantity')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Price -->
                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Regular Price (৳) *</label>
                            <input type="number" name="price" id="price" value="{{ old('price', 0) }}" step="0.01" min="0" required class="w-full border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('price')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Limited Time Offer Section -->
                        <div class="border-2 border-dashed border-orange-300 dark:border-orange-700 rounded-lg p-4 bg-orange-50 dark:bg-orange-900/20">
                            <h3 class="text-sm font-semibold text-orange-700 dark:text-orange-300 mb-3 flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Limited Time Offer (Optional)
                            </h3>
                            
                            <!-- Offer Price -->
                            <div class="mb-4">
                                <label for="offer_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Offer Price (৳)</label>
                                <input type="number" name="offer_price" id="offer_price" value="{{ old('offer_price') }}" step="0.01" min="0" class="w-full border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                @error('offer_price')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Set a special price for limited time</p>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <!-- Offer Start Date -->
                                <div>
                                    <label for="offer_starts_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Start Date & Time</label>
                                    <input type="datetime-local" name="offer_starts_at" id="offer_starts_at" value="{{ old('offer_starts_at') }}" class="w-full border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                    @error('offer_starts_at')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Offer End Date -->
                                <div>
                                    <label for="offer_ends_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">End Date & Time</label>
                                    <input type="datetime-local" name="offer_ends_at" id="offer_ends_at" value="{{ old('offer_ends_at') }}" class="w-full border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                    @error('offer_ends_at')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                ⏰ The offer will automatically activate and expire based on these dates
                            </p>
                        </div>

                        <!-- Pre-order/Upcoming Section -->
                        <div class="border-2 border-dashed border-purple-300 dark:border-purple-700 rounded-lg p-4 bg-purple-50 dark:bg-purple-900/20">
                            <h3 class="text-sm font-semibold text-purple-700 dark:text-purple-300 mb-3 flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Pre-order/Upcoming Product (Optional)
                            </h3>
                            
                            <div class="space-y-3">
                                <!-- Pre-order Checkbox -->
                                <div>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="is_preorder" value="1" {{ old('is_preorder') ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-700 text-purple-600 shadow-sm focus:ring-purple-500">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">This is a Pre-order (accepting orders now)</span>
                                    </label>
                                </div>

                                <!-- Upcoming Checkbox -->
                                <div>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="is_upcoming" value="1" {{ old('is_upcoming') ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-700 text-purple-600 shadow-sm focus:ring-purple-500">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">This is Upcoming (not yet available)</span>
                                    </label>
                                </div>

                                <!-- Price TBA Checkbox -->
                                <div>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="price_tba" value="1" {{ old('price_tba') ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-700 text-purple-600 shadow-sm focus:ring-purple-500">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Price to be announced (show "Price will be announced later")</span>
                                    </label>
                                </div>

                                <!-- Bookable Checkbox -->
                                <div>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="is_bookable" value="1" {{ old('is_bookable') ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-700 text-purple-600 shadow-sm focus:ring-purple-500">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Allow booking for this product (customer pays ৳200 booking fee)</span>
                                    </label>
                                </div>
                            </div>
                            
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                💡 Use for products that are coming soon or available for pre-order. If bookable is enabled, customers will pay ৳200 as booking fee and the remaining amount later.
                            </p>
                        </div>

                        <!-- Images Upload -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Product Images</label>
                            <div id="images-container" class="space-y-3">
                                <input type="file" name="images[]" accept="image/*" class="w-full border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <button type="button" id="add-image-input" class="mt-3 inline-flex items-center px-3 py-2 text-sm font-semibold border border-dashed border-violet-500/60 text-violet-500 rounded-lg hover:bg-violet-500/10 transition">
                                <span class="text-lg leading-none mr-2">+</span> Add another image
                            </button>
                            @error('images.*')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Upload one or more images. Max size: 10MB each. Formats: JPEG, PNG, JPG, GIF</p>
                        </div>

                        <!-- Rating & Reviews -->

                        <!-- Active Status -->
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-700 text-blue-600 shadow-sm focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Product is active</span>
                            </label>
                        </div>

                        <!-- Featured Status -->
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', false) ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-700 text-blue-600 shadow-sm focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Feature on homepage</span>
                            </label>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-6">
                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description</label>
                            <textarea name="description" id="description" rows="6" class="w-full border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Notes -->
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Notes</label>
                            <textarea name="notes" id="notes" rows="6" class="w-full border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="mt-6 flex justify-end gap-4">
                    <a href="{{ route('admin.products') }}" class="px-6 py-2 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        Create Product
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const container = document.getElementById('images-container');
            const addBtn = document.getElementById('add-image-input');
            if (container && addBtn) {
                addBtn.addEventListener('click', () => {
                    const input = document.createElement('input');
                    input.type = 'file';
                    input.name = 'images[]';
                    input.accept = 'image/*';
                    input.className = 'w-full border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500';
                    container.appendChild(input);
                });
            }
        });
    </script>
</x-admin-layout>

