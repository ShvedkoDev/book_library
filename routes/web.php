<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\BookNoteController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\Api\ShareTrackingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// API Routes
// Share tracking - NO AUTH REQUIRED (as per requirements)
Route::post('/api/track-share', [ShareTrackingController::class, 'trackShare'])->name('api.track-share');

// Library routes (requires authentication)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/library', [LibraryController::class, 'index'])->name('library.index');
    Route::get('/library/book/{slug}', [LibraryController::class, 'show'])->name('library.show');
    Route::get('/library/book/{book}/view-pdf/{file}', [LibraryController::class, 'viewPdf'])->name('library.view-pdf');
    Route::get('/library/book/{book}/download/{file}', [LibraryController::class, 'download'])->name('library.download');
    Route::post('/library/book/{book}/request-access', [LibraryController::class, 'requestAccess'])->name('library.request-access');
    Route::post('/library/book/{book}/rate', [LibraryController::class, 'submitRating'])->name('library.rate');
    Route::post('/library/book/{book}/review', [LibraryController::class, 'submitReview'])->name('library.review');

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

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// CMS Page preview route (admin only - authorization checked in controller)
Route::middleware('auth')->get('/admin/pages/{id}/preview', [PageController::class, 'preview'])->name('pages.preview');

require __DIR__.'/auth.php';

// CMS Pages - Catch-all route (must be last to avoid conflicts with other routes)
Route::get('/{slug}', [PageController::class, 'show'])->name('pages.show')->where('slug', '[a-z0-9\-]+');
