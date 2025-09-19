<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\CmsCategory;
use App\Services\ContentBlockRenderer;
use App\Services\Cms\CmsSeoService;
use App\Services\Cms\CmsCacheService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;

class CmsController extends Controller
{
    protected ContentBlockRenderer $blockRenderer;
    protected CmsSeoService $seoService;
    protected CmsCacheService $cacheService;

    public function __construct(
        ContentBlockRenderer $blockRenderer,
        CmsSeoService $seoService,
        CmsCacheService $cacheService
    ) {
        $this->blockRenderer = $blockRenderer;
        $this->seoService = $seoService;
        $this->cacheService = $cacheService;

    }

    /**
     * Display individual page with content blocks
     */
    public function showPage(string $slug)
    {
            try {
                $page = Page::where('slug', $slug)
                    ->published()
                    ->with(['categories', 'contentBlocks' => function ($query) {
                        $query->active()->orderBy('sort_order');
                    }])
                    ->firstOrFail();

                // Increment view count
                $page->incrementViewCount();

                // Prepare view data
                $viewData = $this->preparePageViewData($page);

                return view('cms.pages.show', $viewData);

            } catch (ModelNotFoundException $e) {
                // Check for redirects from old slugs
                $redirect = $this->checkForRedirect($slug);
                if ($redirect) {
                    return redirect()->to($redirect, 301);
                }

                abort(404, 'Page not found');
            }
    }
    /**
     * Display paginated category listings
     */
    public function categoryPages(string $categorySlug, Request $request)
    {
        $page = max(1, (int) $request->get('page', 1));
        $perPage = 12;
        $cacheKey = "cms.category.{$categorySlug}.page.{$page}";

        return Cache::remember($cacheKey, $this->cacheService->getCategoryCacheTtl(), function () use ($categorySlug, $page, $perPage) {
            try {
                $category = CmsCategory::where('slug', $categorySlug)
                    ->where('is_active', true)
                    ->firstOrFail();

                // Get pages in this category and subcategories
                $categoryIds = $this->getCategoryWithDescendants($category);

                $pages = Page::published()
                    ->whereHas('categories', function ($query) use ($categoryIds) {
                        $query->whereIn('cms_categories.id', $categoryIds);
                    })
                    ->with(['categories'])
                    ->orderBy('published_at', 'desc')
                    ->paginate($perPage, ['*'], 'page', $page);

                // Prepare view data
                $viewData = $this->prepareCategoryViewData($category, $pages);

                return view('cms.categories.show', $viewData);

            } catch (ModelNotFoundException $e) {
                abort(404, 'Category not found');
            }
        });
    }

