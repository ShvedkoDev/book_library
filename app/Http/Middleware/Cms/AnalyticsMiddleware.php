<?php

namespace App\Http\Middleware\Cms;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AnalyticsMiddleware
{
    /**
     * Handle an incoming request for analytics tracking
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        $response = $next($request);

        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000; // Convert to milliseconds

        // Log page view analytics
        $this->logPageView($request, $response, $duration);

        // Add performance monitoring headers (only in debug mode)
        if (config('app.debug')) {
            $response->headers->set('X-Response-Time', round($duration, 2) . 'ms');
            $response->headers->set('X-Memory-Usage', $this->formatBytes(memory_get_peak_usage(true)));
        }

        return $response;
    }

    /**
     * Log page view for analytics
     */
    protected function logPageView(Request $request, Response $response, float $duration): void
    {
        // Only log successful responses
        if ($response->getStatusCode() >= 400) {
            return;
        }

        $data = [
            'url' => $request->fullUrl(),
            'route' => $request->route()?->getName(),
            'method' => $request->method(),
            'status_code' => $response->getStatusCode(),
            'duration_ms' => round($duration, 2),
            'memory_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            'user_agent' => $request->userAgent(),
            'ip' => $request->ip(),
            'referer' => $request->header('referer'),
            'timestamp' => now()->toISOString(),
        ];

        // Extract route parameters for additional context
        if ($request->route()) {
            $routeParams = $request->route()->parameters();
            if (!empty($routeParams)) {
                $data['route_params'] = $routeParams;
            }
        }

        // Log to analytics channel
        Log::channel('analytics')->info('cms_page_view', $data);

        // You could also send this data to external analytics services like:
        // - Google Analytics Measurement Protocol
        // - Mixpanel
        // - Custom analytics API
        $this->sendToExternalAnalytics($data);
    }

    /**
     * Send analytics data to external services
     */
    protected function sendToExternalAnalytics(array $data): void
    {
        // Example: Send to Google Analytics 4
        if (config('cms.analytics.ga4_measurement_id')) {
            try {
                // Implementation for GA4 Measurement Protocol would go here
                // This is a placeholder for the actual implementation
                Log::debug('Would send to GA4', ['data' => $data]);
            } catch (\Exception $e) {
                Log::warning('Failed to send analytics to GA4', ['error' => $e->getMessage()]);
            }
        }

        // Example: Send to custom analytics API
        if (config('cms.analytics.custom_endpoint')) {
            try {
                // Implementation for custom analytics API would go here
                Log::debug('Would send to custom analytics', ['data' => $data]);
            } catch (\Exception $e) {
                Log::warning('Failed to send analytics to custom endpoint', ['error' => $e->getMessage()]);
            }
        }
    }

    /**
     * Format bytes to human readable format
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
