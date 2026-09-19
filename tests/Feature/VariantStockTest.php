<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VariantStockTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_variant_holds_its_own_stock(): void
    {
        $product = Product::factory()->withCategory()->create(['quantity' => 50]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $this->assertSame(3, $product->availableStock($variant));
    }

    public function test_stock_falls_back_to_the_product_when_no_variant_given(): void
    {
        $product = Product::factory()->withCategory()->create(['quantity' => 50]);

        $this->assertSame(50, $product->availableStock());
    }

    public function test_a_variant_product_aggregates_stock_from_active_variants_only(): void
    {
        $product = Product::factory()->withCategory()->create(['quantity' => 999]);
        ProductVariant::factory()->create(['product_id' => $product->id, 'quantity' => 4, 'is_active' => true]);
        ProductVariant::factory()->create(['product_id' => $product->id, 'quantity' => 6, 'is_active' => true]);
        ProductVariant::factory()->create(['product_id' => $product->id, 'quantity' => 99, 'is_active' => false]);

        $this->assertSame(10, $product->refresh()->availableStock());
    }

    public function test_one_variant_can_be_sold_out_while_another_is_in_stock(): void
    {
        $product = Product::factory()->withCategory()->create();
        $soldOut = ProductVariant::factory()->create(['product_id' => $product->id, 'quantity' => 0]);
        $inStock = ProductVariant::factory()->create(['product_id' => $product->id, 'quantity' => 5]);

        $this->assertSame(0, $product->availableStock($soldOut));
        $this->assertSame(5, $product->availableStock($inStock));
    }
}