    /**
     * Search functionality with filters
     */
    public function searchPages(Request $request)
    {
        $query = $request->get('q', '');
        $categoryId = $request->get('category');
        $page = max(1, (int) $request->get('page', 1));
        $perPage = 12;

        // Generate cache key based on search parameters
        $cacheKey = 'cms.search.' . md5(serialize([
            'query' => $query,
            'category' => $categoryId,
            'page' => $page
        ]));

        return Cache::remember($cacheKey, $this->cacheService->getSearchCacheTtl(), function () use ($query, $categoryId, $page, $perPage) {
            $pagesQuery = Page::published()->with(['categories']);

            // Apply search query
            if (!empty($query)) {
                $pagesQuery->where(function ($q) use ($query) {
                    $q->where('title', 'LIKE', "%{$query}%")
                        ->orWhere('excerpt', 'LIKE', "%{$query}%")
                        ->orWhere('content', 'LIKE', "%{$query}%")
                        ->orWhere('seo_title', 'LIKE', "%{$query}%")
                        ->orWhere('seo_description', 'LIKE', "%{$query}%");
                });
            }

            // Apply category filter
            if (!empty($categoryId)) {
                $category = CmsCategory::find($categoryId);
                if ($category) {
                    $categoryIds = $this->getCategoryWithDescendants($category);
                    $pagesQuery->whereHas('categories', function ($q) use ($categoryIds) {
                        $q->whereIn('cms_categories.id', $categoryIds);
                    });
                }
            }

            $pages = $pagesQuery->orderBy('published_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            // Prepare view data
            $viewData = $this->prepareSearchViewData($query, $categoryId, $pages);

            return view('cms.search.results', $viewData);
        });
    }

    /**
     * Generate XML sitemap
     */
    public function sitemapXml()
    {
        $cacheKey = 'cms.sitemap.xml';

        $sitemapXml = Cache::remember($cacheKey, $this->cacheService->getSitemapCacheTtl(), function () {
            $pages = Page::published()
                ->select(['slug', 'updated_at', 'published_at'])
                ->orderBy('updated_at', 'desc')
                ->get();

            $categories = CmsCategory::where('is_active', true)
                ->select(['slug', 'updated_at'])
                ->orderBy('updated_at', 'desc')
                ->get();

            return $this->generateSitemapXml($pages, $categories);
        });

        return response($sitemapXml, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Generate RSS feed
     */
    public function feedRss()
    {
        $cacheKey = 'cms.feed.rss';

        $rssXml = Cache::remember($cacheKey, $this->cacheService->getFeedCacheTtl(), function () {
            $pages = Page::published()
                ->with(['categories'])
                ->orderBy('published_at', 'desc')
                ->limit(20)
                ->get();

            return $this->generateRssFeed($pages);
        });

        return response($rssXml, 200, [
            'Content-Type' => 'application/rss+xml; charset=utf-8',
            'Cache-Control' => 'public, max-age=1800',
        ]);
    }

    /**
     * Prepare view data for individual page
     */
    protected function preparePageViewData(Page $page): array
    {
        // Render content blocks
        $renderedContent = '';
        if ($page->contentBlocks->isNotEmpty()) {
            $blocks = $page->contentBlocks->map(function ($block) {
                return [
                    'block_type' => $block->block_type,
                    'content' => $block->content,
                    'settings' => $block->settings,
                ];
            })->toArray();

            $renderedContent = $this->blockRenderer->renderBlocks($blocks);
        }

        // Get related pages
        $relatedPages = $page->getRelatedPages(6);

        // Generate breadcrumbs
        $breadcrumbs = $this->generateBreadcrumbs($page);

        // Prepare SEO data
        $seoData = $this->seoService->generatePageSeoData($page);

        return [
            'page' => $page,
            'renderedContent' => $renderedContent,
            'relatedPages' => $relatedPages,
            'breadcrumbs' => $breadcrumbs,
            'seoData' => $seoData,
            'nextPage' => $page->getNextPage(),
            'previousPage' => $page->getPreviousPage(),
        ];
    }

    /**
     * Prepare view data for category listing
     */
    protected function prepareCategoryViewData(CmsCategory $category, $pages): array
    {
        // Generate breadcrumbs
        $breadcrumbs = $this->generateCategoryBreadcrumbs($category);

        // Get subcategories
        $subcategories = $category->children()->where('is_active', true)->get();

        // Prepare SEO data
        $seoData = $this->seoService->generateCategorySeoData($category);

        return [
            'category' => $category,
            'pages' => $pages,
            'subcategories' => $subcategories,
            'breadcrumbs' => $breadcrumbs,
            'seoData' => $seoData,
        ];
    }

    /**
     * Prepare view data for search results
     */
    protected function prepareSearchViewData(string $query, ?int $categoryId, $pages): array
    {
        // Get all categories for filter dropdown
        $categories = CmsCategory::where('is_active', true)
            ->orderBy('name')
            ->get();

        $selectedCategory = $categoryId ? CmsCategory::find($categoryId) : null;

        // Prepare SEO data
        $seoData = $this->seoService->generateSearchSeoData($query, $pages->total());

        return [
            'query' => $query,
            'pages' => $pages,
            'categories' => $categories,
            'selectedCategory' => $selectedCategory,
            'seoData' => $seoData,
            'totalResults' => $pages->total(),
        ];
    }

    /**
     * Get category with all descendants
     */
    protected function getCategoryWithDescendants(CmsCategory $category): array
    {
        $categoryIds = [$category->id];
        
        // Get all descendant categories
        $descendants = $category->descendants();
        if ($descendants->isNotEmpty()) {
            $categoryIds = array_merge($categoryIds, $descendants->pluck('id')->toArray());
        }

        return $categoryIds;
    }

    /**
     * Generate breadcrumbs for page
     */
    protected function generateBreadcrumbs(Page $page): array
    {
        $breadcrumbs = [
            ['title' => 'Home', 'url' => url('/')],
        ];

        // Add primary category if exists
        $primaryCategory = $page->categories->first();
        if ($primaryCategory) {
            $ancestors = $primaryCategory->getAncestors();
            
            foreach ($ancestors as $ancestor) {
                $breadcrumbs[] = [
                    'title' => $ancestor->name,
                    'url' => route('cms.category', $ancestor->slug),
                ];
            }

            $breadcrumbs[] = [
                'title' => $primaryCategory->name,
                'url' => route('cms.category', $primaryCategory->slug),
            ];
        }

        $breadcrumbs[] = [
            'title' => $page->title,
            'url' => null, // Current page
        ];

        return $breadcrumbs;
    }

    /**
     * Generate breadcrumbs for category
     */
    protected function generateCategoryBreadcrumbs(CmsCategory $category): array
    {
        $breadcrumbs = [
            ['title' => 'Home', 'url' => url('/')],
        ];

        $ancestors = $category->getAncestors();
        foreach ($ancestors as $ancestor) {
            $breadcrumbs[] = [
                'title' => $ancestor->name,
                'url' => route('cms.category', $ancestor->slug),
            ];
        }

        $breadcrumbs[] = [
            'title' => $category->name,
            'url' => null, // Current category
        ];

        return $breadcrumbs;
    }

    /**
     * Check for redirect from old slug
     */
    protected function checkForRedirect(string $slug): ?string
    {
        // This could be implemented with a redirects table
        // For now, just return null
        return null;
    }

    /**
     * Generate sitemap XML
     */
    protected function generateSitemapXml($pages, $categories): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        // Add homepage
        $xml .= $this->addSitemapUrl(url('/'), now(), 'daily', '1.0');

        // Add pages
        foreach ($pages as $page) {
            $url = route('cms.page', $page->slug);
            $lastmod = $page->updated_at;
            $changefreq = 'weekly';
            $priority = '0.8';

            $xml .= $this->addSitemapUrl($url, $lastmod, $changefreq, $priority);
        }

        // Add categories
        foreach ($categories as $category) {
            $url = route('cms.category', $category->slug);
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
     * Generate RSS feed
     */
    protected function generateRssFeed($pages): string
    {
        $siteName = config('app.name', 'CMS');
        $siteUrl = url('/');
        $siteDescription = 'Latest content from ' . $siteName;

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">' . "\n";
        $xml .= "  <channel>\n";
        $xml .= "    <title>" . htmlspecialchars($siteName) . "</title>\n";
        $xml .= "    <link>{$siteUrl}</link>\n";
        $xml .= "    <description>" . htmlspecialchars($siteDescription) . "</description>\n";
        $xml .= "    <language>en-us</language>\n";
        $xml .= "    <lastBuildDate>" . now()->toRSSString() . "</lastBuildDate>\n";
        $xml .= "    <atom:link href=\"" . route('cms.feed') . "\" rel=\"self\" type=\"application/rss+xml\" />\n";

        foreach ($pages as $page) {
            $pageUrl = route('cms.page', $page->slug);
            $categories = $page->categories->pluck('name')->join(', ');

            $xml .= "    <item>\n";
            $xml .= "      <title>" . htmlspecialchars($page->title) . "</title>\n";
            $xml .= "      <link>{$pageUrl}</link>\n";
            $xml .= "      <description>" . htmlspecialchars($page->excerpt ?: Str::limit(strip_tags($page->content), 200)) . "</description>\n";
            $xml .= "      <pubDate>" . $page->published_at->toRSSString() . "</pubDate>\n";
            $xml .= "      <guid isPermaLink=\"true\">{$pageUrl}</guid>\n";
            
            if ($categories) {
                $xml .= "      <category>" . htmlspecialchars($categories) . "</category>\n";
            }
            
            $xml .= "    </item>\n";
        }

        $xml .= "  </channel>\n";
        $xml .= "</rss>\n";

        return $xml;
    }
}
