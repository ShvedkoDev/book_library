<?php

namespace App\Services\Cms;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\HasMedia;
use Spatie\Image\Image;
use Spatie\Image\Enums\Fit;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

/**
 * Comprehensive Media Management Service for CMS
 *
 * Handles file uploads, image processing, optimization, and media organization
 */
class MediaService
{
    protected array $allowedMimeTypes;
    protected array $imageOptimizationSettings;
    protected int $maxFileSize;
    protected ImageManager $imageManager;

    public function __construct()
    {
        $this->allowedMimeTypes = config('cms.media.allowed_mime_types', [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/svg+xml',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'video/mp4',
            'video/mpeg',
            'video/quicktime',
            'audio/mpeg',
            'audio/wav',
        ]);

        $this->imageOptimizationSettings = config('cms.media.optimization', [
            'jpeg_quality' => 85,
            'png_compression' => 6,
            'webp_quality' => 80,
            'avif_quality' => 75,
        ]);

        $this->maxFileSize = config('cms.media.max_file_size', 10 * 1024 * 1024); // 10MB
        $this->imageManager = new ImageManager(new Driver());
    }

    /**
     * Upload and process media file
     */
    public function uploadMedia(UploadedFile $file, string $collection, HasMedia $model, array $options = []): Media
    {
        // Validate file
        $this->validateFile($file);

        // Sanitize filename
        $sanitizedName = $this->sanitizeFilename($file->getClientOriginalName());

        // Add media to model
        $mediaAdder = $model->addMediaFromRequest('file')
            ->sanitizingFileName(fn($fileName) => $sanitizedName)
            ->toMediaCollection($collection);

        if (isset($options['disk'])) {
            $mediaAdder->toMediaCollection($collection, $options['disk']);
        }

        $media = $mediaAdder;

        // Process image if it's an image file
        if ($this->isImage($media)) {
            $this->processImage($media);
        }

        // Generate alt text for images if AI service is available
        if ($this->isImage($media) && config('cms.media.auto_alt_text', false)) {
            $this->generateAltText($media);
        }

        // Log upload
        Log::info('Media uploaded successfully', [
            'media_id' => $media->id,
            'collection' => $collection,
            'filename' => $media->file_name,
            'model' => get_class($model),
            'model_id' => $model->id
        ]);

        return $media;
    }

    /**
     * Process image conversions and optimizations
     */
    public function processImage(Media $media): void
    {
        if (!$this->isImage($media)) {
            return;
        }

        $conversions = config('cms.media.conversions', []);

        foreach ($conversions as $name => $config) {
            try {
                $this->createImageConversion($media, $name, $config);
            } catch (\Exception $e) {
                Log::error("Failed to create conversion {$name} for media {$media->id}", [
                    'error' => $e->getMessage(),
                    'media_id' => $media->id
                ]);
            }
        }

        // Create WebP versions
        if (config('cms.media.generate_webp', true)) {
            $this->createWebPVersions($media);
        }

        // Create retina versions
        if (config('cms.media.generate_retina', true)) {
            $this->createRetinaVersions($media);
        }
    }

    /**
     * Optimize media file
     */
    public function optimizeMedia(Media $media): void
    {
        if ($this->isImage($media)) {
            $this->optimizeImage($media);
        } elseif ($this->isPdf($media)) {
            $this->optimizePdf($media);
        }

        // Update media record with optimization info
        $media->setCustomProperty('optimized', true);
        $media->setCustomProperty('optimized_at', now());
        $media->save();
    }

    /**
     * Generate AI-powered alt text for images
     */
    public function generateAltText(Media $media): string
    {
        if (!$this->isImage($media)) {
            return '';
        }

        // Check if already has alt text
        if ($media->getCustomProperty('alt')) {
            return $media->getCustomProperty('alt');
        }

        try {
            // This would integrate with an AI service like OpenAI Vision or Google Vision
            // For now, we'll generate descriptive alt text based on filename and metadata
            $altText = $this->generateDescriptiveAltText($media);

            $media->setCustomProperty('alt', $altText);
            $media->save();

            return $altText;
        } catch (\Exception $e) {
            Log::error('Failed to generate alt text', [
                'media_id' => $media->id,
                'error' => $e->getMessage()
            ]);

            return '';
        }
    }

