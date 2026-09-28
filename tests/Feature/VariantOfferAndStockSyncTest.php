<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\CartService;
use App\Services\PricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * A product-level offer now reaches every variant, and a variant product's
 * stock total is derived rather than typed.
 *
 * Both reverse earlier deliberate choices, at the owner's request (2026-09-28):
 * an offer used to stop at the product, and products.quantity used to be a
 * field an admin filled in by hand even though the storefront reads the
 * variants.
 *
 * The offer never RAISES a variant's price - CSGO Butterfly holds a 1400 and a
 * 1599 option, so a 1450 offer must only touch the dearer one.
 */
class VariantOfferAndStockSyncTest extends TestCase
{
    use RefreshDatabase;

    private ?User $admin = null;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    private function admin(): User
    {
        return $this->admin ??= User::factory()->create(['email' => 'ifti3061@gmail.com']);
    }

    private function category(): Category
    {
        return Category::firstOrCreate(
            ['slug' => 'csgo'],
            ['name' => 'CS GO', 'parent_id' => null, 'is_active' => true]
        );
    }

    /** A merged product with two differently priced options. */
    private function butterfly(array $productAttributes = []): Product
    {
        $product = Product::factory()->create(array_merge([
            'name' => 'CSGO Butterfly Knife with Box',
            'category_id' => $this->category()->id,
            'is_active' => true,
            'price' => 0,
            'quantity' => 0,
            'sale_price' => null,
            'offer_price' => null,
            'offer_starts_at' => null,
            'offer_ends_at' => null,
        ], $productAttributes));

        ProductVariant::create(['product_id' => $product->id, 'name' => 'Plain', 'price' => 1400, 'quantity' => 2, 'is_active' => true, 'sort_order' => 0]);
        ProductVariant::create(['product_id' => $product->id, 'name' => 'Gold', 'price' => 1599, 'quantity' => 3, 'is_active' => true, 'sort_order' => 1]);

        return $product->refresh();
    }

    private function openOffer(float $price): array
    {
        return [
            'offer_price' => $price,
            'offer_starts_at' => now()->subDay(),
            'offer_ends_at' => now()->addDay(),
        ];
    }

    // ---------------------------------------------------------------- pricing

    public function test_an_open_offer_reaches_every_variant(): void
    {
        $product = $this->butterfly($this->openOffer(1300));
        $pricing = app(PricingService::class);

        foreach ($product->variants as $variant) {
            $this->assertSame(1300.0, $pricing->priceFor($product, $variant), $variant->name);
        }
    }

    public function test_an_offer_never_raises_a_cheaper_variant(): void
    {
        $product = $this->butterfly($this->openOffer(1450));
        $pricing = app(PricingService::class);

        $plain = $product->variants->firstWhere('name', 'Plain');
        $gold = $product->variants->firstWhere('name', 'Gold');

        $this->assertSame(1400.0, $pricing->priceFor($product, $plain), 'Already cheaper than the offer.');
        $this->assertSame(1450.0, $pricing->priceFor($product, $gold));
    }

    public function test_an_offer_outside_its_window_changes_nothing(): void
    {
        $product = $this->butterfly([
            'offer_price' => 900,
            'offer_starts_at' => now()->addDay(),
            'offer_ends_at' => now()->addDays(3),
        ]);
        $pricing = app(PricingService::class);

        $this->assertSame(1400.0, $pricing->priceFor($product, $product->variants->firstWhere('name', 'Plain')));
    }

    public function test_a_variant_sale_price_still_wins_when_it_is_lowest(): void
    {
        $product = $this->butterfly($this->openOffer(1300));
        $plain = $product->variants->firstWhere('name', 'Plain');
        $plain->update(['sale_price' => 1100]);

        $this->assertSame(1100.0, app(PricingService::class)->priceFor($product->refresh(), $plain->refresh()));
    }

    public function test_the_from_price_reflects_the_offer(): void
    {
        $product = $this->butterfly($this->openOffer(1300));

        $this->assertSame(1300.0, app(PricingService::class)->displayPriceFor($product));
    }

    /** The money that actually changes hands, not just the label. */
    public function test_the_cart_charges_the_offer_price(): void
    {
        $product = $this->butterfly($this->openOffer(1300));
        $gold = $product->variants->firstWhere('name', 'Gold');

        $cart = app(CartService::class);
        $cart->add($product, $gold, 2);

        $line = $cart->lines()->first();

        $this->assertSame(1300.0, $line->unitPrice);
        $this->assertSame(2600.0, $line->lineTotal());
        // The struck-through "was" price stays the variant's own.
        $this->assertSame(1599.0, $line->compareAtPrice);
    }

