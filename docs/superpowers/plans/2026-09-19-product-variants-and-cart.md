# Product Variants & Cart Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make product variants first-class sellable units — each with its own price, stock, images and availability — and consolidate the five diverging copies of the cart pricing rule into one service so a variant's price survives from the product page to the charged order.

**Architecture:** Two new services (`PricingService`, `CartService`) become the single source of truth for what an item costs and what is in the cart. Every existing call site — cart page, checkout page, order creation, navbar mini-cart — is migrated onto them, which removes the hand-rolled composite-ID parsing and the N+1 `Product::find()` loops. `product_variants` gains stock, SKU and an active flag; `products` gains `availability` and `booking_fee` replacing three overlapping booleans and a hardcoded `200`.

**Tech Stack:** Laravel 12, PHP 8.2+, PHPUnit 11.5, `darryldecode/cart` ^4.2 (session-backed), Blade, Tailwind. Production DB is MySQL; tests run on SQLite `:memory:`.

**Spec:** [docs/superpowers/specs/2026-09-19-product-variants-and-cart-redesign-design.md](../specs/2026-09-19-product-variants-and-cart-redesign-design.md)

## Scope: this is Plan 1 of 3

The spec covers three separable subsystems. This plan is the first and delivers working, testable software on its own.

| Plan | Covers | Status |
|---|---|---|
| **1. Variants & cart** (this plan) | Variant stock/pricing, `PricingService`, `CartService`, all 5 call sites, stock locking, `availability`/`booking_fee`, PDP swatches, shop cards, admin gate, dead code | Ready |
| 2. Schema cleanup | `products.slug` + URL change, drop legacy `category` enum, admin analytics fix, delete repair console commands | Not yet written |
| 3. Product reviews | `reviews.product_id`, verified-purchase submission, moderation, computed ratings, Popular sort | Not yet written |

`availability` and `booking_fee` live in **this** plan rather than Plan 2 because they are read by the exact controllers this plan rewrites — deferring them would mean touching `CartController` and `CheckoutController` twice.

## Global Constraints

- **Laravel 12 / PHP 8.2+.** Use constructor property promotion and `readonly` properties.
- **Production is MySQL.** Tests run SQLite `:memory:`. Never write MySQL-only raw SQL in a migration without a `DB::getDriverName()` guard.
- **Every migration backfills before it drops, and every migration is reversible.** Additive migrations and their matching drop migrations are always separate files.
- **`orders` and `order_items` are never modified.** Historical orders keep their snapshotted `product_name`, `price` and `subtotal`.
- **`orders.is_preorder_booking` is a different column from `products.is_preorder` and stays exactly as it is.**
- **Currency is BDT, rendered `৳` with `number_format($value, 2)`.** Money is `decimal(10,2)` in the DB and `float` in PHP, matching the existing codebase.
- **The variant stock backfill intentionally overstates stock** (copies parent quantity onto each variant) rather than zeroing it, so nothing silently goes out of stock on deploy.

---

### Task 1: Bootstrap the test harness

The project declares `phpunit/phpunit ^11.5.3` but has **no `tests/` directory and no `phpunit.xml`**. Nothing in this plan can be verified until that exists. One migration also uses MySQL-only syntax that aborts `migrate` on SQLite, which must be guarded before `RefreshDatabase` can work.

**Files:**
- Create: `phpunit.xml`
- Create: `tests/TestCase.php`
- Create: `tests/Feature/SmokeTest.php`
- Create: `database/factories/ProductVariantFactory.php`
- Modify: `database/migrations/2026_04_30_194200_make_order_id_nullable_in_giveaway_entries_table.php`
- Modify: `database/factories/ProductFactory.php`

**Interfaces:**
- Consumes: nothing (first task)
- Produces: `Tests\TestCase` base class; `ProductVariant::factory()`; `Product::factory()` with a `withCategory()` state. Every later task's tests extend `Tests\TestCase` and use `RefreshDatabase`.

- [ ] **Step 1: Create `phpunit.xml`**

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true"
>
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory>tests/Feature</directory>
        </testsuite>
    </testsuites>
    <source>
        <include>
            <directory>app</directory>
        </include>
    </source>
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="APP_MAINTENANCE_DRIVER" value="file"/>
        <env name="BCRYPT_ROUNDS" value="4"/>
        <env name="CACHE_STORE" value="array"/>
        <env name="DB_CONNECTION" value="sqlite"/>
        <env name="DB_DATABASE" value=":memory:"/>
        <env name="MAIL_MAILER" value="array"/>
        <env name="QUEUE_CONNECTION" value="sync"/>
        <env name="SESSION_DRIVER" value="array"/>
    </php>
</phpunit>
```

`composer.json` already maps `"Tests\\": "tests/"` under `autoload-dev`, so no composer change is needed.

- [ ] **Step 2: Create `tests/TestCase.php`**

Laravel 11+ folded the old `CreatesApplication` trait into the base class, so this is all that is required:

```php
<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    //
}
```

- [ ] **Step 3: Write the smoke test (it will fail)**

```php
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_migrations_run_on_sqlite(): void
    {
        $this->assertTrue(\Schema::hasTable('products'));
        $this->assertTrue(\Schema::hasTable('product_variants'));
        $this->assertTrue(\Schema::hasTable('giveaway_entries'));
    }
}
```

- [ ] **Step 4: Run it to confirm it fails on the MySQL-only migration**

Run: `php artisan test --filter=test_migrations_run_on_sqlite`
Expected: FAIL. SQLite rejects `ALTER TABLE giveaway_entries DROP FOREIGN KEY ...` — this is the blocker, and seeing it fail first confirms Step 5 is what fixes it.

- [ ] **Step 5: Guard the MySQL-only migration**

Replace the body of `database/migrations/2026_04_30_194200_make_order_id_nullable_in_giveaway_entries_table.php`. The MySQL path is preserved byte-for-byte so production behaviour does not change; SQLite takes the Schema-builder path.

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE giveaway_entries DROP FOREIGN KEY giveaway_entries_order_id_foreign');
            DB::statement('ALTER TABLE giveaway_entries MODIFY order_id BIGINT UNSIGNED NULL');
            DB::statement('ALTER TABLE giveaway_entries ADD CONSTRAINT giveaway_entries_order_id_foreign FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE');

            return;
        }

        Schema::table('giveaway_entries', function (Blueprint $table) {
            $table->foreignId('order_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE giveaway_entries DROP FOREIGN KEY giveaway_entries_order_id_foreign');
            DB::statement('ALTER TABLE giveaway_entries MODIFY order_id BIGINT UNSIGNED NOT NULL');
            DB::statement('ALTER TABLE giveaway_entries ADD CONSTRAINT giveaway_entries_order_id_foreign FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE');

            return;
        }

        Schema::table('giveaway_entries', function (Blueprint $table) {
            $table->foreignId('order_id')->nullable(false)->change();
        });
    }
};
```

- [ ] **Step 6: Run the smoke test to verify it passes**

Run: `php artisan test --filter=test_migrations_run_on_sqlite`
Expected: PASS

- [ ] **Step 7: Add a `withCategory` state to `ProductFactory`**

Tests need a product with a real category. Append this method to the existing `ProductFactory` class (leave `definition()` untouched — it is rewritten in Task 3 once the columns change):

```php
public function withCategory(?string $slug = null): static
{
    return $this->state(fn () => [
        'category_id' => \App\Models\Category::factory()->create(
            $slug ? ['name' => $slug, 'slug' => $slug] : []
        )->id,
    ]);
}
```

- [ ] **Step 8: Create `ProductVariantFactory`**

Only the columns that exist today — Task 2 adds the rest.

```php
<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'name' => fake()->unique()->words(2, true),
            'price' => fake()->randomFloat(2, 100, 5000),
            'sort_order' => 0,
        ];
    }
}
```

- [ ] **Step 9: Verify the factories work**

Add to `tests/Feature/SmokeTest.php`:

```php
public function test_factories_build_a_product_with_variants(): void
{
    $product = \App\Models\Product::factory()->withCategory()->create();
    \App\Models\ProductVariant::factory()->count(3)->create(['product_id' => $product->id]);

    $this->assertCount(3, $product->refresh()->variants);
}
```

Run: `php artisan test --filter=SmokeTest`
Expected: PASS (2 tests)

- [ ] **Step 10: Commit**

```bash
git add phpunit.xml tests/ database/factories/ database/migrations/2026_04_30_194200_make_order_id_nullable_in_giveaway_entries_table.php
git commit -m "test: bootstrap PHPUnit harness and make migrations SQLite-safe

The project declared phpunit but had no tests directory, no phpunit.xml
and one migration using MySQL-only raw SQL that aborted migrate on
SQLite. Guard it by driver so RefreshDatabase can run.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

### Task 2: Give variants their own stock

`product_variants` currently holds only `name`, `price`, `sort_order`. Stock lives on the parent, so a single colour can never sell out.

**Files:**
- Create: `database/migrations/2026_09_19_100000_add_stock_fields_to_product_variants_table.php`
- Modify: `app/Models/ProductVariant.php`
- Modify: `app/Models/Product.php`
- Modify: `database/factories/ProductVariantFactory.php`
- Test: `tests/Feature/VariantStockTest.php`

**Interfaces:**
- Consumes: `Tests\TestCase`, `ProductVariant::factory()` (Task 1)
- Produces:
  - `ProductVariant` columns `sku`, `quantity`, `sale_price`, `is_active`
  - `ProductVariant::scopeActive(Builder $q): Builder`
  - `Product::availableStock(?ProductVariant $variant = null): int`
  - `Product::hasVariants(): bool`
  - Tasks 4–8 and 11–14 all call `Product::availableStock()`.

- [ ] **Step 1: Write the failing tests**

```php
<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VariantStockTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_variant_holds_its_own_stock(): void
    {
        $product = Product::factory()->withCategory()->create(['quantity' => 50]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $this->assertSame(3, $product->availableStock($variant));
    }

    public function test_stock_falls_back_to_the_product_when_no_variant_given(): void
    {
        $product = Product::factory()->withCategory()->create(['quantity' => 50]);

        $this->assertSame(50, $product->availableStock());
    }

    public function test_a_variant_product_aggregates_stock_from_active_variants_only(): void
    {
        $product = Product::factory()->withCategory()->create(['quantity' => 999]);
        ProductVariant::factory()->create(['product_id' => $product->id, 'quantity' => 4, 'is_active' => true]);
        ProductVariant::factory()->create(['product_id' => $product->id, 'quantity' => 6, 'is_active' => true]);
        ProductVariant::factory()->create(['product_id' => $product->id, 'quantity' => 99, 'is_active' => false]);

        $this->assertSame(10, $product->refresh()->availableStock());
    }

    public function test_one_variant_can_be_sold_out_while_another_is_in_stock(): void
    {
        $product = Product::factory()->withCategory()->create();
        $soldOut = ProductVariant::factory()->create(['product_id' => $product->id, 'quantity' => 0]);
        $inStock = ProductVariant::factory()->create(['product_id' => $product->id, 'quantity' => 5]);

        $this->assertSame(0, $product->availableStock($soldOut));
        $this->assertSame(5, $product->availableStock($inStock));
    }
}
```

- [ ] **Step 2: Run to verify they fail**

Run: `php artisan test --filter=VariantStockTest`
Expected: FAIL — `quantity` is not a column on `product_variants` and `availableStock()` does not exist.

- [ ] **Step 3: Create the migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->string('sku')->nullable()->unique()->after('name');
            $table->decimal('sale_price', 10, 2)->nullable()->after('price');
            $table->unsignedInteger('quantity')->default(0)->after('sale_price');
            $table->boolean('is_active')->default(true)->after('quantity');
        });

        // No per-variant sales history exists to reconstruct true counts from, so
        // copy the parent's stock onto each variant. This overstates total stock
        // rather than understating it, so nothing silently goes out of stock on
        // deploy. The admin reconciles real counts afterwards.
        DB::table('product_variants')->update([
            'quantity' => DB::raw('(SELECT quantity FROM products WHERE products.id = product_variants.product_id)'),
            'is_active' => true,
        ]);
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['sku', 'sale_price', 'quantity', 'is_active']);
        });
    }
};
```

