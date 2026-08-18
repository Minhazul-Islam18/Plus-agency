<?php

namespace App\Services;

use App\BasicExtra;
use Illuminate\Support\Facades\Log;

class ImageConverter
{
    // Vector needs no conversion; animated GIF would lose its animation if
    // re-encoded to a static AVIF/WebP frame — always skipped, not a setting.
    protected static $skipExtensions = ['svg', 'gif'];

    protected static $allowedFormats = ['webp', 'avif'];

    /**
     * Compress + convert a just-uploaded LFM file in place, per the admin's
     * Image Optimization setting. Rewrites the file under a new extension,
     * deletes the original, and rebuilds its LFM thumbnail to match.
     */
    public static function handle(string $absolutePath): void
    {
        if (!is_file($absolutePath) || !extension_loaded('imagick')) {
            return;
        }

        $ext = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
        if (in_array($ext, self::$skipExtensions, true)) {
            return;
        }

        $bex = BasicExtra::first();
        if (!$bex || !$bex->image_convert_enabled) {
            return;
        }

        $format = in_array($bex->image_convert_format, self::$allowedFormats, true)
            ? $bex->image_convert_format
            : 'webp';

        if ($ext === $format) {
            return;
        }

        $quality = $bex->image_convert_quality ?: 80;
        $newPath = preg_replace('/\.' . preg_quote($ext, '/') . '$/i', '.' . $format, $absolutePath);

        try {
            $image = new \Imagick($absolutePath);
            $image->setImageFormat($format);
            $image->setImageCompressionQuality($quality);
            $image->stripImage();
            $image->writeImage($newPath);
            $image->destroy();
        } catch (\Throwable $e) {
            Log::error('ImageConverter: failed to convert ' . $absolutePath . ' - ' . $e->getMessage());
            return;
        }

        @unlink($absolutePath);
        self::rebuildThumbnail($absolutePath, $newPath, $format, $quality);
    }

    protected static function rebuildThumbnail(string $oldPath, string $newPath, string $format, int $quality): void
    {
        $oldThumb = dirname($oldPath) . '/thumbs/' . basename($oldPath);
        $newThumb = dirname($newPath) . '/thumbs/' . basename($newPath);

        if (!is_file($oldThumb)) {
            return;
        }

        try {
            $thumb = new \Imagick($oldThumb);
            $thumb->setImageFormat($format);
            $thumb->setImageCompressionQuality($quality);
            $thumb->writeImage($newThumb);
            $thumb->destroy();
            @unlink($oldThumb);
        } catch (\Throwable $e) {
            Log::error('ImageConverter: failed to rebuild thumbnail for ' . $newPath . ' - ' . $e->getMessage());
        }
    }
}
