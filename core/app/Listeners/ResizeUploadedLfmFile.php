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

        // Generic cap for the standalone file manager / any LFM picker that
        // isn't one of the specific admin forms with their own display-size-
        // aware bound (those call copy_resized_image() directly in their
        // own controller instead, e.g. Logo/Partner/Tender). Editable at
        // admin/basicinfo.
        $maxWidth = $bex->image_resize_max_width ?? 1920;
        $maxHeight = $bex->image_resize_max_height ?? 1920;

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
