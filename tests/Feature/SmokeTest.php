<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_migrations_run_on_sqlite(): void
    {
        $this->assertTrue(\Schema::hasTable('products'));
        $this->assertTrue(\Schema::hasTable('product_variants'));
        $this->assertTrue(\Schema::hasTable('giveaway_entries'));
    }

    public function test_factories_build_a_product_with_variants(): void
    {
        $product = \App\Models\Product::factory()->withCategory()->create();
        \App\Models\ProductVariant::factory()->count(3)->create(['product_id' => $product->id]);

        $this->assertCount(3, $product->refresh()->variants);
    }
}
