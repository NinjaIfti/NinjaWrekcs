{{--
    The product form body, shared by admin/product-create and admin/product-edit.

    $product is null on create and the model on edit; every difference between
    the two pages is a check on that. The field names are the contract with
    AdminController::productStore/productUpdate and must not change.
--}}
@php
    $product = $product ?? null;
    $isEdit = $product !== null;
    // A product whose stock and price live on its variants - its own price and
    // quantity columns are ignored by the storefront, so the form says so
    // rather than letting an admin set a value that does nothing.
    $variantDriven = $isEdit && $product->variants->isNotEmpty();

    $field = 'w-full border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500';
    $fieldSm = 'w-full border border-gray-300 dark:border-gray-700 rounded-lg px-3 py-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-white text-sm';
    $label = 'block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2';
    $card = 'bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700';
    $cardHead = 'px-5 py-4 border-b border-gray-200 dark:border-gray-700';
    $muted = 'bg-gray-100 dark:bg-gray-900/60 text-gray-500 dark:text-gray-500 cursor-not-allowed';
@endphp

@if($isEdit)
    {{-- Which products-list tab the admin came from, so saving returns them there --}}
    <input type="hidden" name="return_category_id" value="{{ request('category_id') }}">
    <input type="hidden" name="return_subcategory_id" value="{{ request('subcategory_id') }}">
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

    {{-- ---------------------------------------------------------------- --}}
    {{-- Main column                                                      --}}
    {{-- ---------------------------------------------------------------- --}}
    <div class="lg:col-span-2 space-y-6">

        <section class="{{ $card }}">
            <div class="{{ $cardHead }}">
                <h3 class="font-semibold text-gray-900 dark:text-gray-100">Product details</h3>
            </div>
            <div class="p-5 space-y-4">
                <div>
                    <label for="name" class="{{ $label }}">Product name *</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $product->name ?? '') }}" required class="{{ $field }}">
                    @error('name')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="description" class="{{ $label }}">Description</label>
                    <textarea name="description" id="description" rows="5" class="{{ $field }}">{{ old('description', $product->description ?? '') }}</textarea>
                    @error('description')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="notes" class="{{ $label }}">Notes</label>
                    <textarea name="notes" id="notes" rows="3" class="{{ $field }}">{{ old('notes', $product->notes ?? '') }}</textarea>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Shown on the product page under "Additional notes".</p>
                    @error('notes')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        <section class="{{ $card }}">
            <div class="{{ $cardHead }}">
                <h3 class="font-semibold text-gray-900 dark:text-gray-100">Media</h3>
                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Max 10MB each. JPEG, PNG, JPG or GIF.</p>
            </div>
            <div class="p-5 space-y-5">
                <div>
                    <label class="{{ $label }}">Cover photo</label>
                    @if($isEdit && $product->cover_photo)
                        <div class="flex items-center gap-3 mb-2">
                            <img src="{{ asset('storage/' . $product->cover_photo) }}" alt="Cover" class="h-24 w-24 object-cover rounded-lg border border-gray-300 dark:border-gray-700">
                            <label class="flex items-center">
                                <input type="checkbox" name="delete_cover_photo" value="1" class="rounded border-gray-300 dark:border-gray-700 text-amber-600">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Remove cover photo</span>
                            </label>
                        </div>
                    @endif
                    <input type="file" name="cover_photo" accept="image/*" class="{{ $field }}">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">The single image used as this product's main photo.</p>
                </div>

                @if($isEdit && $product->images && $product->images->count())
                    <div>
                        <label class="{{ $label }}">Current images</label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            @foreach($product->images as $img)
                                <label class="block border border-gray-300 dark:border-gray-700 rounded-lg overflow-hidden cursor-pointer">
                                    <img src="{{ asset('storage/' . $img->path) }}" alt="{{ $product->name }}" class="h-32 w-full object-cover">
                                    <div class="p-2 flex items-center space-x-2">
                                        <input type="checkbox" name="delete_images[]" value="{{ $img->id }}" class="rounded text-blue-600 focus:ring-blue-500">
                                        <span class="text-xs text-gray-600 dark:text-gray-400">Remove</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @elseif($isEdit && $product->image)
                    <div>
                        <label class="{{ $label }}">Current image</label>
                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="h-32 w-32 object-cover rounded-lg border border-gray-300 dark:border-gray-700">
                    </div>
                @endif

                <div>
                    <label for="images" class="{{ $label }}">{{ $isEdit ? 'Add images' : 'Product images' }}</label>
                    <div id="images-container" class="space-y-3">
                        <input type="file" name="images[]" accept="image/*" class="{{ $field }}">
                    </div>
                    <button type="button" id="add-image-input" class="mt-3 inline-flex items-center px-3 py-2 text-sm font-semibold border border-dashed border-violet-500/60 text-violet-500 rounded-lg hover:bg-violet-500/10 transition">
                        <span class="text-lg leading-none mr-2">+</span> Add another image
                    </button>
                    @error('images.*')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        <section class="{{ $card }}">
            <div class="{{ $cardHead }} flex items-center justify-between gap-2">
                <h3 class="font-semibold text-gray-900 dark:text-gray-100">Pricing</h3>
                @if($isEdit && $product->has_active_offer)
                    <span class="text-xs bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 px-2 py-1 rounded-full">Offer active now</span>
                @endif
            </div>
            <div class="p-5 space-y-5">
                <div>
                    <label for="price" class="{{ $label }}">Regular price (৳) *</label>
                    <input type="number" name="price" id="price" value="{{ old('price', $product->price ?? 0) }}" step="0.01" min="0" required
                           @if($variantDriven) readonly @endif
                           class="{{ $field }} @if($variantDriven) {{ $muted }} @endif">
                    <p id="price-variant-note" class="mt-1 text-xs text-amber-600 dark:text-amber-400 {{ $variantDriven ? '' : 'hidden' }}">
                        This product is priced by its variants — the price below each variant is what customers pay.
                    </p>
                    @error('price')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div class="border-2 border-dashed border-orange-300 dark:border-orange-700 rounded-lg p-4 bg-orange-50 dark:bg-orange-900/20">
                    <h4 class="text-sm font-semibold text-orange-700 dark:text-orange-300 mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Limited time offer (optional)
                    </h4>

                    <div class="mb-4">
                        <label for="offer_price" class="{{ $label }}">Offer price (৳)</label>
                        <input type="number" name="offer_price" id="offer_price" value="{{ old('offer_price', $product->offer_price ?? '') }}" step="0.01" min="0" class="{{ $field }} focus:ring-orange-500 focus:border-orange-500">
                        @error('offer_price')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label for="offer_starts_at" class="{{ $label }}">Start date &amp; time</label>
                            <input type="datetime-local" name="offer_starts_at" id="offer_starts_at" value="{{ old('offer_starts_at', $isEdit ? $product->offer_starts_at?->format('Y-m-d\TH:i') : '') }}" class="{{ $field }} focus:ring-orange-500 focus:border-orange-500">
                            @error('offer_starts_at')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="offer_ends_at" class="{{ $label }}">End date &amp; time</label>
                            <input type="datetime-local" name="offer_ends_at" id="offer_ends_at" value="{{ old('offer_ends_at', $isEdit ? $product->offer_ends_at?->format('Y-m-d\TH:i') : '') }}" class="{{ $field }} focus:ring-orange-500 focus:border-orange-500">
                            @error('offer_ends_at')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                        ⏰ The offer activates and expires on its own between these dates. All three fields are needed for it to run.
                    </p>
                </div>
            </div>
        </section>

        <section class="{{ $card }}">
            <div class="{{ $cardHead }}">
                <h3 class="font-semibold text-gray-900 dark:text-gray-100">Inventory</h3>
            </div>
            <div class="p-5">
                <div class="sm:max-w-xs">
                    <label for="quantity" class="{{ $label }}">Quantity in stock *</label>
                    <input type="number" name="quantity" id="quantity" value="{{ old('quantity', $product->quantity ?? 0) }}" min="0" required
                           @if($variantDriven) readonly @endif
                           class="{{ $field }} @if($variantDriven) {{ $muted }} @endif">
                    <p id="quantity-variant-note" class="mt-1 text-xs text-amber-600 dark:text-amber-400 {{ $variantDriven ? '' : 'hidden' }}">
                        Stock is held per variant — this total is the sum of the active variants below.
                    </p>
                    @error('quantity')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        <section class="{{ $card }}">
            <div class="{{ $cardHead }}">
                <h3 class="font-semibold text-gray-900 dark:text-gray-100">Variants</h3>
                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                    Optional, and available to any category. Add one per colour or design; each carries its own price, stock and pictures.
                </p>
            </div>
            <div class="p-5">
                @if($isEdit)
                    @foreach($product->variants as $v)
                        <div class="variant-block mb-4 p-4 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-900/50" data-variant-id="{{ $v->id }}">
                            <div class="flex justify-between items-start gap-2 mb-2">
                                <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    <input type="text" name="variants[{{ $v->id }}][name]" value="{{ old('variants.'.$v->id.'.name', $v->name) }}" placeholder="Variant name" class="{{ $fieldSm }}">
                                    <input type="number" name="variants[{{ $v->id }}][price]" value="{{ old('variants.'.$v->id.'.price', $v->price) }}" step="0.01" min="0" placeholder="Price ৳" class="{{ $fieldSm }}">
                                    <input type="number" name="variants[{{ $v->id }}][quantity]" value="{{ old('variants.'.$v->id.'.quantity', $v->quantity) }}" min="0" placeholder="Stock" class="{{ $fieldSm }}">
                                    <input type="text" name="variants[{{ $v->id }}][sku]" value="{{ old('variants.'.$v->id.'.sku', $v->sku) }}" placeholder="SKU (optional)" class="{{ $fieldSm }}">
                                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                        <input type="hidden" name="variants[{{ $v->id }}][is_active]" value="0">
                                        <input type="checkbox" name="variants[{{ $v->id }}][is_active]" value="1" @checked(old('variants.'.$v->id.'.is_active', $v->is_active)) class="rounded"> Active
                                    </label>
                                </div>
                                <label class="flex items-center shrink-0 text-red-600 dark:text-red-400 text-sm whitespace-nowrap">
                                    <input type="checkbox" name="delete_variants[]" value="{{ $v->id }}" class="rounded">
                                    <span class="ml-1">Delete</span>
                                </label>
                            </div>
                            @if($v->images->count())
                                <div class="flex flex-wrap gap-2 mb-2">
                                    @foreach($v->images as $img)
                                        <div class="flex flex-col items-center">
                                            <img src="{{ asset('storage/' . $img->path) }}" alt="" class="h-16 w-16 object-cover rounded border border-gray-300 dark:border-gray-700">
                                            <label class="text-xs mt-1 flex items-center gap-1">
                                                <input type="checkbox" name="variants[{{ $v->id }}][delete_images][]" value="{{ $img->id }}" class="rounded"> Remove
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            <input type="file" name="variants[{{ $v->id }}][images][]" accept="image/*" multiple class="w-full text-sm border border-gray-300 dark:border-gray-700 rounded px-2 py-1 bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
                        </div>
                    @endforeach
                @endif

                <div id="new-variants-container"></div>

                <button type="button" id="add-variant-btn" class="mt-2 inline-flex items-center px-3 py-2 text-sm font-semibold border border-dashed border-amber-500 text-amber-600 dark:text-amber-400 rounded-lg hover:bg-amber-500/10 transition">
                    <span class="text-lg leading-none mr-2">+</span> Add variant
                </button>
            </div>
        </section>
    </div>

    {{-- ---------------------------------------------------------------- --}}
    {{-- Sidebar                                                          --}}
    {{-- ---------------------------------------------------------------- --}}
    <div class="space-y-6">

        <section class="{{ $card }}">
            <div class="{{ $cardHead }}">
                <h3 class="font-semibold text-gray-900 dark:text-gray-100">Status</h3>
            </div>
            <div class="p-5 space-y-3">
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-700 text-blue-600 shadow-sm focus:ring-blue-500">
                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Product is active</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $product->is_featured ?? false) ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-700 text-blue-600 shadow-sm focus:ring-blue-500">
                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Feature on homepage</span>
                </label>
                <p class="text-xs text-gray-500 dark:text-gray-400">An inactive product disappears from the shop but keeps its order history.</p>
            </div>
        </section>

        <section class="{{ $card }}">
            <div class="{{ $cardHead }}">
                <h3 class="font-semibold text-gray-900 dark:text-gray-100">Organisation</h3>
            </div>
            <div class="p-5">
                <label for="category_id" class="{{ $label }}">Category *</label>
                <select name="category_id" id="category_id" required class="{{ $field }}">
                    <option value="">Select category</option>
                    @foreach($categories as $parentCategory)
                        <optgroup label="{{ $parentCategory->name }}">
                            @if($parentCategory->hasChildren())
                                @foreach($parentCategory->children as $childCategory)
                                    <option value="{{ $childCategory->id }}" {{ old('category_id', $product->category_id ?? null) == $childCategory->id ? 'selected' : '' }}>
                                        {{ $childCategory->name }}
                                    </option>
                                @endforeach
                            @else
                                <option value="{{ $parentCategory->id }}" {{ old('category_id', $product->category_id ?? null) == $parentCategory->id ? 'selected' : '' }}>
                                    {{ $parentCategory->name }}
                                </option>
                            @endif
                        </optgroup>
                    @endforeach
                </select>
                @error('category_id')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>
        </section>

        <section class="{{ $card }}">
            <div class="{{ $cardHead }}">
                <h3 class="font-semibold text-gray-900 dark:text-gray-100">Availability</h3>
            </div>
            <div class="p-5 space-y-3">
                <label class="flex items-start">
                    <input type="checkbox" name="is_preorder" value="1" {{ old('is_preorder', $product->is_preorder ?? false) ? 'checked' : '' }} class="mt-0.5 rounded border-gray-300 dark:border-gray-700 text-purple-600 shadow-sm focus:ring-purple-500">
                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Pre-order — accepting orders now</span>
                </label>
                <label class="flex items-start">
                    <input type="checkbox" name="is_upcoming" value="1" {{ old('is_upcoming', $product->is_upcoming ?? false) ? 'checked' : '' }} class="mt-0.5 rounded border-gray-300 dark:border-gray-700 text-purple-600 shadow-sm focus:ring-purple-500">
                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Upcoming — not yet available</span>
                </label>
                <label class="flex items-start">
                    <input type="checkbox" name="is_bookable" value="1" {{ old('is_bookable', $product->is_bookable ?? false) ? 'checked' : '' }} class="mt-0.5 rounded border-gray-300 dark:border-gray-700 text-purple-600 shadow-sm focus:ring-purple-500">
                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Bookable — customer pays a ৳200 booking fee now, the rest later</span>
                </label>
                <p class="text-xs text-gray-500 dark:text-gray-400">An upcoming product cannot be bought until you clear this.</p>
            </div>
        </section>
    </div>
