<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Link nesting on the storefront.
 *
 * The shop card used to be one <a> wrapping everything, including the "Choose
 * Options" link inside its hover overlay. An <a> cannot contain another <a> -
 * the parser closes the outer one early and the DOM that results is not what
 * the markup says, which left the product name below unresponsive to taps on
 * mobile while the button still worked.
 *
 * assertSee cannot catch this, because the server output looks fine; only the
 * nesting does. So the nesting is measured directly.
 */
class ShopCardMarkupTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // The default shop page caches for 30 minutes and RefreshDatabase does
        // not clear the cache, so a previous test's page would be served here.
        \Illuminate\Support\Facades\Cache::flush();
    }

    private function variantProduct(): Product
    {
        $product = Product::factory()->withCategory()->create([
            'name' => 'CSGO Butterfly',
            'price' => 0,
            'quantity' => 0,
            'is_active' => true,
        ]);

        ProductVariant::factory()->create([
            'product_id' => $product->id,
            'name' => 'Blue Shadow',
            'price' => 1500,
            'quantity' => 4,
            'is_active' => true,
        ]);

        return $product->refresh();
    }

    /**
     * Walks the anchors in document order and reports the deepest nesting
     * reached. Anything above 1 is invalid HTML the browser will rewrite.
     */
    private function deepestAnchorNesting(string $html): int
    {
        preg_match_all('#<a\b|</a\s*>#i', $html, $matches);

        $depth = 0;
        $deepest = 0;

        foreach ($matches[0] as $token) {
            if (stripos($token, '</a') === 0) {
                $depth = max(0, $depth - 1);
            } else {
                $depth++;
                $deepest = max($deepest, $depth);
            }
        }

        return $deepest;
    }

    public function test_the_product_page_never_nests_one_link_inside_another(): void
    {
        $product = $this->variantProduct();

        $html = $this->get(route('shop.show', $product))->assertOk()->getContent();

        $this->assertSame(
            1,
            $this->deepestAnchorNesting($html),
            'An <a> inside an <a> makes the browser rewrite the DOM and breaks sibling links.'
        );
    }

    public function test_the_shop_listing_never_nests_one_link_inside_another(): void
    {
        $this->variantProduct();

        $html = $this->get(route('shop.index'))->assertOk()->getContent();

        $this->assertSame(1, $this->deepestAnchorNesting($html));
    }

    public function test_the_home_page_never_nests_one_link_inside_another(): void
    {
        $this->variantProduct();

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertSame(1, $this->deepestAnchorNesting($html));
    }
}
