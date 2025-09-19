<?php

namespace App\Services\Cms;

use App\Models\Page;
use App\Models\CmsCategory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CmsCacheService
{
    protected array $cacheConfig;

    public function __construct()
    {
        $this->cacheConfig = [
            'page_ttl' => config('cms.cache.page_ttl', 3600), // 1 hour
            'category_ttl' => config('cms.cache.category_ttl', 7200), // 2 hours  
            'search_ttl' => config('cms.cache.search_ttl', 1800), // 30 minutes
            'sitemap_ttl' => config('cms.cache.sitemap_ttl', 86400), // 24 hours
            'feed_ttl' => config('cms.cache.feed_ttl', 3600), // 1 hour
            'navigation_ttl' => config('cms.cache.navigation_ttl', 3600), // 1 hour
            'enabled' => config('cms.cache.enabled', true),
        ];
    }

    /**
     * Get page cache TTL
     */
    public function getPageCacheTtl(): int
    {
        return $this->cacheConfig['page_ttl'];
    }

    /**
     * Get category cache TTL
     */
    public function getCategoryCacheTtl(): int
    {
        return $this->cacheConfig['category_ttl'];
    }

    /**
     * Get search cache TTL
     */
    public function getSearchCacheTtl(): int
    {
        return $this->cacheConfig['search_ttl'];
    }

    /**
     * Get sitemap cache TTL
     */
    public function getSitemapCacheTtl(): int
    {
        return $this->cacheConfig['sitemap_ttl'];
    }

    /**
     * Get feed cache TTL
     */
    public function getFeedCacheTtl(): int
    {
        return $this->cacheConfig['feed_ttl'];
    }

    /**
     * Get navigation cache TTL
     */
    public function getNavigationCacheTtl(): int
    {
        return $this->cacheConfig['navigation_ttl'];
    }

    /**
     * Clear page cache
     */
    public function clearPageCache(Page $page): void
    {
        if (!$this->cacheConfig['enabled']) {
            return;
        }

        $cacheKeys = [
            "cms.page.{$page->slug}",
            'cms.sitemap.xml',
            'cms.feed.rss',
            'cms.navigation.main',
        ];

        // Clear category caches for all categories this page belongs to
        foreach ($page->categories as $category) {
            $cacheKeys[] = "cms.category.{$category->slug}.page.1";
            $cacheKeys[] = "cms.category.{$category->slug}.page.2";
            $cacheKeys[] = "cms.category.{$category->slug}.page.3";
            $cacheKeys[] = "cms.category.{$category->slug}.page.4";
            $cacheKeys[] = "cms.category.{$category->slug}.page.5";
        }

        // Clear search cache (simplified - in production use cache tags)
        $this->clearSearchCache();

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }

        Log::info('Cleared cache for page', ['page_id' => $page->id, 'slug' => $page->slug]);
    }

    /**
     * Clear category cache
     */
    public function clearCategoryCache(CmsCategory $category): void
    {
        if (!$this->cacheConfig['enabled']) {
            return;
        }

        $cacheKeys = [
            'cms.sitemap.xml',
            'cms.navigation.main',
        ];

        // Clear all pages for this category
        for ($page = 1; $page <= 10; $page++) {
            $cacheKeys[] = "cms.category.{$category->slug}.page.{$page}";
        }

        // Clear parent and children category caches
        if ($category->parent) {
            for ($page = 1; $page <= 10; $page++) {
                $cacheKeys[] = "cms.category.{$category->parent->slug}.page.{$page}";
            }
        }

        foreach ($category->children as $child) {
            for ($page = 1; $page <= 10; $page++) {
                $cacheKeys[] = "cms.category.{$child->slug}.page.{$page}";
            }
        }

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }

        Log::info('Cleared cache for category', ['category_id' => $category->id, 'slug' => $category->slug]);
    }

    /**
     * Clear search cache
     */
    public function clearSearchCache(): void
    {
        if (!$this->cacheConfig['enabled']) {
            return;
        }

        // In production, use cache tags for better granular control
        // For now, we'll use a pattern-based approach
        $cacheKeys = Cache::getRedis()->keys('cms.search.*');
        
        if (!empty($cacheKeys)) {
            foreach ($cacheKeys as $key) {
                Cache::forget($key);
            }
        }

        Log::info('Cleared search cache');
    }

    /**
     * Clear all CMS caches
     */
    public function clearAllCache(): void
    {
        if (!$this->cacheConfig['enabled']) {
            return;
        }

        $patterns = [
            'cms.page.*',
            'cms.category.*',
            'cms.search.*',
            'cms.sitemap.*',
            'cms.feed.*',
            'cms.navigation.*',
        ];

        foreach ($patterns as $pattern) {
            $keys = Cache::getRedis()->keys($pattern);
            if (!empty($keys)) {
                foreach ($keys as $key) {
                    Cache::forget($key);
                }
            }
        }

        Log::info('Cleared all CMS cache');
    }

    /**
     * Warm page cache
     */
    public function warmPageCache(Page $page): void
    {
        if (!$this->cacheConfig['enabled']) {
            return;
        }

        try {
            // This would trigger the cache warming by making a request
            // In practice, you might want to use a more sophisticated approach
            $cacheKey = "cms.page.{$page->slug}";
            
            if (!Cache::has($cacheKey)) {
                // Trigger cache warming via HTTP request or direct method call
                Log::info('Warming cache for page', ['page_id' => $page->id, 'slug' => $page->slug]);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to warm page cache', [
                'page_id' => $page->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get cache statistics
     */
    public function getCacheStats(): array
    {
        if (!$this->cacheConfig['enabled']) {
            return ['enabled' => false];
        }

        try {
            $redis = Cache::getRedis();
            $cmsKeys = $redis->keys('cms.*');
            
            $stats = [
                'enabled' => true,
                'driver' => config('cache.default'),
                'total_cms_keys' => count($cmsKeys),
                'page_keys' => count($redis->keys('cms.page.*')),
                'category_keys' => count($redis->keys('cms.category.*')),
                'search_keys' => count($redis->keys('cms.search.*')),
                'sitemap_keys' => count($redis->keys('cms.sitemap.*')),
                'feed_keys' => count($redis->keys('cms.feed.*')),
                'navigation_keys' => count($redis->keys('cms.navigation.*')),
                'config' => $this->cacheConfig,
            ];

            return $stats;
        } catch (\Exception $e) {
            Log::warning('Failed to get cache stats', ['error' => $e->getMessage()]);
            
            return [
                'enabled' => true,
                'driver' => config('cache.default'),
                'error' => 'Unable to retrieve cache statistics',
                'config' => $this->cacheConfig,
            ];
        }
    }

    /**
     * Schedule cache warming for popular content
     */
    public function scheduleWarmCache(): void
    {
        if (!$this->cacheConfig['enabled']) {
            return;
        }

        try {
            // Warm cache for most viewed pages
            $popularPages = Page::published()
                ->orderBy('view_count', 'desc')
                ->limit(20)
                ->get();

            foreach ($popularPages as $page) {
                $this->warmPageCache($page);
            }

            // Warm cache for active categories
            $activeCategories = CmsCategory::where('is_active', true)
                ->orderBy('sort_order')
                ->limit(10)
                ->get();

            foreach ($activeCategories as $category) {
                // You could implement category cache warming here
                Log::debug('Would warm cache for category', ['category' => $category->slug]);
            }

            Log::info('Completed cache warming schedule');
        } catch (\Exception $e) {
            Log::error('Failed to schedule cache warming', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Check if caching is enabled
     */
    public function isEnabled(): bool
    {
        return $this->cacheConfig['enabled'];
    }

    /**
     * Generate cache key with prefix
     */
    public function generateCacheKey(string $type, array $params = []): string
    {
        $key = 'cms.' . $type;
        
        if (!empty($params)) {
            $key .= '.' . implode('.', array_map(function ($value) {
                return is_array($value) ? md5(serialize($value)) : $value;
            }, $params));
        }

        return $key;
    }

    /**
     * Cache with automatic invalidation tags
     */
    public function remember(string $key, int $ttl, \Closure $callback, array $tags = []): mixed
    {
        if (!$this->cacheConfig['enabled']) {
            return $callback();
        }

        // In Laravel with Redis, you could implement cache tagging here
        // For now, we'll use simple caching
        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Invalidate cache by tags
     */
    public function invalidateByTags(array $tags): void
    {
        if (!$this->cacheConfig['enabled']) {
            return;
        }

        // Implementation would depend on cache tagging support
        // For now, we'll clear related caches manually
        foreach ($tags as $tag) {
            switch ($tag) {
                case 'pages':
                    $this->clearAllCache();
                    break;
                case 'categories':
                    Cache::forget('cms.navigation.main');
                    Cache::forget('cms.sitemap.xml');
                    break;
                case 'navigation':
                    Cache::forget('cms.navigation.main');
                    break;
            }
        }

        Log::info('Invalidated cache by tags', ['tags' => $tags]);
    }
}
