<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'value',
        'minimum_order',
        'maximum_discount',
        'usage_limit',
        'used_count',
        'valid_from',
        'valid_until',
        'is_active',
        'description',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'minimum_order' => 'decimal:2',
        'maximum_discount' => 'decimal:2',
        'valid_from' => 'date',
        'valid_until' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Check if coupon is valid
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return false;
        }

        $now = Carbon::now();

        if ($this->valid_from && $now->lt($this->valid_from)) {
            return false;
        }

        if ($this->valid_until && $now->gt($this->valid_until->endOfDay())) {
            return false;
        }

        return true;
    }

    /**
     * Calculate discount for given subtotal
     */
    public function calculateDiscount(float $subtotal): float
    {
        if ($this->minimum_order && $subtotal < $this->minimum_order) {
            return 0;
        }

        if ($this->type === 'percentage') {
            $discount = ($subtotal * $this->value) / 100;
            
            if ($this->maximum_discount && $discount > $this->maximum_discount) {
                $discount = $this->maximum_discount;
            }
            
            return round($discount, 2);
        }

        // Fixed amount
        return min($this->value, $subtotal);
    }

    /**
     * Increment usage count
     */
    public function incrementUsage(): void
    {
        $this->increment('used_count');
    }
}
