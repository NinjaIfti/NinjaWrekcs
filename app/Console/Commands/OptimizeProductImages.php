<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariantImage;
use App\Services\ProductImageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Downscale and re-encode the product photos already on disk.
 *
 * Uploads went to disk untouched until ProductImageService::store() took over,
 * so production holds 84MB across 163 files - 22 of them over 2MB - served at
 * full resolution into a card 288px tall. New uploads are handled on the way
 * in; this is the backfill for everything already there.
 *
 * Each file is re-encoded to WebP at ProductImageService::MAX_EDGE. The path
 * changes with the extension, so every row naming the old path is updated
 * before the old file goes: products.image, products.cover_photo,
 * product_images.path and product_variant_images.path. One file is routinely
 * named by several rows - the merge copied paths onto variants rather than
 * copying files - which is exactly why they are updated by path rather than
 * per product.
 *
 * A file that would not get smaller is left alone, so the command is safe to
 * re-run: the second pass finds nothing worth doing.
 *
 * Dry run by default; --commit applies.
 */
class OptimizeProductImages extends Command
{
    protected $signature = 'products:optimize-images
                            {--commit : Actually rewrite the files. Without this the command only shows the plan}
                            {--limit=0 : Stop after this many files, for a cautious first pass}';

    protected $description = 'Downscale and re-encode existing product photos as WebP, updating every row that names them';

    public function __construct(private readonly ProductImageService $images)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $disk = Storage::disk('public');
        $paths = $this->referencedPaths();

        if ($paths->isEmpty()) {
            $this->info('No referenced product images. Nothing to do.');

            return self::SUCCESS;
        }

        $limit = (int) $this->option('limit');
        $commit = (bool) $this->option('commit');

        $this->line('');
        $this->info("Referenced image paths: {$paths->count()}");

        $rows = [];
        $before = 0;
        $after = 0;
        $rewritten = 0;
        $skipped = 0;
        $missing = 0;

        foreach ($paths as $path) {
            if (! $disk->exists($path)) {
                // Dangling references are a separate defect with its own fix -
                // see ProductImageService. Count them and move on.
                $missing++;
                continue;
            }

            if (str_ends_with(strtolower($path), '.gif')) {
                $skipped++;
                continue;
            }

            $originalSize = $disk->size($path);
            $encoded = $this->images->encode($disk->path($path));

            if ($encoded === null || strlen($encoded) >= $originalSize) {
                // Already small enough, or GD cannot read it. Either way the
                // original is the better file.
                $skipped++;
                continue;
            }

            $before += $originalSize;
            $after += strlen($encoded);
            $rewritten++;

            $newPath = preg_replace('/\.[^.]+$/', '', $path) . '.webp';

            $rows[] = [
                'old' => $path,
                'new' => $newPath,
                'saved' => $originalSize - strlen($encoded),
            ];

            if ($commit) {
                $disk->put($newPath, $encoded);
                $this->repoint($path, $newPath);

                if ($newPath !== $path) {
                    $disk->delete($path);
                }
            }

            if ($limit > 0 && $rewritten >= $limit) {
                break;
            }
        }

        $this->renderSummary($rows, $before, $after, $rewritten, $skipped, $missing);

        if (! $commit) {
            $this->line('');
            $this->warn('Dry run. Nothing was changed. Re-run with --commit to apply.');
            $this->warn('Take a copy of storage/app/public/products first - the originals are replaced.');

            return self::SUCCESS;
        }

        $this->forgetCaches();

        $this->line('');
        $this->info("Rewrote {$rewritten} files.");

        return self::SUCCESS;
    }

    /**
     * Every path any row names, deduplicated.
     *
     * @return \Illuminate\Support\Collection<int, string>
     */
    private function referencedPaths(): \Illuminate\Support\Collection
    {
        return collect()
            ->merge(Product::whereNotNull('image')->where('image', '<>', '')->pluck('image'))
            ->merge(Product::whereNotNull('cover_photo')->where('cover_photo', '<>', '')->pluck('cover_photo'))
            ->merge(ProductImage::pluck('path'))
            ->merge(ProductVariantImage::pluck('path'))
            ->filter()
            ->unique()
            ->values();
    }

    /**
     * Move every reference from the old path to the new one.
     *
     * By path, not by product: one file is routinely named by a source
     * product's gallery, a merged product's legacy column and a variant row at
     * the same time, and missing any of them would strand that row on a file
     * that is about to be deleted.
     */
    private function repoint(string $old, string $new): void
    {
        DB::transaction(function () use ($old, $new) {
            Product::where('image', $old)->update(['image' => $new]);
            Product::where('cover_photo', $old)->update(['cover_photo' => $new]);
            ProductImage::where('path', $old)->update(['path' => $new]);
            ProductVariantImage::where('path', $old)->update(['path' => $new]);
        });
    }

    /**
     * @param  array<int, array{old: string, new: string, saved: int}>  $rows
     */
    private function renderSummary(array $rows, int $before, int $after, int $rewritten, int $skipped, int $missing): void
    {
        if ($missing > 0) {
            $this->warn("{$missing} referenced paths have no file on disk. They are left for the image fallback to handle.");
        }

        if ($skipped > 0) {
            $this->line("{$skipped} already small enough, or not worth re-encoding.");
        }

        if ($rewritten === 0) {
            $this->line('');
            $this->info('Nothing to rewrite.');

            return;
        }

        $biggest = collect($rows)->sortByDesc('saved')->take(10);

        $this->line('');
        $this->table(
            ['File', 'Saved'],
            $biggest->map(fn (array $row) => [
                \Illuminate\Support\Str::limit($row['old'], 50),
                $this->humanize($row['saved']),
            ])->all()
        );

        $this->line('');
        $this->info(sprintf(
            '%d files: %s -> %s (%.0f%% smaller)',
            $rewritten,
            $this->humanize($before),
            $this->humanize($after),
            $before > 0 ? (1 - $after / $before) * 100 : 0
        ));
    }

    private function humanize(int $bytes): string
    {
        return $bytes >= 1048576
            ? sprintf('%.1f MB', $bytes / 1048576)
            : sprintf('%.0f KB', $bytes / 1024);
    }

    private function forgetCaches(): void
    {
        foreach ([
            'homepage_data',
            'shop_categories',
            'shop_category_counts',
            'shop_price_range',
            'deals_page_data',
        ] as $key) {
            \Illuminate\Support\Facades\Cache::forget($key);
        }

        Product::clearShopListingCache();
    }
}
