<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class CheckCategoryIds extends Command
{
    protected $signature = 'products:check-category-ids';
    protected $description = 'Check how many products have each category_id';

    public function handle()
    {
        $this->info("Checking category_id distribution in products...\n");
        
        // Get all categories
        $categories = Category::all()->keyBy('id');
        
        // Count products by category_id
        $categoryCounts = Product::select('category_id', DB::raw('count(*) as count'))
            ->where('is_active', true)
            ->groupBy('category_id')
            ->orderBy('category_id')
            ->get();
        
        // Count null category_id
        $nullCount = Product::whereNull('category_id')
            ->where('is_active', true)
            ->count();
        
        $this->info("Active Products by Category ID:\n");
        $this->info(str_repeat('=', 80));
        
        $total = 0;
        $tableData = [];
        
        foreach ($categoryCounts as $item) {
            $categoryId = $item->category_id;
            $count = $item->count;
            $total += $count;
            
            $category = $categories->get($categoryId);
            $categoryName = $category ? $category->name : 'NOT FOUND';
            $categorySlug = $category ? $category->slug : 'N/A';
            $isParent = $category && $category->parent_id === null ? 'Yes' : 'No';
            
            $tableData[] = [
                'Category ID' => $categoryId,
                'Category Name' => $categoryName,
                'Slug' => $categorySlug,
                'Is Parent' => $isParent,
                'Product Count' => $count,
            ];
        }
        
        if (count($tableData) > 0) {
            $this->table(
                ['Category ID', 'Category Name', 'Slug', 'Is Parent', 'Product Count'],
                $tableData
            );
        }
        
        if ($nullCount > 0) {
            $this->warn("\n⚠️  Products with NULL category_id: {$nullCount}");
            $total += $nullCount;
        }
        
        $this->info("\n" . str_repeat('=', 80));
        $this->info("Total Active Products: {$total}");
        
        // Show all categories in database
        $this->info("\n\nAll Categories in Database:\n");
        $this->info(str_repeat('=', 80));
        
        $allCategories = Category::orderBy('parent_id')->orderBy('order')->get();
        $categoryTable = [];
        
        foreach ($allCategories as $cat) {
            $productCount = Product::where('category_id', $cat->id)
                ->where('is_active', true)
                ->count();
            
            $categoryTable[] = [
                'ID' => $cat->id,
                'Name' => $cat->name,
                'Slug' => $cat->slug,
                'Parent ID' => $cat->parent_id ?? 'NULL',
                'Is Active' => $cat->is_active ? 'Yes' : 'No',
                'Order' => $cat->order ?? 'NULL',
                'Products' => $productCount,
            ];
        }
        
        $this->table(
            ['ID', 'Name', 'Slug', 'Parent ID', 'Is Active', 'Order', 'Products'],
            $categoryTable
        );
        
        // Summary
        $this->info("\n" . str_repeat('=', 80));
        $this->info("Summary:");
        $this->info("  - Total Categories: " . $allCategories->count());
        $this->info("  - Parent Categories: " . $allCategories->whereNull('parent_id')->count());
        $this->info("  - Subcategories: " . $allCategories->whereNotNull('parent_id')->count());
        $this->info("  - Active Products: {$total}");
        $this->info("  - Products with NULL category_id: {$nullCount}");
        
        return 0;
    }
}
