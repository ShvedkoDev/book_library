<?php

namespace App\Http\ViewComposers;

use App\Models\Page;
use Illuminate\View\View;

class NavigationComposer
{
    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view): void
    {
        $cmsPages = $this->getCmsPages();
        $view->with('cmsPages', $cmsPages);
    }

    /**
     * Get published CMS pages organized by parent-child hierarchy.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getCmsPages()
    {
        // Get all published pages with no parent (top-level pages)
        $topLevelPages = Page::published()
            ->whereNull('parent_id')
            ->orderBy('order')
            ->orderBy('title')
            ->get();

        // For each top-level page, load its children
        $topLevelPages->each(function ($page) {
            $page->children = Page::published()
                ->where('parent_id', $page->id)
                ->orderBy('order')
                ->orderBy('title')
                ->get();
        });

        return $topLevelPages;
    }
}
