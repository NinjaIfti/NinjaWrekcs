<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PopupSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'main_heading',
        'subheading',
        'description',
        'discount_text',
        'discount_amount',
        'badge_text',
        'button_text',
        'button_url',
        'is_active',
        'display_delay',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'display_delay' => 'integer',
    ];

    /**
     * Get the popup settings (singleton pattern - only one record)
     */
    public static function getSettings()
    {
        $settings = self::first();
        
        if (!$settings) {
            // Create default settings if none exist
            $settings = self::create([
                'title' => 'Pre-Order Special Offer!',
                'main_heading' => '🎮 VALORANT PRE-ORDER 🎮',
                'subheading' => 'Exclusive Collectibles Now Available!',
                'description' => 'Pre-order your favorite Valorant collectibles and get special discounts on your order.',
                'discount_text' => 'Get',
                'discount_amount' => '100 taka off + 10% Discount',
                'badge_text' => 'LIMITED TIME OFFER',
                'button_text' => 'Shop Now',
                'button_url' => '/shop',
                'is_active' => true,
                'display_delay' => 3000,
            ]);
        }
        
        return $settings;
    }
}




