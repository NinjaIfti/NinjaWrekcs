<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Category;

class CleanupOldCategories extends Command
{
    protected $signature = 'categories:cleanup {--dry-run : Show what would be deleted without deleting}';
    protected $description = 'Remove old subcategories that should not exist';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info("DRY RUN MODE - No categories will be deleted\n");
        }
        
        $this->info("Cleaning up old subcategories...\n");
        
        // Old Valorant subcategories to remove
        $oldValorantSubs = ['valorant-weapons', 'valorant-melee', 'valorant-bundles'];
        $removed = 0;
        
        foreach ($oldValorantSubs as $slug) {
            $category = Category::where('slug', $slug)->first();
            if ($category) {
                $this->warn("Found old subcategory: {$category->name} (ID: {$category->id}, Slug: {$slug})");
                
                // Check if it has products
                $productCount = $category->products()->where('is_active', true)->count();
                if ($productCount > 0) {
                    $this->error("  ⚠️  WARNING: This category has {$productCount} active products!");
                    $this->error("  Products will need to be reassigned before deletion.");
                } else {
                    if (!$dryRun) {
                        $category->delete();
                        $this->info("  ✓ Deleted");
                        $removed++;
                    } else {
                        $this->info("  [Would be deleted]");
                        $removed++;
                    }
                }
            }
        }
        
        // Remove CS:GO subcategories (CS:GO should not have subcategories)
        $csgo = Category::where('slug', 'csgo')->first();
        if ($csgo) {
            $csgoSubs = Category::where('parent_id', $csgo->id)->get();
            
            foreach ($csgoSubs as $sub) {
                $this->warn("Found CS:GO subcategory: {$sub->name} (ID: {$sub->id}, Slug: {$sub->slug})");
                
                $productCount = $sub->products()->where('is_active', true)->count();
                if ($productCount > 0) {
                    $this->error("  ⚠️  WARNING: This category has {$productCount} active products!");
                    $this->error("  Products will need to be reassigned to CS:GO parent before deletion.");
                } else {
                    if (!$dryRun) {
                        $sub->delete();
                        $this->info("  ✓ Deleted");
                        $removed++;
                    } else {
                        $this->info("  [Would be deleted]");
                        $removed++;
                    }
                }
            }
        }
        
        $this->info("\n" . str_repeat('=', 50));
        $this->info("Summary:");
        $this->info("Categories to be removed: {$removed}");
        
        if ($dryRun) {
            $this->info("\nRun without --dry-run to actually delete these categories");
        } else {
            $this->info("\n✓ Cleanup completed!");
        }
        
        return 0;
    }
}
