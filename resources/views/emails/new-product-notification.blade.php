<x-mail::message>
# New Product Available! ✨

We're excited to announce a new product in our collection!

## {{ $product->name }}

@if($product->description)
{{ Str::limit($product->description, 200) }}
@endif

**Price:** ৳{{ number_format($product->display_price ?? $product->price, 2) }}

<x-mail::button :url="route('shop.show', $product)">
View Product
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
