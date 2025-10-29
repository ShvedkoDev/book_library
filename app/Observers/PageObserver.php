<?php

namespace App\Observers;

use App\Models\Page;
use App\Models\PageSection;
use App\Services\PageSectionExtractor;

class PageObserver
{
    /**
     * Handle the Page "saved" event.
     * Extract sections from content and sync to database.
     *
     * @param Page $page
     * @return void
     */
    public function saved(Page $page): void
    {
        // Only process if content has changed
        if (!$page->wasChanged('content') && !$page->wasRecentlyCreated) {
            return;
        }

        // Extract sections from content
        $extractor = app(PageSectionExtractor::class);
        $sections = $extractor->extractSectionsFromHtml($page->content ?? '');

        // Delete old sections
        PageSection::where('page_id', $page->id)->delete();

        // Insert new sections
        if (!empty($sections)) {
            foreach ($sections as $sectionData) {
                PageSection::create([
                    'page_id' => $page->id,
                    'heading' => $sectionData['heading'],
                    'anchor' => $sectionData['anchor'],
                    'order' => $sectionData['order'],
                ]);
            }
        }

        // Clear cached TOC
        if (property_exists($page, 'cachedToc')) {
            $page->cachedToc = null;
        }
    }

    /**
     * Handle the Page "deleting" event.
     * Clean up related sections before page is deleted.
     *
     * @param Page $page
     * @return void
     */
    public function deleting(Page $page): void
    {
        // Sections will be cascade deleted via foreign key constraint,
        // but we can clean them up explicitly if needed
        PageSection::where('page_id', $page->id)->delete();
    }

    /**
     * Handle the Page "restored" event.
     * Re-extract sections when page is restored from soft delete.
     *
     * @param Page $page
     * @return void
     */
    public function restored(Page $page): void
    {
        // Re-extract sections from content
        $extractor = app(PageSectionExtractor::class);
        $sections = $extractor->extractSectionsFromHtml($page->content ?? '');

        if (!empty($sections)) {
            foreach ($sections as $sectionData) {
                PageSection::create([
                    'page_id' => $page->id,
                    'heading' => $sectionData['heading'],
                    'anchor' => $sectionData['anchor'],
                    'order' => $sectionData['order'],
                ]);
            }
        }
    }
}
