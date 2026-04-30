<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE giveaway_entries DROP FOREIGN KEY giveaway_entries_order_id_foreign');
        DB::statement('ALTER TABLE giveaway_entries MODIFY order_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE giveaway_entries ADD CONSTRAINT giveaway_entries_order_id_foreign FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE giveaway_entries DROP FOREIGN KEY giveaway_entries_order_id_foreign');
        DB::statement('ALTER TABLE giveaway_entries MODIFY order_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE giveaway_entries ADD CONSTRAINT giveaway_entries_order_id_foreign FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE');
    }
};
