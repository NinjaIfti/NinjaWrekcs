<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use App\Models\ProductImage;
use App\Models\OrderItem;

class Product extends Model
{
    use HasFactory;

    public const AVAILABILITY_IN_STOCK = 'in_stock';
    public const AVAILABILITY_PREORDER = 'preorder';
    public const AVAILABILITY_UPCOMING = 'upcoming';

    protected $fillable = [
        'name',
        'description',
        'notes',
        'quantity',
        'price',
        'cost_price',
        'sale_price',
        'offer_price',
        'offer_starts_at',
        'offer_ends_at',
        'image',
        'cover_photo',
        'category',
        'category_id',
        'rating',
        'reviews',
        'is_active',
        'is_featured',
        'show_in_all_categories',
        'is_new',
        'is_bestseller',
        'is_limited_edition',
        'is_preorder',
        'is_upcoming',
        'is_bookable',
        'availability',
        'booking_fee',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'show_in_all_categories' => 'boolean',
        'is_new' => 'boolean',
        'is_bestseller' => 'boolean',
        'is_limited_edition' => 'boolean',
        'is_preorder' => 'boolean',
        'is_upcoming' => 'boolean',
        'is_bookable' => 'boolean',
        'booking_fee' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'offer_price' => 'decimal:2',
        'offer_starts_at' => 'datetime',
        'offer_ends_at' => 'datetime',
    ];

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function stockNotifications(): HasMany
    {
        return $this->hasMany(StockNotification::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order');
    }

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
            // Sum the loaded relation when there is one, so a list of products
            // that eager-loaded variants does not fire a query per row.
            return $this->relationLoaded('variants')
                ? (int) $this->getRelation('variants')->where('is_active', true)->sum('quantity')
                : (int) $this->variants()->where('is_active', true)->sum('quantity');
        }

