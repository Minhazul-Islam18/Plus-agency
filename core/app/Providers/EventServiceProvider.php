<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use UniSharp\LaravelFilemanager\Events\ImageWasUploaded;
use App\Services\ImageConverter;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        // LfmPath::upload() fires this for every LFM upload (raster or not);
        // ImageConverter internally no-ops for non-raster/skip-listed types.
        ImageWasUploaded::class => [
            \App\Listeners\ConvertUploadedLfmImage::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
