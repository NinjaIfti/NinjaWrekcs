<x-mail::message>
# Order Status Update 📦

Hello **{{ $order->name }}**,

@if($order->status === 'confirmed')
Great news! Your order has been **confirmed** and we've received your payment.
@elseif($order->status === 'processing')
Your order is now being **processed** and prepared for shipment.
@elseif($order->status === 'shipped')
Exciting news! Your order has been **shipped** and is on its way to you! 🚚
@elseif($order->status === 'delivered')
Your order has been **delivered**! We hope you enjoy your Valorant collectibles! 🎮
@elseif($order->status === 'cancelled')
Your order has been **cancelled**. If you have any questions, please contact us.
@else
Your order status has been updated to **{{ ucfirst($order->status) }}**.
@endif

---

## Order Summary

**Order Number:** #{{ $order->id }}  
**Order Date:** {{ $order->created_at->format('F d, Y h:i A') }}  
**Current Status:** <span style="color: @if($order->status === 'delivered') #16a34a @elseif($order->status === 'cancelled') #dc2626 @else #8b5cf6 @endif; font-weight: bold;">{{ ucfirst($order->status) }}</span>  
@if($oldStatus)
**Previous Status:** {{ ucfirst($oldStatus) }}
@endif

---

## Order Items

@foreach($order->items as $item)
<div style="margin-bottom: 12px; padding: 12px; background-color: #f9fafb; border-left: 3px solid #8b5cf6; border-radius: 4px;">
**{{ $item->product_name }}**  
<small style="color: #6b7280;">Quantity: {{ $item->quantity }} × ৳{{ number_format($item->price, 2) }}</small>  
<strong style="color: #8b5cf6;">৳{{ number_format($item->subtotal, 2) }}</strong>
</div>
@endforeach

---

## Payment Summary

<table style="width: 100%; margin: 20px 0;">
<tr>
    <td style="padding: 6px 0; color: #6b7280;">Subtotal:</td>
    <td style="padding: 6px 0; text-align: right; font-weight: 600;">৳{{ number_format($order->subtotal, 2) }}</td>
</tr>
@if($order->coupon_discount > 0)
<tr>
    <td style="padding: 6px 0; color: #16a34a;">Coupon Discount @if($order->coupon_code)({{ $order->coupon_code }})@endif:</td>
    <td style="padding: 6px 0; text-align: right; font-weight: 600; color: #16a34a;">-৳{{ number_format($order->coupon_discount, 2) }}</td>
</tr>
@endif
@if($order->discount > 0 && $order->discount != $order->coupon_discount)
<tr>
    <td style="padding: 6px 0; color: #16a34a;">Discount:</td>
    <td style="padding: 6px 0; text-align: right; font-weight: 600; color: #16a34a;">-৳{{ number_format($order->discount, 2) }}</td>
</tr>
@endif
<tr style="border-top: 2px solid #8b5cf6;">
    <td style="padding: 10px 0; font-size: 16px; font-weight: bold;">Total:</td>
    <td style="padding: 10px 0; text-align: right; font-size: 16px; font-weight: bold; color: #8b5cf6;">৳{{ number_format($order->total, 2) }}</td>
</tr>
</table>

---

## Shipping Information

**Delivery Address:**  
{{ $order->name }}  
{{ $order->phone }}  
{{ $order->address }}

---

@if($order->status === 'confirmed')
<x-mail::panel>
✅ **What's Next?**

Your payment has been verified and your order is now in our system. We'll start processing your items and notify you once they're ready for shipment.

**Estimated Processing Time:** 1-2 business days
</x-mail::panel>
@elseif($order->status === 'processing')
<x-mail::panel>
⚙️ **What's Next?**

We're carefully preparing your Valorant collectibles for shipment. Quality checks are being performed to ensure everything is perfect.

**Estimated Ship Date:** Within 2-3 business days
</x-mail::panel>
@elseif($order->status === 'shipped')
<x-mail::panel>
🚚 **What's Next?**

Your order is on its way! Our delivery partner will contact you shortly to arrange the delivery.

@if($order->tracking_link)
**Track Your Order:**  
You can track your shipment using the link below:

<x-mail::button :url="$order->tracking_link" color="success">
Track Shipment
</x-mail::button>
@endif

**Estimated Delivery:** 3-7 business days

Please keep your phone available for delivery coordination.
</x-mail::panel>
@elseif($order->status === 'delivered')
<x-mail::panel>
🎉 **Thank You for Shopping with Us!**

We hope you love your new Valorant collectibles! If you have any issues with your order, please contact us within 24 hours.

Would you like to leave a review? Your feedback helps us improve!
</x-mail::panel>
@elseif($order->status === 'cancelled')
<x-mail::panel>
⚠️ **Order Cancellation**

Your order has been cancelled. If you did not request this cancellation or have any questions, please contact us immediately.

Any payments made will be refunded within 5-7 business days.
</x-mail::panel>
@endif

<x-mail::button :url="route('profile.orders.show', $order)" color="primary">
View Order Details
</x-mail::button>

---

## Order Timeline

<div style="padding: 15px; background-color: #f9fafb; border-radius: 8px; margin: 20px 0;">
<ol style="list-style: none; padding: 0; margin: 0;">
    <li style="padding: 8px 0; color: {{ $order->status === 'pending' ? '#8b5cf6' : '#16a34a' }};">
        {{ $order->status === 'pending' ? '📍' : '✅' }} Pending - Order Placed
    </li>
    <li style="padding: 8px 0; color: {{ in_array($order->status, ['pending']) ? '#9ca3af' : ($order->status === 'confirmed' ? '#8b5cf6' : '#16a34a') }};">
        {{ in_array($order->status, ['pending']) ? '⏺️' : ($order->status === 'confirmed' ? '📍' : '✅') }} Confirmed - Payment Verified
    </li>
    <li style="padding: 8px 0; color: {{ in_array($order->status, ['pending', 'confirmed']) ? '#9ca3af' : ($order->status === 'processing' ? '#8b5cf6' : '#16a34a') }};">
        {{ in_array($order->status, ['pending', 'confirmed']) ? '⏺️' : ($order->status === 'processing' ? '📍' : '✅') }} Processing - Preparing Items
    </li>
    <li style="padding: 8px 0; color: {{ in_array($order->status, ['pending', 'confirmed', 'processing']) ? '#9ca3af' : ($order->status === 'shipped' ? '#8b5cf6' : '#16a34a') }};">
        {{ in_array($order->status, ['pending', 'confirmed', 'processing']) ? '⏺️' : ($order->status === 'shipped' ? '📍' : '✅') }} Shipped - On the Way
    </li>
    <li style="padding: 8px 0; color: {{ $order->status === 'delivered' ? '#16a34a' : '#9ca3af' }};">
        {{ $order->status === 'delivered' ? '✅📍' : '⏺️' }} Delivered - Completed
    </li>
</ol>
</div>

---

<x-mail::panel>
**Need Help?**

If you have any questions about your order, we're here to help!

📧 Email: ninjaifti3061@gmail.com  
📱 Phone: {{ $order->phone }}  
🌐 Website: {{ config('app.url') }}

Our support team typically responds within 24 hours.
</x-mail::panel>

Thank you for choosing NinjaWrecks for your Valorant collectibles!

Best regards,  
**The NinjaWrecks Team** 🎮

<small style="color: #9ca3af;">Order #{{ $order->id }} | {{ $order->created_at->format('M d, Y') }}</small>
</x-mail::message>
