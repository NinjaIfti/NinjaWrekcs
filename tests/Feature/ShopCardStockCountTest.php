<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * The stock count on a shop card, and in its quick view.
 *
 * is_low_stock already read the variants, so the "Only N left!" badge
 * correctly appeared for Reaver Krambit 17cm with one unit - but the number
 * inside it printed $product->quantity, which is 0 on a merged product. The
 * card said "Only 0 left!" about an item that was in stock.
 *
 * The quick view was worse: fed the same dead columns, it called the product
 * out of stock with its price still to be announced.
 */
class ShopCardStockCountTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    private function category(): Category
    {
        return Category::firstOrCreate(
            ['slug' => 'csgo'],
            ['name' => 'CS GO', 'parent_id' => null, 'is_active' => true]
        );
    }

    private function reaverKrambit(): Product
    {
        $product = Product::factory()->create([
            'name' => 'Reaver Krambit 17cm',
            'category_id' => $this->category()->id,
            'is_active' => true,
            'price' => 0,
            'quantity' => 0,
            'sale_price' => null,
            'offer_price' => null,
        ]);

        ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Purple',
            'price' => 1400,
            'quantity' => 1,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        return $product->refresh();
    }

    /** A bare /shop is the category landing page; cards need a category. */
    private function shopHtml(): string
    {
        return $this->get(route('shop.index', ['category_id' => $this->category()->id]))
            ->assertOk()
            ->getContent();
    }

    public function test_the_card_counts_the_units_held_on_the_variants(): void
    {
        $this->reaverKrambit();

        $html = $this->shopHtml();

        $this->assertStringContainsString('Only 1 left!', $html);
        $this->assertStringNotContainsString('Only 0 left!', $html);
    }

    public function test_the_quick_view_gets_the_variant_stock_and_from_price(): void
    {
        $this->reaverKrambit();

        $html = $this->shopHtml();

        // The payload is json_encode()d inside an onclick attribute, so Blade
        // escapes its quotes.
        $this->assertStringContainsString('&quot;quantity&quot;:1,', $html);
        $this->assertStringContainsString('&quot;display_price&quot;:1400', $html);
        $this->assertStringContainsString('&quot;has_variants&quot;:true', $html);
    }

    /** A plain product must keep reading its own column. */
    public function test_a_plain_product_still_reports_its_own_quantity(): void
    {
        Product::factory()->create([
            'name' => 'Sage Ring',
            'category_id' => $this->category()->id,
            'is_active' => true,
            'price' => 200,
            'quantity' => 3,
        ]);

        $html = $this->shopHtml();

        $this->assertStringContainsString('Only 3 left!', $html);
        $this->assertStringContainsString('&quot;has_variants&quot;:false', $html);
    }
}
