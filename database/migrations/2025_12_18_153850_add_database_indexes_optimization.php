<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Products table indexes (skip existing ones)
        Schema::table('products', function (Blueprint $table) {
            if (!$this->hasIndex('products', 'products_category_index')) {
                $table->index('category');
            }
            if (!$this->hasIndex('products', 'products_is_active_category_index')) {
                $table->index(['is_active', 'category']);
            }
            if (!$this->hasIndex('products', 'products_is_active_created_at_index')) {
                $table->index(['is_active', 'created_at']);
            }
            if (!$this->hasIndex('products', 'products_price_index')) {
                $table->index('price');
            }
            if (!$this->hasIndex('products', 'products_quantity_index')) {
                $table->index('quantity');
            }
        });

        // Orders table indexes
        Schema::table('orders', function (Blueprint $table) {
            if (!$this->hasIndex('orders', 'orders_status_index')) {
                $table->index('status');
            }
            if (!$this->hasIndex('orders', 'orders_created_at_index')) {
                $table->index('created_at');
            }
            if (!$this->hasIndex('orders', 'orders_user_id_status_index')) {
                $table->index(['user_id', 'status']);
            }
            if (!$this->hasIndex('orders', 'orders_status_created_at_index')) {
                $table->index(['status', 'created_at']);
            }
            if (!$this->hasIndex('orders', 'orders_payment_method_index')) {
                $table->index('payment_method');
            }
            if (!$this->hasIndex('orders', 'orders_created_at_status_index')) {
                $table->index(['created_at', 'status']);
            }
        });

        // Order items table indexes  
        Schema::table('order_items', function (Blueprint $table) {
            if (!$this->hasIndex('order_items', 'order_items_product_id_index')) {
                $table->index('product_id');
            }
            if (!$this->hasIndex('order_items', 'order_items_order_id_product_id_index')) {
                $table->index(['order_id', 'product_id']);
            }
        });

        // Users table indexes
        Schema::table('users', function (Blueprint $table) {
            if (!$this->hasIndex('users', 'users_created_at_index')) {
                $table->index('created_at');
            }
            if (!$this->hasIndex('users', 'users_email_verified_at_index')) {
                $table->index('email_verified_at');
            }
        });

        // Coupons table indexes
        Schema::table('coupons', function (Blueprint $table) {
            if (!$this->hasIndex('coupons', 'coupons_is_active_index')) {
                $table->index('is_active');
            }
            if (!$this->hasIndex('coupons', 'coupons_valid_from_valid_until_index')) {
                $table->index(['valid_from', 'valid_until']);
            }
            if (!$this->hasIndex('coupons', 'coupons_used_count_index')) {
                $table->index('used_count');
            }
        });

        // Visitors table indexes (already has ip_address and last_visit_at indexes)
        Schema::table('visitors', function (Blueprint $table) {
            if (!$this->hasIndex('visitors', 'visitors_created_at_index')) {
                $table->index('created_at');
            }
            if (!$this->hasIndex('visitors', 'visitors_created_at_ip_address_index')) {
                $table->index(['created_at', 'ip_address']);
            }
        });
    }

    /**
     * Check if index exists
     */
    private function hasIndex(string $table, string $index): bool
    {
        $indexes = DB::select("SHOW INDEX FROM {$table}");
        foreach ($indexes as $idx) {
            if ($idx->Key_name === $index) {
                return true;
            }
        }
        return false;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Products
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
            $table->dropIndex(['category']);
            $table->dropIndex(['is_featured']);
            $table->dropIndex(['is_active', 'category']);
            $table->dropIndex(['is_active', 'created_at']);
            $table->dropIndex(['price']);
            $table->dropIndex(['quantity']);
        });

        // Orders
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['user_id', 'status']);
            $table->dropIndex(['status', 'created_at']);
            $table->dropIndex(['payment_method']);
            $table->dropIndex(['created_at', 'status']);
        });

        // Order items
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex(['product_id']);
            $table->dropIndex(['order_id', 'product_id']);
        });

        // Users
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['email_verified_at']);
        });

        // Coupons
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
            $table->dropIndex(['valid_from', 'valid_until']);
            $table->dropIndex(['used_count']);
        });

        // Visitors
        Schema::table('visitors', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['ip_address']);
            $table->dropIndex(['created_at', 'ip_address']);
        });
    }
};
