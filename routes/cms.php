<?php

use App\Http\Controllers\Cms\CmsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| CMS Routes
|--------------------------------------------------------------------------
|
| Here are the frontend routes for the CMS system. These routes handle
| public-facing content display including pages, categories, search,
| sitemap, and RSS feeds with proper SEO-friendly URLs and caching.
|
*/

// CMS Frontend Routes
Route::prefix('')->group(function () {

    // Search functionality
    Route::get('/search', [CmsController::class, 'searchPages'])
        ->name('cms.search');

    // Sitemap and feeds
    Route::get('/sitemap.xml', [CmsController::class, 'sitemapXml'])
        ->name('cms.sitemap');

    Route::get('/feed', [CmsController::class, 'feedRss'])
        ->name('cms.feed');

    Route::get('/rss', [CmsController::class, 'feedRss'])
        ->name('cms.rss');

    // Category pages - must come before page routes to avoid conflicts
    Route::get('/category/{categorySlug}', [CmsController::class, 'categoryPages'])
        ->name('cms.category')
        ->where('categorySlug', '[a-z0-9\-]+');

    // Individual pages - this should be last to avoid conflicts
    Route::get('/page/{slug}', [CmsController::class, 'showPage'])
        ->name('cms.page')
        ->where('slug', '[a-z0-9\-]+');

    // Alternative route pattern for pages at root level (optional)
    // Uncomment if you want pages accessible directly at /{slug}
    // Route::get('/{slug}', [CmsController::class, 'showPage'])
    //     ->name('cms.page.root')
    //     ->where('slug', '[a-z0-9\-]+')
    //     ->middleware('cms.page_fallback'); // Custom middleware to check if it's not an existing route
});

// API Routes for CMS (optional - for AJAX requests)
Route::prefix('api/cms')->group(function () {

    // Live search API
    Route::get('/search/suggest', [CmsController::class, 'searchSuggestions'])
        ->name('cms.api.search.suggest');

    // Page views tracking
    Route::post('/page/{slug}/view', [CmsController::class, 'trackPageView'])
        ->name('cms.api.page.view')
        ->where('slug', '[a-z0-9\-]+');

    // Related pages API
    Route::get('/page/{slug}/related', [CmsController::class, 'getRelatedPages'])
        ->name('cms.api.page.related')
        ->where('slug', '[a-z0-9\-]+');
});