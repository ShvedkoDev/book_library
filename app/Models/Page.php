<?php

namespace App\Models;

use App\Services\PageSectionExtractor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Page extends Model
{
    use SoftDeletes;

    /**
     * Cache for table of contents to avoid re-parsing.
     *
     * @var array|null
     */
    protected ?array $cachedToc = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'slug',
        'content',
        'custom_html_blocks',
        'meta_description',
        'meta_keywords',
        'is_published',
        'show_in_navigation',
        'is_homepage',
        'published_at',
        'order',
        'parent_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'custom_html_blocks' => 'array',
        'published_at' => 'datetime',
        'is_published' => 'boolean',
        'show_in_navigation' => 'boolean',
        'is_homepage' => 'boolean',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate slug from title when creating
        static::creating(function ($page) {
            if (empty($page->slug) && !empty($page->title)) {
                $page->slug = static::generateUniqueSlug($page->title);
            }
        });

        // Ensure only one page can be homepage at a time
        static::saving(function ($page) {
            if ($page->is_homepage && $page->isDirty('is_homepage')) {
                // Unset all other pages as homepage
                static::where('id', '!=', $page->id)
                    ->where('is_homepage', true)
                    ->update(['is_homepage' => false]);
            }
        });
    }

    /**
     * Generate a unique slug from the given title.
     *
     * @param string $title
     * @param int|null $id
     * @return string
     */
    public static function generateUniqueSlug(string $title, ?int $id = null): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $count = 1;

        while (static::whereSlug($slug)->when($id, function ($query, $id) {
            return $query->where('id', '!=', $id);
        })->exists()) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        return $slug;
    }

    /**
     * Get the parent page.
     *
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'parent_id');
    }

    /**
     * Get the child pages.
     *
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(Page::class, 'parent_id');
    }

    /**
     * Get the resource contributors for this page.
     *
     * @return BelongsToMany
     */
    public function resourceContributors(): BelongsToMany
    {
        return $this->belongsToMany(ResourceContributor::class, 'page_resource_contributor')
            ->withPivot('order')
            ->orderByPivot('order');
    }

    /**
     * Get the sections for this page.
     *
     * @return HasMany
     */
    public function sections(): HasMany
    {
        return $this->hasMany(PageSection::class)->orderBy('order');
    }

    /**
     * Scope a query to only include published pages.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->where(function ($q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    /**
     * Scope a query to order by the order field.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('title');
    }

    /**
     * Scope a query to only include the homepage.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeHomepage($query)
    {
        return $query->where('is_homepage', true);
    }

    /**
     * Get the excerpt for the page.
     * Returns meta_description if set, otherwise truncated content.
     *
     * @return string
     */
    public function getExcerptAttribute(): string
    {
        if (!empty($this->meta_description)) {
            return $this->meta_description;
        }

        if (!empty($this->content)) {
            return Str::limit(strip_tags($this->content), 160);
        }

        return '';
    }

    /**
     * Extract H2 sections from the page content using PageSectionExtractor service.
     * Uses merged content with custom HTML blocks.
     *
     * @return array
     */
    public function extractSections(): array
    {
        if (empty($this->content)) {
            return [];
        }

        // Extract from merged content so custom blocks' H2s are included
        $mergedContent = $this->getMergedContent();

        $extractor = app(PageSectionExtractor::class);
        return $extractor->extractSectionsFromHtml($mergedContent);
    }

    /**
     * Get table of contents from sections with caching.
     *
     * @return array
     */
    public function getTableOfContents(): array
    {
        // Return cached TOC if available
        if ($this->cachedToc !== null) {
            return $this->cachedToc;
        }

        // Try to get from database first
        if ($this->relationLoaded('sections') && $this->sections->isNotEmpty()) {
            $toc = $this->sections->map(function ($section) {
                return [
                    'heading' => $section->heading,
                    'anchor' => $section->anchor,
                    'order' => $section->order,
                    'url' => '#' . $section->anchor,
                ];
            })->toArray();
        } else {
            // Otherwise extract from content
            $sections = $this->extractSections();
            $extractor = app(PageSectionExtractor::class);
            $toc = $extractor->buildTableOfContents($sections);
        }

        // Cache the result
        $this->cachedToc = $toc;

        return $toc;
    }

    /**
     * Get content with anchor IDs injected into H2 tags using PageSectionExtractor service.
     * Also merges custom HTML blocks before injecting anchors.
     *
     * @return string
     */
    public function getContentWithAnchors(): string
    {
        if (empty($this->content)) {
            return '';
        }

        // First, merge custom HTML blocks
        $mergedContent = $this->getMergedContent();

        // Then, inject anchor IDs into H2 tags
        $extractor = app(PageSectionExtractor::class);
        return $extractor->injectAnchorIds($mergedContent);
    }

    /**
     * Accessor for content with anchors.
     *
     * @return string
     */
    public function getContentWithAnchorsAttribute(): string
    {
        return $this->getContentWithAnchors();
    }

    /**
     * Check if the page is currently published.
     *
     * @return bool
     */
    public function isPublished(): bool
    {
        if (!$this->is_published) {
            return false;
        }

        if ($this->published_at && $this->published_at->isFuture()) {
            return false;
        }

        return true;
    }

    /**
     * Get the full URL for this page.
     *
     * @return string|null
     */
    public function getUrl(): ?string
    {
        // Check if the frontend route exists
        if (!\Route::has('pages.show')) {
            return null;
        }

        return route('pages.show', ['slug' => $this->slug]);
    }

    /**
     * Get content with custom HTML blocks merged in.
     * Replaces placeholders like {{block-1}} with actual HTML blocks.
     *
     * @return string
     */
    public function getMergedContent(): string
    {
        $content = $this->content ?? '';

        // If no custom blocks, return content as-is
        if (empty($this->custom_html_blocks)) {
            return $content;
        }

        // Replace each custom HTML block placeholder
        foreach ($this->custom_html_blocks as $block) {
            if (isset($block['id']) && isset($block['html'])) {
                $placeholder = '{{' . $block['id'] . '}}';
                $content = str_replace($placeholder, $block['html'], $content);
            }
        }

        return $content;
    }
}
