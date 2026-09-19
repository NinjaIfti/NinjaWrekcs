# Product Variants & Cart Redesign

**Status:** Approved design, ready for implementation planning
**Date:** 2026-09-19

## Goal

Make product variants first-class across the whole store, the way a real
ecommerce site works: one product name ("RGX Butterfly") with its own colour
variants, or one "Keychain" product holding many keychain designs. Each variant
carries its own price, stock, images and availability.

Variants already exist in the schema but are crippled — they are locked to a
single hardcoded category, they have no stock of their own, and their price does
not survive the trip from product page to charged order. Fixing that requires
touching the pricing path, so this redesign also consolidates the duplicated
pricing logic and cleans up the schema debt sitting in the same code.

## Problems this solves

Each of these was confirmed by reading the code, not inferred.

### Variants are not sellable units

1. **Locked to one category.** `AdminController::productStore` gates the variant
   repeater behind `if ($request->category_id == $keychainsCategoryId`
   (`AdminController.php:938`). No other category can have variants.
2. **No variant stock.** `product_variants` holds only `name`, `price`,
   `sort_order`. Quantity lives on the parent `products` row, so a single colour
   can never be sold out, and checkout decrements the parent regardless of which
   variant was bought.
3. **No variant availability.** A variant cannot be hidden without deleting it,
   which would orphan its order history.

### The price does not survive to the order

4. **The pricing rule is reimplemented five times**, and they disagree:
   - `CartController::index` (cart page subtotal)
   - `CheckoutController::index` (checkout page subtotal)
   - `CheckoutController::store` (the amount actually charged)
   - `home/components/navigation/actions.blade.php:80-103` (navbar mini-cart) —
     raw PHP with DB queries inside a Blade template
   - `CartController::add` (the price snapshotted into the cart)

   For pre-order items the cart uses `display_price` but `CheckoutController::store`
   uses raw `price`, so **the cart total and the charged total differ** whenever a
   bookable item is on offer.

5. **Composite cart IDs are parsed by hand in five places.** Variant cart items
   are keyed `"12_3"`. Some call sites `explode('_')`; `CheckoutController.php:187`
   and `:277` pass the composite string straight into `Product::find()`, as does
   the navbar partial. The cart item already carries `attributes.product_id` and
   `attributes.variant_id` — they are written but never read.

6. **The booking fee is applied inconsistently.** `CartController.php:129`
   subtracts a hardcoded 200 from the item price, but only when there is no
   variant and only when the price exceeds 200. Checkout adds it back as
   `booking_amount`. A bookable product that has variants is priced wrong.

7. **No stock lock at checkout.** `$product->decrement('quantity', ...)` runs with
   no `lockForUpdate`, so two concurrent buyers of the last unit both succeed.

8. **N+1 queries.** `Product::find()` is called inside `foreach` loops over cart
   items, in three separate controller loops plus the navbar template.

### Schema debt in the same code path

9. **`products.slug` does not exist.** Code reads `$product->slug ?? $product->id`
   in the sitemap (`routes/web.php:127`) and in cart attributes. It silently falls
   back to IDs every time.
10. **Two category systems.** The legacy `category` enum
    (`figures`/`knives`/`stickers`) sits beside `category_id`, with a string-match
    fallback in `Product.php:105-112`.
11. **Four overlapping booleans** — `is_preorder`, `is_upcoming`, `price_tba`,
    `is_bookable` — with the booking amount hardcoded as `200` in three files.
12. **`products.rating` / `products.reviews` are admin-typed integers** with no
    connection to the `reviews` table, which itself has no `product_id` and is a
    site-wide testimonial list. The shop's "Popular" sort
    (`ShopController.php:163`) orders by hand-typed numbers.

### Dead weight

13. `$cartTotal` is computed via `\Cart::getTotal()` in both `CartController::index`
    and `CheckoutController::index` and passed to both views. Neither view uses it.
14. `$totalDiscount` and `$finalTotal` (`CheckoutController.php:69-70`) are
    hardcoded to `0` and `$cartSubTotal`, passed to the view, never referenced.
