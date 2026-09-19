<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Reverse a products:merge-variants run.
 *
 * Because the merge only deactivated the source products, undoing it is just:
 * reactivate them, and remove the merged product that was created. The merged
 * product is only deleted when nothing has been ordered from it - once a real
 * order exists against it, deleting would cascade to order_items and destroy
 * that order's history, so it is deactivated instead.
 */
class UnmergeProductsFromVariants extends Command
{
    protected $signature = 'products:unmerge-variants
                            {merged : The merged product id created by products:merge-variants}
                            {--ids= : Comma-separated source product ids to reactivate}
                            {--commit : Actually perform the undo. Without this the command only shows the plan}';

    protected $description = 'Undo a products:merge-variants run, restoring the original split products';

    public function handle(): int
    {
        $merged = Product::with('variants')->find($this->argument('merged'));

        if (! $merged) {
            $this->error('Merged product #' . $this->argument('merged') . ' not found.');

            return self::FAILURE;
        }

        $ids = collect(explode(',', (string) $this->option('ids')))
            ->map(fn ($id) => (int) trim($id))
            ->filter()
            ->values();

        $sources = Product::whereIn('id', $ids)->get();

        if ($sources->isEmpty()) {
            $this->error('No source products found for --ids.');

            return self::FAILURE;
        }

        $orderedFrom = $merged->orderItems()->count();

        $this->line('');
        $this->line("Merged product #{$merged->id} \"{$merged->name}\" has {$merged->variants->count()} variants.");
        $this->line('Reactivating ' . $sources->count() . ' source products: ' . $sources->pluck('id')->implode(', '));

        if ($orderedFrom > 0) {
            $this->warn("{$orderedFrom} order item(s) reference the merged product, so it will be DEACTIVATED rather than deleted - deleting would cascade and destroy that order history.");
        } else {
            $this->line('Nothing has been ordered from the merged product, so it will be deleted.');
        }

        $this->line('');

        if (! $this->option('commit')) {
            $this->warn('Dry run. Nothing was changed. Re-run with --commit to apply.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($merged, $sources, $orderedFrom) {
            foreach ($sources as $source) {
                $source->update(['is_active' => true]);
            }

            if ($orderedFrom > 0) {
                $merged->update(['is_active' => false]);
            } else {
                // Variants and their images cascade from product_variants.
                $merged->delete();
            }
        });

        $this->info('Undo complete. Original products are active again.');

        return self::SUCCESS;
    }
}
