<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PageController extends Controller
{
    /**
     * Display the specified page by slug.
     *
     * @param string $slug
     * @return \Illuminate\View\View
     */
    public function show(string $slug)
    {
        // Find page by slug with eager loading
        $page = Page::where('slug', $slug)
            ->with(['resourceContributors' => function ($query) {
                $query->where('is_active', true)
                    ->orderBy('page_resource_contributor.order');
            }])
            ->first();

        // Handle 404 if page not found
        if (!$page) {
            abort(404, 'Page not found');
        }

        // Check if page is published or user is admin
        $isAdmin = Auth::check() && Auth::user()->isAdmin();

        if (!$page->isPublished() && !$isAdmin) {
            abort(404, 'Page not found');
        }

        // Extract table of contents from content
        $tableOfContents = $page->getTableOfContents();

        // Get content with anchor IDs injected
        $contentWithAnchors = $page->getContentWithAnchors();

        // Return view with page data
        return view('pages.show', [
            'page' => $page,
            'tableOfContents' => $tableOfContents,
            'contentWithAnchors' => $contentWithAnchors,
        ]);
    }

    /**
     * Preview a page (admin only).
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function preview(int $id)
    {
        // Check if user is admin
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized');
        }

        // Find page by ID with eager loading
        $page = Page::with(['resourceContributors' => function ($query) {
            $query->where('is_active', true)
                ->orderBy('page_resource_contributor.order');
        }])->findOrFail($id);

        // Extract table of contents from content
        $tableOfContents = $page->getTableOfContents();

        // Get content with anchor IDs injected
        $contentWithAnchors = $page->getContentWithAnchors();

        // Return view with page data and preview flag
        return view('pages.show', [
            'page' => $page,
            'tableOfContents' => $tableOfContents,
            'contentWithAnchors' => $contentWithAnchors,
            'isPreview' => true,
        ]);
    }
}
