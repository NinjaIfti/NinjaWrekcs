<x-mail::message>
# Order Confirmed! 🎮

Thank you for your order, **{{ $order->name }}**!

We're excited to fulfill your Valorant collectibles order. Your order has been received and is being prepared for shipment.

## Order Details

**Order Number:** #{{ $order->id }}  
**Order Date:** {{ $order->created_at->format('F d, Y h:i A') }}  
**Order Type:** @if($order->is_preorder_booking) 📦 **Pre-Order Booking** @else Regular Order @endif  
**Payment Method:** {{ ucfirst($order->payment_method) }}  
**Status:** <span style="color: #8b5cf6; font-weight: bold;">{{ ucfirst($order->status) }}</span>

---

## Shipping Information

**Name:** {{ $order->name }}  
**Phone:** {{ $order->phone }}  
**Address:** {{ $order->address }}  
@if($order->email)
**Email:** {{ $order->email }}
@endif

---

## Order Items

@foreach($order->items as $item)
<div style="margin-bottom: 15px; padding: 10px; background-color: #f3f4f6; border-radius: 8px;">
**{{ $item->product_name }}**  
Quantity: {{ $item->quantity }} × ৳{{ number_format($item->price, 2) }}  
Subtotal: ৳{{ number_format($item->subtotal, 2) }}
</div>
@endforeach

---

## Payment Summary

<table style="width: 100%; margin-top: 20px;">
<tr>
    <td style="padding: 8px 0; color: #6b7280;">Subtotal:</td>
    <td style="padding: 8px 0; text-align: right; font-weight: 600;">৳{{ number_format($order->subtotal, 2) }}</td>
</tr>
@if($order->coupon_discount > 0)
<tr>
    <td style="padding: 8px 0; color: #16a34a;">Coupon Discount @if($order->coupon_code)({{ $order->coupon_code }})@endif:</td>
    <td style="padding: 8px 0; text-align: right; font-weight: 600; color: #16a34a;">-৳{{ number_format($order->coupon_discount, 2) }}</td>
</tr>
@endif
@if($order->discount > 0 && $order->discount != $order->coupon_discount)
<tr>
    <td style="padding: 8px 0; color: #16a34a;">Discount:</td>
    <td style="padding: 8px 0; text-align: right; font-weight: 600; color: #16a34a;">-৳{{ number_format($order->discount, 2) }}</td>
</tr>
@endif
@if($order->delivery_charge && $order->delivery_charge > 0)
<tr>
    <td style="padding: 8px 0; color: #6b7280;">Delivery Charge:</td>
    <td style="padding: 8px 0; text-align: right; font-weight: 600;">+৳{{ number_format($order->delivery_charge, 2) }}</td>
</tr>
@endif
@if($order->is_preorder_booking && $order->booking_amount && $order->booking_amount > 0)
<tr style="border-top: 1px solid #9333ea;">
    <td style="padding: 8px 0; color: #9333ea; font-weight: 600;">Total:</td>
    <td style="padding: 8px 0; text-align: right; font-weight: 600; color: #9333ea;">৳{{ number_format($order->subtotal + $order->delivery_charge - ($order->discount ?? 0), 2) }}</td>
</tr>
<tr>
    <td style="padding: 8px 0; color: #9333ea;">Booking Fee Paid:</td>
    <td style="padding: 8px 0; text-align: right; font-weight: 600; color: #9333ea;">-৳{{ number_format($order->booking_amount, 2) }}</td>
</tr>
<tr style="border-top: 2px solid #9333ea;">
    <td style="padding: 12px 0; font-size: 18px; font-weight: bold; color: #9333ea;">Amount Paid Now:</td>
    <td style="padding: 12px 0; text-align: right; font-size: 18px; font-weight: bold; color: #9333ea;">৳{{ number_format($order->total, 2) }}</td>
</tr>
<tr>
    <td colspan="2" style="padding: 8px 0; font-size: 12px; color: #9333ea; font-style: italic;">
        ⚠️ Remaining DUE will be collected on Cash On Delivery
    </td>
</tr>
@else
<tr style="border-top: 2px solid #8b5cf6;">
    <td style="padding: 12px 0; font-size: 18px; font-weight: bold; color: #8b5cf6;">Total:</td>
    <td style="padding: 12px 0; text-align: right; font-size: 18px; font-weight: bold; color: #8b5cf6;">৳{{ number_format($order->total, 2) }}</td>
</tr>
@endif
</table>

@if($order->payment_method === 'bkash')
---

## Payment Information

**Transaction Number:** {{ $order->transaction_number }}  
**Sending Number:** {{ $order->sending_number }}

We will verify your payment and process your order shortly.
@elseif($order->payment_method === 'cod')
---

## Cash on Delivery

@if($order->is_preorder_booking)
**Pre-Order Booking:** You've paid the booking fee of **৳{{ number_format($order->booking_amount ?? 0, 2) }}**.  
The remaining amount of **৳{{ number_format($order->booking_amount ?? 0, 2) }}** will be collected on delivery.
@else
You will pay **৳{{ number_format($order->total, 2) }}** when your order is delivered.
@endif
@endif

@if($order->is_preorder_booking)
---

## Pre-Order Booking Information

**Booking Fee Paid:** ৳{{ number_format($order->booking_amount ?? 0, 2) }}  
**Status:** ✅ Booking confirmed

**Important:** This is a pre-order booking. The remaining amount will be collected when your order is delivered. You will be notified when your items are ready to ship.
@endif

@if($order->notes)
---

## Order Notes

{{ $order->notes }}
@endif

---

## What's Next?

1. **Order Confirmation** - You are here! ✅
2. **Processing** - We're preparing your items
3. **Shipped** - Your order is on the way
4. **Delivered** - Enjoy your Valorant collectibles!

You can track your order status anytime by logging into your account.

<x-mail::button :url="route('profile.index')">
View Order Status
</x-mail::button>

If you have any questions about your order, feel free to contact us.

<x-mail::panel>
**Need Help?**  
Email: ninjaifti3061@gmail.com  
Phone: {{ $order->phone }}  
Website: {{ config('app.url') }}
</x-mail::panel>

Thank you for shopping with us!

Best regards,  
**NinjaWrecks Team** 🎮

<small style="color: #9ca3af;">Order ID: {{ $order->id }} | Placed on {{ $order->created_at->format('M d, Y') }}</small>
</x-mail::message>
















