<x-admin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Edit Product') }}
            </h2>
            <a href="{{ route('admin.products') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                Back to Products
            </a>
        </div>
    </x-slot>

    <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data" id="product-edit-form">
        @csrf
        @method('PUT')

        @include('admin.partials.product-form', [
            'product' => $product,
            'cancelUrl' => route('admin.products', array_merge(
                request()->only('category_id', 'subcategory_id'),
                ['highlight' => $product->id]
            )),
        ])
    </form>
</x-admin-layout>
