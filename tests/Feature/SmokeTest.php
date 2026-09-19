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
        $this->assertTrue(\Schema::hasTable('orders'));

        // This used to assert giveaway_entries existed, as proof that the
        // MySQL-only ALTER in its migration had been guarded for SQLite. The
        // giveaway feature is gone and that table is now dropped by a later
        // migration, so the whole chain running clean is the proof instead.
        $this->assertFalse(\Schema::hasTable('giveaway_entries'));
    }

    public function test_factories_build_a_product_with_variants(): void
    {
        $product = \App\Models\Product::factory()->withCategory()->create();
        \App\Models\ProductVariant::factory()->count(3)->create(['product_id' => $product->id]);

        $this->assertCount(3, $product->refresh()->variants);
    }
}
