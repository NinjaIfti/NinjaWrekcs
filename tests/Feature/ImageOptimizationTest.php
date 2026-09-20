<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\ProductImageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Downscaling product photos on the way in.
 *
 * Uploads used to go to disk untouched, so production was serving 2-3MB
 * originals into a card 288px tall: 84MB across 163 files, 22 of them over
 * 2MB. Three of the four cover photos were 2.3-2.6MB PNGs.
 */
class ImageOptimizationTest extends TestCase
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

    public function test_an_oversized_upload_is_scaled_down_to_the_cap(): void
    {
        Storage::fake('public');

        $path = app(ProductImageService::class)->store(
            UploadedFile::fake()->image('huge.png', 4000, 3000)
        );

        $this->assertStringEndsWith('.webp', $path);

        $size = getimagesizefromstring(Storage::disk('public')->get($path));

        $this->assertSame(ProductImageService::MAX_EDGE, $size[0]);
        $this->assertSame(1200, $size[1], 'Aspect ratio has to survive the downscale.');
    }

    public function test_a_photo_already_under_the_cap_keeps_its_dimensions(): void
    {
        Storage::fake('public');

        $path = app(ProductImageService::class)->store(
            UploadedFile::fake()->image('small.png', 600, 400)
        );

        $size = getimagesizefromstring(Storage::disk('public')->get($path));

        $this->assertSame([600, 400], [$size[0], $size[1]]);
    }

    public function test_uploading_a_product_photo_through_the_admin_stores_webp(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())->post(route('admin.products.store'), [
            'name' => 'CSGO HawkBill Krambit',
            'description' => 'Full metal',
            'notes' => '',
            'category_id' => $this->category()->id,
            'price' => 950,
            'quantity' => 5,
            'cover_photo' => UploadedFile::fake()->image('cover.png', 3000, 3000),
        ]);

        $product = Product::firstOrFail();

        $this->assertStringEndsWith('.webp', $product->cover_photo);
        $this->assertTrue(Storage::disk('public')->exists($product->cover_photo));

        $size = getimagesizefromstring(Storage::disk('public')->get($product->cover_photo));
        $this->assertSame(ProductImageService::MAX_EDGE, $size[0]);
    }

    /**
     * The home page carried 7 hero slides plus a card per product, all fetched
     * at once. Only the first hero slide should be eager - it is the largest
     * thing above the fold and the one the visitor is waiting on.
     */
    public function test_the_home_page_defers_every_image_but_the_first_hero_slide(): void
    {
        \Illuminate\Support\Facades\Cache::forget('homepage_data');

        $response = $this->get('/');

        $response->assertOk();

        $html = $response->getContent();

        $this->assertStringContainsString('/img/f1.jpg', $html);
        $this->assertGreaterThanOrEqual(
            5,
            substr_count($html, 'loading="lazy"'),
            'The hero slides behind the first one should not be fetched up front.'
        );

        // The eager one must not also be marked lazy.
        preg_match('/<img[^>]*\/img\/f1\.jpg[^>]*>/', $html, $firstSlide);
        $this->assertNotEmpty($firstSlide);
        $this->assertStringNotContainsString('loading="lazy"', $firstSlide[0]);
    }

    /**
     * GD flattens an animated GIF to its first frame, so it is left alone - a
     * still image is a worse picture than a large one.
     */
    public function test_an_animated_gif_is_stored_untouched(): void
    {
        Storage::fake('public');

        $gif = UploadedFile::fake()->createWithContent(
            'spin.gif',
            $this->animatedGifBytes()
        );

        $path = app(ProductImageService::class)->store($gif);

        $this->assertStringEndsWith('.gif', $path);
        $this->assertTrue(Storage::disk('public')->exists($path));
    }

    /**
     * The smallest valid two-frame GIF89a: header, two graphic control
     * extensions, two image descriptors, trailer.
     */
    private function animatedGifBytes(): string
    {
        return base64_decode(
            'R0lGODlhAQABAIAAAAAAAP///yH/C05FVFNDQVBFMi4wAwEAAAAh+QQJAAAAACwAAAAAAQAB'
            . 'AAACAkQBACH5BAkAAAAALAAAAAABAAEAAAICRAEAOw=='
        );
    }
}
