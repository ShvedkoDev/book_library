<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\BookNoteController;
use App\Http\Controllers\BookReviewController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\Api\ShareTrackingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // Check if a homepage CMS page exists
    $homepage = \App\Models\Page::published()->homepage()->first();

    if ($homepage) {
        return app(\App\Http\Controllers\PageController::class)->show($homepage->slug);
    }

    // Fall back to welcome view if no homepage is set
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// API Routes
// Share tracking - NO AUTH REQUIRED (as per requirements)
Route::post('/api/track-share', [ShareTrackingController::class, 'trackShare'])->name('api.track-share');

// Library routes - PUBLIC ACCESS (browsing and viewing books)
Route::get('/library', [LibraryController::class, 'index'])->name('library.index');

// Library routes - REQUIRES AUTHENTICATION (interactive features)
// IMPORTANT: These specific routes must come BEFORE the {slug} route to avoid matching conflicts
Route::middleware(['auth', 'verified'])->group(function () {
    // PDF viewing and downloading
    Route::get('/library/book/{book}/viewer/{file}', [LibraryController::class, 'viewPdfViewer'])->name('library.view-pdf');
    Route::get('/library/book/{book}/view-pdf/{file}', [LibraryController::class, 'viewPdf'])->name('library.view-pdf-direct');
    Route::get('/library/book/{book}/download/{file}', [LibraryController::class, 'download'])->name('library.download');

    // Book interactions (rating, review, access request)
    Route::post('/library/book/{book}/request-access', [LibraryController::class, 'requestAccess'])->name('library.request-access');
    Route::post('/library/book/{book}/rate', [LibraryController::class, 'submitRating'])->name('library.rate');
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
});

require __DIR__.'/auth.php';

// CMS Pages - Catch-all route (must be last to avoid conflicts with other routes)
Route::get('/{slug}', [PageController::class, 'show'])->name('pages.show')->where('slug', '[a-z0-9\-]+');
