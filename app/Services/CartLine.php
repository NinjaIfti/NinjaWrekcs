<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;

final class CartLine
{
    public function __construct(
        public readonly string $id,
        public readonly Product $product,
        public readonly ?ProductVariant $variant,
        public readonly string $name,
        public readonly float $unitPrice,
        public readonly ?float $compareAtPrice,
        public readonly int $quantity,
        public readonly float $bookingFeePerUnit,
        public readonly ?string $image,
    ) {
    }

    public function lineTotal(): float
    {
        return $this->unitPrice * $this->quantity;
    }

    public function bookingTotal(): float
    {
        return $this->bookingFeePerUnit * $this->quantity;
    }

    public function availableStock(): int
    {
        return $this->product->availableStock($this->variant);
    }

    public function requiresBooking(): bool
    {
        return $this->bookingFeePerUnit > 0;
    }
}
