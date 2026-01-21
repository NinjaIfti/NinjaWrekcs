<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a category for products
        $category = Category::factory()->create([
            'name' => 'Test Category',
            'slug' => 'test-category',
        ]);
        $this->categoryId = $category->id;
    }

    public function test_user_cannot_add_upcoming_product_to_cart(): void
    {
        $product = Product::factory()->create([
            'category_id' => $this->categoryId,
            'is_upcoming' => true,
            'is_active' => true,
            'quantity' => 10,
            'price' => 1000,
        ]);

        $response = $this->post(route('cart.add', $product), [
            'quantity' => 1,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['error']);
        $this->assertStringContainsString('upcoming', session('error'));
    }

    public function test_user_cannot_add_price_tba_product_to_cart(): void
    {
        $product = Product::factory()->create([
            'category_id' => $this->categoryId,
            'price_tba' => true,
            'is_active' => true,
            'quantity' => 10,
        ]);

        $response = $this->post(route('cart.add', $product), [
            'quantity' => 1,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['error']);
        $this->assertStringContainsString('price', strtolower(session('error')));
    }

    public function test_user_can_add_regular_product_to_cart(): void
    {
        $product = Product::factory()->create([
            'category_id' => $this->categoryId,
            'is_upcoming' => false,
            'price_tba' => false,
            'is_active' => true,
            'quantity' => 10,
            'price' => 1000,
        ]);

        $response = $this->post(route('cart.add', $product), [
            'quantity' => 1,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $cartItems = \Cart::getContent();
        $this->assertCount(1, $cartItems);
        $this->assertEquals($product->id, $cartItems->first()->id);
    }

    public function test_user_cannot_mix_bookable_and_regular_items(): void
    {
        $regularProduct = Product::factory()->create([
            'category_id' => $this->categoryId,
            'is_bookable' => false,
            'is_active' => true,
            'quantity' => 10,
            'price' => 1000,
        ]);

        $bookableProduct = Product::factory()->create([
            'category_id' => $this->categoryId,
            'is_bookable' => true,
            'is_active' => true,
            'quantity' => 10,
            'price' => 2000,
        ]);

        // Add regular product first
        $this->post(route('cart.add', $regularProduct), ['quantity' => 1]);
        
        // Try to add bookable product
        $response = $this->post(route('cart.add', $bookableProduct), ['quantity' => 1]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['error']);
        $this->assertStringContainsString('pre-order', strtolower(session('error')));
    }

    public function test_user_cannot_mix_regular_and_bookable_items(): void
    {
        $regularProduct = Product::factory()->create([
            'category_id' => $this->categoryId,
            'is_bookable' => false,
            'is_active' => true,
            'quantity' => 10,
            'price' => 1000,
        ]);

        $bookableProduct = Product::factory()->create([
            'category_id' => $this->categoryId,
            'is_bookable' => true,
            'is_active' => true,
            'quantity' => 10,
            'price' => 2000,
        ]);

        // Add bookable product first
        $this->post(route('cart.add', $bookableProduct), ['quantity' => 1]);
        
        // Try to add regular product
        $response = $this->post(route('cart.add', $regularProduct), ['quantity' => 1]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['error']);
        $this->assertStringContainsString('in-stock', strtolower(session('error')));
    }

    public function test_bookable_product_deducts_booking_fee_from_price(): void
    {
        $product = Product::factory()->create([
            'category_id' => $this->categoryId,
            'is_bookable' => true,
            'is_active' => true,
            'quantity' => 10,
            'price' => 1000,
        ]);

        $this->post(route('cart.add', $product), ['quantity' => 1]);

        $cartItem = \Cart::get($product->id);
        $this->assertEquals(800, $cartItem->price); // 1000 - 200 booking fee
        $this->assertEquals(1000, $cartItem->attributes->original_price);
    }
}
