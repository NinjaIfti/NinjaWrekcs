<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->enum('availability', ['in_stock', 'preorder', 'upcoming'])
                ->default('in_stock')
                ->after('is_active');
            $table->decimal('booking_fee', 10, 2)->nullable()->after('availability');
        });

        // Backfill. `upcoming` wins over `preorder`: a product flagged both was
        // not purchasable before and must stay not purchasable.
        DB::table('products')->where('is_upcoming', true)->update(['availability' => 'upcoming']);
        DB::table('products')->where('is_upcoming', false)->where('is_preorder', true)->update(['availability' => 'preorder']);

        // 200 was the hardcoded booking amount in CartController, CheckoutController
        // and the checkout view. It becomes data.
        DB::table('products')->where('is_bookable', true)->update(['booking_fee' => 200.00]);
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['availability', 'booking_fee']);
        });
    }
};
