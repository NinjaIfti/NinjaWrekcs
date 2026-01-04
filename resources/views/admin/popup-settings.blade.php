<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Popup Settings') }}
        </h2>
    </x-slot>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Settings Form -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Edit Popup Content</h3>
                
                <form action="{{ route('admin.popup-settings.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="space-y-4">
                        <!-- Title -->
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Popup Title <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="title" 
                                   id="title" 
                                   value="{{ old('title', $settings->title) }}"
                                   class="w-full border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100"
                                   required>
                            @error('title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Main Heading -->
                        <div>
                            <label for="main_heading" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Main Heading
                            </label>
                            <input type="text" 
                                   name="main_heading" 
                                   id="main_heading" 
                                   value="{{ old('main_heading', $settings->main_heading) }}"
                                   placeholder="🎮 VALORANT PRE-ORDER 🎮"
                                   class="w-full border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100">
                            @error('main_heading')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Subheading -->
                        <div>
                            <label for="subheading" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Subheading
                            </label>
                            <input type="text" 
                                   name="subheading" 
                                   id="subheading" 
                                   value="{{ old('subheading', $settings->subheading) }}"
                                   placeholder="Exclusive Collectibles Now Available!"
                                   class="w-full border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100">
                            @error('subheading')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Description
                            </label>
                            <textarea name="description" 
                                      id="description" 
                                      rows="3"
                                      placeholder="Pre-order your favorite Valorant collectibles..."
                                      class="w-full border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100">{{ old('description', $settings->description) }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Discount Section -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="discount_text" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Discount Text
                                </label>
                                <input type="text" 
                                       name="discount_text" 
                                       id="discount_text" 
                                       value="{{ old('discount_text', $settings->discount_text) }}"
                                       placeholder="Get"
                                       class="w-full border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100">
                            </div>

                            <div>
                                <label for="discount_amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Discount Amount
                                </label>
                                <input type="text" 
                                       name="discount_amount" 
                                       id="discount_amount" 
                                       value="{{ old('discount_amount', $settings->discount_amount) }}"
                                       placeholder="100 taka off"
                                       class="w-full border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100">
                            </div>
                        </div>

                        <!-- Badge Text -->
                        <div>
                            <label for="badge_text" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Badge Text
                            </label>
                            <input type="text" 
                                   name="badge_text" 
                                   id="badge_text" 
                                   value="{{ old('badge_text', $settings->badge_text) }}"
                                   placeholder="LIMITED TIME OFFER"
                                   class="w-full border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100">
                            @error('badge_text')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Button Section -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="button_text" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Button Text <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       name="button_text" 
                                       id="button_text" 
                                       value="{{ old('button_text', $settings->button_text) }}"
                                       class="w-full border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100"
                                       required>
                            </div>

                            <div>
                                <label for="button_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Button URL <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       name="button_url" 
                                       id="button_url" 
                                       value="{{ old('button_url', $settings->button_url) }}"
                                       class="w-full border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100"
                                       required>
                            </div>
                        </div>

                        <!-- Display Delay -->
                        <div>
                            <label for="display_delay" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Display Delay (milliseconds) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" 
                                   name="display_delay" 
                                   id="display_delay" 
                                   value="{{ old('display_delay', $settings->display_delay) }}"
                                   min="0"
                                   max="30000"
                                   class="w-full border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100"
                                   required>
                            <p class="mt-1 text-xs text-gray-500">How long to wait before showing popup (3000 = 3 seconds)</p>
                            @error('display_delay')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Active Toggle -->
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="is_active" 
                                       value="1"
                                       {{ old('is_active', $settings->is_active) ? 'checked' : '' }}
                                       class="rounded border-gray-300 dark:border-gray-700 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Show popup on website</span>
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-4">
                            <button type="submit" class="w-full px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold">
                                Update Popup Settings
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Preview -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Preview</h3>
                
                <div class="bg-gradient-to-br from-violet-900 via-purple-900 to-black rounded-2xl overflow-hidden relative border-4 border-violet-500/30 shadow-2xl">
                    <!-- Close Button -->
                    <div class="absolute top-4 right-4 z-10">
                        <div class="w-8 h-8 rounded-full bg-white/10 backdrop-blur-sm flex items-center justify-center">
                            <span class="text-white text-lg">×</span>
                        </div>
                    </div>

                    <div class="p-8">
                        <!-- Badge -->
                        @if($settings->badge_text)
                        <div class="mb-4">
                            <span class="inline-block px-4 py-1 bg-violet-500/20 border border-violet-500/50 rounded-full text-violet-300 text-xs font-bold">
                                {{ $settings->badge_text }}
                            </span>
                        </div>
                        @endif

                        <!-- Main Heading -->
                        @if($settings->main_heading)
                        <h2 class="text-2xl md:text-3xl font-bold text-white mb-2">
                            {{ $settings->main_heading }}
                        </h2>
                        @endif

                        <!-- Subheading -->
                        @if($settings->subheading)
                        <p class="text-violet-300 text-lg mb-4">
                            {{ $settings->subheading }}
                        </p>
                        @endif

                        <!-- Description -->
                        @if($settings->description)
                        <p class="text-gray-300 mb-6">
                            {{ $settings->description }}
                        </p>
                        @endif

                        <!-- Discount Box -->
                        @if($settings->discount_text || $settings->discount_amount)
                        <div class="bg-violet-500/20 border-2 border-violet-500/50 rounded-xl p-4 mb-6">
                            <p class="text-white font-semibold text-base">
                                @if($settings->discount_text){{ $settings->discount_text }} @endif
                                <span class="text-white">{{ $settings->discount_amount }}</span>
                            </p>
                        </div>
                        @endif

                        <!-- Button -->
                        <button class="w-full px-6 py-3 bg-gradient-to-r from-violet-600 to-purple-600 text-white rounded-lg font-semibold">
                            {{ $settings->button_text }}
                        </button>
                    </div>
                </div>

                <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <p class="text-sm text-blue-800 dark:text-blue-300">
                        <strong>Status:</strong> {{ $settings->is_active ? '✅ Active' : '❌ Inactive' }}
                    </p>
                    <p class="text-sm text-blue-800 dark:text-blue-300 mt-1">
                        <strong>Delay:</strong> {{ $settings->display_delay }}ms ({{ $settings->display_delay / 1000 }} seconds)
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>









