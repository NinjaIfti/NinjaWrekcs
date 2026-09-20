<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Notify-me sign-ups.
 *
 * The gate asked the product's own quantity column, which is 0 on every merged
 * product - so a product with stock on its variants looked permanently sold out
 * and kept accepting sign-ups for restocks it would never need.
 */
class StockNotificationSignupTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_variant_product_with_stock_is_not_accepting_signups(): void
    {
        $product = Product::factory()->withCategory()->create(['quantity' => 0]);
        ProductVariant::factory()->create([
            'product_id' => $product->id,
            'quantity' => 3,
            'is_active' => true,
        ]);

        $response = $this->postJson(route('stock-notification.store'), [
            'product_id' => $product->id,
            'email' => 'buyer@example.com',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => false]);
        $this->assertStringContainsString('in stock', $response->json('message'));
    }

    public function test_a_variant_product_that_is_fully_sold_out_accepts_signups(): void
    {
        $product = Product::factory()->withCategory()->create(['quantity' => 0]);
        ProductVariant::factory()->create([
            'product_id' => $product->id,
            'quantity' => 0,
            'is_active' => true,
        ]);

        $response = $this->postJson(route('stock-notification.store'), [
            'product_id' => $product->id,
            'email' => 'buyer@example.com',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }
}
