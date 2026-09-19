<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Merge colour-split products into one product with variants.
 *
 * The catalogue grew a family per colour - "RGX DAGGER - Red", "- BLUE",
 * "- GREEN" are three separate products. This folds them into one product
 * whose variants are the colours, which is what the storefront now supports.
 *
 * Two hard rules, both enforced and verified:
 *
 *  1. STOCK IS CONSERVED. Each source product's quantity becomes exactly one
 *     variant's quantity. The command aborts if the totals do not match.
 *
 *  2. SOURCE PRODUCTS ARE NEVER DELETED, only deactivated. order_items.product_id
 *     is ON DELETE CASCADE, so deleting a merged-away product would delete its
 *     order items and destroy order history. Deactivating keeps every past order
 *     intact and makes the merge reversible.
 */
class MergeProductsIntoVariants extends Command
{
    protected $signature = 'products:merge-variants
                            {name : The merged product name, e.g. "RGX Dagger"}
                            {--ids= : Comma-separated source product ids, in the order you want the variants}
                            {--names= : Optional comma-separated variant names; defaults to each source name with the common prefix stripped}
                            {--commit : Actually perform the merge. Without this the command only shows the plan}';

    protected $description = 'Fold colour-split products into one product with variants, preserving stock exactly';

    public function handle(): int
    {
        $ids = collect(explode(',', (string) $this->option('ids')))
            ->map(fn ($id) => (int) trim($id))
            ->filter()
            ->values();

        if ($ids->count() < 2) {
            $this->error('Give at least two source ids, e.g. --ids=12,13,14');

            return self::FAILURE;
        }

        // Preserve the order the user listed, not the database's order.
        $sources = Product::with('images')->whereIn('id', $ids)->get()->sortBy(
            fn ($p) => $ids->search($p->id)
        )->values();

        if ($sources->count() !== $ids->count()) {
            $missing = $ids->diff($sources->pluck('id'))->implode(', ');
            $this->error("These product ids do not exist: {$missing}");

            return self::FAILURE;
        }

        $alreadyVariantProducts = $sources->filter(fn ($p) => $p->variants()->exists());
        if ($alreadyVariantProducts->isNotEmpty()) {
            $this->error('These sources already have variants of their own, refusing to nest: ' . $alreadyVariantProducts->pluck('id')->implode(', '));

            return self::FAILURE;
        }

        $variantNames = $this->resolveVariantNames($sources);
        $stockBefore = (int) $sources->sum('quantity');

        $this->line('');
        $this->info('Merged product: ' . $this->argument('name'));
        $this->line('Category taken from source #' . $sources->first()->id . ' (' . ($sources->first()->category_name ?? 'none') . ')');
        $this->line('');

        $this->table(
            ['Source id', 'Source name', 'Becomes variant', 'Price', 'Stock', 'Images'],
            $sources->map(fn ($p, $i) => [
                $p->id,
                \Illuminate\Support\Str::limit($p->name, 40),
                $variantNames[$i],
                number_format($this->priceFor($p), 2),
                $p->quantity,
                $p->images->count(),
            ])->all()
        );

        $this->line("Total stock carried over: <options=bold>{$stockBefore}</> units");
        $this->line('Source products will be DEACTIVATED, never deleted - order history stays intact.');
        $this->line('');

        if (! $this->option('commit')) {
            $this->warn('Dry run. Nothing was changed. Re-run with --commit to apply.');

            return self::SUCCESS;
        }

        $merged = null;

        DB::transaction(function () use ($sources, $variantNames, $stockBefore, &$merged) {
            $first = $sources->first();

            $merged = Product::create([
                'name' => $this->argument('name'),
                'description' => $first->description,
                'notes' => $first->notes,
                'category_id' => $first->category_id,
                'category' => $first->category,
                // A variant product carries no stock or price of its own -
                // availableStock() sums its active variants instead.
                'quantity' => 0,
                'price' => 0,
                'cost_price' => 0,
                'image' => $first->image,
                'cover_photo' => $first->cover_photo,
                'is_active' => true,
                'availability' => $first->availability,
                'booking_fee' => $first->booking_fee,
            ]);

            foreach ($sources as $i => $source) {
                $variant = ProductVariant::create([
                    'product_id' => $merged->id,
                    'name' => $variantNames[$i],
                    'price' => $this->priceFor($source),
                    'sale_price' => null,
                    // The whole point: this source's stock, unchanged.
                    'quantity' => (int) $source->quantity,
                    'is_active' => (bool) $source->is_active,
                    'sort_order' => $i,
                ]);

                foreach ($source->images as $img) {
                    $variant->images()->create([
                        'path' => $img->path,
                        'sort_order' => $img->sort_order,
                    ]);
                }

                // Deactivate, never delete - see the class docblock.
                $source->update(['is_active' => false]);
            }

            $stockAfter = (int) $merged->variants()->sum('quantity');

            if ($stockAfter !== $stockBefore) {
                throw new \RuntimeException(
                    "Stock mismatch, rolling back: {$stockBefore} units before, {$stockAfter} after."
                );
            }
        });

        $this->line('');
        $this->info("Merged into product #{$merged->id} with {$sources->count()} variants.");
        $this->info("Stock verified: {$stockBefore} units in, {$stockBefore} units out.");
        $this->line('');
        $this->line('To undo:');
        $this->line("  php artisan products:unmerge-variants {$merged->id} --ids={$sources->pluck('id')->implode(',')} --commit");

        return self::SUCCESS;
    }

    /**
     * The price a customer actually pays today - an open offer or sale beats the
     * list price, and that is what should carry onto the variant.
     */
    private function priceFor(Product $product): float
    {
        return (float) (app(\App\Services\PricingService::class)->priceFor($product));
    }

    /**
     * "RGX DAGGER - Red" under parent "RGX Dagger" becomes just "Red".
     */
    private function resolveVariantNames($sources): array
    {
        if ($explicit = $this->option('names')) {
            $names = array_map('trim', explode(',', $explicit));

            if (count($names) !== $sources->count()) {
                $this->warn('--names count does not match --ids count; falling back to derived names.');
            } else {
                return $names;
            }
        }

        return $sources->map(function ($p) {
            $name = $p->name;
            $parent = $this->argument('name');

            // Strip the shared prefix case-insensitively, then any leading
            // separator left behind ("- Red" -> "Red").
            if (stripos($name, $parent) === 0) {
                $name = substr($name, strlen($parent));
            }

            $name = trim($name, " \t-–—:,|");

            return $name !== '' ? $name : $p->name;
        })->all();
    }
}
