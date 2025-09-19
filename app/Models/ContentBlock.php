<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

/**
 * Class ContentBlock
 *
 * @property int $id
 * @property int $page_id
 * @property string $block_type
 * @property array|null $content
 * @property array|null $settings
 * @property int $sort_order
 * @property bool $is_active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read Page $page
 *
 * @package App\Models
 */
class ContentBlock extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'content_blocks';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'page_id',
        'block_type',
        'content',
        'settings',
        'sort_order',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'content' => 'array',
        'settings' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'page_id' => 'integer',
    ];

    /**
     * Block type constants
     */
    const TYPE_TEXT = 'text';
    const TYPE_IMAGE = 'image';
    const TYPE_GALLERY = 'gallery';
    const TYPE_VIDEO = 'video';
    const TYPE_QUOTE = 'quote';
    const TYPE_CODE = 'code';
    const TYPE_CTA = 'cta';
    const TYPE_DIVIDER = 'divider';
    const TYPE_TABLE = 'table';
    const TYPE_ACCORDION = 'accordion';
    const TYPE_EMBED = 'embed';

    /**
     * Available block types
     *
     * @return array<string>
     */
    public static function getBlockTypes(): array
    {
        return [
            self::TYPE_TEXT => 'Text Block',
            self::TYPE_IMAGE => 'Image Block',
            self::TYPE_GALLERY => 'Gallery Block',
            self::TYPE_VIDEO => 'Video Block',
            self::TYPE_QUOTE => 'Quote Block',
            self::TYPE_CODE => 'Code Block',
            self::TYPE_CTA => 'Call to Action',
            self::TYPE_DIVIDER => 'Divider',
            self::TYPE_TABLE => 'Table',
            self::TYPE_ACCORDION => 'Accordion',
            self::TYPE_EMBED => 'Embed',
        ];
    }

    /**
     * Get the page that owns the content block.
     *
     * @return BelongsTo
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    /**
     * Scope a query to only include active content blocks.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter content blocks by type.
     *
     * @param Builder $query
     * @param string $type
     * @return Builder
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('block_type', $type);
    }

    /**
     * Render the content block.
     *
     * @return string
     */
    public function render(): string
    {
        if (!$this->is_active) {
            return '';
        }

        $viewName = "cms.blocks.{$this->block_type}";

        if (!view()->exists($viewName)) {
            $viewName = 'cms.blocks.default';
        }

        return view($viewName, [
            'block' => $this,
            'content' => $this->content,
            'settings' => $this->settings,
        ])->render();
    }

    /**
     * Get the block configuration.
     *
     * @return array
     */
    public function getBlockConfig(): array
    {
        $configs = [
            self::TYPE_TEXT => [
                'label' => 'Text Block',
                'icon' => 'heroicon-o-document-text',
                'description' => 'Rich text content with formatting options',
                'fields' => ['content' => 'html'],
                'settings' => ['alignment', 'background'],
            ],
            self::TYPE_IMAGE => [
                'label' => 'Image Block',
                'icon' => 'heroicon-o-photo',
                'description' => 'Single image with caption and alt text',
                'fields' => ['image' => 'image', 'caption' => 'text', 'alt' => 'text'],
                'settings' => ['alignment', 'size', 'link'],
            ],
            self::TYPE_GALLERY => [
                'label' => 'Gallery Block',
                'icon' => 'heroicon-o-rectangle-stack',
                'description' => 'Multiple images in a gallery layout',
                'fields' => ['images' => 'images', 'layout' => 'select'],
                'settings' => ['columns', 'spacing', 'lightbox'],
            ],
            self::TYPE_VIDEO => [
                'label' => 'Video Block',
                'icon' => 'heroicon-o-play',
                'description' => 'Embedded or uploaded video content',
                'fields' => ['source' => 'select', 'url' => 'url', 'file' => 'file'],
                'settings' => ['autoplay', 'controls', 'poster'],
            ],
            self::TYPE_QUOTE => [
                'label' => 'Quote Block',
                'icon' => 'heroicon-o-chat-bubble-left-ellipsis',
                'description' => 'Blockquote with author attribution',
                'fields' => ['quote' => 'textarea', 'author' => 'text', 'title' => 'text'],
                'settings' => ['style', 'alignment'],
            ],
            self::TYPE_CODE => [
                'label' => 'Code Block',
                'icon' => 'heroicon-o-code-bracket',
                'description' => 'Syntax highlighted code snippets',
                'fields' => ['code' => 'textarea', 'language' => 'select'],
                'settings' => ['theme', 'line_numbers', 'copy_button'],
            ],
            self::TYPE_CTA => [
                'label' => 'Call to Action',
                'icon' => 'heroicon-o-megaphone',
                'description' => 'Call-to-action button with customizable styling',
                'fields' => ['title' => 'text', 'description' => 'textarea', 'button_text' => 'text', 'button_url' => 'url'],
                'settings' => ['style', 'alignment', 'background'],
            ],
            self::TYPE_DIVIDER => [
                'label' => 'Divider',
                'icon' => 'heroicon-o-minus',
                'description' => 'Visual separator with customizable styling',
                'fields' => [],
                'settings' => ['style', 'width', 'spacing', 'color'],
            ],
            self::TYPE_TABLE => [
                'label' => 'Table',
                'icon' => 'heroicon-o-table-cells',
                'description' => 'Responsive data table',
                'fields' => ['headers' => 'repeater', 'rows' => 'repeater'],
                'settings' => ['style', 'striped', 'bordered', 'hover'],
            ],
            self::TYPE_ACCORDION => [
                'label' => 'Accordion',
                'icon' => 'heroicon-o-bars-3-bottom-left',
                'description' => 'Collapsible content sections',
                'fields' => ['items' => 'repeater'],
                'settings' => ['multiple_open', 'first_open', 'style'],
            ],
            self::TYPE_EMBED => [
                'label' => 'Embed',
                'icon' => 'heroicon-o-globe-alt',
                'description' => 'Embedded content from external sources',
                'fields' => ['embed_code' => 'textarea', 'source' => 'text'],
                'settings' => ['responsive', 'aspect_ratio'],
            ],
        ];

        return $configs[$this->block_type] ?? [
            'label' => 'Unknown Block',
            'icon' => 'heroicon-o-question-mark-circle',
            'description' => 'Unknown block type',
            'fields' => [],
            'settings' => [],
        ];
    }

    /**
     * Get the content field value.
     *
     * @param string $field
     * @param mixed $default
     * @return mixed
     */
    public function getContentField(string $field, $default = null)
    {
        return $this->content[$field] ?? $default;
    }

    /**
     * Get the settings field value.
     *
     * @param string $field
     * @param mixed $default
     * @return mixed
     */
    public function getSettingsField(string $field, $default = null)
    {
        return $this->settings[$field] ?? $default;
    }

    /**
     * Set a content field value.
     *
     * @param string $field
     * @param mixed $value
     * @return void
     */
    public function setContentField(string $field, $value): void
    {
        $content = $this->content ?? [];
        $content[$field] = $value;
        $this->content = $content;
    }

    /**
     * Set a settings field value.
     *
     * @param string $field
     * @param mixed $value
     * @return void
     */
    public function setSettingsField(string $field, $value): void
    {
        $settings = $this->settings ?? [];
        $settings[$field] = $value;
        $this->settings = $settings;
    }

    /**
     * Check if the block has content.
     *
     * @return bool
     */
    public function hasContent(): bool
    {
        if (empty($this->content)) {
            return false;
        }

        foreach ($this->content as $value) {
            if (!empty($value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the block's CSS classes based on settings.
     *
     * @return string
     */
    public function getCssClasses(): string
    {
        $classes = ['content-block', "content-block--{$this->block_type}"];

        $alignment = $this->getSettingsField('alignment');
        if ($alignment) {
            $classes[] = "content-block--{$alignment}";
        }

        $style = $this->getSettingsField('style');
        if ($style) {
            $classes[] = "content-block--style-{$style}";
        }

        return implode(' ', $classes);
    }

    /**
     * Get the block's inline styles based on settings.
     *
     * @return string
     */
    public function getInlineStyles(): string
    {
        $styles = [];

        $background = $this->getSettingsField('background');
        if ($background) {
            $styles[] = "background-color: {$background}";
        }

        $color = $this->getSettingsField('color');
        if ($color) {
            $styles[] = "color: {$color}";
        }

        $spacing = $this->getSettingsField('spacing');
        if ($spacing) {
            $styles[] = "margin: {$spacing}px 0";
        }

        return implode('; ', $styles);
    }

    /**
     * Duplicate the content block.
     *
     * @return ContentBlock
     */
    public function duplicate(): ContentBlock
    {
        $duplicate = $this->replicate();
        $duplicate->sort_order = $this->sort_order + 1;
        $duplicate->save();

        return $duplicate;
    }

    /**
     * Move the block up in the sort order.
     *
     * @return void
     */
    public function moveUp(): void
    {
        $previousBlock = static::where('page_id', $this->page_id)
            ->where('sort_order', '<', $this->sort_order)
            ->orderBy('sort_order', 'desc')
            ->first();

        if ($previousBlock) {
            $tempOrder = $this->sort_order;
            $this->sort_order = $previousBlock->sort_order;
            $previousBlock->sort_order = $tempOrder;

            $this->save();
            $previousBlock->save();
        }
    }

    /**
     * Move the block down in the sort order.
     *
     * @return void
     */
    public function moveDown(): void
    {
        $nextBlock = static::where('page_id', $this->page_id)
            ->where('sort_order', '>', $this->sort_order)
            ->orderBy('sort_order', 'asc')
            ->first();

        if ($nextBlock) {
            $tempOrder = $this->sort_order;
            $this->sort_order = $nextBlock->sort_order;
            $nextBlock->sort_order = $tempOrder;

            $this->save();
            $nextBlock->save();
        }
    }

    /**
     * Get the next sort order for a page.
     *
     * @param int $pageId
     * @return int
     */
    public static function getNextSortOrder(int $pageId): int
    {
        $maxOrder = static::where('page_id', $pageId)->max('sort_order');
        return ($maxOrder ?? 0) + 1;
    }
}