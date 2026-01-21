<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
use App\Models\Product;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Store existing product category assignments BEFORE deleting categories
        $products = Product::whereNotNull('category_id')->get(['id', 'category_id']);
        
        // Step 2: Get old category names for mapping BEFORE deleting
        $oldCategories = Category::all()->keyBy('id');
        
        // Step 3: Temporarily set products category_id to null (we'll reassign after)
        DB::table('products')->update(['category_id' => null]);
        
        // Step 4: Delete all existing categories (will recreate with new structure)
        DB::table('categories')->delete();
        
        // Step 5: Create new category structure
        // Mother Category (top level)
        $motherCategory = Category::create([
            'name' => 'Shop',
            'slug' => 'shop',
            'parent_id' => null,
            'order' => 1,
            'is_active' => true,
        ]);
        
        // Valorant Category (under mother)
        $valorant = Category::create([
            'name' => 'Valorant',
            'slug' => 'valorant',
            'parent_id' => $motherCategory->id,
            'order' => 1,
            'is_active' => true,
        ]);
        
        // Valorant Subcategories
        $valorantKnife = Category::create([
            'name' => 'Knives/Melees',
            'slug' => 'valorant-knives-melees',
            'parent_id' => $valorant->id,
            'order' => 1,
            'is_active' => true,
        ]);
        
        $valorantFigures = Category::create([
            'name' => 'Agent Figures',
            'slug' => 'valorant-agent-figures',
            'parent_id' => $valorant->id,
            'order' => 2,
            'is_active' => true,
        ]);
        
        $valorantStickers = Category::create([
            'name' => 'Keychains & Stickers',
            'slug' => 'valorant-keychains-stickers',
            'parent_id' => $valorant->id,
            'order' => 3,
            'is_active' => true,
        ]);
        
        // CS:GO Category (under mother)
        $csgo = Category::create([
            'name' => 'CS:GO',
            'slug' => 'csgo',
            'parent_id' => $motherCategory->id,
            'order' => 2,
            'is_active' => true,
        ]);
        
        // Toys Category (under mother)
        $toys = Category::create([
            'name' => 'Toys',
            'slug' => 'toys',
            'parent_id' => $motherCategory->id,
            'order' => 3,
            'is_active' => true,
        ]);
        
        // Pre-order/Upcoming Category (under mother)
        $preorder = Category::create([
            'name' => 'Pre-order/Upcoming',
            'slug' => 'pre-order-upcoming',
            'parent_id' => $motherCategory->id,
            'order' => 4,
            'is_active' => true,
        ]);
        
        // Step 6: Migrate existing products to new structure
        $categoryMapping = [
            // Map old Valorant parent (ID 1) to new Valorant parent
            'valorant' => $valorant->id,
            // Map old CS:GO (ID 5) to new CS:GO
            'csgo' => $csgo->id,
            // Map old Toys (ID 7) to new Toys
            'toys' => $toys->id,
            // Map old Pre-order (ID 8) to new Pre-order
            'pre-order-upcoming' => $preorder->id,
        ];
        
        // Map old subcategories to new ones
        $subcategoryMapping = [
            // Old slugs that might exist
            'valorant-knife' => $valorantKnife->id,
            'valorant-melee' => $valorantKnife->id,
            'valorant-weapons' => $valorantKnife->id,
            'valorant-agent-figures' => $valorantFigures->id,
            'valorant-keychains-stickers' => $valorantStickers->id,
            'valorant-bundles' => $valorantStickers->id, // Map old bundles to stickers
        ];
        
        // Migrate products
        foreach ($products as $product) {
            $productId = $product->id;
            $oldCategoryId = $product->category_id;
            $oldCategory = $oldCategories->get($oldCategoryId);
            
            if (!$oldCategory) {
                continue;
            }
            
            $newCategoryId = null;
            
            // Check if it's a parent category
            if ($oldCategory->parent_id === null) {
                // Map parent categories
                if (isset($categoryMapping[$oldCategory->slug])) {
                    $newCategoryId = $categoryMapping[$oldCategory->slug];
                }
            } else {
                // Map subcategories
                if (isset($subcategoryMapping[$oldCategory->slug])) {
                    $newCategoryId = $subcategoryMapping[$oldCategory->slug];
                } else {
                    // If subcategory doesn't map, assign to parent
                    $parentSlug = $oldCategories->get($oldCategory->parent_id)->slug ?? null;
                    if ($parentSlug && isset($categoryMapping[$parentSlug])) {
                        $newCategoryId = $categoryMapping[$parentSlug];
                    }
                }
            }
            
            // Default to Valorant if no mapping found
            if (!$newCategoryId) {
                $newCategoryId = $valorant->id;
            }
            
            Product::where('id', $productId)->update(['category_id' => $newCategoryId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is a destructive migration, rollback would require restoring from backup
        // Not implementing rollback for safety
    }
};
