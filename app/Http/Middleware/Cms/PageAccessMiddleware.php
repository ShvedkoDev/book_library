<?php

namespace App\Http\Middleware\Cms;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PageAccessMiddleware
{
    /**
     * Handle an incoming request for page access control
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Add security headers
        $response = $next($request);

        // Security headers for CMS pages
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Cache control headers
        if ($request->route()->getName() === 'cms.page') {
            $response->headers->set('Cache-Control', 'public, max-age=3600');
        }

        return $response;
    }
}