    /**
     * Organize media items into folders
     */
    public function organizeMedia(array $mediaItems, string $folder): array
    {
        $organized = [];

        foreach ($mediaItems as $mediaItem) {
            if ($mediaItem instanceof Media) {
                try {
                    // Update collection path
                    $mediaItem->setCustomProperty('folder', $folder);
                    $mediaItem->save();

                    $organized[] = $mediaItem;
                } catch (\Exception $e) {
                    Log::error('Failed to organize media item', [
                        'media_id' => $mediaItem->id,
                        'folder' => $folder,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }

        return $organized;
    }

    /**
     * Get responsive image sources
     */
    public function getResponsiveImageSources(Media $media): array
    {
        if (!$this->isImage($media)) {
            return [];
        }

        $sources = [];
        $conversions = config('cms.media.conversions', []);

        foreach ($conversions as $name => $config) {
            if ($media->hasGeneratedConversion($name)) {
                $sources[$name] = [
                    'url' => $media->getUrl($name),
                    'width' => $config['width'] ?? null,
                    'height' => $config['height'] ?? null,
                ];

                // Add WebP version if available
                $webpName = $name . '_webp';
                if ($media->hasGeneratedConversion($webpName)) {
                    $sources[$name . '_webp'] = [
                        'url' => $media->getUrl($webpName),
                        'width' => $config['width'] ?? null,
                        'height' => $config['height'] ?? null,
                        'format' => 'webp'
                    ];
                }
            }
        }

        return $sources;
    }

    /**
     * Generate responsive image HTML
     */
    public function generateResponsiveImageHtml(Media $media, array $attributes = []): string
    {
        if (!$this->isImage($media)) {
            return '';
        }

        $sources = $this->getResponsiveImageSources($media);
        $alt = $media->getCustomProperty('alt', '');
        $title = $media->getCustomProperty('title', $media->name);

        $html = '<picture>';

        // Add WebP sources first
        foreach ($sources as $name => $source) {
            if (isset($source['format']) && $source['format'] === 'webp') {
                $media_query = $this->getMediaQueryForConversion($name);
                $html .= "<source srcset=\"{$source['url']}\" type=\"image/webp\"";
                if ($media_query) {
                    $html .= " media=\"{$media_query}\"";
                }
                $html .= ">";
            }
        }

        // Add regular sources
        foreach ($sources as $name => $source) {
            if (!isset($source['format']) || $source['format'] !== 'webp') {
                $media_query = $this->getMediaQueryForConversion($name);
                $html .= "<source srcset=\"{$source['url']}\"";
                if ($media_query) {
                    $html .= " media=\"{$media_query}\"";
                }
                $html .= ">";
            }
        }

        // Fallback img tag
        $img_attributes = array_merge([
            'src' => $media->getUrl(),
            'alt' => $alt,
            'title' => $title,
            'loading' => 'lazy'
        ], $attributes);

        $html .= '<img';
        foreach ($img_attributes as $key => $value) {
            $html .= " {$key}=\"" . htmlspecialchars($value) . "\"";
        }
        $html .= '>';
        $html .= '</picture>';

        return $html;
    }

    /**
     * Bulk delete media with usage check
     */
    public function bulkDeleteMedia(array $mediaIds, bool $force = false): array
    {
        $results = [
            'deleted' => [],
            'skipped' => [],
            'errors' => []
        ];

        foreach ($mediaIds as $mediaId) {
            try {
                $media = Media::find($mediaId);
                if (!$media) {
                    $results['errors'][] = "Media {$mediaId} not found";
                    continue;
                }

                // Check usage unless forced
                if (!$force && $this->isMediaInUse($media)) {
                    $results['skipped'][] = [
                        'id' => $mediaId,
                        'reason' => 'Media is currently in use'
                    ];
                    continue;
                }

                $media->delete();
                $results['deleted'][] = $mediaId;

            } catch (\Exception $e) {
                $results['errors'][] = "Failed to delete media {$mediaId}: " . $e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Find duplicate media files
     */
    public function findDuplicateMedia(): array
    {
        $duplicates = [];

        $media = Media::all()->groupBy(function ($item) {
            return $item->getCustomProperty('checksum', md5_file($item->getPath()));
        });

        foreach ($media as $checksum => $items) {
            if ($items->count() > 1) {
                $duplicates[] = [
                    'checksum' => $checksum,
                    'items' => $items->toArray()
                ];
            }
        }

        return $duplicates;
    }

    /**
     * Clean unused media files
     */
    public function cleanUnusedMedia(): array
    {
        $unusedMedia = Media::whereDoesntHave('model')->get();
        $cleaned = [];

        foreach ($unusedMedia as $media) {
            try {
                $media->delete();
                $cleaned[] = $media->id;
            } catch (\Exception $e) {
                Log::error('Failed to clean unused media', [
                    'media_id' => $media->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $cleaned;
    }

    /**
     * Get media usage statistics
     */
    public function getMediaUsageStats(): array
    {
        return [
            'total_files' => Media::count(),
            'total_size' => Media::sum('size'),
            'images' => Media::where('mime_type', 'like', 'image/%')->count(),
            'documents' => Media::where('mime_type', 'like', 'application/%')->count(),
            'videos' => Media::where('mime_type', 'like', 'video/%')->count(),
            'by_collection' => Media::selectRaw('collection_name, count(*) as count, sum(size) as total_size')
                ->groupBy('collection_name')
                ->get()
                ->toArray(),
            'storage_usage' => $this->getStorageUsage()
        ];
    }

    // Protected helper methods

    protected function validateFile(UploadedFile $file): void
    {
        // Check file size
        if ($file->getSize() > $this->maxFileSize) {
            throw new \InvalidArgumentException('File size exceeds maximum allowed size');
        }

        // Check MIME type
        if (!in_array($file->getMimeType(), $this->allowedMimeTypes)) {
            throw new \InvalidArgumentException('File type not allowed');
        }

        // Additional security checks
        $this->performSecurityChecks($file);
    }

    protected function performSecurityChecks(UploadedFile $file): void
    {
        // Check for executable files
        $dangerousExtensions = ['php', 'exe', 'bat', 'cmd', 'scr', 'pif', 'vbs', 'js'];
        $extension = strtolower($file->getClientOriginalExtension());

        if (in_array($extension, $dangerousExtensions)) {
            throw new \InvalidArgumentException('File type not allowed for security reasons');
        }

        // Scan for malware if scanner is available
        if (config('cms.media.virus_scan', false) && class_exists('\Xenolope\Quahog\Client')) {
            $this->scanForVirus($file);
        }
    }

    protected function sanitizeFilename(string $filename): string
    {
        // Remove special characters and normalize
        $filename = Str::ascii($filename);
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
        $filename = preg_replace('/_{2,}/', '_', $filename);

        return trim($filename, '_');
    }

    protected function isImage(Media $media): bool
    {
        return str_starts_with($media->mime_type, 'image/');
    }

    protected function isPdf(Media $media): bool
    {
        return $media->mime_type === 'application/pdf';
    }

    protected function createImageConversion(Media $media, string $name, array $config): void
    {
        $image = $this->imageManager->read($media->getPath());

        $width = $config['width'] ?? null;
        $height = $config['height'] ?? null;
        $fit = $config['fit'] ?? 'contain';
        $quality = $config['quality'] ?? $this->imageOptimizationSettings['jpeg_quality'];

        if ($width || $height) {
            switch ($fit) {
                case 'crop':
                    $image->cover($width, $height);
                    break;
                case 'contain':
                    $image->scale($width, $height);
                    break;
                case 'fill':
                    $image->resize($width, $height);
                    break;
            }
        }

        // Save conversion
        $conversionPath = $this->getConversionPath($media, $name);
        $image->save($conversionPath, $quality);
    }

    protected function createWebPVersions(Media $media): void
    {
        if (!extension_loaded('gd') || !function_exists('imagewebp')) {
            return;
        }

        $conversions = config('cms.media.conversions', []);

        foreach ($conversions as $name => $config) {
            if ($media->hasGeneratedConversion($name)) {
                $this->createWebPFromConversion($media, $name);
            }
        }
    }

    protected function createWebPFromConversion(Media $media, string $conversionName): void
    {
        try {
            $sourcePath = $media->getPath($conversionName);
            $webpPath = $this->getConversionPath($media, $conversionName . '_webp');

            $image = $this->imageManager->read($sourcePath);
            $image->toWebp($this->imageOptimizationSettings['webp_quality']);
            $image->save($webpPath);

        } catch (\Exception $e) {
            Log::error("Failed to create WebP version for {$conversionName}", [
                'media_id' => $media->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    protected function createRetinaVersions(Media $media): void
    {
        $conversions = config('cms.media.conversions', []);

        foreach ($conversions as $name => $config) {
            if ($media->hasGeneratedConversion($name)) {
                $this->createRetinaVersion($media, $name, $config);
            }
        }
    }

    protected function createRetinaVersion(Media $media, string $conversionName, array $config): void
    {
        try {
            $width = ($config['width'] ?? 0) * 2;
            $height = ($config['height'] ?? 0) * 2;

            if ($width === 0 && $height === 0) {
                return;
            }

            $image = $this->imageManager->read($media->getPath());
            $image->scale($width, $height);

            $retinaPath = $this->getConversionPath($media, $conversionName . '_2x');
            $image->save($retinaPath);

        } catch (\Exception $e) {
            Log::error("Failed to create retina version for {$conversionName}", [
                'media_id' => $media->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    protected function optimizeImage(Media $media): void
    {
        try {
            $image = $this->imageManager->read($media->getPath());

            // Apply optimization based on format
            switch ($media->mime_type) {
                case 'image/jpeg':
                    $image->save($media->getPath(), $this->imageOptimizationSettings['jpeg_quality']);
                    break;
                case 'image/png':
                    // PNG optimization would require additional tools like pngquant
                    break;
                case 'image/webp':
                    $image->toWebp($this->imageOptimizationSettings['webp_quality']);
                    $image->save($media->getPath());
                    break;
            }

        } catch (\Exception $e) {
            Log::error('Failed to optimize image', [
                'media_id' => $media->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    protected function optimizePdf(Media $media): void
    {
        // PDF optimization would require tools like Ghostscript
        // This is a placeholder for PDF optimization logic
        Log::info('PDF optimization requested', ['media_id' => $media->id]);
    }

    protected function generateDescriptiveAltText(Media $media): string
    {
        $filename = pathinfo($media->file_name, PATHINFO_FILENAME);
        $filename = str_replace(['_', '-'], ' ', $filename);
        $filename = ucwords($filename);

        // Add descriptive context based on collection
        $collection = $media->collection_name;
        $context = match($collection) {
            'page_featured' => 'Featured image for',
            'page_gallery' => 'Gallery image showing',
            'content_blocks' => 'Content image depicting',
            default => 'Image of'
        };

        return "{$context} {$filename}";
    }

    protected function getConversionPath(Media $media, string $conversionName): string
    {
        $directory = dirname($media->getPath());
        $extension = pathinfo($media->file_name, PATHINFO_EXTENSION);
        $basename = pathinfo($media->file_name, PATHINFO_FILENAME);

        return "{$directory}/{$basename}_{$conversionName}.{$extension}";
    }

    protected function getMediaQueryForConversion(string $conversionName): ?string
    {
        $mediaQueries = config('cms.media.media_queries', [
            'thumbnail' => null,
            'small' => '(max-width: 480px)',
            'medium' => '(max-width: 768px)',
            'large' => '(max-width: 1200px)',
        ]);

        $baseName = str_replace(['_webp', '_2x'], '', $conversionName);
        return $mediaQueries[$baseName] ?? null;
    }

    protected function isMediaInUse(Media $media): bool
    {
        // Check if media is referenced in content blocks
        $inContentBlocks = \App\Models\ContentBlock::where('data', 'like', '%' . $media->id . '%')->exists();

        // Check if media is featured image for pages
        $inPages = \App\Models\Page::whereHas('media', function($query) use ($media) {
            $query->where('media_id', $media->id);
        })->exists();

        return $inContentBlocks || $inPages;
    }

    protected function scanForVirus(UploadedFile $file): void
    {
        // Implement virus scanning if needed
        // This would integrate with ClamAV or similar
    }

    protected function getStorageUsage(): array
    {
        $totalSize = Media::sum('size');
        $availableSpace = disk_free_space(storage_path('app/public'));

        return [
            'used' => $totalSize,
            'available' => $availableSpace,
            'percentage' => $availableSpace > 0 ? ($totalSize / ($totalSize + $availableSpace)) * 100 : 0
        ];
    }
}