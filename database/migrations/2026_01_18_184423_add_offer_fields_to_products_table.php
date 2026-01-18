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
            $table->decimal('offer_price', 10, 2)->nullable()->after('price');
            $table->timestamp('offer_starts_at')->nullable()->after('offer_price');
            $table->timestamp('offer_ends_at')->nullable()->after('offer_starts_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['offer_price', 'offer_starts_at', 'offer_ends_at']);
        });
    }
};
