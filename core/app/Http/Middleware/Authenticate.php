<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            $prefix = config('app.admin_prefix', 'admin');
            if (\Request::is($prefix) || \Request::is($prefix . '/*')){
                return route('admin.login');
            }else{
                return route('user.login');
            }
        }
    }
}
