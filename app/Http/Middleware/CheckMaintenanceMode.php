<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip check for admin routes
        if ($request->is('admin/*') || $request->is('admin')) {
            return $next($request);
        }

        // Check if maintenance mode is enabled
        $maintenanceMode = Setting::get('maintenance_mode', false);

        if ($maintenanceMode) {
            // Get allowed IPs
            $allowedIps = Setting::get('maintenance_allow_ips', []);
            $userIp = $request->ip();

            // Check if user's IP is in allowed list
            if (!in_array($userIp, $allowedIps)) {
                // Get maintenance message
                $message = Setting::get('maintenance_message', 'We are currently performing scheduled maintenance. Please check back soon.');
                $retryAfter = Setting::get('maintenance_retry_after', 3600);

                return response()->view('errors.503', [
                    'message' => $message,
                ], 503)->header('Retry-After', $retryAfter);
            }
        }

        return $next($request);
    }
}
