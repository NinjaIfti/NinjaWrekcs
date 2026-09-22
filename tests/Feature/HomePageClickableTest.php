<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Home page product cards take a tap, and pre-order / upcoming is gone.
 *
 * The reviews section's decorative background was `absolute inset-0` inside a
 * section that was not `relative`, so it positioned against the page instead
 * of its own section and lay invisibly across the category showcase above it.
 * Measured in headless Chrome on production: every category card's tap landed
 * on div.absolute.inset-0.opacity-30 rather than the card's link.
 *
 * assertSee cannot detect that - the links render perfectly - so this pins the
 * two classes that prevent it. See storefront-markup-gotchas in memory.
 */
class HomePageClickableTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_the_reviews_background_is_confined_and_ignores_taps(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        preg_match('/<section[^>]*class="([^"]*)"[^>]*>\s*<!-- Background Effects -->\s*<div class="([^"]*)"/', $html, $m);

        $this->assertNotEmpty($m, 'The reviews section should still open with its background layer.');
        $this->assertContains('relative', explode(' ', $m[1]), 'Without it the layer escapes the section and covers the cards above.');
        $this->assertContains('pointer-events-none', explode(' ', $m[2]), 'A decoration must never take a tap.');
    }

    public function test_the_home_page_no_longer_shows_pre_order_and_upcoming(): void
    {
        $preorder = Category::firstOrCreate(
            ['slug' => 'pre-order-upcoming'],
            ['name' => 'Pre-order & Upcoming', 'parent_id' => null, 'is_active' => true]
        );
        Product::factory()->create(['name' => 'Future Knife', 'category_id' => $preorder->id, 'is_active' => true]);

        $categories = $this->get('/')->assertOk()->original->getData()['categories'];

        $this->assertNull($categories->firstWhere('slug', 'pre-order-upcoming'));
    }

    /** Removed from the home page only - it is still a category in the shop. */
    public function test_pre_order_and_upcoming_is_still_in_the_shop(): void
    {
        $preorder = Category::firstOrCreate(
            ['slug' => 'pre-order-upcoming'],
            ['name' => 'Pre-order & Upcoming', 'parent_id' => null, 'is_active' => true]
        );
        Product::factory()->create(['name' => 'Future Knife', 'category_id' => $preorder->id, 'is_active' => true]);

        $names = $this->get(route('shop.index', ['category_id' => $preorder->id]))
            ->assertOk()->original->getData()['products']->pluck('name')->all();

        $this->assertContains('Future Knife', $names);
    }
}
