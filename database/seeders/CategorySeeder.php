<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // Valorant parent category
        $valorant = Category::create([
            'name' => 'Valorant',
            'slug' => 'valorant',
            'order' => 1,
            'is_active' => true,
        ]);

        // Valorant subcategories
        Category::create([
            'name' => 'Weapons',
            'slug' => 'valorant-weapons',
            'parent_id' => $valorant->id,
            'order' => 1,
            'is_active' => true,
        ]);

        Category::create([
            'name' => 'Melee',
            'slug' => 'valorant-melee',
            'parent_id' => $valorant->id,
            'order' => 2,
            'is_active' => true,
        ]);

        Category::create([
            'name' => 'Bundles',
            'slug' => 'valorant-bundles',
            'parent_id' => $valorant->id,
            'order' => 3,
            'is_active' => true,
        ]);

        // CSGO parent category
        $csgo = Category::create([
            'name' => 'CS:GO',
            'slug' => 'csgo',
            'order' => 2,
            'is_active' => true,
        ]);

        // CSGO subcategories
        Category::create([
            'name' => 'Knife',
            'slug' => 'csgo-knife',
            'parent_id' => $csgo->id,
            'order' => 1,
            'is_active' => true,
        ]);

        // Toys parent category (no subcategories)
        Category::create([
            'name' => 'Toys',
            'slug' => 'toys',
            'order' => 3,
            'is_active' => true,
        ]);

        // Pre-order/Upcoming parent category (no subcategories)
        Category::create([
            'name' => 'Pre-order/Upcoming',
            'slug' => 'pre-order-upcoming',
            'order' => 4,
            'is_active' => true,
        ]);
    }
}
