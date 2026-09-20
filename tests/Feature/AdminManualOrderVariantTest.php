<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Manual orders placed by an admin, for products sold by variant.
 *
 * Before this, orderCreate() filtered on "quantity > 0" and orderStore()
 * checked products.quantity - both 0 by design on a merged product - so an
 * admin could not put any of the 13 merged products on an order at all. The
 * controller also never recorded product_variant_id, so stock came off the
 * wrong row.
 */
class AdminManualOrderVariantTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['email' => 'ifti3061@gmail.com']);
    }

    /** A merged product: no stock or price of its own, two colours that have both. */
    private function variantProduct(): Product
    {
        $product = Product::factory()->withCategory()->create([
            'name' => 'CSGO Knife',
            'price' => 0,
            'quantity' => 0,
            'is_active' => true,
        ]);

        ProductVariant::factory()->create([
            'product_id' => $product->id,
            'name' => 'Blue Shadow',
            'price' => 1500,
            'quantity' => 4,
            'is_active' => true,
        ]);

        ProductVariant::factory()->create([
            'product_id' => $product->id,
            'name' => 'Gradient Finish',
            'price' => 950,
            'quantity' => 2,
            'is_active' => true,
        ]);

        return $product->refresh();
    }

    private function orderPayload(array $products): array
    {
        // The optional fields are sent empty rather than omitted, because that
        // is what the real form posts - orderStore reads $validated['email']
        // directly and an absent key is a fatal, which is its own latent bug.
        return [
            'name' => 'Walk-in Buyer',
            'phone' => '01700000000',
            'address' => 'Dhaka',
            'email' => '',
            'payment_method' => 'cod',
            'transaction_number' => '',
            'sending_number' => '',
            'coupon_code' => '',
            'notes' => '',
            'status' => 'pending',
            'delivery_location' => 'inside_dhaka',
            'products' => $products,
        ];
    }

    public function test_a_variant_product_is_offered_once_per_option(): void
    {
        $product = $this->variantProduct();

        $response = $this->actingAs($this->admin())->get(route('admin.orders.create'));

        $response->assertOk();
        // Listed per option, not once for the product with a 0 price.
        $response->assertSee('Blue Shadow', false);
        $response->assertSee('Gradient Finish', false);
        $response->assertSee('data-variant-id="' . $product->variants->first()->id . '"', false);
    }

    public function test_a_fully_sold_out_variant_product_is_not_offered(): void
    {
        $product = $this->variantProduct();
        $product->variants()->update(['quantity' => 0]);

        $response = $this->actingAs($this->admin())->get(route('admin.orders.create'));

        $response->assertOk();
        $response->assertDontSee('Blue Shadow', false);
    }

    public function test_an_order_records_the_chosen_variant_and_its_price(): void
    {
        $product = $this->variantProduct();
        $gradient = $product->variants->firstWhere('name', 'Gradient Finish');

        $response = $this->actingAs($this->admin())->post(
            route('admin.orders.store'),
            $this->orderPayload([
                ['id' => $product->id, 'variant_id' => $gradient->id, 'quantity' => 2],
            ])
        );

        $response->assertRedirect(route('admin.orders'));

        $item = OrderItem::firstOrFail();
        $this->assertSame($product->id, $item->product_id);
        $this->assertSame($gradient->id, $item->product_variant_id);
        // The variant's price, not the product's 0.
        $this->assertSame('950.00', (string) $item->price);
        $this->assertStringContainsString('Gradient Finish', $item->product_name);
    }

    public function test_stock_comes_off_the_variant_not_the_product(): void
    {
        $product = $this->variantProduct();
        $gradient = $product->variants->firstWhere('name', 'Gradient Finish');

        $this->actingAs($this->admin())->post(
            route('admin.orders.store'),
            $this->orderPayload([
                ['id' => $product->id, 'variant_id' => $gradient->id, 'quantity' => 2],
            ])
        );

        $this->assertSame(0, (int) $gradient->refresh()->quantity);
        // Untouched: the product's own column is not where stock lives.
        $this->assertSame(0, (int) $product->refresh()->quantity);
        $this->assertSame(4, (int) $product->variants->firstWhere('name', 'Blue Shadow')->quantity);
    }

    public function test_an_order_cannot_exceed_the_variants_stock(): void
    {
        $product = $this->variantProduct();
        $gradient = $product->variants->firstWhere('name', 'Gradient Finish');

        $response = $this->actingAs($this->admin())->post(
            route('admin.orders.store'),
            $this->orderPayload([
                ['id' => $product->id, 'variant_id' => $gradient->id, 'quantity' => 3],
            ])
        );

        $response->assertSessionHas('error');
        $this->assertSame(0, Order::count());
        $this->assertSame(2, (int) $gradient->refresh()->quantity);
    }

    public function test_a_variant_product_cannot_be_ordered_without_an_option(): void
    {
        $product = $this->variantProduct();

        $response = $this->actingAs($this->admin())->post(
            route('admin.orders.store'),
            $this->orderPayload([
                ['id' => $product->id, 'quantity' => 1],
            ])
        );

        $response->assertSessionHas('error');
        $this->assertSame(0, Order::count());
    }

    public function test_a_variant_of_another_product_is_rejected(): void
    {
        $product = $this->variantProduct();
        $other = Product::factory()->withCategory()->create(['quantity' => 5]);
        $foreign = ProductVariant::factory()->create(['product_id' => $other->id, 'quantity' => 5]);

        $response = $this->actingAs($this->admin())->post(
            route('admin.orders.store'),
            $this->orderPayload([
                ['id' => $product->id, 'variant_id' => $foreign->id, 'quantity' => 1],
            ])
        );

        $response->assertSessionHas('error');
        $this->assertSame(0, Order::count());
    }

    /**
     * Two colours of one knife are two lines. Keying order items by product
     * alone collapsed them into one, losing an item and its stock movement.
     */
    public function test_two_variants_of_one_product_are_two_separate_lines(): void
    {
        $product = $this->variantProduct();
        $blue = $product->variants->firstWhere('name', 'Blue Shadow');
        $gradient = $product->variants->firstWhere('name', 'Gradient Finish');

        $this->actingAs($this->admin())->post(
            route('admin.orders.store'),
            $this->orderPayload([
                ['id' => $product->id, 'variant_id' => $blue->id, 'quantity' => 1],
                ['id' => $product->id, 'variant_id' => $gradient->id, 'quantity' => 1],
            ])
        );

        $this->assertSame(2, OrderItem::count());
        $this->assertSame(3, (int) $blue->refresh()->quantity);
        $this->assertSame(1, (int) $gradient->refresh()->quantity);
    }

    public function test_a_plain_product_still_works(): void
    {
        $product = Product::factory()->withCategory()->create([
            'name' => 'CSGO HawkBill Krambit',
            'price' => 950,
            'quantity' => 5,
            'is_active' => true,
        ]);

        $this->actingAs($this->admin())->post(
            route('admin.orders.store'),
            $this->orderPayload([
                ['id' => $product->id, 'quantity' => 2],
            ])
        );

        $item = OrderItem::firstOrFail();
        $this->assertNull($item->product_variant_id);
        $this->assertSame('950.00', (string) $item->price);
        $this->assertSame(3, (int) $product->refresh()->quantity);
    }
}
