<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Test Buyer',
            'phone' => '01700000000',
            'address' => '12 Test Road, Dhaka',
            'delivery_location' => 'inside_dhaka',
            'payment_method' => 'cod',
            'terms_accepted' => 'on',
        ], $overrides);
    }

    public function test_cart_subtotal_equals_the_charged_order_subtotal_for_a_discounted_variant(): void
    {
        // The exact shape of the old bug: an item whose cart price and order
        // price were derived by two different rules.
        $product = Product::factory()->withCategory()->create([
            'price' => 1000,
            'offer_price' => 600,
            'offer_starts_at' => now()->subHour(),
            'offer_ends_at' => now()->addHour(),
        ]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id, 'price' => 450, 'sale_price' => 350, 'quantity' => 5,
        ]);

        $cart = app(CartService::class);
        $cart->add($product, $variant, 2);
        $cartSubtotal = $cart->summary()->subtotal;

        $this->post(route('checkout.store'), $this->validPayload());

        $order = Order::latest('id')->first();

        $this->assertSame(700.0, $cartSubtotal);
        $this->assertSame(700.0, (float) $order->subtotal);
    }

    public function test_buying_a_variant_decrements_that_variant_not_the_parent(): void
    {
        $product = Product::factory()->withCategory()->create(['price' => 0, 'quantity' => 100]);
        $blue = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 450, 'quantity' => 5]);
        $red = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 450, 'quantity' => 5]);

        app(CartService::class)->add($product, $blue, 2);
        $this->post(route('checkout.store'), $this->validPayload());

        $this->assertSame(3, $blue->refresh()->quantity);
        $this->assertSame(5, $red->refresh()->quantity);
        $this->assertSame(100, $product->refresh()->quantity, 'parent stock must not move for a variant sale');
    }

    public function test_the_order_item_records_the_variant(): void
    {
        $product = Product::factory()->withCategory()->create(['price' => 0]);
        $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'name' => 'Blue', 'price' => 450, 'quantity' => 5]);

        app(CartService::class)->add($product, $variant, 1);
        $this->post(route('checkout.store'), $this->validPayload());

        $item = Order::latest('id')->first()->items()->first();

        $this->assertSame($variant->id, $item->product_variant_id);
        $this->assertSame(450.0, (float) $item->price);
        $this->assertStringContainsString('Blue', $item->product_name);
    }

    public function test_a_variant_that_sold_out_while_in_the_cart_fails_checkout(): void
    {
        $product = Product::factory()->withCategory()->create(['price' => 0]);
        $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 450, 'quantity' => 2]);

        app(CartService::class)->add($product, $variant, 2);

        // Someone else buys them in between.
        $variant->update(['quantity' => 0]);

        $this->post(route('checkout.store'), $this->validPayload())
            ->assertSessionHas('error');

        $this->assertSame(0, Order::count());
    }

    public function test_a_bookable_variant_product_charges_the_correct_booking_amount(): void
    {
        // Silently wrong before: the fee was skipped whenever a variant existed.
        $product = Product::factory()->withCategory()->create(['price' => 0, 'booking_fee' => 200]);
        $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 450, 'quantity' => 5]);

        app(CartService::class)->add($product, $variant, 3);

        $this->post(route('checkout.store'), $this->validPayload(['payment_method' => 'bkash', 'transaction_number' => 'TRX123', 'sending_number' => '01700000000']));

        $order = Order::latest('id')->first();

        $this->assertTrue((bool) $order->is_preorder_booking);
        $this->assertSame(600.0, (float) $order->booking_amount);
        $this->assertSame(1350.0, (float) $order->subtotal);
    }

    public function test_cod_is_rejected_for_a_booking_order(): void
    {
        $product = Product::factory()->withCategory()->create(['price' => 1000, 'quantity' => 5, 'booking_fee' => 200]);
        app(CartService::class)->add($product, null, 1);

        $this->post(route('checkout.store'), $this->validPayload(['payment_method' => 'cod']))
            ->assertSessionHas('error');

        $this->assertSame(0, Order::count());
    }

    public function test_a_plain_product_still_decrements_its_own_stock(): void
    {
        $product = Product::factory()->withCategory()->create(['price' => 1000, 'quantity' => 10]);
        app(CartService::class)->add($product, null, 3);

        $this->post(route('checkout.store'), $this->validPayload());

        $this->assertSame(7, $product->refresh()->quantity);
    }
}