- [ ] **Step 4: Update `ProductVariant`**

Replace `$fillable` and `$casts`, and add the scope:

```php
protected $fillable = [
    'product_id',
    'name',
    'sku',
    'price',
    'sale_price',
    'quantity',
    'sort_order',
    'is_active',
];

protected $casts = [
    'price' => 'decimal:2',
    'sale_price' => 'decimal:2',
    'quantity' => 'integer',
    'is_active' => 'boolean',
];

public function scopeActive($query)
{
    return $query->where('is_active', true);
}

public function isInStock(): bool
{
    return $this->is_active && $this->quantity > 0;
}
```

- [ ] **Step 5: Add stock resolution to `Product`**

Add to `app/Models/Product.php`. Note the `activeVariants` relation — Tasks 12 and 13 reuse it so sold-out variants render disabled rather than being silently addable.

```php
public function activeVariants(): HasMany
{
    return $this->hasMany(ProductVariant::class)->where('is_active', true)->orderBy('sort_order');
}

public function hasVariants(): bool
{
    return $this->relationLoaded('variants')
        ? $this->getRelation('variants')->isNotEmpty()
        : $this->variants()->exists();
}

/**
 * Stock for a specific variant, or the product's own sellable stock.
 *
 * A product with variants has no stock of its own - its quantity column is
 * legacy and is ignored in favour of the sum of its active variants, so the
 * sold-out badge stays correct without a denormalised counter.
 */
public function availableStock(?ProductVariant $variant = null): int
{
    if ($variant !== null) {
        return $variant->is_active ? (int) $variant->quantity : 0;
    }

    if ($this->hasVariants()) {
        return (int) $this->variants()->where('is_active', true)->sum('quantity');
    }

    return (int) $this->quantity;
}
```

- [ ] **Step 6: Add the new columns to `ProductVariantFactory`**

Replace `definition()`:

```php
public function definition(): array
{
    return [
        'product_id' => Product::factory(),
        'name' => fake()->unique()->words(2, true),
        'sku' => fake()->unique()->bothify('SKU-####-???'),
        'price' => fake()->randomFloat(2, 100, 5000),
        'sale_price' => null,
        'quantity' => fake()->numberBetween(1, 20),
        'sort_order' => 0,
        'is_active' => true,
    ];
}
```

- [ ] **Step 7: Run tests to verify they pass**

Run: `php artisan test --filter=VariantStockTest`
Expected: PASS (4 tests)

- [ ] **Step 8: Commit**

```bash
git add database/migrations/2026_09_19_100000_add_stock_fields_to_product_variants_table.php app/Models/ProductVariant.php app/Models/Product.php database/factories/ProductVariantFactory.php tests/Feature/VariantStockTest.php
git commit -m "feat: give product variants their own stock, sku and active flag

A variant could not previously sell out independently - quantity lived
only on the parent product, so checkout decremented the parent no matter
which variant was bought.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

### Task 3: Replace three overlapping booleans with `availability` and `booking_fee`

`is_preorder`, `is_upcoming` and `is_bookable` overlap in meaning, and the booking amount is hardcoded as `200` in three files — which is why a bookable product **with variants** is priced wrong today. This task adds the replacements and backfills them. The old columns stay until Task 10.

**Files:**
- Create: `database/migrations/2026_09_19_100100_add_availability_and_booking_fee_to_products_table.php`
- Modify: `app/Models/Product.php`
- Modify: `database/factories/ProductFactory.php`
- Test: `tests/Feature/ProductAvailabilityTest.php`

**Interfaces:**
- Consumes: `Product::factory()` (Task 1)
- Produces:
  - `products.availability` enum(`in_stock`, `preorder`, `upcoming`), default `in_stock`
  - `products.booking_fee` decimal(10,2) nullable — `null` means no booking required
  - `Product::AVAILABILITY_IN_STOCK`, `::AVAILABILITY_PREORDER`, `::AVAILABILITY_UPCOMING`
  - `Product::requiresBooking(): bool`
  - `Product::isPurchasable(): bool`
  - Tasks 4–9 and 11–14 read `availability` and `booking_fee`; nothing reads the three booleans after Task 9.

- [ ] **Step 1: Write the failing tests**

```php
<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProductAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_bookable_product_reports_its_booking_fee(): void
    {
        $product = Product::factory()->withCategory()->create(['booking_fee' => 200.00]);

        $this->assertTrue($product->requiresBooking());
        $this->assertSame(200.00, (float) $product->booking_fee);
    }

    public function test_a_product_without_a_booking_fee_does_not_require_booking(): void
    {
        $product = Product::factory()->withCategory()->create(['booking_fee' => null]);

        $this->assertFalse($product->requiresBooking());
    }

    public function test_an_upcoming_product_is_not_purchasable(): void
    {
        $product = Product::factory()->withCategory()->create([
            'availability' => Product::AVAILABILITY_UPCOMING,
        ]);

        $this->assertFalse($product->isPurchasable());
    }

    public function test_in_stock_and_preorder_products_are_purchasable(): void
    {
        $inStock = Product::factory()->withCategory()->create([
            'availability' => Product::AVAILABILITY_IN_STOCK,
        ]);
        $preorder = Product::factory()->withCategory()->create([
            'availability' => Product::AVAILABILITY_PREORDER,
        ]);

        $this->assertTrue($inStock->isPurchasable());
        $this->assertTrue($preorder->isPurchasable());
    }

    public function test_an_inactive_product_is_not_purchasable(): void
    {
        $product = Product::factory()->withCategory()->create([
            'availability' => Product::AVAILABILITY_IN_STOCK,
            'is_active' => false,
        ]);

        $this->assertFalse($product->isPurchasable());
    }
}
```

- [ ] **Step 2: Run to verify they fail**

Run: `php artisan test --filter=ProductAvailabilityTest`
Expected: FAIL — `availability` and `booking_fee` are not columns.

- [ ] **Step 3: Create the migration**

`upcoming` deliberately wins over `preorder` in the backfill: a product flagged both was unpurchasable before, and must stay unpurchasable.

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->enum('availability', ['in_stock', 'preorder', 'upcoming'])
                ->default('in_stock')
                ->after('is_active');
            $table->decimal('booking_fee', 10, 2)->nullable()->after('availability');
        });

        // Backfill. `upcoming` wins over `preorder`: a product flagged both was
        // not purchasable before and must stay not purchasable.
        DB::table('products')->where('is_upcoming', true)->update(['availability' => 'upcoming']);
        DB::table('products')->where('is_upcoming', false)->where('is_preorder', true)->update(['availability' => 'preorder']);

        // 200 was the hardcoded booking amount in CartController, CheckoutController
        // and the checkout view. It becomes data.
        DB::table('products')->where('is_bookable', true)->update(['booking_fee' => 200.00]);
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['availability', 'booking_fee']);
        });
    }
};
```

- [ ] **Step 4: Update the `Product` model**

Add the constants and helpers, and add `availability` + `booking_fee` to `$fillable`. Leave the three old booleans in `$fillable` and `$casts` for now — Task 10 removes them.

```php
public const AVAILABILITY_IN_STOCK = 'in_stock';
public const AVAILABILITY_PREORDER = 'preorder';
public const AVAILABILITY_UPCOMING = 'upcoming';

public function requiresBooking(): bool
{
    return $this->booking_fee !== null && (float) $this->booking_fee > 0;
}

public function isPurchasable(): bool
{
    return $this->is_active && $this->availability !== self::AVAILABILITY_UPCOMING;
}
```

Add to `$fillable`: `'availability'`, `'booking_fee'`.
Add to `$casts`: `'booking_fee' => 'decimal:2'`.

- [ ] **Step 5: Update `ProductFactory`**

Replace `definition()` — the three booleans stay until Task 10 so existing rows and the down-migration remain consistent:

```php
public function definition(): array
{
    return [
        'name' => fake()->words(3, true),
        'description' => fake()->paragraph(),
        'notes' => null,
        'quantity' => fake()->numberBetween(0, 100),
        'price' => fake()->randomFloat(2, 100, 5000),
        'cost_price' => null,
        'sale_price' => null,
        'offer_price' => null,
        'offer_starts_at' => null,
        'offer_ends_at' => null,
        'image' => null,
        'category' => 'figures',
        'category_id' => null,
        'rating' => 0,
        'reviews' => 0,
        'is_active' => true,
        'is_featured' => false,
        'is_new' => false,
        'is_bestseller' => false,
        'is_limited_edition' => false,
        'is_preorder' => false,
        'is_upcoming' => false,
        'price_tba' => false,
        'is_bookable' => false,
        'availability' => Product::AVAILABILITY_IN_STOCK,
        'booking_fee' => null,
    ];
}
```

Add `use App\Models\Product;` if not already imported.

- [ ] **Step 6: Run tests to verify they pass**

Run: `php artisan test --filter=ProductAvailabilityTest`
Expected: PASS (5 tests)

- [ ] **Step 7: Commit**

```bash
git add database/migrations/2026_09_19_100100_add_availability_and_booking_fee_to_products_table.php app/Models/Product.php database/factories/ProductFactory.php tests/Feature/ProductAvailabilityTest.php
git commit -m "feat: add products.availability and booking_fee

Replaces the overlapping is_preorder/is_upcoming/is_bookable booleans and
turns the booking amount hardcoded as 200 in three files into data. Old
columns are kept until call sites migrate.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

### Task 4: `PricingService` — one precedence rule

Five places currently re-derive an item's price and they disagree. This builds the one rule; Tasks 6–8 migrate the call sites onto it.

**Files:**
- Create: `app/Services/PricingService.php`
- Test: `tests/Unit/PricingServiceTest.php`

**Interfaces:**
- Consumes: `Product`, `ProductVariant` (Tasks 2–3)
- Produces:
  - `PricingService::priceFor(Product $product, ?ProductVariant $variant = null): float`
  - `PricingService::compareAtPriceFor(Product $product, ?ProductVariant $variant = null): ?float`
  - `PricingService::bookingFeeFor(Product $product): float`
  - `PricingService::hasAnnouncedPrice(Product $product, ?ProductVariant $variant = null): bool`
  - Task 5 injects this into `CartService`; Tasks 12–13 use it in views.

- [ ] **Step 1: Write the failing tests**

```php
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
```

- [ ] **Step 2: Run to verify they fail**

Run: `php artisan test --filter=PricingServiceTest`
Expected: FAIL — `App\Services\PricingService` does not exist.

- [ ] **Step 3: Implement `PricingService`**

```php
<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;

