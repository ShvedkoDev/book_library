<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LibraryController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Library routes (requires authentication)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/library', [LibraryController::class, 'index'])->name('library.index');
    Route::get('/library/book/{slug}', [LibraryController::class, 'show'])->name('library.show');
    Route::get('/library/book/{book}/view-pdf/{file}', [LibraryController::class, 'viewPdf'])->name('library.view-pdf');
    Route::get('/library/book/{book}/download/{file}', [LibraryController::class, 'download'])->name('library.download');
    Route::post('/library/book/{book}/request-access', [LibraryController::class, 'requestAccess'])->name('library.request-access');
    Route::post('/library/book/{book}/rate', [LibraryController::class, 'submitRating'])->name('library.rate');
    Route::post('/library/book/{book}/review', [LibraryController::class, 'submitReview'])->name('library.review');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
