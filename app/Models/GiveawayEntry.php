<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GiveawayEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'phone',
        'invoice_number',
        'order_date',
    ];

    protected $casts = [
        'order_date' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Giveaway rules, as published on the poster and the /giveaway page.
     */
    public const STARTS_AT = '2026-08-01 00:00:00';
    public const MIN_ORDER_TOTAL = 1500;

    /**
     * Orders that automatically qualify for the giveaway.
     *
     * Entries are derived from this query rather than copied into a table, so the
     * list tracks order status by itself: an order that is later cancelled (or has
     * its status moved off "delivered", or is hidden) drops out with no sync step.
     */
    public static function qualifyingOrders()
    {
        return Order::query()
            ->where('is_deleted', false)
            ->where('status', 'delivered')
            ->where('created_at', '>=', self::STARTS_AT)
            ->where('total', '>=', self::MIN_ORDER_TOTAL);
    }

    /**
     * Whether a specific order currently qualifies.
     */
    public static function orderQualifies(Order $order): bool
    {
        return ! $order->is_deleted
            && $order->status === 'delivered'
            && $order->created_at >= \Carbon\Carbon::parse(self::STARTS_AT)
            && (float) $order->total >= self::MIN_ORDER_TOTAL;
    }
}
