<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'name' => fake()->unique()->words(2, true),
            'sku' => fake()->unique()->bothify('SKU-####-???'),
            'price' => fake()->randomFloat(2, 100, 5000),
            'sale_price' => null,
            'quantity' => fake()->numberBetween(1, 20),
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}
