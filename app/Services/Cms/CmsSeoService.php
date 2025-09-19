<?php

namespace App\Services\Cms;

use App\Models\Page;
use App\Models\CmsCategory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;

class CmsSeoService
{
    /**
     * Generate SEO data for individual page
     */
    public function generatePageSeoData(Page $page): array
    {
        $title = $page->seo_title ?: $page->title;
        $description = $page->seo_description ?: $page->excerpt;
        $keywords = $page->seo_keywords;
        $canonicalUrl = route('cms.page.show', $page->slug);
        $ogImage = $page->featured_image ? asset($page->featured_image) : null;
        $robots = $page->status === 'published' ? 'index,follow' : 'noindex,nofollow';

        $metaTags = '<title>' . e($title) . '</title>' . "\n";
        $metaTags .= '<meta name="description" content="' . e($description) . '">' . "\n";
        if ($keywords) {
            $metaTags .= '<meta name="keywords" content="' . e($keywords) . '">' . "\n";
        }
        $metaTags .= '<meta name="robots" content="' . $robots . '">' . "\n";
        $metaTags .= '<link rel="canonical" href="' . $canonicalUrl . '">' . "\n";

        // Open Graph tags
        $metaTags .= '<meta property="og:title" content="' . e($title) . '">' . "\n";
        $metaTags .= '<meta property="og:description" content="' . e($description) . '">' . "\n";
        $metaTags .= '<meta property="og:url" content="' . $canonicalUrl . '">' . "\n";
        $metaTags .= '<meta property="og:type" content="article">' . "\n";
        if ($ogImage) {
            $metaTags .= '<meta property="og:image" content="' . $ogImage . '">' . "\n";
        }
        if ($page->published_at) {
            $metaTags .= '<meta property="article:published_time" content="' . $page->published_at->toISOString() . '">' . "\n";
        }
        $metaTags .= '<meta property="article:modified_time" content="' . $page->updated_at->toISOString() . '">' . "\n";
        if ($page->author?->name) {
            $metaTags .= '<meta property="article:author" content="' . e($page->author->name) . '">' . "\n";
        }

        return [
            'meta_tags' => $metaTags,
            'structured_data' => json_encode($this->generateStructuredData($page)),
            'title' => $title,
            'description' => $description,
            'keywords' => $keywords,
            'canonical_url' => $canonicalUrl,
            'robots' => $robots,
        ];
    }

    /**
     * Generate SEO data for category pages
     */
    public function generateCategorySeoData(CmsCategory $category): array
    {
        $title = $category->seo_title ?: $category->name . ' - ' . config('app.name');
        $description = $category->seo_description ?:
            "Browse content in {$category->name} category. " .
            "Find articles, guides, and resources about {$category->name}.";

        $canonicalUrl = route('cms.category.show', $category->slug);
        $ogImage = $category->featured_image ? asset($category->featured_image) : null;
        $robots = $category->is_active ? 'index,follow' : 'noindex,nofollow';

        $metaTags = '<title>' . e($title) . '</title>' . "\n";
        $metaTags .= '<meta name="description" content="' . e($description) . '">' . "\n";
        if ($category->seo_keywords) {
            $metaTags .= '<meta name="keywords" content="' . e($category->seo_keywords) . '">' . "\n";
        }
        $metaTags .= '<meta name="robots" content="' . $robots . '">' . "\n";
        $metaTags .= '<link rel="canonical" href="' . $canonicalUrl . '">' . "\n";

        // Open Graph tags
        $metaTags .= '<meta property="og:title" content="' . e($title) . '">' . "\n";
        $metaTags .= '<meta property="og:description" content="' . e($description) . '">' . "\n";
        $metaTags .= '<meta property="og:url" content="' . $canonicalUrl . '">' . "\n";
        $metaTags .= '<meta property="og:type" content="website">' . "\n";
        if ($ogImage) {
            $metaTags .= '<meta property="og:image" content="' . $ogImage . '">' . "\n";
        }

        return [
            'meta_tags' => $metaTags,
            'title' => $title,
            'description' => $description,
            'keywords' => $category->seo_keywords,
            'canonical_url' => $canonicalUrl,
            'robots' => $robots,
        ];
    }

    /**
     * Generate SEO data for search results
     */
    public function generateSearchSeoData(string $query, int $totalResults): array
    {
        $title = !empty($query)
            ? "Search results for '{$query}' - " . config('app.name')
            : "Search - " . config('app.name');

        $description = !empty($query)
            ? "Found {$totalResults} results for '{$query}'. " .
              "Search our content library for articles, guides, and resources."
            : "Search our content library. Find articles, guides, and resources.";

        $canonicalUrl = route('cms.search', ['q' => $query]);
        $robots = 'noindex,follow';

        $metaTags = '<title>' . e($title) . '</title>' . "\n";
        $metaTags .= '<meta name="description" content="' . e($description) . '">' . "\n";
        $metaTags .= '<meta name="robots" content="' . $robots . '">' . "\n";
        $metaTags .= '<link rel="canonical" href="' . $canonicalUrl . '">' . "\n";

        // Open Graph tags
        $metaTags .= '<meta property="og:title" content="' . e($title) . '">' . "\n";
        $metaTags .= '<meta property="og:description" content="' . e($description) . '">' . "\n";
        $metaTags .= '<meta property="og:url" content="' . $canonicalUrl . '">' . "\n";
        $metaTags .= '<meta property="og:type" content="website">' . "\n";

        return [
            'meta_tags' => $metaTags,
            'title' => $title,
            'description' => $description,
            'canonical_url' => $canonicalUrl,
            'robots' => $robots,
        ];
    }

