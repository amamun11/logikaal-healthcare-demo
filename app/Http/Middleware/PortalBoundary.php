<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/** Portal accounts never enter the legacy staff routes, even by a direct URL. */
class PortalBoundary
{
    public function handle(Request $request, Closure $next)
    {
        $role = $request->user()?->group;
        if (in_array($role, ['Patient', 'Doctor'], true)) {
            $home = '/portal/'.strtolower($role);
            if ($request->is('/') || $request->is('dashboard')) {
                return redirect($home);
            }
            if (! $request->is('portal', 'portal/*', 'tests', 'patient/login', 'doctor/login')) {
                abort(403, 'This page is for hospital staff. Use your portal.');
            }
        }
        return $next($request);
    }
}
