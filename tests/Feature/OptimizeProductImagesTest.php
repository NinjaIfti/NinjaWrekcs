<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\ProductImageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * products:optimize-images
 *
 * The backfill for the 84MB of full-resolution photos already on disk. The
 * path changes with the extension, so the risk is not the encoding - it is
 * leaving a row pointing at a file that has just been deleted.
 */
class OptimizeProductImagesTest extends TestCase
{
    use RefreshDatabase;

    /** A file big enough that WebP is certain to beat it. */
    private function putLargeImage(string $path): void
    {
        Storage::disk('public')->put(
            $path,
            UploadedFile::fake()->image('x.png', 3000, 3000)->getContent()
        );
    }

    public function test_a_dry_run_changes_nothing(): void
    {
        Storage::fake('public');
        $this->putLargeImage('products/big.png');

        $product = Product::factory()->withCategory()->create(['cover_photo' => 'products/big.png']);

        $this->artisan('products:optimize-images')->assertSuccessful();

        $this->assertTrue(Storage::disk('public')->exists('products/big.png'));
        $this->assertSame('products/big.png', $product->fresh()->cover_photo);
    }

    public function test_it_rewrites_the_file_and_every_row_that_names_it(): void
    {
        Storage::fake('public');
        $this->putLargeImage('products/shared.png');

        // One file, three rows - the shape the merge left behind.
        $product = Product::factory()->withCategory()->create([
            'cover_photo' => 'products/shared.png',
            'image' => 'products/shared.png',
        ]);
        $galleryImage = $product->images()->create(['path' => 'products/shared.png', 'sort_order' => 0]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Black',
            'price' => 900,
            'quantity' => 1,
            'is_active' => true,
            'sort_order' => 0,
        ]);
        $variantImage = $variant->images()->create(['path' => 'products/shared.png', 'sort_order' => 0]);

        $this->artisan('products:optimize-images', ['--commit' => true])->assertSuccessful();

        $product->refresh();

        $this->assertSame('products/shared.webp', $product->cover_photo);
        $this->assertSame('products/shared.webp', $product->image);
        $this->assertSame('products/shared.webp', $galleryImage->fresh()->path);
        $this->assertSame('products/shared.webp', $variantImage->fresh()->path);

        $this->assertTrue(Storage::disk('public')->exists('products/shared.webp'));
        $this->assertFalse(Storage::disk('public')->exists('products/shared.png'));
    }

    public function test_the_rewritten_file_is_smaller_and_within_the_cap(): void
    {
        Storage::fake('public');
        $this->putLargeImage('products/big.png');

        $originalSize = Storage::disk('public')->size('products/big.png');

        Product::factory()->withCategory()->create(['cover_photo' => 'products/big.png']);

        $this->artisan('products:optimize-images', ['--commit' => true])->assertSuccessful();

        $newSize = Storage::disk('public')->size('products/big.webp');
        $this->assertLessThan($originalSize, $newSize);

        $dimensions = getimagesizefromstring(Storage::disk('public')->get('products/big.webp'));
        $this->assertSame(ProductImageService::MAX_EDGE, $dimensions[0]);
    }

    /**
     * The whole point of leaving small files alone: running it twice must not
     * degrade a photo by re-encoding it over and over.
     */
    public function test_running_it_twice_leaves_the_second_pass_with_nothing_to_do(): void
    {
        Storage::fake('public');
        $this->putLargeImage('products/big.png');

        Product::factory()->withCategory()->create(['cover_photo' => 'products/big.png']);

        $this->artisan('products:optimize-images', ['--commit' => true])->assertSuccessful();

        $afterFirst = Storage::disk('public')->size('products/big.webp');

        $this->artisan('products:optimize-images', ['--commit' => true])
            ->expectsOutputToContain('Nothing to rewrite.')
            ->assertSuccessful();

        $this->assertSame($afterFirst, Storage::disk('public')->size('products/big.webp'));
    }

    /**
     * 32 referenced paths on production have no file behind them. The command
     * has to step over those rather than fail on them.
     */
    public function test_a_dangling_reference_is_reported_not_fatal(): void
    {
        Storage::fake('public');

        Product::factory()->withCategory()->create(['cover_photo' => 'products/deleted.png']);

        $this->artisan('products:optimize-images', ['--commit' => true])
            ->expectsOutputToContain('have no file on disk')
            ->assertSuccessful();
    }
}