    /**
     * Generate XML sitemap
     */
    public function generateSitemap(): string
    {
        $pages = Page::published()
            ->select(['slug', 'updated_at', 'published_at'])
            ->orderBy('updated_at', 'desc')
            ->get();

        $categories = CmsCategory::where('is_active', true)
            ->select(['slug', 'updated_at'])
            ->orderBy('updated_at', 'desc')
            ->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        // Add homepage
        $xml .= $this->addSitemapUrl(url('/'), now(), 'daily', '1.0');

        // Add pages
        foreach ($pages as $page) {
            $url = route('cms.page.show', $page->slug);
            $lastmod = $page->updated_at;
            $changefreq = 'weekly';
            $priority = '0.8';

            $xml .= $this->addSitemapUrl($url, $lastmod, $changefreq, $priority);
        }

        // Add categories
        foreach ($categories as $category) {
            $url = route('cms.category.show', $category->slug);
            $lastmod = $category->updated_at;
            $changefreq = 'weekly';
            $priority = '0.6';

            $xml .= $this->addSitemapUrl($url, $lastmod, $changefreq, $priority);
        }

        $xml .= '</urlset>';

        return $xml;
    }

    /**
     * Generate sitemap index for large sites
     */
    public function generateSitemapIndex(): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        // Add sub-sitemaps
        $sitemaps = [
            ['url' => route('sitemap.pages'), 'lastmod' => now()],
            ['url' => route('sitemap.categories'), 'lastmod' => now()],
        ];

        foreach ($sitemaps as $sitemap) {
            $xml .= "  <sitemap>\n";
            $xml .= "    <loc>" . htmlspecialchars($sitemap['url']) . "</loc>\n";
            $xml .= "    <lastmod>" . $sitemap['lastmod']->toISOString() . "</lastmod>\n";
            $xml .= "  </sitemap>\n";
        }

        $xml .= '</sitemapindex>';

        return $xml;
    }

    /**
     * Generate pages sitemap
     */
    public function generatePagesSitemap(): string
    {
        $pages = Page::published()
            ->select(['slug', 'updated_at', 'published_at'])
            ->orderBy('updated_at', 'desc')
            ->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($pages as $page) {
            $url = route('cms.page.show', $page->slug);
            $lastmod = $page->updated_at;
            $changefreq = 'weekly';
            $priority = '0.8';

            $xml .= $this->addSitemapUrl($url, $lastmod, $changefreq, $priority);
        }

        $xml .= '</urlset>';

        return $xml;
    }

    /**
     * Generate categories sitemap
     */
    public function generateCategoriesSitemap(): string
    {
        $categories = CmsCategory::where('is_active', true)
            ->select(['slug', 'updated_at'])
            ->orderBy('updated_at', 'desc')
            ->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($categories as $category) {
            $url = route('cms.category.show', $category->slug);
            $lastmod = $category->updated_at;
            $changefreq = 'weekly';
            $priority = '0.6';

            $xml .= $this->addSitemapUrl($url, $lastmod, $changefreq, $priority);
        }

        $xml .= '</urlset>';

        return $xml;
    }

    /**
     * Add URL to sitemap
     */
    protected function addSitemapUrl(string $url, $lastmod, string $changefreq, string $priority): string
    {
        $xml = "  <url>\n";
        $xml .= "    <loc>" . htmlspecialchars($url) . "</loc>\n";
        $xml .= "    <lastmod>" . $lastmod->toISOString() . "</lastmod>\n";
        $xml .= "    <changefreq>{$changefreq}</changefreq>\n";
        $xml .= "    <priority>{$priority}</priority>\n";
        $xml .= "  </url>\n";

        return $xml;
    }

    /**
     * Generate structured data for page
     */
    public function generateStructuredData(Page $page): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $page->title,
            'description' => $page->excerpt,
            'datePublished' => $page->published_at?->toISOString(),
            'dateModified' => $page->updated_at->toISOString(),
            'author' => [
                '@type' => 'Person',
                'name' => $page->author?->name ?? config('app.name'),
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => config('app.name'),
                'url' => url('/'),
            ],
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => route('cms.page.show', $page->slug),
            ],
            'url' => route('cms.page.show', $page->slug),
        ];
    }

    /**
     * Clear SEO-related caches
     */
    public function clearSeoCache(): void
    {
        $cacheKeys = [
            'cms.sitemap.xml',
            'cms.sitemap.index',
            'cms.sitemap.pages',
            'cms.sitemap.categories',
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
    }
}