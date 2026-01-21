<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class FixProductCategories extends Command
{
    protected $signature = 'products:fix-categories {--dry-run : Show what would be changed without making changes}';
    protected $description = 'Fix product category assignments to match category slugs';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info("DRY RUN MODE - No changes will be made\n");
        }
        
        // Get all categories by slug
        $categories = Category::all()->keyBy('slug');
        
        // Find products with invalid or missing category assignments
        $products = Product::where('is_active', true)->get();
        
        $fixed = 0;
        $skipped = 0;
        
        foreach ($products as $product) {
            $category = Category::find($product->category_id);
            
            if (!$category) {
                $this->warn("Product '{$product->name}' (ID: {$product->id}) has invalid category_id: {$product->category_id}");
                
                // Try to find category by name or other means
                // For now, just report it
                $skipped++;
                continue;
            }
            
            // Check if product category is correct
            if ($category->is_active) {
                $this->line("Product '{$product->name}' is correctly assigned to '{$category->name}'");
            } else {
                $this->warn("Product '{$product->name}' is assigned to inactive category '{$category->name}'");
                if (!$dryRun) {
                    // Try to find a parent category
                    $parentCategory = Category::whereNull('parent_id')
                        ->where('is_active', true)
                        ->where('slug', 'like', '%' . strtolower($category->name) . '%')
                        ->first();
                    
                    if ($parentCategory) {
                        $product->category_id = $parentCategory->id;
                        $product->save();
                        $this->info("  → Fixed: Assigned to parent category '{$parentCategory->name}'");
                        $fixed++;
                    } else {
                        $skipped++;
                    }
                }
            }
        }
        
        $this->info("\nSummary:");
        $this->info("Fixed: {$fixed}");
        $this->info("Skipped: {$skipped}");
        
        if ($dryRun) {
            $this->info("\nRun without --dry-run to apply changes");
        }
        
        return 0;
    }
}
