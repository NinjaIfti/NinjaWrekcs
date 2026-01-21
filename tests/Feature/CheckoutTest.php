<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $category = Category::factory()->create([
            'name' => 'Test Category',
            'slug' => 'test-category',
        ]);
        $this->categoryId = $category->id;
    }

    public function test_cod_disabled_for_preorder_bookings(): void
    {
        $user = User::factory()->create();
        
        $product = Product::factory()->create([
            'category_id' => $this->categoryId,
            'is_bookable' => true,
            'is_active' => true,
            'quantity' => 10,
            'price' => 1000,
        ]);

        // Add bookable product to cart
        $this->actingAs($user)->post(route('cart.add', $product), ['quantity' => 1]);

        // Try to checkout with COD
        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '1234567890',
            'address' => '123 Test St',
            'delivery_location' => 'inside_dhaka',
            'payment_method' => 'cod',
            'transaction_number' => '',
            'sending_number' => '',
            'terms_accepted' => true,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['error']);
        $this->assertStringContainsString('cash on delivery', strtolower(session('error')));
        $this->assertStringContainsString('not available', strtolower(session('error')));
    }

    public function test_preorder_booking_amount_stored_correctly(): void
    {
        $user = User::factory()->create();
        
        $product = Product::factory()->create([
            'category_id' => $this->categoryId,
            'is_bookable' => true,
            'is_active' => true,
            'quantity' => 10,
            'price' => 1000,
        ]);

        // Add bookable product to cart
        $this->actingAs($user)->post(route('cart.add', $product), ['quantity' => 2]); // 2 items = 400 booking fee

        // Checkout with mobile banking
        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '1234567890',
            'address' => '123 Test St',
            'delivery_location' => 'inside_dhaka',
            'payment_method' => 'bkash',
            'transaction_number' => 'TXN123',
            'sending_number' => '01712345678',
            'terms_accepted' => true,
        ]);

        $response->assertRedirect();
        
        $order = Order::latest()->first();
        $this->assertNotNull($order);
        $this->assertTrue($order->is_preorder_booking);
        $this->assertEquals(400, $order->booking_amount); // 200 * 2 items
    }

    public function test_regular_order_works_with_cod(): void
    {
        $user = User::factory()->create();
        
        $product = Product::factory()->create([
            'category_id' => $this->categoryId,
            'is_bookable' => false,
            'is_active' => true,
            'quantity' => 10,
            'price' => 1000,
        ]);

        // Add regular product to cart
        $this->actingAs($user)->post(route('cart.add', $product), ['quantity' => 1]);

        // Checkout with COD
        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '1234567890',
            'address' => '123 Test St',
            'delivery_location' => 'inside_dhaka',
            'payment_method' => 'cod',
            'transaction_number' => '',
            'sending_number' => '',
            'terms_accepted' => true,
        ]);

        $response->assertRedirect(route('checkout.success', Order::latest()->first()));
        
        $order = Order::latest()->first();
        $this->assertNotNull($order);
        $this->assertFalse($order->is_preorder_booking);
        $this->assertEquals(0, $order->booking_amount);
    }
}
