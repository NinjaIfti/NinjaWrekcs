<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Send Notifications') }}
        </h2>
    </x-slot>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Send Special Offer -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <span class="text-4xl mr-3">🎁</span>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Send Special Offer</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Notify all {{ $totalUsers }} users about a special offer</p>
                    </div>
                </div>

                <form action="{{ route('admin.send-special-offer') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Offer Title *
                        </label>
                        <input type="text" 
                               name="title" 
                               required 
                               placeholder="e.g., Flash Sale: 30% Off!"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
                        @error('title')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Message *
                        </label>
                        <textarea name="message" 
                                  rows="3" 
                                  required 
                                  placeholder="Limited time offer! Get 30% off all products today only."
                                  class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white"></textarea>
                        @error('message')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Link URL (optional)
                        </label>
                        <input type="url" 
                               name="url" 
                               placeholder="https://ninjawrekcs.com/shop"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
                    </div>

                    <button type="submit" 
                            onclick="return confirm('Send notification to all {{ $totalUsers }} users?')"
                            class="w-full px-4 py-3 bg-gradient-to-r from-yellow-600 to-orange-600 text-white rounded-lg font-semibold hover:from-yellow-700 hover:to-orange-700 transition">
                        🎁 Send to All Users ({{ $totalUsers }})
                    </button>
                </form>
            </div>
        </div>

        <!-- Send New Product Notification -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <span class="text-4xl mr-3">✨</span>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Announce New Product</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Let users know about a new product</p>
                    </div>
                </div>

                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @forelse($products as $product)
                        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
                            <div class="flex-1 mr-4">
                                <h4 class="font-semibold text-gray-900 dark:text-white">{{ $product->name }}</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">৳{{ number_format($product->price, 2) }}</p>
                            </div>
                            <form action="{{ route('admin.send-new-product', $product) }}" method="POST">
                                @csrf
                                <button type="submit" 
                                        onclick="return confirm('Send new product notification for: {{ $product->name }}?')"
                                        class="px-4 py-2 bg-purple-600 text-white rounded-lg text-sm font-semibold hover:bg-purple-700 transition">
                                    ✨ Notify
                                </button>
                            </form>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                            No active products found
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Tips -->
    <div class="mt-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
        <h4 class="font-semibold text-blue-900 dark:text-blue-300 mb-2">💡 Tips for Effective Notifications:</h4>
        <ul class="list-disc list-inside text-sm text-blue-800 dark:text-blue-400 space-y-1">
            <li>Keep titles short and attention-grabbing (under 50 characters)</li>
            <li>Make messages clear and actionable</li>
            <li>Include specific details (discount percentage, product name, etc.)</li>
            <li>Add urgency for limited-time offers ("Today only!", "Limited stock!")</li>
            <li>Don't over-notify - space out notifications to avoid spam</li>
        </ul>
    </div>
</x-admin-layout>





