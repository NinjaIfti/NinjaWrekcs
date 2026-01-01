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
        Schema::create('order_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Admin who made the change
            $table->string('change_type'); // 'item_added', 'item_removed', 'item_quantity_changed', 'order_updated'
            $table->text('description'); // Human-readable description
            $table->json('old_data')->nullable(); // Previous state
            $table->json('new_data')->nullable(); // New state
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_changes');
    }
};
