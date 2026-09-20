<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Deleting a product from the admin list.
 *
 * The delete itself always worked. What did not was where it put the admin
 * afterwards: the redirect carried no query string, so the list snapped back
 * to active-only. Deleting an inactive product therefore made every other
 * inactive product disappear from view at the same time, which reads as the
 * delete having done nothing.
 */
class AdminProductDeleteTest extends TestCase
{
    use RefreshDatabase;

    private ?User $admin = null;

    private function admin(): User
    {
        return $this->admin ??= User::factory()->create(['email' => 'ifti3061@gmail.com']);
    }

    public function test_a_product_is_deleted(): void
    {
        $product = Product::factory()->withCategory()->create();

        $this->actingAs($this->admin())
            ->delete(route('admin.products.destroy', $product))
            ->assertRedirect();

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_a_product_with_variants_images_and_orders_is_deleted(): void
    {
        Storage::fake('public');

        $product = Product::factory()->withCategory()->create([
            'cover_photo' => 'products/cover.jpg',
        ]);
        $product->images()->create(['path' => 'products/gallery.jpg', 'sort_order' => 0]);
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);
        $variant->images()->create(['path' => 'products/variant.jpg', 'sort_order' => 0]);

        $order = Order::create([
            'name' => 'Past Buyer', 'phone' => '01700000000', 'address' => 'Dhaka',
            'delivery_location' => 'inside_dhaka', 'delivery_charge' => 80,
            'subtotal' => 1400, 'total' => 1480, 'payment_method' => 'cod',
            'status' => 'delivered', 'terms_accepted' => true,
        ]);
        OrderItem::create([
            'order_id' => $order->id, 'product_id' => $product->id,
            'product_variant_id' => $variant->id, 'product_name' => 'x',
            'price' => 1400, 'quantity' => 1, 'subtotal' => 1400,
        ]);

        $this->actingAs($this->admin())
            ->delete(route('admin.products.destroy', $product))
            ->assertRedirect();

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        $this->assertDatabaseMissing('product_variants', ['id' => $variant->id]);
    }

    /**
     * The actual complaint: after deleting an inactive product the admin was
     * returned to a list that hides inactive products, so the remaining ones
     * vanished and the delete looked like it had failed.
     */
    public function test_deleting_from_the_inactive_view_returns_to_the_inactive_view(): void
    {
        $product = Product::factory()->withCategory()->create(['is_active' => false]);
        $other = Product::factory()->withCategory()->create([
            'name' => 'Still Deactivated',
            'is_active' => false,
        ]);

        $response = $this->actingAs($this->admin())->delete(
            route('admin.products.destroy', ['product' => $product, 'show_inactive' => 1])
        );

        $response->assertRedirect(route('admin.products', ['show_inactive' => 1]));

        // Following it, the remaining inactive product is still on screen.
        $this->actingAs($this->admin())
            ->get(route('admin.products', ['show_inactive' => 1]))
            ->assertSee('Still Deactivated');

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        $this->assertDatabaseHas('products', ['id' => $other->id]);
    }

    public function test_deleting_keeps_the_selected_category_tab(): void
    {
        $product = Product::factory()->withCategory()->create();

        $this->actingAs($this->admin())
            ->delete(route('admin.products.destroy', [
                'product' => $product,
                'category_id' => $product->category_id,
            ]))
            ->assertRedirect(route('admin.products', ['category_id' => $product->category_id]));
    }

    public function test_the_list_offers_a_delete_that_carries_the_current_view(): void
    {
        $product = Product::factory()->withCategory()->create(['is_active' => false]);

        $response = $this->actingAs($this->admin())
            ->get(route('admin.products', ['show_inactive' => 1]));

        $response->assertOk();
        $response->assertSee('show_inactive=1', false);
    }
}
