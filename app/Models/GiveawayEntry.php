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
}
