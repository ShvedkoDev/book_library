<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAccessCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip auth routes
        if ($request->routeIs('filament.*.auth.*')) {
            return $next($request);
        }

        // Check if user is authenticated and is an active admin.
        // Note: use isAdmin() (role === 'admin'), NOT an is_admin attribute -
        // there is no is_admin column, so $user->is_admin is always null.
        if (auth()->check() && auth()->user()->isAdmin() && auth()->user()->is_active) {
            return $next($request);
        }

        // If not admin, show access denied
        abort(403, 'Access denied. Admin privileges required.');
    }
}
