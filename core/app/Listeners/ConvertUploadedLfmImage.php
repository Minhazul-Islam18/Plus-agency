<?php

namespace App\Listeners;

use App\Services\ImageConverter;
use UniSharp\LaravelFilemanager\Events\ImageWasUploaded;

class ConvertUploadedLfmImage
{
    public function handle(ImageWasUploaded $event)
    {
        ImageConverter::handle($event->path());
    }
}
