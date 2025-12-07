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
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            }
            if (!Schema::hasColumn('orders', 'name')) {
                $table->string('name')->after('user_id');
            }
            if (!Schema::hasColumn('orders', 'phone')) {
                $table->string('phone')->after('name');
            }
            if (!Schema::hasColumn('orders', 'address')) {
                $table->text('address')->after('phone');
            }
            if (!Schema::hasColumn('orders', 'email')) {
                $table->string('email')->nullable()->after('address');
            }
            if (!Schema::hasColumn('orders', 'subtotal')) {
                $table->decimal('subtotal', 10, 2)->after('email');
            }
            if (!Schema::hasColumn('orders', 'discount')) {
                $table->decimal('discount', 10, 2)->default(0)->after('subtotal');
            }
            if (!Schema::hasColumn('orders', 'total')) {
                $table->decimal('total', 10, 2)->after('discount');
            }
            if (!Schema::hasColumn('orders', 'payment_method')) {
                $table->string('payment_method')->default('bkash')->after('total');
            }
            if (!Schema::hasColumn('orders', 'transaction_number')) {
                $table->string('transaction_number')->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('orders', 'sending_number')) {
                $table->string('sending_number')->nullable()->after('transaction_number');
            }
            if (!Schema::hasColumn('orders', 'status')) {
                $table->enum('status', ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'])->default('pending')->after('sending_number');
            }
            if (!Schema::hasColumn('orders', 'save_info')) {
                $table->boolean('save_info')->default(false)->after('status');
            }
            if (!Schema::hasColumn('orders', 'terms_accepted')) {
                $table->boolean('terms_accepted')->default(false)->after('save_info');
            }
            if (!Schema::hasColumn('orders', 'notes')) {
                $table->text('notes')->nullable()->after('terms_accepted');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $columns = [
                'user_id', 'name', 'phone', 'address', 'email', 'subtotal', 
                'discount', 'total', 'payment_method', 'transaction_number', 
                'sending_number', 'status', 'save_info', 'terms_accepted', 'notes'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
