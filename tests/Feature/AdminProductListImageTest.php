<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * The thumbnail in the admin product list.
 *
 * It read the legacy `image` column directly, which is null on every product
 * whose photo is a cover or lives on its variants - so the list showed "No
 * Image" for products that plainly had one (CSGO Krambit Sharpen, CSGO
 * Butterfly, Valorant Agent Lego Set), and a broken thumbnail for Singularity
 * 22cm, whose column still named a file that had been deleted.
 */
class AdminProductListImageTest extends TestCase
{
    use RefreshDatabase;

    private ?User $admin = null;

    private function admin(): User
    {
        return $this->admin ??= User::factory()->create(['email' => 'ifti3061@gmail.com']);
    }

    private function listHtml(): string
    {
        return $this->actingAs($this->admin())->get(route('admin.products'))->assertOk()->getContent();
    }

    public function test_a_cover_photo_is_shown_when_the_legacy_column_is_empty(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/cover.webp', 'binary');

        Product::factory()->withCategory()->create([
            'name' => 'CSGO Krambit Sharpen with adhesive oil',
            'image' => null,
            'cover_photo' => 'products/cover.webp',
        ]);

        $html = $this->listHtml();

        $this->assertStringContainsString('products/cover.webp', $html);
        $this->assertStringNotContainsString('No Image', $html);
    }

    public function test_a_variant_photo_is_shown_when_the_product_has_none_of_its_own(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/variant.webp', 'binary');

        $product = Product::factory()->withCategory()->create([
            'name' => 'Valorant Agent Lego Set',
            'image' => null,
            'cover_photo' => null,
            'price' => 0,
            'quantity' => 0,
        ]);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Jett',
            'price' => 900,
            'quantity' => 2,
            'is_active' => true,
            'sort_order' => 0,
        ]);
        $variant->images()->create(['path' => 'products/variant.webp', 'sort_order' => 0]);

        $html = $this->listHtml();

        $this->assertStringContainsString('products/variant.webp', $html);
        $this->assertStringNotContainsString('No Image', $html);
    }

    /**
     * Singularity 22cm: the column names a file that no longer exists. A dead
     * path reaches the browser as a broken thumbnail; "No Image" is honest.
     */
    public function test_a_dead_path_is_not_rendered_as_a_broken_thumbnail(): void
    {
        Storage::fake('public');

        Product::factory()->withCategory()->create([
            'name' => 'Singularity 22cm',
            'image' => 'products/deleted.png',
            'cover_photo' => null,
        ]);

        $html = $this->listHtml();

        $this->assertStringNotContainsString('products/deleted.png', $html);
        $this->assertStringContainsString('No Image', $html);
    }
}
