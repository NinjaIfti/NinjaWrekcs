<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * products:delete-inactive
 *
 * order_items.product_id is ON DELETE CASCADE, so deleting a product that
 * appears on a past order silently takes that order's line items with it. On
 * production 72 of the 81 inactive products are referenced by 289 orders, so
 * the whole value of this command is in what it refuses to delete.
 */
class DeleteInactiveProductsTest extends TestCase
{
    use RefreshDatabase;

    private function orderedProduct(): Product
    {
        $product = Product::factory()->withCategory()->create([
            'name' => 'RGX DAGGER - Red',
            'is_active' => false,
        ]);

        // No OrderFactory in this project, so build the minimum an order needs.
        $order = Order::create([
            'name' => 'Past Buyer',
            'phone' => '01700000000',
            'address' => 'Dhaka',
            'delivery_location' => 'inside_dhaka',
            'delivery_charge' => 80,
            'subtotal' => 1400,
            'total' => 1480,
            'payment_method' => 'cod',
            'status' => 'delivered',
            'terms_accepted' => true,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => 1400,
            'quantity' => 1,
            'subtotal' => 1400,
        ]);

        return $product;
    }

    public function test_a_dry_run_deletes_nothing(): void
    {
        $product = Product::factory()->withCategory()->create(['is_active' => false]);

        $this->artisan('products:delete-inactive')->assertSuccessful();

        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    public function test_it_deletes_an_inactive_product_that_was_never_ordered(): void
    {
        $product = Product::factory()->withCategory()->create(['is_active' => false]);

        $this->artisan('products:delete-inactive', ['--commit' => true])->assertSuccessful();

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_it_leaves_active_products_alone(): void
    {
        $product = Product::factory()->withCategory()->create(['is_active' => true]);

        $this->artisan('products:delete-inactive', ['--commit' => true])->assertSuccessful();

        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    /**
     * The point of the whole command.
     */
    public function test_it_refuses_to_delete_a_product_that_carries_order_history(): void
    {
        $product = $this->orderedProduct();

        $this->artisan('products:delete-inactive', ['--commit' => true])->assertSuccessful();

        $this->assertDatabaseHas('products', ['id' => $product->id]);
        $this->assertSame(1, OrderItem::where('product_id', $product->id)->count());
    }

    public function test_the_order_line_survives_even_when_other_products_are_deleted(): void
    {
        $ordered = $this->orderedProduct();
        $orphan = Product::factory()->withCategory()->create(['is_active' => false]);

        $this->artisan('products:delete-inactive', ['--commit' => true])->assertSuccessful();

        $this->assertDatabaseMissing('products', ['id' => $orphan->id]);
        $this->assertDatabaseHas('products', ['id' => $ordered->id]);
        $this->assertSame(1, OrderItem::count(), 'no order item may be cascaded away');
    }

    public function test_it_names_what_it_is_keeping_and_why(): void
    {
        $this->orderedProduct();

        $this->artisan('products:delete-inactive')
            ->expectsOutputToContain('KEPT')
            ->assertSuccessful();
    }

    public function test_it_removes_the_image_files_of_what_it_deletes(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/gone.jpg', 'x');
        Storage::disk('public')->put('products/variant-gone.jpg', 'x');

        $product = Product::factory()->withCategory()->create([
            'is_active' => false,
            'cover_photo' => 'products/gone.jpg',
        ]);
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);
        $variant->images()->create(['path' => 'products/variant-gone.jpg', 'sort_order' => 0]);

        $this->artisan('products:delete-inactive', ['--commit' => true])->assertSuccessful();

        Storage::disk('public')->assertMissing('products/gone.jpg');
        Storage::disk('public')->assertMissing('products/variant-gone.jpg');
    }

    public function test_it_keeps_the_files_of_a_product_it_did_not_delete(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/keep.jpg', 'x');

        $product = $this->orderedProduct();
        $product->update(['cover_photo' => 'products/keep.jpg']);

        $this->artisan('products:delete-inactive', ['--commit' => true])->assertSuccessful();

        Storage::disk('public')->assertExists('products/keep.jpg');
    }

    public function test_it_copes_with_nothing_to_do(): void
    {
        Product::factory()->withCategory()->create(['is_active' => true]);

        $this->artisan('products:delete-inactive')
            ->expectsOutputToContain('No inactive products')
            ->assertSuccessful();
    }
}
