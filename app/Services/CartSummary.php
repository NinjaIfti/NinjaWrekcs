<?php

namespace App\Services;

final class CartSummary
{
    public function __construct(
        public readonly float $subtotal,
        public readonly float $bookingTotal,
        public readonly int $itemCount,
        public readonly bool $hasBookingItems,
    ) {
    }

    public function isEmpty(): bool
    {
        return $this->itemCount === 0;
    }
}
