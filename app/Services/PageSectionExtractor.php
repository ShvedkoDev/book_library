<?php

namespace App\Services;

use DOMDocument;
use DOMXPath;
use Illuminate\Support\Str;

class PageSectionExtractor
{
    /**
     * Extract sections (H2 tags) from HTML content.
     *
     * @param string $html
     * @return array Array of ['heading' => string, 'anchor' => string, 'order' => int]
     */
    public function extractSectionsFromHtml(string $html): array
    {
        if (empty($html)) {
            return [];
        }

        $dom = new DOMDocument();
        // Suppress warnings for malformed HTML
        @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $h2Tags = $dom->getElementsByTagName('h2');
        $sections = [];
        $anchorCounts = []; // Track anchor usage for duplicates

        foreach ($h2Tags as $index => $h2) {
            $heading = trim($h2->textContent);

            if (empty($heading)) {
                continue;
            }

            // Generate base anchor from heading
            $baseAnchor = $this->generateAnchor($heading);

            // Handle duplicate anchors by appending -2, -3, etc.
            if (isset($anchorCounts[$baseAnchor])) {
                $anchorCounts[$baseAnchor]++;
                $anchor = $baseAnchor . '-' . $anchorCounts[$baseAnchor];
            } else {
                $anchorCounts[$baseAnchor] = 1;
                $anchor = $baseAnchor;
            }

            $sections[] = [
                'heading' => $heading,
                'anchor' => $anchor,
                'order' => $index,
            ];
        }

        return $sections;
    }

    /**
     * Inject anchor IDs into H2 tags in HTML content.
     *
     * @param string $html
     * @return string Modified HTML with anchor IDs
     */
    public function injectAnchorIds(string $html): string
    {
        if (empty($html)) {
            return $html;
        }

        $dom = new DOMDocument();
        // Suppress warnings for malformed HTML
        @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $h2Tags = $dom->getElementsByTagName('h2');
        $anchorCounts = []; // Track anchor usage for duplicates

        // Need to convert to array to avoid iterator issues when modifying
        $h2Array = [];
        foreach ($h2Tags as $h2) {
            $h2Array[] = $h2;
        }

        foreach ($h2Array as $h2) {
            $heading = trim($h2->textContent);

            if (empty($heading)) {
                continue;
            }

            // Generate base anchor from heading
            $baseAnchor = $this->generateAnchor($heading);

            // Handle duplicate anchors
            if (isset($anchorCounts[$baseAnchor])) {
                $anchorCounts[$baseAnchor]++;
                $anchor = $baseAnchor . '-' . $anchorCounts[$baseAnchor];
            } else {
                $anchorCounts[$baseAnchor] = 1;
                $anchor = $baseAnchor;
            }

            // Set or update the id attribute
            $h2->setAttribute('id', $anchor);
        }

        // Save HTML without doctype and html/body wrappers
        $html = $dom->saveHTML();

        // Clean up unwanted tags added by DOMDocument
        $html = preg_replace('~<(?:!DOCTYPE|/?(?:html|body))[^>]*>\s*~i', '', $html);

        return trim($html);
    }

    /**
     * Generate a URL-friendly anchor from heading text.
     *
     * @param string $heading
     * @return string
     */
    protected function generateAnchor(string $heading): string
    {
        $anchor = Str::slug($heading);

        // If slug is empty (e.g., only special characters), generate random anchor
        if (empty($anchor)) {
            $anchor = 'section-' . Str::random(6);
        }

        return $anchor;
    }

    /**
     * Build a table of contents array with hierarchical structure.
     * This can be expanded later to support H3, H4 nesting.
     *
     * @param array $sections
     * @return array
     */
    public function buildTableOfContents(array $sections): array
    {
        // Currently returns flat structure
        // Can be expanded for nested TOC with H3, H4 support
        return array_map(function ($section) {
            return [
                'heading' => $section['heading'],
                'anchor' => $section['anchor'],
                'url' => '#' . $section['anchor'],
            ];
        }, $sections);
    }
}
