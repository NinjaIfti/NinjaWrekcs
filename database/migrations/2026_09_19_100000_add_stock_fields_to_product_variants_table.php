<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->string('sku')->nullable()->unique()->after('name');
            $table->decimal('sale_price', 10, 2)->nullable()->after('price');
            $table->unsignedInteger('quantity')->default(0)->after('sale_price');
            $table->boolean('is_active')->default(true)->after('quantity');
        });

        // No per-variant sales history exists to reconstruct true counts from, so
        // copy the parent's stock onto each variant. This overstates total stock
        // rather than understating it, so nothing silently goes out of stock on
        // deploy. The admin reconciles real counts afterwards.
        DB::table('product_variants')->update([
            'quantity' => DB::raw('(SELECT quantity FROM products WHERE products.id = product_variants.product_id)'),
            'is_active' => true,
        ]);
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['sku', 'sale_price', 'quantity', 'is_active']);
        });
    }
};
