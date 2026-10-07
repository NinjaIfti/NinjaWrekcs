<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * The discount furniture around a variant product's price.
 *
 * has_active_offer ended with `offer_price < $this->price`, and a merged
 * product's price column is 0 - so RGX Butterfly, with three ৳1400 variants
 * and an open ৳1200 offer, sold at ৳1200 while reporting that it had no offer
 * at all. The card showed the discounted figure with no "-14% OFF" badge, no
 * struck-through ৳1400 and no countdown, while a plain product beside it
 * showed all three.
 */
class VariantDiscountDisplayTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    private function category(): Category
    {
        return Category::firstOrCreate(
            ['slug' => 'csgo'],
            ['name' => 'CS GO', 'parent_id' => null, 'is_active' => true]
        );
    }

    /** RGX Butterfly as production holds it: three ৳1400 options, ৳1200 offer. */
    private function rgxButterfly(?float $offer = 1200, array $variantPrices = [1400, 1400, 1400]): Product
    {
        $product = Product::factory()->create([
            'name' => 'RGX Butterfly',
            'category_id' => $this->category()->id,
            'is_active' => true,
            'price' => 0,
            'quantity' => 0,
            'sale_price' => null,
            'offer_price' => $offer,
            'offer_starts_at' => $offer ? now()->subDay() : null,
            'offer_ends_at' => $offer ? now()->addDays(12) : null,
        ]);

        foreach ($variantPrices as $i => $price) {
            ProductVariant::create([
                'product_id' => $product->id,
                'name' => 'Colour ' . $i,
                'price' => $price,
                'quantity' => 2,
                'is_active' => true,
                'sort_order' => $i,
            ]);
        }

        return $product->refresh();
    }

    public function test_a_variant_product_reports_its_open_offer(): void
    {
        $this->assertTrue($this->rgxButterfly()->has_active_offer);
    }

    public function test_the_discount_percentage_is_worked_out_from_the_variants(): void
    {
        // 1400 -> 1200 is 14.28%, rounded to 14.
        $this->assertSame(14.0, (float) $this->rgxButterfly()->discount_percentage);
    }

    public function test_an_offer_above_every_variant_is_not_an_offer(): void
    {
        $product = $this->rgxButterfly(offer: 1500);

        $this->assertFalse($product->has_active_offer);
        $this->assertSame(0, (int) $product->discount_percentage);
    }

    public function test_a_variant_product_with_no_offer_reports_none(): void
    {
        $this->assertFalse($this->rgxButterfly(offer: null)->has_active_offer);
    }

    /** A plain product must keep behaving exactly as it did. */
    public function test_a_plain_product_is_unchanged(): void
    {
        $product = Product::factory()->withCategory()->create([
            'name' => 'Valorant Champion Vandal 18cm',
            'is_active' => true,
            'price' => 1200,
            'quantity' => 3,
            'offer_price' => 1050,
            'offer_starts_at' => now()->subDay(),
            'offer_ends_at' => now()->addDays(12),
        ]);

        $this->assertTrue($product->has_active_offer);
        $this->assertSame(13.0, (float) $product->discount_percentage);
    }

    /**
     * The product page priced each swatch by hand and missed the product's
     * offer, so picking a colour showed ৳1400 while the cart charged ৳1200.
     */
    public function test_the_product_page_prices_each_variant_with_the_offer(): void
    {
        $product = $this->rgxButterfly();

        $html = $this->get(route('shop.show', $product))->assertOk()->getContent();

        // Every swatch hands the script the price the cart will charge.
        $this->assertStringContainsString('data-price="1200"', $html);
        $this->assertStringNotContainsString('data-price="1400"', $html);

        // And the pre-offer price travels with it for the strike-through.
        $this->assertStringContainsString('data-compare="1400"', $html);
    }

    public function test_the_headline_price_shows_the_offer_and_the_old_price(): void
    {
        $product = $this->rgxButterfly();

        $html = $this->get(route('shop.show', $product))->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/id="variant-price"[^>]*>\s*৳1,200\.00/u', $html);
        $this->assertMatchesRegularExpression('/id="variant-compare"[^>]*>[\s\S]{0,40}৳1,400\.00/u', $html);
        $this->assertStringContainsString('Save 14%', $html);
    }

    /** No offer, no strike-through - the old price must not appear from nowhere. */
    public function test_a_variant_product_without_an_offer_shows_one_plain_price(): void
    {
        $product = $this->rgxButterfly(offer: null);

        $html = $this->get(route('shop.show', $product))->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/id="variant-price"[^>]*>\s*৳1,400\.00/u', $html);
        // The badge and the old price stay in the markup so the swatch script can
        // fill them for another colour, but both must start hidden.
        $this->assertMatchesRegularExpression('/<span[^>]*\bhidden\b[^>]*id="variant-saving"/', $html);
        $this->assertMatchesRegularExpression('/<div[^>]*\bhidden\b[^>]*id="variant-compare"/', $html);
    }

    /** A variant cheaper than the offer keeps its own price, here too. */
    public function test_a_variant_below_the_offer_is_not_marked_down(): void
    {
        $product = $this->rgxButterfly(offer: 1450, variantPrices: [1400, 1599]);

        $html = $this->get(route('shop.show', $product))->assertOk()->getContent();

        // 1400 stays 1400 and carries no compare price; 1599 drops to 1450.
        $this->assertStringContainsString('data-price="1400"', $html);
        $this->assertStringContainsString('data-price="1450"', $html);
        $this->assertStringContainsString('data-compare="1599"', $html);
    }

    public function test_the_card_shows_the_badge_the_old_price_and_the_timer(): void
    {
        $this->rgxButterfly();

        $html = $this->get(route('shop.index', ['category_id' => $this->category()->id]))
            ->assertOk()->getContent();

        $this->assertStringContainsString('1,200.00', $html, 'the price customers pay');
        $this->assertStringContainsString('1,400.00', $html, 'struck through as the old price');
        $this->assertStringContainsString('-14% OFF', $html);
        $this->assertStringContainsString('offer-countdown', $html);
    }
}