15. `CheckoutController::store` computes `$hasBookableItems` and
    `$totalBookingAmount` in a full loop at `:180`, discards them, and recomputes
    them in an identical loop at `:262`.
16. `resources/views/shop/index-mobile.blade.php` (0 bytes) and
    `resources/views/shop/mobile.blade.php` (559 lines) are referenced by no
    route, controller or view.

## Non-goals

- Multi-axis option matrices (Size × Color). Variants are a flat single-axis list.
  The schema does not preclude adding a matrix later, but nothing here builds for it.
- Persisting carts across devices or sessions. The cart stays session-backed.
- Restyling the shop, product or cart pages beyond what variant selection requires.
- Any change to payment methods, couriers or the admin order workflow.

## Design

### 1. Schema

#### `product_variants` — becomes a sellable unit

| Column | Type | Purpose |
|---|---|---|
| `sku` | string, nullable, unique | Inventory tracking per variant |
| `quantity` | unsigned int, default 0 | Per-variant stock |
| `sale_price` | decimal(10,2), nullable | A single variant can go on offer |
| `is_active` | boolean, default true | Hide a variant without orphaning order history |

Existing columns (`product_id`, `name`, `price`, `sort_order`) are unchanged.

**Backfill:** there is no per-variant sales history to reconstruct true counts
from, so the migration copies the parent `products.quantity` onto *each* variant
and sets `is_active = true`. This intentionally overstates total stock rather
than understating it, so no product silently goes out of stock on deploy. The
admin reconciles real counts afterwards — see Open risks.

#### `products` — cleanup

**Added:**

- `slug` — string, unique, backfilled `Str::slug(name)` with a numeric suffix on
  collision. Route model binding moves to `slug`.
- `availability` — enum(`in_stock`, `preorder`, `upcoming`), default `in_stock`.
  Replaces `is_preorder` and `is_upcoming`.
- `booking_fee` — decimal(10,2), nullable. Replaces `is_bookable`. `null` means
  no booking required; a value is the per-unit booking amount. This removes the
  hardcoded `200` from `CartController.php:129`, `CheckoutController.php:64` and
  `CheckoutController.php:200`, and makes the fee apply correctly to variant
  products.

**Dropped:**

- `category` (the legacy enum), after backfilling any row with a null
  `category_id` from its enum value. Removes the string-match block in
  `Product.php:105-112`.
- `is_preorder`, `is_upcoming`, `is_bookable` — folded into `availability` and
  `booking_fee` above.

**Kept:** `price_tba` stays as its own flag; it is orthogonal to availability (a
preorder item may or may not have announced pricing).

**Renamed:** `rating` → `manual_rating`, `reviews` → `manual_review_count`. These
remain admin-editable, but are now clearly labelled as manual overrides distinct
from computed review data (see §6).

#### `reviews` — becomes product-aware

| Column | Type | Purpose |
|---|---|---|
| `product_id` | FK nullable, null on delete | `null` keeps existing rows as site-wide testimonials |
| `order_id` | FK nullable, null on delete | Links a review to the purchase that earned it |
| `is_approved` | boolean, default false | Moderation gate before a review renders |

Existing rows migrate with `product_id = null`, so the current testimonial
carousel keeps working untouched.

#### `order_items`

No schema change. `product_variant_id` already exists and is already populated.

### 2. Pricing: one source of truth

Two classes replace the five hand-rolled copies.

**`App\Services\PricingService`**

```php
public function priceFor(Product $product, ?ProductVariant $variant = null): float
public function compareAtPriceFor(Product $product, ?ProductVariant $variant = null): ?float
public function bookingFeeFor(Product $product): ?float
```

`priceFor` is the single precedence rule, evaluated in this order:

1. Variant `sale_price`, if a variant is given and the value is set and lower
   than the variant `price`
2. Variant `price`, if a variant is given
3. Product `offer_price`, if the offer window is currently open and the offer
   price is below `price`
4. Product `sale_price`, if set and below `price`
5. Product `price`

`compareAtPriceFor` returns the struck-through original, or `null` when there is
no discount. `Product::getDisplayPriceAttribute` and the related accessors are
reduced to thin delegates so existing Blade calls keep working during the
transition, then removed once call sites are migrated.