</div>

{{-- Sticky save bar, so Save is reachable without scrolling to the bottom --}}
<div class="sticky bottom-0 mt-6 -mx-4 sm:mx-0 px-4 sm:px-5 py-3 bg-white/95 dark:bg-gray-800/95 backdrop-blur border-t sm:border border-gray-200 dark:border-gray-700 sm:rounded-lg shadow-lg flex justify-end gap-3">
    <a href="{{ $cancelUrl }}" class="px-6 py-2 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
        Cancel
    </a>
    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
        {{ $isEdit ? 'Update product' : 'Create product' }}
    </button>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const container = document.getElementById('images-container');
        const addImageBtn = document.getElementById('add-image-input');
        if (container && addImageBtn) {
            addImageBtn.addEventListener('click', () => {
                const input = document.createElement('input');
                input.type = 'file';
                input.name = 'images[]';
                input.accept = 'image/*';
                input.className = @json($field);
                container.appendChild(input);
            });
        }

        const newVariantsContainer = document.getElementById('new-variants-container');
        const addVariantBtn = document.getElementById('add-variant-btn');
        const priceInput = document.getElementById('price');
        const quantityInput = document.getElementById('quantity');
        const priceNote = document.getElementById('price-variant-note');
        const quantityNote = document.getElementById('quantity-variant-note');
        const mutedClasses = @json(explode(' ', $muted));

        // Adding a variant makes the product-level price and stock inert, so
        // say so straight away rather than after the save.
        const syncVariantDrivenFields = () => {
            const hasAny = document.querySelectorAll('.variant-block, .new-variant-block').length > 0;
            [[priceInput, priceNote], [quantityInput, quantityNote]].forEach(([input, note]) => {
                if (!input) return;
                input.readOnly = hasAny;
                mutedClasses.forEach((c) => input.classList.toggle(c, hasAny));
                if (note) note.classList.toggle('hidden', !hasAny);
            });
        };

        let newVariantIndex = 0;
        if (newVariantsContainer && addVariantBtn) {
            addVariantBtn.addEventListener('click', () => {
                const i = newVariantIndex++;
                const block = document.createElement('div');
                block.className = 'new-variant-block mb-4 p-4 border border-amber-200 dark:border-amber-800 rounded-lg bg-amber-50/50 dark:bg-amber-900/10';
                block.innerHTML =
                    '<div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mb-2">' +
                    '<input type="text" name="new_variants[' + i + '][name]" placeholder="Variant name" class="' + @json($fieldSm) + '">' +
                    '<input type="number" name="new_variants[' + i + '][price]" step="0.01" min="0" placeholder="Price ৳" class="' + @json($fieldSm) + '">' +
                    '<input type="number" name="new_variants[' + i + '][quantity]" min="0" value="0" placeholder="Stock" class="' + @json($fieldSm) + '">' +
                    '<input type="text" name="new_variants[' + i + '][sku]" placeholder="SKU (optional)" class="' + @json($fieldSm) + '">' +
                    '</div>' +
                    '<div class="flex items-center justify-between gap-2 mb-2">' +
                    '<label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">' +
                    '<input type="hidden" name="new_variants[' + i + '][is_active]" value="0">' +
                    '<input type="checkbox" name="new_variants[' + i + '][is_active]" value="1" checked class="rounded"> Active</label>' +
                    '<button type="button" class="remove-new-variant text-sm text-red-600 dark:text-red-400 hover:underline">Remove</button>' +
                    '</div>' +
                    '<input type="file" name="new_variants[' + i + '][images][]" accept="image/*" multiple class="w-full text-sm border border-gray-300 dark:border-gray-700 rounded px-2 py-1 bg-white dark:bg-gray-900 text-gray-900 dark:text-white">';
                newVariantsContainer.appendChild(block);
                block.querySelector('.remove-new-variant').addEventListener('click', () => {
                    block.remove();
                    syncVariantDrivenFields();
                });
                syncVariantDrivenFields();
            });
        }

        syncVariantDrivenFields();
    });
</script>
