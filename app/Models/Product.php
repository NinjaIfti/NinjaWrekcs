<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        return $this->quantity > 0 && $this->quantity < 5;
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
