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
        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45)->index();
            $table->text('user_agent')->nullable();
            $table->string('device_type', 20)->nullable();
            $table->string('browser', 50)->nullable();
            $table->string('os', 50)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('page_visited', 500)->nullable();
            $table->timestamp('last_visit_at')->index();
            $table->integer('visit_count')->default(1);
            $table->timestamps();
            
            // Unique constraint on IP address
            $table->unique('ip_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitors');
    }
};

