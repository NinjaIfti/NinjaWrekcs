<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The Back link on a product page.
 *
 * It was a hardcoded route('shop.index'), so whatever the customer had been
 * browsing - a category, a search, page 3 - was thrown away and they landed at
 * the top of the unfiltered shop.
 */
class ProductPageBackLinkTest extends TestCase
{
    use RefreshDatabase;

    private function product(?Category $category = null): Product
    {
        $category ??= Category::factory()->create(['name' => 'Valorant', 'slug' => 'valorant-test']);

        return Product::factory()->create([
            'category_id' => $category->id,
            'is_active' => true,
        ]);
    }

    public function test_the_back_link_keeps_the_category_being_browsed(): void
    {
        $product = $this->product();

        $response = $this->get(route('shop.show', [
            'product' => $product,
            'category_id' => $product->category_id,
        ]));

        $response->assertOk();
        $response->assertSee('category_id=' . $product->category_id, false);
    }

    public function test_the_back_link_keeps_a_search_and_the_page_number(): void
    {
        $product = $this->product();

        $response = $this->get(route('shop.show', [
            'product' => $product,
            'search' => 'kuronami',
            'page' => 3,
        ]));

        $response->assertOk();
        $response->assertSee('search=kuronami', false);
        $response->assertSee('page=3', false);
    }

    /**
     * Arriving from the home page or a shared link carries no filters, so the
     * link falls back to the category the product is actually in - which is
     * still better than the unfiltered shop.
     */
    public function test_with_no_filters_it_falls_back_to_the_products_own_category(): void
    {
        $product = $this->product();

        $response = $this->get(route('shop.show', $product));

        $response->assertOk();
        $response->assertSee('category_id=' . $product->category_id, false);
    }

    public function test_the_link_is_labelled_with_the_destination(): void
    {
        $category = Category::factory()->create(['name' => 'CS GO', 'slug' => 'csgo-test']);
        $product = $this->product($category);

        $response = $this->get(route('shop.show', $product));

        $response->assertOk();
        $response->assertSee('Back to CS GO');
    }

    public function test_a_product_with_no_category_still_offers_the_shop(): void
    {
        $product = Product::factory()->create(['category_id' => null, 'is_active' => true]);

        $response = $this->get(route('shop.show', $product));

        $response->assertOk();
        $response->assertSee('Back to Shop');
    }

    public function test_the_listing_hands_its_filters_to_the_product_links(): void
    {
        $product = $this->product();

        $response = $this->get(route('shop.index', [
            'category_id' => $product->category_id,
            'sort' => 'price_low',
        ]));

        $response->assertOk();
        // The product link carries the listing's state forward.
        $response->assertSee('category_id=' . $product->category_id, false);
        $response->assertSee('sort=price_low', false);
    }
}
