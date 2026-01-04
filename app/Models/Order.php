<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'coupon_id',
        'coupon_code',
        'coupon_discount',
        'name',
        'phone',
        'address',
        'delivery_location',
        'delivery_charge',
        'email',
        'subtotal',
        'discount',
        'total',
        'payment_method',
        'transaction_number',
        'sending_number',
        'status',
        'save_info',
        'terms_accepted',
        'notes',
        'is_deleted',
        'deleted_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'coupon_discount' => 'decimal:2',
        'delivery_charge' => 'decimal:2',
        'total' => 'decimal:2',
        'save_info' => 'boolean',
        'terms_accepted' => 'boolean',
        'is_deleted' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function changes(): HasMany
    {
        return $this->hasMany(OrderChange::class);
    }

    /**
     * Scope to get only non-deleted orders (for admin panel)
     */
    public function scopeNotDeleted($query)
    {
        return $query->where('is_deleted', false);
    }

    /**
     * Soft delete the order (hide from admin)
     */
    public function softDelete()
    {
        $this->update([
            'is_deleted' => true,
            'deleted_at' => now(),
        ]);
    }

    /**
     * Restore a soft-deleted order
     */
    public function restore()
    {
        $this->update([
            'is_deleted' => false,
            'deleted_at' => null,
        ]);
    }
}
