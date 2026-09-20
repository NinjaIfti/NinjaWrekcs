<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Product cover photos.
 *
 * Creating a product only stored the cover photo when the product was in the
 * keychains category - for anything else the upload validated, then vanished.
 * The shop cards ignored cover_photo entirely, and the product page honoured it
 * only for variant products, so a plain product's cover never appeared at all.
 */
class CoverPhotoTest extends TestCase
{
    use RefreshDatabase;

    private ?User $admin = null;

    private function admin(): User
    {
        return $this->admin ??= User::factory()->create(['email' => 'ifti3061@gmail.com']);
    }

    private function productPayload(int $categoryId, UploadedFile $cover): array
    {
        return [
            'name' => 'CSGO HawkBill Krambit',
            'description' => 'Full metal',
            'notes' => '',
            'category_id' => $categoryId,
            'price' => 950,
            'quantity' => 5,
            'cover_photo' => $cover,
        ];
    }

    /**
     * @dataProvider imageFormats
     */
    public function test_a_cover_photo_is_stored_for_any_category(string $filename, string $mime): void
    {
        Storage::fake('public');

        // Deliberately NOT the keychains category - that was the only one that
        // used to keep the upload.
        $category = Category::firstOrCreate(['slug' => 'csgo'], ['name' => 'CS GO', 'parent_id' => null, 'is_active' => true]);

        $response = $this->actingAs($this->admin())->post(
            route('admin.products.store'),
            $this->productPayload($category->id, UploadedFile::fake()->image($filename)->mimeType($mime))
        );

        $response->assertRedirect();

        $product = Product::firstOrFail();
        $this->assertNotNull($product->cover_photo, 'The cover photo was dropped on create.');
        Storage::disk('public')->assertExists($product->cover_photo);
    }

    public static function imageFormats(): array
    {
        return [
            'jpg' => ['cover.jpg', 'image/jpeg'],
            'png' => ['cover.png', 'image/png'],
        ];
    }

    public function test_the_keychains_category_still_works(): void
    {
        Storage::fake('public');
        $category = Category::firstOrCreate(['slug' => 'valorant-keychains-stickers'], ['name' => 'Valorant Keychains & Stickers', 'parent_id' => null, 'is_active' => true]);

        $this->actingAs($this->admin())->post(
            route('admin.products.store'),
            $this->productPayload($category->id, UploadedFile::fake()->image('cover.png'))
        );

        $this->assertNotNull(Product::firstOrFail()->cover_photo);
    }

    public function test_the_cover_photo_beats_the_gallery_as_the_card_image(): void
    {
        $product = Product::factory()->withCategory()->create(['cover_photo' => 'products/cover.png']);
        $product->images()->create(['path' => 'products/gallery.jpg', 'sort_order' => 0]);

        $this->assertSame('products/cover.png', $product->refresh()->primaryImagePath());
    }

    public function test_the_gallery_is_used_when_there_is_no_cover(): void
    {
        $product = Product::factory()->withCategory()->create(['cover_photo' => null]);
        $product->images()->create(['path' => 'products/gallery.jpg', 'sort_order' => 0]);

        $this->assertSame('products/gallery.jpg', $product->refresh()->primaryImagePath());
    }

    public function test_a_variant_photo_is_used_when_there_is_neither(): void
    {
        $product = Product::factory()->withCategory()->create(['cover_photo' => null, 'image' => null]);
        $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'is_active' => true]);
        $variant->images()->create(['path' => 'products/variant.jpg', 'sort_order' => 0]);

        $this->assertSame('products/variant.jpg', $product->refresh()->primaryImagePath());
    }

    public function test_a_plain_products_cover_photo_shows_on_its_product_page(): void
    {
        $product = Product::factory()->withCategory()->create([
            'cover_photo' => 'products/cover.png',
            'is_active' => true,
        ]);

        $response = $this->get(route('shop.show', $product));

        $response->assertOk();
        $response->assertSee('products/cover.png', false);
    }

    public function test_the_cover_photo_shows_on_the_home_card(): void
    {
        Cache::forget('homepage_data');
        $category = Category::firstOrCreate(
            ['slug' => 'csgo'],
            ['name' => 'CS GO', 'parent_id' => null, 'is_active' => true]
        );
        Product::factory()->create([
            'category_id' => $category->id,
            'cover_photo' => 'products/cover.png',
            'is_active' => true,
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('products/cover.png', false);
    }
}