**Booking fee is no longer subtracted from the item price.** It is carried
separately in the cart summary and written to `orders.booking_amount`, which
fixes the variant case and removes the `> 200` guard entirely.

**`App\Services\CartService`**

```php
public function add(Product $product, ?ProductVariant $variant, int $quantity): void
public function update(string $cartItemId, int $quantity): void
public function remove(string $cartItemId): void
public function contents(): Collection        // hydrated with Product + Variant, eager-loaded
public function summary(): CartSummary
```

`CartSummary` is a readonly DTO carrying `subtotal`, `bookingTotal`, `itemCount`,
`hasBookingItems`, `hasMixedTypes`.

**Composite IDs stop being parsed.** `"12_3"` stays as an opaque cart key. Every
consumer reads `attributes.product_id` and `attributes.variant_id` instead —
values the cart already writes today. `explode('_')` disappears from all five
sites. `contents()` resolves every product and variant in two queries, ending the
N+1 loops.

A guard in `contents()` handles carts left in a visitor's session from before
this change: an item lacking `attributes.product_id` is parsed once from its key,
re-saved with proper attributes, and served normally.

**All five call sites route through the service.** The navbar's inline PHP loop
becomes an `<x-cart-summary>` Blade component backed by `CartService::summary()`,
ending DB queries inside a template.

### 3. Stock and concurrency

Stock resolves to the variant when one is selected, and to the product otherwise.
`Product::availableStock(?ProductVariant $variant)` is the single accessor.

At checkout, the existing `DB::transaction` gains `lockForUpdate` on the product
and variant rows before the availability check and decrement, closing the oversell
window. Variant selection and stock are re-validated at checkout, not only at
add-to-cart — today a variant can be deactivated or sold out between the two.

A product with variants derives its own aggregate stock from
`SUM(variants.quantity) WHERE is_active`, so the shop card's sold-out badge is
correct without a denormalised counter.

### 4. Storefront

**Product page** (`shop/show.blade.php`)

The `<select>` dropdown at line 137 becomes a row of image swatch buttons.
Selecting a swatch swaps the gallery, the price block and the stock line. Variants
that are sold out or inactive render disabled and struck through rather than being
silently addable. The add-to-cart button reflects the selected variant's
availability. No variant is preselected when the first one is out of stock.

**Shop card** (`shop/partials/product-grid.blade.php`, `product-mobile.blade.php`)

Shows `From ৳450` when active variants differ in price, and a single price when
they agree. The sold-out badge appears only when every active variant is out of
stock. Variant products continue to route to the product page rather than
quick-adding, since a variant must be chosen.

**Cart** (`cart/index.blade.php`)

Each line shows the variant's own image and name. Quantity controls validate
against the variant's stock, not the parent's.

### 5. Admin

The keychain gate at `AdminController.php:938` is removed. The variant repeater
is available for every category, with per-variant `name`, `price`, `sale_price`,
`quantity`, `sku`, `is_active` and images.

Product create and edit gain `availability` and `booking_fee` controls replacing
the three removed checkboxes.

A review moderation screen lists pending reviews with approve and reject actions,
and allows attaching an existing site-wide testimonial to a product.

**Two admin screens break on the schema change and are fixed in the same pass:**

- The low-stock alert renders `{{ $product->category }}`
  (`admin/analytics.blade.php:238`). Once the enum column is dropped, that
  property resolves to the `belongsTo` relation object instead of a string. It
  becomes `{{ $product->category_name }}`.
- The same alert reads only `products.quantity`, so it would never warn about a
  variant running low. Its query changes to surface low-stock *variants* by name
  alongside low-stock simple products.

### 6. Product reviews

Customers who have an order containing the product with `status = 'delivered'`
may submit one review per product per order. This reuses the exact delivered-order
pattern the giveaway feature already relies on
(`GiveawayEntry.php:46`, `GiveawayController.php:30`).

Submitted reviews are held at `is_approved = false` until an admin approves them.

A product's displayed rating is computed from its approved, product-linked reviews.
When a product has no approved reviews, `manual_rating` and `manual_review_count`
are used as the fallback, so nothing regresses for products the admin has already
rated by hand. The shop's "Popular" sort orders by computed review count and
rating, falling back the same way.

