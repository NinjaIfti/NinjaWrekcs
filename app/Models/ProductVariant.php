<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    use HasFactory;

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

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function isInStock(): bool
    {
        return $this->is_active && $this->quantity > 0;
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductVariantImage::class, 'product_variant_id')->orderBy('sort_order');
    }
}
