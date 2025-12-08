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
                            <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Category *</label>
                            <select name="category" id="category" required class="w-full border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select Category</option>
                                @foreach($categories as $key => $name)
                                    <option value="{{ $key }}" {{ old('category') === $key ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            @error('category')
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
                            <label for="price" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Price (৳) *</label>
                            <input type="number" name="price" id="price" value="{{ old('price', 0) }}" step="0.01" min="0" required class="w-full border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('price')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
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
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Upload one or more images. Max size: 2MB each. Formats: JPEG, PNG, JPG, GIF</p>
                        </div>

                        <!-- Rating & Reviews -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="rating" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Rating (0-5)</label>
                                <input type="number" name="rating" id="rating" value="{{ old('rating', 0) }}" min="0" max="5" class="w-full border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label for="reviews" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Reviews Count</label>
                                <input type="number" name="reviews" id="reviews" value="{{ old('reviews', 0) }}" min="0" class="w-full border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                        <!-- Active Status -->
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-700 text-blue-600 shadow-sm focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Product is active</span>
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

