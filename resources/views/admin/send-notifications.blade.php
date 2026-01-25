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
                               placeholder="https://NinjaWrecks.com/shop"
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

    <!-- Stock Notification Requests -->
    <div class="mt-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                    <span class="text-4xl mr-3">🔔</span>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Stock Notification Requests</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Customers waiting for out-of-stock products</p>
                    </div>
                </div>
                <span class="px-3 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full text-sm font-semibold">
                    {{ $stockNotifications->sum(fn($group) => $group->count()) }} waiting
                </span>
            </div>

            @if($stockNotifications->count() > 0)
                <div class="space-y-4">
                    @foreach($stockNotifications as $productId => $notifications)
                        @php
                            $product = $notifications->first()->product;
                        @endphp
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-gray-50 dark:bg-gray-900">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        <h4 class="font-semibold text-gray-900 dark:text-white">{{ $product->name }}</h4>
                                        @if($product->quantity == 0)
                                            <span class="px-2 py-1 bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 rounded text-xs font-semibold">Out of Stock</span>
                                        @else
                                            <span class="px-2 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded text-xs font-semibold">✓ In Stock</span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ $notifications->count() }} {{ Str::plural('person', $notifications->count()) }} waiting
                                    </p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <form action="{{ route('admin.send-stock-notification', $product) }}" method="POST" onsubmit="return confirm('Send stock notification to all {{ $notifications->count() }} users who requested it?');">
                                        @csrf
                                        <button type="submit" 
                                                class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-semibold hover:bg-green-700 transition">
                                            🔔 Send
                                        </button>
                                    </form>
                                    <a href="{{ route('admin.products.edit', $product) }}" 
                                       class="px-4 py-2 bg-violet-600 text-white rounded-lg text-sm font-semibold hover:bg-violet-700 transition">
                                        Edit Product
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Email List -->
                            <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                                <details class="group">
                                    <summary class="cursor-pointer text-sm font-semibold text-violet-600 dark:text-violet-400 hover:text-violet-700 dark:hover:text-violet-300 flex items-center gap-2">
                                        <svg class="w-4 h-4 transform group-open:rotate-90 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                        View Email Addresses ({{ $notifications->count() }})
                                    </summary>
                                    <div class="mt-3 space-y-2">
                                        @foreach($notifications as $notification)
                                            <div class="flex items-center justify-between p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                                <div class="flex items-center gap-3">
                                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                                                    </svg>
                                                    <span class="text-sm text-gray-900 dark:text-white font-mono">{{ $notification->email }}</span>
                                                </div>
                                                <div class="flex items-center gap-4">
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ $notification->created_at->diffForHumans() }}</span>
                                                    <button onclick="copyEmail('{{ $notification->email }}')" 
                                                            class="px-3 py-1 text-xs bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                                                        Copy
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </details>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <!-- Copy All Emails Button -->
                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button onclick="copyAllEmails()" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition">
                        📋 Copy All Email Addresses
                    </button>
                </div>
            @else
                <div class="text-center py-12">
                    <span class="text-6xl mb-4 block">📭</span>
                    <p class="text-gray-500 dark:text-gray-400">No stock notification requests yet</p>
                    <p class="text-sm text-gray-400 dark:text-gray-500 mt-2">Customers can request notifications when products are out of stock</p>
                </div>
            @endif
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

    <!-- JavaScript for Copy Functions -->
    <script>
        function copyEmail(email) {
            navigator.clipboard.writeText(email).then(() => {
                alert('Email copied: ' + email);
            });
        }

        function copyAllEmails() {
            const emails = [
                @foreach($stockNotifications as $notifications)
                    @foreach($notifications as $notification)
                        '{{ $notification->email }}',
                    @endforeach
                @endforeach
            ];
            const emailList = emails.join(', ');
            navigator.clipboard.writeText(emailList).then(() => {
                alert('Copied ' + emails.length + ' email addresses!');
            });
        }
    </script>
</x-admin-layout>
