/**
 * The single source of truth for what an item costs.
 *
 * Before this class the precedence rule was reimplemented in five places
 * (the cart page, the checkout page, order creation, the navbar mini-cart and
 * add-to-cart) and they disagreed - the cart used display_price for pre-order
 * items while checkout used the raw price, so the cart total and the charged
 * total differed whenever a bookable item was on offer.
 */
class PricingService
{
    /**
     * Precedence, highest first:
     *   1. variant sale_price  (when a variant is given and it undercuts variant price)
     *   2. variant price       (when a variant is given)
     *   3. product offer_price (when the offer window is open and it undercuts price)
     *   4. product sale_price  (when it undercuts price)
     *   5. product price
     *
     * A variant carries its own pricing outright - a product-level offer does
     * not leak onto it, because a variant's price is not derived from the
     * product's.
     */
    public function priceFor(Product $product, ?ProductVariant $variant = null): float
    {
        if ($variant !== null) {
            $variantPrice = (float) $variant->price;
            $variantSale = $variant->sale_price !== null ? (float) $variant->sale_price : null;

            return ($variantSale !== null && $variantSale < $variantPrice)
                ? $variantSale
                : $variantPrice;
        }

        $price = (float) $product->price;

        if ($this->offerIsOpen($product) && (float) $product->offer_price < $price) {
            return (float) $product->offer_price;
        }

        if ($product->sale_price !== null && (float) $product->sale_price < $price) {
            return (float) $product->sale_price;
        }

        return $price;
    }

    /**
     * The struck-through "was" price, or null when there is no discount.
     */
    public function compareAtPriceFor(Product $product, ?ProductVariant $variant = null): ?float
    {
        $original = $variant !== null ? (float) $variant->price : (float) $product->price;
        $effective = $this->priceFor($product, $variant);

        return $effective < $original ? $original : null;
    }

    /**
     * Per-unit booking fee, or 0.0 when the product does not require booking.
     *
     * This replaces the hardcoded 200 that lived in CartController,
     * CheckoutController and the checkout view. Critically it is NOT subtracted
     * from the item price the way the old code did - it is carried separately
     * in CartSummary, which is what makes it correct for variant products.
     */
    public function bookingFeeFor(Product $product): float
    {
        return $product->booking_fee !== null ? (float) $product->booking_fee : 0.0;
    }

    /**
     * A price_tba product has no announced price of its own, but selecting a
     * variant does announce one.
     */
    public function hasAnnouncedPrice(Product $product, ?ProductVariant $variant = null): bool
    {
        if ($variant !== null) {
            return (float) $variant->price > 0;
        }

        return ! $product->price_tba && (float) $product->price > 0;
    }

    private function offerIsOpen(Product $product): bool
    {
        if ($product->offer_price === null || $product->offer_starts_at === null || $product->offer_ends_at === null) {
            return false;
        }

        return now()->between($product->offer_starts_at, $product->offer_ends_at);
    }
}
```

- [ ] **Step 4: Run tests to verify they pass**

Run: `php artisan test --filter=PricingServiceTest`
Expected: PASS (13 tests)

- [ ] **Step 5: Commit**

```bash
git add app/Services/PricingService.php tests/Unit/PricingServiceTest.php
git commit -m "feat: add PricingService as the single price precedence rule

Call sites migrate onto it in the tasks that follow.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

### Task 5: `CartService` and the `CartLine` / `CartSummary` DTOs

Composite cart IDs (`"12_3"`) are parsed by hand in five places, and `Product::find()` runs inside `foreach` loops. The cart already writes `attributes.product_id` and `attributes.variant_id` — nothing reads them. This task makes them the only thing anything reads.

**Files:**
- Create: `app/Services/CartLine.php`
- Create: `app/Services/CartSummary.php`
- Create: `app/Services/CartService.php`
- Test: `tests/Feature/CartServiceTest.php`

**Interfaces:**
- Consumes: `PricingService` (Task 4), `Product::availableStock()` (Task 2), `Product::requiresBooking()` (Task 3)
- Produces:
  - `CartLine` readonly DTO: `id`, `product`, `variant`, `name`, `unitPrice`, `quantity`, `bookingFeePerUnit`, `image`; methods `lineTotal(): float`, `bookingTotal(): float`, `availableStock(): int`
  - `CartSummary` readonly DTO: `subtotal`, `bookingTotal`, `itemCount`, `hasBookingItems`, `hasBookingConflict`
  - `CartService::add(Product, ?ProductVariant, int): void` (throws `CartException`)
  - `CartService::update(string $cartItemId, int $quantity): void` (throws `CartException`)
  - `CartService::remove(string): void`, `CartService::clear(): void`
  - `CartService::lines(): Collection<CartLine>`
  - `CartService::summary(): CartSummary`
  - Tasks 6, 7, 8, 13, 14 all consume these.
- Create: `app/Exceptions/CartException.php`

- [ ] **Step 1: Write the failing tests**

```php
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
}
```

- [ ] **Step 2: Run to verify they fail**

Run: `php artisan test --filter=CartServiceTest`
Expected: FAIL — `App\Services\CartService` does not exist.

- [ ] **Step 3: Create `CartException`**

```php
<?php

namespace App\Exceptions;

use Exception;

/**
 * A cart operation the customer should be told about in plain language -
 * insufficient stock, a sold-out variant, a disallowed mix. The message is
 * shown directly to the customer, so write it for them.
 */
class CartException extends Exception
{
}
```

- [ ] **Step 4: Create `CartLine`**

```php
<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;

final class CartLine
{
    public function __construct(
        public readonly string $id,
        public readonly Product $product,
        public readonly ?ProductVariant $variant,
        public readonly string $name,
        public readonly float $unitPrice,
        public readonly ?float $compareAtPrice,
        public readonly int $quantity,
        public readonly float $bookingFeePerUnit,
        public readonly ?string $image,
    ) {
    }

    public function lineTotal(): float
    {
        return $this->unitPrice * $this->quantity;
    }

    public function bookingTotal(): float
    {
        return $this->bookingFeePerUnit * $this->quantity;
    }

    public function availableStock(): int
    {
        return $this->product->availableStock($this->variant);
    }

    public function requiresBooking(): bool
    {
        return $this->bookingFeePerUnit > 0;
    }
}
```

- [ ] **Step 5: Create `CartSummary`**

```php
<?php

namespace App\Services;

final class CartSummary
{
    public function __construct(
        public readonly float $subtotal,
        public readonly float $bookingTotal,
        public readonly int $itemCount,
        public readonly bool $hasBookingItems,
    ) {
    }

    public function isEmpty(): bool
    {
        return $this->itemCount === 0;
    }
}
```

- [ ] **Step 6: Implement `CartService`**

```php
<?php

namespace App\Services;

use App\Exceptions\CartException;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;

/**
 * The only thing that touches the underlying cart package.
 *
 * Cart item keys stay composite ("12_3") for backward compatibility with
 * sessions created before this class existed, but the key is treated as
 * OPAQUE - product and variant are always read from the item's attributes,
 * never parsed out of the key. That removes the explode('_') that used to be
 * duplicated across five call sites, two of which passed the composite string
 * straight into Product::find().
 */
class CartService
{
    public function __construct(private readonly PricingService $pricing)
    {
    }

    public function add(Product $product, ?ProductVariant $variant, int $quantity): void
    {
        if ($quantity < 1) {
            throw new CartException('Quantity must be at least 1.');
        }

        if (! $product->is_active) {
            throw new CartException('This product is not available.');
        }

        if (! $product->isPurchasable()) {
            throw new CartException('This item is upcoming and cannot be added to cart yet.');
        }

        if ($variant !== null && $variant->product_id !== $product->id) {
            throw new CartException('Invalid variant selected.');
        }

        if ($variant !== null && ! $variant->is_active) {
            throw new CartException('That option is no longer available.');
        }

        if ($product->hasVariants() && $variant === null) {
            throw new CartException('Please choose an option before adding to cart.');
        }

        if (! $this->pricing->hasAnnouncedPrice($product, $variant)) {
            throw new CartException('Price will be announced later. Please check back once pricing is available.');
        }

        $this->guardBookingMix($product);

        $key = $this->keyFor($product, $variant);
        $existing = \Cart::get($key);
        $alreadyInCart = $existing ? (int) $existing->quantity : 0;
        $available = $product->availableStock($variant);

        if ($available < 1) {
            throw new CartException('That option is sold out.');
        }

        if (($alreadyInCart + $quantity) > $available) {
            throw new CartException("Only {$available} available in stock.");
        }

        \Cart::add([
            'id' => $key,
            'name' => $this->displayName($product, $variant),
            'price' => $this->pricing->priceFor($product, $variant),
            'quantity' => $quantity,
            'attributes' => $this->attributesFor($product, $variant),
        ]);
    }

    public function update(string $cartItemId, int $quantity): void
    {
        if ($quantity < 1) {
            throw new CartException('Quantity must be at least 1.');
        }

        $line = $this->lines()->firstWhere('id', $cartItemId);

        if ($line === null) {
            throw new CartException('Item not found in cart.');
        }

        $available = $line->availableStock();

        if ($quantity > $available) {
            throw new CartException("Only {$available} available in stock.");
        }

        \Cart::update($cartItemId, [
            'quantity' => ['relative' => false, 'value' => $quantity],
        ]);
    }

    public function remove(string $cartItemId): void
    {
        \Cart::remove($cartItemId);
    }

    public function clear(): void
    {
        \Cart::clear();
    }

    /**
     * Every cart item, hydrated with its Product and Variant in two queries.
     *
     * Lines whose product or variant no longer exists are dropped from the cart
     * rather than returned, so a deleted product cannot reach checkout.
     *
     * @return Collection<int, CartLine>
     */
    public function lines(): Collection
    {
        $raw = collect(\Cart::getContent()->values());

        if ($raw->isEmpty()) {
            return collect();
        }

        $resolved = $raw->map(fn ($item) => [
            'item' => $item,
            'product_id' => $this->productIdFor($item),
            'variant_id' => $this->variantIdFor($item),
        ]);

        $products = Product::with('variants')
            ->whereIn('id', $resolved->pluck('product_id')->filter()->unique())
            ->get()
            ->keyBy('id');

        $variants = ProductVariant::whereIn('id', $resolved->pluck('variant_id')->filter()->unique())
            ->get()
            ->keyBy('id');

        return $resolved->map(function (array $entry) use ($products, $variants) {
            $product = $products->get($entry['product_id']);
            $variant = $entry['variant_id'] ? $variants->get($entry['variant_id']) : null;

            if ($product === null || ($entry['variant_id'] && $variant === null)) {
                \Cart::remove($entry['item']->id);

                return null;
            }

            return new CartLine(
                id: (string) $entry['item']->id,
                product: $product,
                variant: $variant,
                name: $this->displayName($product, $variant),
                unitPrice: $this->pricing->priceFor($product, $variant),
                compareAtPrice: $this->pricing->compareAtPriceFor($product, $variant),
                quantity: (int) $entry['item']->quantity,
                bookingFeePerUnit: $this->pricing->bookingFeeFor($product),
                image: $this->imageFor($product, $variant),
            );
        })->filter()->values();
    }

    public function summary(): CartSummary
    {
        $lines = $this->lines();

        return new CartSummary(
            subtotal: (float) $lines->sum(fn (CartLine $line) => $line->lineTotal()),
            bookingTotal: (float) $lines->sum(fn (CartLine $line) => $line->bookingTotal()),
            itemCount: (int) $lines->sum(fn (CartLine $line) => $line->quantity),
            hasBookingItems: $lines->contains(fn (CartLine $line) => $line->requiresBooking()),
        );
    }

    public function keyFor(Product $product, ?ProductVariant $variant): string
    {
        return $variant !== null
            ? $product->id . '_' . $variant->id
            : (string) $product->id;
    }

    private function displayName(Product $product, ?ProductVariant $variant): string
    {
        return $variant !== null
            ? $product->name . ' — ' . $variant->name
            : $product->name;
    }

    private function imageFor(Product $product, ?ProductVariant $variant): ?string
    {
        if ($variant !== null && $variant->images->isNotEmpty()) {
            return $variant->images->first()->path;
        }

        return $product->cover_photo ?? $product->image;
    }

    private function attributesFor(Product $product, ?ProductVariant $variant): array
    {
        return [
            'product_id' => $product->id,
            'variant_id' => $variant?->id,
            'image' => $this->imageFor($product, $variant),
            'category' => $product->category_name,
        ];
    }

    /**
     * A booking item and a regular item cannot share a cart - they are paid for
     * differently (booking requires mobile banking, COD is not allowed).
     */
    private function guardBookingMix(Product $incoming): void
    {
        $lines = $this->lines();

        if ($lines->isEmpty()) {
            return;
        }

        $cartHasBooking = $lines->contains(fn (CartLine $line) => $line->requiresBooking());
        $incomingIsBooking = $incoming->requiresBooking();

        if ($incomingIsBooking && ! $cartHasBooking) {
            throw new CartException('Cannot add a pre-order item to a cart with in-stock items. Please clear your cart or complete your current order first.');
        }

        if (! $incomingIsBooking && $cartHasBooking) {
            throw new CartException('Cannot add an in-stock item to a cart with pre-order items. Please clear your cart or complete your current order first.');
        }
    }

    /**
     * Read the product id from attributes, falling back to parsing the key ONCE
     * for carts created before this class existed.
     */
    private function productIdFor($item): ?int
    {
        $fromAttributes = $item->attributes->product_id ?? null;

        if ($fromAttributes) {
            return (int) $fromAttributes;
        }

        return (int) explode('_', (string) $item->id)[0] ?: null;
    }

    private function variantIdFor($item): ?int
    {
        $fromAttributes = $item->attributes->variant_id ?? null;

        if ($fromAttributes) {
            return (int) $fromAttributes;
        }

        $parts = explode('_', (string) $item->id);

        return isset($parts[1]) ? (int) $parts[1] : null;
    }
}
```

