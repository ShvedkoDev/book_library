<?php

namespace App\Services\Cms;

use App\Models\Page;
use App\Models\CmsCategory;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class CmsSeoService
{
    protected array $defaultSettings;

    public function __construct()
    {
        $this->defaultSettings = [
            'site_name' => config('app.name', 'CMS'),
            'site_description' => 'Content Management System',
            'site_url' => config('app.url'),
            'twitter_handle' => '@cms',
            'fb_app_id' => '',
            'default_image' => asset('images/default-og-image.jpg'),
        ];
    }

    /**
     * Generate complete SEO data for a page
     */
    public function generatePageSeoData(Page $page): array
    {
        $title = $page->getSeoTitle();
        $description = $page->getSeoDescription();
        $canonicalUrl = route('cms.page', $page->slug);
        $image = $page->featured_image ?: $this->defaultSettings['default_image'];

        return [
            'title' => $title,
            'description' => $description,
            'canonical' => $canonicalUrl,
            'image' => $image,
            'type' => 'article',
            'meta_tags' => $this->generateMetaTags([
                'title' => $title,
                'description' => $description,
                'canonical' => $canonicalUrl,
                'image' => $image,
                'type' => 'article',
                'article' => [
                    'published_time' => $page->published_at?->toISOString(),
                    'modified_time' => $page->updated_at->toISOString(),
                    'author' => $page->creator?->name,
                    'section' => $page->categories->first()?->name,
                    'tag' => $page->categories->pluck('name')->join(','),
                ],
            ]),
            'structured_data' => $this->generatePageStructuredData($page),
        ];
    }

    /**
     * Generate SEO data for category pages
     */
    public function generateCategorySeoData(CmsCategory $category): array
    {
        $title = $category->seo_title ?: $category->name . ' - ' . $this->defaultSettings['site_name'];
        $description = $category->seo_description ?: 'Browse content in ' . $category->name . ' category';
        $canonicalUrl = route('cms.category', $category->slug);
        $image = $this->defaultSettings['default_image'];

        return [
            'title' => $title,
            'description' => $description,
            'canonical' => $canonicalUrl,
            'image' => $image,
            'type' => 'website',
            'meta_tags' => $this->generateMetaTags([
                'title' => $title,
                'description' => $description,
                'canonical' => $canonicalUrl,
                'image' => $image,
                'type' => 'website',
            ]),
            'structured_data' => $this->generateCategoryStructuredData($category),
        ];
    }

    /**
     * Generate SEO data for search results
     */
    public function generateSearchSeoData(string $query, int $totalResults): array
    {
        $title = !empty($query) 
            ? "Search results for '$query' - " . $this->defaultSettings['site_name']
            : 'Search - ' . $this->defaultSettings['site_name'];
        
        $description = !empty($query)
            ? "Found $totalResults results for '$query'"
            : 'Search our content library';

        $canonicalUrl = route('cms.search') . (!empty($query) ? '?q=' . urlencode($query) : '');

        return [
            'title' => $title,
            'description' => $description,
            'canonical' => $canonicalUrl,
            'image' => $this->defaultSettings['default_image'],
            'type' => 'website',
            'meta_tags' => $this->generateMetaTags([
                'title' => $title,
                'description' => $description,
                'canonical' => $canonicalUrl,
                'image' => $this->defaultSettings['default_image'],
                'type' => 'website',
                'noindex' => true, // Don't index search results
            ]),
            'structured_data' => null,
        ];
    }

    /**
     * Generate HTML meta tags
     */
    protected function generateMetaTags(array $data): string
    {
        $tags = [];

        // Basic meta tags
        $tags[] = '<title>' . e($data['title']) . '</title>';
        $tags[] = '<meta name="description" content="' . e($data['description']) . '">';
        $tags[] = '<link rel="canonical" href="' . e($data['canonical']) . '">';

        // Robots meta
        if (!empty($data['noindex'])) {
            $tags[] = '<meta name="robots" content="noindex, nofollow">';
        } else {
            $tags[] = '<meta name="robots" content="index, follow">';
        }

        // Open Graph tags
        $tags[] = '<meta property="og:title" content="' . e($data['title']) . '">';
        $tags[] = '<meta property="og:description" content="' . e($data['description']) . '">';
        $tags[] = '<meta property="og:type" content="' . e($data['type']) . '">';
        $tags[] = '<meta property="og:url" content="' . e($data['canonical']) . '">';
        $tags[] = '<meta property="og:image" content="' . e($data['image']) . '">';
        $tags[] = '<meta property="og:site_name" content="' . e($this->defaultSettings['site_name']) . '">';

        // Article-specific Open Graph tags
        if (!empty($data['article'])) {
            foreach ($data['article'] as $key => $value) {
                if ($value) {
                    $tags[] = '<meta property="article:' . $key . '" content="' . e($value) . '">';
                }
            }
        }

        // Twitter Card tags
        $tags[] = '<meta name="twitter:card" content="summary_large_image">';
        $tags[] = '<meta name="twitter:title" content="' . e($data['title']) . '">';
        $tags[] = '<meta name="twitter:description" content="' . e($data['description']) . '">';
        $tags[] = '<meta name="twitter:image" content="' . e($data['image']) . '">';
        
        if (!empty($this->defaultSettings['twitter_handle'])) {
            $tags[] = '<meta name="twitter:site" content="' . e($this->defaultSettings['twitter_handle']) . '">';
        }

        // Facebook App ID
        if (!empty($this->defaultSettings['fb_app_id'])) {
            $tags[] = '<meta property="fb:app_id" content="' . e($this->defaultSettings['fb_app_id']) . '">';
        }

        return implode("\n", $tags);
    }

    /**
     * Generate structured data for a page
     */
    protected function generatePageStructuredData(Page $page): string
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $page->title,
            'description' => $page->getSeoDescription(),
            'author' => [
                '@type' => 'Person',
                'name' => $page->creator?->name ?: 'Unknown',
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => $this->defaultSettings['site_name'],
                'url' => $this->defaultSettings['site_url'],
            ],
            'datePublished' => $page->published_at?->toISOString(),
            'dateModified' => $page->updated_at->toISOString(),
            'url' => route('cms.page', $page->slug),
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => route('cms.page', $page->slug),
            ],
        ];

        if ($page->featured_image) {
            $data['image'] = [
                '@type' => 'ImageObject',
                'url' => $page->featured_image,
            ];
        }

        if ($page->categories->isNotEmpty()) {
            $data['articleSection'] = $page->categories->pluck('name')->toArray();
        }

        return '<script type="application/ld+json">' . json_encode($data, JSON_UNESCAPED_SLASHES) . '</script>';
    }

    /**
     * Generate structured data for a category
     */
    protected function generateCategoryStructuredData(CmsCategory $category): string
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => $category->name,
            'description' => $category->description ?: 'Content in ' . $category->name . ' category',
            'url' => route('cms.category', $category->slug),
            'isPartOf' => [
                '@type' => 'WebSite',
                'name' => $this->defaultSettings['site_name'],
                'url' => $this->defaultSettings['site_url'],
            ],
        ];

        // Add breadcrumb list
        $breadcrumbs = [];
        $ancestors = $category->getAncestors();
        $position = 1;

        $breadcrumbs[] = [
            '@type' => 'ListItem',
            'position' => $position++,
            'name' => 'Home',
            'item' => $this->defaultSettings['site_url'],
        ];

        foreach ($ancestors as $ancestor) {
            $breadcrumbs[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => $ancestor->name,
                'item' => route('cms.category', $ancestor->slug),
            ];
        }

        $breadcrumbs[] = [
            '@type' => 'ListItem',
            'position' => $position,
            'name' => $category->name,
            'item' => route('cms.category', $category->slug),
        ];

        $breadcrumbData = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $breadcrumbs,
        ];

        $combinedData = [$data, $breadcrumbData];

        return '<script type="application/ld+json">' . json_encode($combinedData, JSON_UNESCAPED_SLASHES) . '</script>';
    }

    /**
     * Analyze SEO score for a page
     */
    public function analyzeSeoScore(Page $page): array
    {
        $score = 0;
        $issues = [];
        $recommendations = [];

        // Title analysis
        $title = $page->getSeoTitle();
        if (empty($title)) {
            $issues[] = 'Missing SEO title';
        } elseif (strlen($title) < 30) {
            $issues[] = 'SEO title too short (less than 30 characters)';
        } elseif (strlen($title) > 60) {
            $issues[] = 'SEO title too long (more than 60 characters)';
        } else {
            $score += 20;
        }

        // Description analysis
        $description = $page->getSeoDescription();
        if (empty($description)) {
            $issues[] = 'Missing SEO description';
        } elseif (strlen($description) < 120) {
            $issues[] = 'SEO description too short (less than 120 characters)';
        } elseif (strlen($description) > 160) {
            $issues[] = 'SEO description too long (more than 160 characters)';
        } else {
            $score += 20;
        }

        // Content analysis
        $contentLength = strlen(strip_tags($page->content));
        if ($contentLength < 300) {
            $issues[] = 'Content too short (less than 300 characters)';
        } else {
            $score += 15;
        }

        // Featured image
        if ($page->featured_image) {
            $score += 10;
        } else {
            $recommendations[] = 'Add a featured image for better social sharing';
        }

        // Categories
        if ($page->categories->isNotEmpty()) {
            $score += 10;
        } else {
            $recommendations[] = 'Assign the page to relevant categories';
        }

        // URL structure
        if (strlen($page->slug) > 5 && !Str::contains($page->slug, '_')) {
            $score += 10;
        } else {
            $recommendations[] = 'Use descriptive, hyphen-separated URL slug';
        }

        // Internal links (basic check)
        $internalLinkCount = substr_count($page->content, config('app.url'));
        if ($internalLinkCount > 0) {
            $score += 5;
        } else {
            $recommendations[] = 'Add internal links to related content';
        }

        // Headings structure
        $headingCount = substr_count($page->content, '<h') + substr_count($page->content, '<H');
        if ($headingCount > 0) {
            $score += 10;
        } else {
            $recommendations[] = 'Use headings to structure your content';
        }

        return [
            'score' => min(100, $score),
            'grade' => $this->getScoreGrade($score),
            'issues' => $issues,
            'recommendations' => $recommendations,
        ];
    }

    /**
     * Get letter grade for SEO score
     */
    protected function getScoreGrade(int $score): string
    {
        if ($score >= 90) return 'A+';
        if ($score >= 80) return 'A';
        if ($score >= 70) return 'B';
        if ($score >= 60) return 'C';
        if ($score >= 50) return 'D';
        return 'F';
    }
}
