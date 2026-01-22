<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\Category;

class TestCartCategoryLogic extends Command
{
    protected $signature = 'cart:test-category-logic';
    protected $description = 'Test if cart logic works with new category structure';

    public function handle()
    {
        $this->info("Testing Cart Category Logic\n");
        $this->info(str_repeat('=', 60));
        
        // Test 1: Check if products have category_id
        $this->info("\n1. Checking products with category_id:");
        $productsWithCategory = Product::whereNotNull('category_id')->count();
        $productsWithoutCategory = Product::whereNull('category_id')->count();
        $this->info("   Products with category_id: {$productsWithCategory}");
        $this->info("   Products without category_id: {$productsWithoutCategory}");
        
        if ($productsWithoutCategory > 0) {
            $this->warn("   ⚠️  Some products don't have category_id assigned!");
        }
        
        // Test 2: Check category_name accessor
        $this->info("\n2. Testing category_name accessor:");
        $products = Product::with('category')->take(5)->get();
        
        foreach ($products as $product) {
            $categoryName = $product->category_name;
            $hasCategory = $product->category_id !== null;
            $categoryLoaded = $product->category !== null;
            
            $status = $hasCategory && $categoryLoaded ? '✓' : '✗';
            $this->line("   {$status} Product: {$product->name}");
            $this->line("      Category ID: " . ($product->category_id ?? 'NULL'));
            $this->line("      Category Name: {$categoryName}");
            $this->line("      Category Loaded: " . ($categoryLoaded ? 'Yes' : 'No'));
        }
        
        // Test 3: Check if categories exist
        $this->info("\n3. Checking category relationships:");
        $categories = Category::whereNull('parent_id')->get();
        $this->info("   Main categories found: {$categories->count()}");
        
        foreach ($categories as $category) {
            $productCount = Product::where('category_id', $category->id)->count();
            $this->line("   - {$category->name} (ID: {$category->id}): {$productCount} products");
        }
        
        // Test 4: Simulate cart add
        $this->info("\n4. Simulating cart add operation:");
        $testProduct = Product::with('category')->whereNotNull('category_id')->first();
        
        if ($testProduct) {
            $categoryName = $testProduct->category_name;
            $this->info("   Product: {$testProduct->name}");
            $this->info("   Category Name (for cart): {$categoryName}");
            
            if ($categoryName && $categoryName !== 'Uncategorized') {
                $this->info("   ✓ Category name is available for cart");
            } else {
                $this->error("   ✗ Category name is missing or uncategorized");
            }
        } else {
            $this->warn("   No products found to test");
        }
        
        // Summary
        $this->info("\n" . str_repeat('=', 60));
        $this->info("Summary:");
        
        $allGood = true;
        if ($productsWithoutCategory > 0) {
            $this->warn("  ⚠️  Some products missing category_id");
            $allGood = false;
        }
        
        if ($allGood && $products->count() > 0) {
            $this->info("  ✓ Cart logic should work correctly with new category structure");
        } else {
            $this->warn("  ⚠️  Please check the issues above");
        }
        
        return 0;
    }
}
