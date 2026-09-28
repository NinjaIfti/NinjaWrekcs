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
 * The deals page card photo.
 *
 * It read $product->images directly, which is empty on a merged product -
 * those photos live on the variants, and the cover photo lives in its own
 * column. So a knife on offer showed "No Image" on the very page meant to
 * sell it.
 *
 * Same defect class as the shop cards, the share preview and the admin list.
 */
class DealsCardImageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        Storage::fake('public');
    }

    private function stored(string $path): string
    {
        Storage::disk('public')->put($path, 'binary');

        return $path;
    }

    private function category(): Category
    {
        return Category::firstOrCreate(
            ['slug' => 'csgo'],
            ['name' => 'CS GO', 'parent_id' => null, 'is_active' => true]
        );
    }

    /** A merged product on offer: nothing in products.images. */
    private function discountedVariantProduct(array $attributes = []): Product
    {
        $product = Product::factory()->create(array_merge([
            'name' => 'Kuronami 17cm',
            'category_id' => $this->category()->id,
            'is_active' => true,
            'price' => 0,
            'quantity' => 0,
            'sale_price' => null,
            'offer_price' => 1050,
            'offer_starts_at' => now()->subDay(),
            'offer_ends_at' => now()->addDay(),
        ], $attributes));

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Black',
            'price' => 1200,
            'quantity' => 4,
            'is_active' => true,
            'sort_order' => 0,
        ]);
        $variant->images()->create(['path' => $this->stored('products/variant.webp'), 'sort_order' => 0]);

        return $product->refresh();
    }

    public function test_a_merged_products_variant_photo_shows_on_the_deals_page(): void
    {
        $this->discountedVariantProduct();

        $html = $this->get(route('deals.index'))->assertOk()->getContent();

        $this->assertStringContainsString('products/variant.webp', $html);
        $this->assertStringNotContainsString('No Image', $html);
    }

    public function test_the_cover_photo_wins_when_there_is_one(): void
    {
        $this->discountedVariantProduct(['cover_photo' => $this->stored('products/cover.webp')]);

        $html = $this->get(route('deals.index'))->assertOk()->getContent();

        $this->assertStringContainsString('products/cover.webp', $html);
    }

    /** A dead path must not reach the browser as a broken picture. */
    public function test_a_missing_file_falls_back_to_no_image(): void
    {
        $this->discountedVariantProduct(['cover_photo' => 'products/deleted.png']);

        $html = $this->get(route('deals.index'))->assertOk()->getContent();

        $this->assertStringNotContainsString('products/deleted.png', $html);
        $this->assertStringContainsString('products/variant.webp', $html);
    }
}
