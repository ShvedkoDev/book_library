<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * Class CmsCategory
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property int|null $parent_id
 * @property int $sort_order
 * @property bool $is_active
 * @property string|null $color
 * @property string|null $seo_title
 * @property string|null $seo_description
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read CmsCategory|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection|CmsCategory[] $children
 * @property-read \Illuminate\Database\Eloquent\Collection|Page[] $pages
 *
 * @package App\Models
 */
class CmsCategory extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cms_categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'sort_order',
        'is_active',
        'color',
        'seo_title',
        'seo_description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'parent_id' => 'integer',
    ];

    /**
     * Boot the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });

        static::updating(function ($category) {
            if ($category->isDirty('name') && empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    /**
     * Get the parent category.
     *
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(CmsCategory::class, 'parent_id');
    }

    /**
     * Get the subcategories (children).
     *
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(CmsCategory::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * Get all subcategories recursively.
     *
     * @return HasMany
     */
    public function subcategories(): HasMany
    {
        return $this->children()->with('subcategories');
    }

    /**
     * Get the pages for the category.
     *
     * @return BelongsToMany
     */
    public function pages(): BelongsToMany
    {
        return $this->belongsToMany(Page::class, 'page_categories', 'cms_category_id', 'page_id')
            ->withTimestamps();
    }

    /**
     * Scope a query to only include active categories.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include root categories.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeRoots(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope a query to include categories with depth calculation.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeWithDepth(Builder $query): Builder
    {
        return $query->selectRaw('cms_categories.*,
            (SELECT COUNT(*) FROM cms_categories AS c2
             WHERE c2.id != cms_categories.id
             AND c2.id IN (
                 SELECT parent_id FROM cms_categories AS c3
                 WHERE c3.parent_id IS NOT NULL
                 AND (c3.id = cms_categories.id
                      OR EXISTS (SELECT 1 FROM cms_categories AS c4
                                 WHERE c4.parent_id = c3.id
                                 AND c4.id = cms_categories.id))
             )) as depth');
    }

    /**
     * Get all children categories.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getChildren()
    {
        return $this->children()->active()->orderBy('sort_order')->get();
    }

    /**
     * Get all ancestors (parents) of the category.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAncestors()
    {
        $ancestors = collect();
        $current = $this->parent;

        while ($current) {
            $ancestors->prepend($current);
            $current = $current->parent;
        }

        return $ancestors;
    }

    /**
     * Check if the category is a root category.
     *
     * @return bool
     */
    public function isRoot(): bool
    {
        return is_null($this->parent_id);
    }

    /**
     * Check if the category has children.
     *
     * @return bool
     */
    public function hasChildren(): bool
    {
        return $this->children()->count() > 0;
    }

    /**
     * Get all descendant categories.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getDescendants()
    {
        $descendants = collect();

        foreach ($this->children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->getDescendants());
        }

        return $descendants;
    }

    /**
     * Get the depth level of the category.
     *
     * @return int
     */
    public function getDepth(): int
    {
        return $this->getAncestors()->count();
    }

    /**
     * Get the category tree path.
     *
     * @param string $separator
     * @return string
     */
    public function getPath(string $separator = ' > '): string
    {
        $ancestors = $this->getAncestors();
        $path = $ancestors->pluck('name')->toArray();
        $path[] = $this->name;

        return implode($separator, $path);
    }

    /**
     * Check if this category is an ancestor of the given category.
     *
     * @param CmsCategory $category
     * @return bool
     */
    public function isAncestorOf(CmsCategory $category): bool
    {
        return $category->getAncestors()->contains('id', $this->id);
    }

    /**
     * Check if this category is a descendant of the given category.
     *
     * @param CmsCategory $category
     * @return bool
     */
    public function isDescendantOf(CmsCategory $category): bool
    {
        return $this->getAncestors()->contains('id', $category->id);
    }

    /**
     * Get the category tree as a nested array.
     *
     * @param int|null $parentId
     * @return array
     */
    public static function getTree(?int $parentId = null): array
    {
        $categories = static::active()
            ->where('parent_id', $parentId)
            ->orderBy('sort_order')
            ->get();

        $tree = [];

        foreach ($categories as $category) {
            $tree[] = [
                'category' => $category,
                'children' => static::getTree($category->id),
            ];
        }

        return $tree;
    }

    /**
     * Get a flat list of categories with indentation.
     *
     * @param int $depth
     * @param string $indent
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getFlatTree(int $depth = 0, string $indent = '—'): \Illuminate\Database\Eloquent\Collection
    {
        $categories = collect();

        $rootCategories = static::active()
            ->roots()
            ->orderBy('sort_order')
            ->get();

        foreach ($rootCategories as $category) {
            $categories->push($category);
            $categories = $categories->merge($category->getFlatChildren($depth + 1, $indent));
        }

        return $categories;
    }

    /**
     * Get flat children for tree display.
     *
     * @param int $depth
     * @param string $indent
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getFlatChildren(int $depth = 1, string $indent = '—'): \Illuminate\Database\Eloquent\Collection
    {
        $categories = collect();

        foreach ($this->getChildren() as $child) {
            $child->setAttribute('depth', $depth);
            $child->setAttribute('indented_name', str_repeat($indent, $depth) . ' ' . $child->name);
            $categories->push($child);
            $categories = $categories->merge($child->getFlatChildren($depth + 1, $indent));
        }

        return $categories;
    }

    /**
     * Get the number of pages in this category and all subcategories.
     *
     * @return int
     */
    public function getTotalPageCount(): int
    {
        $count = $this->pages()->count();

        foreach ($this->children as $child) {
            $count += $child->getTotalPageCount();
        }

        return $count;
    }

    /**
     * Get the URL for the category.
     *
     * @return string
     */
    public function getUrl(): string
    {
        return route('cms.category.show', ['slug' => $this->slug]);
    }

    /**
     * Set the slug attribute.
     *
     * @param string|null $value
     * @return void
     */
    public function setSlugAttribute(?string $value): void
    {
        if (empty($value)) {
            $this->attributes['slug'] = Str::slug($this->attributes['name'] ?? '');
        } else {
            $this->attributes['slug'] = Str::slug($value);
        }
    }

    /**
     * Get the SEO title or fall back to the category name.
     *
     * @return string
     */
    public function getSeoTitle(): string
    {
        return $this->seo_title ?: $this->name;
    }

    /**
     * Get the SEO description or fall back to the description.
     *
     * @return string|null
     */
    public function getSeoDescription(): ?string
    {
        return $this->seo_description ?: $this->description;
    }
}