<?php

namespace App\Services;

use App\Exceptions\CartException;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;

/**
 * The only thing that touches the underlying cart package.
 *
 * Cart item keys stay composite ("12_3") for backward compatibility with
 * sessions created before this class existed, but the key is treated as
 * OPAQUE - product and variant are always read from the item's attributes,
 * never parsed out of the key. That removes the explode('_') that used to be
 * duplicated across five call sites, two of which passed the composite string
 * straight into Product::find().
 */
class CartService
{
    public function __construct(private readonly PricingService $pricing)
    {
    }

    public function add(Product $product, ?ProductVariant $variant, int $quantity): void
    {
        if ($quantity < 1) {
            throw new CartException('Quantity must be at least 1.');
        }

        if (! $product->is_active) {
            throw new CartException('This product is not available.');
        }

        if (! $product->isPurchasable()) {
            throw new CartException('This item is upcoming and cannot be added to cart yet.');
        }

        if ($variant !== null && $variant->product_id !== $product->id) {
            throw new CartException('Invalid variant selected.');
        }

        if ($variant !== null && ! $variant->is_active) {
            throw new CartException('That option is no longer available.');
        }

        if ($product->hasVariants() && $variant === null) {
            throw new CartException('Please choose an option before adding to cart.');
        }

        if (! $this->pricing->hasAnnouncedPrice($product, $variant)) {
            throw new CartException('Price will be announced later. Please check back once pricing is available.');
        }

        $this->guardBookingMix($product);

        $key = $this->keyFor($product, $variant);
        $existing = \Cart::get($key);
        $alreadyInCart = $existing ? (int) $existing->quantity : 0;
        $available = $product->availableStock($variant);

        if ($available < 1) {
            throw new CartException('That option is sold out.');
        }

        if (($alreadyInCart + $quantity) > $available) {
            throw new CartException("Only {$available} available in stock.");
        }

        \Cart::add([
            'id' => $key,
            'name' => $this->displayName($product, $variant),
            'price' => $this->pricing->priceFor($product, $variant),
            'quantity' => $quantity,
            'attributes' => $this->attributesFor($product, $variant),
        ]);
    }

    public function update(string $cartItemId, int $quantity): void
    {
        if ($quantity < 1) {
            throw new CartException('Quantity must be at least 1.');
        }

        $line = $this->lines()->firstWhere('id', $cartItemId);

        if ($line === null) {
            throw new CartException('Item not found in cart.');
        }

        $available = $line->availableStock();

        if ($quantity > $available) {
            throw new CartException("Only {$available} available in stock.");
        }

        \Cart::update($cartItemId, [
            'quantity' => ['relative' => false, 'value' => $quantity],
        ]);
    }

    public function remove(string $cartItemId): void
    {
        \Cart::remove($cartItemId);
    }

    public function clear(): void
    {
        \Cart::clear();
    }

    /**
     * Every cart item, hydrated with its Product and Variant in two queries.
     *
     * Lines whose product or variant no longer exists are dropped from the cart
     * rather than returned, so a deleted product cannot reach checkout. A line
     * whose variant does not belong to its product is dropped the same way:
     * add() validates that pairing, but a stale/legacy composite key ("12_3")
     * could otherwise rehydrate a CartLine priced from a foreign variant.
     *
     * @return Collection<int, CartLine>
     */
    public function lines(): Collection
    {
        $raw = collect(\Cart::getContent()->values());

        if ($raw->isEmpty()) {
            return collect();
        }

        $resolved = $raw->map(fn ($item) => [
            'item' => $item,
            'product_id' => $this->productIdFor($item),
            'variant_id' => $this->variantIdFor($item),
        ]);

        $products = Product::with('variants')
            ->whereIn('id', $resolved->pluck('product_id')->filter()->unique())
            ->get()
            ->keyBy('id');

        $variants = ProductVariant::with('images')
            ->whereIn('id', $resolved->pluck('variant_id')->filter()->unique())
            ->get()
            ->keyBy('id');

        return $resolved->map(function (array $entry) use ($products, $variants) {
            $product = $products->get($entry['product_id']);
            $variant = $entry['variant_id'] ? $variants->get($entry['variant_id']) : null;

            if ($product === null || ($entry['variant_id'] && $variant === null)) {
                \Cart::remove($entry['item']->id);

                return null;
            }

            if ($variant !== null && $variant->product_id !== $product->id) {
                \Cart::remove($entry['item']->id);

                return null;
            }

            return new CartLine(
                id: (string) $entry['item']->id,
                product: $product,
                variant: $variant,
                name: $this->displayName($product, $variant),
                unitPrice: $this->pricing->priceFor($product, $variant),
                compareAtPrice: $this->pricing->compareAtPriceFor($product, $variant),
                quantity: (int) $entry['item']->quantity,
                bookingFeePerUnit: $this->pricing->bookingFeeFor($product),
                image: $this->imageFor($product, $variant),
            );
        })->filter()->values();
    }

