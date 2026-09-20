<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariantImage;
use Illuminate\Support\Facades\Storage;

/**
 * Deleting product image files safely.
 *
 * Merging a family copied each source's image PATH onto the new variant rather
 * than copying the file, so one file on disk is routinely named by several
 * rows at once: a source product's gallery, the merged product's legacy
 * `image` column, and one or more variant image rows.
 *
 * Every delete path used to call Storage::delete() on the path outright, which
 * removed the file while other rows still pointed at it. On production that
 * left 32 of 193 referenced paths with no file behind them, and rendered a
 * broken <img> on three live products.
 */
class ProductImageService
{
    /**
     * Delete the file only if no row in the database still names it.
     *
     * Call this AFTER the owning rows are gone: whatever references remain are
     * then exactly the ones that still need the file. Returns true only when a
     * file was actually removed, so callers can keep an honest count.
     */
    public function deleteIfUnreferenced(?string $path): bool
    {
        if (! $path || $this->isReferenced($path)) {
            return false;
        }

        if (! Storage::disk('public')->exists($path)) {
            return false;
        }

        return Storage::disk('public')->delete($path);
    }

    /**
     * Does any product, gallery row or variant image row still name this file?
     */
    public function isReferenced(string $path): bool
    {
        $onAProduct = Product::query()
            ->where('image', $path)
            ->orWhere('cover_photo', $path)
            ->exists();

        return $onAProduct
            || ProductImage::where('path', $path)->exists()
            || ProductVariantImage::where('path', $path)->exists();
    }
}
