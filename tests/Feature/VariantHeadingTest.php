<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The product page heading follows the chosen option.
 *
 * A merged product like "Valorant Gun Keychains" holds a dozen distinct items
 * as variants - Prime Classic, Reaver Krambit White, Reaver Vandal Purple. The
 * heading only ever showed the family name, so picking one gave no sign of
 * which item was in the cart-to-be. It now names the selected option, with the
 * family name kept as a smaller line above it.
 */
class VariantHeadingTest extends TestCase
{
    use RefreshDatabase;

    private function keychains(): Product
    {
        $category = Category::firstOrCreate(
            ['slug' => 'valorant-keychains-stickers'],
            ['name' => 'Keychains & Stickers', 'parent_id' => null, 'is_active' => true]
        );

        $product = Product::factory()->create([
            'name' => 'Valorant Gun Keychains',
            'category_id' => $category->id,
            'is_active' => true,
            'price' => 0,
            'quantity' => 0,
        ]);

        foreach (['Prime Classic' => 0, 'Reaver Krambit White' => 4] as $name => $qty) {
            ProductVariant::create([
                'product_id' => $product->id,
                'name' => $name,
                'price' => 350,
                'quantity' => $qty,
                'is_active' => true,
                'sort_order' => $qty,
            ]);
        }

        return $product->refresh();
    }

    public function test_the_heading_names_the_preselected_option(): void
    {
        $product = $this->keychains();

        $html = $this->get(route('shop.show', $product))->assertOk()->getContent();

        // Prime Classic is sold out, so the first in-stock option is the one
        // preselected - and the one the heading must name.
        $this->assertMatchesRegularExpression(
            '/<h1[^>]*id="product-heading"[^>]*>\s*Reaver Krambit White\s*<\/h1>/',
            $html
        );
        // The family name stays on the page as context.
        $this->assertMatchesRegularExpression('/id="product-family-name"[^>]*>\s*Valorant Gun Keychains/', $html);
    }

    public function test_every_swatch_carries_its_name_for_the_script_to_use(): void
    {
        $product = $this->keychains();

        $html = $this->get(route('shop.show', $product))->assertOk()->getContent();

        $this->assertStringContainsString('data-name="Prime Classic"', $html);
        $this->assertStringContainsString('data-name="Reaver Krambit White"', $html);
    }

    public function test_a_plain_product_keeps_its_own_name_and_no_family_line(): void
    {
        $product = Product::factory()->withCategory()->create([
            'name' => 'Sage Ring',
            'is_active' => true,
            'price' => 200,
            'quantity' => 3,
        ]);

        $html = $this->get(route('shop.show', $product))->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<h1[^>]*id="product-heading"[^>]*>\s*Sage Ring\s*<\/h1>/', $html);
        $this->assertStringNotContainsString('id="product-family-name"', $html);
    }
}
