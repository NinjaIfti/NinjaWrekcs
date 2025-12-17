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
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'coupon_discount' => 'decimal:2',
        'total' => 'decimal:2',
        'save_info' => 'boolean',
        'terms_accepted' => 'boolean',
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
}
