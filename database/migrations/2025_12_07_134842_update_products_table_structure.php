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
            if (!Schema::hasColumn('products', 'name')) {
                $table->string('name')->after('id');
            }
            if (!Schema::hasColumn('products', 'description')) {
                $table->text('description')->nullable()->after('name');
            }
            if (!Schema::hasColumn('products', 'notes')) {
                $table->text('notes')->nullable()->after('description');
            }
            if (!Schema::hasColumn('products', 'quantity')) {
                $table->integer('quantity')->default(0)->after('notes');
            }
            if (!Schema::hasColumn('products', 'image')) {
                $table->string('image')->nullable()->after('quantity');
            }
            if (!Schema::hasColumn('products', 'category')) {
                $table->enum('category', ['figures', 'knives', 'stickers'])->default('figures')->after('image');
            }
            if (!Schema::hasColumn('products', 'rating')) {
                $table->integer('rating')->default(0)->after('category');
            }
            if (!Schema::hasColumn('products', 'reviews')) {
                $table->integer('reviews')->default(0)->after('rating');
            }
            if (!Schema::hasColumn('products', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('reviews');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            //
        });
    }
};
