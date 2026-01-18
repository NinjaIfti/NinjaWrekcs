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

    public function getCategoryNameAttribute()
    {
        return match($this->category) {
            'figures' => 'Agent Figures',
            'knives' => 'Knives & Weapons',
            'stickers' => 'Stickers & Keychains',
            default => 'Unknown',
        };
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
}