- [ ] **Step 7: Run tests to verify they pass**

Run: `php artisan test --filter=CartServiceTest`
Expected: PASS (16 tests)

- [ ] **Step 8: Commit**

```bash
git add app/Services/CartService.php app/Services/CartLine.php app/Services/CartSummary.php app/Exceptions/CartException.php tests/Feature/CartServiceTest.php
git commit -m "feat: add CartService with CartLine and CartSummary DTOs

Cart keys stay composite for session compatibility but are now opaque -
product and variant come from item attributes the cart already wrote but
nothing read. Removes the explode('_') duplicated across five call sites
and the N+1 Product::find() loops. Booking fees are carried separately
rather than subtracted from the item price, which fixes variant pricing.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

### Task 6: Migrate `CartController` onto `CartService`

**Files:**
- Modify: `app/Http/Controllers/CartController.php` (full rewrite — 209 lines becomes ~90)
- Modify: `resources/views/cart/index.blade.php`
- Test: `tests/Feature/CartControllerTest.php`

**Interfaces:**
- Consumes: `CartService`, `CartLine`, `CartSummary`, `CartException` (Task 5)
- Produces: `cart.index` view receives `$lines` (Collection<CartLine>) and `$summary` (CartSummary). `$cartItems`, `$cartTotal`, `$cartSubTotal` and `$hasBookableItems` are gone — Task 8 and Task 14 update the remaining consumers.

- [ ] **Step 1: Write the failing tests**

```php
<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_adding_a_variant_redirects_back_with_success(): void
    {
        $product = Product::factory()->withCategory()->create(['price' => 0]);
        $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 450, 'quantity' => 5]);

        $response = $this->post(route('cart.add', $product), ['variant_id' => $variant->id, 'quantity' => 1]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertSame(1, app(\App\Services\CartService::class)->summary()->itemCount);
    }

    public function test_adding_a_sold_out_variant_shows_an_error(): void
    {
        $product = Product::factory()->withCategory()->create(['price' => 0]);
        $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 450, 'quantity' => 0]);

        $response = $this->post(route('cart.add', $product), ['variant_id' => $variant->id, 'quantity' => 1]);

        $response->assertSessionHas('error');
        $this->assertSame(0, app(\App\Services\CartService::class)->summary()->itemCount);
    }

    public function test_the_cart_page_renders_a_variant_line(): void
    {
        $product = Product::factory()->withCategory()->create(['name' => 'RGX Butterfly', 'price' => 0]);
        $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'name' => 'Blue', 'price' => 450, 'quantity' => 5]);

        $this->post(route('cart.add', $product), ['variant_id' => $variant->id, 'quantity' => 2]);

        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee('RGX Butterfly')
            ->assertSee('Blue');
    }

    public function test_updating_beyond_variant_stock_is_rejected(): void
    {
        $product = Product::factory()->withCategory()->create(['price' => 0]);
        $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 450, 'quantity' => 3]);
        $this->post(route('cart.add', $product), ['variant_id' => $variant->id, 'quantity' => 1]);

        $key = $product->id . '_' . $variant->id;

        $this->put(route('cart.update', $key), ['quantity' => 10])
            ->assertSessionHas('error');

        $this->assertSame(1, app(\App\Services\CartService::class)->summary()->itemCount);
    }
}
```

- [ ] **Step 2: Run to verify they fail**

Run: `php artisan test --filter=CartControllerTest`
Expected: FAIL — the controller still resolves prices itself and does not enforce variant stock.

- [ ] **Step 3: Rewrite `CartController`**

```php
<?php

namespace App\Http\Controllers;

use App\Exceptions\CartException;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cart)
    {
    }

    public function index()
    {
        return view('cart.index', [
            'lines' => $this->cart->lines(),
            'summary' => $this->cart->summary(),
        ]);
    }

    public function add(Request $request, Product $product)
    {
        $variant = null;

        if ($variantId = $request->input('variant_id')) {
            $variant = ProductVariant::where('product_id', $product->id)->find($variantId);

            if (! $variant) {
                return back()->with('error', 'Invalid variant selected.');
            }
        }

        $quantity = max(1, (int) $request->input('quantity', 1));

        try {
            $this->cart->add($product, $variant, $quantity);
        } catch (CartException $e) {
            return back()->with('error', $e->getMessage());
        }

        $line = $this->cart->lines()->firstWhere('id', $this->cart->keyFor($product, $variant));

        return back()
            ->with('success', 'Product added to cart successfully!')
            ->with('data_layer_event', [
                'event' => 'add_to_cart',
                'ecommerce' => [
                    'currency' => 'BDT',
                    'value' => $line ? $line->unitPrice * $quantity : 0.0,
                    'items' => [[
                        'item_id' => (string) $product->id,
                        'item_name' => $line?->name ?? $product->name,
                        'item_category' => $product->category_name ?? 'Valorant Collectibles',
                        'price' => $line?->unitPrice ?? 0.0,
                        'quantity' => $quantity,
                    ]],
                ],
            ]);
    }

    public function update(Request $request, string $itemId)
    {
        try {
            $this->cart->update($itemId, (int) $request->input('quantity', 1));
        } catch (CartException $e) {
            return redirect()->route('cart.index')->with('error', $e->getMessage());
        }

        return redirect()->route('cart.index')->with('success', 'Cart updated successfully!');
    }

    public function remove(string $itemId)
    {
        $this->cart->remove($itemId);

        return redirect()->route('cart.index')->with('success', 'Item removed from cart.');
    }

    public function clear()
    {
        $this->cart->clear();

        return redirect()->route('cart.index')->with('success', 'Cart cleared successfully.');
    }
}
```

- [ ] **Step 4: Update `resources/views/cart/index.blade.php` to the new variables**

Replace every `$cartItems` loop with `$lines`, and every `$cartSubTotal` / `$cartTotal` reference with `$summary->subtotal`. Each line now reads:

```blade
@foreach($lines as $line)
    <div class="flex items-center gap-4">
        @if($line->image)
            <img src="{{ asset('storage/' . $line->image) }}" alt="{{ $line->name }}" class="w-20 h-20 object-cover rounded-lg">
        @endif

        <div class="flex-1">
            <p class="text-white font-semibold">{{ $line->name }}</p>

            @if($line->variant)
                <p class="text-sm text-violet-300">{{ $line->variant->name }}</p>
            @endif

            <p class="text-sm text-gray-400">
                ৳{{ number_format($line->unitPrice, 2) }}
                @if($line->compareAtPrice)
                    <span class="line-through text-gray-600">৳{{ number_format($line->compareAtPrice, 2) }}</span>
                @endif
            </p>

            @if($line->requiresBooking())
                <p class="text-xs text-amber-400">
                    + ৳{{ number_format($line->bookingFeePerUnit, 2) }} booking fee per unit
                </p>
            @endif
        </div>

        <form method="POST" action="{{ route('cart.update', $line->id) }}">
            @csrf
            @method('PUT')
            <input type="number" name="quantity" value="{{ $line->quantity }}"
                   min="1" max="{{ $line->availableStock() }}"
                   class="w-20 px-2 py-1 bg-black/50 border border-violet-500/30 rounded text-white">
            <button type="submit" class="text-sm text-violet-400">Update</button>
        </form>

        <p class="text-white font-bold">৳{{ number_format($line->lineTotal(), 2) }}</p>
    </div>
@endforeach
```

And the totals block:

```blade
<div class="space-y-2">
    <div class="flex justify-between text-gray-300">
        <span>Subtotal ({{ $summary->itemCount }} items)</span>
        <span>৳{{ number_format($summary->subtotal, 2) }}</span>
    </div>

    @if($summary->hasBookingItems)
        <div class="flex justify-between text-amber-400">
            <span>Booking due now</span>
            <span>৳{{ number_format($summary->bookingTotal, 2) }}</span>
        </div>
    @endif
</div>
```

- [ ] **Step 5: Run tests to verify they pass**

Run: `php artisan test --filter=CartControllerTest`
Expected: PASS (4 tests)

- [ ] **Step 6: Run the whole suite — nothing earlier may regress**

Run: `php artisan test`
Expected: PASS

- [ ] **Step 7: Commit**

```bash
git add app/Http/Controllers/CartController.php resources/views/cart/index.blade.php tests/Feature/CartControllerTest.php
git commit -m "refactor: move CartController onto CartService

Drops the hand-rolled price recalculation, the composite-id parsing and
the unused \$cartTotal that was computed and passed to a view that never
read it.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

### Task 7: Migrate `CheckoutController` — cart/order price parity and stock locking

