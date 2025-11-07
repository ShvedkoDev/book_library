<?php

use App\Http\Controllers\Admin\BulkEditingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Bulk Editing API Routes
Route::prefix('admin/bulk-editing')->middleware(['web', 'auth'])->group(function () {
    Route::get('/books', [BulkEditingController::class, 'index']);
    Route::post('/books/update', [BulkEditingController::class, 'bulkUpdate']);
});

// Lookup data for dropdowns
Route::prefix('admin')->middleware(['web', 'auth'])->group(function () {
    Route::get('/publishers', [BulkEditingController::class, 'publishers']);
    Route::get('/collections', [BulkEditingController::class, 'collections']);
    Route::get('/languages', [BulkEditingController::class, 'languages']);
    Route::get('/creators', [BulkEditingController::class, 'creators']);
});
