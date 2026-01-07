<x-admin-layout>
    <div class="max-w-3xl">
        <div class="mb-6">
            <a href="{{ route('admin.reviews') }}" class="text-blue-600 dark:text-blue-400 hover:underline flex items-center mb-4">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Reviews
            </a>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Add New Review</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Upload a customer review image for the homepage slideshow</p>
        </div>

        <form action="{{ route('admin.reviews.store') }}" method="POST" enctype="multipart/form-data" class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
            @csrf

            <!-- Customer Name -->
            <div class="mb-6">
                <label for="customer_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Customer Name *
                </label>
                <input type="text" 
                       name="customer_name" 
                       id="customer_name" 
                       value="{{ old('customer_name') }}"
                       required
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                       placeholder="e.g., John Doe">
                @error('customer_name')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Review Text -->
            <div class="mb-6">
                <label for="review_text" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Review Text (Optional)
                </label>
                <textarea name="review_text" 
                          id="review_text" 
                          rows="4"
                          class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                          placeholder="Customer's review text...">{{ old('review_text') }}</textarea>
                @error('review_text')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Rating -->
            <div class="mb-6">
                <label for="rating" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Rating *
                </label>
                <select name="rating" 
                        id="rating" 
                        required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="5" {{ old('rating') == 5 ? 'selected' : '' }}>⭐⭐⭐⭐⭐ (5 Stars)</option>
                    <option value="4" {{ old('rating') == 4 ? 'selected' : '' }}>⭐⭐⭐⭐ (4 Stars)</option>
                    <option value="3" {{ old('rating') == 3 ? 'selected' : '' }}>⭐⭐⭐ (3 Stars)</option>
                    <option value="2" {{ old('rating') == 2 ? 'selected' : '' }}>⭐⭐ (2 Stars)</option>
                    <option value="1" {{ old('rating') == 1 ? 'selected' : '' }}>⭐ (1 Star)</option>
                </select>
                @error('rating')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Image Upload -->
            <div class="mb-6">
                <label for="image" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Review Image * (Max 10MB)
                </label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 dark:border-gray-600 border-dashed rounded-lg hover:border-blue-400 transition">
                    <div class="space-y-1 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div class="flex text-sm text-gray-600 dark:text-gray-400">
                            <label for="image" class="relative cursor-pointer bg-white dark:bg-gray-800 rounded-md font-medium text-blue-600 hover:text-blue-500">
                                <span>Upload an image</span>
                                <input id="image" 
                                       name="image" 
                                       type="file" 
                                       accept="image/jpeg,image/png,image/jpg,image/webp"
                                       required
                                       class="sr-only"
                                       onchange="previewImage(this)">
                            </label>
                            <p class="pl-1">or drag and drop</p>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">JPEG, PNG, JPG, WEBP up to 10MB</p>
                    </div>
                </div>
                <div id="image-preview" class="mt-4 hidden">
                    <img src="" alt="Preview" class="max-w-full h-64 object-contain rounded-lg mx-auto">
                </div>
                @error('image')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Order -->
            <div class="mb-6">
                <label for="order" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Display Order *
                </label>
                <input type="number" 
                       name="order" 
                       id="order" 
                       value="{{ old('order', $nextOrder) }}"
                       min="0"
                       required
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Lower numbers appear first in slideshow (0, 1, 2...)</p>
                @error('order')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Active Status -->
            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" 
                           name="is_active" 
                           value="1"
                           checked
                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Active (show in slideshow)</span>
                </label>
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-4">
                <a href="{{ route('admin.reviews') }}" class="px-6 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    Add Review
                </button>
            </div>
        </form>
    </div>

    <script>
        function previewImage(input) {
            const preview = document.getElementById('image-preview');
            const img = preview.querySelector('img');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    img.src = e.target.result;
                    preview.classList.remove('hidden');
                }
                
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.classList.add('hidden');
            }
        }
    </script>
</x-admin-layout>



