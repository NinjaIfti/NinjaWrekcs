<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * The share preview for a product page.
 *
 * og:image, twitter:image and the JSON-LD "image" all read the legacy `image`
 * column, which is empty on a merged product and on production named a file
 * that had been deleted - so sharing Singularity 22cm on Facebook or WhatsApp
 * produced a broken thumbnail, and Google was handed a 404.
 */
class ProductSeoImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_share_image_falls_back_when_the_legacy_file_is_gone(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/real.webp', 'binary');

        $product = Product::factory()->withCategory()->create([
            'name' => 'Singularity 22cm',
            'is_active' => true,
            'image' => 'products/deleted.png',
            'cover_photo' => null,
        ]);
        $product->images()->create(['path' => 'products/real.webp', 'sort_order' => 0]);

        $html = $this->get(route('shop.show', $product))->assertOk()->getContent();

        $this->assertStringNotContainsString('products/deleted.png', $html);
        $this->assertStringContainsString('products/real.webp', $html);
    }

    public function test_the_share_image_is_the_site_logo_when_nothing_survives(): void
    {
        Storage::fake('public');

        $product = Product::factory()->withCategory()->create([
            'is_active' => true,
            'image' => 'products/deleted.png',
        ]);

        $html = $this->get(route('shop.show', $product))->assertOk()->getContent();

        $this->assertStringNotContainsString('products/deleted.png', $html);
        $this->assertStringContainsString('og:image" content="' . asset('img/fav.png'), $html);
    }
}
