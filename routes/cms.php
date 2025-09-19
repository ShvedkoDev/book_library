<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CmsController;

/*
|--------------------------------------------------------------------------
| CMS Frontend Routes
|--------------------------------------------------------------------------
|
| These routes handle the frontend display of CMS content including
| pages, categories, search functionality, and SEO-related routes.
|
*/

// Page routes
Route::get('/page/{slug}', [CmsController::class, 'showPage'])
    ->name('cms.page.show')
    ->where('slug', '[a-zA-Z0-9\-_]+');

// Category routes
Route::get('/category/{slug}', [CmsController::class, 'showCategory'])
    ->name('cms.category.show')
    ->where('slug', '[a-zA-Z0-9\-_]+');

// Search routes
Route::get('/search', [CmsController::class, 'search'])
    ->name('cms.search');

Route::post('/search', [CmsController::class, 'search'])
    ->name('cms.search.post');

// SEO and utility routes
Route::get('/sitemap.xml', [CmsController::class, 'sitemap'])
    ->name('cms.sitemap');

Route::get('/feed', [CmsController::class, 'feed'])
    ->name('cms.feed');

Route::get('/feed.xml', [CmsController::class, 'feed'])
    ->name('cms.feed.xml');

// CMS home/index route (optional - can be used for CMS landing page)
Route::get('/', [CmsController::class, 'index'])
    ->name('cms.index');

// API routes for frontend interactions (AJAX)
Route::prefix('api')->group(function () {
    // Page view tracking
    Route::post('/page/{id}/view', [CmsController::class, 'trackPageView'])
        ->name('cms.api.page.view')
        ->where('id', '[0-9]+');

    // Search suggestions
    Route::get('/search/suggestions', [CmsController::class, 'searchSuggestions'])
        ->name('cms.api.search.suggestions');

    // Load more pages (for infinite scroll)
    Route::get('/pages/load-more', [CmsController::class, 'loadMorePages'])
        ->name('cms.api.pages.load-more');

    // Category pages with pagination
    Route::get('/category/{id}/pages', [CmsController::class, 'getCategoryPages'])
        ->name('cms.api.category.pages')
        ->where('id', '[0-9]+');
});