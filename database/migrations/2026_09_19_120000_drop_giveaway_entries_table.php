<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The giveaway feature has been removed, so its table goes with it.
 *
 * Verified empty on production before dropping (0 rows), so no entry data is
 * lost. down() recreates the table exactly as the original pair of migrations
 * left it - created with a non-null order_id, then made nullable - so a
 * rollback restores a working schema rather than an approximation.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('giveaway_entries');
    }

    public function down(): void
    {
        Schema::create('giveaway_entries', function (Blueprint $table) {
            $table->id();
            // Nullable here because a later migration made it so; recreating it
            // nullable straight away lands on the same final shape.
            $table->foreignId('order_id')->nullable()->unique()->constrained()->cascadeOnDelete();
            $table->string('phone', 30);
            $table->string('invoice_number', 50)->nullable();
            $table->timestamp('order_date')->nullable();
            $table->timestamps();
        });
    }
};
