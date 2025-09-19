<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;

class ContentBlockRenderer
{
    protected ContentBlockManager $blockManager;
    protected int $cacheTimeout;
    protected bool $cacheEnabled;

    public function __construct(ContentBlockManager $blockManager)
    {
        $this->blockManager = $blockManager;
        $this->cacheTimeout = config('cms.cache.content_blocks_ttl', 3600); // 1 hour default
        $this->cacheEnabled = config('cms.cache.enabled', true);
    }

    public function renderBlocks(array $blocks): string
    {
        if (empty($blocks)) {
            return '';
        }

        $html = '';
        
        foreach ($blocks as $index => $blockData) {
            $html .= $this->renderBlock($blockData, $index);
        }

        return $html;
    }

    public function renderBlock(array $blockData, int $index = 0): string
    {
        if (empty($blockData['block_type'])) {
            return '';
        }

        $blockType = $blockData['block_type'];
        $content = $blockData['content'] ?? [];
        $settings = $blockData['settings'] ?? [];

        // Generate cache key
        $cacheKey = $this->generateCacheKey($blockType, $content, $settings, $index);

        if ($this->cacheEnabled) {
            return Cache::remember($cacheKey, $this->cacheTimeout, function () use ($blockType, $content, $settings) {
                return $this->doRenderBlock($blockType, $content, $settings);
            });
        }

        return $this->doRenderBlock($blockType, $content, $settings);
    }

    protected function doRenderBlock(string $blockType, array $content, array $settings): string
    {
        try {
            // Validate block data
            $validation = $this->blockManager->validateBlockData($blockType, $content);
            
            if (isset($validation['error'])) {
                Log::warning('Content block validation failed', [
                    'block_type' => $blockType,
                    'error' => $validation['error'],
                    'content' => $content
                ]);
                
                return $this->renderErrorBlock('Invalid block content: ' . $validation['error']);
            }

            // Merge with default settings
            $defaultSettings = $this->blockManager->getDefaultSettings($blockType);
            $mergedSettings = array_merge($defaultSettings, $settings);

            // Render the block
            $html = $this->blockManager->renderBlock($blockType, $content, $mergedSettings);

            if (empty($html)) {
                return $this->renderErrorBlock('Block rendered empty content');
            }

            return $html;

        } catch (\Exception $e) {
            Log::error('Content block rendering failed', [
                'block_type' => $blockType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->renderErrorBlock('Block rendering error: ' . $e->getMessage());
        }
    }

    protected function renderErrorBlock(string $message): string
    {
        if (app()->environment('production')) {
            return '<!-- Content block error: ' . e($message) . ' -->';
        }

        return '<div class="content-block-error alert alert-danger">'
            . '<strong>Content Block Error:</strong> ' . e($message)
            . '</div>';
    }

    protected function generateCacheKey(string $blockType, array $content, array $settings, int $index): string
    {
        $data = [
            'block_type' => $blockType,
            'content' => $content,
            'settings' => $settings,
            'index' => $index,
            'version' => '1.0', // Increment to invalidate all cached blocks
        ];

        return 'content_block:' . md5(serialize($data));
    }

    public function clearCache(array $blockData = null): void
    {
        if ($blockData) {
            // Clear specific block cache
            $blockType = $blockData['block_type'] ?? '';
            $content = $blockData['content'] ?? [];
            $settings = $blockData['settings'] ?? [];
            $index = $blockData['index'] ?? 0;

            $cacheKey = $this->generateCacheKey($blockType, $content, $settings, $index);
            Cache::forget($cacheKey);
        } else {
            // Clear all content block caches
            Cache::flush(); // Note: This clears ALL cache. In production, use tagged cache
        }
    }

    public function warmCache(array $blocks): void
    {
        foreach ($blocks as $index => $blockData) {
            $this->renderBlock($blockData, $index);
        }
    }

    public function renderBlocksWithWrapper(array $blocks, string $wrapperClass = 'content-blocks'): string
    {
        if (empty($blocks)) {
            return '';
        }

        $html = '<div class="' . e($wrapperClass) . '">';
        $html .= $this->renderBlocks($blocks);
        $html .= '</div>';

        return $html;
    }

    public function getBlockPreview(string $blockType, array $content, array $settings = []): string
    {
        $previewHtml = $this->doRenderBlock($blockType, $content, $settings);
        
        return '<div class="content-block-preview" data-block-type="' . e($blockType) . '">'
            . $previewHtml
            . '</div>';
    }

    public function exportBlocks(array $blocks): array
    {
        $exported = [];
        
        foreach ($blocks as $blockData) {
            if (empty($blockData['block_type'])) {
                continue;
            }

            $exported[] = $this->blockManager->exportBlock(
                $blockData['block_type'],
                $blockData['content'] ?? [],
                $blockData['settings'] ?? []
            );
        }

        return $exported;
    }

    public function importBlocks(array $blocksData): array
    {
        $imported = [];
        
        foreach ($blocksData as $blockData) {
            try {
                $imported[] = $this->blockManager->importBlock($blockData);
            } catch (\Exception $e) {
                Log::warning('Block import failed', [
                    'block_data' => $blockData,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $imported;
    }

    public function getCacheStats(): array
    {
        // This would need implementation specific to cache driver
        return [
            'enabled' => $this->cacheEnabled,
            'timeout' => $this->cacheTimeout,
            'driver' => config('cache.default'),
        ];
    }

    public function validateAllBlocks(array $blocks): array
    {
        $results = [];
        
        foreach ($blocks as $index => $blockData) {
            if (empty($blockData['block_type'])) {
                $results[$index] = ['valid' => false, 'error' => 'Missing block type'];
                continue;
            }

            $blockType = $blockData['block_type'];
            $content = $blockData['content'] ?? [];
            
            $results[$index] = $this->blockManager->validateBlockData($blockType, $content);
        }

        return $results;
    }
}
