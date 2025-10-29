<?php

namespace App\Models;

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
     * Extract H2 sections from the page content.
     *
     * @return array
     */
    public function extractSections(): array
    {
        if (empty($this->content)) {
            return [];
        }

        $sections = [];
        $dom = new \DOMDocument();

        // Suppress errors for malformed HTML
        libxml_use_internal_errors(true);
        $dom->loadHTML(mb_convert_encoding($this->content, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $h2Tags = $dom->getElementsByTagName('h2');

        foreach ($h2Tags as $index => $h2) {
            $heading = trim($h2->textContent);
            if (!empty($heading)) {
                $anchor = $this->generateAnchor($heading, $index);
                $sections[] = [
                    'heading' => $heading,
                    'anchor' => $anchor,
                    'order' => $index,
                ];
            }
        }

        return $sections;
    }

    /**
     * Generate an anchor from a heading.
     *
     * @param string $heading
     * @param int $index
     * @return string
     */
    protected function generateAnchor(string $heading, int $index): string
    {
        $anchor = Str::slug($heading);

        // If anchor is empty or too short, use index
        if (empty($anchor) || strlen($anchor) < 2) {
            $anchor = "section-{$index}";
        }

        return $anchor;
    }

    /**
     * Get table of contents from sections.
     *
     * @return array
     */
    public function getTableOfContents(): array
    {
        // Try to get from database first
        if ($this->relationLoaded('sections') && $this->sections->isNotEmpty()) {
            return $this->sections->map(function ($section) {
                return [
                    'heading' => $section->heading,
                    'anchor' => $section->anchor,
                    'order' => $section->order,
                ];
            })->toArray();
        }

        // Otherwise extract from content
        return $this->extractSections();
    }

    /**
     * Get content with anchor IDs injected into H2 tags.
     *
     * @return string
     */
    public function getContentWithAnchors(): string
    {
        if (empty($this->content)) {
            return '';
        }

        $sections = $this->extractSections();
        if (empty($sections)) {
            return $this->content;
        }

        $content = $this->content;
        $dom = new \DOMDocument();

        libxml_use_internal_errors(true);
        $dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $h2Tags = $dom->getElementsByTagName('h2');

        foreach ($h2Tags as $index => $h2) {
            if (isset($sections[$index])) {
                $h2->setAttribute('id', $sections[$index]['anchor']);
            }
        }

        return $dom->saveHTML();
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
     * @return string
     */
    public function getUrl(): string
    {
        return route('pages.show', ['slug' => $this->slug]);
    }
}
