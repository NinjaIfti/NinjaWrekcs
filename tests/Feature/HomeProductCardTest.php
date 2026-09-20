<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Home page product cards for merged products.
 *
 * Merging moved each source's photos onto its variant and left the product's
 * own price and quantity at 0, so the cards rendered "No Image", "Price will
 * be announced soon" and "Out of Stock" for products that had photos, a price
 * and stock.
 */
class HomeProductCardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forget('homepage_data');
        Storage::fake('public');
    }

    /**
     * A path whose file actually exists. The card skips a photo whose file is
     * missing, so a test about which photo is chosen has to put it there.
     */
    private function stored(string $path): string
    {
        Storage::disk('public')->put($path, 'binary');

        return $path;
    }

    /** Mirrors a merged product: photos and price on the variants, nothing of its own. */
    private function mergedProduct(): Product
    {
        $category = Category::firstOrCreate(
            ['slug' => 'csgo'],
            ['name' => 'CS GO', 'parent_id' => null, 'is_active' => true]
        );

        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'CSGO Knife',
            'price' => 0,
            'quantity' => 0,
            'is_active' => true,
            'is_featured' => false,
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'name' => 'Gradient Finish',
            'price' => 950,
            'quantity' => 4,
            'is_active' => true,
        ]);
        $variant->images()->create(['path' => $this->stored('products/gradient.jpg'), 'sort_order' => 0]);

        ProductVariant::factory()->create([
            'product_id' => $product->id,
            'name' => 'Blue Shadow',
            'price' => 1500,
            'quantity' => 2,
            'is_active' => true,
        ]);

        return $product->refresh();
    }

    public function test_the_card_shows_a_variants_photo_rather_than_no_image(): void
    {
        $this->mergedProduct();

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('products/gradient.jpg', false);
        $response->assertDontSee('No Image');
    }

    public function test_the_card_shows_a_from_price_rather_than_announcing_one(): void
    {
        $this->mergedProduct();

        $response = $this->get('/');

        $response->assertOk();
        $response->assertDontSee('Price will be announced soon');
        $response->assertDontSee('Price to be announced');
        // The cheapest active variant, marked as a starting price.
        $response->assertSee('950.00');
        $response->assertSee('From');
    }

    public function test_the_card_reports_the_stock_held_on_the_variants(): void
    {
        $this->mergedProduct();

        $response = $this->get('/');

        $response->assertOk();
        $response->assertDontSee('Out of Stock');
    }

    public function test_a_fully_sold_out_merged_product_still_reads_out_of_stock(): void
    {
        $product = $this->mergedProduct();
        $product->variants()->update(['quantity' => 0]);
        Cache::forget('homepage_data');

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Out of Stock');
    }

    public function test_a_plain_product_keeps_its_own_photo_and_price(): void
    {
        $category = Category::firstOrCreate(
            ['slug' => 'csgo'],
            ['name' => 'CS GO', 'parent_id' => null, 'is_active' => true]
        );

        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'CSGO HawkBill Krambit',
            'price' => 950,
            'quantity' => 5,
            'is_active' => true,
        ]);
        $product->images()->create(['path' => $this->stored('products/hawkbill.jpg'), 'sort_order' => 0]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('products/hawkbill.jpg', false);
        $response->assertSee('950.00');
        $response->assertDontSee('Out of Stock');
    }
}
