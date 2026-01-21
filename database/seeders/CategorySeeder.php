<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // Valorant parent category - update or create to ensure it exists and is active
        $valorant = Category::updateOrCreate(
            ['slug' => 'valorant'],
            [
                'name' => 'Valorant',
                'parent_id' => null,
                'order' => 1,
                'is_active' => true,
            ]
        );

        // Valorant subcategories
        Category::updateOrCreate(
            ['slug' => 'valorant-weapons'],
            [
                'name' => 'Weapons',
                'parent_id' => $valorant->id,
                'order' => 1,
                'is_active' => true,
            ]
        );

        Category::updateOrCreate(
            ['slug' => 'valorant-melee'],
            [
                'name' => 'Melee',
                'parent_id' => $valorant->id,
                'order' => 2,
                'is_active' => true,
            ]
        );

        Category::updateOrCreate(
            ['slug' => 'valorant-bundles'],
            [
                'name' => 'Bundles',
                'parent_id' => $valorant->id,
                'order' => 3,
                'is_active' => true,
            ]
        );

        // CSGO parent category
        $csgo = Category::updateOrCreate(
            ['slug' => 'csgo'],
            [
                'name' => 'CS:GO',
                'parent_id' => null,
                'order' => 2,
                'is_active' => true,
            ]
        );

        // CSGO subcategories
        Category::updateOrCreate(
            ['slug' => 'csgo-knife'],
            [
                'name' => 'Knife',
                'parent_id' => $csgo->id,
                'order' => 1,
                'is_active' => true,
            ]
        );

        // Toys parent category (no subcategories)
        Category::updateOrCreate(
            ['slug' => 'toys'],
            [
                'name' => 'Toys',
                'parent_id' => null,
                'order' => 3,
                'is_active' => true,
            ]
        );

        // Pre-order/Upcoming parent category (no subcategories)
        Category::updateOrCreate(
            ['slug' => 'pre-order-upcoming'],
            [
                'name' => 'Pre-order/Upcoming',
                'parent_id' => null,
                'order' => 4,
                'is_active' => true,
            ]
        );

        $this->command->info('Categories seeded successfully! All main categories are now active.');
    }
}
