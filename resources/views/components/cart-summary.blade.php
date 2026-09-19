<div class="flex justify-between items-center mb-3">
    <span class="text-gray-300 font-semibold">Total:</span>
    <span class="text-xl font-bold text-violet-400">৳{{ number_format($summary->subtotal, 2) }}</span>
</div>

@if($summary->hasBookingItems)
    <div class="flex justify-between items-center mb-3 text-sm">
        <span class="text-amber-400">Booking due now</span>
        <span class="text-amber-400 font-semibold">৳{{ number_format($summary->bookingTotal, 2) }}</span>
    </div>
@endif
