<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\BookNoteController;
use App\Http\Controllers\BookReviewController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\Api\ShareTrackingController;
use App\Services\TarStreamService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    // Check if a homepage CMS page exists
    $homepage = \App\Models\Page::published()->homepage()->first();

    if ($homepage) {
        return app(\App\Http\Controllers\PageController::class)->show($homepage->slug);
    }

    // Fall back to welcome view if no homepage is set
    return view('welcome');
});

// Temporary debug route - check logo paths on production
Route::get('/debug/pdf-logos', function () {
    $logoFiles = ['NDOE.png', 'iREi-top.png', 'C4GTS.png'];
    $results = [];

    foreach ($logoFiles as $filename) {
        $paths = [
            'public_path' => public_path('library-assets/images/' . $filename),
            'base_path' => base_path('public/library-assets/images/' . $filename),
            'storage_path' => storage_path('app/public/library-assets/images/' . $filename),
        ];

        $results[$filename] = [];
        foreach ($paths as $label => $path) {
            $results[$filename][$label] = [
                'path' => $path,
                'exists' => file_exists($path),
                'readable' => file_exists($path) && is_readable($path),
                'filesize' => file_exists($path) ? filesize($path) : null,
            ];
        }
    }

    return response()->json([
        'php_sapi' => php_sapi_name(),
        'base_path' => base_path(),
        'public_path' => public_path(),
        'storage_path' => storage_path(),
        'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'not set',
        'script_filename' => $_SERVER['SCRIPT_FILENAME'] ?? 'not set',
        'results' => $results,
    ], 200, [], JSON_PRETTY_PRINT);
})->name('debug.pdf-logos');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// API Routes
// Share tracking - NO AUTH REQUIRED (as per requirements)
Route::post('/api/track-share', [ShareTrackingController::class, 'trackShare'])->name('api.track-share');

// Library routes - PUBLIC ACCESS (browsing and viewing books)
Route::get('/library', [LibraryController::class, 'index'])->name('library.index');

// PDF viewing - PUBLIC ACCESS (no login required, views are still tracked)
Route::get('/library/book/{book}/viewer/{file}', [LibraryController::class, 'viewPdfViewer'])->name('library.view-pdf');
Route::get('/library/book/{book}/view-pdf/{file}', [LibraryController::class, 'viewPdf'])->name('library.view-pdf-direct');

// Library routes - REQUIRES AUTHENTICATION (interactive features)
// IMPORTANT: These specific routes must come BEFORE the {slug} route to avoid matching conflicts
Route::middleware(['auth', 'verified'])->group(function () {
    // PDF downloading (requires login)
    Route::get('/library/book/{book}/download/{file}', [LibraryController::class, 'download'])->name('library.download');

    // Book interactions (rating, review, access request)
    Route::post('/library/book/{book}/request-access', [LibraryController::class, 'requestAccess'])->name('library.request-access');
    Route::post('/library/book/{book}/rate', [LibraryController::class, 'submitRating'])->name('library.rate');
    Route::delete('/library/book/{book}/rate', [LibraryController::class, 'deleteRating'])->name('library.rate.delete');
    Route::post('/library/book/{book}/review', [LibraryController::class, 'submitReview'])->name('library.review');
    Route::put('/library/reviews/{review}', [BookReviewController::class, 'update'])->name('library.reviews.update');
    Route::delete('/library/reviews/{review}', [BookReviewController::class, 'destroy'])->name('library.reviews.destroy');

    // Bookmark routes
    Route::post('/library/book/{book}/bookmark', [BookmarkController::class, 'toggle'])->name('library.bookmark');
    Route::get('/my-bookmarks', [BookmarkController::class, 'index'])->name('bookmarks.index');
    Route::delete('/bookmarks/{bookmark}', [BookmarkController::class, 'destroy'])->name('bookmarks.destroy');

    // Notes routes
    Route::get('/library/book/{book}/notes', [BookNoteController::class, 'index'])->name('library.notes.index');
    Route::post('/library/book/{book}/notes', [BookNoteController::class, 'store'])->name('library.notes.store');
    Route::put('/notes/{note}', [BookNoteController::class, 'update'])->name('library.notes.update');
    Route::delete('/notes/{note}', [BookNoteController::class, 'destroy'])->name('library.notes.destroy');
});

// Book detail page - PUBLIC ACCESS (must come AFTER specific routes to avoid conflicts)
Route::get('/library/book/{slug}', [LibraryController::class, 'show'])->name('library.show');

