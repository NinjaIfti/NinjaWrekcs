<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Category;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get the Shop mother category
        $shopCategory = Category::where('slug', 'shop')->whereNull('parent_id')->first();
        
        if (!$shopCategory) {
            return; // Shop category doesn't exist, nothing to do
        }
        
        // Get Shop's children (the 4 main categories: Valorant, CS:GO, Toys, Pre-order/Upcoming)
        $mainCategories = Category::where('parent_id', $shopCategory->id)->get();
        
        // Make the 4 main categories top-level (set parent_id to null)
        foreach ($mainCategories as $category) {
            $category->parent_id = null;
            $category->save();
        }
        
        // Delete the Shop mother category (no longer needed)
        $shopCategory->delete();
        
        // Verify structure:
        // - Valorant, CS:GO, Toys, Pre-order/Upcoming are now top-level (parent_id = null)
        // - Valorant's 3 subcategories remain under Valorant
        // - CS:GO, Toys, Pre-order/Upcoming have no subcategories
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate Shop category and make it parent of the 4 main categories
        $shopCategory = Category::create([
            'name' => 'Shop',
            'slug' => 'shop',
            'parent_id' => null,
            'order' => 1,
            'is_active' => true,
        ]);
        
        // Get the 4 main categories (Valorant, CS:GO, Toys, Pre-order/Upcoming)
        $mainCategories = Category::whereIn('slug', ['valorant', 'csgo', 'toys', 'pre-order-upcoming'])
            ->whereNull('parent_id')
            ->get();
        
        foreach ($mainCategories as $category) {
            $category->parent_id = $shopCategory->id;
            $category->save();
        }
    }
};
