<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProductAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_bookable_product_reports_its_booking_fee(): void
    {
        $product = Product::factory()->withCategory()->create(['booking_fee' => 200.00]);

        $this->assertTrue($product->requiresBooking());
        $this->assertSame(200.00, (float) $product->booking_fee);
    }

    public function test_a_product_without_a_booking_fee_does_not_require_booking(): void
    {
        $product = Product::factory()->withCategory()->create(['booking_fee' => null]);

        $this->assertFalse($product->requiresBooking());
    }

    public function test_an_upcoming_product_is_not_purchasable(): void
    {
        $product = Product::factory()->withCategory()->create([
            'availability' => Product::AVAILABILITY_UPCOMING,
        ]);

        $this->assertFalse($product->isPurchasable());
    }

    public function test_in_stock_and_preorder_products_are_purchasable(): void
    {
        $inStock = Product::factory()->withCategory()->create([
            'availability' => Product::AVAILABILITY_IN_STOCK,
        ]);
        $preorder = Product::factory()->withCategory()->create([
            'availability' => Product::AVAILABILITY_PREORDER,
        ]);

        $this->assertTrue($inStock->isPurchasable());
        $this->assertTrue($preorder->isPurchasable());
    }

    public function test_an_inactive_product_is_not_purchasable(): void
    {
        $product = Product::factory()->withCategory()->create([
            'availability' => Product::AVAILABILITY_IN_STOCK,
            'is_active' => false,
        ]);

        $this->assertFalse($product->isPurchasable());
    }
}
