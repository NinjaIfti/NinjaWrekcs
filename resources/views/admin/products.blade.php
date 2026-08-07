<x-admin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Products') }}
            </h2>
            <a href="{{ route('admin.products.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                Add New Product
            </a>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <!-- Category Tabs -->
            <div class="mb-6 border-b border-gray-200 dark:border-gray-700">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <a href="{{ route('admin.products') }}" 
                       class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition {{ !$selectedCategoryId ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                        All Products
                        <span class="ml-2 text-xs bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded-full">
                            {{ $totalProductCount }} products
                        </span>
                    </a>
                    @foreach($mainCategories as $category)
                        <a href="{{ route('admin.products', ['category_id' => $category->id]) }}" 
                           class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition {{ $selectedCategoryId == $category->id ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                            {{ $category->name }}
                            <span class="ml-2 text-xs bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded-full">
                                {{ $category->product_count ?? 0 }} products
                            </span>
                        </a>
                    @endforeach
                </nav>
            </div>
            
            @if($selectedCategoryId)
                @php
                    $selectedCategory = $mainCategories->firstWhere('id', $selectedCategoryId);
                @endphp
                @if($selectedCategory && $selectedCategory->hasChildren())
                    <!-- Valorant Subcategory Filters -->
                    <div class="mb-4 flex flex-wrap gap-2">
                        <a href="{{ route('admin.products', ['category_id' => $selectedCategory->id]) }}" 
                           class="px-3 py-1.5 text-sm rounded-lg transition {{ !$selectedSubcategoryId ? 'bg-violet-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600' }}">
                            All {{ $selectedCategory->name }}
                        </a>
                        @foreach($selectedCategory->children as $subcategory)
                            <a href="{{ route('admin.products', ['category_id' => $selectedCategory->id, 'subcategory_id' => $subcategory->id]) }}" 
                               class="px-3 py-1.5 text-sm rounded-lg transition {{ $selectedSubcategoryId == $subcategory->id ? 'bg-violet-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600' }}">
                                {{ $subcategory->name }}
                            </a>
                        @endforeach
                    </div>
                @endif
            @endif

            <!-- Products Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Image</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($products as $product)
                        <tr id="product-row-{{ $product->id }}" class="transition-colors duration-1000 {{ (string) request('highlight') === (string) $product->id ? 'product-row-highlight' : '' }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="h-16 w-16 object-cover rounded">
                                @else
                                    <div class="h-16 w-16 bg-gray-200 dark:bg-gray-700 rounded flex items-center justify-center">
                                        <span class="text-gray-400 text-xs">No Image</span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $product->name }}</div>
                                @if($product->description)
                                    <div class="text-sm text-gray-500 dark:text-gray-400 line-clamp-1">{{ Str::limit($product->description, 50) }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-violet-100 dark:bg-violet-900 text-violet-800 dark:text-violet-200">
                                    {{ $product->category_name }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ $product->quantity }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($product->is_active)
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">Active</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200">Inactive</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                {{-- Carry the active category tab through the edit page so saving returns here, not to "All Products" --}}
                                <a href="{{ route('admin.products.edit', array_merge(['product' => $product], request()->only('category_id', 'subcategory_id'))) }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300 mr-3">Edit</a>
                                <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                No products found. <a href="{{ route('admin.products.create') }}" class="text-blue-600 dark:text-blue-400 hover:underline">Create your first product</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <style>
        /* Fades out on its own once the row-level transition kicks in */
        .product-row-highlight {
            background-color: rgb(254 249 195); /* amber-100 */
            box-shadow: inset 4px 0 0 0 rgb(37 99 235); /* blue-600 edge marker */
        }
        @media (prefers-color-scheme: dark) {
            .product-row-highlight {
                background-color: rgb(69 26 3); /* amber-950 */
            }
        }
    </style>

    <script>
        // After saving a product we come back here with ?highlight=<id>. Scroll that row
        // into view instead of dumping the admin at the top of the list.
        document.addEventListener('DOMContentLoaded', function () {
            const highlightId = new URLSearchParams(window.location.search).get('highlight');
            if (!highlightId) return;

            const row = document.getElementById(`product-row-${highlightId}`);
            if (!row) return;

            row.scrollIntoView({ behavior: 'smooth', block: 'center' });

            // Let it sit long enough to be noticed, then fade the highlight away
            setTimeout(() => row.classList.remove('product-row-highlight'), 2500);

            // Drop ?highlight= from the URL so a refresh doesn't re-trigger the effect
            const url = new URL(window.location.href);
            url.searchParams.delete('highlight');
            window.history.replaceState({}, '', url);
        });
    </script>
</x-admin-layout>


