<x-mail::message>
# Order Status Update

Hello {{ $order->name }},

Your order **#{{ $order->id }}** status has been updated to **{{ ucfirst($order->status) }}**.

## Order Details

**Order ID:** #{{ $order->id }}  
**Status:** {{ ucfirst($order->status) }}  
**Total Amount:** ৳{{ number_format($order->total, 2) }}  
**Order Date:** {{ $order->created_at->format('F d, Y h:i A') }}

## Order Items

@foreach($order->items as $item)
- **{{ $item->product_name }}** - Qty: {{ $item->quantity }} × ৳{{ number_format($item->price, 2) }} = ৳{{ number_format($item->subtotal, 2) }}
@endforeach

## Summary

**Subtotal:** ৳{{ number_format($order->subtotal, 2) }}  
**Discount:** -৳{{ number_format($order->discount, 2) }}  
**Total:** ৳{{ number_format($order->total, 2) }}

@if($order->status === 'confirmed')
We have confirmed your order and payment. Your order is now being processed and will be shipped soon.
@elseif($order->status === 'processing')
Your order is currently being processed. We'll notify you once it's ready to ship.
@elseif($order->status === 'shipped')
Great news! Your order has been shipped. You should receive it within 10-15 business days.
@elseif($order->status === 'delivered')
Your order has been delivered. Thank you for shopping with us!
@elseif($order->status === 'cancelled')
Unfortunately, your order has been cancelled. If you have any questions, please contact us.
@endif

<x-mail::button :url="route('profile.orders.show', $order)">
View Order Details
</x-mail::button>

If you have any questions, please contact us at **01533133309** or reply to this email.

Thanks,<br>
{{ config('app.name') }} Team
</x-mail::message>
