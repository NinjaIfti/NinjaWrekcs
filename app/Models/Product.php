<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'notes',
        'quantity',
        'price',
        'image',
        'category',
        'rating',
        'reviews',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getCategoryNameAttribute()
    {
        return match($this->category) {
            'figures' => 'Agent Figures',
            'knives' => 'Knives & Weapons',
            'stickers' => 'Stickers & Keychains',
            default => 'Unknown',
        };
    }
}
