<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PageSection extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'page_id',
        'heading',
        'anchor',
        'order',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate anchor from heading when creating
        static::creating(function ($section) {
            if (empty($section->anchor) && !empty($section->heading)) {
                $section->anchor = static::generateAnchorFromHeading($section->heading);
            }
        });
    }

    /**
     * Get the page that owns this section.
     *
     * @return BelongsTo
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    /**
     * Generate a URL-friendly anchor from a heading.
     *
     * @param string $heading
     * @return string
     */
    public static function generateAnchorFromHeading(string $heading): string
    {
        $anchor = Str::slug($heading);

        // If anchor is empty, use a random string
        if (empty($anchor)) {
            $anchor = 'section-' . Str::random(6);
        }

        return $anchor;
    }

    /**
     * Ensure the anchor is unique within the page.
     *
     * @param string $anchor
     * @param int $pageId
     * @param int|null $excludeId
     * @return string
     */
    public static function ensureUniqueAnchor(string $anchor, int $pageId, ?int $excludeId = null): string
    {
        $originalAnchor = $anchor;
        $count = 1;

        while (static::where('page_id', $pageId)
            ->where('anchor', $anchor)
            ->when($excludeId, function ($query, $excludeId) {
                return $query->where('id', '!=', $excludeId);
            })
            ->exists()) {
            $anchor = "{$originalAnchor}-{$count}";
            $count++;
        }

        return $anchor;
    }

    /**
     * Get the full anchor link for this section.
     *
     * @return string
     */
    public function getAnchorLinkAttribute(): string
    {
        return "#{$this->anchor}";
    }
}
