<?php

namespace App\Listeners;

use App\BasicExtra;
use UniSharp\LaravelFilemanager\Events\FileWasUploaded;

class ResizeUploadedLfmFile
{
    public function handle(FileWasUploaded $event)
    {
        $bex = BasicExtra::first();

        // The upload modal's checkbox always sends this (default reflects
        // the site-wide setting) — its absence means the request came from
        // somewhere that doesn't know about it, so fall back to the
        // site-wide setting rather than silently skipping the resize.
        $enabled = request()->has('optimize_resize')
            ? request()->boolean('optimize_resize')
            : (bool) ($bex->image_resize_enabled ?? true);

        if (!$enabled) {
            return;
        }

        // The opening picker modal passes its own field's recommended size
        // (optimize_resize_width/height — e.g. Logo's 600x160) when it has
        // one, so the upload is resized to the size that field actually
        // needs. Falls back to the generic site-wide bound (editable at
        // admin/basicinfo) for the standalone file manager and any picker
        // that doesn't have a field-specific size.
        $maxWidth = (int) (request()->input('optimize_resize_width') ?: ($bex->image_resize_max_width ?? 1920));
        $maxHeight = (int) (request()->input('optimize_resize_height') ?: ($bex->image_resize_max_height ?? 1920));

        $path = $event->path();
        copy_resized_image($path, $path, $maxWidth, $maxHeight);

        // Same stale-thumbnail-path bug ConvertUploadedLfmImage works around
        // (see its comment) — this event can fire with the thumb's path
        // instead of the main uploaded file's.
        if (preg_match('#/thumbs/([^/]+)$#', $path, $m)) {
            $mainPath = preg_replace('#/thumbs/[^/]+$#', '/' . $m[1], $path);
            if ($mainPath !== $path && is_file($mainPath)) {
                copy_resized_image($mainPath, $mainPath, $maxWidth, $maxHeight);
            }
        }
    }
}
