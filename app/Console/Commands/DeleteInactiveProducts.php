<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\ProductImageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Delete deactivated products that nothing has been ordered from.
 *
 * The important rule, and the reason this command is not a one-line delete:
 * order_items.product_id is ON DELETE CASCADE. Deleting a product that appears
 * on a past order takes that order's line items with it - the order survives
 * as a shell with a total but no contents, and the sale disappears from every
 * report built on order_items.
 *
 * Most deactivated products are merge sources, and merging deliberately
 * deactivated rather than deleted them for exactly this reason. So a product
 * referenced by any order is SKIPPED, loudly, and the command says how much
 * history that protected.
 *
 * Dry run by default; --commit applies. Unlike the merges, deleting cannot be
 * reversed by another command - only by restoring a backup.
 */
class DeleteInactiveProducts extends Command
{
    protected $signature = 'products:delete-inactive
                            {--commit : Actually delete. Without this the command only shows the plan}';

    protected $description = 'Delete deactivated products that have never been ordered, keeping any that carry order history';

    public function __construct(private readonly ProductImageService $images)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $inactive = Product::with(['images', 'variants.images'])
            ->where('is_active', false)
            ->orderBy('id')
            ->get();

        if ($inactive->isEmpty()) {
            $this->info('No inactive products. Nothing to do.');

            return self::SUCCESS;
        }

        // One query for all of them rather than one per product.
        $orderCounts = DB::table('order_items')
            ->selectRaw('product_id, COUNT(*) AS items, COUNT(DISTINCT order_id) AS orders, COALESCE(SUM(subtotal), 0) AS value')
            ->whereIn('product_id', $inactive->pluck('id'))
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id');

        $deletable = $inactive->filter(fn (Product $p) => ! $orderCounts->has($p->id));
        $protected = $inactive->reject(fn (Product $p) => ! $orderCounts->has($p->id));

        $this->line('');
        $this->info("Inactive products: {$inactive->count()}");

        if ($protected->isNotEmpty()) {
            $this->line('');
            $this->warn('KEPT - these carry order history, and deleting them would delete it too:');
            $this->table(
                ['Id', 'Name', 'Order items', 'Orders', 'Value'],
                $protected->map(function (Product $p) use ($orderCounts) {
                    $counts = $orderCounts->get($p->id);

                    return [
                        $p->id,
                        \Illuminate\Support\Str::limit($p->name, 45),
                        $counts->items,
                        $counts->orders,
                        number_format((float) $counts->value, 2),
                    ];
                })->all()
            );

            $this->warn(sprintf(
                'Keeping %d products protects %d order items across %d orders, worth %s.',
                $protected->count(),
                $orderCounts->sum('items'),
                $orderCounts->sum('orders'),
                number_format((float) $orderCounts->sum('value'), 2)
            ));
        }

        $this->line('');

        if ($deletable->isEmpty()) {
            $this->info('Nothing can be deleted safely - every inactive product has been ordered.');

            return self::SUCCESS;
        }

        $this->info('TO DELETE - never ordered, so no history is lost:');
        $this->table(
            ['Id', 'Name', 'Stock', 'Images', 'Variants'],
            $deletable->map(fn (Product $p) => [
                $p->id,
                \Illuminate\Support\Str::limit($p->name, 45),
                $p->quantity,
                $p->images->count(),
                $p->variants->count(),
            ])->all()
        );

        $strandedStock = (int) $deletable->sum(fn (Product $p) => $p->availableStock());
        if ($strandedStock > 0) {
            $this->warn("These hold {$strandedStock} units of stock between them. Deleting discards that count.");
        }

        if (! $this->option('commit')) {
            $this->line('');
            $this->warn('Dry run. Nothing was changed. Re-run with --commit to apply.');
            $this->warn('There is no undo command for this one - only a database restore.');

            return self::SUCCESS;
        }

        // Collect the paths while the rows still exist - the image rows cascade
        // away with the product, so they cannot be read afterwards. The files
        // themselves are swept only once the rows are gone, because whether a
        // file may be deleted depends on what still references it.
        $candidatePaths = $deletable
            ->flatMap(fn (Product $product) => $this->imagePathsFor($product))
            ->unique()
            ->values();

        DB::transaction(function () use ($deletable) {
            foreach ($deletable as $product) {
                $product->delete();
            }
        });

        $deletedFiles = 0;

        foreach ($candidatePaths as $path) {
            if ($this->images->deleteIfUnreferenced($path)) {
                $deletedFiles++;
            }
        }

        $this->forgetCaches();

        $this->line('');
        $this->info("Deleted {$deletable->count()} products and {$deletedFiles} image files.");

        if ($protected->isNotEmpty()) {
            $this->info("Kept {$protected->count()} that carry order history.");
        }

        return self::SUCCESS;
    }

    /**
     * Every image file belonging to a product, including its variants'.
     *
     * @return array<int, string>
     */
    private function imagePathsFor(Product $product): array
    {
        $paths = [];

        if ($product->image) {
            $paths[] = $product->image;
        }

        if ($product->cover_photo) {
            $paths[] = $product->cover_photo;
        }

        foreach ($product->images as $image) {
            $paths[] = $image->path;
        }

        foreach ($product->variants as $variant) {
            foreach ($variant->images as $image) {
                $paths[] = $image->path;
            }
        }

        // The merge copied a source's photo onto the merged product, so the
        // same path can belong to two rows. These are candidates only: whether
        // each file may actually be deleted is ProductImageService's call,
        // after the rows are gone.
        return array_values(array_unique($paths));
    }

    private function forgetCaches(): void
    {
        foreach ([
            'homepage_data',
            'shop_categories',
            'shop_category_counts',
            'shop_price_range',
            'admin_dashboard_stats',
            'admin_financial_data',
            'deals_page_data',
        ] as $key) {
            \Illuminate\Support\Facades\Cache::forget($key);
        }

        Product::clearShopListingCache();
    }
}
