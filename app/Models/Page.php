<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Class Page
 *
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string|null $excerpt
 * @property string|null $content
 * @property string|null $featured_image
 * @property string|null $seo_title
 * @property string|null $seo_description
 * @property string|null $seo_keywords
 * @property string $status
 * @property Carbon|null $published_at
 * @property Carbon|null $scheduled_at
 * @property string $template
 * @property int $sort_order
 * @property bool $is_featured
 * @property int $view_count
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property-read User|null $creator
 * @property-read User|null $updater
 * @property-read \Illuminate\Database\Eloquent\Collection|CmsCategory[] $categories
 * @property-read \Illuminate\Database\Eloquent\Collection|ContentBlock[] $contentBlocks
 *
 * @package App\Models
 */
class Page extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pages';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'status',
        'published_at',
        'scheduled_at',
        'template',
        'sort_order',
        'is_featured',
        'view_count',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'published_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'is_featured' => 'boolean',
        'view_count' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Page status constants
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_ARCHIVED = 'archived';

    /**
     * Available page statuses
     *
     * @return array<string>
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_PUBLISHED,
            self::STATUS_SCHEDULED,
            self::STATUS_ARCHIVED,
        ];
    }

    /**
     * Register media collections
     *
     * @return void
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured_image')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp']);

        $this->addMediaCollection('gallery')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp']);

        $this->addMediaCollection('documents')
            ->acceptsMimeTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);
    }

    /**
     * Get the user who created the page.
     *
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the page.
     *
     * @return BelongsTo
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the categories for the page.
     *
     * @return BelongsToMany
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(CmsCategory::class, 'page_categories', 'page_id', 'cms_category_id')
            ->withTimestamps();
    }

    /**
     * Get the content blocks for the page.
     *
     * @return HasMany
     */
    public function contentBlocks(): HasMany
    {
        return $this->hasMany(ContentBlock::class)->orderBy('sort_order');
    }

    /**
     * Scope a query to only include published pages.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED)
            ->where(function ($q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    /**
     * Scope a query to only include featured pages.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope a query to filter pages by status.
     *
     * @param Builder $query
     * @param string $status
     * @return Builder
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Get the excerpt attribute.
     * Auto-generate from content if empty.
     *
     * @return string|null
     */
    public function getExcerptAttribute(): ?string
    {
        if (!empty($this->attributes['excerpt'])) {
            return $this->attributes['excerpt'];
        }

        if (!empty($this->attributes['content'])) {
            $content = strip_tags($this->attributes['content']);
            return Str::limit($content, 160, '...');
        }

        return null;
    }

    /**
     * Set the slug attribute.
     * Auto-generate from title if not provided.
     *
     * @param string|null $value
     * @return void
     */
    public function setSlugAttribute(?string $value): void
    {
        if (empty($value)) {
            $this->attributes['slug'] = Str::slug($this->attributes['title'] ?? '');
        } else {
            $this->attributes['slug'] = Str::slug($value);
        }
    }

    /**
     * Check if the page is published.
     *
     * @return bool
     */
    public function isPublished(): bool
    {
        if ($this->status !== self::STATUS_PUBLISHED) {
            return false;
        }

        if ($this->published_at && $this->published_at->isFuture()) {
            return false;
        }

        return true;
    }

    /**
     * Check if the page can be viewed.
     *
     * @return bool
     */
    public function canBeViewed(): bool
    {
        return $this->isPublished() && !$this->trashed();
    }

    /**
     * Get the URL for the page.
     *
     * @return string
     */
    public function getUrl(): string
    {
        return route('cms.page.show', ['slug' => $this->slug]);
    }

    /**
     * Increment the view count.
     *
     * @return void
     */
    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }

    /**
     * Get the SEO title or fall back to the page title.
     *
     * @return string
     */
    public function getSeoTitle(): string
    {
        return $this->seo_title ?: $this->title;
    }

    /**
     * Get the SEO description or fall back to the excerpt.
     *
     * @return string|null
     */
    public function getSeoDescription(): ?string
    {
        return $this->seo_description ?: $this->excerpt;
    }

    /**
     * Check if the page should be automatically published.
     *
     * @return void
     */
    public function checkScheduledPublishing(): void
    {
        if ($this->status === self::STATUS_SCHEDULED &&
            $this->scheduled_at &&
            $this->scheduled_at->isPast()) {
            $this->update([
                'status' => self::STATUS_PUBLISHED,
                'published_at' => $this->scheduled_at,
            ]);
        }
    }

    /**
     * Get the next page in the same category.
     *
     * @return Page|null
     */
    public function getNextPage(): ?Page
    {
        return static::published()
            ->where('id', '!=', $this->id)
            ->where('published_at', '>', $this->published_at)
            ->orderBy('published_at', 'asc')
            ->first();
    }

    /**
     * Get the previous page in the same category.
     *
     * @return Page|null
     */
    public function getPreviousPage(): ?Page
    {
        return static::published()
            ->where('id', '!=', $this->id)
            ->where('published_at', '<', $this->published_at)
            ->orderBy('published_at', 'desc')
            ->first();
    }

    /**
     * Get related pages based on categories.
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRelatedPages(int $limit = 5)
    {
        $categoryIds = $this->categories->pluck('id');

        if ($categoryIds->isEmpty()) {
            return collect();
        }

        return static::published()
            ->where('id', '!=', $this->id)
            ->whereHas('categories', function ($query) use ($categoryIds) {
                $query->whereIn('cms_categories.id', $categoryIds);
            })
            ->orderBy('published_at', 'desc')
            ->limit($limit)
            ->get();
    }
}