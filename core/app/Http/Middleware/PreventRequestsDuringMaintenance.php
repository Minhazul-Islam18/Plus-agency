<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance as Middleware;

class PreventRequestsDuringMaintenance extends Middleware
{
    /**
     * The URIs that should be reachable while maintenance mode is enabled.
     *
     * @var array
     */
    protected $except = [
        'laravel-filemanager',
        'laravel-filemanager/*'
    ];

    public function __construct(Application $app)
    {
        parent::__construct($app);

        $prefix = config('app.admin_prefix', 'admin');
        $this->except = array_merge($this->except, [$prefix, $prefix . '/*']);
    }
}