### 7. Removals

Deleted in the same pass, since this redesign rewrites the code that contains them:

- `$cartTotal` from both controllers and both views
- `$totalDiscount` and `$finalTotal` from `CheckoutController::index`
- The duplicate booking-fee loop at `CheckoutController.php:180`
- `resources/views/shop/index-mobile.blade.php` and `resources/views/shop/mobile.blade.php`
- The `explode('_')` composite-ID parsing at all five sites
- The legacy category string-match in `Product.php:105-112`
- The hardcoded `200` booking amount in three files
- The console commands that exist only to repair the legacy category column
  (`FixNullCategoryIds`, `CheckProductCategoryFields`, `CheckCategoryProducts`),
  once the column is dropped

## Blast radius of the dropped booleans

`is_preorder`, `is_upcoming` and `is_bookable` are read in **14 files**, all of
which must migrate to `availability` / `booking_fee` in the same pass:

- Controllers: `AdminController`, `CartController`, `CheckoutController`
- Model: `Product`
- Admin views: `product-create`, `product-edit`, `orders`
- Storefront views: `shop/show`, `cart/index`, `checkout/index`,
  `home/components/navigation/actions`, `home/sections/categories`
- Profile views: `profile/index`, `profile/order-details`

Some of these read the *cart attribute* `attributes->is_bookable` rather than the
product column; those switch to reading the booking fee off the `CartSummary`
rather than a boolean. Each file needs checking individually — a blind
find-and-replace will conflate the two.

**`orders.is_preorder_booking` is a different column and is not touched.** It
stays on the `Order` model exactly as it is, so historical orders and the order
emails keep working.

## URL change

Product URLs move from `/shop/47` to `/shop/rgx-butterfly`. Per explicit decision,
**no redirect is added** — existing ID-based links will 404. The sitemap is
regenerated with slug URLs, and the dead `$product->slug ?? $product->id` fallback
is removed now that slugs genuinely exist.

## Migration safety

This runs against a live database holding real orders.

- Every migration backfills before it drops, and every migration is reversible.
- Column drops (`category`, `is_preorder`, `is_upcoming`, `is_bookable`) happen in
  a migration separate from and later than the one that adds and backfills their
  replacements, so a bad deploy can be rolled back without data loss.
- The variant stock backfill is generous rather than zeroing, so no product
  silently goes out of stock on deploy.
- `orders` and `order_items` are not modified. Historical orders keep their
  snapshotted `product_name`, `price` and `subtotal` regardless of what happens
  to the product afterwards.

## Testing

Feature tests covering the behaviours that are broken today:

1. Cart subtotal equals the charged order total for a variant item on offer —
   the cart/checkout disagreement in problem 4.
2. Buying a variant decrements that variant's stock, not the parent's.
3. Two concurrent checkouts for the last unit: one succeeds, one fails cleanly.
4. A sold-out or inactive variant cannot be added to the cart, and cannot pass
   checkout if it sold out while sitting in the cart.
5. A bookable product **with variants** charges the correct booking fee — the
   case that is silently wrong today.
6. The navbar mini-cart total equals the cart page total for variant items.
7. A legacy session cart keyed `"12_3"` without proper attributes still renders
   and checks out.
8. Slug routing resolves; a product with a colliding name gets a unique slug.
9. A review is only submittable by a customer whose order containing that product
   reached `delivered`, and only renders once approved.

## Open risks

- **The variant stock backfill is a guess.** There is no per-variant sales history
  to reconstruct real counts from. The admin must reconcile inventory after deploy.
  This is called out rather than solved because no correct automated answer exists.
- **Dropping ID-based product URLs will lose any existing search rankings** for
  those pages. This was an explicit decision, recorded here so it is not a surprise.
- **Product reviews are a new feature, not a rewiring.** The `reviews` table had no
  product relationship at all. The submission and moderation flow is genuine new
  surface area and is the largest single piece of work in this spec — it is a
  reasonable candidate to split into its own phase if the variant work needs to
  ship first.
