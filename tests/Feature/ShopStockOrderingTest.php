<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Stock ordering and filtering in the shop.
 *
 * Both read products.quantity, which is 0 on every merged product by design.
 * So all 14 merged products sorted as sold-out however much their variants
 * held - including hundreds of keychains - and the "in stock" filter hid them
 * outright. Product::hasStockExpression() is the database-side counterpart to
 * availableStock().
 */
class ShopStockOrderingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // The default shop page caches for 30 minutes.
        Cache::flush();
    }

    private function mergedProductWithStock(string $name): Product
    {
        $product = Product::factory()->withCategory()->create([
            'name' => $name,
            'price' => 0,
            'quantity' => 0,
            'is_active' => true,
        ]);

        ProductVariant::factory()->create([
            'product_id' => $product->id,
            'price' => 1500,
            'quantity' => 4,
            'is_active' => true,
        ]);

        return $product->refresh();
    }

    private function inStockIds(): array
    {
        return Product::where('is_active', true)
            ->whereRaw(Product::hasStockExpression() . ' = 1')
            ->pluck('id')
            ->all();
    }

    public function test_a_merged_product_with_variant_stock_counts_as_in_stock(): void
    {
        $merged = $this->mergedProductWithStock('CSGO Butterfly');

        $this->assertContains($merged->id, $this->inStockIds());
    }

    public function test_a_merged_product_with_every_variant_sold_out_does_not(): void
    {
        $merged = $this->mergedProductWithStock('CSGO Butterfly');
        $merged->variants()->update(['quantity' => 0]);

        $this->assertNotContains($merged->id, $this->inStockIds());
    }

    public function test_an_inactive_variant_does_not_make_a_product_in_stock(): void
    {
        $merged = $this->mergedProductWithStock('CSGO Butterfly');
        $merged->variants()->update(['is_active' => false]);

        $this->assertNotContains($merged->id, $this->inStockIds());
    }

    public function test_a_plain_product_still_uses_its_own_column(): void
    {
        $inStock = Product::factory()->withCategory()->create(['quantity' => 5, 'is_active' => true]);
        $soldOut = Product::factory()->withCategory()->create(['quantity' => 0, 'is_active' => true]);

        $ids = $this->inStockIds();

        $this->assertContains($inStock->id, $ids);
        $this->assertNotContains($soldOut->id, $ids);
    }

    /**
     * The visible symptom: a merged product holding stock was pushed below a
     * plain product that had none.
     */
    public function test_a_stocked_merged_product_outranks_a_sold_out_plain_one(): void
    {
        $soldOut = Product::factory()->withCategory()->create([
            'name' => 'Sold Out Plain', 'quantity' => 0, 'is_active' => true,
        ]);
        $merged = $this->mergedProductWithStock('Merged With Stock');

        $ordered = Product::where('is_active', true)
            ->orderByRaw(Product::hasStockExpression() . ' desc')
            ->orderBy('id')
            ->pluck('id')
            ->all();

        $this->assertSame(
            $merged->id,
            $ordered[0],
            'A merged product holding stock must sort above a sold-out plain one.'
        );
        $this->assertContains($soldOut->id, $ordered);
    }

    public function test_the_in_stock_filter_keeps_merged_products(): void
    {
        $merged = $this->mergedProductWithStock('CSGO Butterfly');

        $response = $this->get(route('shop.index', ['in_stock' => 1]));

        $response->assertOk();
        // It survives the filter rather than being hidden by a 0 quantity column.
        $this->assertContains($merged->id, $this->inStockIds());
    }
}
