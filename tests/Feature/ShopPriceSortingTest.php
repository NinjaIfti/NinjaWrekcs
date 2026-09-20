<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Sorting and filtering the shop by price.
 *
 * Both read products.price, which is 0 by design on a merged product - its
 * price lives on the variants. So price_asc listed every merged product first
 * at an apparent 0, and a "min price" filter excluded them outright while a
 * "max price" filter always let them through.
 *
 * Same defect class as the stock sort, and the same remedy: a database-side
 * expression mirroring what PricingService::displayPriceFor() computes in PHP,
 * because a listing cannot call PHP once per row and still paginate.
 */
class ShopPriceSortingTest extends TestCase
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

    private function plain(string $name, float $price, array $extra = []): Product
    {
        return Product::factory()->create(array_merge([
            'name' => $name,
            'category_id' => $this->category()->id,
            'is_active' => true,
            'price' => $price,
            'quantity' => 5,
            'sale_price' => null,
            'offer_price' => null,
            'offer_starts_at' => null,
            'offer_ends_at' => null,
        ], $extra));
    }

    /** A merged product: price 0 of its own, real prices on its variants. */
    private function merged(string $name, array $variantPrices): Product
    {
        $product = $this->plain($name, 0, ['quantity' => 0]);

        foreach ($variantPrices as $i => $price) {
            ProductVariant::create([
                'product_id' => $product->id,
                'name' => 'V' . $i,
                'price' => $price,
                'quantity' => 3,
                'is_active' => true,
                'sort_order' => $i,
            ]);
        }

        return $product->refresh();
    }

    /** @return array<int, string> product names in the order the shop returned them */
    private function sortedNames(string $sort): array
    {
        $response = $this->get(route('shop.index', ['category_id' => $this->category()->id, 'sort' => $sort]));
        $response->assertOk();

        $products = $response->original->getData()['products'];

        return $products->pluck('name')->all();
    }

    public function test_cheapest_first_uses_the_variant_price_of_a_merged_product(): void
    {
        $this->plain('Cheap plain', 300);
        $this->merged('Merged mid', [800, 1200]);
        $this->plain('Dear plain', 2000);

        // Without the fix the merged product sorts at 0 and leads the list.
        $this->assertSame(['Cheap plain', 'Merged mid', 'Dear plain'], $this->sortedNames('price_asc'));
    }

    public function test_dearest_first_is_the_exact_reverse(): void
    {
        $this->plain('Cheap plain', 300);
        $this->merged('Merged mid', [800, 1200]);
        $this->plain('Dear plain', 2000);

        $this->assertSame(['Dear plain', 'Merged mid', 'Cheap plain'], $this->sortedNames('price_desc'));
    }

    /** The cheapest ACTIVE variant is the comparable figure, not the first one. */
    public function test_the_cheapest_active_variant_decides_the_position(): void
    {
        $this->plain('Plain 500', 500);
        $product = $this->merged('Merged 400', [400, 3000]);

        $this->assertSame(['Merged 400', 'Plain 500'], $this->sortedNames('price_asc'));

        // Deactivate the cheap variant and the product should move above 500.
        $product->variants()->where('price', 400)->update(['is_active' => false]);
        Cache::flush();

        $this->assertSame(['Plain 500', 'Merged 400'], $this->sortedNames('price_asc'));
    }

    public function test_a_sale_price_is_what_the_product_sorts_by(): void
    {
        $this->plain('Discounted', 2000, ['sale_price' => 250]);
        $this->plain('Full price', 400);

        $this->assertSame(['Discounted', 'Full price'], $this->sortedNames('price_asc'));
    }

    public function test_an_open_offer_beats_the_list_price_but_a_closed_one_does_not(): void
    {
        $this->plain('Offer open', 2000, [
            'offer_price' => 100,
            'offer_starts_at' => now()->subDay(),
            'offer_ends_at' => now()->addDay(),
        ]);
        $this->plain('Offer expired', 900, [
            'offer_price' => 50,
            'offer_starts_at' => now()->subDays(10),
            'offer_ends_at' => now()->subDays(5),
        ]);
        $this->plain('Plain', 500);

        $this->assertSame(['Offer open', 'Plain', 'Offer expired'], $this->sortedNames('price_asc'));
    }

    /**
     * The filter had it too: a merged product's 0 failed every min_price test,
     * so filtering "over 500" hid products that cost 800.
     */
    public function test_the_minimum_price_filter_judges_a_merged_product_on_its_variants(): void
    {
        $this->merged('Merged 800', [800]);
        $this->plain('Plain 200', 200);

        $response = $this->get(route('shop.index', [
            'category_id' => $this->category()->id,
            'min_price' => 500,
        ]));

        $names = $response->original->getData()['products']->pluck('name')->all();

        $this->assertContains('Merged 800', $names);
        $this->assertNotContains('Plain 200', $names);
    }

    public function test_the_maximum_price_filter_does_not_let_every_merged_product_through(): void
    {
        $this->merged('Merged 800', [800]);
        $this->plain('Plain 200', 200);

        $response = $this->get(route('shop.index', [
            'category_id' => $this->category()->id,
            'max_price' => 500,
        ]));

        $names = $response->original->getData()['products']->pluck('name')->all();

        $this->assertContains('Plain 200', $names);
        $this->assertNotContains('Merged 800', $names);
    }
}
