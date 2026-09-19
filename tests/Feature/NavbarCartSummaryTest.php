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
}
