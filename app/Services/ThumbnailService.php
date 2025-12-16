<?php

namespace App\Services;

use App\Models\Book;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ThumbnailService
{
    /**
     * Get thumbnail URL for a book
     * Priority: 1) Existing thumbnail, 2) PDF first page, 3) Colored placeholder, 4) Default icon
     */
    public function getThumbnailUrl(Book $book): string
    {
        // Check if book has an existing thumbnail file
        $thumbnailFile = $book->files()
            ->where('file_type', 'thumbnail')
            ->where('is_primary', true)
            ->first();

        if ($thumbnailFile) {
            $filePath = $thumbnailFile->getFilePath();
            if ($filePath) {
                // Normalize Unicode path (NFD to NFC) for filesystem compatibility
                $normalizedPath = \Normalizer::normalize($filePath, \Normalizer::NFC);
                if (Storage::disk('public')->exists($normalizedPath)) {
                    return Storage::disk('public')->url($normalizedPath);
                }
            }
        }

        // Try to generate from PDF (Option 1)
        if (config('thumbnails.enable_pdf_extraction', false)) {
            $pdfThumbnail = $this->generateFromPdf($book);
            if ($pdfThumbnail) {
                return $pdfThumbnail;
            }
        }

        // Generate colored placeholder with first letter (Option 2)
        return $this->generateColoredPlaceholder($book);
    }

    /**
     * Option 1: Generate thumbnail from first page of PDF
     */
    public function generateFromPdf(Book $book): ?string
    {
        // Check if Imagick is available
        if (!extension_loaded('imagick')) {
            return null;
        }

        // Find PDF file
        $pdfFile = $book->files()
            ->where('file_type', 'pdf')
            ->where('is_primary', true)
            ->first();

        if (!$pdfFile) {
            return null;
        }

        $pdfFilePath = $pdfFile->getFilePath();
        if (!$pdfFilePath) {
            return null;
        }

        // Normalize Unicode path (NFD to NFC) for filesystem compatibility
        $pdfFilePath = \Normalizer::normalize($pdfFilePath, \Normalizer::NFC);
        if (!Storage::disk('public')->exists($pdfFilePath)) {
            return null;
        }

        try {
            $pdfPath = Storage::disk('public')->path($pdfFilePath);

            // Generate thumbnail filename
            $thumbnailFilename = 'thumbnails/generated/' . $book->id . '_' . time() . '.jpg';
            $thumbnailPath = Storage::disk('public')->path($thumbnailFilename);

            // Ensure directory exists
            $thumbnailDir = dirname($thumbnailPath);
            if (!file_exists($thumbnailDir)) {
                mkdir($thumbnailDir, 0755, true);
            }

            // Use Imagick to extract first page
            $imagick = new \Imagick();
            $imagick->setResolution(300, 300); // High resolution for better quality
            $imagick->readImage($pdfPath . '[0]'); // Read only first page
            $imagick->setImageFormat('jpg');
            $imagick->setImageCompressionQuality(85);

            // Resize to thumbnail size (maintain aspect ratio)
            $imagick->thumbnailImage(400, 600, true);

            // Write to storage
            $imagick->writeImage($thumbnailPath);
            $imagick->clear();
            $imagick->destroy();

            return Storage::disk('public')->url($thumbnailFilename);
        } catch (\Exception $e) {
            // Log error but don't fail
            logger()->error('Failed to generate PDF thumbnail: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Option 2: Generate colored placeholder with first letter of title
     */
    public function generateColoredPlaceholder(Book $book): string
    {
        $firstLetter = strtoupper(mb_substr($book->title, 0, 1));
        $color = $this->getColorForLetter($firstLetter);

        // Return SVG data URL
        return $this->generateSvgPlaceholder($firstLetter, $color);
    }

    /**
     * Option 3: Get default book icon placeholder
     */
    public function getDefaultPlaceholder(): string
    {
        // Check if default placeholder image exists
        if (file_exists(public_path('library-assets/images/book-placeholder.svg'))) {
            return asset('library-assets/images/book-placeholder.svg');
        }

        // Return a simple SVG placeholder
        return $this->generateSvgPlaceholder('?', '#6B7280');
    }

    /**
     * Generate SVG placeholder image
     */
    private function generateSvgPlaceholder(string $letter, string $color): string
    {
        $svg = '<svg width="400" height="600" xmlns="http://www.w3.org/2000/svg">';
        $svg .= '<rect width="400" height="600" fill="' . $color . '"/>';
        $svg .= '<text x="50%" y="50%" font-size="200" font-family="Arial, sans-serif" fill="white" text-anchor="middle" dominant-baseline="middle">';
        $svg .= htmlspecialchars($letter);
        $svg .= '</text>';
        $svg .= '</svg>';

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    /**
     * Get consistent color for a letter
     */
    private function getColorForLetter(string $letter): string
    {
        // Pastel Color Palette - Soft, professional colors for book thumbnails
        $colors = [
            // Row 1: Pink/Coral tones
            '#f5e5e8', // Very Light Pink/Blush
            '#f0b8c8', // Rose Pink
            '#e89a9a', // Coral/Salmon
            '#f5c3a0', // Light Peach
            '#f0aa8f', // Peachy Coral

            // Row 2: Blue/Purple tones
            '#a0bdd9', // Powder Blue
            '#7fa9d8', // Cornflower Blue
            '#9b95c9', // Lavender Purple
            '#b9a5c5', // Mauve/Light Purple
            '#c9b5d4', // Pale Lilac

            // Row 3: Green tones
            '#8fb5a8', // Sage Green
            '#7db5a0', // Medium Sage/Sea Green
            '#92cdc9', // Mint/Aqua
            '#b5d499', // Light Yellow-Green
            '#d5e599', // Pale Chartreuse/Lime

            // Row 4: Neutral/Yellow tones
            '#9ca899', // Olive Gray/Sage Gray
            '#c5c9bd', // Light Gray/Greige
            '#e8ec99', // Pale Yellow
            '#f0ec99', // Light Butter Yellow
            '#f5f0a8', // Cream Yellow
        ];

        // Use ASCII value to pick consistent color
        $index = ord($letter) % count($colors);
        return $colors[$index];
    }

    /**
     * Generate and save physical thumbnail file from placeholder
     * Useful for caching generated thumbnails
     */
    public function savePlaceholderToFile(Book $book): ?string
    {
        try {
            $firstLetter = strtoupper(mb_substr($book->title, 0, 1));
            $color = $this->getColorForLetter($firstLetter);

            // Generate filename
            $filename = 'thumbnails/placeholders/' . Str::slug($book->title) . '_' . $book->id . '.svg';
            $filePath = Storage::disk('public')->path($filename);

            // Ensure directory exists
            $dir = dirname($filePath);
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
            }

            // Generate SVG content
            $svg = '<?xml version="1.0" encoding="UTF-8"?>';
            $svg .= '<svg width="400" height="600" xmlns="http://www.w3.org/2000/svg">';
            $svg .= '<rect width="400" height="600" fill="' . $color . '"/>';
            $svg .= '<text x="50%" y="50%" font-size="200" font-family="Arial, sans-serif" fill="white" text-anchor="middle" dominant-baseline="middle">';
            $svg .= htmlspecialchars($firstLetter);
            $svg .= '</text>';
            $svg .= '</svg>';

            // Save to file
            file_put_contents($filePath, $svg);

            return Storage::disk('public')->url($filename);
        } catch (\Exception $e) {
            logger()->error('Failed to save placeholder thumbnail: ' . $e->getMessage());
            return null;
        }
    }
}