This is the task that fixes the headline bug. `CheckoutController::store` currently re-derives every price a third time, uses the raw `price` for bookable items where the cart used `display_price`, calls `Product::find()` on composite IDs, computes the booking totals in two identical loops, and decrements stock with no lock.

**Files:**
- Modify: `app/Http/Controllers/CheckoutController.php`
- Modify: `resources/views/checkout/index.blade.php`
- Test: `tests/Feature/CheckoutTest.php`

**Interfaces:**
- Consumes: `CartService`, `CartLine`, `CartSummary` (Task 5)
- Produces: `checkout.index` view receives `$lines`, `$summary`. `$cartItems`, `$cartTotal`, `$cartSubTotal`, `$totalDiscount`, `$finalTotal`, `$hasBookableItems`, `$totalBookingAmount` are all gone.

- [ ] **Step 1: Write the failing tests**

```php
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
```

- [ ] **Step 2: Run to verify they fail**

Run: `php artisan test --filter=CheckoutTest`
Expected: FAIL — variant stock is not decremented, booking amount is wrong for variants.

- [ ] **Step 3: Replace `CheckoutController::index`**

```php
public function __construct(private readonly CartService $cart)
{
}

public function index(): View|RedirectResponse
{
    $lines = $this->cart->lines();

    if ($lines->isEmpty()) {
        return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
    }

    return view('checkout.index', [
        'lines' => $lines,
        'summary' => $this->cart->summary(),
    ]);
}
```

This deletes the 45-line subtotal loop and the unused `$cartTotal`, `$totalDiscount` and `$finalTotal`.

- [ ] **Step 4: Replace the order-building half of `store`**

Replace everything from the start of `store` down to the end of the order-items loop. The user/account handling block between them is unchanged — leave it exactly as it is.

```php
public function store(Request $request): RedirectResponse
{
    $lines = $this->cart->lines();

    if ($lines->isEmpty()) {
        return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
    }

    $summary = $this->cart->summary();
    $isLoggedIn = Auth::check();

    $rules = [
        'name' => 'required|string|max:255',
        'phone' => 'required|string|max:20',
        'address' => 'required|string',
        'delivery_location' => 'required|in:inside_dhaka,outside_dhaka',
        'payment_method' => 'required|in:bkash,cod',
        'transaction_number' => 'required_if:payment_method,bkash|nullable|string|max:50',
        'sending_number' => 'required_if:payment_method,bkash|nullable|string|max:20',
        'terms_accepted' => 'required|accepted',
        'coupon_code' => 'nullable|string|exists:coupons,code',
    ];

    if (! $isLoggedIn) {
        if ($request->boolean('create_account')) {
            $rules['email'] = 'required|email|max:255';
            $rules['password'] = 'required|string|min:8';
        } else {
            $rules['email'] = 'nullable|email|max:255';
        }
    }

    $validated = $request->validate($rules);

    if ($summary->hasBookingItems && $validated['payment_method'] === 'cod') {
        return back()
            ->with('error', 'Cash on Delivery is not available for pre-order bookings. Please use Mobile Banking (bKash/Nagad).')
            ->withInput();
    }

    DB::beginTransaction();

    try {
        // ... existing user / account-creation block stays exactly as it is,
        // producing $user, $accountCreated, $passwordUpdated ...

        $deliveryCharge = $validated['delivery_location'] === 'inside_dhaka' ? 80 : 120;

        $coupon = null;
        $couponDiscount = 0;

        if (! empty($validated['coupon_code'])) {
            $coupon = Coupon::where('code', strtoupper($validated['coupon_code']))->first();

            if ($coupon && $coupon->isValid()) {
                $couponDiscount = $coupon->calculateDiscount($summary->subtotal);
            }
        }

        $finalTotal = max(0, $summary->subtotal + $deliveryCharge - $couponDiscount);

        $orderEmail = $user?->email ?: ($validated['email'] ?? null);

        $order = Order::create([
            'user_id' => $user?->id,
            'coupon_id' => $coupon?->id,
            'coupon_code' => $coupon?->code,
            'coupon_discount' => $couponDiscount,
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'delivery_location' => $validated['delivery_location'],
            'delivery_charge' => $deliveryCharge,
            'email' => $orderEmail,
            'subtotal' => $summary->subtotal,
            'discount' => $couponDiscount,
            'total' => $finalTotal,
            'payment_method' => $validated['payment_method'],
            'transaction_number' => $validated['transaction_number'] ?? null,
            'sending_number' => $validated['sending_number'] ?? null,
            'status' => 'pending',
            'save_info' => $request->has('save_info'),
            'terms_accepted' => true,
            'notes' => $request->input('notes'),
            'is_preorder_booking' => $summary->hasBookingItems,
            'booking_amount' => $summary->hasBookingItems ? $summary->bookingTotal : null,
        ]);

        if ($coupon) {
            $coupon->incrementUsage();
        }

        foreach ($lines as $line) {
            // Lock the row that actually holds the stock before checking it, so
            // two simultaneous buyers of the last unit cannot both succeed.
            if ($line->variant !== null) {
                $locked = ProductVariant::whereKey($line->variant->id)->lockForUpdate()->first();
                $available = $locked?->is_active ? (int) $locked->quantity : 0;
            } else {
                $locked = Product::whereKey($line->product->id)->lockForUpdate()->first();
                $available = $locked ? (int) $locked->quantity : 0;
            }

            if ($locked === null) {
                throw new \Exception("{$line->name} is no longer available.");
            }

            if ($available < $line->quantity) {
                throw new \Exception("Insufficient stock for {$line->name}. Only {$available} available.");
            }

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $line->product->id,
                'product_variant_id' => $line->variant?->id,
                'product_name' => $line->name,
                'price' => $line->unitPrice,
                'quantity' => $line->quantity,
                'subtotal' => $line->lineTotal(),
            ]);

            $locked->decrement('quantity', $line->quantity);
        }

        DB::commit();

        // ... existing cache clearing, email, notification block stays as it is ...
```

At the end of the try block, replace `\Cart::clear();` with `$this->cart->clear();`.

Add `use App\Models\ProductVariant;` and `use App\Services\CartService;` to the imports, and remove the now-unused `use App\Models\Product;` only if nothing else in the file references it.

- [ ] **Step 5: Update `saveProgress` to use the service**

The cart snapshot loop duplicates price logic too:

```php
$lines = $this->cart->lines();

$cartSnapshot = $lines->map(fn ($line) => [
    'name' => $line->name,
    'quantity' => $line->quantity,
    'price' => $line->unitPrice,
])->values()->all();
```

and `'subtotal' => $this->cart->summary()->subtotal,`.

- [ ] **Step 6: Update `resources/views/checkout/index.blade.php`**

Same substitution as the cart view: `$cartItems` → `$lines`, `$cartSubTotal` → `$summary->subtotal`, `$hasBookableItems` → `$summary->hasBookingItems`, `$totalBookingAmount` → `$summary->bookingTotal`. Delete every reference to `$cartTotal`, `$totalDiscount` and `$finalTotal` — they were never read.

- [ ] **Step 7: Run tests to verify they pass**

Run: `php artisan test --filter=CheckoutTest`
Expected: PASS (7 tests)

- [ ] **Step 8: Run the whole suite**

Run: `php artisan test`
Expected: PASS

- [ ] **Step 9: Commit**

```bash
git add app/Http/Controllers/CheckoutController.php resources/views/checkout/index.blade.php tests/Feature/CheckoutTest.php
git commit -m "fix: make the charged order match the cart, and lock stock at checkout

CheckoutController re-derived every price a third time and used raw price
for bookable items where the cart used display_price, so the two totals
disagreed on any discounted booking item. It also decremented the parent
product for variant sales and ran decrement with no row lock.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

### Task 8: Replace the navbar's inline PHP with a `<x-cart-summary>` component

`actions.blade.php:80-103` runs a `foreach` with `Product::find()` inside a Blade template — the fifth copy of the pricing rule, and the one that breaks outright on composite IDs.

**Files:**
- Create: `app/View/Components/CartSummary.php`
- Create: `resources/views/components/cart-summary.blade.php`
- Modify: `resources/views/home/components/navigation/actions.blade.php:78-107`
- Test: `tests/Feature/NavbarCartSummaryTest.php`

**Interfaces:**
- Consumes: `CartService::summary()` (Task 5)
- Produces: `<x-cart-summary />` Blade component, usable anywhere.

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavbarCartSummaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_navbar_total_matches_the_cart_page_total_for_a_variant(): void
    {
        $product = Product::factory()->withCategory()->create(['price' => 0]);
        $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 450, 'quantity' => 5]);

        $cart = app(CartService::class);
        $cart->add($product, $variant, 2);

        $expected = number_format($cart->summary()->subtotal, 2);

        // The old navbar called Product::find("12_3") and produced a wrong number.
        $this->get(route('cart.index'))->assertSee('৳' . $expected, escape: false);
        $this->get('/')->assertSee('৳' . $expected, escape: false);
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `php artisan test --filter=NavbarCartSummaryTest`
Expected: FAIL — the navbar total is derived by the inline loop and differs.

- [ ] **Step 3: Create the component class**

```php
<?php

namespace App\View\Components;

use App\Services\CartService;
use Illuminate\View\Component;
use Illuminate\View\View;

class CartSummary extends Component
{
    public function __construct(private readonly CartService $cart)
    {
    }

    public function render(): View
    {
        return view('components.cart-summary', [
            'summary' => $this->cart->summary(),
        ]);
    }
}
```

- [ ] **Step 4: Create the component view**

```blade
<div class="flex justify-between items-center mb-3">
    <span class="text-gray-300">Total</span>
    <span class="text-xl font-bold text-violet-400">৳{{ number_format($summary->subtotal, 2) }}</span>
</div>

@if($summary->hasBookingItems)
    <div class="flex justify-between items-center mb-3 text-sm">
        <span class="text-amber-400">Booking due now</span>
        <span class="text-amber-400 font-semibold">৳{{ number_format($summary->bookingTotal, 2) }}</span>
    </div>
@endif
```

- [ ] **Step 5: Replace the inline block in `actions.blade.php`**

Delete the entire `@php ... @endphp` block at lines 79-103 and the `<div class="flex justify-between items-center mb-3">` that followed it, and put in its place:

```blade
<x-cart-summary />
```

- [ ] **Step 6: Run tests to verify they pass**

Run: `php artisan test --filter=NavbarCartSummaryTest`
Expected: PASS

- [ ] **Step 7: Commit**

```bash
git add app/View/Components/CartSummary.php resources/views/components/cart-summary.blade.php resources/views/home/components/navigation/actions.blade.php tests/Feature/NavbarCartSummaryTest.php
git commit -m "refactor: replace the navbar cart loop with an x-cart-summary component

The mini-cart ran Product::find() inside a Blade foreach on composite
cart ids, so its total was wrong for any variant item.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

### Task 9: Migrate the remaining readers off the three booleans

Eleven files still read `is_preorder`, `is_upcoming` or `is_bookable`. They must all move to `availability` / `booking_fee` before Task 10 drops the columns.

