<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * One image file, several rows pointing at it.
 *
 * Merging a family copied each source's image PATH onto the new variant rather
 * than copying the file, so a single file on disk is routinely referenced by a
 * source product's gallery, the merged product's legacy column and one of its
 * variants at the same time. Every delete path then removed the file whenever
 * any one of those rows went away, which blanked the photo everywhere else.
 *
 * That is how three live products - Singularity 22cm, Valorant Agent Lego Set
 * and CSGO Butterfly Knife with Box - ended up rendering a broken <img> on the
 * shop: 32 of the 193 referenced paths had no file left behind them.
 */
class SharedImageFileTest extends TestCase
{
    use RefreshDatabase;

    private ?User $admin = null;

    private function admin(): User
    {
        return $this->admin ??= User::factory()->create(['email' => 'ifti3061@gmail.com']);
    }

    private function category(): Category
    {
        return Category::firstOrCreate(
            ['slug' => 'csgo'],
            ['name' => 'CS GO', 'parent_id' => null, 'is_active' => true]
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        // RefreshDatabase does not clear the cache, and the shop and home pages
        // are both cached for 30 minutes.
        Cache::flush();
    }

    /**
     * The production failure, reduced: a merged product's variant and a
     * deletable source both name the same file.
     */
    public function test_deleting_an_inactive_source_keeps_a_file_its_merged_variant_still_uses(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/shared.png', 'binary');

        $source = Product::factory()->withCategory()->create([
            'name' => 'CSGO Butterfly - Gradient',
            'is_active' => false,
        ]);
        $source->images()->create(['path' => 'products/shared.png', 'sort_order' => 0]);

        $merged = Product::factory()->withCategory()->create([
            'name' => 'CSGO Butterfly Knife with Box',
            'is_active' => true,
            'price' => 0,
            'quantity' => 0,
        ]);
        $variant = ProductVariant::create([
            'product_id' => $merged->id,
            'name' => 'Gradient',
            'price' => 1200,
            'quantity' => 3,
            'is_active' => true,
            'sort_order' => 0,
        ]);
        // The merge copies the path, not the file.
        $variant->images()->create(['path' => 'products/shared.png', 'sort_order' => 0]);

        $this->artisan('products:delete-inactive', ['--commit' => true])->assertSuccessful();

        $this->assertDatabaseMissing('products', ['id' => $source->id]);
        $this->assertTrue(
            Storage::disk('public')->exists('products/shared.png'),
            'The merged product still points at this file, so deleting the source must not remove it.'
        );
        $this->assertSame('products/shared.png', $merged->fresh()->primaryImagePath());
    }

    /**
     * A file nothing else references should still go - otherwise the command
     * stops reclaiming disk space at all.
     */
    public function test_deleting_an_inactive_product_still_removes_its_own_unshared_files(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/only-mine.png', 'binary');

        $source = Product::factory()->withCategory()->create([
            'name' => 'Kuronami - RED',
            'is_active' => false,
        ]);
        $source->images()->create(['path' => 'products/only-mine.png', 'sort_order' => 0]);

        $this->artisan('products:delete-inactive', ['--commit' => true])->assertSuccessful();

        $this->assertFalse(Storage::disk('public')->exists('products/only-mine.png'));
    }

    /**
     * Deleting one gallery image in the admin form must not pull the file out
     * from under a variant that shares the path.
     */
    public function test_deleting_a_gallery_image_keeps_a_file_a_variant_still_uses(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/shared.png', 'binary');

        $product = Product::factory()->withCategory()->create([
            'name' => 'Singularity 22cm',
            'is_active' => true,
        ]);
        $galleryImage = $product->images()->create(['path' => 'products/shared.png', 'sort_order' => 0]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Black',
            'price' => 900,
            'quantity' => 2,
            'is_active' => true,
            'sort_order' => 0,
        ]);
        $variant->images()->create(['path' => 'products/shared.png', 'sort_order' => 0]);

        $this->actingAs($this->admin())->put(route('admin.products.update', $product), [
            'name' => $product->name,
            'description' => 'x',
            'notes' => '',
            'category_id' => $product->category_id,
            'price' => 900,
            'quantity' => 2,
            'delete_images' => [$galleryImage->id],
        ]);

        $this->assertDatabaseMissing('product_images', ['id' => $galleryImage->id]);
        $this->assertTrue(
            Storage::disk('public')->exists('products/shared.png'),
            'The variant still points at this file.'
        );
    }

    /**
     * The second half of the defect: even once the file is gone, the accessor
     * handed the dead path straight to the <img>, so a product with a perfectly
     * good second photo still rendered broken.
     */
    public function test_primary_image_skips_a_candidate_whose_file_is_missing(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/real.png', 'binary');

        $product = Product::factory()->withCategory()->create([
            'cover_photo' => 'products/deleted.png',
            'is_active' => true,
        ]);
        $product->images()->create(['path' => 'products/real.png', 'sort_order' => 0]);

        $this->assertSame('products/real.png', $product->fresh()->primaryImagePath());
    }

    public function test_primary_image_is_null_when_no_candidate_file_survives(): void
    {
        Storage::fake('public');

        $product = Product::factory()->withCategory()->create([
            'cover_photo' => 'products/deleted.png',
            'image' => 'products/also-deleted.png',
            'is_active' => true,
        ]);

        $this->assertNull($product->fresh()->primaryImagePath());
    }

    /**
     * What the customer actually sees. A dangling path reaches the browser as a
     * 404'd <img>; the placeholder is the whole point of the fallback.
     */
    public function test_a_product_with_no_surviving_file_renders_the_placeholder(): void
    {
        Storage::fake('public');

        $product = Product::factory()->withCategory()->create([
            'name' => 'Valorant Agent Lego Set',
            'cover_photo' => 'products/deleted.png',
            'is_active' => true,
            'quantity' => 4,
        ]);

        $response = $this->get(route('shop.show', $product));

        $response->assertOk();
        $response->assertSee('/img/placeholder.jpg');
        $response->assertDontSee('products/deleted.png');
    }
}
