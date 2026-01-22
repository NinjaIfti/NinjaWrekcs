<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class CheckProductCategoryFields extends Command
{
    protected $signature = 'products:check-category-fields';
    protected $description = 'Check if products have old category enum field set';

    public function handle()
    {
        $this->info("Checking Product Category Fields\n");
        
        // Check if old category column exists
        $hasOldCategoryColumn = DB::getSchemaBuilder()->hasColumn('products', 'category');
        
        if ($hasOldCategoryColumn) {
            $this->info("Old 'category' enum column exists");
            
            $productsWithOldCategory = Product::whereNotNull('category')->get();
            $this->info("Products with old category field set: {$productsWithOldCategory->count()}");
            
            if ($productsWithOldCategory->count() > 0) {
                $this->warn("\nSample products with old category field:");
                foreach ($productsWithOldCategory->take(5) as $product) {
                    $this->line("  - {$product->name}");
                    $this->line("    Category ID: " . ($product->category_id ?? 'NULL'));
                    $this->line("    Old Category: {$product->category}");
                    $this->line("    Category Name (accessor): {$product->category_name}");
                    $this->line("    Category Relationship: " . ($product->category ? $product->category->name : 'NULL'));
                    $this->line("");
                }
            }
        } else {
            $this->info("Old 'category' enum column does not exist");
        }
        
        // Check category_name accessor behavior
        $this->info("\nTesting category_name accessor:");
        $testProduct = Product::whereNotNull('category_id')->first();
        
        if ($testProduct) {
            $this->info("Test Product: {$testProduct->name}");
            $this->info("  Category ID: {$testProduct->category_id}");
            $this->info("  Category Relationship Loaded: " . ($testProduct->relationLoaded('category') ? 'Yes' : 'No'));
            
            // Load category
            $testProduct->load('category');
            $this->info("  Category Relationship After Load: " . ($testProduct->category ? $testProduct->category->name : 'NULL'));
            $this->info("  Category Name (accessor): {$testProduct->category_name}");
        }
        
        return 0;
    }
}