**Files:**
- Modify: `app/Http/Controllers/AdminController.php` (validation + store/update for products)
- Modify: `app/Support/DataLayerHelper.php`
- Modify: `resources/views/admin/product-create.blade.php`
- Modify: `resources/views/admin/product-edit.blade.php`
- Modify: `resources/views/admin/orders.blade.php`
- Modify: `resources/views/shop/show.blade.php`
- Modify: `resources/views/home/sections/categories.blade.php`
- Modify: `resources/views/profile/index.blade.php`
- Modify: `resources/views/profile/order-details.blade.php`
- Test: `tests/Feature/NoLegacyBooleanReadsTest.php`

**Interfaces:**
- Consumes: `Product::AVAILABILITY_*`, `Product::requiresBooking()`, `Product::isPurchasable()` (Task 3)
- Produces: no new interface — this is the migration step that makes Task 10 safe.

- [ ] **Step 1: Write the guard test**

This test is the gate for Task 10 — it fails until every reader has moved.

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class NoLegacyBooleanReadsTest extends TestCase
{
    public function test_no_application_code_reads_the_legacy_product_booleans(): void
    {
        $roots = [base_path('app'), base_path('resources/views'), base_path('routes')];
        $offenders = [];

        foreach ($roots as $root) {
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root));

            foreach ($files as $file) {
                if (! $file->isFile() || $file->getExtension() !== 'php') {
                    continue;
                }

                $contents = file_get_contents($file->getPathname());

                // orders.is_preorder_booking is a DIFFERENT column and stays.
                $contents = str_replace('is_preorder_booking', '', $contents);

                foreach (['is_preorder', 'is_upcoming', 'is_bookable'] as $legacy) {
                    if (str_contains($contents, $legacy)) {
                        $offenders[] = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file->getPathname()) . " => {$legacy}";
                    }
                }
            }
        }

        $this->assertSame([], array_unique($offenders), "Legacy product booleans still read in:\n" . implode("\n", array_unique($offenders)));
    }
}
```

- [ ] **Step 2: Run to see the full list of offenders**

Run: `php artisan test --filter=test_no_application_code_reads_the_legacy_product_booleans`
Expected: FAIL, listing every file still to migrate. Work the list.

- [ ] **Step 3: Migrate the admin product forms**

In `product-create.blade.php` and `product-edit.blade.php`, replace the three checkboxes with one select and one number input:

```blade
<div>
    <label for="availability" class="block text-sm font-medium text-gray-300 mb-2">Availability</label>
    <select name="availability" id="availability"
            class="w-full px-4 py-2 bg-black/50 border border-violet-500/30 rounded-lg text-white">
        <option value="in_stock" @selected(old('availability', $product->availability ?? 'in_stock') === 'in_stock')>In stock</option>
        <option value="preorder" @selected(old('availability', $product->availability ?? '') === 'preorder')>Pre-order</option>
        <option value="upcoming" @selected(old('availability', $product->availability ?? '') === 'upcoming')>Upcoming (not purchasable)</option>
    </select>
</div>

<div>
    <label for="booking_fee" class="block text-sm font-medium text-gray-300 mb-2">
        Booking fee (৳) — leave blank if no booking required
    </label>
    <input type="number" step="0.01" min="0" name="booking_fee" id="booking_fee"
           value="{{ old('booking_fee', $product->booking_fee ?? '') }}"
           class="w-full px-4 py-2 bg-black/50 border border-violet-500/30 rounded-lg text-white">
</div>
```

In `product-create.blade.php` the `$product` variable does not exist, so use `old('availability', 'in_stock')` and `old('booking_fee')` directly.

- [ ] **Step 4: Migrate `AdminController` product validation**

In `productStore` and `productUpdate`, replace the three boolean rules with:

```php
'availability' => 'required|in:in_stock,preorder,upcoming',
'booking_fee' => 'nullable|numeric|min:0',
```

and remove `is_preorder`, `is_upcoming` and `is_bookable` from the validated array and from any `$validated[...] = $request->boolean(...)` lines. A blank `booking_fee` must be stored as `null`, not `0`:

```php
$validated['booking_fee'] = $request->filled('booking_fee') ? (float) $request->input('booking_fee') : null;
```

- [ ] **Step 5: Migrate the storefront and profile views**

Mechanical substitution:

| Old | New |
|---|---|
| `$product->is_upcoming` | `$product->availability === \App\Models\Product::AVAILABILITY_UPCOMING` |
| `$product->is_preorder` | `$product->availability === \App\Models\Product::AVAILABILITY_PREORDER` |
| `$product->is_bookable` | `$product->requiresBooking()` |
| `$item->attributes->is_bookable` | `$line->requiresBooking()` |

In `profile/index.blade.php` and `profile/order-details.blade.php` the `is_bookable` references sit on **order** data, not cart data — check each one: if it is describing a placed order, it should read `$order->is_preorder_booking`, which is unchanged.

In `DataLayerHelper.php`, `$item->attributes->category` is a cart attribute and stays; only the legacy boolean reads change.

- [ ] **Step 6: Run the guard test until it passes**

Run: `php artisan test --filter=test_no_application_code_reads_the_legacy_product_booleans`
Expected: PASS

- [ ] **Step 7: Run the whole suite**

Run: `php artisan test`
Expected: PASS

- [ ] **Step 8: Commit**

```bash
git add app/ resources/views/ tests/Feature/NoLegacyBooleanReadsTest.php
git commit -m "refactor: move all readers off is_preorder/is_upcoming/is_bookable

Adds a guard test that fails if any of the three legacy booleans is read
from app, views or routes, so the drop migration is safe.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

### Task 10: Drop the three legacy booleans

A separate migration from the one that added their replacements, so a bad deploy rolls back without data loss.

**Files:**
- Create: `database/migrations/2026_09_19_100200_drop_legacy_flags_from_products_table.php`
- Modify: `app/Models/Product.php`
- Modify: `database/factories/ProductFactory.php`
- Test: `tests/Feature/ProductAvailabilityTest.php` (add one case)

**Interfaces:**
- Consumes: the guard test from Task 9 must be passing before this runs.
- Produces: `products` no longer has `is_preorder`, `is_upcoming`, `is_bookable`.

- [ ] **Step 1: Write the failing test**

Append to `tests/Feature/ProductAvailabilityTest.php`:

```php
public function test_the_legacy_boolean_columns_are_gone(): void
{
    $this->assertFalse(\Schema::hasColumn('products', 'is_preorder'));
    $this->assertFalse(\Schema::hasColumn('products', 'is_upcoming'));
    $this->assertFalse(\Schema::hasColumn('products', 'is_bookable'));
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `php artisan test --filter=test_the_legacy_boolean_columns_are_gone`
Expected: FAIL — the columns still exist.

- [ ] **Step 3: Create the drop migration**

The `down()` restores both the columns and their values from `availability` / `booking_fee`, so the rollback is genuinely reversible.

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['is_preorder', 'is_upcoming', 'is_bookable']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_preorder')->default(false);
            $table->boolean('is_upcoming')->default(false);
            $table->boolean('is_bookable')->default(false);
        });

        DB::table('products')->where('availability', 'upcoming')->update(['is_upcoming' => true]);
        DB::table('products')->where('availability', 'preorder')->update(['is_preorder' => true]);
        DB::table('products')->whereNotNull('booking_fee')->update(['is_bookable' => true]);
    }
};
```

- [ ] **Step 4: Remove the columns from the model and factory**

In `app/Models/Product.php`, delete `'is_preorder'`, `'is_upcoming'`, `'is_bookable'` from `$fillable` and from `$casts`.

In `database/factories/ProductFactory.php`, delete the same three keys from `definition()`.

- [ ] **Step 5: Run the whole suite**

Run: `php artisan test`
Expected: PASS

- [ ] **Step 6: Commit**

