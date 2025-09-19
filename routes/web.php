<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Cms\CmsController;
use App\Services\Cms\CmsSeoService;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// CMS Frontend Routes
Route::prefix('cms')->name('cms.')->group(function () {
    // Search
    Route::get('/search', [CmsController::class, 'search'])->name('search');

    // Category pages
    Route::get('/category/{slug}', [CmsController::class, 'category'])->name('category.show');

    // RSS Feed
    Route::get('/feed', [CmsController::class, 'feed'])->name('feed');

    // CMS Pages (must be last to avoid conflicts)
    Route::get('/{slug}', [CmsController::class, 'show'])->name('page.show');
});

// Alternative simpler URL structure (optional - comment out above and use this if preferred)
// Route::get('/page/{slug}', [CmsController::class, 'show'])->name('cms.page.show');
// Route::get('/category/{slug}', [CmsController::class, 'category'])->name('cms.category.show');
// Route::get('/search', [CmsController::class, 'search'])->name('cms.search');

// Sitemap routes
Route::get('/sitemap.xml', function () {
    $seoService = app(CmsSeoService::class);
    $sitemap = $seoService->generateSitemap();

    return response($sitemap, 200)
        ->header('Content-Type', 'application/xml');
})->name('sitemap');

Route::get('/sitemap-index.xml', function () {
    $seoService = app(CmsSeoService::class);
    $sitemap = $seoService->generateSitemapIndex();

    return response($sitemap, 200)
        ->header('Content-Type', 'application/xml');
})->name('sitemap.index');

Route::get('/sitemap-pages.xml', function () {
    $seoService = app(CmsSeoService::class);
    $sitemap = $seoService->generatePagesSitemap();

    return response($sitemap, 200)
        ->header('Content-Type', 'application/xml');
})->name('sitemap.pages');

Route::get('/sitemap-categories.xml', function () {
    $seoService = app(CmsSeoService::class);
    $sitemap = $seoService->generateCategoriesSitemap();

    return response($sitemap, 200)
        ->header('Content-Type', 'application/xml');
})->name('sitemap.categories');

require __DIR__.'/auth.php';
