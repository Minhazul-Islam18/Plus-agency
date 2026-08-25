<?php

namespace App\Listeners;

use App\Services\ImageConverter;
use UniSharp\LaravelFilemanager\Events\ImageWasUploaded;

class ConvertUploadedLfmImage
{
    public function handle(ImageWasUploaded $event)
    {
        $path = $event->path();
        ImageConverter::handle($path);

        // unisharp/laravel-filemanager v2.12.1's LfmPath::upload() recomputes
        // the event path AFTER generateThumbnail() runs, but that method sets
        // $this->thumb(true) internally and never resets it — so the
        // recomputed path inherits stale "thumb mode" and this event fires
        // with the thumbnail's path instead of the main uploaded file's. Not
        // something we can fix in vendor/ (wiped on the next composer
        // install), so detect it here and also convert the main file it
        // should have pointed to.
        if (preg_match('#/thumbs/([^/]+)$#', $path, $m)) {
            $mainPath = preg_replace('#/thumbs/[^/]+$#', '/' . $m[1], $path);
            if ($mainPath !== $path) {
                ImageConverter::handle($mainPath);
            }
        }
    }
}