Route::middleware('auth')->group(function () {
    // Basic profile routes (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // User activity and interaction history routes
    Route::get('/my-activity', [UserProfileController::class, 'activity'])->name('profile.activity');
    Route::get('/my-activity/ratings', [UserProfileController::class, 'ratings'])->name('profile.ratings');
    Route::get('/my-activity/reviews', [UserProfileController::class, 'reviews'])->name('profile.reviews');
    Route::get('/my-activity/downloads', [UserProfileController::class, 'downloads'])->name('profile.downloads');
    Route::get('/my-activity/bookmarks', [UserProfileController::class, 'bookmarks'])->name('profile.bookmarks');
    Route::get('/my-activity/notes', [UserProfileController::class, 'notes'])->name('profile.notes');
    Route::get('/my-activity/timeline', [UserProfileController::class, 'timeline'])->name('profile.timeline');

    // Admin routes to view other users' activities
    Route::middleware('can:viewAny,App\Models\User')->group(function () {
        Route::get('/admin/users/{user}/activity', [UserProfileController::class, 'viewUserActivity'])->name('admin.users.activity');
        Route::get('/admin/users/{user}/ratings', [UserProfileController::class, 'viewUserRatings'])->name('admin.users.ratings');
        Route::get('/admin/users/{user}/reviews', [UserProfileController::class, 'viewUserReviews'])->name('admin.users.reviews');
        Route::get('/admin/users/{user}/downloads', [UserProfileController::class, 'viewUserDownloads'])->name('admin.users.downloads');
    });
});

// CMS Page preview route (admin only - authorization checked in controller)
Route::middleware('auth')->get('/admin/pages/{id}/preview', [PageController::class, 'preview'])->name('pages.preview');

// Sitemap route
Route::get('/sitemap', [SitemapController::class, 'index'])->name('sitemap');

// CSV Template & Export Download routes (admin only)
Route::middleware(['auth'])->group(function () {
    Route::get('/csv/download-template/{type}', function ($type) {
        $filename = $type === 'example' ? 'book-import-example.csv' : 'book-import-template.csv';
        $filePath = storage_path('csv-templates/' . $filename);

        if (!file_exists($filePath)) {
            abort(404, 'Template file not found');
        }

        return response()->download($filePath, $filename);
    })->name('csv.download-template');

    Route::get('/csv/download-export/{filename}', function ($filename) {
        $filePath = storage_path('csv-exports/' . $filename);

        if (!file_exists($filePath)) {
            abort(404, 'Export file not found');
        }

        // Check if file is older than 24 hours and delete
        if (filemtime($filePath) < time() - 86400) {
            unlink($filePath);
            abort(404, 'Export file has expired (24 hour limit)');
        }

        return response()->download($filePath, $filename)->deleteFileAfterSend(false);
    })->name('csv.download-export');

    // Admin media download route
    Route::get('/admin/media/download/{file}', function ($file) {
        $path = base64_decode($file);

        if (!Storage::disk('public')->exists($path)) {
            abort(404, 'File not found');
        }

        return Storage::disk('public')->download($path, basename($path));
    })->name('admin.media.download');

    // Admin backup download route
    Route::get('/admin/backups/download/{file}', function ($file) {
        $filePath = storage_path('app/full-backups/' . $file);
        if (file_exists($filePath)) {
            return response()->download($filePath, $file);
        }
        // If requesting .tar that doesn't exist, stream from folder
        if (str_ends_with($file, '.tar')) {
            $base = basename($file, '.tar');
            $dirPath = storage_path('app/full-backups/' . $base);
            if (is_dir($dirPath)) {
                return TarStreamService::streamDirectoryAsTar($dirPath, $file);
            }
        }
        abort(404, 'Backup file not found');
    })->name('admin.backups.download');
});

require __DIR__.'/auth.php';

// TEMPORARY DEBUG ROUTE - Remove after debugging
Route::get('/debug-pdf-cover/{book}', function (\App\Models\Book $book) {
    $pdfCoverService = new \App\Services\PdfCoverService();

    // Use reflection to access the protected buildCoverData method
    $reflection = new \ReflectionClass($pdfCoverService);
    $method = $reflection->getMethod('buildCoverData');
    $method->setAccessible(true);

    // Get the user (if authenticated) or null
    $user = auth()->user();

    // Call the protected method
    $data = $method->invoke($pdfCoverService, $book, $user);

    // Render the view with the data
    return view('pdf.cover', $data);
})->name('debug.pdf.cover');

// CMS Pages - Catch-all route (must be last to avoid conflicts with other routes)
Route::get('/{slug}', [PageController::class, 'show'])->name('pages.show')->where('slug', '[a-z0-9\-]+');
