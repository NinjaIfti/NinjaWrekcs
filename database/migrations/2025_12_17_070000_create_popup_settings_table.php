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
        Schema::create('popup_settings', function (Blueprint $table) {
            $table->id();
            $table->string('title')->default('Pre-Order Special Offer!');
            $table->text('main_heading')->nullable();
            $table->text('subheading')->nullable();
            $table->text('description')->nullable();
            $table->string('discount_text')->nullable();
            $table->string('discount_amount')->nullable();
            $table->string('badge_text')->nullable();
            $table->string('button_text')->default('Shop Now');
            $table->string('button_url')->default('/shop');
            $table->boolean('is_active')->default(true);
            $table->integer('display_delay')->default(3000);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('popup_settings');
    }
};




