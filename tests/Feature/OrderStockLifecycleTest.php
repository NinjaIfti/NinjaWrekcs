<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * When an order takes stock and when it gives it back.
 *
 * Stock used to come off the moment an order was placed, so a pending order
 * nobody had accepted was already reducing what could be sold, and cancelling
 * a variant order returned the units to the product's dead quantity column
 * instead of the variant.
 *
 * The rule is now: confirmed, processing, shipped and delivered hold stock;
 * pending and cancelled do not. Because the status is the only source of
 * truth, transitions are idempotent and cannot double-deduct.
 */
class OrderStockLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private ?User $admin = null;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    private function admin(): User
    {
        return $this->admin ??= User::factory()->create(['email' => 'ifti3061@gmail.com']);
    }

    private function setStatus(Order $order, string $status): void
    {
        $this->actingAs($this->admin())
            ->put(route('admin.orders.update-status', $order), ['status' => $status])
            ->assertRedirect();
    }

    /** A variant with 5 units, and an order for 2 of them sitting at pending. */
    private function pendingVariantOrder(): array
    {
        $product = Product::factory()->withCategory()->create(['price' => 0, 'quantity' => 0]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id, 'price' => 450, 'quantity' => 5, 'is_active' => true,
        ]);

        app(CartService::class)->add($product, $variant, 2);
        $this->post(route('checkout.store'), [
            'name' => 'Test Buyer',
            'phone' => '01700000000',
            'address' => '12 Test Road, Dhaka',
            'delivery_location' => 'inside_dhaka',
            'payment_method' => 'cod',
            'terms_accepted' => 'on',
        ]);

        return [$product, $variant, Order::latest('id')->firstOrFail()];
    }

    public function test_a_pending_order_holds_no_stock(): void
    {
        [, $variant] = $this->pendingVariantOrder();

        $this->assertSame(5, (int) $variant->refresh()->quantity);
    }

    public function test_confirming_takes_the_stock_off_the_variant(): void
    {
        [$product, $variant, $order] = $this->pendingVariantOrder();

        $this->setStatus($order, 'confirmed');

        $this->assertSame(3, (int) $variant->refresh()->quantity);
        $this->assertSame(0, (int) $product->refresh()->quantity, 'the parent column must not move');
    }

    public function test_cancelling_a_confirmed_order_returns_stock_to_the_variant(): void
    {
        [$product, $variant, $order] = $this->pendingVariantOrder();

        $this->setStatus($order, 'confirmed');
        $this->setStatus($order->refresh(), 'cancelled');

        $this->assertSame(5, (int) $variant->refresh()->quantity, 'units must come back to the variant');
        $this->assertSame(0, (int) $product->refresh()->quantity, 'and not to the parent column');
    }

    public function test_cancelling_a_pending_order_changes_nothing(): void
    {
        [, $variant, $order] = $this->pendingVariantOrder();

        $this->setStatus($order, 'cancelled');

        $this->assertSame(5, (int) $variant->refresh()->quantity);
    }

    /**
     * The hazard this design exists to avoid: moving between two statuses that
     * both hold stock must not take it twice.
     */
    public function test_moving_between_holding_statuses_does_not_deduct_twice(): void
    {
        [, $variant, $order] = $this->pendingVariantOrder();

        $this->setStatus($order, 'confirmed');
        $this->assertSame(3, (int) $variant->refresh()->quantity);

        foreach (['processing', 'shipped', 'delivered'] as $status) {
            $this->setStatus($order->refresh(), $status);
            $this->assertSame(3, (int) $variant->refresh()->quantity, "moving to {$status} moved stock");
        }
    }

    public function test_cancelling_twice_does_not_restore_twice(): void
    {
        [, $variant, $order] = $this->pendingVariantOrder();

        $this->setStatus($order, 'confirmed');
        $this->setStatus($order->refresh(), 'cancelled');
        $this->setStatus($order->refresh(), 'cancelled');

        $this->assertSame(5, (int) $variant->refresh()->quantity);
    }

    public function test_reconfirming_a_cancelled_order_takes_the_stock_again(): void
    {
        [, $variant, $order] = $this->pendingVariantOrder();

        $this->setStatus($order, 'confirmed');
        $this->setStatus($order->refresh(), 'cancelled');
        $this->assertSame(5, (int) $variant->refresh()->quantity);

        $this->setStatus($order->refresh(), 'confirmed');
        $this->assertSame(3, (int) $variant->refresh()->quantity);
    }

    public function test_going_back_to_pending_returns_the_stock(): void
    {
        [, $variant, $order] = $this->pendingVariantOrder();

        $this->setStatus($order, 'confirmed');
        $this->setStatus($order->refresh(), 'pending');

        $this->assertSame(5, (int) $variant->refresh()->quantity);
    }

    public function test_a_plain_product_follows_the_same_rule(): void
    {
        $product = Product::factory()->withCategory()->create(['price' => 1000, 'quantity' => 10]);

        app(CartService::class)->add($product, null, 3);
        $this->post(route('checkout.store'), [
            'name' => 'Test Buyer',
            'phone' => '01700000000',
            'address' => '12 Test Road, Dhaka',
            'delivery_location' => 'inside_dhaka',
            'payment_method' => 'cod',
            'terms_accepted' => 'on',
        ]);

        $order = Order::latest('id')->firstOrFail();
        $this->assertSame(10, (int) $product->refresh()->quantity);

        $this->setStatus($order, 'confirmed');
        $this->assertSame(7, (int) $product->refresh()->quantity);

        $this->setStatus($order->refresh(), 'cancelled');
        $this->assertSame(10, (int) $product->refresh()->quantity);
    }
}
