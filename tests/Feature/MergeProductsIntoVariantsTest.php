<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MergeProductsIntoVariantsTest extends TestCase
{
    use RefreshDatabase;

    private function family(): array
    {
        return [
            Product::factory()->withCategory()->create(['name' => 'RGX DAGGER - Red', 'quantity' => 3, 'price' => 1200]),
            Product::factory()->create(['name' => 'RGX DAGGER - BLUE', 'quantity' => 5, 'price' => 1200]),
            Product::factory()->create(['name' => 'RGX DAGGER - GREEN', 'quantity' => 2, 'price' => 1300]),
        ];
    }

    public function test_dry_run_changes_nothing(): void
    {
        [$red, $blue, $green] = $this->family();

        $this->artisan('products:merge-variants', [
            'name' => 'RGX Dagger',
            '--ids' => "{$red->id},{$blue->id},{$green->id}",
        ])->assertSuccessful();

        $this->assertTrue($red->refresh()->is_active, 'dry run must not deactivate sources');
        $this->assertSame(0, Product::where('name', 'RGX Dagger')->count());
    }

    public function test_merge_conserves_total_stock_exactly(): void
    {
        [$red, $blue, $green] = $this->family();
        $before = $red->quantity + $blue->quantity + $green->quantity;

        $this->artisan('products:merge-variants', [
            'name' => 'RGX Dagger',
            '--ids' => "{$red->id},{$blue->id},{$green->id}",
            '--commit' => true,
        ])->assertSuccessful();

        $merged = Product::where('name', 'RGX Dagger')->firstOrFail();

        $this->assertSame(10, $before);
        $this->assertSame($before, (int) $merged->variants()->sum('quantity'));
        $this->assertSame($before, $merged->availableStock());
    }

    public function test_each_variant_keeps_its_own_source_stock(): void
    {
        [$red, $blue, $green] = $this->family();

        $this->artisan('products:merge-variants', [
            'name' => 'RGX Dagger',
            '--ids' => "{$red->id},{$blue->id},{$green->id}",
            '--commit' => true,
        ])->assertSuccessful();

        $merged = Product::where('name', 'RGX Dagger')->firstOrFail();
        $byName = $merged->variants->keyBy('name');

        // Per-colour stock is not lost or averaged - it lands on its own variant.
        $this->assertSame(3, $byName['Red']->quantity);
        $this->assertSame(5, $byName['BLUE']->quantity);
        $this->assertSame(2, $byName['GREEN']->quantity);
    }

    public function test_variant_names_have_the_parent_prefix_stripped(): void
    {
        [$red, $blue, $green] = $this->family();

        $this->artisan('products:merge-variants', [
            'name' => 'RGX Dagger',
            '--ids' => "{$red->id},{$blue->id},{$green->id}",
            '--commit' => true,
        ])->assertSuccessful();

        $names = Product::where('name', 'RGX Dagger')->firstOrFail()->variants->pluck('name')->all();

        $this->assertSame(['Red', 'BLUE', 'GREEN'], $names);
    }

    public function test_sources_are_deactivated_never_deleted(): void
    {
        [$red, $blue, $green] = $this->family();

        $this->artisan('products:merge-variants', [
            'name' => 'RGX Dagger',
            '--ids' => "{$red->id},{$blue->id},{$green->id}",
            '--commit' => true,
        ])->assertSuccessful();

        // order_items.product_id is ON DELETE CASCADE, so deleting a source would
        // destroy its order history. They must survive, just hidden.
        $this->assertDatabaseHas('products', ['id' => $red->id]);
        $this->assertFalse($red->refresh()->is_active);
        $this->assertFalse($blue->refresh()->is_active);
    }

    public function test_past_order_history_survives_the_merge(): void
    {
        [$red, $blue, $green] = $this->family();

        $order = Order::create([
            'name' => 'Buyer', 'phone' => '01700000000', 'address' => 'Dhaka',
            'delivery_location' => 'inside_dhaka', 'subtotal' => 1200, 'total' => 1280,
            'payment_method' => 'cod', 'status' => 'delivered', 'terms_accepted' => true,
        ]);
        OrderItem::create([
            'order_id' => $order->id, 'product_id' => $red->id, 'product_name' => $red->name,
            'price' => 1200, 'quantity' => 1, 'subtotal' => 1200,
        ]);

        $this->artisan('products:merge-variants', [
            'name' => 'RGX Dagger',
            '--ids' => "{$red->id},{$blue->id},{$green->id}",
            '--commit' => true,
        ])->assertSuccessful();

        $this->assertSame(1, $order->refresh()->items()->count());
        $this->assertSame(1200.0, (float) $order->items()->first()->price);
    }

    public function test_images_carry_across_to_the_variant(): void
    {
        [$red, $blue, $green] = $this->family();
        ProductImage::create(['product_id' => $red->id, 'path' => 'products/red-one.jpg', 'sort_order' => 0]);
        ProductImage::create(['product_id' => $red->id, 'path' => 'products/red-two.jpg', 'sort_order' => 1]);

        $this->artisan('products:merge-variants', [
            'name' => 'RGX Dagger',
            '--ids' => "{$red->id},{$blue->id},{$green->id}",
            '--commit' => true,
        ])->assertSuccessful();

        $variant = Product::where('name', 'RGX Dagger')->firstOrFail()->variants->firstWhere('name', 'Red');

        $this->assertSame(
            ['products/red-one.jpg', 'products/red-two.jpg'],
            $variant->images->pluck('path')->all()
        );
    }

    public function test_undo_restores_the_original_products(): void
    {
        [$red, $blue, $green] = $this->family();

        $this->artisan('products:merge-variants', [
            'name' => 'RGX Dagger',
            '--ids' => "{$red->id},{$blue->id},{$green->id}",
            '--commit' => true,
        ])->assertSuccessful();

        $merged = Product::where('name', 'RGX Dagger')->firstOrFail();

        $this->artisan('products:unmerge-variants', [
            'merged' => $merged->id,
            '--ids' => "{$red->id},{$blue->id},{$green->id}",
            '--commit' => true,
        ])->assertSuccessful();

        $this->assertTrue($red->refresh()->is_active);
        $this->assertTrue($blue->refresh()->is_active);
        $this->assertSame(3, $red->quantity, 'stock must be untouched by the round trip');
        $this->assertNull(Product::find($merged->id));
    }

    public function test_it_refuses_to_nest_a_product_that_already_has_variants(): void
    {
        [$red, $blue, $green] = $this->family();
        \App\Models\ProductVariant::factory()->create(['product_id' => $red->id]);

        $this->artisan('products:merge-variants', [
            'name' => 'RGX Dagger',
            '--ids' => "{$red->id},{$blue->id},{$green->id}",
            '--commit' => true,
        ])->assertFailed();

        $this->assertSame(0, Product::where('name', 'RGX Dagger')->count());
    }
}
