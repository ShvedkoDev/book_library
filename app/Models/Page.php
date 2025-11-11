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
        'meta_description',
        'meta_keywords',
        'is_published',
        'show_in_navigation',
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
        'published_at' => 'datetime',
        'is_published' => 'boolean',
        'show_in_navigation' => 'boolean',
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
     *
     * @return array
     */
    public function extractSections(): array
    {
        if (empty($this->content)) {
            return [];
        }

        $extractor = app(PageSectionExtractor::class);
        return $extractor->extractSectionsFromHtml($this->content);
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
     *
     * @return string
     */
    public function getContentWithAnchors(): string
    {
        if (empty($this->content)) {
            return '';
        }

        $extractor = app(PageSectionExtractor::class);
        return $extractor->injectAnchorIds($this->content);
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
}