    /**
     * The card has to show what the cart will charge. It builds its own "from"
     * figure out of the variants, so applying offers in PricingService alone
     * would have left the card advertising the pre-offer price.
     */
    public function test_the_shop_card_shows_the_offer_price(): void
    {
        $this->butterfly($this->openOffer(1300));

        $html = $this->get(route('shop.index', ['category_id' => $this->category()->id]))
            ->assertOk()->getContent();

        $this->assertStringContainsString('1,300.00', $html, 'the price the cart will charge');
        // 1,400 still appears, struck through as the old price - what matters
        // is that it is not offered as the price to pay.
        $this->assertMatchesRegularExpression('/line-through[^>]*>\s*৳1,400\.00/u', $html);
    }

    public function test_the_deals_card_shows_the_offer_price_not_a_placeholder(): void
    {
        $this->butterfly($this->openOffer(1300));

        $html = $this->get(route('deals.index'))->assertOk()->getContent();

        $this->assertStringContainsString('1,300.00', $html);
        $this->assertStringNotContainsString('Price to be announced', $html);
    }

    /** Sorting must agree with the price on the card. */
    public function test_price_sorting_uses_the_offer_price(): void
    {
        $this->butterfly($this->openOffer(900));
        Product::factory()->create([
            'name' => 'Plain 1000',
            'category_id' => $this->category()->id,
            'is_active' => true,
            'price' => 1000,
            'quantity' => 3,
            'sale_price' => null,
            'offer_price' => null,
        ]);

        $names = $this->get(route('shop.index', ['category_id' => $this->category()->id, 'sort' => 'price_asc']))
            ->assertOk()->original->getData()['products']->pluck('name')->all();

        $this->assertSame(['CSGO Butterfly Knife with Box', 'Plain 1000'], $names);
    }

    // ------------------------------------------------------------------ stock

    public function test_the_product_total_is_the_sum_of_active_variants(): void
    {
        $product = $this->butterfly();

        $this->assertSame(5, (int) $product->fresh()->quantity);
    }

    public function test_editing_a_variant_quantity_updates_the_total(): void
    {
        $product = $this->butterfly();

        $product->variants->firstWhere('name', 'Gold')->update(['quantity' => 10]);

        $this->assertSame(12, (int) $product->fresh()->quantity);
    }

    public function test_deactivating_a_variant_removes_its_stock_from_the_total(): void
    {
        $product = $this->butterfly();

        $product->variants->firstWhere('name', 'Gold')->update(['is_active' => false]);

        $this->assertSame(2, (int) $product->fresh()->quantity);
    }

    public function test_deleting_a_variant_updates_the_total(): void
    {
        $product = $this->butterfly();

        $product->variants->firstWhere('name', 'Plain')->delete();

        $this->assertSame(3, (int) $product->fresh()->quantity);
    }

    public function test_a_plain_product_keeps_the_quantity_it_was_given(): void
    {
        $product = Product::factory()->withCategory()->create(['quantity' => 7, 'price' => 500]);

        $this->assertSame(7, (int) $product->fresh()->quantity);
    }

    // ------------------------------------------------------------------ admin

    /** The error in the screenshot: offer rejected for being "less than 0.00". */
    public function test_an_offer_price_is_accepted_on_a_variant_product(): void
    {
        $product = $this->butterfly();

        $response = $this->actingAs($this->admin())->put(route('admin.products.update', $product), [
            'name' => $product->name,
            'description' => 'x',
            'notes' => '',
            'category_id' => $product->category_id,
            'price' => 0,
            'quantity' => 0,
            'is_active' => '1',
            'offer_price' => 455,
            'offer_starts_at' => now()->subDay()->format('Y-m-d H:i:s'),
            'offer_ends_at' => now()->addDay()->format('Y-m-d H:i:s'),
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertSame(455.0, (float) $product->fresh()->offer_price);
    }

    /** A plain product still may not be given an offer above its own price. */
    public function test_a_plain_product_still_rejects_an_offer_above_its_price(): void
    {
        $product = Product::factory()->withCategory()->create(['price' => 500, 'quantity' => 3]);

        $response = $this->actingAs($this->admin())->put(route('admin.products.update', $product), [
            'name' => $product->name,
            'description' => 'x',
            'notes' => '',
            'category_id' => $product->category_id,
            'price' => 500,
            'quantity' => 3,
            'is_active' => '1',
            'offer_price' => 900,
        ]);

        $response->assertSessionHasErrors('offer_price');
    }

    /** Whatever the browser posts, the total is recomputed from the variants. */
    public function test_a_posted_quantity_cannot_override_the_variant_total(): void
    {
        $product = $this->butterfly();

        $this->actingAs($this->admin())->put(route('admin.products.update', $product), [
            'name' => $product->name,
            'description' => 'x',
            'notes' => '',
            'category_id' => $product->category_id,
            'price' => 0,
            'quantity' => 999,
            'is_active' => '1',
        ]);

        $this->assertSame(5, (int) $product->fresh()->quantity);
    }
}
