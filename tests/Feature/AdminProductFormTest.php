<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The admin add/edit product forms.
 *
 * The field names are the contract with AdminController::productStore and
 * productUpdate - a redesign that quietly drops one produces a form that saves
 * successfully while losing data, with nothing to show for it. These tests pin
 * every name= that the controller reads.
 */
class AdminProductFormTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        // AdminMiddleware authorises on this exact address, via User::isAdmin().
        return User::factory()->create(['email' => 'ifti3061@gmail.com']);
    }

    /** Fields both forms must post. */
    private const SHARED_FIELDS = [
        'name', 'description', 'notes', 'category_id',
        'price', 'quantity',
        'offer_price', 'offer_starts_at', 'offer_ends_at',
        'is_active', 'is_featured',
        'is_preorder', 'is_upcoming', 'is_bookable',
        'cover_photo', 'images[]',
    ];

    public function test_the_create_form_keeps_every_field(): void
    {
        $response = $this->actingAs($this->admin())->get(route('admin.products.create'));

        $response->assertOk();

        foreach (self::SHARED_FIELDS as $field) {
            $response->assertSee('name="' . $field . '"', false);
        }
    }

    public function test_the_edit_form_keeps_every_field(): void
    {
        $product = Product::factory()->withCategory()->create();

        $response = $this->actingAs($this->admin())->get(route('admin.products.edit', $product));

        $response->assertOk();

        foreach (self::SHARED_FIELDS as $field) {
            $response->assertSee('name="' . $field . '"', false);
        }
    }

    public function test_the_edit_form_keeps_its_edit_only_fields(): void
    {
        $product = Product::factory()->withCategory()->create();

        $response = $this->actingAs($this->admin())->get(route('admin.products.edit', $product));

        $response->assertOk();
        // Where the admin came from, so saving returns them to the same tab.
        $response->assertSee('name="return_category_id"', false);
        $response->assertSee('name="return_subcategory_id"', false);
    }

    public function test_the_edit_form_exposes_each_existing_variant(): void
    {
        $product = Product::factory()->withCategory()->create();
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'name' => 'Blood Red',
        ]);

        $response = $this->actingAs($this->admin())->get(route('admin.products.edit', $product));

        $response->assertOk();
        foreach (['name', 'price', 'quantity', 'sku', 'is_active'] as $field) {
            $response->assertSee('name="variants[' . $variant->id . '][' . $field . ']"', false);
        }
        $response->assertSee('name="variants[' . $variant->id . '][images][]"', false);
        $response->assertSee('name="delete_variants[]"', false);
        $response->assertSee('Blood Red', false);
    }

    public function test_existing_images_can_be_marked_for_deletion(): void
    {
        $product = Product::factory()->withCategory()->create();
        $product->images()->create(['path' => 'products/example.jpg', 'sort_order' => 0]);

        $response = $this->actingAs($this->admin())->get(route('admin.products.edit', $product));

        $response->assertOk();
        $response->assertSee('name="delete_images[]"', false);
    }

    /**
     * readonly, not disabled: a disabled input posts nothing, and the
     * controller validates price and quantity as required, so disabling them
     * would make every variant product unsaveable.
     */
    public function test_price_and_stock_are_readonly_for_a_variant_product(): void
    {
        $product = Product::factory()->withCategory()->create(['price' => 0, 'quantity' => 0]);
        ProductVariant::factory()->create(['product_id' => $product->id, 'quantity' => 3]);

        $response = $this->actingAs($this->admin())->get(route('admin.products.edit', $product->refresh()));

        $response->assertOk();
        $this->assertMatchesRegularExpression(
            '/<input[^>]*id="price"[^>]*readonly/',
            $response->getContent(),
            'Price should be readonly when the product is priced by its variants.'
        );
        $this->assertMatchesRegularExpression(
            '/<input[^>]*id="quantity"[^>]*readonly/',
            $response->getContent(),
            'Quantity should be readonly when stock is held per variant.'
        );
    }

    public function test_price_and_stock_stay_editable_without_variants(): void
    {
        $product = Product::factory()->withCategory()->create(['price' => 1400, 'quantity' => 5]);

        $response = $this->actingAs($this->admin())->get(route('admin.products.edit', $product));

        $response->assertOk();
        $this->assertDoesNotMatchRegularExpression(
            '/<input[^>]*id="price"[^>]*readonly/',
            $response->getContent()
        );
        $this->assertDoesNotMatchRegularExpression(
            '/<input[^>]*id="quantity"[^>]*readonly/',
            $response->getContent()
        );
    }

    public function test_a_non_admin_cannot_reach_the_form(): void
    {
        $this->actingAs(User::factory()->create(['email' => 'someone@example.com']))
            ->get(route('admin.products.create'))
            ->assertForbidden();
    }
}
