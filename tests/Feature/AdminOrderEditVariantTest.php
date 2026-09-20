<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Editing an order that has variant lines.
 *
 * orderUpdate keyed items by product_id alone, so two colours of one knife
 * collapsed into a single line - one of them silently vanished and its stock
 * was never returned. It also moved stock on the product row rather than the
 * variant, so the numbers drifted on every edit.
 */
class AdminOrderEditVariantTest extends TestCase
{
    use RefreshDatabase;

    private ?User $admin = null;

    /** Memoised: the admin is identified by a unique email, so one per test. */
    private function admin(): User
    {
        return $this->admin ??= User::factory()->create(['email' => 'ifti3061@gmail.com']);
    }

    private function variantProduct(): Product
    {
        $product = Product::factory()->withCategory()->create([
            'name' => 'CSGO Knife', 'price' => 0, 'quantity' => 0, 'is_active' => true,
        ]);
        ProductVariant::factory()->create([
            'product_id' => $product->id, 'name' => 'Blue Shadow',
            'price' => 1500, 'quantity' => 4, 'is_active' => true,
        ]);
        ProductVariant::factory()->create([
            'product_id' => $product->id, 'name' => 'Gradient Finish',
            'price' => 950, 'quantity' => 2, 'is_active' => true,
        ]);

        return $product->refresh();
    }

    /**
     * Optional fields are sent empty rather than omitted, matching what the
     * real form posts - the controller reads them out of $validated directly.
     */
    private function payload(array $products): array
    {
        return [
            'name' => 'Walk-in Buyer', 'phone' => '01700000000', 'address' => 'Dhaka',
            'email' => '', 'payment_method' => 'cod', 'transaction_number' => '',
            'sending_number' => '', 'coupon_code' => '', 'notes' => '', 'tracking_link' => '',
            'status' => 'confirmed', 'delivery_location' => 'inside_dhaka',
            'products' => $products,
        ];
    }

    private function placeOrder(array $lines): Order
    {
        $payload = $this->payload($lines);
        unset($payload['tracking_link']);

        $this->actingAs($this->admin())->post(route('admin.orders.store'), $payload);

        return Order::firstOrFail();
    }

    public function test_two_variant_lines_survive_an_edit(): void
    {
        $product = $this->variantProduct();
        $blue = $product->variants->firstWhere('name', 'Blue Shadow');
        $gradient = $product->variants->firstWhere('name', 'Gradient Finish');

        $order = $this->placeOrder([
            ['id' => $product->id, 'variant_id' => $blue->id, 'quantity' => 1],
            ['id' => $product->id, 'variant_id' => $gradient->id, 'quantity' => 1],
        ]);

        $this->assertSame(2, $order->items()->count());

        // Resubmit both lines unchanged.
        $this->actingAs($this->admin())->put(
            route('admin.orders.update', $order),
            $this->payload([
                ['id' => $product->id, 'variant_id' => $blue->id, 'quantity' => 1],
                ['id' => $product->id, 'variant_id' => $gradient->id, 'quantity' => 1],
            ])
        );

        $this->assertSame(2, $order->refresh()->items()->count());
        // Unchanged quantities must not move stock.
        $this->assertSame(3, (int) $blue->refresh()->quantity);
        $this->assertSame(1, (int) $gradient->refresh()->quantity);
    }

    public function test_raising_a_variant_line_takes_stock_from_that_variant(): void
    {
        $product = $this->variantProduct();
        $gradient = $product->variants->firstWhere('name', 'Gradient Finish');
        $blue = $product->variants->firstWhere('name', 'Blue Shadow');

        $order = $this->placeOrder([
            ['id' => $product->id, 'variant_id' => $gradient->id, 'quantity' => 1],
        ]);
        $this->assertSame(1, (int) $gradient->refresh()->quantity);

        $this->actingAs($this->admin())->put(
            route('admin.orders.update', $order),
            $this->payload([
                ['id' => $product->id, 'variant_id' => $gradient->id, 'quantity' => 2],
            ])
        );

        $this->assertSame(0, (int) $gradient->refresh()->quantity);
        $this->assertSame(4, (int) $blue->refresh()->quantity);
        $this->assertSame(0, (int) $product->refresh()->quantity);
    }

    public function test_removing_a_variant_line_returns_stock_to_that_variant(): void
    {
        $product = $this->variantProduct();
        $blue = $product->variants->firstWhere('name', 'Blue Shadow');
        $gradient = $product->variants->firstWhere('name', 'Gradient Finish');

        $order = $this->placeOrder([
            ['id' => $product->id, 'variant_id' => $blue->id, 'quantity' => 2],
            ['id' => $product->id, 'variant_id' => $gradient->id, 'quantity' => 1],
        ]);

        $this->actingAs($this->admin())->put(
            route('admin.orders.update', $order),
            $this->payload([
                ['id' => $product->id, 'variant_id' => $blue->id, 'quantity' => 2],
            ])
        );

        $this->assertSame(1, $order->refresh()->items()->count());
        // The dropped unit goes back to Gradient Finish, not to the product.
        $this->assertSame(2, (int) $gradient->refresh()->quantity);
        $this->assertSame(2, (int) $blue->refresh()->quantity);
        $this->assertSame(0, (int) $product->refresh()->quantity);
    }

    public function test_an_edit_cannot_exceed_the_variants_stock(): void
    {
        $product = $this->variantProduct();
        $gradient = $product->variants->firstWhere('name', 'Gradient Finish');

        $order = $this->placeOrder([
            ['id' => $product->id, 'variant_id' => $gradient->id, 'quantity' => 1],
        ]);

        $response = $this->actingAs($this->admin())->put(
            route('admin.orders.update', $order),
            $this->payload([
                ['id' => $product->id, 'variant_id' => $gradient->id, 'quantity' => 5],
            ])
        );

        $response->assertSessionHas('error');
        // Rolled back: the line and the stock are as they were.
        $this->assertSame(1, (int) $gradient->refresh()->quantity);
        $this->assertSame(1, $order->refresh()->items()->count());
    }

    public function test_the_edit_form_preselects_the_right_variant(): void
    {
        $product = $this->variantProduct();
        $gradient = $product->variants->firstWhere('name', 'Gradient Finish');

        $order = $this->placeOrder([
            ['id' => $product->id, 'variant_id' => $gradient->id, 'quantity' => 1],
        ]);

        $response = $this->actingAs($this->admin())->get(route('admin.orders.edit', $order));

        $response->assertOk();
        $response->assertSee('data-variant-id="' . $gradient->id . '"', false);
        // Sold-out options stay listed so an existing line remains editable.
        $response->assertSee('Blue Shadow', false);
    }
}
