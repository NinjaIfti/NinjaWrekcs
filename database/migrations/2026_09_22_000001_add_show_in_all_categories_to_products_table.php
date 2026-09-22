<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A product that is listed under every category, not only its own.
 *
 * Deliberately a flag on the one product row rather than copies of it: stock,
 * variants, the cart, orders and reports all key off products.id, so a single
 * row shown in many places cannot drift out of step the way duplicates would.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('products', 'show_in_all_categories')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            $table->boolean('show_in_all_categories')->default(false)->after('is_featured');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('products', 'show_in_all_categories')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('show_in_all_categories');
        });
    }
};
