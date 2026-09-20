<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;

/**
 * The single source of truth for what an item costs.
 *
 * Before this class the precedence rule was reimplemented in five places
 * (the cart page, the checkout page, order creation, the navbar mini-cart and
 * add-to-cart) and they disagreed - the cart used display_price for pre-order
 * items while checkout used the raw price, so the cart total and the charged
 * total differed whenever a bookable item was on offer.
 */
class PricingService
{
    /**
     * Precedence, highest first:
     *   1. variant sale_price  (when a variant is given and it undercuts variant price)
     *   2. variant price       (when a variant is given)
     *   3. product offer_price (when the offer window is open and it undercuts price)
     *   4. product sale_price  (when it undercuts price)
     *   5. product price
     *
     * A variant carries its own pricing outright - a product-level offer does
     * not leak onto it, because a variant's price is not derived from the
     * product's.
     */
    public function priceFor(Product $product, ?ProductVariant $variant = null): float
    {
        if ($variant !== null) {
            $variantPrice = (float) $variant->price;
            $variantSale = $variant->sale_price !== null ? (float) $variant->sale_price : null;

            return ($variantSale !== null && $variantSale < $variantPrice)
                ? $variantSale
                : $variantPrice;
        }

        $price = (float) $product->price;

        if ($this->offerIsOpen($product) && (float) $product->offer_price < $price) {
            return (float) $product->offer_price;
        }

        if ($product->sale_price !== null && (float) $product->sale_price < $price) {
            return (float) $product->sale_price;
        }

        return $price;
    }

    /**
     * The struck-through "was" price, or null when there is no discount.
     */
    public function compareAtPriceFor(Product $product, ?ProductVariant $variant = null): ?float
    {
        $original = $variant !== null ? (float) $variant->price : (float) $product->price;
        $effective = $this->priceFor($product, $variant);

        return $effective < $original ? $original : null;
    }

    /**
     * The price to show where no variant has been chosen - a listing row, a
     * dashboard tile, a notification email.
     *
     * A variant product has no price of its own (its price column is 0 by
     * design), so the figure to show is its cheapest active variant - the
     * "from" price the shop cards already display. Null when there is no price
     * to show at all, which the caller renders rather than printing 0.
     */
    public function displayPriceFor(Product $product): ?float
    {
        if ($product->hasVariants()) {
            $prices = $product->variants
                ->where('is_active', true)
                ->map(fn (ProductVariant $variant) => $this->priceFor($product, $variant))
                ->filter(fn (float $price) => $price > 0);

            return $prices->isEmpty() ? null : (float) $prices->min();
        }

        $displayPrice = $product->display_price;

        return $displayPrice !== null && (float) $displayPrice > 0
            ? (float) $displayPrice
            : null;
    }

    /**
     * Per-unit booking fee, or 0.0 when the product does not require booking.
     *
     * This replaces the hardcoded 200 that lived in CartController,
     * CheckoutController and the checkout view. Critically it is NOT subtracted
     * from the item price the way the old code did - it is carried separately
     * in CartSummary, which is what makes it correct for variant products.
     */
    public function bookingFeeFor(Product $product): float
    {
        return $product->booking_fee !== null ? (float) $product->booking_fee : 0.0;
    }

    /**
     * Whether there is a price to charge. A variant product's own price column
     * is 0 by design - its price lives on the variants - so selecting a variant
     * is what announces one.
     */
    public function hasAnnouncedPrice(Product $product, ?ProductVariant $variant = null): bool
    {
        if ($variant !== null) {
            return (float) $variant->price > 0;
        }

        return (float) $product->price > 0;
    }

    private function offerIsOpen(Product $product): bool
    {
        if ($product->offer_price === null || $product->offer_starts_at === null || $product->offer_ends_at === null) {
            return false;
        }

        return now()->between($product->offer_starts_at, $product->offer_ends_at);
    }
}