```bash
git add database/migrations/2026_09_19_100200_drop_legacy_flags_from_products_table.php app/Models/Product.php database/factories/ProductFactory.php tests/Feature/ProductAvailabilityTest.php
git commit -m "refactor: drop is_preorder, is_upcoming and is_bookable

Separate from the migration that added availability and booking_fee so a
bad deploy rolls back without data loss. down() restores both columns and
their values.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

### Task 11: Open variants to every category in admin

`AdminController.php:938` gates the variant repeater behind `if ($request->category_id == $keychainsCategoryId`. Only keychains can have variants.

**Files:**
- Modify: `app/Http/Controllers/AdminController.php` (`productStore` ~line 885-975, `productUpdate` ~line 1007-1090)
- Modify: `resources/views/admin/product-create.blade.php`
- Modify: `resources/views/admin/product-edit.blade.php`
- Test: `tests/Feature/Admin/ProductVariantAdminTest.php`

**Interfaces:**
- Consumes: variant columns (Task 2), `availability`/`booking_fee` (Tasks 3, 9)
- Produces: admin can create and edit variants with `name`, `sku`, `price`, `sale_price`, `quantity`, `is_active` for any category.

- [ ] **Step 1: Write the failing tests**

```php
<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductVariantAdminTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        // User::isAdmin() is email equality - admin routes use the 'admin'
        // middleware, which checks exactly this.
        return User::factory()->create(['email' => 'ifti3061@gmail.com']);
    }

    public function test_a_non_keychain_category_can_have_variants(): void
    {
        $category = Category::factory()->create(['name' => 'Knives', 'slug' => 'knives']);

        $this->actingAs($this->admin())->post(route('admin.products.store'), [
            'name' => 'RGX Butterfly',
            'price' => 0,
            'quantity' => 0,
            'category_id' => $category->id,
            'availability' => 'in_stock',
            'is_active' => 1,
            'new_variants' => [
                ['name' => 'Blue', 'price' => 450, 'quantity' => 5, 'sku' => 'RGX-BLU'],
                ['name' => 'Red', 'price' => 500, 'quantity' => 3, 'sku' => 'RGX-RED'],
            ],
        ]);

        $product = Product::where('name', 'RGX Butterfly')->first();

        $this->assertNotNull($product, 'product was not created');
        $this->assertCount(2, $product->variants);
        $this->assertSame(5, $product->variants->firstWhere('name', 'Blue')->quantity);
    }

    public function test_variant_stock_can_be_edited(): void
    {
        $product = Product::factory()->withCategory()->create();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'quantity' => 5]);

        $this->actingAs($this->admin())->put(route('admin.products.update', $product), [
            'name' => $product->name,
            'price' => $product->price,
            'quantity' => $product->quantity,
            'category_id' => $product->category_id,
            'availability' => 'in_stock',
            'is_active' => 1,
            'variants' => [
                $variant->id => ['name' => 'Blue', 'price' => 450, 'quantity' => 12, 'is_active' => 1],
            ],
        ]);

        $this->assertSame(12, $variant->refresh()->quantity);
    }

    public function test_a_variant_can_be_deactivated_without_deleting_it(): void
    {
        $product = Product::factory()->withCategory()->create();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'is_active' => true]);

        $this->actingAs($this->admin())->put(route('admin.products.update', $product), [
            'name' => $product->name,
            'price' => $product->price,
            'quantity' => $product->quantity,
            'category_id' => $product->category_id,
            'availability' => 'in_stock',
            'is_active' => 1,
            'variants' => [
                $variant->id => ['name' => $variant->name, 'price' => $variant->price, 'quantity' => 4, 'is_active' => 0],
            ],
        ]);

        $this->assertFalse($variant->refresh()->is_active);
        $this->assertDatabaseHas('product_variants', ['id' => $variant->id]);
    }
}
```

- [ ] **Step 2: Run to verify they fail**

Run: `php artisan test --filter=ProductVariantAdminTest`
Expected: FAIL — the keychain gate rejects variants for other categories.

- [ ] **Step 3: Remove the gate and extend validation in `productStore`**

Replace the `new_variants` validation rules with:

```php
'new_variants' => 'nullable|array',
'new_variants.*.name' => 'nullable|string|max:255',
'new_variants.*.sku' => 'nullable|string|max:100',
'new_variants.*.price' => 'nullable|numeric|min:0',
'new_variants.*.sale_price' => 'nullable|numeric|min:0',
'new_variants.*.quantity' => 'nullable|integer|min:0',
'new_variants.*.is_active' => 'nullable|boolean',
'new_variants.*.images' => 'nullable|array',
'new_variants.*.images.*' => 'image|mimes:jpeg,png,jpg,gif|max:10240',
```

Replace the gate `if ($request->category_id == $keychainsCategoryId && is_array($request->new_variants)) {` with:

```php
if (is_array($request->new_variants)) {
```

and the variant creation call with:

```php
$variant = $product->variants()->create([
    'name' => $v['name'],
    'sku' => $v['sku'] ?? null,
    'price' => (float) ($v['price'] ?? 0),
    'sale_price' => isset($v['sale_price']) && $v['sale_price'] !== '' ? (float) $v['sale_price'] : null,
    'quantity' => (int) ($v['quantity'] ?? 0),
    'is_active' => (bool) ($v['is_active'] ?? true),
    'sort_order' => $idx,
]);
```

Delete the now-unused `$keychainsCategoryId` lookups at lines 861, 925, 981 and 1032.

- [ ] **Step 4: Apply the same changes in `productUpdate`**

Mirror the validation rules for both `variants.*` and `new_variants.*`, and replace the existing-variant update call with:

```php
$variant->update([
    'name' => $v['name'] ?? $variant->name,
    'sku' => $v['sku'] ?? $variant->sku,
    'price' => isset($v['price']) ? (float) $v['price'] : $variant->price,
    'sale_price' => isset($v['sale_price']) && $v['sale_price'] !== '' ? (float) $v['sale_price'] : null,
    'quantity' => isset($v['quantity']) ? (int) $v['quantity'] : $variant->quantity,
    'is_active' => (bool) ($v['is_active'] ?? false),
]);
```

- [ ] **Step 5: Add the new fields to both admin forms**

In the variant repeater row in `product-create.blade.php` and `product-edit.blade.php`, add alongside the existing name and price inputs:

```blade
<input type="text" name="new_variants[{{ $i }}][sku]" placeholder="SKU"
       class="px-3 py-2 bg-black/50 border border-violet-500/30 rounded text-white">

<input type="number" step="0.01" min="0" name="new_variants[{{ $i }}][sale_price]" placeholder="Sale price"
       class="px-3 py-2 bg-black/50 border border-violet-500/30 rounded text-white">

<input type="number" min="0" name="new_variants[{{ $i }}][quantity]" placeholder="Stock" value="0"
       class="px-3 py-2 bg-black/50 border border-violet-500/30 rounded text-white">

<label class="flex items-center gap-2 text-gray-300">
    <input type="hidden" name="new_variants[{{ $i }}][is_active]" value="0">
    <input type="checkbox" name="new_variants[{{ $i }}][is_active]" value="1" checked>
    Active
</label>
```

Use `variants[{{ $variant->id }}][...]` for the existing-variant rows in `product-edit.blade.php`, with `value="{{ $variant->sku }}"` etc.

Also remove any `@if($product->category_id == $keychainsCategoryId)` wrapper around the repeater so it shows for every category.

- [ ] **Step 6: Run tests to verify they pass**

Run: `php artisan test --filter=ProductVariantAdminTest`
Expected: PASS (3 tests)

- [ ] **Step 7: Commit**

```bash
git add app/Http/Controllers/AdminController.php resources/views/admin/product-create.blade.php resources/views/admin/product-edit.blade.php tests/Feature/Admin/ProductVariantAdminTest.php
git commit -m "feat: allow variants on any category, with stock and sku

Removes the hardcoded keychains-only gate so RGX Butterfly can have
colour variants the same way keychains have design variants.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

### Task 12: Swatch selector on the product page

The `<select>` at `show.blade.php:137` gives no indication which options are sold out, and the page always preselects the first variant even when it has no stock.

**Files:**
- Modify: `resources/views/shop/show.blade.php:73-84, 133-175, 230-245, 300-360`
- Modify: `app/Http/Controllers/ShopController.php` (`show` — eager loads)
- Test: `tests/Feature/ProductPageTest.php`

**Interfaces:**
- Consumes: `Product::activeVariants()` (Task 2), `PricingService` (Task 4)
- Produces: the add-to-cart form posts `variant_id` for the selected swatch.

- [ ] **Step 1: Write the failing tests**

```php
<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_active_variant_renders_as_a_swatch(): void
    {
        $product = Product::factory()->withCategory()->create(['price' => 0]);
        ProductVariant::factory()->create(['product_id' => $product->id, 'name' => 'Blue', 'price' => 450, 'quantity' => 5]);
        ProductVariant::factory()->create(['product_id' => $product->id, 'name' => 'Red', 'price' => 500, 'quantity' => 2]);

        $this->get(route('shop.show', $product))
            ->assertOk()
            ->assertSee('Blue')
            ->assertSee('Red');
    }

    public function test_a_sold_out_variant_renders_disabled(): void
    {
        $product = Product::factory()->withCategory()->create(['price' => 0]);
        ProductVariant::factory()->create(['product_id' => $product->id, 'name' => 'Gone', 'price' => 450, 'quantity' => 0]);

        $this->get(route('shop.show', $product))
            ->assertOk()
            ->assertSee('data-in-stock="0"', escape: false);
    }

    public function test_an_inactive_variant_is_not_shown_at_all(): void
    {
        $product = Product::factory()->withCategory()->create(['price' => 0]);
        ProductVariant::factory()->create(['product_id' => $product->id, 'name' => 'Visible', 'price' => 450, 'quantity' => 5, 'is_active' => true]);
        ProductVariant::factory()->create(['product_id' => $product->id, 'name' => 'Hidden', 'price' => 450, 'quantity' => 5, 'is_active' => false]);

        $this->get(route('shop.show', $product))
            ->assertOk()
            ->assertSee('Visible')
            ->assertDontSee('Hidden');
    }
}
```

- [ ] **Step 2: Run to verify they fail**

Run: `php artisan test --filter=ProductPageTest`
Expected: FAIL — inactive variants render and nothing marks stock.

- [ ] **Step 3: Eager-load active variants in `ShopController::show`**

```php
public function show(Product $product): View
{
    if (! $product->is_active) {
        abort(404);
    }

    $product->load(['images', 'category', 'activeVariants.images']);

    return view('shop.show', compact('product'));
}
```

- [ ] **Step 4: Replace the `<select>` with swatches**

Replace lines 133-145 of `show.blade.php`:

```blade
@php
    $variants = $product->activeVariants;
    $hasVariants = $variants->isNotEmpty();
    // Never preselect a sold-out option.
    $defaultVariant = $variants->firstWhere('quantity', '>', 0);
@endphp

@if($hasVariants)
<div class="space-y-3">
    <label class="block text-sm font-medium text-gray-400">Choose an option</label>

    <div class="flex flex-wrap gap-3" id="variant-swatches">
        @foreach($variants as $v)
            @php $inStock = $v->quantity > 0; @endphp
            <button type="button"
                    class="variant-swatch group relative px-4 py-3 rounded-lg border-2 transition
                           {{ $inStock ? 'border-violet-500/30 hover:border-violet-500 cursor-pointer' : 'border-gray-700 opacity-40 cursor-not-allowed' }}
                           {{ $defaultVariant && $defaultVariant->id === $v->id ? 'border-violet-500 bg-violet-500/10' : '' }}"
                    data-variant-id="{{ $v->id }}"
                    data-price="{{ $v->sale_price && $v->sale_price < $v->price ? $v->sale_price : $v->price }}"
                    data-compare-at="{{ $v->sale_price && $v->sale_price < $v->price ? $v->price : '' }}"
                    data-in-stock="{{ $inStock ? 1 : 0 }}"
                    data-stock="{{ $v->quantity }}"
                    data-images="{{ $v->images->map(fn($i) => asset('storage/'.$i->path))->values()->toJson() }}"
                    @disabled(! $inStock)>
                @if($v->images->isNotEmpty())
                    <img src="{{ asset('storage/' . $v->images->first()->path) }}" alt="{{ $v->name }}"
                         class="w-12 h-12 object-cover rounded mb-1">
                @endif
                <span class="block text-sm {{ $inStock ? 'text-white' : 'text-gray-500 line-through' }}">{{ $v->name }}</span>
                <span class="block text-xs text-violet-300">৳{{ number_format($v->sale_price ?? $v->price, 2) }}</span>
                @unless($inStock)
                    <span class="block text-[10px] text-red-400 uppercase tracking-wide">Sold out</span>
                @endunless
            </button>
        @endforeach
    </div>

    @unless($defaultVariant)
        <p class="text-sm text-red-400">Every option is currently sold out.</p>
    @endunless
</div>
@endif
```

- [ ] **Step 5: Point the add-to-cart form at the selected swatch**

Replace the hidden input at line 238:

```blade
<input type="hidden" name="variant_id" id="form_variant_id" value="{{ $defaultVariant?->id }}">
```

and disable the submit button when no variant is selectable:

```blade
<button type="submit" id="add-to-cart-btn"
        @disabled($hasVariants && ! $defaultVariant)
        class="... disabled:opacity-40 disabled:cursor-not-allowed">
    Add to Cart
</button>
```

- [ ] **Step 6: Replace the select's JS handler**

Replace the `variantSelect` block at lines 307-360:

```javascript
const swatches = document.querySelectorAll('.variant-swatch');
const formVariantInput = document.getElementById('form_variant_id');
const variantPriceEl = document.getElementById('variant-price');
const addToCartBtn = document.getElementById('add-to-cart-btn');
const qtyInput = document.querySelector('input[name="quantity"]');

const formatTaka = (value) =>
    '৳' + parseFloat(value).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');

swatches.forEach((swatch) => {
    swatch.addEventListener('click', function () {
        if (this.dataset.inStock !== '1') return;

        swatches.forEach((s) => s.classList.remove('border-violet-500', 'bg-violet-500/10'));
        this.classList.add('border-violet-500', 'bg-violet-500/10');

        if (formVariantInput) formVariantInput.value = this.dataset.variantId;
        if (variantPriceEl) variantPriceEl.textContent = formatTaka(this.dataset.price);
        if (addToCartBtn) addToCartBtn.disabled = false;

        // Never let the quantity box exceed this variant's stock.
        if (qtyInput) {
            qtyInput.max = this.dataset.stock;
            if (parseInt(qtyInput.value, 10) > parseInt(this.dataset.stock, 10)) {
                qtyInput.value = this.dataset.stock;
            }
        }

        const images = JSON.parse(this.dataset.images || '[]');
        if (images.length) swapGallery(images);
    });
});
```

Keep whatever the existing `swapGallery` logic is called in this file — if the gallery swap is inline in the old handler, extract it into a `swapGallery(images)` function first so both paths share it.

- [ ] **Step 7: Run tests to verify they pass**

Run: `php artisan test --filter=ProductPageTest`
Expected: PASS (3 tests)

- [ ] **Step 8: Commit**

```bash
git add resources/views/shop/show.blade.php app/Http/Controllers/ShopController.php tests/Feature/ProductPageTest.php
git commit -m "feat: swatch variant selector with sold-out states

Replaces the plain select, which gave no indication which options were
sold out and always preselected the first one even with zero stock.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

### Task 13: Price ranges and aggregate sold-out on the shop cards

**Files:**
- Modify: `resources/views/shop/partials/product-grid.blade.php:65-90`
- Modify: `resources/views/shop/partials/product-mobile.blade.php:103-128`
- Modify: `app/Http/Controllers/ShopController.php` (eager loads in both query branches)
- Test: `tests/Feature/ShopListingTest.php`

**Interfaces:**
- Consumes: `Product::availableStock()` (Task 2), `Product::activeVariants()` (Task 2)
- Produces: no new interface.

- [ ] **Step 1: Write the failing tests**

```php
<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_variant_product_shows_a_from_price(): void
    {
        $product = Product::factory()->withCategory()->create(['name' => 'RGX Butterfly', 'price' => 0]);
        ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 450, 'quantity' => 5]);
        ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 700, 'quantity' => 5]);

        $this->get(route('shop.index'))
            ->assertOk()
            ->assertSee('From')
            ->assertSee('450.00');
    }

    public function test_variants_at_one_price_show_a_single_price(): void
    {
        $product = Product::factory()->withCategory()->create(['price' => 0]);
        ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 450, 'quantity' => 5]);
        ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 450, 'quantity' => 5]);

        $this->get(route('shop.index'))
            ->assertOk()
            ->assertDontSee('From');
    }

    public function test_sold_out_shows_only_when_every_variant_is_out(): void
    {
        $partial = Product::factory()->withCategory()->create(['name' => 'Partly Available', 'price' => 0]);
        ProductVariant::factory()->create(['product_id' => $partial->id, 'price' => 450, 'quantity' => 0]);
        ProductVariant::factory()->create(['product_id' => $partial->id, 'price' => 450, 'quantity' => 3]);

        $gone = Product::factory()->withCategory()->create(['name' => 'All Gone', 'price' => 0]);
        ProductVariant::factory()->create(['product_id' => $gone->id, 'price' => 450, 'quantity' => 0]);

        $html = $this->get(route('shop.index'))->assertOk()->getContent();

        $this->assertStringContainsString('All Gone', $html);
        $this->assertStringContainsString('Partly Available', $html);
        $this->assertSame(1, substr_count($html, 'Sold Out'), 'only the fully sold-out product should carry the badge');
    }
}
```

- [ ] **Step 2: Run to verify they fail**

Run: `php artisan test --filter=ShopListingTest`
Expected: FAIL — cards show the product price and use `quantity` for the badge.

- [ ] **Step 3: Eager-load active variants in `ShopController::index`**

In **both** the cached branch and the filtered branch, change `Product::with('images', 'category', 'variants')` to:

```php
Product::with('images', 'category', 'activeVariants')
```

- [ ] **Step 4: Update the card price and badge in `product-grid.blade.php`**

Replace the `$hasVariants` block at line 71:

```blade
@php
    $variants = $product->activeVariants;
    $hasVariants = $variants->isNotEmpty();
    $variantPrices = $variants->map(fn ($v) => (float) ($v->sale_price ?? $v->price));
    $minPrice = $variantPrices->min();
    $maxPrice = $variantPrices->max();
    $isSoldOut = $product->availableStock() < 1;
