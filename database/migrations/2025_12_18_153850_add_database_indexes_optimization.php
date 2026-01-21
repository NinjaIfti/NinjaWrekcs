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
        if (!Schema::hasTable($table)) {
            return false;
        }

        $driver = DB::connection()->getDriverName();

        // SQLite doesn't support "SHOW INDEX" (MySQL syntax)
        if ($driver === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list('{$table}')");
            foreach ($indexes as $idx) {
                if (($idx->name ?? null) === $index) {
                    return true;
                }
            }
            return false;
        }

        $indexes = DB::select("SHOW INDEX FROM {$table}");
        foreach ($indexes as $idx) {
            if (($idx->Key_name ?? null) === $index) {
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
        // Drop only the indexes added in up() (by name)

        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropIndex('products_category_index');
                $table->dropIndex('products_is_active_category_index');
                $table->dropIndex('products_is_active_created_at_index');
                $table->dropIndex('products_price_index');
                $table->dropIndex('products_quantity_index');
            });
        }

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropIndex('orders_status_index');
                $table->dropIndex('orders_created_at_index');
                $table->dropIndex('orders_user_id_status_index');
                $table->dropIndex('orders_status_created_at_index');
                $table->dropIndex('orders_payment_method_index');
                $table->dropIndex('orders_created_at_status_index');
            });
        }

        if (Schema::hasTable('order_items')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->dropIndex('order_items_product_id_index');
                $table->dropIndex('order_items_order_id_product_id_index');
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex('users_created_at_index');
                $table->dropIndex('users_email_verified_at_index');
            });
        }

        if (Schema::hasTable('coupons')) {
            Schema::table('coupons', function (Blueprint $table) {
                $table->dropIndex('coupons_is_active_index');
                $table->dropIndex('coupons_valid_from_valid_until_index');
                $table->dropIndex('coupons_used_count_index');
            });
        }

        if (Schema::hasTable('visitors')) {
            Schema::table('visitors', function (Blueprint $table) {
                $table->dropIndex('visitors_created_at_index');
                $table->dropIndex('visitors_created_at_ip_address_index');
            });
        }
    }
};
