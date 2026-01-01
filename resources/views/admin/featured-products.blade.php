<x-admin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Featured Products') }}
            </h2>
        </div>
    </x-slot>

    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            @if(session('success'))
                <div class="mb-6 p-4 bg-green-500/20 border border-green-500/50 rounded-lg text-green-400">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('admin.featured-products.update') }}" method="POST">
                @csrf
                
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Select Products to Feature</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Choose products that will appear in the Featured Products section on the homepage.</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-h-96 overflow-y-auto p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                        @forelse($allProducts as $product)
                            <label class="flex items-start space-x-3 p-3 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer transition">
                                <input type="checkbox" 
                                       name="featured_product_ids[]" 
                                       value="{{ $product->id }}"
                                       {{ $product->is_featured ? 'checked' : '' }}
                                       class="mt-1 rounded border-gray-300 dark:border-gray-700 text-blue-600 focus:ring-blue-500">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center space-x-2">
                                        @if($product->image)
                                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-12 h-12 object-cover rounded">
                                        @endif
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $product->name }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">৳{{ number_format($product->price ?? 0, 2) }}</p>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        @empty
                            <div class="col-span-full text-center py-8 text-gray-500 dark:text-gray-400">
                                No products available.
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="flex justify-end gap-4">
                    <a href="{{ route('admin.products') }}" class="px-6 py-2 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        Update Featured Products
                    </button>
                </div>
            </form>

            @if($featuredProducts->count() > 0)
                <div class="mt-8">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Currently Featured Products</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        @foreach($featuredProducts as $product)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-32 object-cover rounded mb-2">
                                @endif
                                <h4 class="font-semibold text-gray-900 dark:text-white text-sm">{{ $product->name }}</h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">৳{{ number_format($product->price ?? 0, 2) }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>














