<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\PricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PricingServiceTest extends TestCase
{
    use RefreshDatabase;

    private PricingService $pricing;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pricing = new PricingService();
    }

    public function test_plain_product_uses_its_price(): void
    {
        $product = Product::factory()->withCategory()->create(['price' => 1000]);

        $this->assertSame(1000.0, $this->pricing->priceFor($product));
    }

    public function test_sale_price_beats_price(): void
    {
        $product = Product::factory()->withCategory()->create(['price' => 1000, 'sale_price' => 800]);

        $this->assertSame(800.0, $this->pricing->priceFor($product));
    }

    public function test_an_open_offer_beats_sale_price(): void
    {
        $product = Product::factory()->withCategory()->create([
            'price' => 1000,
            'sale_price' => 800,
            'offer_price' => 600,
            'offer_starts_at' => now()->subHour(),
            'offer_ends_at' => now()->addHour(),
        ]);

        $this->assertSame(600.0, $this->pricing->priceFor($product));
    }

    public function test_a_closed_offer_is_ignored(): void
    {
        $product = Product::factory()->withCategory()->create([
            'price' => 1000,
            'sale_price' => 800,
            'offer_price' => 600,
            'offer_starts_at' => now()->subDays(5),
            'offer_ends_at' => now()->subDay(),
        ]);

        $this->assertSame(800.0, $this->pricing->priceFor($product));
    }

    public function test_a_variant_price_overrides_the_product_price(): void
    {
        $product = Product::factory()->withCategory()->create(['price' => 1000]);
        $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 450]);

        $this->assertSame(450.0, $this->pricing->priceFor($product, $variant));
    }

    public function test_a_variant_sale_price_beats_its_price(): void
    {
        $product = Product::factory()->withCategory()->create(['price' => 1000]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'price' => 450,
            'sale_price' => 350,
        ]);

        $this->assertSame(350.0, $this->pricing->priceFor($product, $variant));
    }

    public function test_a_product_offer_does_not_leak_onto_a_variant_price(): void
    {
        $product = Product::factory()->withCategory()->create([
            'price' => 1000,
            'offer_price' => 600,
            'offer_starts_at' => now()->subHour(),
            'offer_ends_at' => now()->addHour(),
        ]);
        $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 450]);

        $this->assertSame(450.0, $this->pricing->priceFor($product, $variant));
    }

    public function test_compare_at_price_is_null_without_a_discount(): void
    {
        $product = Product::factory()->withCategory()->create(['price' => 1000]);

        $this->assertNull($this->pricing->compareAtPriceFor($product));
    }

    public function test_compare_at_price_returns_the_original_when_discounted(): void
    {
        $product = Product::factory()->withCategory()->create(['price' => 1000, 'sale_price' => 800]);

        $this->assertSame(1000.0, $this->pricing->compareAtPriceFor($product));
    }

    public function test_booking_fee_is_zero_when_not_bookable(): void
    {
        $product = Product::factory()->withCategory()->create(['booking_fee' => null]);

        $this->assertSame(0.0, $this->pricing->bookingFeeFor($product));
    }

    public function test_booking_fee_applies_to_a_variant_product(): void
    {
        $product = Product::factory()->withCategory()->create(['booking_fee' => 200, 'price' => 0]);
        $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 450]);

        // The bug this fixes: the old code skipped the fee entirely when a
        // variant was selected, and skipped it again when price <= 200.
        $this->assertSame(200.0, $this->pricing->bookingFeeFor($product));
        $this->assertSame(450.0, $this->pricing->priceFor($product, $variant));
    }

    public function test_a_tba_product_has_no_announced_price(): void
    {
        $product = Product::factory()->withCategory()->create(['price_tba' => true, 'price' => 0]);

        $this->assertFalse($this->pricing->hasAnnouncedPrice($product));
    }

    public function test_a_variant_gives_a_tba_product_an_announced_price(): void
    {
        $product = Product::factory()->withCategory()->create(['price_tba' => true, 'price' => 0]);
        $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 450]);

        $this->assertTrue($this->pricing->hasAnnouncedPrice($product, $variant));
    }
}
