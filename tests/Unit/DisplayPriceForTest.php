<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\PricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The price to show where no variant has been chosen.
 *
 * A merged product's own price column is 0 by design, so listings, dashboards
 * and notification emails that read it were printing "৳0.00" for products that
 * cost up to 1,599.
 */
class DisplayPriceForTest extends TestCase
{
    use RefreshDatabase;

    private PricingService $pricing;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pricing = app(PricingService::class);
    }

    public function test_a_plain_product_shows_its_own_price(): void
    {
        $product = Product::factory()->withCategory()->create(['price' => 1400]);

        $this->assertSame(1400.0, $this->pricing->displayPriceFor($product));
    }

    public function test_a_variant_product_shows_its_cheapest_active_variant(): void
    {
        $product = Product::factory()->withCategory()->create(['price' => 0]);
        ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 1500, 'is_active' => true]);
        ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 950, 'is_active' => true]);

        $this->assertSame(950.0, $this->pricing->displayPriceFor($product->refresh()));
    }

    public function test_a_sold_out_variant_still_counts_towards_the_from_price(): void
    {
        // Sold out is not the same as unavailable - the price is still real.
        $product = Product::factory()->withCategory()->create(['price' => 0]);
        ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 950, 'quantity' => 0, 'is_active' => true]);
        ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 1500, 'quantity' => 3, 'is_active' => true]);

        $this->assertSame(950.0, $this->pricing->displayPriceFor($product->refresh()));
    }

    public function test_an_inactive_variant_is_ignored(): void
    {
        $product = Product::factory()->withCategory()->create(['price' => 0]);
        ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 100, 'is_active' => false]);
        ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 1500, 'is_active' => true]);

        $this->assertSame(1500.0, $this->pricing->displayPriceFor($product->refresh()));
    }

    public function test_a_product_with_no_price_at_all_returns_null(): void
    {
        // Null rather than 0.0, so callers can omit the line instead of
        // telling a customer the item is free.
        $product = Product::factory()->withCategory()->create(['price' => 0]);

        $this->assertNull($this->pricing->displayPriceFor($product));
    }

    public function test_an_open_offer_lowers_the_shown_price(): void
    {
        $product = Product::factory()->withCategory()->create([
            'price' => 1400,
            'offer_price' => 1050,
            'offer_starts_at' => now()->subDay(),
            'offer_ends_at' => now()->addDay(),
        ]);

        $this->assertSame(1050.0, $this->pricing->displayPriceFor($product));
    }

    public function test_a_closed_offer_does_not(): void
    {
        $product = Product::factory()->withCategory()->create([
            'price' => 1400,
            'offer_price' => 1050,
            'offer_starts_at' => now()->subDays(10),
            'offer_ends_at' => now()->subDay(),
        ]);

        $this->assertSame(1400.0, $this->pricing->displayPriceFor($product));
    }

    public function test_the_model_delegates_to_the_service(): void
    {
        $product = Product::factory()->withCategory()->create(['price' => 0]);
        ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 750, 'is_active' => true]);

        $this->assertSame(750.0, $product->refresh()->displayPriceFrom());
    }
}
