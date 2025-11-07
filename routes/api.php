<?php

use App\Http\Controllers\Admin\BulkEditingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Bulk Editing API Routes
Route::prefix('admin/bulk-editing')->middleware('auth:sanctum')->group(function () {
    Route::get('/books', [BulkEditingController::class, 'index']);
    Route::post('/books/update', [BulkEditingController::class, 'bulkUpdate']);
});
