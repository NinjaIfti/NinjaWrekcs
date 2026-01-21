<x-mail::message>
# @if($order->is_preorder_booking)📦 Pre-Order Booking Received @else 🎉 New Order Received @endif

Hello Admin,

@if($order->is_preorder_booking)
A **pre-order booking** has been placed and requires your attention.
@else
A **new order** has been placed and requires your attention.
@endif

## Order Summary

**Order Number:** #{{ $order->id }}  
**Order Date:** {{ $order->created_at->format('F d, Y h:i A') }}  
**Order Type:** @if($order->is_preorder_booking) 📦 **Pre-Order Booking** @else **Regular Order** @endif  
**Payment Method:** {{ ucfirst($order->payment_method) }}  
**Status:** <span style="color: #8b5cf6; font-weight: bold;">{{ ucfirst($order->status) }}</span>

---

## Customer Information

**Name:** {{ $order->name }}  
**Email:** {{ $order->email }}  
**Phone:** {{ $order->phone }}  
**Address:** {{ $order->address }}  
**Delivery Location:** {{ ucfirst(str_replace('_', ' ', $order->delivery_location ?? 'N/A')) }}

@if($order->user)
**User ID:** {{ $order->user->id }}  
**Registered User:** Yes
@else
**Registered User:** No (Guest Order)
@endif

---

## Order Items

@foreach($order->items as $item)
<div style="margin-bottom: 15px; padding: 12px; background-color: #f3f4f6; border-radius: 8px; border-left: 4px solid #8b5cf6;">
**{{ $item->product_name }}**  
@if($item->product)
Product ID: {{ $item->product_id }}  
@endif
Quantity: {{ $item->quantity }} × ৳{{ number_format($item->price, 2) }}  
**Subtotal:** ৳{{ number_format($item->subtotal, 2) }}
</div>
@endforeach

---

## Payment Summary

<table style="width: 100%; margin-top: 20px;">
<tr>
    <td style="padding: 8px 0; color: #6b7280;">Subtotal:</td>
    <td style="padding: 8px 0; text-align: right; font-weight: 600;">৳{{ number_format($order->subtotal, 2) }}</td>
</tr>
@if($order->delivery_charge && $order->delivery_charge > 0)
<tr>
    <td style="padding: 8px 0; color: #6b7280;">Delivery Charge:</td>
    <td style="padding: 8px 0; text-align: right; font-weight: 600;">+৳{{ number_format($order->delivery_charge, 2) }}</td>
</tr>
@endif
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
@if($order->is_preorder_booking && $order->booking_amount && $order->booking_amount > 0)
@php
    $fullOrderTotal = $order->subtotal + ($order->delivery_charge ?? 0) - ($order->discount ?? 0);
    $remainingDue = max(0, $order->subtotal - ($order->discount ?? 0) - $order->booking_amount);
@endphp
<tr style="border-top: 1px solid #9333ea;">
    <td style="padding: 8px 0; color: #9333ea; font-weight: 600;">Subtotal:</td>
    <td style="padding: 8px 0; text-align: right; font-weight: 600; color: #9333ea;">৳{{ number_format($order->subtotal, 2) }}</td>
</tr>
<tr>
    <td style="padding: 8px 0; color: #9333ea;">Delivery Charge:</td>
    <td style="padding: 8px 0; text-align: right; font-weight: 600; color: #9333ea;">+৳{{ number_format($order->delivery_charge ?? 0, 2) }}</td>
</tr>
<tr>
    <td style="padding: 8px 0; color: #9333ea;">Total:</td>
    <td style="padding: 8px 0; text-align: right; font-weight: 600; color: #9333ea;">৳{{ number_format($fullOrderTotal, 2) }}</td>
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
        ⚠️ Remaining DUE: ৳{{ number_format($remainingDue, 2) }} will be collected on delivery
    </td>
</tr>
@else
<tr style="border-top: 2px solid #8b5cf6;">
    <td style="padding: 12px 0; font-size: 18px; font-weight: bold; color: #8b5cf6;">Total:</td>
    <td style="padding: 12px 0; text-align: right; font-size: 18px; font-weight: bold; color: #8b5cf6;">৳{{ number_format($order->total, 2) }}</td>
</tr>
@endif
</table>

---

## Payment Details

**Payment Method:** {{ strtoupper($order->payment_method) }}  
@if($order->transaction_number)
**Transaction Number:** {{ $order->transaction_number }}  
@endif
@if($order->sending_number)
**Sending Number:** {{ $order->sending_number }}  
@endif

@if($order->payment_method === 'bkash')
⚠️ **Action Required:** Please verify the payment transaction before processing this order.
@endif

---

@if($order->is_preorder_booking)
## Pre-Order Booking Information

**Booking Fee Paid:** ৳{{ number_format($order->booking_amount ?? 0, 2) }}  
@php
    $remainingDue = max(0, ($order->subtotal - ($order->discount ?? 0)) - ($order->booking_amount ?? 0));
@endphp
**Remaining Amount:** ৳{{ number_format($remainingDue, 2) }} (Due on delivery)  

⚠️ **Important:** This is a pre-order booking. The customer has paid the booking fee plus delivery charge. The remaining product amount (৳{{ number_format($remainingDue, 2) }}) will be collected on delivery.

---

@endif
@if($order->notes)
## Order Notes

{{ $order->notes }}

---

@endif
## Quick Actions

<x-mail::button :url="route('admin.orders.edit', $order)" color="primary">
View & Manage Order
</x-mail::button>

@if($order->payment_method === 'bkash')
<x-mail::panel>
⚠️ **Payment Verification Required**

Please verify the payment transaction number **{{ $order->transaction_number }}** before processing this order.
</x-mail::panel>
@endif

---

**Order Details:**  
- Order ID: {{ $order->id }}  
- Placed on: {{ $order->created_at->format('M d, Y h:i A') }}  
- Order Link: {{ route('admin.orders.edit', $order) }}

Best regards,  
**NinjaWrecks System** 🎮

<small style="color: #9ca3af;">This is an automated notification. Please do not reply to this email.</small>
</x-mail::message>
