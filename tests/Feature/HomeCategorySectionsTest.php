<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * The "Shop By Category" sections on the home page.
 *
 * The home page showed a section per category whether or not it had anything
 * in it, so Pre-order Upcoming rendered as a heading, "0 products available",
 * a View All button and an empty box.
 */
class HomeCategorySectionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // The page caches its data for 30 minutes.
        Cache::forget('homepage_data');
    }

    /**
     * The home page's categories are created by migration, so take the
     * existing row rather than making a second one with the same slug.
     */
    private function category(string $slug, string $name): Category
    {
        return Category::firstOrCreate(
            ['slug' => $slug],
            ['name' => $name, 'parent_id' => null, 'is_active' => true]
        );
    }

    public function test_a_category_with_no_products_is_not_shown(): void
    {
        $this->category('pre-order-upcoming', 'Pre-order Upcoming');

        $response = $this->get('/');

        $response->assertOk();
        $response->assertDontSee('Pre-order Upcoming');
        $response->assertDontSee('No products in this category yet');
    }

    public function test_a_category_with_products_is_still_shown(): void
    {
        $category = $this->category('valorant', 'Valorant');
        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'RGX Dagger',
            'is_active' => true,
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Valorant');
        $response->assertSee('RGX Dagger');
    }

    /**
     * Only active products count, so a category holding nothing but
     * merged-away sources is empty as far as the home page is concerned.
     */
    public function test_a_category_holding_only_inactive_products_is_not_shown(): void
    {
        $category = $this->category('pre-order-upcoming', 'Pre-order Upcoming');
        Product::factory()->create([
            'category_id' => $category->id,
            'is_active' => false,
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertDontSee('Pre-order Upcoming');
    }
}
