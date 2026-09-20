<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

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

    /**
     * This variant's photos, skipping any whose file is missing.
     *
     * The merge copied image paths onto variants rather than copying the files,
     * so deleting a source product used to delete a file its variant still
     * named. Picking that colour on the product page then swapped the gallery
     * to a broken image - keep those out of the swatch and the slideshow.
     *
     * @return array<int, string>
     */
    public function existingImagePaths(): array
    {
        return array_values(array_filter(
            $this->images->pluck('path')->all(),
            fn (string $path) => Storage::disk('public')->exists($path)
        ));
    }
}
