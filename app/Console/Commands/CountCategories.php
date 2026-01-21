<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Category;

class CountCategories extends Command
{
    protected $signature = 'categories:count';
    protected $description = 'Count total number of categories';

    public function handle()
    {
        $total = Category::count();
        $parents = Category::whereNull('parent_id')->count();
        $subcategories = Category::whereNotNull('parent_id')->count();
        
        $this->info("Category Count Summary:");
        $this->info("======================");
        $this->info("Total Categories: {$total}");
        $this->info("Parent Categories: {$parents}");
        $this->info("Subcategories: {$subcategories}");
        
        $this->info("\nAll Categories:");
        $this->info("===============");
        
        $categories = Category::orderBy('parent_id')->orderBy('order')->get();
        
        foreach ($categories as $cat) {
            $type = $cat->parent_id === null ? 'PARENT' : 'SUB';
            $parent = $cat->parent_id ? "Parent ID: {$cat->parent_id}" : 'None';
            $this->line("  [{$type}] ID: {$cat->id} | {$cat->name} | Slug: {$cat->slug} | {$parent}");
        }
        
        return 0;
    }
}