    public function summary(): CartSummary
    {
        $lines = $this->lines();

        return new CartSummary(
            subtotal: (float) $lines->sum(fn (CartLine $line) => $line->lineTotal()),
            bookingTotal: (float) $lines->sum(fn (CartLine $line) => $line->bookingTotal()),
            itemCount: (int) $lines->sum(fn (CartLine $line) => $line->quantity),
            hasBookingItems: $lines->contains(fn (CartLine $line) => $line->requiresBooking()),
        );
    }

    public function keyFor(Product $product, ?ProductVariant $variant): string
    {
        return $variant !== null
            ? $product->id . '_' . $variant->id
            : (string) $product->id;
    }

    private function displayName(Product $product, ?ProductVariant $variant): string
    {
        return $variant !== null
            ? $product->name . ' — ' . $variant->name
            : $product->name;
    }

    private function imageFor(Product $product, ?ProductVariant $variant): ?string
    {
        if ($variant !== null && $variant->images->isNotEmpty()) {
            return $variant->images->first()->path;
        }

        return $product->cover_photo ?? $product->image;
    }

    private function attributesFor(Product $product, ?ProductVariant $variant): array
    {
        return [
            'product_id' => $product->id,
            'variant_id' => $variant?->id,
            'image' => $this->imageFor($product, $variant),
            'category' => $product->category_name,
        ];
    }

    /**
     * A booking item and a regular item cannot share a cart - they are paid for
     * differently (booking requires mobile banking, COD is not allowed).
     */
    private function guardBookingMix(Product $incoming): void
    {
        $lines = $this->lines();

        if ($lines->isEmpty()) {
            return;
        }

        $cartHasBooking = $lines->contains(fn (CartLine $line) => $line->requiresBooking());
        $incomingIsBooking = $incoming->requiresBooking();

        if ($incomingIsBooking && ! $cartHasBooking) {
            throw new CartException('Cannot add a pre-order item to a cart with in-stock items. Please clear your cart or complete your current order first.');
        }

        if (! $incomingIsBooking && $cartHasBooking) {
            throw new CartException('Cannot add an in-stock item to a cart with pre-order items. Please clear your cart or complete your current order first.');
        }
    }

    /**
     * Read the product id from attributes, falling back to parsing the key ONCE
     * for carts created before this class existed.
     */
    private function productIdFor($item): ?int
    {
        $fromAttributes = $item->attributes->product_id ?? null;

        if ($fromAttributes) {
            return (int) $fromAttributes;
        }

        return (int) explode('_', (string) $item->id)[0] ?: null;
    }

    private function variantIdFor($item): ?int
    {
        $fromAttributes = $item->attributes->variant_id ?? null;

        if ($fromAttributes) {
            return (int) $fromAttributes;
        }

        $parts = explode('_', (string) $item->id);

        return isset($parts[1]) ? (int) $parts[1] : null;
    }
}
