<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_adding_a_variant_redirects_back_with_success(): void
    {
        $product = Product::factory()->withCategory()->create(['price' => 0]);
        $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 450, 'quantity' => 5]);

        $response = $this->post(route('cart.add', $product), ['variant_id' => $variant->id, 'quantity' => 1]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertSame(1, app(\App\Services\CartService::class)->summary()->itemCount);
    }

    public function test_adding_a_sold_out_variant_shows_an_error(): void
    {
        $product = Product::factory()->withCategory()->create(['price' => 0]);
        $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 450, 'quantity' => 0]);

        $response = $this->post(route('cart.add', $product), ['variant_id' => $variant->id, 'quantity' => 1]);

        $response->assertSessionHas('error');
        $this->assertSame(0, app(\App\Services\CartService::class)->summary()->itemCount);
    }

    public function test_the_cart_page_renders_a_variant_line(): void
    {
        $product = Product::factory()->withCategory()->create(['name' => 'RGX Butterfly', 'price' => 0]);
        $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'name' => 'Blue', 'price' => 450, 'quantity' => 5]);

        $this->post(route('cart.add', $product), ['variant_id' => $variant->id, 'quantity' => 2]);

        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee('RGX Butterfly')
            ->assertSee('Blue');
    }

    public function test_updating_beyond_variant_stock_is_rejected(): void
    {
        $product = Product::factory()->withCategory()->create(['price' => 0]);
        $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 450, 'quantity' => 3]);
        $this->post(route('cart.add', $product), ['variant_id' => $variant->id, 'quantity' => 1]);

        $key = $product->id . '_' . $variant->id;

        $this->put(route('cart.update', $key), ['quantity' => 10])
            ->assertSessionHas('error');

        $this->assertSame(1, app(\App\Services\CartService::class)->summary()->itemCount);
    }
}
