<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'description',
        'amount',
        'order_id',
        'product_id',
        'expense_date',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Category constants
    const CATEGORY_SHIPPING = 'shipping';
    const CATEGORY_ADS = 'ads';
    const CATEGORY_COURIER = 'courier';
    const CATEGORY_PACKAGING = 'packaging';
    const CATEGORY_DAMAGED = 'damaged';
    const CATEGORY_RETURNED = 'returned';
    const CATEGORY_LOST = 'lost';
    const CATEGORY_OTHER = 'other';

    public static function categories(): array
    {
        return [
            self::CATEGORY_SHIPPING => 'Shipping Cost',
            self::CATEGORY_ADS => 'Advertisement',
            self::CATEGORY_COURIER => 'Courier Charges',
            self::CATEGORY_PACKAGING => 'Packaging Cost',
            self::CATEGORY_DAMAGED => 'Damaged/Broken Items',
            self::CATEGORY_RETURNED => 'Customer Returns',
            self::CATEGORY_LOST => 'Lost in Transit',
            self::CATEGORY_OTHER => 'Other Expenses',
        ];
    }
}
