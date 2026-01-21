<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // Valorant Category (top-level, has subcategories)
        $valorant = Category::updateOrCreate(
            ['slug' => 'valorant'],
            [
                'name' => 'Valorant',
                'parent_id' => null,
                'order' => 1,
                'is_active' => true,
            ]
        );

        // Valorant Subcategories
        Category::updateOrCreate(
            ['slug' => 'valorant-knives-melees'],
            [
                'name' => 'Knives/Melees',
                'parent_id' => $valorant->id,
                'order' => 1,
                'is_active' => true,
            ]
        );

        Category::updateOrCreate(
            ['slug' => 'valorant-agent-figures'],
            [
                'name' => 'Agent Figures',
                'parent_id' => $valorant->id,
                'order' => 2,
                'is_active' => true,
            ]
        );

        Category::updateOrCreate(
            ['slug' => 'valorant-keychains-stickers'],
            [
                'name' => 'Keychains & Stickers',
                'parent_id' => $valorant->id,
                'order' => 3,
                'is_active' => true,
            ]
        );

        // CS:GO Category (top-level, no subcategories)
        Category::updateOrCreate(
            ['slug' => 'csgo'],
            [
                'name' => 'CS:GO',
                'parent_id' => null,
                'order' => 2,
                'is_active' => true,
            ]
        );

        // Toys Category (top-level, no subcategories)
        Category::updateOrCreate(
            ['slug' => 'toys'],
            [
                'name' => 'Toys',
                'parent_id' => null,
                'order' => 3,
                'is_active' => true,
            ]
        );

        // Pre-order/Upcoming Category (top-level, no subcategories)
        Category::updateOrCreate(
            ['slug' => 'pre-order-upcoming'],
            [
                'name' => 'Pre-order/Upcoming',
                'parent_id' => null,
                'order' => 4,
                'is_active' => true,
            ]
        );

        // Remove Shop category if it exists
        Category::where('slug', 'shop')->delete();
        
        // Remove any old categories that don't match the new structure
        $validSlugs = [
            'valorant', 'valorant-knives-melees', 'valorant-agent-figures', 
            'valorant-keychains-stickers', 'csgo', 'toys', 'pre-order-upcoming'
        ];
        
        Category::whereNotIn('slug', $validSlugs)->delete();

        $this->command->info('Categories seeded successfully!');
        $this->command->info('Structure:');
        $this->command->info('  Top-level categories (4):');
        $this->command->info('    - Valorant (with 3 subcategories: Knives/Melees, Agent Figures, Keychains & Stickers)');
        $this->command->info('    - CS:GO (no subcategories)');
        $this->command->info('    - Toys (no subcategories)');
        $this->command->info('    - Pre-order/Upcoming (no subcategories)');
    }
}
