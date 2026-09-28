<?php

namespace App\Observers;

use App\Models\ProductVariant;

/**
 * Keeps a variant product's stock total in step with its options.
 *
 * products.quantity used to be typed in by hand even though the real stock has
 * lived on the variants since the merge, so the two drifted apart - two
 * keychain products have carried an inflated total ever since. Deriving it
 * here means every path keeps it right: the admin form, the merge command, and
 * an order confirming or cancelling, which move stock on the variant row.
 */
class ProductVariantObserver
{
    public function created(ProductVariant $variant): void
    {
        $this->sync($variant);
    }

    /**
     * `updated` rather than `saved` on purpose: Eloquent's increment() and
     * decrement() fire updating/updated but never saved, and those are exactly
     * how OrderStockService moves stock when an order is confirmed or
     * cancelled. Observing `saved` left the total stale after every sale.
     */
    public function updated(ProductVariant $variant): void
    {
        $this->sync($variant);
    }

    public function deleted(ProductVariant $variant): void
    {
        $this->sync($variant);
    }

    private function sync(ProductVariant $variant): void
    {
        // loadMissing would hand back a stale product when the variant was
        // fetched with its relation already loaded earlier in the request.
        $variant->product()->first()?->syncStockFromVariants();
    }
}
