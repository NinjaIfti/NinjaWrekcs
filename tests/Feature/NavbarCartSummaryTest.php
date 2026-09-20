<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavbarCartSummaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_navbar_total_matches_the_cart_page_total_for_a_variant(): void
    {
        $product = Product::factory()->withCategory()->create(['price' => 0]);
        $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 450, 'quantity' => 5]);

        $cart = app(CartService::class);
        $cart->add($product, $variant, 2);

        $expected = number_format($cart->summary()->subtotal, 2);

        // The old navbar called Product::find("12_3") and produced a wrong number.
        $this->get(route('cart.index'))->assertSee('৳' . $expected, escape: false);
        $this->get('/')->assertSee('৳' . $expected, escape: false);
    }

    public function test_navbar_total_matches_the_cart_page_total_for_a_bookable_variant(): void
    {
        $product = Product::factory()->withCategory()->create([
            'price' => 0,
            'is_bookable' => true,
            'booking_fee' => 200,
        ]);
        $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 450, 'quantity' => 5]);

        $cart = app(CartService::class);
        $cart->add($product, $variant, 2);

        $expected = number_format($cart->summary()->subtotal, 2);

        // The old navbar's item-listing loop called Product::find("12_3") to
        // resolve is_bookable/display_price, which fails on a composite id.
        $this->get(route('cart.index'))->assertSee('৳' . $expected, escape: false);
        $this->get('/')->assertSee('৳' . $expected, escape: false);
    }

    public function test_navbar_item_row_reflects_current_variant_price_not_a_stale_cart_price(): void
    {
        $product = Product::factory()->withCategory()->create([
            'price' => 0,
            'is_bookable' => true,
            'booking_fee' => 200,
        ]);
        $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 450, 'quantity' => 5]);

        $cart = app(CartService::class);
        // Quantity 3, not 2: at 2 the stale total (450 x 2) equals the variant's
        // new unit price, which the home page now legitimately prints as a
        // "From" price - so the assertion below could not tell them apart.
        $cart->add($product, $variant, 3);

        // Price changes after the item is already in the cart (e.g. an admin
        // edit). The old navbar's item-listing loop rendered the composite
        // cart id's STORED price ($cartItem->price, frozen at add-time)
        // because Product::find("12_3") returns null and it falls back to
        // that stale value. CartService::lines() recomputes the price fresh
        // from the current Product/ProductVariant on every call, which is
        // what the cart page already does and what the navbar item row must
        // also do to stay correct.
        $variant->update(['price' => 900]);

        $freshLineTotal = number_format($cart->lines()->first()->lineTotal(), 2); // 2700.00
        $staleLineTotal = number_format(450 * 3, 2); // 1350.00 - what the old loop would still show

        $this->get(route('cart.index'))->assertSee('৳' . $freshLineTotal, escape: false);

        $response = $this->get('/');
        $response->assertSee('৳' . $freshLineTotal, escape: false);
        $response->assertDontSee('৳' . $staleLineTotal, escape: false);
    }
}