        return (int) $this->quantity;
    }

    /**
     * The price to show where no variant has been chosen, or null when there
     * is none. Delegates to PricingService so listings, dashboards and emails
     * agree with the shop on what a variant product costs.
     */
    public function displayPriceFrom(): ?float
    {
        return app(\App\Services\PricingService::class)->displayPriceFor($this);
    }

    /**
     * SQL for "a customer can buy this right now", as 1 or 0.
     *
     * availableStock() is the PHP answer, but sorting and filtering happen in
     * the database, where it cannot be called per row. Reading products.quantity
     * there was wrong in both directions: every merged product sorted as
     * sold-out however much its variants held, and the in-stock filter hid them
     * outright.
     *
     * A product with variants is in stock when an active variant is; a product
     * without variants when its own column is.
     */
    public static function hasStockExpression(): string
    {
        return '(CASE
            WHEN EXISTS (
                SELECT 1 FROM product_variants pv
                WHERE pv.product_id = products.id AND pv.is_active = 1 AND pv.quantity > 0
            ) THEN 1
            WHEN NOT EXISTS (
                SELECT 1 FROM product_variants pv2 WHERE pv2.product_id = products.id
            ) AND products.quantity > 0 THEN 1
            ELSE 0
        END)';
    }

    /**
     * The price a listing should sort and filter on, computed in the database.
     *
     * The counterpart to PricingService::displayPriceFor(), which cannot be
     * used here: a paginated listing cannot call PHP once per row. The two must
     * agree, or the shop sorts by one figure and prints another.
     *
     * Precedence mirrors that method exactly:
     *   - a product with active variants sorts on its CHEAPEST active variant,
     *     taking each variant's sale_price when it undercuts its price
     *   - otherwise an open offer, then a sale price, then the list price
     *
     * products.price is 0 on every merged product by design, so reading the
     * column directly listed all of them first at an apparent 0.
     *
     * The expression carries one placeholder for "now", because the offer
     * window has to be judged on Laravel's clock rather than the database's.
     * Use the orderByEffectivePrice() / whereEffectivePrice() scopes rather
     * than pasting this into a raw call, so the binding cannot be forgotten.
     */
    public static function effectivePriceExpression(): string
    {
        return '(CASE
            WHEN EXISTS (
                SELECT 1 FROM product_variants pv
                WHERE pv.product_id = products.id AND pv.is_active = 1
            ) THEN (
                SELECT MIN(CASE
                    WHEN pv2.sale_price IS NOT NULL AND pv2.sale_price < pv2.price
                        THEN pv2.sale_price
                    ELSE pv2.price
                END)
                FROM product_variants pv2
                WHERE pv2.product_id = products.id AND pv2.is_active = 1
            )
            WHEN products.offer_price IS NOT NULL
                AND products.offer_starts_at IS NOT NULL
                AND products.offer_ends_at IS NOT NULL
                AND ? BETWEEN products.offer_starts_at AND products.offer_ends_at
                AND products.offer_price < products.price
                THEN products.offer_price
            WHEN products.sale_price IS NOT NULL AND products.sale_price < products.price
                THEN products.sale_price
            ELSE products.price
        END)';
    }

    /**
     * Products that belong under any of these categories - their own, or every
     * category when "show in all categories" is ticked.
     *
     * The flag lists one product row in many places rather than copying it, so
     * its stock, variants, cart lines and orders are the same wherever it is
     * found. Every category listing should go through this scope so the shop,
     * the counts beside it and the home page all agree on what is "in" a
     * category.
     *
     * @param  array<int, int>  $categoryIds
     */
    public function scopeInCategories(Builder $query, array $categoryIds): Builder
    {
        // Grouped, so the OR cannot leak past other conditions such as
        // is_active.
        return $query->where(function (Builder $q) use ($categoryIds) {
            $q->whereIn('category_id', $categoryIds)
                ->orWhere('show_in_all_categories', true);
        });
    }

    /**
     * Order a listing by what a customer actually pays.
     */
    public function scopeOrderByEffectivePrice(Builder $query, string $direction = 'asc'): Builder
    {
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        return $query->orderByRaw(self::effectivePriceExpression() . ' ' . $direction, [now()]);
    }

    /**
     * Compare a listing against a price the customer would actually pay.
     *
     * The bound value is CAST rather than compared directly. Laravel hands a
     * PHP float to PDO as PARAM_STR, and SQLite compares an INTEGER column
     * against a TEXT value by storage class rather than by value - every
     * number sorts below every string, so `800 >= '500.0'` is false. MySQL
     * coerces the string and would have hidden this entirely.
     *
     * DECIMAL(10,2) carries NUMERIC affinity in SQLite and is a real type in
     * MySQL, so the cast means the same thing on both.
     */
    public function scopeWhereEffectivePrice(Builder $query, string $operator, float $value): Builder
    {
        $operator = in_array($operator, ['<', '<=', '>', '>=', '=', '<>'], true) ? $operator : '=';

        return $query->whereRaw(
            self::effectivePriceExpression() . ' ' . $operator . ' CAST(? AS DECIMAL(10,2))',
            [now(), $value]
        );
    }

    public function requiresBooking(): bool
    {
        return $this->booking_fee !== null && (float) $this->booking_fee > 0;
    }

    public function isPurchasable(): bool
    {
        return $this->is_active && $this->availability !== self::AVAILABILITY_UPCOMING;
    }

    public function isKeychain(): bool
    {
        $category = $this->relationLoaded('category')
            ? $this->getRelation('category')
            : $this->category()->first();
        return $category && is_object($category) && $category->slug === 'valorant-keychains-stickers';
    }

    public function getCategoryNameAttribute()
    {
        // Always try to get from new category relationship first
        if ($this->category_id) {
            // getRelation() rather than $this->category, because 'category' is
            // also a legacy string column and the attribute would win. Reading
            // the loaded relation keeps a list of products from querying once
            // per row just to print its category.
            $categoryModel = $this->relationLoaded('category')
                ? $this->getRelation('category')
                : $this->category()->first();

            if ($categoryModel && is_object($categoryModel)) {
                return $categoryModel->name;
            }
        }
        
        // Fallback to old string-based category (for backward compatibility)
        if (isset($this->attributes['category']) && $this->attributes['category']) {
            return match($this->attributes['category']) {
                'figures' => 'Agent Figures',
                'knives' => 'Knives & Weapons',
                'stickers' => 'Stickers & Keychains',
                default => 'Unknown',
            };
        }
        
        return 'Uncategorized';
    }

    // Check if offer is currently active
    public function getHasActiveOfferAttribute()
    {
        if (!$this->offer_price || !$this->offer_starts_at || !$this->offer_ends_at) {
            return false;
        }
        
        $now = now();
        return $now->between($this->offer_starts_at, $this->offer_ends_at) && $this->offer_price < $this->price;
    }

    // Get the final display price (offer price if active, then sale price, otherwise regular price)
    public function getDisplayPriceAttribute()
    {
        if ($this->price == 0) {
            return null;
        }
        
        if ($this->has_active_offer) {
            return $this->offer_price;
        }
        return $this->sale_price ?? $this->price;
    }

    // Check if product has a discount
    public function getHasDiscountAttribute()
    {
        if ($this->has_active_offer) {
            return true;
        }
        return $this->sale_price && $this->sale_price < $this->price;
    }

    // Calculate discount percentage
    public function getDiscountPercentageAttribute()
    {
        if (!$this->has_discount) {
            return 0;
        }
        
        $originalPrice = $this->price;
        $discountedPrice = $this->has_active_offer ? $this->offer_price : $this->sale_price;
        
        return round((($originalPrice - $discountedPrice) / $originalPrice) * 100);
    }

    // Get time remaining for offer in seconds
    public function getOfferTimeRemainingAttribute()
    {
        if (!$this->has_active_offer) {
            return 0;
        }
        
        return now()->diffInSeconds($this->offer_ends_at, false);
    }

    // Check if stock is low
    public function getIsLowStockAttribute()
    {
        $stock = $this->availableStock();

        return $stock > 0 && $stock < 5;
    }

    /**
     * The photo to show for this product, as a storage-relative path.
     *
     * Merging a family moved each source's photos onto its variant, so a merged
     * product has no product_images rows of its own - reading only those showed
     * "No Image" for every one of them.
     *
     * The cover photo comes first: it is the one an admin picked deliberately
     * as the main image, so it beats whatever happens to be first in the
     * gallery. Then the gallery, then the first active variant's photo, then
     * the legacy single-image column.
     *
     * A candidate whose file is missing is skipped rather than returned. The
     * merge left several rows naming one file, and deleting any one of those
     * rows used to delete the file for all of them, so dangling paths exist on
     * production. Handing one to an <img> renders a broken image even when the
     * product has a perfectly good second photo; falling through to the next
     * candidate - and ultimately to null, which the views draw as the
     * placeholder - is always the better picture.
     */
    public function primaryImagePath(): ?string
    {
        foreach ($this->imagePathCandidates() as $candidate) {
            if ($candidate && Storage::disk('public')->exists($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * Every photo for the product page's slideshow, best first, skipping any
     * whose file is missing.
     *
     * Same reasoning as primaryImagePath(): a dangling path reaches the browser
     * as a broken slide. An empty result makes the view draw the placeholder.
     *
     * @return array<int, string>
     */
    public function galleryImagePaths(): array
    {
        $paths = [];

        // The cover photo leads whenever there is one.
        if ($this->cover_photo) {
            $paths[] = $this->cover_photo;
        }

        if ($this->hasVariants()) {
            foreach ($this->variants->first()?->images ?? [] as $image) {
                $paths[] = $image->path;
            }
        } else {
            foreach ($this->images as $image) {
                $paths[] = $image->path;
            }
        }

        $surviving = $this->onlyExisting($paths);

        if ($surviving === [] && $this->image) {
            $surviving = $this->onlyExisting([$this->image]);
        }

        return $surviving;
    }

    /**
     * @param  array<int, string|null>  $paths
     * @return array<int, string>
     */
    private function onlyExisting(array $paths): array
    {
        $unique = array_values(array_unique(array_filter($paths)));

        return array_values(array_filter(
            $unique,
            fn (string $path) => Storage::disk('public')->exists($path)
        ));
    }

    /**
     * Every path this product could show, best first.
     *
     * @return array<int, string>
     */
    private function imagePathCandidates(): array
    {
        $candidates = [$this->cover_photo];

        if ($this->images && $this->images->isNotEmpty()) {
            foreach ($this->images as $image) {
                $candidates[] = $image->path;
            }
        }

        if ($this->hasVariants()) {
            foreach ($this->variants->where('is_active', true) as $variant) {
                foreach ($variant->images as $image) {
                    $candidates[] = $image->path;
                }
            }
        }

        $candidates[] = $this->image;

        return array_values(array_unique(array_filter($candidates)));
    }

    // Get recent sales count in last 24 hours
    public function getRecentSalesAttribute()
    {
        return $this->orderItems()
            ->whereHas('order', function($query) {
                $query->where('created_at', '>=', now()->subDay());
            })
            ->sum('quantity');
    }

    /**
     * Forget the cached shop listing pages.
     *
     * The shop orders in-stock products above sold-out ones, so a stock change
     * reorders the listing. Without this the cached pages would serve a stale
     * order (and a stale "In Stock" badge) for up to 30 minutes.
     */
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

    protected static function booted(): void
    {
        // Stock moves from many places - checkout, admin order create/edit/cancel,
        // product edit - so hook the model rather than each call site.
        static::updated(function (Product $product) {
            if ($product->wasChanged(['quantity', 'is_active'])) {
                static::clearShopListingCache();
            }
        });

        static::created(fn () => static::clearShopListingCache());
        static::deleted(fn () => static::clearShopListingCache());
    }
}
