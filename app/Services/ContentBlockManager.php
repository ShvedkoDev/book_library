<?php

namespace App\Services;

use App\Services\ContentBlocks\TextBlock;
use App\Services\ContentBlocks\ImageBlock;
use App\Services\ContentBlocks\GalleryBlock;
use App\Services\ContentBlocks\VideoBlock;
use App\Services\ContentBlocks\QuoteBlock;
use App\Services\ContentBlocks\CodeBlock;
use App\Services\ContentBlocks\CTABlock;
use App\Services\ContentBlocks\DividerBlock;
use App\Services\ContentBlocks\TableBlock;
use App\Services\ContentBlocks\AccordionBlock;
use App\Services\ContentBlocks\Contracts\ContentBlockInterface;
use Illuminate\Support\Collection;

class ContentBlockManager
{
    protected array $blocks = [];
    protected static ?self $instance = null;

    public function __construct()
    {
        $this->registerDefaultBlocks();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    protected function registerDefaultBlocks(): void
    {
        $this->registerBlock('text', TextBlock::class);
        $this->registerBlock('image', ImageBlock::class);
        $this->registerBlock('gallery', GalleryBlock::class);
        $this->registerBlock('video', VideoBlock::class);
        $this->registerBlock('quote', QuoteBlock::class);
        $this->registerBlock('code', CodeBlock::class);
        $this->registerBlock('cta', CTABlock::class);
        $this->registerBlock('divider', DividerBlock::class);
        $this->registerBlock('table', TableBlock::class);
        $this->registerBlock('accordion', AccordionBlock::class);
    }

    public function registerBlock(string $type, string $blockClass): void
    {
        if (!class_exists($blockClass)) {
            throw new \InvalidArgumentException("Block class {$blockClass} does not exist");
        }

        if (!in_array(ContentBlockInterface::class, class_implements($blockClass))) {
            throw new \InvalidArgumentException("Block class {$blockClass} must implement ContentBlockInterface");
        }

        $this->blocks[$type] = $blockClass;
    }

    public function getBlock(string $type): ?ContentBlockInterface
    {
        if (!isset($this->blocks[$type])) {
            return null;
        }

        return new $this->blocks[$type]();
    }

    public function getAvailableBlocks(): array
    {
        $blocks = [];

        foreach ($this->blocks as $type => $class) {
            $instance = new $class();
            $blocks[$type] = [
                'type' => $type,
                'name' => $instance->getName(),
                'description' => $instance->getDescription(),
                'icon' => $instance->getIcon(),
                'category' => $instance->getCategory(),
                'class' => $class,
            ];
        }

        return $blocks;
    }

    public function getBlocksByCategory(): array
    {
        $categorized = [];
        $blocks = $this->getAvailableBlocks();

        foreach ($blocks as $type => $block) {
            $category = $block['category'] ?? 'General';
            $categorized[$category][] = $block;
        }

        return $categorized;
    }

    public function getFormSchema(string $type): array
    {
        $block = $this->getBlock($type);

        if (!$block) {
            return [];
        }

        return $block->getFormSchema();
    }

    public function getValidationRules(string $type): array
    {
        $block = $this->getBlock($type);

        if (!$block) {
            return [];
        }

        return $block->getValidationRules();
    }

    public function getDefaultSettings(string $type): array
    {
        $block = $this->getBlock($type);

        if (!$block) {
            return [];
        }

        return $block->getDefaultSettings();
    }

    public function renderBlock(string $type, array $content, array $settings = []): string
    {
        $block = $this->getBlock($type);

        if (!$block) {
            return '';
        }

        return $block->render($content, $settings);
    }

    public function getPreviewTemplate(string $type): string
    {
        $block = $this->getBlock($type);

        if (!$block) {
            return '';
        }

        return $block->getPreviewTemplate();
    }

    public function validateBlockData(string $type, array $data): array
    {
        $block = $this->getBlock($type);

        if (!$block) {
            return ['error' => 'Invalid block type'];
        }

        return $block->validateData($data);
    }

    public function exportBlock(string $type, array $content, array $settings = []): array
    {
        return [
            'type' => $type,
            'content' => $content,
            'settings' => $settings,
            'version' => '1.0',
            'exported_at' => now()->toISOString(),
        ];
    }

    public function importBlock(array $blockData): array
    {
        if (!isset($blockData['type']) || !isset($blockData['content'])) {
            throw new \InvalidArgumentException('Invalid block data structure');
        }

        $type = $blockData['type'];
        $content = $blockData['content'];
        $settings = $blockData['settings'] ?? [];

        $validation = $this->validateBlockData($type, $content);

        if (isset($validation['error'])) {
            throw new \InvalidArgumentException($validation['error']);
        }

        return [
            'type' => $type,
            'content' => $content,
            'settings' => array_merge($this->getDefaultSettings($type), $settings),
        ];
    }

    public function duplicateBlock(array $blockData): array
    {
        $duplicated = $blockData;

        // Add duplicate suffix to text content if present
        if (isset($duplicated['content']['title'])) {
            $duplicated['content']['title'] .= ' (Copy)';
        }

        if (isset($duplicated['content']['text'])) {
            $duplicated['content']['text'] .= ' (Copy)';
        }

        return $duplicated;
    }

    public function getBlockOptionsForSelect(): array
    {
        $options = [];
        $blocks = $this->getAvailableBlocks();

        foreach ($blocks as $type => $block) {
            $options[$type] = $block['name'];
        }

        return $options;
    }

    public function getBlockCategoriesForSelect(): array
    {
        $categories = [];
        $blocks = $this->getBlocksByCategory();

        foreach ($blocks as $category => $categoryBlocks) {
            $categories[$category] = $category;
        }

        return $categories;
    }
}
