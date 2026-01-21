<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Category;
use App\Models\Product;

class CheckCategoryProducts extends Command
{
    protected $signature = 'categories:check-products {category?}';
    protected $description = 'Check which products are assigned to categories';

    public function handle()
    {
        $categorySlug = $this->argument('category');
        
        if ($categorySlug) {
            $category = Category::where('slug', $categorySlug)->first();
            if (!$category) {
                $this->error("Category '{$categorySlug}' not found!");
                return 1;
            }
            $this->checkCategory($category);
        } else {
            $categories = Category::whereNull('parent_id')->where('is_active', true)->get();
            
            $this->info("Checking all parent categories...\n");
            
            foreach ($categories as $category) {
                $this->checkCategory($category);
                $this->line('');
            }
        }
        
        return 0;
    }
    
    private function checkCategory(Category $category)
    {
        $this->info("Category: {$category->name} (ID: {$category->id}, Slug: {$category->slug})");
        $this->info("Active: " . ($category->is_active ? 'Yes' : 'No'));
        $this->info("Has Children: " . ($category->hasChildren() ? 'Yes' : 'No'));
        
        // Count products directly in this category
        $directProducts = Product::where('category_id', $category->id)
            ->where('is_active', true)
            ->count();
        
        $this->info("Products directly in this category: {$directProducts}");
        
        if ($directProducts > 0) {
            $products = Product::where('category_id', $category->id)
                ->where('is_active', true)
                ->select('id', 'name', 'category_id', 'is_active')
                ->get();
            
            $this->table(
                ['ID', 'Name', 'Category ID', 'Active'],
                $products->map(function($p) {
                    return [$p->id, $p->name, $p->category_id, $p->is_active ? 'Yes' : 'No'];
                })->toArray()
            );
        }
        
        // Check child categories
        if ($category->hasChildren()) {
            $this->info("\nChild Categories:");
            foreach ($category->children as $child) {
                $childProducts = Product::where('category_id', $child->id)
                    ->where('is_active', true)
                    ->count();
                $this->info("  - {$child->name} (ID: {$child->id}): {$childProducts} products");
            }
        }
    }
}
