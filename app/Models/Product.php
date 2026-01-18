<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\ProductImage;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'notes',
        'quantity',
        'price',
        'cost_price',
        'sale_price',
        'image',
        'category',
        'rating',
        'reviews',
        'is_active',
        'is_featured',
        'is_new',
        'is_bestseller',
        'is_limited_edition',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'is_new' => 'boolean',
        'is_bestseller' => 'boolean',
        'is_limited_edition' => 'boolean',
        'cost_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
    ];

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function getCategoryNameAttribute()
    {
        return match($this->category) {
            'figures' => 'Agent Figures',
            'knives' => 'Knives & Weapons',
            'stickers' => 'Stickers & Keychains',
            default => 'Unknown',
        };
    }

    // Get the final display price (sale price if available, otherwise regular price)
    public function getDisplayPriceAttribute()
    {
        return $this->sale_price ?? $this->price;
    }

    // Check if product has a discount
    public function getHasDiscountAttribute()
    {
        return $this->sale_price && $this->sale_price < $this->price;
    }

    // Calculate discount percentage
    public function getDiscountPercentageAttribute()
    {
        if (!$this->has_discount) {
            return 0;
        }
        return round((($this->price - $this->sale_price) / $this->price) * 100);
    }

    // Check if stock is low
    public function getIsLowStockAttribute()
    {
        return $this->quantity > 0 && $this->quantity < 5;
    }
}
