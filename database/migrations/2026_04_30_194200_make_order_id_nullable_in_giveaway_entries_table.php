<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE giveaway_entries DROP FOREIGN KEY giveaway_entries_order_id_foreign');
            DB::statement('ALTER TABLE giveaway_entries MODIFY order_id BIGINT UNSIGNED NULL');
            DB::statement('ALTER TABLE giveaway_entries ADD CONSTRAINT giveaway_entries_order_id_foreign FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE');

            return;
        }

        Schema::table('giveaway_entries', function (Blueprint $table) {
            $table->foreignId('order_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE giveaway_entries DROP FOREIGN KEY giveaway_entries_order_id_foreign');
            DB::statement('ALTER TABLE giveaway_entries MODIFY order_id BIGINT UNSIGNED NOT NULL');
            DB::statement('ALTER TABLE giveaway_entries ADD CONSTRAINT giveaway_entries_order_id_foreign FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE');

            return;
        }

        Schema::table('giveaway_entries', function (Blueprint $table) {
            $table->foreignId('order_id')->nullable(false)->change();
        });
    }
};
