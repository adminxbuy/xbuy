<?php

namespace App\Services;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Illuminate\Support\Facades\Log;

class ImageOptimizer
{
    /**
     * Optimize local image by URL or path.
     * Resizes the image to max 1200px width/height (maintaining aspect ratio)
     * and compresses it to reduce file size.
     *
     * @param string $imageUrlOrPath
     * @return bool
     */
    public static function optimize(string $imageUrlOrPath): bool
    {
        try {
            $localPath = self::resolveLocalPath($imageUrlOrPath);

            if (!$localPath || !file_exists($localPath)) {
                Log::warning("ImageOptimizer: Local file not found for '{$imageUrlOrPath}'");
                return false;
            }

            // Check if file is actually an image
            $mimeType = @mime_content_type($localPath);
            if (!$mimeType || !str_starts_with($mimeType, 'image/')) {
                Log::warning("ImageOptimizer: File at '{$localPath}' is not a valid image. Mime: {$mimeType}");
                return false;
            }

            // SVG files shouldn't be compressed/resized via raster engines
            if (str_contains($mimeType, 'svg') || str_contains($mimeType, 'xml')) {
                return true;
            }

            // Initialize Intervention Image Manager (v4)
            $driver = extension_loaded('imagick') ? new ImagickDriver() : new GdDriver();
            $manager = new ImageManager($driver);

            // Read the image
            $image = $manager->read($localPath);

            $modified = false;

            // Resize image if it exceeds 1200px on either dimension
            $maxWidth = 1200;
            $maxHeight = 1200;

            if ($image->width() > $maxWidth || $image->height() > $maxHeight) {
                if ($image->width() > $image->height()) {
                    $image->scale(width: $maxWidth);
                } else {
                    $image->scale(height: $maxHeight);
                }
                $modified = true;
            }

            // Compress/re-encode
            $extension = strtolower(pathinfo($localPath, PATHINFO_EXTENSION));
            switch ($extension) {
                case 'png':
                    $encoded = $image->toPng();
                    $modified = true;
                    break;
                case 'gif':
                    $encoded = $image->toGif();
                    $modified = true;
                    break;
                case 'webp':
                    $encoded = $image->toWebp(80);
                    $modified = true;
                    break;
                case 'avif':
                    $encoded = $image->toAvif(80);
                    $modified = true;
                    break;
                case 'jpg':
                case 'jpeg':
                default:
                    $encoded = $image->toJpeg(80);
                    $modified = true;
                    break;
            }

            if ($modified && isset($encoded)) {
                file_put_contents($localPath, (string) $encoded);
                Log::info("ImageOptimizer: Successfully optimized image '{$localPath}'");
            }

            return true;
        } catch (\Exception $e) {
            Log::error("ImageOptimizer failed for '{$imageUrlOrPath}': " . $e->getMessage(), [
                'exception' => $e
            ]);
            return false;
        }
    }

    /**
     * Resolve absolute or relative URL to a local filesystem path.
     *
     * @param string $url
     * @return string|null
     */
    protected static function resolveLocalPath(string $url): ?string
    {
        // If it's already a local path, return it
        if (file_exists($url)) {
            return $url;
        }

        // Parse path from URL
        $path = parse_url($url, PHP_URL_PATH);
        if (!$path) {
            return null;
        }

        // Clean double slashes
        $path = ltrim($path, '/');

        // Check 1: Private local storage served via Laravel (e.g. storage/{path})
        if (str_starts_with($path, 'storage/')) {
            $relativePath = substr($path, 8); // remove 'storage/'
            
            // Check private disk root (storage/app/private)
            $privateFile = storage_path('app/private/' . $relativePath);
            if (file_exists($privateFile)) {
                return $privateFile;
            }

            // Check public disk root (storage/app/public)
            $publicFile = storage_path('app/public/' . $relativePath);
            if (file_exists($publicFile)) {
                return $publicFile;
            }
        }

        // Check 2: Direct public uploads or assets
        $publicPathFile = public_path($path);
        if (file_exists($publicPathFile)) {
            return $publicPathFile;
        }

        // Check 3: If storage symlink is not set, fallback check
        if (str_starts_with($path, 'storage/')) {
            $relativePath = substr($path, 8);
            $fallbackPath = public_path('storage/' . $relativePath);
            if (file_exists($fallbackPath)) {
                return $fallbackPath;
            }
        }

        return null;
    }
}
