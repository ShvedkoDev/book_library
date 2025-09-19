<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CmsAdminController;

/*
|--------------------------------------------------------------------------
| CMS Admin Routes
|--------------------------------------------------------------------------
|
| These routes handle administrative functions for the CMS that are
| not handled by Filament. Most admin functionality will be in Filament
| but these routes can be used for custom admin features.
|
*/

// Dashboard and overview routes
Route::get('/', [CmsAdminController::class, 'dashboard'])
    ->name('cms.admin.dashboard');

Route::get('/dashboard', [CmsAdminController::class, 'dashboard'])
    ->name('cms.admin.dashboard.main');

// Bulk operations routes
Route::prefix('bulk')->group(function () {
    // Bulk page operations
    Route::post('/pages/publish', [CmsAdminController::class, 'bulkPublishPages'])
        ->name('cms.admin.bulk.pages.publish');

    Route::post('/pages/unpublish', [CmsAdminController::class, 'bulkUnpublishPages'])
        ->name('cms.admin.bulk.pages.unpublish');

    Route::post('/pages/delete', [CmsAdminController::class, 'bulkDeletePages'])
        ->name('cms.admin.bulk.pages.delete');

    // Bulk category operations
    Route::post('/categories/activate', [CmsAdminController::class, 'bulkActivateCategories'])
        ->name('cms.admin.bulk.categories.activate');

    Route::post('/categories/deactivate', [CmsAdminController::class, 'bulkDeactivateCategories'])
        ->name('cms.admin.bulk.categories.deactivate');
});

// Cache management routes
Route::prefix('cache')->group(function () {
    Route::post('/clear', [CmsAdminController::class, 'clearCache'])
        ->name('cms.admin.cache.clear');

    Route::post('/warm', [CmsAdminController::class, 'warmCache'])
        ->name('cms.admin.cache.warm');

    Route::get('/status', [CmsAdminController::class, 'cacheStatus'])
        ->name('cms.admin.cache.status');
});

// SEO tools routes
Route::prefix('seo')->group(function () {
    Route::get('/analyze', [CmsAdminController::class, 'seoAnalyze'])
        ->name('cms.admin.seo.analyze');

    Route::post('/generate-sitemap', [CmsAdminController::class, 'generateSitemap'])
        ->name('cms.admin.seo.sitemap.generate');

    Route::get('/meta-tags/missing', [CmsAdminController::class, 'missingMetaTags'])
        ->name('cms.admin.seo.meta.missing');
});

// Media management routes
Route::prefix('media')->group(function () {
    Route::post('/optimize', [CmsAdminController::class, 'optimizeMedia'])
        ->name('cms.admin.media.optimize');

    Route::get('/usage', [CmsAdminController::class, 'mediaUsage'])
        ->name('cms.admin.media.usage');

    Route::get('/unused', [CmsAdminController::class, 'unusedMedia'])
        ->name('cms.admin.media.unused');

    Route::post('/cleanup', [CmsAdminController::class, 'cleanupMedia'])
        ->name('cms.admin.media.cleanup');
});

// Import/Export routes
Route::prefix('import-export')->group(function () {
    Route::post('/pages/export', [CmsAdminController::class, 'exportPages'])
        ->name('cms.admin.export.pages');

    Route::post('/pages/import', [CmsAdminController::class, 'importPages'])
        ->name('cms.admin.import.pages');

    Route::post('/settings/export', [CmsAdminController::class, 'exportSettings'])
        ->name('cms.admin.export.settings');

    Route::post('/settings/import', [CmsAdminController::class, 'importSettings'])
        ->name('cms.admin.import.settings');
});

// Analytics and reports routes
Route::prefix('reports')->group(function () {
    Route::get('/analytics', [CmsAdminController::class, 'analytics'])
        ->name('cms.admin.reports.analytics');

    Route::get('/popular-pages', [CmsAdminController::class, 'popularPages'])
        ->name('cms.admin.reports.popular');

    Route::get('/search-queries', [CmsAdminController::class, 'searchQueries'])
        ->name('cms.admin.reports.search');

    Route::get('/performance', [CmsAdminController::class, 'performance'])
        ->name('cms.admin.reports.performance');
});

// System maintenance routes
Route::prefix('system')->group(function () {
    Route::get('/health-check', [CmsAdminController::class, 'healthCheck'])
        ->name('cms.admin.system.health');

    Route::post('/backup', [CmsAdminController::class, 'createBackup'])
        ->name('cms.admin.system.backup');

    Route::get('/logs', [CmsAdminController::class, 'viewLogs'])
        ->name('cms.admin.system.logs');

    Route::post('/maintenance', [CmsAdminController::class, 'toggleMaintenance'])
        ->name('cms.admin.system.maintenance');
});

// API routes for admin panel interactions
Route::prefix('api')->group(function () {
    // Quick edit functionality
    Route::patch('/pages/{id}/quick-edit', [CmsAdminController::class, 'quickEditPage'])
        ->name('cms.admin.api.pages.quick-edit')
        ->where('id', '[0-9]+');

    // Live preview
    Route::post('/pages/preview', [CmsAdminController::class, 'previewPage'])
        ->name('cms.admin.api.pages.preview');

    // Auto-save
    Route::post('/pages/{id}/auto-save', [CmsAdminController::class, 'autoSavePage'])
        ->name('cms.admin.api.pages.auto-save')
        ->where('id', '[0-9]+');

    // Category tree operations
    Route::post('/categories/reorder', [CmsAdminController::class, 'reorderCategories'])
        ->name('cms.admin.api.categories.reorder');

    // Content block operations
    Route::post('/content-blocks/reorder', [CmsAdminController::class, 'reorderContentBlocks'])
        ->name('cms.admin.api.blocks.reorder');

    // Settings operations
    Route::get('/settings/{group}', [CmsAdminController::class, 'getSettings'])
        ->name('cms.admin.api.settings.get');

    Route::post('/settings/{group}', [CmsAdminController::class, 'saveSettings'])
        ->name('cms.admin.api.settings.save');
});