<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get the 4 main parent category IDs
        $valorantId = DB::table('categories')->where('slug', 'valorant')->whereNull('parent_id')->value('id');
        $csgoId = DB::table('categories')->where('slug', 'csgo')->whereNull('parent_id')->value('id');
        $toysId = DB::table('categories')->where('slug', 'toys')->whereNull('parent_id')->value('id');
        $preorderId = DB::table('categories')->where('slug', 'pre-order-upcoming')->whereNull('parent_id')->value('id');
        
        // Get Valorant subcategory IDs (for products that should go to subcategories)
        $valorantKnifeId = DB::table('categories')->where('slug', 'valorant-knife')->value('id');
        $valorantFiguresId = DB::table('categories')->where('slug', 'valorant-agent-figures')->value('id');
        $valorantStickersId = DB::table('categories')->where('slug', 'valorant-keychains-stickers')->value('id');
        
        // Fix products with null category_id
        // If they have the old 'category' enum field, map it appropriately
        if (Schema::hasColumn('products', 'category')) {
            // Map old category enum to new category_id
            // 'figures' -> Valorant Agent Figures subcategory
            if ($valorantFiguresId) {
                DB::table('products')
                    ->whereNull('category_id')
                    ->where('category', 'figures')
                    ->update(['category_id' => $valorantFiguresId]);
            }
            
            // 'knives' -> CS:GO parent category (no subcategories)
            if ($csgoId) {
                DB::table('products')
                    ->whereNull('category_id')
                    ->where('category', 'knives')
                    ->update(['category_id' => $csgoId]);
            }
            
            // 'stickers' -> Valorant Keychains/Stickers subcategory
            if ($valorantStickersId) {
                DB::table('products')
                    ->whereNull('category_id')
                    ->where('category', 'stickers')
                    ->update(['category_id' => $valorantStickersId]);
            }
        }
        
        // For any remaining null category_id products, assign to Valorant parent as default
        // (User can manually reassign to correct category/subcategory later)
        if ($valorantId) {
            DB::table('products')
                ->whereNull('category_id')
                ->where('is_active', true)
                ->update(['category_id' => $valorantId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration doesn't need a rollback
        // If you want to rollback, you'd need to set category_id back to null
        // which is not recommended
    }
};
