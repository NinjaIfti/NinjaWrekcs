<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('sale_price', 10, 2)->nullable()->after('price');
            $table->boolean('is_new')->default(false)->after('is_featured');
            $table->boolean('is_bestseller')->default(false)->after('is_new');
            $table->boolean('is_limited_edition')->default(false)->after('is_bestseller');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['sale_price', 'is_new', 'is_bestseller', 'is_limited_edition']);
        });
    }
};
