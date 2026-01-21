<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class FixNullCategoryIds extends Command
{
    protected $signature = 'products:fix-null-categories {--dry-run : Show what would be changed without making changes}';
    protected $description = 'Fix products with null category_id by assigning them to appropriate categories';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info("DRY RUN MODE - No changes will be made\n");
        }
        
        // Get all categories
        $categories = Category::all()->keyBy('slug');
        
        // Count products with null category_id
        $nullCount = Product::whereNull('category_id')->where('is_active', true)->count();
        $this->info("Found {$nullCount} active products with null category_id\n");
        
        if ($nullCount === 0) {
            $this->info("No products need fixing!");
            return 0;
        }
        
        // Get products with null category_id
        $products = Product::whereNull('category_id')->where('is_active', true)->get();
        
        $fixed = 0;
        $skipped = 0;
        
        // Get Valorant subcategories
        $valorantKnife = $categories->get('valorant-knife');
        $valorantFigures = $categories->get('valorant-agent-figures');
        $valorantStickers = $categories->get('valorant-keychains-stickers');
        
        // Mapping from old category enum to new category slugs
        // 'figures' -> Valorant Agent Figures subcategory
        // 'knives' -> CS:GO parent (no subcategories)
        // 'stickers' -> Valorant Keychains/Stickers subcategory
        $categoryMapping = [
            'figures' => 'valorant-agent-figures',
            'knives' => 'csgo',
            'stickers' => 'valorant-keychains-stickers',
        ];
        
        foreach ($products as $product) {
            $assignedCategory = null;
            
            // Try to use old category field if it exists
            if (isset($product->category) && !empty($product->category)) {
                $oldCategory = $product->category;
                
                if (isset($categoryMapping[$oldCategory])) {
                    $targetSlug = $categoryMapping[$oldCategory];
                    $assignedCategory = $categories->get($targetSlug);
                    
                    if ($assignedCategory) {
                        $this->info("Product '{$product->name}' (ID: {$product->id})");
                        $this->line("  Old category: {$oldCategory} → New category: {$assignedCategory->name} (ID: {$assignedCategory->id})");
                        
                        if (!$dryRun) {
                            $product->category_id = $assignedCategory->id;
                            $product->save();
                            $this->info("  ✓ Fixed!");
                            $fixed++;
                        } else {
                            $this->info("  [Would be fixed]");
                            $fixed++;
                        }
                        continue;
                    }
                }
            }
            
            // If no old category or mapping failed, assign to Valorant parent as default
            // (User can manually reassign to correct category/subcategory later)
            $defaultCategory = $categories->get('valorant');
            
            if ($defaultCategory) {
                $this->warn("Product '{$product->name}' (ID: {$product->id})");
                $this->line("  No category mapping found, assigning to default: {$defaultCategory->name} (ID: {$defaultCategory->id})");
                
                if (!$dryRun) {
                    $product->category_id = $defaultCategory->id;
                    $product->save();
                    $this->info("  ✓ Assigned to default category");
                    $fixed++;
                } else {
                    $this->info("  [Would be assigned to default]");
                    $fixed++;
                }
            } else {
                $this->error("Product '{$product->name}' (ID: {$product->id}) - Cannot assign: Valorant category not found!");
                $skipped++;
            }
        }
        
        $this->info("\n" . str_repeat('=', 50));
        $this->info("Summary:");
        $this->info("Fixed: {$fixed}");
        $this->info("Skipped: {$skipped}");
        
        if ($dryRun) {
            $this->info("\nRun without --dry-run to apply changes");
        } else {
            $this->info("\n✓ Changes applied successfully!");
        }
        
        return 0;
    }
}
