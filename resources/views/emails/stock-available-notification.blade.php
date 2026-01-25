<x-mail::message>
# Product Back in Stock! 🔔

Great news! The product you were waiting for is now available!

## {{ $product->name }}

The product is back in stock and ready to order!

**Price:** ৳{{ number_format($product->display_price ?? $product->price, 2) }}  
**Stock:** {{ $product->quantity }} available

<x-mail::button :url="route('shop.show', $product)">
Order Now
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
