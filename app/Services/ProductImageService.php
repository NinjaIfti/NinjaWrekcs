<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariantImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

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
     * The longest edge any stored product photo needs.
     *
     * The largest a photo is ever drawn is the product page slideshow; the
     * cards are 288px tall. 1600 covers both at 2x on a retina screen, and
     * everything above it was only ever downscaled by the browser after being
     * paid for on the wire.
     */
    public const MAX_EDGE = 1600;

    private const WEBP_QUALITY = 82;

    /**
     * Store an uploaded photo, downscaled and re-encoded as WebP.
     *
     * Uploads went to disk untouched, so the store was serving 2-3MB originals
     * into a 288px card - 84MB across 163 files, with 22 of them over 2MB.
     *
     * An animated GIF is stored as-is: GD flattens it to a single frame, and a
     * still image is a worse picture than a large one.
     */
    public function store(UploadedFile $file, string $directory = 'products'): string
    {
        if ($this->isAnimatedGif($file->getRealPath())) {
            return $file->store($directory, 'public');
        }

        $encoded = $this->encode($file->getRealPath());

        if ($encoded === null) {
            // Not something GD can read. Keep the original rather than lose it.
            return $file->store($directory, 'public');
        }

        $path = $directory . '/' . Str::random(40) . '.webp';
        Storage::disk('public')->put($path, $encoded);

        return $path;
    }

    /**
     * Downscale and re-encode an image, returning the WebP bytes.
     *
     * Returns null when the file cannot be decoded, so callers can leave the
     * original alone rather than replacing it with nothing.
     */
    public function encode(string $absolutePath): ?string
    {
        try {
            $image = (new ImageManager(new Driver()))->read($absolutePath);
        } catch (\Throwable) {
            return null;
        }

        // scaleDown never enlarges, so a photo already under the cap keeps its
        // dimensions and is only re-encoded.
        $image->scaleDown(width: self::MAX_EDGE, height: self::MAX_EDGE);

        return (string) $image->toWebp(quality: self::WEBP_QUALITY);
    }

    /**
     * An animated GIF holds more than one image descriptor block.
     */
    public function isAnimatedGif(string $absolutePath): bool
    {
        $handle = @fopen($absolutePath, 'rb');

        if ($handle === false) {
            return false;
        }

        $contents = fread($handle, 1024 * 1024);
        fclose($handle);

        if ($contents === false || ! str_starts_with($contents, 'GIF')) {
            return false;
        }

        return preg_match_all('/\x00\x21\xF9\x04/', $contents) > 1;
    }

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