@endphp

@if($isSoldOut)
    <span class="absolute top-3 right-3 px-3 py-1 bg-red-500/90 text-white text-xs font-bold rounded-full">Sold Out</span>
@endif

<div class="text-violet-400 font-bold">
    @if($hasVariants)
        @if($minPrice < $maxPrice)
            <span class="text-xs text-gray-400">From</span> ৳{{ number_format($minPrice, 2) }}
        @else
            ৳{{ number_format($minPrice, 2) }}
        @endif
    @elseif($product->price_tba)
        <span class="text-yellow-400 text-sm">Price TBA</span>
    @else
        ৳{{ number_format($product->display_price ?? $product->price, 2) }}
    @endif
</div>
```

Keep the existing `{{-- Keychains and multi-variant products need the PDP to pick options --}}` branch that routes variant products to the product page rather than quick-adding.

- [ ] **Step 5: Apply the identical change to `product-mobile.blade.php`**

Same block, at line 109. Repeat the code rather than extracting a partial — the two cards have different surrounding markup and diverging them for a shared partial is a bigger change than this task warrants.

- [ ] **Step 6: Run tests to verify they pass**

Run: `php artisan test --filter=ShopListingTest`
Expected: PASS (3 tests)

- [ ] **Step 7: Commit**

```bash
git add resources/views/shop/partials/ app/Http/Controllers/ShopController.php tests/Feature/ShopListingTest.php
git commit -m "feat: show From-prices and per-variant sold-out state on shop cards

Cards previously showed the parent price (often 0 for variant products)
and derived Sold Out from the parent quantity.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

### Task 14: Delete the dead code and fix the stale shop cache

The listing cache is keyed on product rows only, so a variant selling out leaves a stale "In Stock" badge for up to 30 minutes. The dead views and unused variables go in the same pass.

**Files:**
- Delete: `resources/views/shop/index-mobile.blade.php`
- Delete: `resources/views/shop/mobile.blade.php`
- Modify: `app/Models/ProductVariant.php`
- Modify: `app/Models/Product.php`
- Test: `tests/Feature/ShopCacheTest.php`

**Interfaces:**
- Consumes: `Product::clearShopListingCache()` (existing)
- Produces: variant writes bust the shop listing cache.

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ShopCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_variant_stock_change_busts_the_shop_listing_cache(): void
    {
        $product = Product::factory()->withCategory()->create(['price' => 0]);
        $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 450, 'quantity' => 5]);

        Cache::put('shop_products_page_12_1', 'stale', 1800);

        $variant->update(['quantity' => 0]);

        $this->assertNull(Cache::get('shop_products_page_12_1'));
    }

    public function test_creating_a_variant_busts_the_cache(): void
    {
        $product = Product::factory()->withCategory()->create(['price' => 0]);

        Cache::put('shop_products_page_12_1', 'stale', 1800);

        ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 450, 'quantity' => 5]);

        $this->assertNull(Cache::get('shop_products_page_12_1'));
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `php artisan test --filter=ShopCacheTest`
Expected: FAIL — only `Product` writes bust the cache.

- [ ] **Step 3: Hook the variant model into the cache bust**

Add to `app/Models/ProductVariant.php`:

```php
protected static function booted(): void
{
    // Variant stock and price drive the shop card's From-price and its
    // sold-out badge, so a variant write has to invalidate the same cached
    // listing pages a product write does.
    static::saved(function (ProductVariant $variant) {
        if ($variant->wasRecentlyCreated || $variant->wasChanged(['quantity', 'price', 'sale_price', 'is_active'])) {
            Product::clearShopListingCache();
        }
    });

    static::deleted(fn () => Product::clearShopListingCache());
}
```

- [ ] **Step 4: Also clear the sibling caches that were being missed**

`clearShopListingCache` forgets the 30 paginated keys but leaves `shop_category_counts` and `shop_price_range` stale for an hour. Extend it in `app/Models/Product.php`:

```php
public static function clearShopListingCache(): void
{
    foreach ([12, 24, 48] as $perPage) {
        for ($page = 1; $page <= 10; $page++) {
            \Illuminate\Support\Facades\Cache::forget("shop_products_page_{$perPage}_{$page}");
        }
    }

    // These are cached for an hour and go stale on the same writes.
    \Illuminate\Support\Facades\Cache::forget('shop_category_counts');
    \Illuminate\Support\Facades\Cache::forget('shop_price_range');
}
```

- [ ] **Step 5: Delete the dead views**

```bash
git rm resources/views/shop/index-mobile.blade.php resources/views/shop/mobile.blade.php
```

Neither is referenced by any route, controller or view — confirm before deleting:

Run: `grep -rn "shop.mobile\|index-mobile" routes/ app/ resources/`
Expected: no output.

- [ ] **Step 6: Run the whole suite**

Run: `php artisan test`
Expected: PASS — every test from every task.

- [ ] **Step 7: Commit**

```bash
git add app/Models/ProductVariant.php app/Models/Product.php
git commit -m "fix: bust the shop cache on variant writes, drop dead mobile views

A variant selling out left a stale In Stock badge for up to 30 minutes,
and the category-count and price-range caches were never cleared at all.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

## Self-Review

**Spec coverage.** Every section of Plan 1's scope maps to a task:

| Spec requirement | Task |
|---|---|
| Variant `sku` / `quantity` / `sale_price` / `is_active` + backfill | 2 |
| `availability` + `booking_fee` replacing three booleans | 3, 9, 10 |
| `PricingService` single precedence rule | 4 |
| `CartService`, composite IDs opaque, N+1 removed, legacy session guard | 5 |
| All five pricing call sites migrated | 6, 7, 8 |
| Stock resolves to variant; `lockForUpdate` at checkout | 2, 7 |
| Variant re-validated at checkout | 7 |
| Aggregate stock from active variants | 2, 13 |
| PDP swatches with sold-out disabled | 12 |
| Shop card From-price and aggregate sold-out | 13 |
| Cart shows variant image and name | 6 |
| Admin keychain gate removed, per-variant fields | 11 |
| Dead vars (`$cartTotal`, `$totalDiscount`, `$finalTotal`), duplicate loop, dead views | 6, 7, 14 |
| Test coverage for the 6 in-scope spec test cases | 1, 5, 7, 8 |

Spec test cases 8 (slug routing) and 9 (review permissions) belong to Plans 2 and 3 and are deliberately absent.

**Deferred to Plan 2 / 3, tracked here so nothing is lost:** `products.slug`, dropping the legacy `category` enum, the `admin/analytics.blade.php:238` `{{ $product->category }}` fix, deleting the category-repair console commands, and the whole reviews subsystem including the `manual_rating` rename.

**Type consistency check.** `availableStock()` takes `?ProductVariant` and returns `int` in Tasks 2, 5, 7, 13. `priceFor()` returns `float` everywhere. `CartLine::requiresBooking()` is defined in Task 5 and used in Tasks 6, 7, 8, 9. `Product::AVAILABILITY_UPCOMING` is defined in Task 3 and used in Tasks 5, 9. `CartService::keyFor()` is public because Task 6 calls it. `activeVariants()` is defined in Task 2 and used in Tasks 12, 13.

**Auth scheme verified.** Admin routes are `Route::middleware(['auth', 'admin'])->prefix('admin')`, and `User::isAdmin()` is `$this->email === 'ifti3061@gmail.com'`. The `ProductVariantAdminTest::admin()` helper matches this exactly — no adjustment needed.

**Known risk carried from the spec.** The Task 2 variant stock backfill copies the parent quantity onto every variant because no per-variant sales history exists to reconstruct true counts from. This deliberately overstates stock. **Inventory must be reconciled in admin after deploy.** No automated answer exists for this.
