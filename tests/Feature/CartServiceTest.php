<?php

namespace Tests\Feature;

use App\Exceptions\CartException;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartServiceTest extends TestCase
{
    use RefreshDatabase;

    private CartService $cart;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cart = app(CartService::class);
        $this->cart->clear();
    }

    public function test_adding_a_plain_product_records_one_line(): void
    {
        $product = Product::factory()->withCategory()->create(['price' => 1000, 'quantity' => 5]);

        $this->cart->add($product, null, 2);

        $lines = $this->cart->lines();
        $this->assertCount(1, $lines);
        $this->assertSame(1000.0, $lines->first()->unitPrice);
        $this->assertSame(2000.0, $lines->first()->lineTotal());
    }

    public function test_adding_a_variant_records_the_variant_price_and_name(): void
    {
        $product = Product::factory()->withCategory()->create(['name' => 'RGX Butterfly', 'price' => 0]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'name' => 'Blue',
            'price' => 450,
            'quantity' => 10,
        ]);

        $this->cart->add($product, $variant, 1);

        $line = $this->cart->lines()->first();
        $this->assertSame(450.0, $line->unitPrice);
        $this->assertSame($variant->id, $line->variant->id);
        $this->assertStringContainsString('Blue', $line->name);
    }

    public function test_two_variants_of_one_product_are_separate_lines(): void
    {
        $product = Product::factory()->withCategory()->create(['price' => 0]);
        $blue = ProductVariant::factory()->create(['product_id' => $product->id, 'name' => 'Blue', 'price' => 450, 'quantity' => 5]);
        $red = ProductVariant::factory()->create(['product_id' => $product->id, 'name' => 'Red', 'price' => 500, 'quantity' => 5]);

        $this->cart->add($product, $blue, 1);
        $this->cart->add($product, $red, 1);

        $this->assertCount(2, $this->cart->lines());
        $this->assertSame(950.0, $this->cart->summary()->subtotal);
    }

    public function test_adding_the_same_variant_twice_increases_quantity(): void
    {
        $product = Product::factory()->withCategory()->create(['price' => 0]);
        $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 450, 'quantity' => 10]);

        $this->cart->add($product, $variant, 1);
        $this->cart->add($product, $variant, 2);

        $this->assertCount(1, $this->cart->lines());
        $this->assertSame(3, $this->cart->lines()->first()->quantity);
    }

    public function test_cannot_add_more_than_the_variant_has_in_stock(): void
    {
        $product = Product::factory()->withCategory()->create(['price' => 0]);
        $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 450, 'quantity' => 3]);

        $this->expectException(CartException::class);
        $this->cart->add($product, $variant, 4);
    }

    public function test_cannot_add_a_sold_out_variant(): void
    {
        $product = Product::factory()->withCategory()->create(['price' => 0]);
        $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 450, 'quantity' => 0]);

        $this->expectException(CartException::class);
        $this->cart->add($product, $variant, 1);
    }

    public function test_cannot_add_an_inactive_variant(): void
    {
        $product = Product::factory()->withCategory()->create(['price' => 0]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id, 'price' => 450, 'quantity' => 10, 'is_active' => false,
        ]);

        $this->expectException(CartException::class);
        $this->cart->add($product, $variant, 1);
    }

    public function test_cannot_add_an_upcoming_product(): void
    {
        $product = Product::factory()->withCategory()->create([
            'availability' => Product::AVAILABILITY_UPCOMING,
            'price' => 1000,
            'quantity' => 5,
        ]);

        $this->expectException(CartException::class);
        $this->cart->add($product, null, 1);
    }

    public function test_cannot_add_a_variant_belonging_to_another_product(): void
    {
        $product = Product::factory()->withCategory()->create(['price' => 0]);
        $other = Product::factory()->withCategory()->create(['price' => 0]);
        $foreign = ProductVariant::factory()->create(['product_id' => $other->id, 'price' => 450, 'quantity' => 5]);

        $this->expectException(CartException::class);
        $this->cart->add($product, $foreign, 1);
    }

    public function test_booking_fee_is_carried_separately_not_subtracted_from_price(): void
    {
        $product = Product::factory()->withCategory()->create([
            'price' => 1000, 'quantity' => 5, 'booking_fee' => 200,
        ]);

        $this->cart->add($product, null, 2);
        $summary = $this->cart->summary();

        // The old code subtracted 200 from the item price. It is now separate,
        // so the subtotal is the true goods value and the fee is its own number.
        $this->assertSame(2000.0, $summary->subtotal);
        $this->assertSame(400.0, $summary->bookingTotal);
        $this->assertTrue($summary->hasBookingItems);
    }

    public function test_booking_fee_applies_to_a_variant_product(): void
    {
        $product = Product::factory()->withCategory()->create(['price' => 0, 'booking_fee' => 200]);
        $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 450, 'quantity' => 5]);

        $this->cart->add($product, $variant, 1);
        $summary = $this->cart->summary();

        // Silently wrong before: the old code skipped the fee whenever a
        // variant was selected.
        $this->assertSame(450.0, $summary->subtotal);
        $this->assertSame(200.0, $summary->bookingTotal);
    }

    public function test_mixing_booking_and_regular_items_is_flagged(): void
    {
        $bookable = Product::factory()->withCategory()->create(['price' => 1000, 'quantity' => 5, 'booking_fee' => 200]);
        $regular = Product::factory()->withCategory()->create(['price' => 500, 'quantity' => 5, 'booking_fee' => null]);

        $this->cart->add($bookable, null, 1);

        $this->expectException(CartException::class);
        $this->cart->add($regular, null, 1);
    }

    public function test_update_changes_quantity_and_respects_stock(): void
    {
        $product = Product::factory()->withCategory()->create(['price' => 1000, 'quantity' => 5]);
        $this->cart->add($product, null, 1);
        $id = $this->cart->lines()->first()->id;

        $this->cart->update($id, 3);
        $this->assertSame(3, $this->cart->lines()->first()->quantity);

        $this->expectException(CartException::class);
        $this->cart->update($id, 99);
    }

    public function test_summary_counts_units_not_lines(): void
    {
        $a = Product::factory()->withCategory()->create(['price' => 100, 'quantity' => 10]);
        $b = Product::factory()->withCategory()->create(['price' => 200, 'quantity' => 10]);

        $this->cart->add($a, null, 2);
        $this->cart->add($b, null, 3);

        $this->assertSame(5, $this->cart->summary()->itemCount);
        $this->assertSame(800.0, $this->cart->summary()->subtotal);
    }

    public function test_a_legacy_session_cart_without_attributes_still_resolves(): void
    {
        $product = Product::factory()->withCategory()->create(['price' => 1000, 'quantity' => 5]);
        $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 450, 'quantity' => 5]);

        // Simulate a cart left in a visitor's session before this change:
        // composite key, no product_id/variant_id attributes.
        \Cart::add([
            'id' => $product->id . '_' . $variant->id,
            'name' => 'Legacy item',
            'price' => 450,
            'quantity' => 1,
            'attributes' => ['image' => null],
        ]);

        $lines = $this->cart->lines();

        $this->assertCount(1, $lines);
        $this->assertSame($product->id, $lines->first()->product->id);
        $this->assertSame($variant->id, $lines->first()->variant->id);
    }

    public function test_a_line_whose_product_was_deleted_is_dropped(): void
    {
        $product = Product::factory()->withCategory()->create(['price' => 1000, 'quantity' => 5]);
        $this->cart->add($product, null, 1);

        $product->delete();

        $this->assertCount(0, $this->cart->lines());
    }

    public function test_a_line_whose_variant_belongs_to_another_product_is_dropped(): void
    {
        $productA = Product::factory()->withCategory()->create(['price' => 0]);
        $productB = Product::factory()->withCategory()->create(['price' => 0]);
        $variantOfB = ProductVariant::factory()->create(['product_id' => $productB->id, 'price' => 450, 'quantity' => 5]);

        // Simulate a stale/legacy composite key that pairs product A with a
        // variant that actually belongs to product B - add() would reject
        // this pairing, but lines() must also refuse to rehydrate it.
        \Cart::add([
            'id' => $productA->id . '_' . $variantOfB->id,
            'name' => 'Mismatched item',
            'price' => 450,
            'quantity' => 1,
            'attributes' => ['image' => null],
        ]);

        $lines = $this->cart->lines();

        $this->assertCount(0, $lines);
    }
}
