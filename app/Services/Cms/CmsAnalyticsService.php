<?php

namespace App\Services\Cms;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Cms\Page;
use App\Models\Cms\Category;

class CmsAnalyticsService
{
    protected array $config;
    protected string $measurementId;
    protected string $apiSecret;

    public function __construct()
    {
        $this->config = config('cms.analytics', []);
        $this->measurementId = $this->config['google_analytics']['measurement_id'] ?? '';
        $this->apiSecret = $this->config['google_analytics']['api_secret'] ?? '';
    }

    public function trackPageView(Page $page, Request $request): void
    {
        $data = [
            'page_id' => $page->id,
            'page_title' => $page->title,
            'page_slug' => $page->slug,
            'user_agent' => $request->userAgent(),
            'ip_address' => $request->ip(),
            'referrer' => $request->headers->get('referer'),
            'session_id' => $request->session()->getId(),
            'timestamp' => now(),
        ];

        $this->storeAnalyticsEvent('page_view', $data);

        if ($this->isGoogleAnalyticsEnabled()) {
            $this->sendToGoogleAnalytics('page_view', [
                'page_title' => $page->title,
                'page_location' => $request->fullUrl(),
                'page_referrer' => $request->headers->get('referer'),
                'engagement_time_msec' => 1,
            ], $request);
        }

        Cache::increment("page_views:{$page->id}");
        $today = Carbon::today()->format('Y-m-d');
        Cache::increment("daily_page_views:{$today}");
    }

    public function trackSearch(string $query, int $resultsCount, Request $request): void
    {
        $data = [
            'search_query' => $query,
            'results_count' => $resultsCount,
            'user_agent' => $request->userAgent(),
            'ip_address' => $request->ip(),
            'session_id' => $request->session()->getId(),
            'timestamp' => now(),
        ];

        $this->storeAnalyticsEvent('search', $data);

        if ($this->isGoogleAnalyticsEnabled()) {
            $this->sendToGoogleAnalytics('search', [
                'search_term' => $query,
                'search_results' => $resultsCount,
            ], $request);
        }

        Cache::increment("search_queries_total");
        Cache::increment("search_query:{$query}");
    }

    public function trackEvent(string $eventName, array $parameters, Request $request): void
    {
        $data = array_merge([
            'event_name' => $eventName,
            'user_agent' => $request->userAgent(),
            'ip_address' => $request->ip(),
            'session_id' => $request->session()->getId(),
            'timestamp' => now(),
        ], $parameters);

        $this->storeAnalyticsEvent($eventName, $data);

        if ($this->isGoogleAnalyticsEnabled()) {
            $this->sendToGoogleAnalytics($eventName, $parameters, $request);
        }
    }

    public function trackDownload(string $filename, string $fileType, Request $request): void
    {
        $this->trackEvent('file_download', [
            'file_name' => $filename,
            'file_type' => $fileType,
            'file_url' => $request->fullUrl(),
        ], $request);
    }

    public function getPageAnalytics(int $days = 30): array
    {
        $cacheKey = "page_analytics:{$days}_days";

        return Cache::remember($cacheKey, 3600, function () use ($days) {
            $startDate = Carbon::now()->subDays($days);

            return [
                'total_views' => $this->getTotalPageViews($startDate),
                'unique_visitors' => $this->getUniqueVisitors($startDate),
                'popular_pages' => $this->getPopularPages($startDate, 10),
                'daily_views' => $this->getDailyPageViews($startDate),
                'bounce_rate' => $this->getBounceRate($startDate),
                'avg_session_duration' => $this->getAverageSessionDuration($startDate),
            ];
        });
    }

    public function getSearchAnalytics(int $days = 30): array
    {
        $cacheKey = "search_analytics:{$days}_days";

        return Cache::remember($cacheKey, 3600, function () use ($days) {
            $startDate = Carbon::now()->subDays($days);

            return [
                'total_searches' => $this->getTotalSearches($startDate),
                'unique_search_terms' => $this->getUniqueSearchTerms($startDate),
                'popular_searches' => $this->getPopularSearches($startDate, 20),
                'no_results_searches' => $this->getNoResultsSearches($startDate),
                'search_success_rate' => $this->getSearchSuccessRate($startDate),
                'daily_searches' => $this->getDailySearches($startDate),
            ];
        });
    }

    public function getRealTimeAnalytics(): array
    {
        return [
            'active_users' => $this->getActiveUsers(),
            'current_page_views' => $this->getCurrentPageViews(),
            'recent_searches' => $this->getRecentSearches(10),
            'top_content_now' => $this->getTopContentNow(),
            'traffic_sources' => $this->getTrafficSources(),
        ];
    }

    protected function storeAnalyticsEvent(string $eventType, array $data): void
    {
        try {
            DB::table('cms_analytics_events')->insert([
                'event_type' => $eventType,
                'event_data' => json_encode($data),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to store analytics event: ' . $e->getMessage(), [
                'event_type' => $eventType,
                'data' => $data,
            ]);
        }
    }

    protected function sendToGoogleAnalytics(string $eventName, array $parameters, Request $request): void
    {
        if (!$this->measurementId || !$this->apiSecret) {
            return;
        }

        try {
            $clientId = $this->getClientId($request);

            $payload = [
                'client_id' => $clientId,
                'events' => [
                    [
                        'name' => $eventName,
                        'params' => array_merge($parameters, [
                            'session_id' => $request->session()->getId(),
                            'timestamp_micros' => (int) (microtime(true) * 1000000),
                        ]),
                    ],
                ],
            ];

            Http::timeout(5)->post(
                "https://www.google-analytics.com/mp/collect?measurement_id={$this->measurementId}&api_secret={$this->apiSecret}",
                $payload
            );

        } catch (\Exception $e) {
            Log::error('Failed to send event to Google Analytics: ' . $e->getMessage(), [
                'event_name' => $eventName,
                'parameters' => $parameters,
            ]);
        }
    }

