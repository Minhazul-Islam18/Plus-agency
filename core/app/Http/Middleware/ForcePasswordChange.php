<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class ForcePasswordChange
{
    /**
     * Routes an admin with must_change_password=1 must still be able to reach
     * (the change-password page itself, and logout) even while otherwise
     * locked out of the rest of the panel.
     */
    protected $exempt = ['admin.forcedChangePassword', 'admin.forcedChangePassword.update', 'admin.logout'];

    public function handle($request, Closure $next)
    {
        $admin = Auth::guard('admin')->user();

        if ($admin && $admin->must_change_password && !in_array($request->route()->getName(), $this->exempt)) {
            return redirect()->route('admin.forcedChangePassword');
        }

        return $next($request);
    }
}
