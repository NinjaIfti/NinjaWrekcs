<x-admin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Create Special Offer') }}
            </h2>
            <a href="{{ route('admin.special-offers') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                ← Back to Offers
            </a>
        </div>
    </x-slot>

    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <form action="{{ route('admin.special-offers.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <!-- Badge Text -->
                <div>
                    <label for="badge_text" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Badge Text</label>
                    <input type="text" name="badge_text" id="badge_text" value="{{ old('badge_text', 'Limited Time Offer') }}" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">
                    @error('badge_text')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Main Title -->
                <div>
                    <label for="main_title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Main Title *</label>
                    <input type="text" name="main_title" id="main_title" value="{{ old('main_title') }}" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">
                    @error('main_title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Subtitle -->
                <div>
                    <label for="subtitle" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Subtitle (Optional)</label>
                    <input type="text" name="subtitle" id="subtitle" value="{{ old('subtitle') }}" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">
                    @error('subtitle')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description *</label>
                    <textarea name="description" id="description" rows="4" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Image Upload -->
                <div>
                    <label for="image" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Offer Image</label>
                    <input type="file" name="image" id="image" accept="image/*" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Upload an image for the special offer (Max: 2MB, Formats: JPG, PNG, GIF, WebP)</p>
                    @error('image')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Features -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Features (Optional)</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="feature_1" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Feature 1</label>
                            <input type="text" name="feature_1" id="feature_1" value="{{ old('feature_1') }}" placeholder="e.g., No code required" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="feature_2" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Feature 2</label>
                            <input type="text" name="feature_2" id="feature_2" value="{{ old('feature_2') }}" placeholder="e.g., Valid till pre order ends" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>

                <!-- Is Active -->
                <div class="flex items-center">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="is_active" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Active (Show on homepage)</label>
                </div>

                <!-- Submit Button -->
                <div class="flex gap-4 pt-4">
                    <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold">
                        Create Special Offer
                    </button>
                    <a href="{{ route('admin.special-offers') }}" class="px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition font-semibold">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>





