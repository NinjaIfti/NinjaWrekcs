<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="space-y-1">
            <span class="glitch-text-large block text-3xl font-bold" data-text="Shop">Shop</span>
            <p class="text-gray-400 text-sm">Browse our Valorant collectibles</p>
        </div>
        <span class="px-3 py-1 text-xs rounded-full bg-violet-500/20 text-violet-200 border border-violet-500/40">
            {{ $products->count() }} items
        </span>
    </div>

    <!-- Filters -->
    <div class="flex items-end gap-3">
        <div class="flex-1">
            <label for="mobileCategory" class="block text-xs uppercase tracking-wide text-gray-400 mb-2">Category</label>
            <select id="mobileCategory" class="w-full px-3 py-2 bg-gray-900 border border-violet-500/30 rounded-lg text-sm text-gray-200 focus:outline-none focus:ring-2 focus:ring-violet-500"
                onchange="if(this.value){ window.location.href = this.value; }">
                <option value="{{ route('shop.index') }}" {{ !$selectedCategory ? 'selected' : '' }}>All Products</option>
                @foreach($categories as $key => $name)
                    <option value="{{ route('shop.index', ['category' => $key]) }}" {{ $selectedCategory === $key ? 'selected' : '' }}>
                        {{ $name }}
                    </option>
                @endforeach
            </select>
        </div>
        <a href="{{ route('shop.index') }}" class="px-4 py-2 bg-violet-600 text-white rounded-lg text-sm font-semibold border border-violet-500/50 hover:bg-violet-700 transition">
            Clear
        </a>
    </div>

    @if($selectedCategory)
        <div class="px-4 py-2 bg-violet-500/10 border border-violet-500/30 rounded-lg text-sm text-violet-200 flex items-center justify-between">
            <span>Showing: {{ $categories[$selectedCategory] }}</span>
            <a href="{{ route('shop.index') }}" class="text-violet-300 underline">Reset</a>
        </div>
    @endif

    <!-- Products Grid -->
    <div class="grid grid-cols-2 gap-4">
        @forelse($products as $product)
            <div class="bg-gray-900 border border-violet-500/20 rounded-xl overflow-hidden">
                <a href="{{ route('shop.show', $product) }}" class="block">
                    <div class="relative">
                        <img src="{{ $product->image ? asset('storage/' . $product->image) : '/img/placeholder.jpg' }}" alt="{{ $product->name }}" class="w-full h-40 object-cover">
                        @if($product->quantity <= 0)
                            <div class="absolute inset-0 bg-black/70 flex items-center justify-center text-red-300 font-semibold text-sm">
                                Out of Stock
                            </div>
                        @endif
                    </div>
                </a>
                <div class="p-3 space-y-2">
                    <a href="{{ route('shop.show', $product) }}">
                        <h3 class="text-sm font-semibold text-white line-clamp-2">{{ $product->name }}</h3>
                    </a>
                    @if($product->price)
                        <p class="text-base font-bold text-violet-400">৳{{ number_format($product->price, 2) }}</p>
                    @endif
                    <div class="flex items-center justify-between">
                        @if($product->quantity > 0)
                            <span class="text-xs text-violet-200">Stock: {{ $product->quantity }}</span>
                        @else
                            <span class="text-xs text-red-300">Out of stock</span>
                        @endif
                        @if($product->quantity > 0)
                            <form action="{{ route('cart.add', $product) }}" method="POST" onclick="event.stopPropagation();">
                                @csrf
                                <button type="submit" class="px-3 py-2 text-xs font-semibold bg-gradient-to-r from-violet-600 to-purple-600 text-white rounded-lg hover:scale-105 transition">
                                    Add to Cart
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-2 text-center py-10">
                <p class="text-gray-400 text-sm">No products found.</p>
                <a href="{{ route('shop.index') }}" class="mt-3 inline-block px-5 py-2 bg-violet-600 text-white rounded-lg text-sm hover:bg-violet-700 transition">
                    View All Products
                </a>
            </div>
        @endforelse
    </div>
</div>
