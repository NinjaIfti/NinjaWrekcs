<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'description' => fake()->paragraph(),
            'notes' => null,
            'quantity' => fake()->numberBetween(0, 100),
            'price' => fake()->randomFloat(2, 100, 5000),
            'cost_price' => 0,
            'sale_price' => null,
            'offer_price' => null,
            'offer_starts_at' => null,
            'offer_ends_at' => null,
            'image' => null,
            'category' => 'figures',
            'category_id' => null,
            'rating' => 0,
            'reviews' => 0,
            'is_active' => true,
            'is_featured' => false,
            'is_new' => false,
            'is_bestseller' => false,
            'is_limited_edition' => false,
            'is_preorder' => false,
            'is_upcoming' => false,
            'is_bookable' => false,
            'availability' => Product::AVAILABILITY_IN_STOCK,
            'booking_fee' => null,
        ];
    }

    public function withCategory(?string $slug = null): static
    {
        return $this->state(fn () => [
            'category_id' => \App\Models\Category::factory()->create(
                $slug ? ['name' => $slug, 'slug' => $slug] : []
            )->id,
        ]);
    }
}
