<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncompleteOrder extends Model
{
    protected $fillable = [
        'session_id',
        'user_id',
        'name',
        'phone',
        'email',
        'address',
        'delivery_location',
        'cart_snapshot',
        'subtotal',
        'ip_address',
        'last_activity_at',
    ];

    protected $casts = [
        'cart_snapshot' => 'array',
        'subtotal' => 'decimal:2',
        'last_activity_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
