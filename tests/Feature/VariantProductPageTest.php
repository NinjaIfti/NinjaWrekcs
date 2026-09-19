<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The product detail page for a merged, variant-backed product.
 *
 * A merged product carries no price or stock of its own - both live on its
 * variants, so its own price and quantity columns are 0 by design. The page
 * has to read the variants, not those legacy columns, or a product that is
 * genuinely in stock renders as "Out of Stock" with no price.
 */
class VariantProductPageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Mirrors production's "Reaver Krambit 17cm": price 0, quantity 0, four
     * colour variants, exactly one of which still has stock.
     */
    private function mergedProduct(): Product
    {
        $product = Product::factory()->withCategory()->create([
            'name' => 'Reaver Krambit 17cm',
            'price' => 0,
            'quantity' => 0,
            'is_active' => true,
        ]);

        foreach ([
            ['name' => 'Purple', 'quantity' => 0],
            ['name' => 'Blood Red', 'quantity' => 1],
            ['name' => 'Silver Shadow', 'quantity' => 0],
            ['name' => 'Green Shadow', 'quantity' => 0],
        ] as $i => $spec) {
            ProductVariant::factory()->create([
                'product_id' => $product->id,
                'name' => $spec['name'],
                'price' => 1400,
                'sale_price' => null,
                'quantity' => $spec['quantity'],
                'is_active' => true,
                'sort_order' => $i,
            ]);
        }

        return $product->refresh();
    }

    public function test_a_variant_product_does_not_claim_its_price_is_unannounced(): void
    {
        $response = $this->get(route('shop.show', $this->mergedProduct()));

        $response->assertOk();
        $response->assertDontSee('Price to be announced');
        $response->assertDontSee('Price will be announced soon');
    }

    public function test_a_variant_product_shows_the_variant_price(): void
    {
        $response = $this->get(route('shop.show', $this->mergedProduct()));

        $response->assertOk();
        $response->assertSee('1,400.00');
    }

    /**
     * The swatch JS carries both the in-stock and sold-out lines as templates,
     * so a whole-page assertion matches either way. Only the rendered
     * #stock-status block says what the page actually claims.
     */
    private function stockBlock(string $html): string
    {
        preg_match('/<div id="stock-status".*?<\/div>/s', $html, $matches);

        return $matches[0] ?? '(no #stock-status block rendered)';
    }

    public function test_a_variant_product_with_stock_shows_the_selected_variants_stock(): void
    {
        $response = $this->get(route('shop.show', $this->mergedProduct()));

        $response->assertOk();
        $this->assertStringContainsString(
            'In Stock (1 available)',
            $this->stockBlock($response->getContent())
        );
    }

    public function test_a_variant_product_with_stock_can_be_added_to_the_cart(): void
    {
        $response = $this->get(route('shop.show', $this->mergedProduct()));

        $response->assertOk();
        $response->assertSee('id="add-to-cart-form"', false);
    }

    public function test_a_variant_product_with_every_variant_sold_out_is_out_of_stock(): void
    {
        $product = $this->mergedProduct();
        $product->variants()->update(['quantity' => 0]);

        $response = $this->get(route('shop.show', $product->refresh()));

        $response->assertOk();
        $this->assertStringContainsString(
            'Out of Stock',
            $this->stockBlock($response->getContent())
        );
        $response->assertDontSee('id="add-to-cart-form"', false);
    }
}