    protected function getClientId(Request $request): string
    {
        $clientId = $request->session()->get('ga_client_id');

        if (!$clientId) {
            $clientId = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
            );

            $request->session()->put('ga_client_id', $clientId);
        }

        return $clientId;
    }

    protected function isGoogleAnalyticsEnabled(): bool
    {
        return !empty($this->measurementId) &&
               !empty($this->apiSecret) &&
               ($this->config['google_analytics']['enabled'] ?? false);
    }

    protected function getTotalPageViews(Carbon $startDate): int
    {
        return DB::table('cms_analytics_events')
            ->where('event_type', 'page_view')
            ->where('created_at', '>=', $startDate)
            ->count();
    }

    protected function getUniqueVisitors(Carbon $startDate): int
    {
        return DB::table('cms_analytics_events')
            ->where('event_type', 'page_view')
            ->where('created_at', '>=', $startDate)
            ->distinct()
            ->count('JSON_EXTRACT(event_data, "$.session_id")');
    }

    protected function getPopularPages(Carbon $startDate, int $limit = 10): array
    {
        return DB::table('cms_analytics_events')
            ->select(
                DB::raw('JSON_EXTRACT(event_data, "$.page_title") as page_title'),
                DB::raw('JSON_EXTRACT(event_data, "$.page_slug") as page_slug'),
                DB::raw('COUNT(*) as views')
            )
            ->where('event_type', 'page_view')
            ->where('created_at', '>=', $startDate)
            ->groupBy('page_title', 'page_slug')
            ->orderBy('views', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    protected function getDailyPageViews(Carbon $startDate): array
    {
        return DB::table('cms_analytics_events')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as views')
            )
            ->where('event_type', 'page_view')
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    protected function getTotalSearches(Carbon $startDate): int
    {
        return DB::table('cms_analytics_events')
            ->where('event_type', 'search')
            ->where('created_at', '>=', $startDate)
            ->count();
    }

    protected function getPopularSearches(Carbon $startDate, int $limit = 20): array
    {
        return DB::table('cms_analytics_events')
            ->select(
                DB::raw('JSON_EXTRACT(event_data, "$.search_query") as search_query'),
                DB::raw('COUNT(*) as search_count'),
                DB::raw('AVG(JSON_EXTRACT(event_data, "$.results_count")) as avg_results')
            )
            ->where('event_type', 'search')
            ->where('created_at', '>=', $startDate)
            ->groupBy('search_query')
            ->orderBy('search_count', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    protected function getBounceRate(Carbon $startDate): float
    {
        return 0.0;
    }

    protected function getAverageSessionDuration(Carbon $startDate): float
    {
        return 0.0;
    }

    protected function getUniqueSearchTerms(Carbon $startDate): int
    {
        return DB::table('cms_analytics_events')
            ->where('event_type', 'search')
            ->where('created_at', '>=', $startDate)
            ->distinct()
            ->count('JSON_EXTRACT(event_data, "$.search_query")');
    }

    protected function getNoResultsSearches(Carbon $startDate): int
    {
        return DB::table('cms_analytics_events')
            ->where('event_type', 'search')
            ->where('created_at', '>=', $startDate)
            ->where('JSON_EXTRACT(event_data, "$.results_count")', 0)
            ->count();
    }

    protected function getSearchSuccessRate(Carbon $startDate): float
    {
        $totalSearches = $this->getTotalSearches($startDate);
        $noResultsSearches = $this->getNoResultsSearches($startDate);

        return $totalSearches > 0 ? (($totalSearches - $noResultsSearches) / $totalSearches) * 100 : 0;
    }

    protected function getDailySearches(Carbon $startDate): array
    {
        return DB::table('cms_analytics_events')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as searches')
            )
            ->where('event_type', 'search')
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    protected function getActiveUsers(): int
    {
        return DB::table('cms_analytics_events')
            ->where('created_at', '>=', Carbon::now()->subMinutes(5))
            ->distinct()
            ->count('JSON_EXTRACT(event_data, "$.session_id")');
    }

    protected function getCurrentPageViews(): array
    {
        return DB::table('cms_analytics_events')
            ->select(
                DB::raw('JSON_EXTRACT(event_data, "$.page_title") as page_title'),
                DB::raw('COUNT(*) as current_views')
            )
            ->where('event_type', 'page_view')
            ->where('created_at', '>=', Carbon::now()->subMinutes(30))
            ->groupBy('page_title')
            ->orderBy('current_views', 'desc')
            ->limit(5)
            ->get()
            ->toArray();
    }

    protected function getRecentSearches(int $limit): array
    {
        return DB::table('cms_analytics_events')
            ->select(
                DB::raw('JSON_EXTRACT(event_data, "$.search_query") as search_query'),
                'created_at'
            )
            ->where('event_type', 'search')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    protected function getTopContentNow(): array
    {
        return $this->getCurrentPageViews();
    }

    protected function getTrafficSources(): array
    {
        return DB::table('cms_analytics_events')
            ->select(
                DB::raw('JSON_EXTRACT(event_data, "$.referrer") as referrer'),
                DB::raw('COUNT(*) as visits')
            )
            ->where('event_type', 'page_view')
            ->where('created_at', '>=', Carbon::now()->subHours(24))
            ->groupBy('referrer')
            ->orderBy('visits', 'desc')
            ->limit(10)
            ->get()
            ->toArray();
    }
}
